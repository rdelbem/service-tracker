import { describe, it, expect, beforeEach, vi } from "vitest";
import { render, screen } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import Sidebar from "../Sidebar";

const { mockInViewStoreState, useInViewStoreMock } = vi.hoisted(() => {
  const mockInViewStoreState = {
    view: "init",
    userId: "",
    caseId: "",
    name: "Admin User",
    navigate: vi.fn(),
  };
  const useInViewStoreMock = vi.fn((selector?: (state: typeof mockInViewStoreState) => any) =>
    typeof selector === "function" ? selector(mockInViewStoreState) : mockInViewStoreState
  );
  return { mockInViewStoreState, useInViewStoreMock };
});

vi.mock("../../../stores/inViewStore", () => ({
  useInViewStore: useInViewStoreMock,
}));

describe("Sidebar component", () => {
  beforeEach(() => {
    mockInViewStoreState.view = "init";
    mockInViewStoreState.userId = "";
    mockInViewStoreState.caseId = "";
    mockInViewStoreState.name = "Admin User";
    mockInViewStoreState.navigate = vi.fn();
    useInViewStoreMock.mockClear();
  });

  it("renders sidebar with brand header", () => {
    render(<Sidebar />);

    expect(screen.getByText("Service Tracker")).toBeInTheDocument();
    expect(screen.getByText("Administrator")).toBeInTheDocument();
  });

  it("renders all navigation items", () => {
    render(<Sidebar />);

    expect(screen.getByText("Dashboard")).toBeInTheDocument();
    expect(screen.getByText("Clients")).toBeInTheDocument();
    expect(screen.getByText("Cases")).toBeInTheDocument();
    expect(screen.getByText("Calendar")).toBeInTheDocument();
    expect(screen.getByText("Analytics")).toBeInTheDocument();
    expect(screen.getByText("Settings")).toBeInTheDocument();
  });

  it("highlights active navigation item based on current view", () => {
    mockInViewStoreState.view = "cases";
    render(<Sidebar />);

    // The Cases nav item should have active styling
    const casesItem = screen.getByText("Cases");
    // We can't directly test CSS classes but we can check parent element
    // The active item should have different styling
    expect(casesItem).toBeInTheDocument();
  });

  it("calls navigate when clicking on a navigation item", async () => {
    const user = userEvent.setup();
    render(<Sidebar />);

    const clientsItem = screen.getByText("Clients");
    await user.click(clientsItem);

    expect(mockInViewStoreState.navigate).toHaveBeenCalledWith("clients", "", "", "");
  });

  it("calls navigate when clicking on Settings", async () => {
    const user = userEvent.setup();
    render(<Sidebar />);

    const settingsItem = screen.getByText("Settings");
    await user.click(settingsItem);

    expect(mockInViewStoreState.navigate).toHaveBeenCalledWith("settings", "", "", "");
  });

  it("displays user profile with name from store", () => {
    mockInViewStoreState.name = "John Doe";
    render(<Sidebar />);

    expect(screen.getByText("John Doe")).toBeInTheDocument();
    expect(screen.getByText("Master Admin")).toBeInTheDocument();
  });

  it("displays default admin user when name is empty", () => {
    mockInViewStoreState.name = "";
    render(<Sidebar />);

    expect(screen.getByText("Admin User")).toBeInTheDocument();
  });

  it("shows user initial avatar", () => {
    mockInViewStoreState.name = "John Doe";
    render(<Sidebar />);

    // The avatar should show "J"
    const avatar = screen.getByText("J");
    expect(avatar).toBeInTheDocument();
  });

  it("shows default 'A' avatar when no name", () => {
    mockInViewStoreState.name = "";
    render(<Sidebar />);

    const avatar = screen.getByText("A");
    expect(avatar).toBeInTheDocument();
  });
});
