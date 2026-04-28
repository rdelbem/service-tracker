import { useState, useEffect, Fragment } from "react";
import { useInViewStore } from "../../stores/inViewStore";
import { useAnalyticsStore } from "../../stores/analyticsStore";
import Spinner from "./Spinner";
import { stolmc_text, Text } from "../../i18n";

export default function Analytics() {
  const inViewState = useInViewStore((state) => state);
  const { analytics, loading, period, fetchAnalytics, setPeriod } = useAnalyticsStore();
  const [activeTab, setActiveTab] = useState<"summary" | "customers" | "admins" | "trends">("summary");

  useEffect(() => {
    if (inViewState.view !== "analytics") {
      return;
    }

    fetchAnalytics(period);
  }, [inViewState.view, period, fetchAnalytics]);

  // Required for navigation purposes - MUST be after all hooks
  if (inViewState.view !== "analytics") {
    return <Fragment></Fragment>;
  }

  const handlePeriodChange = (newPeriod: "7" | "30" | "90") => {
    setPeriod(newPeriod);
    fetchAnalytics(newPeriod);
  };

  if (loading) {
    return <Spinner />;
  }

  if (!analytics?.summary) {
    return (
      <section className="flex-1 h-full overflow-y-auto">
        <div className="p-8 text-center">
          <p className="text-on-surface-variant">{stolmc_text(Text.AnalyticsNoData)}</p>
        </div>
      </section>
    );
  }

  const formatNumber = (num: number) => num.toLocaleString();

  const formatDate = (dateStr: string | null) => {
    if (!dateStr) return stolmc_text(Text.AnalyticsNever);
    return new Date(dateStr).toLocaleDateString("en-US", {
      year: "numeric",
      month: "short",
      day: "numeric",
    });
  };

  return (
    <section className="flex-1 h-full overflow-y-auto">
      <div className="p-8">
        {/* Header */}
        <div className="mb-8">
          <h1 className="text-3xl font-black text-on-surface tracking-tight">{stolmc_text(Text.AnalyticsHeading)}</h1>
          <p className="text-on-surface-variant text-sm mt-2">
            {stolmc_text(Text.AnalyticsDescription)}
          </p>
        </div>

        {/* Period Filter */}
        <div className="bg-surface-container-low p-4 rounded-xl mb-6 flex items-center gap-4">
          <span className="text-sm font-bold text-on-surface">{stolmc_text(Text.AnalyticsTimePeriod)}</span>
          {(["7", "30", "90"] as const).map((p) => (
            <button
              key={p}
              onClick={() => handlePeriodChange(p)}
              className={`px-4 py-2 rounded-lg text-sm font-bold transition-all ${
                period === p
                  ? "bg-primary text-white"
                  : "bg-surface-container-high text-on-surface-variant hover:bg-surface-container-highest"
              }`}
            >
              {p} {stolmc_text(Text.AnalyticsDays)}
            </button>
          ))}
        </div>

        {/* Tabs */}
        <div className="flex gap-2 mb-6 border-b border-outline-variant/20">
          {(["summary", "customers", "admins", "trends"] as const).map((tab) => (
            <button
              key={tab}
              onClick={() => setActiveTab(tab)}
              className={`px-6 py-3 text-sm font-bold uppercase tracking-wider transition-all ${
                activeTab === tab
                  ? "text-primary border-b-2 border-primary"
                  : "text-on-surface-variant hover:text-on-surface"
              }`}
            >
              {tab === "summary" ? stolmc_text(Text.AnalyticsTabSummary) : tab === "customers" ? stolmc_text(Text.AnalyticsTabCustomers) : tab === "admins" ? stolmc_text(Text.AnalyticsTabAdmins) : stolmc_text(Text.AnalyticsTabTrends)}
            </button>
          ))}
        </div>

        {/* Summary Tab */}
        {activeTab === "summary" && (
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <SummaryCard
              title={stolmc_text(Text.AnalyticsTotalCustomers)}
              value={formatNumber(analytics.summary.total_customers)}
              icon="people"
              color="primary"
            />
            <SummaryCard
              title={stolmc_text(Text.AnalyticsTotalCases)}
              value={formatNumber(analytics.summary.total_cases)}
              icon="folder"
              color="secondary"
            />
            <SummaryCard
              title={stolmc_text(Text.AnalyticsOpenCases)}
              value={formatNumber(analytics.summary.open_cases)}
              icon="folder_open"
              color="secondary"
            />
            <SummaryCard
              title={stolmc_text(Text.AnalyticsClosedCases)}
              value={formatNumber(analytics.summary.closed_cases)}
              icon="folder_check"
              color="tertiary"
            />
            <SummaryCard
              title={stolmc_text(Text.AnalyticsProgressUpdates)}
              value={formatNumber(analytics.summary.total_progress_updates)}
              icon="timeline"
              color="primary"
            />
            <SummaryCard
              title={stolmc_text(Text.AnalyticsEmailsSent)}
              value={formatNumber(analytics.summary.notifications_sent)}
              subtitle={`${formatNumber(analytics.summary.notifications_attempted)} ${stolmc_text(Text.AnalyticsEmailsAttempted)}`}
              icon="send"
              color="tertiary"
            />
            <SummaryCard
              title={stolmc_text(Text.AnalyticsFailedEmails)}
              value={formatNumber(analytics.summary.notifications_failed)}
              icon="error"
              color="error"
            />
            <SummaryCard
              title={stolmc_text(Text.AnalyticsActiveAdmins)}
              value={formatNumber(analytics.summary.active_admins_last_30_days)}
              icon="admin_panel_settings"
              color="primary"
            />
          </div>
        )}

        {/* Customers Tab */}
        {activeTab === "customers" && (
          <div className="bg-surface-container-low rounded-xl overflow-hidden">
            <table className="w-full">
              <thead className="bg-surface-container-high">
                <tr>
                  <th className="p-4 text-left text-xs font-bold text-on-surface-variant uppercase">
                    {stolmc_text(Text.AnalyticsColCustomer)}
                  </th>
                  <th className="p-4 text-left text-xs font-bold text-on-surface-variant uppercase">
                    {stolmc_text(Text.AnalyticsColTotalCases)}
                  </th>
                  <th className="p-4 text-left text-xs font-bold text-on-surface-variant uppercase">
                    {stolmc_text(Text.AnalyticsColOpen)}
                  </th>
                  <th className="p-4 text-left text-xs font-bold text-on-surface-variant uppercase">
                    {stolmc_text(Text.AnalyticsColClosed)}
                  </th>
                  <th className="p-4 text-left text-xs font-bold text-on-surface-variant uppercase">
                    {stolmc_text(Text.AnalyticsColProgress)}
                  </th>
                  <th className="p-4 text-left text-xs font-bold text-on-surface-variant uppercase">
                    {stolmc_text(Text.AnalyticsColEmails)}
                  </th>
                  <th className="p-4 text-left text-xs font-bold text-on-surface-variant uppercase">
                    {stolmc_text(Text.AnalyticsColLastActivity)}
                  </th>
                </tr>
              </thead>
              <tbody>
                {analytics.customer_stats.length === 0 ? (
                  <tr>
                    <td colSpan={7} className="p-8 text-center text-on-surface-variant">
                      {stolmc_text(Text.AnalyticsNoCustomerData)}
                    </td>
                  </tr>
                ) : (
                  analytics.customer_stats.map((customer) => (
                    <tr
                      key={customer.user_id}
                      className="border-t border-outline-variant/10 hover:bg-surface-container-high"
                    >
                      <td className="p-4">
                        <div className="text-sm font-bold text-on-surface">{customer.name}</div>
                        <div className="text-xs text-outline">{customer.email}</div>
                      </td>
                      <td className="p-4 text-sm text-on-surface-variant">
                        {formatNumber(customer.total_cases)}
                      </td>
                      <td className="p-4 text-sm text-secondary">{formatNumber(customer.open_cases)}</td>
                      <td className="p-4 text-sm text-on-surface-variant">
                        {formatNumber(customer.closed_cases)}
                      </td>
                      <td className="p-4 text-sm text-on-surface-variant">
                        {formatNumber(customer.progress_updates)}
                      </td>
                      <td className="p-4 text-sm text-tertiary">
                        {formatNumber(customer.notifications_sent)}
                      </td>
                      <td className="p-4 text-xs text-outline">{formatDate(customer.last_activity_at)}</td>
                    </tr>
                  ))
                )}
              </tbody>
            </table>
          </div>
        )}

        {/* Admins Tab */}
        {activeTab === "admins" && (
          <div className="bg-surface-container-low rounded-xl overflow-hidden">
            <table className="w-full">
              <thead className="bg-surface-container-high">
                <tr>
                  <th className="p-4 text-left text-xs font-bold text-on-surface-variant uppercase">
                    {stolmc_text(Text.AnalyticsColAdmin)}
                  </th>
                  <th className="p-4 text-left text-xs font-bold text-on-surface-variant uppercase">
                    {stolmc_text(Text.AnalyticsColCasesCreated)}
                  </th>
                  <th className="p-4 text-left text-xs font-bold text-on-surface-variant uppercase">
                    {stolmc_text(Text.AnalyticsColCasesUpdated)}
                  </th>
                  <th className="p-4 text-left text-xs font-bold text-on-surface-variant uppercase">
                    {stolmc_text(Text.AnalyticsColCasesDeleted)}
                  </th>
                  <th className="p-4 text-left text-xs font-bold text-on-surface-variant uppercase">
                    {stolmc_text(Text.AnalyticsColProgressAdded)}
                  </th>
                  <th className="p-4 text-left text-xs font-bold text-on-surface-variant uppercase">
                    {stolmc_text(Text.AnalyticsColEmailsTriggered)}
                  </th>
                  <th className="p-4 text-left text-xs font-bold text-on-surface-variant uppercase">
                    {stolmc_text(Text.AnalyticsColLastActivity)}
                  </th>
                </tr>
              </thead>
              <tbody>
                {analytics.admin_stats.length === 0 ? (
                  <tr>
                    <td colSpan={7} className="p-8 text-center text-on-surface-variant">
                      {stolmc_text(Text.AnalyticsNoAdminData)}
                    </td>
                  </tr>
                ) : (
                  analytics.admin_stats.map((admin) => (
                    <tr
                      key={admin.user_id}
                      className="border-t border-outline-variant/10 hover:bg-surface-container-high"
                    >
                      <td className="p-4">
                        <div className="text-sm font-bold text-on-surface">{admin.display_name}</div>
                        <div className="text-xs text-outline">{admin.email}</div>
                      </td>
                      <td className="p-4 text-sm text-on-surface-variant">
                        {formatNumber(admin.cases_created)}
                      </td>
                      <td className="p-4 text-sm text-on-surface-variant">
                        {formatNumber(admin.cases_updated)}
                      </td>
                      <td className="p-4 text-sm text-on-surface-variant">
                        {formatNumber(admin.cases_deleted)}
                      </td>
                      <td className="p-4 text-sm text-on-surface-variant">
                        {formatNumber(admin.progress_created)}
                      </td>
                      <td className="p-4 text-sm text-tertiary">
                        {formatNumber(admin.notifications_triggered)}
                      </td>
                      <td className="p-4 text-xs text-outline">{formatDate(admin.last_activity_at)}</td>
                    </tr>
                  ))
                )}
              </tbody>
            </table>
          </div>
        )}

        {/* Trends Tab */}
        {activeTab === "trends" && (
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <TrendBlock
              title={stolmc_text(Text.TrendCasesCreated)}
              data={analytics.trends.cases_created_by_period}
              icon="add_circle"
            />
            <TrendBlock
              title={stolmc_text(Text.TrendProgressUpdates)}
              data={analytics.trends.progress_created_by_period}
              icon="timeline"
            />
            <TrendBlock
              title={stolmc_text(Text.TrendEmailNotifications)}
              data={analytics.trends.notifications_by_period}
              icon="send"
            />
            <TrendBlock
              title={stolmc_text(Text.TrendAdminActions)}
              data={analytics.trends.admin_actions_by_period}
              icon="admin_panel_settings"
            />
          </div>
        )}
      </div>
    </section>
  );
}

