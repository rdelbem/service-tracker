import { useState, useEffect, Fragment, useCallback } from "react";
import { useInViewStore } from "../../stores/inViewStore";
import { useClientsStore } from "../../stores/clientsStore";
import { get as fetchGet } from "../../utils/fetch";
import Spinner from "./Spinner";
import type { User } from "../../types";
import { stolmc_text, stolmc_text_array, Text } from "../../i18n";

// Calendar item types
interface CalendarCase {
  id: number;
  id_user: number;
  title: string;
  status: string;
  description: string;
  start_at: string | null;
  due_at: string | null;
  client_name: string;
}

interface CalendarProgress {
  id: number;
  id_case: number;
  id_user: number;
  text: string;
  created_at: string;
  case_title: string;
  client_name: string;
}

interface CalendarData {
  cases: CalendarCase[];
  progress: CalendarProgress[];
  date_index: Record<string, { starts: number[]; ends: number[] }>;
}

// Helper functions
function getDaysInMonth(year: number, month: number): number {
  return new Date(year, month + 1, 0).getDate();
}

function getFirstDayOfMonth(year: number, month: number): number {
  return new Date(year, month, 1).getDay();
}

function formatDate(date: Date): string {
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, "0");
  const day = String(date.getDate()).padStart(2, "0");
  return `${year}-${month}-${day}`;
}

