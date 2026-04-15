import { useState, useEffect, useCallback } from "react";
import { useInViewStore } from "../../stores/inViewStore";
import { useCasesStore } from "../../stores/casesStore";
import Spinner from "./Spinner";
import Case from "./Case";
import type { Case as CaseType } from "../../types";
import { get as fetchGet, post } from "../../utils/fetch";
import { toast } from "react-toastify";

declare const data: Record<string, any>;

export default function Cases() {
  const inViewState = useInViewStore((state) => state);
  const { navigate } = useInViewStore();
  const { loadingCases } = useCasesStore();
  const [allCases, setAllCases] = useState<CaseType[]>([]);
  const [filteredCases, setFilteredCases] = useState<CaseType[]>([]);
  const [searchQuery, setSearchQuery] = useState("");

  // Load all cases on mount
  useEffect(() => {
    if (inViewState.view !== "cases") return;

    const loadAllCases = async () => {
      const apiUrlCases = `${data.root_url}/wp-json/${data.api_url}/cases`;
      try {
        // Fetch all users across all pages.
        let allUsers: any[] = [];
        let usersPage = 1;
        let usersTotalPages = 1;

        do {
          const usersRes = await fetchGet(
            `${data.root_url}/wp-json/service-tracker-stolmc/v1/users?page=${usersPage}&per_page=6`,
            { headers: { "X-WP-Nonce": data.nonce } }
          );
          const envelope = usersRes.data;
          allUsers = [...allUsers, ...(envelope.data ?? [])];
          usersTotalPages = envelope.total_pages ?? 1;
          usersPage++;
        } while (usersPage <= usersTotalPages);

        let casesList: CaseType[] = [];

        // For each user, fetch ALL their cases across all pages.
        for (const user of allUsers) {
          let casesPage = 1;
          let casesTotalPages = 1;

          do {
            const casesRes = await fetchGet(
              `${apiUrlCases}/${user.id}?page=${casesPage}&per_page=6`,
              { headers: { "X-WP-Nonce": data.nonce } }
            );
            // Unpack the paginated envelope.
            const casesEnvelope = casesRes.data;
            const pageCases: CaseType[] = casesEnvelope.data ?? [];
            casesTotalPages = casesEnvelope.total_pages ?? 1;

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
      } catch (error) {
        console.error("Error loading cases:", error);
      }
    };

    loadAllCases();
  }, [inViewState.view]);

  // Filter cases based on search query
  useEffect(() => {
    if (searchQuery.trim() === "") {
      setFilteredCases(allCases);
    } else {
      const filtered = allCases.filter(
        (c) =>
          c.title.toLowerCase().includes(searchQuery.toLowerCase()) ||
          c.clientName?.toLowerCase().includes(searchQuery.toLowerCase())
      );
      setFilteredCases(filtered);
    }
  }, [searchQuery, allCases]);

  const handleAddCase = () => {
    navigate("casesAddNew", "", "", "");
  };

  const handleToggleCase = useCallback(async (caseId: string | number) => {
    const theCase = allCases.find((c) => String(c.id) === String(caseId));
    const targetStatus = theCase?.status === "open" ? "close" : "open";

    try {
      await post(`${data.root_url}/wp-json/${data.api_url}/cases-status/${caseId}`, null, {
        headers: { "X-WP-Nonce": data.nonce },
      });

      setAllCases((prev) =>
        prev.map((c) =>
          String(c.id) === String(caseId) ? { ...c, status: targetStatus } : c
        )
      );
      setFilteredCases((prev) =>
        prev.map((c) =>
          String(c.id) === String(caseId) ? { ...c, status: targetStatus } : c
        )
      );
      toast.success(`Case is now ${targetStatus === "open" ? "open" : "closed"}`);
    } catch (error) {
      console.error("Error toggling case:", error);
      toast.error("Failed to update case status");
    }
  }, [allCases]);

  // Keep hook order stable across route transitions.
  if (inViewState.view !== "cases") {
    return null;
  }

  if (loadingCases) {
    return <Spinner />;
  }

  return (
    <section className="flex-1 h-full overflow-y-auto">
      <div className="p-8">
        {/* Header */}
        <div className="flex items-center justify-between mb-6">
          <div>
            <h1 className="text-3xl font-black text-on-surface tracking-tight">
              Cases
            </h1>
            <p className="text-on-surface-variant text-sm mt-1">
              Search and manage all service cases
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
                <span className="material-symbols-outlined text-sm">add_circle</span>
                Create your first case
              </button>
            )}
          </div>
        ) : (
          <div className="grid grid-cols-1 gap-4">
            {filteredCases.map((item: CaseType) => (
              <Case key={item.id} {...item} onToggle={handleToggleCase} />
            ))}
          </div>
        )}
      </div>

      {/* Floating Add Button */}
      <button
        onClick={handleAddCase}
        className="absolute bottom-10 right-10 w-16 h-16 rounded-full bg-primary text-white shadow-2xl flex items-center justify-center active:scale-90 transition-all hover:bg-primary-container z-30"
        title="Add new case"
      >
        <span className="material-symbols-outlined text-3xl">add</span>
      </button>
    </section>
  );
}
