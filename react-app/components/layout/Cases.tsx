import { useState, useEffect, useCallback, useRef } from "react";
import { useInViewStore } from "../../stores/inViewStore";
import { useCasesStore } from "../../stores/casesStore";
import Spinner from "./Spinner";
import Case from "./Case";
import type { Case as CaseType } from "../../types";
import { get as fetchGet, post } from "../../utils/fetch";
import { normalizeUsers } from "../../utils/users";
import { toast } from "react-toastify";
import { stolmc_text, Text } from "../../i18n";

const CASES_PER_PAGE = 10;

export default function Cases() {
  const inViewState = useInViewStore((state) => state);
  const { navigate }  = useInViewStore();

  // We manage our own full list for the global view; the store is used only
  // for the per-client view (ClientDetails). Keep the hook call stable.
  useCasesStore();

  const [allCases, setAllCases]         = useState<CaseType[]>([]);
  const [filteredCases, setFilteredCases] = useState<CaseType[]>([]);
  const [localQuery, setLocalQuery]     = useState("");
  const [loading, setLoading]           = useState(false);
  const [error, setError]               = useState<string | null>(null);
  const [page, setPage]                 = useState(1);

  const debounceRef = useRef<ReturnType<typeof setTimeout> | null>(null);

  // Derived pagination values (client-side slice of the full fetched list).
  const totalPages = Math.max(1, Math.ceil(filteredCases.length / CASES_PER_PAGE));
  const pagedCases = filteredCases.slice(
    (page - 1) * CASES_PER_PAGE,
    page * CASES_PER_PAGE
  );

  // Load ALL cases (across all users/pages) whenever the view becomes "cases".
  useEffect(() => {
    if (inViewState.view !== "cases") return;

    const loadAllCases = async () => {
      setLoading(true);
      setError(null);
      setPage(1);
      setLocalQuery("");

      try {
        const apiUrlCases = `${data.root_url}/wp-json/${data.api_url}/cases`;
        const apiUrlUsers = `${data.root_url}/wp-json/service-tracker-stolmc/v1/users`;

        // Step 1: Fetch all users across all pages.
        let allUsers: any[]  = [];
        let usersPage        = 1;
        let usersTotalPages  = 1;

        do {
          const usersRes = await fetchGet(
            `${apiUrlUsers}?page=${usersPage}&per_page=6`,
            { headers: { "X-WP-Nonce": data.nonce } }
          );
          const body = usersRes.data;
          const pagination = body?.meta?.pagination ?? {};
          const usersOnPage = normalizeUsers(body?.data);
          usersTotalPages = typeof pagination.total_pages === "number" ? pagination.total_pages : 1;
          allUsers            = [...allUsers, ...usersOnPage];
          usersPage++;
        } while (usersPage <= usersTotalPages);

        if (allUsers.length === 0) {
          setAllCases([]);
          setFilteredCases([]);
          setLoading(false);
          return;
        }

        // Step 2: For each user, fetch ALL their cases across all pages.
        let casesList: CaseType[] = [];

        for (const user of allUsers) {
          let casesPage       = 1;
          let casesTotalPages = 1;

          do {
            const casesRes = await fetchGet(
              `${apiUrlCases}/${user.id}?page=${casesPage}&per_page=6`,
              { headers: { "X-WP-Nonce": data.nonce } }
            );
            const casesBody = casesRes.data;
            const casesPagination = casesBody?.meta?.pagination ?? {};
            const pageCases: CaseType[] = Array.isArray(casesBody?.data) ? casesBody.data : [];
            casesTotalPages = typeof casesPagination.total_pages === "number" ? casesPagination.total_pages : 1;

            if (pageCases.length > 0) {
              casesList = [
                ...casesList,
                ...pageCases.map((c: CaseType) => ({
                  ...c,
                  clientName: user.name,
                  clientId: user.id,
                })),
              ];
            }
            casesPage++;
          } while (casesPage <= casesTotalPages);
        }

        setAllCases(casesList);
        setFilteredCases(casesList);
      } catch (err) {
        console.error("Error loading cases:", err);
        setError(stolmc_text(Text.AlertErrorBase));
      } finally {
        setLoading(false);
      }
    };

    loadAllCases();
  }, [inViewState.view]);

  // Debounced local filter against the already-fetched full list.
  // The inverted-index search endpoint is used by ClientDetails (per-user).
  // For the global view we filter client-side since we already have all cases.
  useEffect(() => {
    if (debounceRef.current) clearTimeout(debounceRef.current);

    debounceRef.current = setTimeout(() => {
      setPage(1);
      if (localQuery.trim() === "") {
        setFilteredCases(allCases);
      } else {
        const q = localQuery.toLowerCase();
        setFilteredCases(
          allCases.filter(
            (c) =>
              c.title.toLowerCase().includes(q) ||
              (c.clientName ?? "").toLowerCase().includes(q)
          )
        );
      }
    }, 300);

    return () => {
      if (debounceRef.current) clearTimeout(debounceRef.current);
    };
  }, [localQuery, allCases]);

  const handleAddCase = () => navigate("casesAddNew", "", "", "");

  const handleToggleCase = useCallback(
    async (caseId: string | number) => {
      const theCase      = allCases.find((c) => String(c.id) === String(caseId));
      const targetStatus = theCase?.status === "open" ? "close" : "open";

      try {
        await post(
          `${data.root_url}/wp-json/${data.api_url}/cases-status/${caseId}`,
          null,
          { headers: { "X-WP-Nonce": data.nonce } }
        );

        const update = (prev: CaseType[]) =>
          prev.map((c) =>
            String(c.id) === String(caseId) ? { ...c, status: targetStatus } : c
          );

        setAllCases(update);
        setFilteredCases(update);
        toast.success(`${stolmc_text(Text.ToastToggleBaseMsg)} ${targetStatus === "open" ? stolmc_text(Text.ToastToggleStateOpenMsg) : stolmc_text(Text.ToastToggleStateCloseMsg)}`);
      } catch (err) {
        console.error("Error toggling case:", err);
        toast.error(stolmc_text(Text.ToastCaseToggled));
      }
    },
    [allCases]
  );

  // Keep hook order stable — early return AFTER all hooks.
  if (inViewState.view !== "cases") return null;
  if (loading) return <Spinner />;

  if (error) {
    return (
      <section className="flex-1 h-full overflow-y-auto">
        <div className="p-8 text-center py-16">
          <span className="material-symbols-outlined text-6xl text-error mb-4">error</span>
          <p className="text-on-surface-variant text-sm font-medium">{error}</p>
          <button
            onClick={() => setError(null)}
            className="mt-4 px-6 py-3 bg-primary text-on-primary text-sm font-bold rounded-xl hover:bg-primary-container transition-colors"
          >
            {stolmc_text(Text.CasesRetry)}
          </button>
        </div>
      </section>
    );
  }

  return (
    <section className="flex-1 h-full overflow-y-auto relative">
      <div className="p-8">
        {/* Header */}
        <div className="flex items-center justify-between mb-6">
          <div>
            <h1 className="text-3xl font-black text-on-surface tracking-tight">{stolmc_text(Text.CasesHeading)}</h1>
            <p className="text-on-surface-variant text-sm mt-1">
              {allCases.length > 0
                ? stolmc_text(Text.CasesCountSubtitle).replace('%d', String(allCases.length))
                : stolmc_text(Text.CasesSearchPlaceholder)}
            </p>
          </div>
        </div>

        {/* Search Bar */}
        <div className="relative mb-8">
          <span className="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline text-xl">
            search
          </span>
          <input
            type="text"
            value={localQuery}
            onChange={(e) => setLocalQuery(e.target.value)}
            placeholder={stolmc_text(Text.CasesSearchPlaceholder)}
            className="w-full bg-surface-container-low border-0 rounded-xl py-4 pl-12 pr-10 text-sm focus:ring-2 focus:ring-primary/10 transition-all placeholder:text-outline-variant"
          />
          {localQuery && (
            <button
              onClick={() => setLocalQuery("")}
              className="absolute right-4 top-1/2 -translate-y-1/2 text-outline hover:text-on-surface transition-colors"
            >
              <span className="material-symbols-outlined text-lg">close</span>
            </button>
          )}
        </div>

        {/* Cases List */}
        {filteredCases.length === 0 ? (
          <div className="text-center py-16">
            <span className="material-symbols-outlined text-6xl text-outline-variant mb-4">
              {localQuery ? "search_off" : "folder_open"}
            </span>
            <p className="text-on-surface-variant text-sm font-medium">
              {localQuery ? stolmc_text(Text.CasesEmptySearch) : stolmc_text(Text.CasesEmptySearch)}
            </p>
            {!localQuery && (
              <button
                onClick={handleAddCase}
                className="mt-4 flex items-center gap-2 px-6 py-3 bg-primary text-on-primary text-sm font-bold rounded-xl shadow-sm active:scale-95 transition-all hover:bg-primary-container mx-auto"
              >
                <span className="material-symbols-outlined text-sm">add_circle</span>
                {stolmc_text(Text.CasesCreateFirst)}
              </button>
            )}
          </div>
        ) : (
          <>
            {/* Paged cases */}
            <div className="grid grid-cols-1 gap-4 mb-8">
              {pagedCases.map((item: CaseType) => (
                <Case key={item.id} {...item} onToggle={handleToggleCase} />
              ))}
            </div>

            {/* Pagination */}
            {totalPages > 1 && (
              <div className="flex items-center justify-between gap-2 pb-24">
                <button
                  onClick={() => setPage((p) => Math.max(1, p - 1))}
                  disabled={page <= 1}
                  className="flex items-center gap-1 px-3 py-2 rounded-lg text-xs font-bold text-on-surface-variant hover:bg-surface-container-high disabled:opacity-30 disabled:cursor-not-allowed transition-all"
                >
                  <span className="material-symbols-outlined text-sm">chevron_left</span>
                  {stolmc_text(Text.BtnPrev)}
                </button>

                <div className="flex items-center gap-1">
                  {Array.from({ length: totalPages }, (_, i) => i + 1)
                    .filter((p) => p === 1 || p === totalPages || Math.abs(p - page) <= 2)
                    .reduce<(number | "...")[]>((acc, p, idx, arr) => {
                      if (idx > 0 && p - (arr[idx - 1] as number) > 1) acc.push("...");
                      acc.push(p);
                      return acc;
                    }, [])
                    .map((p, idx) =>
                      p === "..." ? (
                        <span key={`ellipsis-${idx}`} className="w-7 text-center text-xs text-outline-variant">…</span>
                      ) : (
                        <button
                          key={p}
                          onClick={() => setPage(p as number)}
                          className={`w-7 h-7 rounded-lg text-xs font-bold transition-all ${
                            p === page
                              ? "bg-primary text-on-primary shadow-sm"
                              : "text-on-surface-variant hover:bg-surface-container-high"
                          }`}
                        >
                          {p}
                        </button>
                      )
                    )}
                </div>

                <button
                  onClick={() => setPage((p) => Math.min(totalPages, p + 1))}
                  disabled={page >= totalPages}
                  className="flex items-center gap-1 px-3 py-2 rounded-lg text-xs font-bold text-on-surface-variant hover:bg-surface-container-high disabled:opacity-30 disabled:cursor-not-allowed transition-all"
                >
                  {stolmc_text(Text.BtnNext)}
                  <span className="material-symbols-outlined text-sm">chevron_right</span>
                </button>
              </div>
            )}
          </>
        )}
      </div>

      {/* Floating Add Button */}
      <button
        onClick={handleAddCase}
        className="fixed bottom-10 right-10 w-16 h-16 rounded-full bg-primary text-on-primary shadow-lg flex items-center justify-center active:scale-90 transition-all hover:bg-primary-container z-30"
        title={stolmc_text(Text.CasesAddNew)}
      >
        <span className="material-symbols-outlined text-3xl">add</span>
      </button>
    </section>
  );
}
