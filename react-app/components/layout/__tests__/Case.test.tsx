import { describe, it, expect, beforeEach, afterEach, vi } from "vitest";
import { render, screen, waitFor } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import Case from "../Case";

const { mockCasesStore, useCasesStoreMock, mockInViewStoreState, useInViewStoreMock, mockProgressStore, useProgressStoreMock } = vi.hoisted(() => {
  const mockCasesStore = {
    deleteCase: vi.fn(),
    toggleCase: vi.fn(),
    editCase: vi.fn(),
  };
  const useCasesStoreMock = vi.fn((selector?: (state: typeof mockCasesStore) => any) =>
    typeof selector === "function" ? selector(mockCasesStore) : mockCasesStore
  );

  const mockInViewStoreState = {
    view: "cases",
    userId: "user-1",
    caseId: "case-1",
    name: "Test User",
    navigate: vi.fn(),
  };
  const useInViewStoreMock = vi.fn((selector?: (state: typeof mockInViewStoreState) => any) =>
    typeof selector === "function" ? selector(mockInViewStoreState) : mockInViewStoreState
  );

  const mockProgressStore = { getStatus: vi.fn() };
  const useProgressStoreMock = vi.fn((selector?: (state: typeof mockProgressStore) => any) =>
    typeof selector === "function" ? selector(mockProgressStore) : mockProgressStore
  );

  return { mockCasesStore, useCasesStoreMock, mockInViewStoreState, useInViewStoreMock, mockProgressStore, useProgressStoreMock };
});

vi.mock("../../../stores/casesStore", () => ({
  useCasesStore: useCasesStoreMock,
}));
vi.mock("../../../stores/inViewStore", () => ({
  useInViewStore: useInViewStoreMock,
}));
vi.mock("../../../stores/progressStore", () => ({
  useProgressStore: useProgressStoreMock,
}));

describe("Case component", () => {
  beforeEach(() => {
    mockCasesStore.deleteCase = vi.fn();
    mockCasesStore.toggleCase = vi.fn();
    mockCasesStore.editCase = vi.fn();
    mockInViewStoreState.navigate = vi.fn();
    mockProgressStore.getStatus = vi.fn();

    useCasesStoreMock.mockClear();
    useInViewStoreMock.mockClear();
    useProgressStoreMock.mockClear();

    (globalThis as any).data = {};
  });

  afterEach(() => {
    vi.clearAllMocks();
  });

  it("renders case title and status correctly", () => {
    render(
      <Case
        id="case-123"
        id_user="user-456"
        status="open"
        created_at="2023-01-01T00:00:00Z"
        title="Sample Case"
      />
    );

    expect(screen.getByText("Sample Case")).toBeInTheDocument();
    expect(screen.getByText("Active")).toBeInTheDocument();
  });

  it("calls onToggle and updates the status label when the toggle button is clicked", async () => {
    const user = userEvent.setup();
    const onToggle = vi.fn().mockResolvedValue(undefined);

    render(
      <Case
        id="case-123"
        id_user="user-456"
        status="open"
        created_at="2023-01-01T00:00:00Z"
        title="Sample Case"
        onToggle={onToggle}
      />
    );

    expect(screen.getByText("Active")).toBeInTheDocument();

    const toggleButton = screen.getByTitle("Close Case");
    await user.click(toggleButton);

    await waitFor(() => expect(onToggle).toHaveBeenCalledWith("case-123"));
    expect(mockCasesStore.toggleCase).not.toHaveBeenCalled();
    expect(await screen.findByText("Closed")).toBeInTheDocument();
  });

  it("falls back to store toggleCase when onToggle is not provided", async () => {
    const user = userEvent.setup();
    mockCasesStore.toggleCase.mockResolvedValue(undefined);

    render(
      <Case
        id="case-123"
        id_user="user-456"
        status="open"
        created_at="2023-01-01T00:00:00Z"
        title="Sample Case"
      />
    );

    const toggleButton = screen.getByTitle("Close Case");
    await user.click(toggleButton);

    await waitFor(() => expect(mockCasesStore.toggleCase).toHaveBeenCalledWith("case-123"));
    expect(await screen.findByText("Closed")).toBeInTheDocument();
  });

  it("opens edit mode when edit button is clicked", async () => {
    const user = userEvent.setup();

    render(
      <Case
        id="case-123"
        id_user="user-456"
        status="open"
        created_at="2023-01-01T00:00:00Z"
        title="Sample Case"
      />
    );

    // Find the edit icon (material symbol) and get its parent button
    const editIcon = screen.getByText("edit", { selector: ".material-symbols-outlined" });
    const editButton = editIcon.closest("button");
    if (!editButton) {
      throw new Error("Edit button not found");
    }
    await user.click(editButton);

    expect(screen.getByPlaceholderText("Enter new title...")).toBeInTheDocument();
    expect(screen.getByText("Save")).toBeInTheDocument();
  });
});
