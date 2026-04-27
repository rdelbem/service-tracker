import { describe, it, expect, beforeEach, vi } from "vitest";
import { render, screen } from "@testing-library/react";
import Initial from "../Initial";

const { mockInViewState, useInViewStoreMock } = vi.hoisted(() => {
  const mockInViewState = {
    view: "init",
    userId: "",
    caseId: "",
    name: "",
    navigate: vi.fn(),
  };

  const useInViewStoreMock = vi.fn((selector?: (state: typeof mockInViewState) => any) =>
    typeof selector === "function" ? selector(mockInViewState) : mockInViewState
  );

  return { mockInViewState, useInViewStoreMock };
});

vi.mock("../../../stores/inViewStore", () => ({
  useInViewStore: useInViewStoreMock,
}));

describe("Initial component", () => {
  beforeEach(() => {
    vi.clearAllMocks();
    mockInViewState.view = "init";
    (globalThis as any).data = {
      home_screen: undefined,
      brand_name: "Service Tracker",
    };
  });

  it("renders nothing when current view is not init", () => {
    mockInViewState.view = "cases";
    const { container } = render(<Initial />);

    expect(container.firstChild).toBeNull();
  });

  it("renders default welcome content", () => {
    render(<Initial />);

    expect(screen.getByText("Service Tracker")).toBeInTheDocument();
    expect(screen.getByText("home_screen")).toBeInTheDocument();
  });

  it("renders translated home screen message when provided", () => {
    (globalThis as any).data.home_screen = "Escolha um cliente para começar";

    render(<Initial />);

    expect(screen.getByText("Escolha um cliente para começar")).toBeInTheDocument();
  });
});
