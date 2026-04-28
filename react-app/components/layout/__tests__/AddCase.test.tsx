import { describe, it, expect, beforeEach, afterEach, vi } from "vitest";
import { render, screen, waitFor } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import AddCase from "../AddCase";

// Mock dependencies with hoisted variables
const {
  mockInViewStoreState,
  useInViewStoreMock,
  mockClientsStore,
  useClientsStoreMock,
  mockToastError,
} = vi.hoisted(() => {
  const mockInViewStoreState = {
    view: "casesAddNew",
    userId: "",
    caseId: "",
    name: "",
    navigate: vi.fn(),
  };
  const useInViewStoreMock = vi.fn((selector?: (state: typeof mockInViewStoreState) => any) =>
    typeof selector === "function" ? selector(mockInViewStoreState) : mockInViewStoreState
  );

  const mockClientsStore = {
    users: [
      { id: "user-1", name: "John Doe", email: "john@example.com" },
      { id: "user-2", name: "Jane Smith", email: "jane@example.com" },
    ],
  };
  const useClientsStoreMock = vi.fn((selector?: (state: typeof mockClientsStore) => any) =>
    typeof selector === "function" ? selector(mockClientsStore) : mockClientsStore
  );

  const mockToastError = vi.fn();

  return {
    mockInViewStoreState,
    useInViewStoreMock,
    mockClientsStore,
    useClientsStoreMock,
    mockToastError,
  };
});

// Mock casesStore import (dynamic import)
const mockPostCase = vi.fn();
const mockGetCases = vi.fn();
const mockUseCasesStore = {
  getState: vi.fn(() => ({
    postCase: mockPostCase,
    getCases: mockGetCases,
  })),
};
vi.mock("../../../stores/casesStore", () => ({
  useCasesStore: mockUseCasesStore,
}));

vi.mock("../../../stores/inViewStore", () => ({
  useInViewStore: useInViewStoreMock,
}));

vi.mock("../../../stores/clientsStore", () => ({
  useClientsStore: useClientsStoreMock,
}));

vi.mock("react-toastify", () => ({
  toast: {
    error: mockToastError,
  },
}));



