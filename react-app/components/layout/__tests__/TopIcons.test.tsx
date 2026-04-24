import { describe, it, expect, beforeEach, vi } from "vitest";
import { render, screen } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import TopIcons from "../TopIcons";

const { mockInViewStore, useInViewStoreMock } = vi.hoisted(() => {
  const mockInViewStore = {
    view: "init",
    userId: "",
    caseId: "",
    name: "",
    navigate: vi.fn(),
  };

  const useInViewStoreMock = vi.fn((selector?: (state: typeof mockInViewStore) => any) =>
    typeof selector === "function" ? selector(mockInViewStore) : mockInViewStore
  );

  return { mockInViewStore, useInViewStoreMock };
});

vi.mock("../../../stores/inViewStore", () => ({
  useInViewStore: useInViewStoreMock,
}));

vi.mock("react-icons/ai", () => ({
  AiOutlineHome: ({ onClick }: { onClick?: () => void }) => (
    <button type="button" onClick={onClick} aria-label="home icon">
      Home
    </button>
  ),
  AiOutlineTool: ({ onClick }: { onClick?: () => void }) => (
    <button type="button" onClick={onClick} aria-label="tool icon">
      Tool
    </button>
  ),
}));

describe("TopIcons component", () => {
  beforeEach(() => {
    vi.clearAllMocks();
    mockInViewStore.navigate = vi.fn();
  });

  it("navigates to init when home icon is clicked", async () => {
    const user = userEvent.setup();
    render(<TopIcons />);

    await user.click(screen.getByRole("button", { name: /home icon/i }));

    expect(mockInViewStore.navigate).toHaveBeenCalledWith("init", "", "", "");
  });

  it("navigates to howToUse when tool icon is clicked", async () => {
    const user = userEvent.setup();
    render(<TopIcons />);

    await user.click(screen.getByRole("button", { name: /tool icon/i }));

    expect(mockInViewStore.navigate).toHaveBeenCalledWith("howToUse", "", "", "");
  });
});
