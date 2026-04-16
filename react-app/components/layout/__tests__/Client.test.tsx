import { describe, it, expect, beforeEach, vi } from "vitest";
import { render, screen, fireEvent } from "@testing-library/react";
import Client from "../Client";

const { mockInViewState, useInViewStoreMock, mockCasesStore, useCasesStoreMock } = vi.hoisted(() => {
  const mockInViewState = {
    view: "clients",
    userId: "",
    caseId: "",
    name: "",
    navigate: vi.fn(),
    updateIdView: vi.fn(),
  };

  const useInViewStoreMock = vi.fn((selector?: (state: typeof mockInViewState) => any) =>
    typeof selector === "function" ? selector(mockInViewState) : mockInViewState
  );

  const mockCasesStore = {
    getCases: vi.fn(),
  };

  const useCasesStoreMock = vi.fn(() => mockCasesStore);

  return { mockInViewState, useInViewStoreMock, mockCasesStore, useCasesStoreMock };
});

vi.mock("../../../stores/inViewStore", () => ({
  useInViewStore: useInViewStoreMock,
}));

vi.mock("../../../stores/casesStore", () => ({
  useCasesStore: useCasesStoreMock,
}));

describe("Client component", () => {
  beforeEach(() => {
    vi.clearAllMocks();
    mockInViewState.userId = "";
    mockInViewState.navigate = vi.fn();
    mockCasesStore.getCases = vi.fn();
  });

  it("renders client details with default values", () => {
    render(<Client id="123" name="john" />);

    expect(screen.getByText("john")).toBeInTheDocument();
    expect(screen.getByText("J")).toBeInTheDocument();
    expect(screen.getByText(String(new Date().getFullYear()), { exact: false })).toBeInTheDocument();
    expect(screen.getByText("0")).toBeInTheDocument();
    expect(screen.getByText("Cases")).toBeInTheDocument();
  });

  it("shows singular case label when caseCount is one", () => {
    render(<Client id="123" name="John Doe" caseCount={1} />);

    expect(screen.getByText("1")).toBeInTheDocument();
    expect(screen.getByText("Case")).toBeInTheDocument();
  });

  it("applies active styles when selected client id matches current user", () => {
    mockInViewState.userId = "7";
    render(<Client id="7" name="Jane Doe" caseCount={2} activeSince="Active Since 2024" />);

    const root = screen.getByText("Jane Doe").closest("div[class*='cursor-pointer']");
    expect(root).toHaveClass("border-primary");
    expect(screen.getByText("2")).toHaveClass("text-primary");
  });

  it("navigates to cases and fetches cases on click", () => {
    render(<Client id="55" name="Acme Corp" caseCount={4} />);

    const root = screen.getByText("Acme Corp").closest("div[class*='cursor-pointer']");
    expect(root).toBeTruthy();

    fireEvent.click(root!);

    expect(mockInViewState.navigate).toHaveBeenCalledWith("cases", "55", "", "Acme Corp");
    expect(mockCasesStore.getCases).toHaveBeenCalledWith("55", false);
  });
});
