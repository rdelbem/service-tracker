import { describe, it, expect, beforeEach, vi } from "vitest";
import { render, screen, waitFor } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import Analytics from "../Analytics";

const {
  mockInViewState,
  useInViewStoreMock,
  mockAnalyticsStore,
  useAnalyticsStoreMock,
} = vi.hoisted(() => {
  const mockInViewState = {
    view: "analytics",
    userId: "",
    caseId: "",
    name: "",
    navigate: vi.fn(),
  };

  const useInViewStoreMock = vi.fn((selector?: (state: typeof mockInViewState) => any) =>
    typeof selector === "function" ? selector(mockInViewState) : mockInViewState
  );

  const mockAnalyticsStore = {
    analytics: {
      summary: {
        total_customers: 5,
        total_cases: 7,
        open_cases: 3,
        closed_cases: 4,
        total_progress_updates: 12,
        notifications_attempted: 20,
        notifications_sent: 18,
        notifications_failed: 2,
        active_admins_last_30_days: 2,
      },
      customer_stats: [
        {
          user_id: 1,
          name: "John Doe",
          email: "john@example.com",
          total_cases: 3,
          open_cases: 1,
          closed_cases: 2,
          progress_updates: 6,
          notifications_sent: 4,
          last_activity_at: "2024-01-10T10:00:00",
        },
      ],
      admin_stats: [
        {
          user_id: 11,
          display_name: "Admin A",
          email: "admin@example.com",
          cases_created: 2,
          cases_updated: 4,
          cases_deleted: 1,
          progress_created: 5,
          progress_updated: 1,
          progress_deleted: 0,
          notifications_triggered: 6,
          last_activity_at: "2024-01-11T10:00:00",
        },
      ],
      trends: {
        cases_created_by_period: [
          { period: "2024-01-10", count: 2 },
          { period: "2024-01-09", count: 3 },
          { period: "2024-01-08", count: 1 },
          { period: "2024-01-07", count: 4 },
          { period: "2024-01-06", count: 2 },
          { period: "2024-01-05", count: 5 }, // This should not be displayed (6th item)
        ],
        progress_created_by_period: [
          { period: "2024-01-10", count: 3 },
          { period: "2024-01-09", count: 2 },
          { period: "2024-01-08", count: 4 },
          { period: "2024-01-07", count: 1 },
          { period: "2024-01-06", count: 3 },
          { period: "2024-01-05", count: 2 }, // This should not be displayed (6th item)
        ],
        notifications_by_period: [
          { period: "2024-01-10", count: 1 },
          { period: "2024-01-09", count: 2 },
          { period: "2024-01-08", count: 3 },
          { period: "2024-01-07", count: 4 },
          { period: "2024-01-06", count: 5 },
          { period: "2024-01-05", count: 1 }, // This should not be displayed (6th item)
        ],
        admin_actions_by_period: [
          { period: "2024-01-10", count: 4 },
          { period: "2024-01-09", count: 3 },
          { period: "2024-01-08", count: 2 },
          { period: "2024-01-07", count: 1 },
          { period: "2024-01-06", count: 4 },
          { period: "2024-01-05", count: 3 }, // This should not be displayed (6th item)
        ],
      },

    },
    loading: false,
    period: "30" as "7" | "30" | "90",
    fetchAnalytics: vi.fn(),
    setPeriod: vi.fn(),
  };

  const useAnalyticsStoreMock = vi.fn((selector?: (state: typeof mockAnalyticsStore) => any) =>
    typeof selector === "function" ? selector(mockAnalyticsStore) : mockAnalyticsStore
  );

  return {
    mockInViewState,
    useInViewStoreMock,
    mockAnalyticsStore,
    useAnalyticsStoreMock,
  };
});

vi.mock("../../../stores/inViewStore", () => ({
  useInViewStore: useInViewStoreMock,
}));

vi.mock("../../../stores/analyticsStore", () => ({
  useAnalyticsStore: useAnalyticsStoreMock,
}));

vi.mock("../Spinner", () => ({
  default: () => <div data-testid="spinner">Loading...</div>,
}));