// Summary Card Component
function SummaryCard({
  title,
  value,
  subtitle,
  icon,
  color,
}: {
  title: string;
  value: string;
  subtitle?: string;
  icon: string;
  color: "primary" | "secondary" | "tertiary" | "error";
}) {
  const colorClasses = {
    primary: "bg-primary/10 text-primary",
    secondary: "bg-secondary/10 text-secondary",
    tertiary: "bg-tertiary/10 text-tertiary",
    error: "bg-error/10 text-error",
  };

  return (
    <div className="bg-surface-container-low p-6 rounded-xl shadow-lg">
      <div className="flex items-start justify-between mb-4">
        <div>
          <p className="text-xs font-bold text-on-surface-variant uppercase tracking-wider">{title}</p>
          <p className="text-3xl font-black text-on-surface mt-2">{value}</p>
          {subtitle && <p className="text-xs text-outline mt-1">{subtitle}</p>}
        </div>
        <div className={`p-3 rounded-lg ${colorClasses[color]}`}>
          <span className="material-symbols-outlined text-2xl">{icon}</span>
        </div>
      </div>
    </div>
  );
}

// Trend Block Component
function TrendBlock({
  title,
  data,
  icon,
}: {
  title: string;
  data: { period: string; count: number }[];
  icon: string;
}) {
  const maxCount = Math.max(...data.map((d) => d.count), 1);
  const TRENDS_ROWS_LIMIT = 5;

  return (
    <div className="bg-surface-container-low p-6 rounded-xl shadow-lg">
      <div className="flex items-center gap-2 mb-4">
        <span className="material-symbols-outlined text-primary">{icon}</span>
        <h3 className="text-sm font-bold text-on-surface">{title}</h3>
      </div>

      {data.length === 0 ? (
        <p className="text-xs text-outline">{stolmc_text(Text.AnalyticsNoTrendsData)}</p>
      ) : (
        <div className="space-y-2">
          {data.slice(0, TRENDS_ROWS_LIMIT).map((item) => (
            <div key={item.period} className="flex items-center gap-3">
              <span className="text-xs text-outline w-20">
                {new Date(item.period).toLocaleDateString("en-US", { month: "short", day: "numeric" })}
              </span>
              <div className="flex-1 bg-surface-container-high rounded-full h-6 overflow-hidden">
                <div
                  className="bg-primary/60 h-full rounded-full transition-all"
                  style={{ width: `${(item.count / maxCount) * 100}%` }}
                />
              </div>
              <span className="text-xs font-bold text-on-surface-variant w-8 text-right">
                {item.count}
              </span>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
