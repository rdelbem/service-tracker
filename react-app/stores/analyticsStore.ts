import { create } from "zustand";
import { get as fetchGet } from "../utils/fetch";

declare const data: Record<string, any>;

export interface AnalyticsSummary {
  total_customers: number;
  total_cases: number;
  open_cases: number;
  closed_cases: number;
  total_progress_updates: number;
  notifications_attempted: number;
  notifications_sent: number;
  notifications_failed: number;
  active_admins_last_30_days: number;
}

export interface CustomerStat {
  user_id: number;
  name: string;
  email: string;
  total_cases: number;
  open_cases: number;
  closed_cases: number;
  progress_updates: number;
  notifications_sent: number;
  last_activity_at: string | null;
}

export interface AdminStat {
  user_id: number;
  display_name: string;
  email: string;
  cases_created: number;
  cases_updated: number;
  cases_deleted: number;
  progress_created: number;
  progress_updated: number;
  progress_deleted: number;
  notifications_triggered: number;
  last_activity_at: string | null;
}

export interface TrendData {
  period: string;
  count: number;
}

export interface AnalyticsTrends {
  cases_created_by_period: TrendData[];
  progress_created_by_period: TrendData[];
  notifications_by_period: TrendData[];
  admin_actions_by_period: TrendData[];
}

export interface AnalyticsData {
  summary: AnalyticsSummary;
  customer_stats: CustomerStat[];
  admin_stats: AdminStat[];
  trends: AnalyticsTrends;
}

export interface AnalyticsStore {
  analytics: AnalyticsData | null;
  loading: boolean;
  period: "7" | "30" | "90";
  fetchAnalytics: (period?: "7" | "30" | "90") => Promise<void>;
  setPeriod: (period: "7" | "30" | "90") => void;
}

export const useAnalyticsStore = create<AnalyticsStore>((set) => ({
  analytics: null,
  loading: false,
  period: "30",

  fetchAnalytics: async (period = "30") => {
    set({ loading: true });

    const apiUrl = `${data.root_url}/wp-json/${data.api_url}/analytics`;

    const now = new Date();
    const startDate = new Date(now);
    startDate.setDate(now.getDate() - parseInt(period, 10));

    const params = new URLSearchParams({
      start: startDate.toISOString().split("T")[0],
      end: now.toISOString().split("T")[0],
    });

    try {
      const response = await fetchGet(`${apiUrl}?${params.toString()}`, {
        headers: { "X-WP-Nonce": data.nonce },
      });

      set({ analytics: response.data.data, loading: false });
    } catch (error) {
      console.error("Error fetching analytics:", error);
      set({ loading: false });
    }
  },

  setPeriod: (period) => {
    set({ period });
  },
}));
