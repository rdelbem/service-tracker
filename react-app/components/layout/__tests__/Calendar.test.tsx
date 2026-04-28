import { describe, it, expect, beforeEach, afterEach, vi } from "vitest";
import { render, screen, waitFor, fireEvent } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import Calendar from "../Calendar";

const {
  mockInViewState,
  useInViewStoreMock,
  mockClientsStore,
  useClientsStoreMock,
  mockFetchGet,
} = vi.hoisted(() => {
  const mockInViewState = {
    view: "calendar",
    userId: "",
    caseId: "",
    name: "",
    navigate: vi.fn(),
  };

  const useInViewStoreMock = vi.fn((selector?: (state: typeof mockInViewState) => any) =>
    typeof selector === "function" ? selector(mockInViewState) : mockInViewState
  );

  const mockClientsStore = {
    users: [
      { id: "u1", name: "John Client", email: "john@example.com" },
      { id: "u2", name: "Jane Client", email: "jane@example.com" },
    ],
  };

  const useClientsStoreMock = vi.fn((selector?: (state: typeof mockClientsStore) => any) =>
    typeof selector === "function" ? selector(mockClientsStore) : mockClientsStore
  );

  return {
    mockInViewState,
    useInViewStoreMock,
    mockClientsStore,
    useClientsStoreMock,
    mockFetchGet: vi.fn(),
  };
});

vi.mock("../../../stores/inViewStore", () => ({
  useInViewStore: useInViewStoreMock,
}));

vi.mock("../../../stores/clientsStore", () => ({
  useClientsStore: useClientsStoreMock,
}));

vi.mock("../../../utils/fetch", () => ({
  get: mockFetchGet,
}));

vi.mock("../Spinner", () => ({
  default: () => <div data-testid="spinner">Loading...</div>,
}));

function isoForCurrentMonth(day: number) {
  const now = new Date();
  const year = now.getFullYear();
  const month = String(now.getMonth() + 1).padStart(2, "0");
  const dd = String(day).padStart(2, "0");
  return `${year}-${month}-${dd}`;
}

function calendarResponse() {
  const start = `${isoForCurrentMonth(15)}T10:00:00`;
  const due = `${isoForCurrentMonth(20)}T10:00:00`;
  return {
    cases: [
      {
        id: 101,
        id_user: 1,
        title: "Scheduled Visit",
        status: "open",
        description: "Visit",
        start_at: start,
        due_at: due,
        client_name: "John Client",
      },
    ],
    progress: [
      {
        id: 501,
        id_case: 101,
        id_user: 1,
        text: "Status",
        created_at: `${isoForCurrentMonth(16)}T10:00:00`,
        case_title: "Scheduled Visit",
        client_name: "John Client",
      },
    ],
    date_index: {
      [isoForCurrentMonth(15)]: { starts: [101], ends: [] },
      [isoForCurrentMonth(20)]: { starts: [], ends: [101] },
    },
  };
}

describe("Calendar component", () => {
  beforeEach(() => {
    vi.clearAllMocks();

    mockInViewState.view = "calendar";
    mockInViewState.navigate = vi.fn();

    (globalThis as any).stolmcData = {
      ...(globalThis as any).stolmcData,
      root_url: "http://localhost",
      api_url: "service-tracker-stolmc/v1",
      nonce: "nonce",
    };

    mockFetchGet.mockResolvedValue({
      data: {
        success: true,
        data: calendarResponse(),
        error_code: null,
        message: null,
        meta: {},
      },
    });
  });

  afterEach(() => {
    vi.restoreAllMocks();
  });

  it("returns empty fragment when not in calendar view", () => {
    mockInViewState.view = "cases";
    const { container } = render(<Calendar />);

    expect(container.firstChild).toBeNull();
    expect(mockFetchGet).not.toHaveBeenCalled();
  });

  it("fetches calendar data on mount with month date-range params", async () => {
    render(<Calendar />);

    await waitFor(() => {
      expect(mockFetchGet).toHaveBeenCalled();
    });

    const calledUrl = String(mockFetchGet.mock.calls[0][0]);
    expect(calledUrl).toContain("/calendar?");
    const now = new Date();
    const year = now.getFullYear();
    const month = now.getMonth();
    const expectedStart = `${year}-${String(month + 1).padStart(2, "0")}-01`;
    const expectedEnd = new Date(year, month + 1, 0).toISOString().slice(0, 10);

    expect(calledUrl).toContain(`start=${expectedStart}`);
    expect(calledUrl).toContain(`end=${expectedEnd}`);
  });

  it("includes selected client and status in query params", async () => {
    render(<Calendar />);
    await waitFor(() => expect(mockFetchGet).toHaveBeenCalledTimes(1));

    const selects = screen.getAllByRole("combobox");
    fireEvent.change(selects[0], { target: { value: "u1" } });
    fireEvent.change(selects[1], { target: { value: "open" } });

    await waitFor(() => {
      const calledUrl = String(mockFetchGet.mock.calls.at(-1)?.[0]);
      expect(calledUrl).toContain("id_user=u1");
      expect(calledUrl).toContain("status=open");
    });
  });

  it("refetches data when navigating months", async () => {
    const user = userEvent.setup();
    render(<Calendar />);
    await waitFor(() => expect(mockFetchGet).toHaveBeenCalledTimes(1));

    const initialUrl = String(mockFetchGet.mock.calls[0][0]);
    const nextButton = screen.getByText("chevron_right").closest("button") as HTMLButtonElement;
    await user.click(nextButton);

    await waitFor(() => {
      expect(mockFetchGet).toHaveBeenCalledTimes(2);
    });
    const nextUrl = String(mockFetchGet.mock.calls[1][0]);
    expect(nextUrl).not.toBe(initialUrl);

    const prevButton = screen.getByText("chevron_left").closest("button") as HTMLButtonElement;
    await user.click(prevButton);

    await waitFor(() => {
      expect(mockFetchGet).toHaveBeenCalledTimes(3);
    });
    const backUrl = String(mockFetchGet.mock.calls[2][0]);
    expect(backUrl).toBe(initialUrl);
  });

  it("navigates to progress when a case item is clicked", async () => {
    const user = userEvent.setup();
    render(<Calendar />);

    const caseButtons = await screen.findAllByRole("button", { name: "Scheduled Visit" });
    await user.click(caseButtons[0]);

    expect(mockInViewState.navigate).toHaveBeenCalledWith("progress", "1", "101", "John Client");
  });
});