describe("Analytics component", () => {
  beforeEach(() => {
    vi.clearAllMocks();

    mockInViewState.view = "analytics";

    mockAnalyticsStore.loading = false;
    mockAnalyticsStore.period = "30";
    mockAnalyticsStore.fetchAnalytics = vi.fn();
    mockAnalyticsStore.setPeriod = vi.fn();
    mockAnalyticsStore.analytics = {
      summary: {
        total_customers: 5,
        total_cases: 7,
        open_cases: 3,
        closed_cases: 4,
        total_progress_updates: 12,
        notifications_attempted: 20,
        notifications_sent: 18,
        notifications_failed: 2,
        active_admins_last_30_days: 2,
      },
      customer_stats: [
        {
          user_id: 1,
          name: "John Doe",
          email: "john@example.com",
          total_cases: 3,
          open_cases: 1,
          closed_cases: 2,
          progress_updates: 6,
          notifications_sent: 4,
          last_activity_at: "2024-01-10T10:00:00",
        },
      ],
      admin_stats: [
        {
          user_id: 11,
          display_name: "Admin A",
          email: "admin@example.com",
          cases_created: 2,
          cases_updated: 4,
          cases_deleted: 1,
          progress_created: 5,
          progress_updated: 1,
          progress_deleted: 0,
          notifications_triggered: 6,
          last_activity_at: "2024-01-11T10:00:00",
        },
      ],
      trends: {
        cases_created_by_period: [
          { period: "2024-01-10", count: 2 },
          { period: "2024-01-09", count: 3 },
          { period: "2024-01-08", count: 1 },
          { period: "2024-01-07", count: 4 },
          { period: "2024-01-06", count: 2 },
          { period: "2024-01-05", count: 5 }, // This should not be displayed (6th item)
        ],
        progress_created_by_period: [
          { period: "2024-01-10", count: 3 },
          { period: "2024-01-09", count: 2 },
          { period: "2024-01-08", count: 4 },
          { period: "2024-01-07", count: 1 },
          { period: "2024-01-06", count: 3 },
          { period: "2024-01-05", count: 2 }, // This should not be displayed (6th item)
        ],
        notifications_by_period: [
          { period: "2024-01-10", count: 1 },
          { period: "2024-01-09", count: 2 },
          { period: "2024-01-08", count: 3 },
          { period: "2024-01-07", count: 4 },
          { period: "2024-01-06", count: 5 },
          { period: "2024-01-05", count: 1 }, // This should not be displayed (6th item)
        ],
        admin_actions_by_period: [
          { period: "2024-01-10", count: 4 },
          { period: "2024-01-09", count: 3 },
          { period: "2024-01-08", count: 2 },
          { period: "2024-01-07", count: 1 },
          { period: "2024-01-06", count: 4 },
          { period: "2024-01-05", count: 3 }, // This should not be displayed (6th item)
        ],
      },
    } as any;
  });

  it("returns empty fragment when not in analytics view", () => {
    mockInViewState.view = "cases";
    const { container } = render(<Analytics />);

    expect(container.firstChild).toBeNull();
    expect(mockAnalyticsStore.fetchAnalytics).not.toHaveBeenCalled();
  });

  it("shows loading spinner", () => {
    mockAnalyticsStore.loading = true;
    render(<Analytics />);

    expect(screen.getByTestId("spinner")).toBeInTheDocument();
  });

  it("shows no-data message when analytics is null", () => {
    mockAnalyticsStore.analytics = null;
    render(<Analytics />);

    expect(screen.getByText(/no analytics data available/i)).toBeInTheDocument();
  });

  it("calls fetchAnalytics on mount when analytics view is active", async () => {
    render(<Analytics />);

    await waitFor(() => {
      expect(mockAnalyticsStore.fetchAnalytics).toHaveBeenCalledWith("30");
    });
  });

  it("changes period and triggers refetch", async () => {
    const user = userEvent.setup();
    render(<Analytics />);

    await user.click(screen.getByRole("button", { name: /7 days/i }));

    expect(mockAnalyticsStore.setPeriod).toHaveBeenCalledWith("7");
    expect(mockAnalyticsStore.fetchAnalytics).toHaveBeenCalledWith("7");
  });

  it("switches between tabs and renders tab content", async () => {
    const user = userEvent.setup();
    render(<Analytics />);

    expect(screen.getByText(/total customers/i)).toBeInTheDocument();

    await user.click(screen.getByRole("button", { name: "customers" }));
    expect(screen.getByText("John Doe")).toBeInTheDocument();

    await user.click(screen.getByRole("button", { name: "admins" }));
    expect(screen.getByText("Admin A")).toBeInTheDocument();

    await user.click(screen.getByRole("button", { name: "trends" }));
    expect(screen.getByText(/cases created/i)).toBeInTheDocument();
    expect(screen.getByText(/progress updates/i)).toBeInTheDocument();
  });

  it("shows empty customers state", async () => {
    const user = userEvent.setup();
    mockAnalyticsStore.analytics = {
      ...(mockAnalyticsStore.analytics as any),
      customer_stats: [],
    } as any;

    render(<Analytics />);
    await user.click(screen.getByRole("button", { name: "customers" }));

    expect(screen.getByText(/no customer data available/i)).toBeInTheDocument();
  });

  it("shows empty admins state", async () => {
    const user = userEvent.setup();
    mockAnalyticsStore.analytics = {
      ...(mockAnalyticsStore.analytics as any),
      admin_stats: [],
    } as any;

    render(<Analytics />);
    await user.click(screen.getByRole("button", { name: "admins" }));

    expect(screen.getByText(/no admin activity recorded/i)).toBeInTheDocument();
  });

  it("shows only 5 items in trend blocks", async () => {
    const user = userEvent.setup();
    render(<Analytics />);
    
    await user.click(screen.getByRole("button", { name: "trends" }));
    
    // Check that trend blocks are displayed
    expect(screen.getByText(/cases created/i)).toBeInTheDocument();
    expect(screen.getByText(/progress updates/i)).toBeInTheDocument();
    expect(screen.getByText(/email notifications/i)).toBeInTheDocument();
    expect(screen.getByText(/admin actions/i)).toBeInTheDocument();
    
    // Count all date elements - each trend block should show max 5 items
    // With 4 trend blocks and 5 items each, max would be 20
    // But the mock has 6 items per block, so if limit is not applied, we'd get 24
    const dateElements = screen.getAllByText(/Jan \d+/);
    
    // Should have exactly 20 date elements (4 blocks × 5 items)
    expect(dateElements).toHaveLength(20);
    
    // Verify we don't have more than 20 (which would happen if all 6 items were shown)
    expect(dateElements.length).toBeLessThanOrEqual(20);
  });
});