export default function Calendar() {
  const inViewState = useInViewStore((state) => state);
  const { navigate } = useInViewStore();
  const clientsState = useClientsStore((state) => state);

  const [currentDate, setCurrentDate] = useState(new Date());
  const [calendarData, setCalendarData] = useState<CalendarData | null>(null);
  const [loading, setLoading] = useState(false);
  const [selectedClient, setSelectedClient] = useState<string>("");
  const [selectedStatus, setSelectedStatus] = useState<string>("");

  const year = currentDate.getFullYear();
  const month = currentDate.getMonth();

  // Fetch calendar data
  const fetchCalendarData = useCallback(async () => {
    setLoading(true);
    const apiUrlCalendar = `${data.root_url}/wp-json/${data.api_url}/calendar`;

    const startDate = formatDate(new Date(year, month, 1));
    const endDate = formatDate(new Date(year, month + 1, 0));

    try {
      const params = new URLSearchParams({
        start: startDate,
        end: endDate,
      });

      if (selectedClient) {
        params.append("id_user", selectedClient);
      }

      if (selectedStatus) {
        params.append("status", selectedStatus);
      }

      const response = await fetchGet(`${apiUrlCalendar}?${params.toString()}`, {
        headers: { "X-WP-Nonce": data.nonce },
      });

      setCalendarData(response.data?.data || { cases: [], progress: [], date_index: {} });
    } catch (error) {
      console.error("Error fetching calendar:", error);
    } finally {
      setLoading(false);
    }
  }, [year, month, selectedClient, selectedStatus]);

  useEffect(() => {
    if (inViewState.view !== "calendar") {
      return;
    }

    fetchCalendarData();
  }, [inViewState.view, fetchCalendarData]);

  const prevMonth = () => {
    setCurrentDate(new Date(year, month - 1, 1));
  };

  const nextMonth = () => {
    setCurrentDate(new Date(year, month + 1, 1));
  };

  const handleCaseClick = (caseItem: CalendarCase) => {
    navigate("progress", caseItem.id_user.toString(), caseItem.id.toString(), caseItem.client_name);
  };

  // Generate calendar grid
  const daysInMonth = getDaysInMonth(year, month);
  const firstDay = getFirstDayOfMonth(year, month);
  const days = [];

  const MAX_ITEMS_PER_DAY = 3;

  // Empty cells for days before the first day of the month
  for (let i = 0; i < firstDay; i++) {
    days.push(<div key={`empty-${i}`} className="min-h-[120px] bg-surface/30" />);
  }

  // Days of the month
  for (let day = 1; day <= daysInMonth; day++) {
    const currentDayDate = new Date(year, month, day);
    const dateStr = formatDate(currentDayDate);

    // Get start/end indicators from the date index
    const dayIndex = calendarData?.date_index?.[dateStr];
    const hasStarts = dayIndex && dayIndex.starts.length > 0;
    const hasEnds = dayIndex && dayIndex.ends.length > 0;

    // Collect cases that start on this day
    const startingCases = calendarData?.cases.filter((caseItem) => {
      return caseItem.start_at && caseItem.start_at.startsWith(dateStr);
    }) || [];

    // Collect cases that end on this day
    const endingCases = calendarData?.cases.filter((caseItem) => {
      return caseItem.due_at && caseItem.due_at.startsWith(dateStr);
    }) || [];

    // Deduplicate
    const caseSet = new Set<number>();
    const startingList: CalendarCase[] = [];
    const endingList: CalendarCase[] = [];

    for (const c of startingCases) {
      if (!caseSet.has(c.id)) {
        caseSet.add(c.id);
        startingList.push(c);
      }
    }

    for (const c of endingCases) {
      if (!caseSet.has(c.id)) {
        caseSet.add(c.id);
        endingList.push(c);
      }
    }

    // Combine: starting cases first, then ending cases
    const allCases = [...startingList, ...endingList];
    const totalItems = allCases.length;
    const visibleItems = allCases.slice(0, MAX_ITEMS_PER_DAY);
    const hiddenCount = totalItems - MAX_ITEMS_PER_DAY;

    // Track which cases are ending/starting for styling
    const endingIds = new Set(endingList.map((c) => c.id));
    const startingIds = new Set(startingList.map((c) => c.id));

    days.push(
      <div
        key={day}
        className="min-h-[120px] bg-surface-container-low border border-outline-variant/10 p-2 overflow-y-auto"
      >
        {/* Day number with start/end indicators */}
        <div className="flex items-center justify-between mb-2">
          <span className="text-xs font-bold text-on-surface-variant">
            {day}
          </span>
          <div className="flex items-center gap-1">
            {hasEnds && (
              <span
                className="w-2 h-2 rounded-full bg-error/70"
                title={`${dayIndex.ends.length} ${stolmc_text(Text.CalendarCasesEnding)}`}
              />
            )}
            {hasStarts && (
              <span
                className="w-2 h-2 rounded-full bg-secondary/70"
                title={`${dayIndex.starts.length} ${stolmc_text(Text.CalendarCasesStarting)}`}
              />
            )}
          </div>
        </div>

        {/* Case items for this day */}
        {visibleItems.map((item) => {
          const isEnding = endingIds.has(item.id);
          const isStarting = startingIds.has(item.id);
          return (
            <button
              key={`case-${item.id}`}
              onClick={() => handleCaseClick(item)}
              className={`w-full text-left px-2 py-1 mb-1 rounded text-xs truncate transition-all hover:opacity-80 ${
                isEnding
                  ? "bg-error/10 text-error border border-error/40 hover:bg-error/20"
                  : isStarting
                    ? "bg-primary/10 text-primary border border-primary/40 hover:bg-primary/20"
                    : item.status === "open"
                      ? "bg-primary/10 text-primary hover:bg-primary/20"
                      : "bg-surface-variant text-on-surface-variant hover:bg-surface-variant/80"
              }`}
              title={`${item.title} - ${item.client_name}`}
            >
              {item.title}
            </button>
          );
        })}

        {hiddenCount > 0 && (
          <div className="text-xs text-on-surface-variant font-medium px-2 py-1 cursor-pointer hover:text-on-surface">
            +{hiddenCount} {stolmc_text(Text.CalendarMore)}
          </div>
        )}
      </div>
    );
  }

  // Required for navigation purposes - MUST be after all hooks
  if (inViewState.view !== "calendar") {
    return <Fragment></Fragment>;
  }

  const monthNames = stolmc_text_array(Text.CalendarMonths);

  return (
    <section className="flex-1 h-full overflow-y-auto">
      <div className="p-8">
        {/* Header */}
        <div className="mb-8">
          <h1 className="text-3xl font-black text-on-surface tracking-tight">
            {stolmc_text(Text.CalendarHeading)}
          </h1>
          <p className="text-on-surface-variant text-sm mt-2">
            {stolmc_text(Text.CalendarDescription)}
          </p>
        </div>

        {/* Filters */}
        <div className="bg-surface-container-low p-6 rounded-xl mb-6">
          <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
            {/* Month Navigation */}
            <div className="flex items-center gap-2">
              <button
                onClick={prevMonth}
                className="p-2 rounded-lg hover:bg-surface-container-high transition-all"
              >
                <span className="material-symbols-outlined text-on-surface-variant">
                  chevron_left
                </span>
              </button>
              <span className="text-sm font-bold text-on-surface">
                {monthNames[month]} {year}
              </span>
              <button
                onClick={nextMonth}
                className="p-2 rounded-lg hover:bg-surface-container-high transition-all"
              >
                <span className="material-symbols-outlined text-on-surface-variant">
                  chevron_right
                </span>
              </button>
            </div>

            {/* Client Filter */}
            <select
              value={selectedClient}
              onChange={(e) => setSelectedClient(e.target.value)}
              className="bg-surface-container-lowest border border-outline-variant/20 rounded-lg py-2 px-3 text-sm focus:ring-2 focus:ring-primary/10"
            >
              <option value="">{stolmc_text(Text.CalendarAllClients)}</option>
              {clientsState.users.map((user: User) => (
                <option key={user.id} value={user.id}>
                  {user.name}
                </option>
              ))}
            </select>

            {/* Status Filter */}
            <select
              value={selectedStatus}
              onChange={(e) => setSelectedStatus(e.target.value)}
              className="bg-surface-container-lowest border border-outline-variant/20 rounded-lg py-2 px-3 text-sm focus:ring-2 focus:ring-primary/10"
            >
              <option value="">{stolmc_text(Text.CalendarAllStatuses)}</option>
              <option value="open">{stolmc_text(Text.StatusActive)}</option>
              <option value="close">{stolmc_text(Text.StatusClosed)}</option>
            </select>
          </div>
        </div>

        {/* Calendar Grid */}
        {loading ? (
          <Spinner />
        ) : (
          <div className="bg-surface-container-low rounded-xl overflow-hidden border border-outline-variant/20">
            {/* Weekday Headers */}
            <div className="grid grid-cols-7 bg-surface-container-high">
              {stolmc_text_array(Text.CalendarWeekdays).map((day) => (
                <div
                  key={day}
                  className="p-2 text-xs font-bold text-on-surface-variant uppercase tracking-wider text-center border-b border-outline-variant/20"
                >
                  {day}
                </div>
              ))}
            </div>

            {/* Days Grid */}
            <div className="grid grid-cols-7">{days}</div>
          </div>
        )}

        {/* Legend */}
        <div className="mt-6 flex gap-6 text-xs">
          <div className="flex items-center gap-2">
            <div className="w-2 h-2 rounded-full bg-secondary/70"></div>
            <span className="text-on-surface-variant">{stolmc_text(Text.CalendarCaseStarts)}</span>
          </div>
          <div className="flex items-center gap-2">
            <div className="w-2 h-2 rounded-full bg-error/70"></div>
            <span className="text-on-surface-variant">{stolmc_text(Text.CalendarCaseEnds)}</span>
          </div>
        </div>
      </div>
    </section>
  );
}
