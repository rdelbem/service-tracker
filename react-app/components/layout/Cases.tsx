import { useState, useEffect, useCallback } from "react";
import { useInViewStore } from "../../stores/inViewStore";
import { useCasesStore } from "../../stores/casesStore";
import Spinner from "./Spinner";
import Case from "./Case";
import type { Case as CaseType } from "../../types";
import { get as fetchGet, post } from "../../utils/fetch";
import { toast } from "react-toastify";

declare const data: Record<string, any>;

const CASES_PER_PAGE = 10;

export default function Cases() {
  const inViewState = useInViewStore((state) => state);
  const { navigate } = useInViewStore();
  // Keep hook call stable across renders — we don't use store state here.
  useCasesStore();

  const [allCases, setAllCases] = useState<CaseType[]>([]);
  const [filteredCases, setFilteredCases] = useState<CaseType[]>([]);
  const [searchQuery, setSearchQuery] = useState("");
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [page, setPage] = useState(1);

  // Derived pagination values.
  const totalPages = Math.max(1, Math.ceil(filteredCases.length / CASES_PER_PAGE));
  const pagedCases = filteredCases.slice(
    (page - 1) * CASES_PER_PAGE,
    page * CASES_PER_PAGE
  );

  // Load all cases whenever the view becomes "cases".
  useEffect(() => {
    if (inViewState.view !== "cases") return;

    const loadAllCases = async () => {
      setLoading(true);
      setError(null);
      setPage(1);

      try {
        const apiUrlCases = `${data.root_url}/wp-json/${data.api_url}/cases`;
        const apiUrlUsers = `${data.root_url}/wp-json/service-tracker-stolmc/v1/users`;

        // Step 1: Fetch all users across all pages.
        let allUsers: any[] = [];
        let usersPage = 1;
        let usersTotalPages = 1;

        do {
          const usersRes = await fetchGet(
            `${apiUrlUsers}?page=${usersPage}&per_page=6`,
            { headers: { "X-WP-Nonce": data.nonce } }
          );

          const body = usersRes.data;
          const usersOnPage: any[] = Array.isArray(body?.data) ? body.data : [];
          usersTotalPages =
            typeof body?.total_pages === "number" ? body.total_pages : 1;

          allUsers = [...allUsers, ...usersOnPage];
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
          let casesPage = 1;
          let casesTotalPages = 1;

          do {
            const casesRes = await fetchGet(
              `${apiUrlCases}/${user.id}?page=${casesPage}&per_page=6`,
              { headers: { "X-WP-Nonce": data.nonce } }
            );

            const casesBody = casesRes.data;
            const pageCases: CaseType[] = Array.isArray(casesBody?.data)
              ? casesBody.data
              : [];
            casesTotalPages =
              typeof casesBody?.total_pages === "number"
                ? casesBody.total_pages
                : 1;

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
        setError("Failed to load cases. Please try again.");
      } finally {
        setLoading(false);
      }
    };

    loadAllCases();
  }, [inViewState.view]);

  // Filter cases based on search query — reset to page 1 on new query.
  useEffect(() => {
    setPage(1);
    if (searchQuery.trim() === "") {
      setFilteredCases(allCases);
    } else {
      const q = searchQuery.toLowerCase();
      setFilteredCases(
        allCases.filter(
          (c) =>
            c.title.toLowerCase().includes(q) ||
            (c.clientName ?? "").toLowerCase().includes(q)
        )
      );
    }
  }, [searchQuery, allCases]);

  const handleAddCase = () => {
    navigate("casesAddNew", "", "", "");
  };

  const handleToggleCase = useCallback(
    async (caseId: string | number) => {
      const theCase = allCases.find((c) => String(c.id) === String(caseId));
      const targetStatus = theCase?.status === "open" ? "close" : "open";

      try {
        await post(
          `${data.root_url}/wp-json/${data.api_url}/cases-status/${caseId}`,
          null,
          { headers: { "X-WP-Nonce": data.nonce } }
        );

        const update = (prev: CaseType[]) =>
          prev.map((c) =>
            String(c.id) === String(caseId)
              ? { ...c, status: targetStatus }
              : c
          );

        setAllCases(update);
        setFilteredCases(update);
        toast.success(
          `Case is now ${targetStatus === "open" ? "open" : "closed"}`
        );
      } catch (err) {
        console.error("Error toggling case:", err);
        toast.error("Failed to update case status");
      }
    },
    [allCases]
  );

  // Keep hook order stable — early return AFTER all hooks.
  if (inViewState.view !== "cases") {
    return null;
  }

  if (loading) {
    return <Spinner />;
  }

  if (error) {
    return (
      <section className="flex-1 h-full overflow-y-auto">
        <div className="p-8 text-center py-16">
          <span className="material-symbols-outlined text-6xl text-error mb-4">
            error
          </span>
          <p className="text-on-surface-variant text-sm font-medium">{error}</p>
          <button
            onClick={() => setError(null)}
            className="mt-4 px-6 py-3 bg-primary text-white text-sm font-bold rounded-xl"
          >
            Retry
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
            <h1 className="text-3xl font-black text-on-surface tracking-tight">
              Cases
            </h1>
            <p className="text-on-surface-variant text-sm mt-1">
              {allCases.length > 0
                ? `${allCases.length} case${allCases.length !== 1 ? "s" : ""} across all clients`
                : "Search and manage all service cases"}
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
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
            placeholder="Search cases by title or client name..."
            className="w-full bg-surface-container-low border-0 rounded-xl py-4 pl-12 pr-4 text-sm focus:ring-2 focus:ring-primary/10 transition-all placeholder:text-outline-variant"
          />
        </div>

        {/* Cases List */}
        {filteredCases.length === 0 ? (
          <div className="text-center py-16">
            <span className="material-symbols-outlined text-6xl text-outline-variant mb-4">
              folder_open
            </span>
            <p className="text-on-surface-variant text-sm font-medium">
              {searchQuery ? "No cases match your search" : "No cases found"}
            </p>
            {!searchQuery && (
              <button
                onClick={handleAddCase}
                className="mt-4 flex items-center gap-2 px-6 py-3 bg-gradient-to-br from-primary to-primary-container text-white text-sm font-bold rounded-xl shadow-lg active:scale-95 transition-all mx-auto"
              >
                <span className="material-symbols-outlined text-sm">
                  add_circle
                </span>
                Create your first case
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
                {/* Previous */}
                <button
                  onClick={() => setPage((p) => Math.max(1, p - 1))}
                  disabled={page <= 1}
                  className="flex items-center gap-1 px-3 py-2 rounded-lg text-xs font-bold text-on-surface-variant hover:bg-surface-container-high disabled:opacity-30 disabled:cursor-not-allowed transition-all"
                >
                  <span className="material-symbols-outlined text-sm">
                    chevron_left
                  </span>
                  Prev
                </button>

                {/* Page indicators — show at most 7 buttons with ellipsis */}
                <div className="flex items-center gap-1">
                  {Array.from({ length: totalPages }, (_, i) => i + 1)
                    .filter(
                      (p) =>
                        p === 1 ||
                        p === totalPages ||
                        Math.abs(p - page) <= 2
                    )
                    .reduce<(number | "...")[]>((acc, p, idx, arr) => {
                      if (idx > 0 && p - (arr[idx - 1] as number) > 1) {
                        acc.push("...");
                      }
                      acc.push(p);
                      return acc;
                    }, [])
                    .map((p, idx) =>
                      p === "..." ? (
                        <span
                          key={`ellipsis-${idx}`}
                          className="w-7 text-center text-xs text-outline-variant"
                        >
                          …
                        </span>
                      ) : (
                        <button
                          key={p}
                          onClick={() => setPage(p as number)}
                          className={`w-7 h-7 rounded-lg text-xs font-bold transition-all ${
                            p === page
                              ? "bg-primary text-white shadow-sm"
                              : "text-on-surface-variant hover:bg-surface-container-high"
                          }`}
                        >
                          {p}
                        </button>
                      )
                    )}
                </div>

                {/* Next */}
                <button
                  onClick={() => setPage((p) => Math.min(totalPages, p + 1))}
                  disabled={page >= totalPages}
                  className="flex items-center gap-1 px-3 py-2 rounded-lg text-xs font-bold text-on-surface-variant hover:bg-surface-container-high disabled:opacity-30 disabled:cursor-not-allowed transition-all"
                >
                  Next
                  <span className="material-symbols-outlined text-sm">
                    chevron_right
                  </span>
                </button>
              </div>
            )}
          </>
        )}
      </div>

      {/* Floating Add Button */}
      <button
        onClick={handleAddCase}
        className="fixed bottom-10 right-10 w-16 h-16 rounded-full bg-primary text-white shadow-2xl flex items-center justify-center active:scale-90 transition-all hover:bg-primary-container z-30"
        title="Add new case"
      >
        <span className="material-symbols-outlined text-3xl">add</span>
      </button>
    </section>
  );
}
