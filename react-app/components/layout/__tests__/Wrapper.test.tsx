import { describe, it, expect, beforeEach, afterEach, vi } from "vitest";
import { render, screen } from "@testing-library/react";
import Wrapper from "../Wrapper";

vi.mock("../Sidebar", () => ({
  default: () => <aside data-testid="sidebar">Sidebar</aside>,
}));

describe("Wrapper component", () => {
  const originalMutationObserver = globalThis.MutationObserver;
  let observeMock: ReturnType<typeof vi.fn>;
  let disconnectMock: ReturnType<typeof vi.fn>;

  beforeEach(() => {
    observeMock = vi.fn();
    disconnectMock = vi.fn();

    class MutationObserverMock {
      constructor(_cb: MutationCallback) {}
      observe = observeMock;
      disconnect = disconnectMock;
    }

    // @ts-expect-error test override
    globalThis.MutationObserver = MutationObserverMock;
  });

  afterEach(() => {
    globalThis.MutationObserver = originalMutationObserver;
  });

  it("renders sidebar and children content", () => {
    render(
      <Wrapper>
        <div>Inner Content</div>
      </Wrapper>
    );

    expect(screen.getByTestId("sidebar")).toBeInTheDocument();
    expect(screen.getByText("Inner Content")).toBeInTheDocument();
  });

  it("registers and disconnects mutation observer on mount/unmount", () => {
    const { unmount } = render(
      <Wrapper>
        <div>Content</div>
      </Wrapper>
    );

    expect(observeMock).toHaveBeenCalledWith(document.body, {
      attributes: true,
      attributeFilter: ["class"],
    });

    unmount();

    expect(disconnectMock).toHaveBeenCalled();
  });
});
