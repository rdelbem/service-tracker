import { describe, it, expect, beforeEach, vi } from "vitest";
import { render, screen } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import HowToUse from "../HowToUse";

const { mockInViewState, useInViewStoreMock } = vi.hoisted(() => {
  const mockInViewState = {
    view: "howToUse",
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

describe("HowToUse component", () => {
  beforeEach(() => {
    vi.clearAllMocks();
    mockInViewState.view = "howToUse";

    Object.assign(globalThis.data, {
      instructions_page_title: undefined,
      accordion_first_title: undefined,
      accordion_second_title: undefined,
      first_accordion_first_li_item: undefined,
      first_accordion_second_li_item: undefined,
      first_accordion_third_li_item: undefined,
      first_accordion_forth_li_item: undefined,
      second_accordion_firt_li_item: undefined,
      second_accordion_second_li_item: undefined,
    });
  });

  it("renders nothing when current view is not howToUse", () => {
    mockInViewState.view = "clients";
    const { container } = render(<HowToUse />);

    expect(container.firstChild).toBeNull();
  });

  it("renders default title and first accordion content by default", () => {
    render(<HowToUse />);

    expect(screen.getByText("How to Use Service Tracker")).toBeInTheDocument();
    expect(screen.getByText("1. Getting Started")).toBeInTheDocument();
    expect(screen.getByText("2. Advanced Features")).toBeInTheDocument();
    expect(screen.getByText("Select a client from the left sidebar")).toBeInTheDocument();
  });

  it("toggles accordions when headers are clicked", async () => {
    const user = userEvent.setup();
    render(<HowToUse />);

    expect(screen.getByText("Select a client from the left sidebar")).toBeInTheDocument();

    await user.click(screen.getByRole("button", { name: /1\. getting started/i }));
    expect(screen.queryByText("Select a client from the left sidebar")).not.toBeInTheDocument();

    await user.click(screen.getByRole("button", { name: /2\. advanced features/i }));
    expect(screen.getByText("Toggle case status between open and closed")).toBeInTheDocument();
    expect(screen.queryByText("Select a client from the left sidebar")).not.toBeInTheDocument();
  });

  it("uses translated content from global data when provided", () => {
    Object.assign(globalThis.data, {
      instructions_page_title: "Manual",
      accordion_first_title: "Primeira",
      first_accordion_first_li_item: "Passo A",
      accordion_second_title: "Segunda",
    });

    render(<HowToUse />);

    expect(screen.getByText("Manual")).toBeInTheDocument();
    expect(screen.getByText("1. Primeira")).toBeInTheDocument();
    expect(screen.getByText("2. Segunda")).toBeInTheDocument();
    expect(screen.getByText("Passo A")).toBeInTheDocument();
  });
});
