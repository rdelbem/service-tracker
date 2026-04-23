import { describe, it, expect, beforeEach, afterEach, vi } from "vitest";
import { render, screen, waitFor, within, fireEvent } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import Progress from "../Progress";

const {
  mockInViewState,
  useInViewStoreMock,
  mockProgressStore,
  useProgressStoreMock,
  mockFetchGet,
  mockPut,
  mockPost,
  mockDel,
  mockToastSuccess,
  mockToastError,
  mockShowConfirm,
  mockShowAlert,
} = vi.hoisted(() => {
  const mockInViewState = {
    view: "progress",
    userId: "user-1",
    caseId: "case-1",
    name: "",
    navigate: vi.fn(),
  };

  const useInViewStoreMock = vi.fn((selector?: (state: typeof mockInViewState) => any) =>
    typeof selector === "function" ? selector(mockInViewState) : mockInViewState
  );

  const mockProgressStore = {
    status: [
      {
        id: "st-1",
        _id_case: "case-1",
        id_user: "user-1",
        text: "Initial status",
        created_at: "2024-01-01T09:00:00",
        attachments: [],
      },
    ],
    caseTitle: "Fallback Case Title",
    loadingStatus: false,
    postStatus: vi.fn(),
    getStatus: vi.fn(),
    uploadFiles: vi.fn().mockResolvedValue([]),
  };

  const useProgressStoreMock = vi.fn((selector?: (state: typeof mockProgressStore) => any) =>
    typeof selector === "function" ? selector(mockProgressStore) : mockProgressStore
  );

  return {
    mockInViewState,
    useInViewStoreMock,
    mockProgressStore,
    useProgressStoreMock,
    mockFetchGet: vi.fn(),
    mockPut: vi.fn(),
    mockPost: vi.fn(),
    mockDel: vi.fn(),
    mockToastSuccess: vi.fn(),
    mockToastError: vi.fn(),
    mockShowConfirm: vi.fn(),
    mockShowAlert: vi.fn(),
  };
});

vi.mock("../../../stores/inViewStore", () => ({
  useInViewStore: useInViewStoreMock,
}));

vi.mock("../../../stores/progressStore", () => ({
  useProgressStore: useProgressStoreMock,
}));

vi.mock("../../../utils/fetch", () => ({
  get: mockFetchGet,
  put: mockPut,
  post: mockPost,
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
  showAlert: mockShowAlert,
}));

vi.mock("../Spinner", () => ({
  default: () => <div data-testid="spinner">Loading...</div>,
}));

vi.mock("../Status", () => ({
  default: ({ text }: { text: string }) => <div>{text}</div>,
}));

vi.mock("../UserAttachments", () => ({
  default: ({ idUser }: { idUser: string }) => <div>Attachments for {idUser}</div>,
}));

function mockDefaultFetches(caseStatus: "open" | "close" = "open") {
  mockFetchGet.mockImplementation((url: string) => {
    if (url.includes("/users/staff")) {
      return Promise.resolve({
        data: {
          success: true,
          data: [
            { id: "1", name: "Alice", role: "administrator" },
            { id: "2", name: "Bob", role: "editor" },
          ],
          error_code: null,
          message: null,
          meta: {},
        },
      });
    }

    if (url.includes("/cases/user-1?page=1&per_page=6")) {
      return Promise.resolve({
        data: {
          success: true,
          data: [
            {
              id: "case-1",
              id_user: "user-1",
              title: "Case Alpha",
              status: caseStatus,
              owner_id: "1",
              start_at: "2024-01-01T10:00:00",
              due_at: "2024-01-05T10:00:00",
            },
          ],
          error_code: null,
          message: null,
          meta: { pagination: { total_pages: 1 } },
        },
      });
    }

    if (url.includes("/users?page=1&per_page=100")) {
      return Promise.resolve({
        data: {
          success: true,
          data: [{ id: "user-1", name: "John Client" }],
          error_code: null,
          message: null,
          meta: {},
        },
      });
    }

    return Promise.resolve({ data: { success: true, data: [], error_code: null, message: null, meta: {} } });
  });
}

