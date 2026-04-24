import { describe, it, expect, vi } from "vitest";
import { lazy } from "react";
import { render, screen } from "@testing-library/react";
import LazyView from "../LazyView";

vi.mock("../Spinner", () => ({
  default: () => <div data-testid="spinner">Loading...</div>,
}));

describe("LazyView component", () => {
  it("renders suspense fallback while lazy child is pending", () => {
    const PendingComponent = lazy(() => new Promise<any>(() => {}));

    render(
      <LazyView>
        <PendingComponent />
      </LazyView>
    );

    expect(screen.getByTestId("spinner")).toBeInTheDocument();
  });

  it("renders children when they do not suspend", () => {
    render(
      <LazyView>
        <div>Ready content</div>
      </LazyView>
    );

    expect(screen.getByText("Ready content")).toBeInTheDocument();
    expect(screen.queryByTestId("spinner")).not.toBeInTheDocument();
  });

  it("renders lazy child once it resolves", async () => {
    const ResolvedComponent = lazy(async () => ({
      default: () => <div>Lazy child rendered</div>,
    }));

    render(
      <LazyView>
        <ResolvedComponent />
      </LazyView>
    );

    expect(screen.getByTestId("spinner")).toBeInTheDocument();
    expect(await screen.findByText("Lazy child rendered")).toBeInTheDocument();
  });
});
