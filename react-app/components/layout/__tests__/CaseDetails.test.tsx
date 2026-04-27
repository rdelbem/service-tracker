import { describe, it, expect, beforeEach, afterEach, vi } from "vitest";
import { render, screen, waitFor } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import CaseDetails from "../CaseDetails";

const {
  mockInViewState,
  useInViewStoreMock,
  mockEditCase,
  useCasesStoreMock,
  useClientsStoreMock,
  mockUsers,
  mockFetchGet,
  mockPost,
  mockDel,
  mockToastSuccess,
  mockToastError,
  mockShowConfirm,
} = vi.hoisted(() => {
  const mockInViewState = {
    view: "cases",
    userId: "",
    caseId: "case-1",
    name: "",
    navigate: vi.fn(),
  };

  const useInViewStoreMock = vi.fn((selector?: (state: typeof mockInViewState) => any) =>
    typeof selector === "function" ? selector(mockInViewState) : mockInViewState
  );

  const mockEditCase = vi.fn();
  const useCasesStoreMock = vi.fn((selector?: (state: { editCase: typeof mockEditCase }) => any) => {
    const state = { editCase: mockEditCase };
    return typeof selector === "function" ? selector(state) : state;
  });

  const mockUsers = [{ id: "user-1", name: "John Client", email: "john@test.com" }];
  const useClientsStoreMock: any = vi.fn();
  useClientsStoreMock.getState = vi.fn(() => ({ users: mockUsers }));

  return {
    mockInViewState,
    useInViewStoreMock,
    mockEditCase,
    useCasesStoreMock,
    useClientsStoreMock,
    mockUsers,
    mockFetchGet: vi.fn(),
    mockPost: vi.fn(),
    mockDel: vi.fn(),
    mockToastSuccess: vi.fn(),
    mockToastError: vi.fn(),
    mockShowConfirm: vi.fn(),
  };
});

vi.mock("../../../stores/inViewStore", () => ({
  useInViewStore: useInViewStoreMock,
}));

vi.mock("../../../stores/casesStore", () => ({
  useCasesStore: useCasesStoreMock,
}));

vi.mock("../../../stores/clientsStore", () => ({
  useClientsStore: useClientsStoreMock,
}));

vi.mock("../../../utils/fetch", () => ({
  get: mockFetchGet,
  post: mockPost,
  put: vi.fn(),
  del: mockDel,
}));

vi.mock("react-toastify", () => ({
  toast: {
    success: mockToastSuccess,
    error: mockToastError,
  },
}));

vi.mock("../../ui/Modal", () => ({
  showConfirm: mockShowConfirm,
}));

vi.mock("../Spinner", () => ({
  default: () => <div data-testid="spinner">Loading...</div>,
}));

const baseCase = {
  id: "case-1",
  id_user: "user-1",
  title: "Main Case",
  status: "open",
  description: "Case description",
  created_at: "2024-01-01T12:00:00",
  start_at: "2024-01-02T10:00:00",
  due_at: "2024-01-05T10:00:00",
};