describe("Progress component", () => {
  beforeEach(() => {
    vi.clearAllMocks();
    mockInViewState.view = "progress";
    mockInViewState.userId = "user-1";
    mockInViewState.caseId = "case-1";
    mockInViewState.name = "";
    mockInViewState.navigate = vi.fn();

    mockProgressStore.status = [
      {
        id: "st-1",
        _id_case: "case-1",
        id_user: "user-1",
        text: "Initial status",
        created_at: "2024-01-01T09:00:00",
        attachments: [],
      },
    ] as any;
    mockProgressStore.caseTitle = "Fallback Case Title";
    mockProgressStore.loadingStatus = false;
    mockProgressStore.postStatus = vi.fn().mockResolvedValue(undefined);
    mockProgressStore.getStatus = vi.fn().mockResolvedValue(undefined);
    mockProgressStore.uploadFiles = vi.fn().mockResolvedValue([]);

    mockPut.mockResolvedValue({ data: {} });
    mockPost.mockResolvedValue({ data: {} });
    mockDel.mockResolvedValue({ data: {} });
    mockShowConfirm.mockResolvedValue(true);
    mockShowAlert.mockResolvedValue(undefined);
    mockDefaultFetches();

    vi.spyOn(console, "error").mockImplementation(() => {});
  });

  afterEach(() => {
    vi.restoreAllMocks();
  });

  it("does not render when view is not progress", () => {
    mockInViewState.view = "cases";
    const { container } = render(<Progress />);

    expect(container.firstChild).toBeNull();
  });

  it("loads progress and case/client data on mount", async () => {
    render(<Progress />);

    await waitFor(() => {
      expect(mockProgressStore.getStatus).toHaveBeenCalledWith("case-1", false, "Fallback Case Title");
    });

    await waitFor(() => {
      expect(screen.getByText("John Client")).toBeInTheDocument();
    });

    expect(mockFetchGet).toHaveBeenCalledWith(
      expect.stringContaining("/users/staff"),
      expect.anything()
    );
  });

  it("posts a status update successfully", async () => {
    const user = userEvent.setup();
    render(<Progress />);

    await waitFor(() => expect(mockProgressStore.getStatus).toHaveBeenCalled());

    await user.click(screen.getByRole("button", { name: /add status update/i }));
    await user.type(
      screen.getByPlaceholderText(/type progress details here/i),
      "  New status update  "
    );
    await user.click(screen.getByRole("button", { name: /post update/i }));

    await waitFor(() => {
      expect(mockProgressStore.postStatus).toHaveBeenCalledWith(
        "user-1",
        "case-1",
        "New status update",
        undefined
      );
    });

    expect(screen.getByRole("button", { name: /add status update/i })).toBeInTheDocument();
  });

  it("shows error toast when posting status fails", async () => {
    const user = userEvent.setup();
    mockProgressStore.postStatus = vi.fn().mockRejectedValue(new Error("boom"));

    render(<Progress />);
    await waitFor(() => expect(mockProgressStore.getStatus).toHaveBeenCalled());

    await user.click(screen.getByRole("button", { name: /add status update/i }));
    await user.type(screen.getByPlaceholderText(/type progress details here/i), "Failed post");
    await user.click(screen.getByRole("button", { name: /post update/i }));

    await waitFor(() => {
      expect(mockToastError).toHaveBeenCalledWith("Failed to post status update");
    });
  });

  it("toggles case status only when confirmation is accepted", async () => {
    const user = userEvent.setup();
    mockShowConfirm.mockResolvedValueOnce(false);

    render(<Progress />);
    await waitFor(() => expect(mockProgressStore.getStatus).toHaveBeenCalled());

    await user.click(screen.getByRole("button", { name: /active/i }));

    await waitFor(() => {
      expect(mockShowConfirm).toHaveBeenCalled();
    });
    expect(mockPost).not.toHaveBeenCalled();

    mockShowConfirm.mockResolvedValueOnce(true);
    await user.click(screen.getByRole("button", { name: /active/i }));

    await waitFor(() => {
      expect(mockPost).toHaveBeenCalledWith(
        expect.stringContaining("/cases-status/case-1"),
        null,
        expect.anything()
      );
    });
    expect(mockToastSuccess).toHaveBeenCalledWith("Case is now closed");
  });

  it("deletes case only when confirmation is accepted", async () => {
    const user = userEvent.setup();
    mockShowConfirm.mockResolvedValueOnce(false);

    render(<Progress />);
    await waitFor(() => expect(mockProgressStore.getStatus).toHaveBeenCalled());

    await user.click(screen.getByTitle("Delete Case"));
    await waitFor(() => expect(mockShowConfirm).toHaveBeenCalled());
    expect(mockDel).not.toHaveBeenCalled();

    mockShowConfirm.mockResolvedValueOnce(true);
    await user.click(screen.getByTitle("Delete Case"));

    await waitFor(() => {
      expect(mockDel).toHaveBeenCalledWith(
        expect.stringContaining("/cases/case-1"),
        expect.anything()
      );
    });
    expect(mockToastSuccess).toHaveBeenCalledWith("Case deleted successfully");
    expect(mockInViewState.navigate).toHaveBeenCalledWith("cases", "", "", "");
  });

  it("supports title edit save and cancel", async () => {
    const user = userEvent.setup();
    render(<Progress />);

    await waitFor(() => expect(mockProgressStore.getStatus).toHaveBeenCalled());

    await user.click(screen.getByTitle("Edit title"));
    const input = screen.getByPlaceholderText("Enter case title...");
    await user.clear(input);
    await user.type(input, "Updated Case Title");
    await user.click(screen.getByTitle("Save title"));

    await waitFor(() => {
      expect(mockPut).toHaveBeenCalledWith(
        expect.stringContaining("/cases/case-1"),
        expect.objectContaining({ id_user: "user-1", title: "Updated Case Title" }),
        expect.anything()
      );
    });
    expect(mockToastSuccess).toHaveBeenCalledWith("Case title updated successfully");

    mockPut.mockClear();
    await user.click(screen.getByTitle("Edit title"));
    await user.click(screen.getByTitle("Cancel"));
    expect(screen.queryByPlaceholderText("Enter case title...")).not.toBeInTheDocument();
    expect(mockPut).not.toHaveBeenCalled();
  });

  it("saves and cancels date editing", async () => {
    const user = userEvent.setup();
    render(<Progress />);

    await waitFor(() => expect(mockProgressStore.getStatus).toHaveBeenCalled());

    const startLabel = screen.getByText("Start Date");
    const startHeader = startLabel.parentElement as HTMLElement;
    const startCard = startHeader.parentElement as HTMLElement;

    await user.click(within(startHeader).getByRole("button"));

    const startInput = within(startCard).getByDisplayValue("2024-01-01T10:00");
    fireEvent.change(startInput, { target: { value: "2024-01-02T12:30" } });
    await user.click(within(startCard).getByRole("button", { name: "check" }));

    await waitFor(() => {
      expect(mockPut).toHaveBeenCalledWith(
        expect.stringContaining("/cases/case-1"),
        expect.objectContaining({ start_at: "2024-01-02T12:30" }),
        expect.anything()
      );
    });

    mockPut.mockClear();

    const dueLabel = screen.getByText("Due Date");
    const dueHeader = dueLabel.parentElement as HTMLElement;
    const dueCard = dueHeader.parentElement as HTMLElement;

    await user.click(within(dueHeader).getByRole("button"));
    await user.click(within(dueCard).getByRole("button", { name: "close" }));

    expect(mockPut).not.toHaveBeenCalled();
  });

  it("handles owner change success and failure", async () => {
    render(<Progress />);

    await waitFor(() => expect(mockProgressStore.getStatus).toHaveBeenCalled());

    const ownerSelect = screen.getByRole("combobox") as HTMLSelectElement;

    fireEvent.change(ownerSelect, { target: { value: "2" } });
    await waitFor(() => {
      expect(mockPut).toHaveBeenCalledWith(
        expect.stringContaining("/cases/case-1"),
        expect.objectContaining({ owner_id: "2" }),
        expect.anything()
      );
    });
    expect(mockToastSuccess).toHaveBeenCalledWith("Case owner changed to Bob");

    mockPut.mockRejectedValueOnce(new Error("owner update failed"));
    fireEvent.change(ownerSelect, { target: { value: "2" } });

    await waitFor(() => {
      expect(mockToastError).toHaveBeenCalledWith("Failed to update case owner");
    });
    expect(ownerSelect.value).toBe("2");
  });
});
