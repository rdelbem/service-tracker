import { describe, it, expect, beforeEach, afterEach, vi } from "vitest";
import { render, screen, fireEvent, waitFor, act } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import ClientsView from "../ClientsView";

const {
  mockInViewState,
  useInViewStoreMock,
  mockClientsStore,
  useClientsStoreMock,
  mockToastSuccess,
  mockToastError,
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
    createUser: vi.fn(),
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
    mockToastSuccess: vi.fn(),
    mockToastError: vi.fn(),
  };
});

vi.mock("../../../stores/inViewStore", () => ({
  useInViewStore: useInViewStoreMock,
}));

vi.mock("../../../stores/clientsStore", () => ({
  useClientsStore: useClientsStoreMock,
}));

vi.mock("react-toastify", () => ({
  toast: {
    success: mockToastSuccess,
    error: mockToastError,
  },
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
    mockClientsStore.createUser = vi.fn();
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

    const searchInput = screen.getByPlaceholderText(/search clients/i);
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

    expect(screen.getByText(/no clients found/i)).toBeInTheDocument();
  });

  it("navigates when selecting a client", async () => {
    const user = userEvent.setup();
    render(<ClientsView />);

    await user.click(screen.getByText("John Doe"));

    expect(mockInViewState.navigate).toHaveBeenCalledWith("clients", "user-1", "", "John Doe");
  });

  it("opens and closes add client form", async () => {
    const user = userEvent.setup();
    render(<ClientsView />);

    await user.click(screen.getByRole("button", { name: /add client/i }));
    expect(screen.getByRole("button", { name: /create client/i })).toBeInTheDocument();

    await user.click(screen.getByRole("button", { name: /cancel/i }));
    expect(screen.queryByRole("button", { name: /create client/i })).not.toBeInTheDocument();
  });

  it("shows validation error when creating without required fields", async () => {
    const user = userEvent.setup();
    render(<ClientsView />);

    await user.click(screen.getByRole("button", { name: /add client/i }));

    const createButton = screen.getByRole("button", { name: /create client/i });
    const form = createButton.closest("form")!;
    fireEvent.submit(form);

    expect(mockToastError).toHaveBeenCalledWith("Name and email are required");
    expect(mockClientsStore.createUser).not.toHaveBeenCalled();
  });

  it("creates client successfully and resets form", async () => {
    const user = userEvent.setup();
    mockClientsStore.createUser = vi.fn().mockResolvedValue({
      success: true,
      message: "Client created",
    });

    render(<ClientsView />);

    await user.click(screen.getByRole("button", { name: /add client/i }));
    await user.type(screen.getByPlaceholderText(/client name/i), "New Client");
    await user.type(screen.getByPlaceholderText(/client@example.com/i), "new@client.com");

    await user.click(screen.getByRole("button", { name: /create client/i }));

    await waitFor(() => {
      expect(mockClientsStore.createUser).toHaveBeenCalledWith({
        name: "New Client",
        email: "new@client.com",
        phone: undefined,
        cellphone: undefined,
      });
    });

    expect(mockToastSuccess).toHaveBeenCalledWith("Client created");
    expect(screen.queryByRole("button", { name: /create client/i })).not.toBeInTheDocument();
  });

  it("shows error toast when create client fails", async () => {
    const user = userEvent.setup();
    mockClientsStore.createUser = vi.fn().mockResolvedValue({
      success: false,
      message: "Duplicate email",
    });

    render(<ClientsView />);

    await user.click(screen.getByRole("button", { name: /add client/i }));
    await user.type(screen.getByPlaceholderText(/client name/i), "New Client");
    await user.type(screen.getByPlaceholderText(/client@example.com/i), "new@client.com");
    await user.click(screen.getByRole("button", { name: /create client/i }));

    await waitFor(() => {
      expect(mockToastError).toHaveBeenCalledWith("Duplicate email");
    });
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