describe("CaseDetails component", () => {
  beforeEach(() => {
    vi.clearAllMocks();

    mockInViewState.view = "cases";
    mockInViewState.caseId = "case-1";
    mockInViewState.navigate = vi.fn();

    useClientsStoreMock.getState.mockReturnValue({ users: mockUsers });

    mockFetchGet.mockResolvedValue({
      data: { success: true, data: [baseCase], error_code: null, message: null, meta: {} },
    });
    mockEditCase.mockResolvedValue(undefined);
    mockPost.mockResolvedValue({ data: {} });
    mockDel.mockResolvedValue({ data: {} });
    mockShowConfirm.mockResolvedValue(true);

    vi.spyOn(console, "error").mockImplementation(() => {});

    (globalThis as any).data = {
      ...(globalThis as any).data,
      root_url: "http://localhost",
      api_url: "service-tracker-stolmc/v1",
      nonce: "nonce",
    };
  });

  afterEach(() => {
    vi.restoreAllMocks();
  });

  it("returns empty fragment when view is not cases", () => {
    mockInViewState.view = "progress";
    const { container } = render(<CaseDetails />);
    expect(container.firstChild).toBeNull();
  });

  it("returns empty fragment when caseId is missing", () => {
    mockInViewState.caseId = "";
    const { container } = render(<CaseDetails />);
    expect(container.firstChild).toBeNull();
  });

  it("renders loading spinner while case is loading", () => {
    mockFetchGet.mockImplementation(() => new Promise(() => {}));

    render(<CaseDetails />);

    expect(screen.getByTestId("spinner")).toBeInTheDocument();
  });

  it("shows case not found when no case matches", async () => {
    mockFetchGet.mockResolvedValue({
      data: { success: true, data: [], error_code: null, message: null, meta: {} },
    });

    render(<CaseDetails />);

    expect(await screen.findByText(/case_not_found/i)).toBeInTheDocument();
  });

  it("loads and renders case details", async () => {
    render(<CaseDetails />);

    expect(await screen.findByText("Main Case")).toBeInTheDocument();
    expect(screen.getByText("client_label: John Client")).toBeInTheDocument();
    expect(screen.getByText("Case description")).toBeInTheDocument();
  });

  it("validates empty title before saving", async () => {
    const user = userEvent.setup();
    render(<CaseDetails />);

    await screen.findByText("Main Case");

    await user.click(screen.getByTitle(/tip_edit_case/i));
    const titleInput = screen.getByRole("textbox");
    await user.clear(titleInput);
    await user.click(screen.getByRole("button", { name: /save/i }));

    expect(mockToastError).toHaveBeenCalledWith("alert_blank_case_title");
    expect(mockEditCase).not.toHaveBeenCalled();
  });

  it("validates date range before saving", async () => {
    const user = userEvent.setup();
    render(<CaseDetails />);

    await screen.findByText("Main Case");

    await user.click(screen.getByTitle(/tip_edit_case/i));
    const dateInputs = screen.getAllByDisplayValue(/2024-01-0[25]T10:00/);
    await user.clear(dateInputs[0]);
    await user.type(dateInputs[0], "2024-01-06T10:00");
    await user.clear(dateInputs[1]);
    await user.type(dateInputs[1], "2024-01-05T10:00");
    await user.click(screen.getByRole("button", { name: /save/i }));

    expect(mockToastError).toHaveBeenCalledWith("add_case_date_help");
    expect(mockEditCase).not.toHaveBeenCalled();
  });

  it("saves edits successfully", async () => {
    const user = userEvent.setup();
    render(<CaseDetails />);

    await screen.findByText("Main Case");

    await user.click(screen.getByTitle(/tip_edit_case/i));
    const titleInput = screen.getByRole("textbox");
    await user.clear(titleInput);
    await user.type(titleInput, "Updated Case");
    await user.click(screen.getByRole("button", { name: /save/i }));

    await waitFor(() => {
      expect(mockEditCase).toHaveBeenCalledWith(
        "case-1",
        "user-1",
        "Updated Case",
        "2024-01-02T10:00",
        "2024-01-05T10:00"
      );
    });

    expect(screen.getByText("Updated Case")).toBeInTheDocument();
  });

  it("toggles status success and error", async () => {
    const user = userEvent.setup();
    render(<CaseDetails />);

    await screen.findByText("Main Case");

    await user.click(screen.getByRole("button", { name: /status_active/i }));

    await waitFor(() => {
      expect(mockPost).toHaveBeenCalledWith(
        expect.stringContaining("/cases-status/case-1"),
        null,
        expect.anything()
      );
    });
    expect(mockToastSuccess).toHaveBeenCalledWith("toast_toggle_base_msg close");

    mockPost.mockRejectedValueOnce(new Error("fail"));
    await user.click(screen.getByRole("button", { name: /closed/i }));
    await waitFor(() => {
      expect(mockToastError).toHaveBeenCalledWith("toast_case_toggled");
    });
  });

  it("does not delete when confirmation is false", async () => {
    const user = userEvent.setup();
    mockShowConfirm.mockResolvedValueOnce(false);

    render(<CaseDetails />);
    await screen.findByText("Main Case");

    await user.click(screen.getByTitle(/tip_delete_case/i));

    await waitFor(() => {
      expect(mockShowConfirm).toHaveBeenCalled();
    });
    expect(mockDel).not.toHaveBeenCalled();
  });

  it("deletes when confirmation is true and navigates back", async () => {
    const user = userEvent.setup();
    mockShowConfirm.mockResolvedValueOnce(true);

    render(<CaseDetails />);
    await screen.findByText("Main Case");

    await user.click(screen.getByTitle(/tip_delete_case/i));

    await waitFor(() => {
      expect(mockDel).toHaveBeenCalledWith(
        expect.stringContaining("/cases/case-1"),
        expect.anything()
      );
    });
    expect(mockToastSuccess).toHaveBeenCalledWith("toast_case_deleted_success");
    expect(mockInViewState.navigate).toHaveBeenCalledWith("cases", "", "", "");
  });

  it("navigates back via back button", async () => {
    const user = userEvent.setup();
    render(<CaseDetails />);

    await screen.findByText("Main Case");
    await user.click(screen.getByRole("button", { name: /btn_back_to_cases/i }));

    expect(mockInViewState.navigate).toHaveBeenCalledWith("cases", "", "", "");
  });
});
