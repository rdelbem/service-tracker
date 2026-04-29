import { describe, it, expect, beforeEach, afterEach, vi } from "vitest";
import { render, screen, fireEvent, waitFor, act } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import ClientsView from "../ClientsView";

const {
  mockInViewState,
  useInViewStoreMock,
  mockClientsStore,
  useClientsStoreMock,
} = vi.hoisted(() => {
  const mockInViewState = {
    view: "clients",
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
      { id: "user-1", name: "John Doe", email: "john@example.com" },
      { id: "user-2", name: "Jane Smith", email: "jane@example.com" },
    ],
    loadingUsers: false,
    searchUsers: vi.fn(),
    page: 1,
    totalPages: 1,
    total: 2,
    setPage: vi.fn(),
    searchQuery: "",
  };

  const useClientsStoreMock = vi.fn((selector?: (state: typeof mockClientsStore) => any) =>
    typeof selector === "function" ? selector(mockClientsStore) : mockClientsStore
  );

  return {
    mockInViewState,
    useInViewStoreMock,
    mockClientsStore,
    useClientsStoreMock,
  };
});

vi.mock("../../../stores/inViewStore", () => ({
  useInViewStore: useInViewStoreMock,
}));

vi.mock("../../../stores/clientsStore", () => ({
  useClientsStore: useClientsStoreMock,
}));

vi.mock("../Spinner", () => ({
  default: () => <div data-testid="spinner">Loading...</div>,
}));

describe("ClientsView component", () => {
  beforeEach(() => {
    vi.clearAllMocks();
    vi.useRealTimers();

    mockInViewState.view = "clients";
    mockInViewState.userId = "";
    mockInViewState.navigate = vi.fn();

    mockClientsStore.users = [
      { id: "user-1", name: "John Doe", email: "john@example.com" },
      { id: "user-2", name: "Jane Smith", email: "jane@example.com" },
    ] as any;
    mockClientsStore.loadingUsers = false;
    mockClientsStore.searchUsers = vi.fn();
    mockClientsStore.page = 1;
    mockClientsStore.totalPages = 1;
    mockClientsStore.total = 2;
    mockClientsStore.setPage = vi.fn();
    mockClientsStore.searchQuery = "";
  });

  afterEach(() => {
    vi.useRealTimers();
    vi.restoreAllMocks();
  });

  it("renders nothing when view is not clients", () => {
    mockInViewState.view = "cases";
    const { container } = render(<ClientsView />);

    expect(container.firstChild).toBeNull();
  });

  it("debounces search calls", async () => {
    vi.useFakeTimers();

    render(<ClientsView />);

    mockClientsStore.searchUsers.mockClear();

    const searchInput = screen.getByPlaceholderText("clients_search_placeholder");
    fireEvent.change(searchInput, { target: { value: "john" } });

    act(() => {
      vi.advanceTimersByTime(349);
    });
    expect(mockClientsStore.searchUsers).not.toHaveBeenCalled();

    act(() => {
      vi.advanceTimersByTime(1);
    });
    expect(mockClientsStore.searchUsers).toHaveBeenCalledWith("john");
  });

  it("renders empty state when no clients are available", () => {
    mockClientsStore.users = [] as any;
    mockClientsStore.total = 0;

    render(<ClientsView />);

    expect(screen.getByText("clients_empty")).toBeInTheDocument();
  });

  it("navigates when selecting a client", async () => {
    const user = userEvent.setup();
    render(<ClientsView />);

    await user.click(screen.getByText("John Doe"));

    expect(mockInViewState.navigate).toHaveBeenCalledWith("clients", "user-1", "", "John Doe");
  });

  it("uses pagination controls to call setPage", async () => {
    const user = userEvent.setup();
    mockClientsStore.page = 2;
    mockClientsStore.totalPages = 3;

    render(<ClientsView />);

    await user.click(screen.getByRole("button", { name: /prev/i }));
    await user.click(screen.getByRole("button", { name: "3" }));
    await user.click(screen.getByRole("button", { name: /next/i }));

    expect(mockClientsStore.setPage).toHaveBeenCalledWith(1);
    expect(mockClientsStore.setPage).toHaveBeenCalledWith(3);
    expect(mockClientsStore.setPage).toHaveBeenCalledWith(3);
  });
});
