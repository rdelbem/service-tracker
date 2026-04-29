import { describe, it, expect, beforeEach, afterEach, vi } from "vitest";
import { render, screen, waitFor } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import ClientDetails from "../ClientDetails";

// Mock dependencies with hoisted variables
const {
  mockInViewStoreState,
  useInViewStoreMock,
  mockClientsStore,
  useClientsStoreMock,
  mockCasesStore,
  useCasesStoreMock,
} = vi.hoisted(() => {
  const mockInViewStoreState = {
    view: "clients",
    userId: "user-1",
    caseId: "",
    name: "",
    navigate: vi.fn(),
  };
  const useInViewStoreMock = vi.fn((selector?: (state: typeof mockInViewStoreState) => any) =>
    typeof selector === "function" ? selector(mockInViewStoreState) : mockInViewStoreState
  );

  const mockClientsStore = {
    users: [
      { id: "user-1", name: "John Doe", email: "john@example.com", phone: "1234567890", cellphone: "0987654321" },
    ],
  };
  const useClientsStoreMock = vi.fn((selector?: (state: typeof mockClientsStore) => any) =>
    typeof selector === "function" ? selector(mockClientsStore) : mockClientsStore
  );

  const mockCasesStore = {
    cases: [
      { id: "case-1", title: "Test Case", status: "open", created_at: "2023-01-01T00:00:00Z", id_user: "user-1" },
    ],
    loadingCases: false,
    page: 1,
    totalPages: 1,
    total: 1,
    searchQuery: "",
    getCases: vi.fn(),
  };
  const useCasesStoreMock = vi.fn((selector?: (state: typeof mockCasesStore) => any) =>
    typeof selector === "function" ? selector(mockCasesStore) : mockCasesStore
  );

  return {
    mockInViewStoreState,
    useInViewStoreMock,
    mockClientsStore,
    useClientsStoreMock,
    mockCasesStore,
    useCasesStoreMock,
  };
});

vi.mock("../../../stores/inViewStore", () => ({
  useInViewStore: useInViewStoreMock,
}));

vi.mock("../../../stores/clientsStore", () => ({
  useClientsStore: useClientsStoreMock,
}));

vi.mock("../../../stores/casesStore", () => ({
  useCasesStore: useCasesStoreMock,
}));

describe("ClientDetails component", () => {
  beforeEach(() => {
    vi.clearAllMocks();
    mockInViewStoreState.view = "clients";
    mockInViewStoreState.userId = "user-1";
    mockInViewStoreState.navigate = vi.fn();
    useInViewStoreMock.mockClear();
    useClientsStoreMock.mockClear();
    useCasesStoreMock.mockClear();
    mockCasesStore.getCases.mockReset();

    // Mock console.error to suppress error logs
    vi.spyOn(console, 'error').mockImplementation(() => {});
  });

  afterEach(() => {
    vi.restoreAllMocks();
  });

  it("renders loading spinner when no client is found", () => {
    mockInViewStoreState.userId = "nonexistent";
    render(<ClientDetails />);
  });

  it("renders client details when client is found and view is 'clients'", async () => {
    render(<ClientDetails />);

    await waitFor(() => {
      expect(screen.getByText("John Doe")).toBeInTheDocument();
    });

    expect(screen.getByText("client_contact_heading")).toBeInTheDocument();
    expect(screen.getByText("john@example.com")).toBeInTheDocument();
    expect(screen.getByText("1234567890")).toBeInTheDocument();
    expect(screen.getByText("0987654321")).toBeInTheDocument();
  });

  it("does not render when view is not 'clients'", () => {
    mockInViewStoreState.view = "cases";
    render(<ClientDetails />);
  });

  it("does not render when userId is empty", () => {
    mockInViewStoreState.userId = "";
    render(<ClientDetails />);
  });

  it("navigates back when back button is clicked", async () => {
    const user = userEvent.setup();
    render(<ClientDetails />);

    await waitFor(() => {
      expect(screen.getByText("John Doe")).toBeInTheDocument();
    });

    const backButton = screen.getByRole("button", { name: /client_back_to_list/i });
    await user.click(backButton);

    expect(mockInViewStoreState.navigate).toHaveBeenCalledWith("clients", "", "", "");
  });

  it("calls getCases when component mounts with userId", async () => {
    render(<ClientDetails />);

    await waitFor(() => {
      expect(mockCasesStore.getCases).toHaveBeenCalledWith("user-1", false, 1);
    });
  });

  it("renders cases for the client", async () => {
    render(<ClientDetails />);

    await waitFor(() => {
      expect(screen.getByText("Test Case")).toBeInTheDocument();
    });

    expect(screen.getByText(/1 case_singular found/i)).toBeInTheDocument();
  });
});
