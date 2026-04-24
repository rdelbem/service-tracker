import { describe, it, expect } from "vitest";
import { render } from "@testing-library/react";
import Clients from "../Clients";

describe("Clients component", () => {
  it("renders an empty fragment", () => {
    const { container } = render(<Clients />);
    expect(container.firstChild).toBeNull();
  });
});
