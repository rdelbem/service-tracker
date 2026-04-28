import { describe, it, expect, beforeEach, vi } from "vitest";

describe("react-app index bootstrap", () => {
  beforeEach(() => {
    vi.resetModules();
    vi.clearAllMocks();
    document.body.innerHTML = "";
  });

  it("creates a root and renders app when #stolmc-root exists", async () => {
    document.body.innerHTML = '<div id="stolmc-root"></div>';

    const renderMock = vi.fn();
    const createRootMock = vi.fn(() => ({
      render: renderMock,
    }));

    vi.doMock("react-dom/client", () => ({
      createRoot: createRootMock,
    }));

    vi.doMock("../App", () => ({
      default: () => <div>Mocked App</div>,
    }));

    await import("../index");

    expect(createRootMock).toHaveBeenCalledWith(document.getElementById("stolmc-root"));
    expect(renderMock).toHaveBeenCalledTimes(1);
  });

  it("does not create a root when #stolmc-root is missing", async () => {
    const createRootMock = vi.fn();

    vi.doMock("react-dom/client", () => ({
      createRoot: createRootMock,
    }));

    vi.doMock("../App", () => ({
      default: () => <div>Mocked App</div>,
    }));

    await import("../index");

    expect(createRootMock).not.toHaveBeenCalled();
  });
});
