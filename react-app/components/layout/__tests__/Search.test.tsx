import { describe, it, expect, beforeEach, vi } from "vitest";
import { render, screen, fireEvent } from "@testing-library/react";
import Search from "../Search";

const { mockClientsStore, useClientsStoreMock } = vi.hoisted(() => {
  const mockClientsStore = {
    searchUsers: vi.fn(),
  };

  const useClientsStoreMock = vi.fn(() => mockClientsStore);

  return { mockClientsStore, useClientsStoreMock };
});

vi.mock("../../../stores/clientsStore", () => ({
  useClientsStore: useClientsStoreMock,
}));

describe("Search component", () => {
  beforeEach(() => {
    vi.clearAllMocks();
    Object.assign(globalThis.stolmcData, {
      search_bar: undefined,
    });
  });

  it("renders default placeholder when localized text is not provided", () => {
    render(<Search />);

    expect(screen.getByPlaceholderText("search_bar")).toBeInTheDocument();
  });

  it("renders localized placeholder when provided", () => {
    Object.assign(globalThis.stolmcData, {
      search_bar: "Buscar clientes...",
    });

    render(<Search />);

    expect(screen.getByPlaceholderText("Buscar clientes...")).toBeInTheDocument();
  });

  it("calls onSearch when callback prop is provided", () => {
    const onSearch = vi.fn();
    render(<Search onSearch={onSearch} />);

    fireEvent.change(screen.getByRole("textbox"), { target: { value: "john" } });

    expect(onSearch).toHaveBeenCalledWith("john");
    expect(mockClientsStore.searchUsers).not.toHaveBeenCalled();
  });

  it("calls store searchUsers when callback prop is not provided", () => {
    render(<Search />);

    fireEvent.change(screen.getByRole("textbox"), { target: { value: "acme" } });

    expect(mockClientsStore.searchUsers).toHaveBeenCalledWith("acme");
  });
});
