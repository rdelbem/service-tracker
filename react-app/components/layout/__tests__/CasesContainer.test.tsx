import { describe, it, expect } from "vitest";
import { render, screen } from "@testing-library/react";
import CasesContainer from "../CasesContainer";

describe("CasesContainer component", () => {
  it("renders children content", () => {
    render(
      <CasesContainer>
        <div>Cases content</div>
      </CasesContainer>
    );

    expect(screen.getByText("Cases content")).toBeInTheDocument();
  });

  it("renders a section wrapper with expected layout classes", () => {
    const { container } = render(
      <CasesContainer>
        <div>Child</div>
      </CasesContainer>
    );

    const section = container.querySelector("section");
    expect(section).toBeInTheDocument();
    expect(section).toHaveClass("flex-1", "relative", "h-full", "overflow-hidden");
  });
});