describe("AddCase component", () => {
  beforeEach(() => {
    vi.clearAllMocks();
    mockInViewStoreState.view = "casesAddNew";
    mockInViewStoreState.navigate = vi.fn();
    useInViewStoreMock.mockClear();
    useClientsStoreMock.mockClear();
    mockToastError.mockClear();
    mockPostCase.mockReset();
    mockGetCases.mockReset();

    // Mock console.error to suppress error logs
    vi.spyOn(console, 'error').mockImplementation(() => {});
  });

  afterEach(() => {
    vi.restoreAllMocks();
  });

  it("renders add case form when view is 'casesAddNew'", () => {
    render(<AddCase />);

    expect(screen.getByText("add_case_heading")).toBeInTheDocument();
    expect(screen.getByText("add_case_description")).toBeInTheDocument();
    expect(screen.getByPlaceholderText("add_case_client_placeholder")).toBeInTheDocument();
    expect(screen.getByPlaceholderText("add_case_title_placeholder")).toBeInTheDocument();
    expect(screen.getByText("add_case_create_btn")).toBeInTheDocument();
    expect(screen.getByText("btn_cancel")).toBeInTheDocument();
  });

  it("does not render when view is not 'casesAddNew'", () => {
    mockInViewStoreState.view = "cases";
    const { container } = render(<AddCase />);

    expect(container.firstChild).toBeNull();
  });

  it("filters users based on search query", async () => {
    const user = userEvent.setup();
    render(<AddCase />);

    const searchInput = screen.getByPlaceholderText("add_case_client_placeholder");
    await user.type(searchInput, "John");

    // Wait for filtered users to appear
    await waitFor(() => {
      expect(screen.getByText("John Doe")).toBeInTheDocument();
      expect(screen.queryByText("Jane Smith")).not.toBeInTheDocument();
    });
  });

  it("selects a user from dropdown", async () => {
    const user = userEvent.setup();
    render(<AddCase />);

    const searchInput = screen.getByPlaceholderText("add_case_client_placeholder");
    await user.type(searchInput, "John");

    await waitFor(() => {
      expect(screen.getByText("John Doe")).toBeInTheDocument();
    });

    const userItem = screen.getByText("John Doe");
    await user.click(userItem);

    expect(screen.getByText("add_case_client_label: John Doe")).toBeInTheDocument();
  });

  it("shows error when submitting without selecting a client", async () => {
    const user = userEvent.setup();
    render(<AddCase />);

    const submitButton = screen.getByText("add_case_create_btn");
    await user.click(submitButton);

    await waitFor(() => {
      expect(mockToastError).toHaveBeenCalledWith("add_case_select_client");
    });
  });

  it("shows error when submitting without case title", async () => {
    const user = userEvent.setup();
    render(<AddCase />);

    // Select a user first
    const searchInput = screen.getByPlaceholderText("add_case_client_placeholder");
    await user.type(searchInput, "John");

    await waitFor(() => {
      expect(screen.getByText("John Doe")).toBeInTheDocument();
    });

    const userItem = screen.getByText("John Doe");
    await user.click(userItem);

    // Try to submit without title
    const submitButton = screen.getByText("add_case_create_btn");
    await user.click(submitButton);

    await waitFor(() => {
      expect(mockToastError).toHaveBeenCalledWith("add_case_enter_title");
    });
  });

  it("submits form with valid data", async () => {
    const user = userEvent.setup();
    mockPostCase.mockResolvedValue(undefined);
    mockGetCases.mockResolvedValue(undefined);

    render(<AddCase />);

    // Select a user
    const searchInput = screen.getByPlaceholderText("add_case_client_placeholder");
    await user.type(searchInput, "John");

    await waitFor(() => {
      expect(screen.getByText("John Doe")).toBeInTheDocument();
    });

    const userItem = screen.getByText("John Doe");
    await user.click(userItem);

    // Enter case title
    const titleInput = screen.getByPlaceholderText("add_case_title_placeholder");
    await user.type(titleInput, "Test Case Title");

    // Enter description
    const descriptionInput = screen.getByPlaceholderText("add_case_description_placeholder");
    await user.type(descriptionInput, "Test description");

    // Submit form
    const submitButton = screen.getByText("add_case_create_btn");
    await user.click(submitButton);

    // Wait for submission
    await waitFor(() => {
      expect(mockPostCase).toHaveBeenCalled();
      expect(mockInViewStoreState.navigate).toHaveBeenCalledWith("cases", "", "", "");
    });
  });

  it("handles submission error", async () => {
    const user = userEvent.setup();
    const error = new Error("Network error");
    mockPostCase.mockRejectedValue(error);

    render(<AddCase />);

    // Select a user
    const searchInput = screen.getByPlaceholderText("add_case_client_placeholder");
    await user.type(searchInput, "John");

    await waitFor(() => {
      expect(screen.getByText("John Doe")).toBeInTheDocument();
    });

    const userItem = screen.getByText("John Doe");
    await user.click(userItem);

    // Enter case title
    const titleInput = screen.getByPlaceholderText("add_case_title_placeholder");
    await user.type(titleInput, "Test Case Title");

    // Submit form
    const submitButton = screen.getByText("add_case_create_btn");
    await user.click(submitButton);

    await waitFor(() => {
      expect(mockToastError).toHaveBeenCalledWith("Network error");
    });
  });

  it("cancels form and navigates back to cases", async () => {
    const user = userEvent.setup();
    render(<AddCase />);

    const cancelButton = screen.getByText("btn_cancel");
    await user.click(cancelButton);

    expect(mockInViewStoreState.navigate).toHaveBeenCalledWith("cases", "", "", "");
  });
});
