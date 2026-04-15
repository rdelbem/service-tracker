import { useEffect } from "react";
import { useInViewStore } from "../../stores/inViewStore";
import { useClientsStore } from "../../stores/clientsStore";
import { useCasesStore } from "../../stores/casesStore";
import dateformat from "dateformat";
import type { User, Case } from "../../types";

declare const data: Record<string, any>;

export default function ClientDetails() {
  const inViewState = useInViewStore((state) => state);
  const { navigate } = useInViewStore();
  const { users } = useClientsStore();
  const {
    cases: clientCases,
    loadingCases,
    page,
    totalPages,
    total,
    getCases,
    setPage,
  } = useCasesStore();

  // Find the selected user from the clients list.
  const selectedClient = users.find(
    (user: User) => String(user.id) === String(inViewState.userId)
  );

  const clientName = selectedClient?.name || inViewState.name || "Client";

  const createdDate = selectedClient?.created_at
    ? new Date(selectedClient.created_at).toLocaleDateString("en-US", {
        year: "numeric",
        month: "long",
        day: "numeric",
      })
    : "N/A";

  // Fetch cases for this client whenever the userId changes.
  useEffect(() => {
    if (!inViewState.userId) return;
    getCases(inViewState.userId, false, 1);
  }, [inViewState.userId]);

  // Keep hook order stable across route transitions.
  if (inViewState.view !== "clients" || !inViewState.userId) {
    return null;
  }

  const getStatusColor = (status: string) => {
    if (status === "open") return "bg-secondary-container/40 text-on-secondary-container";
    if (status === "close") return "bg-surface-dim/40 text-outline";
    return "bg-outline-variant/20 text-on-surface-variant";
  };

  const getStatusLabel = (status: string) => {
    if (status === "open") return "Active";
    if (status === "close") return "Closed";
    return "Unknown";
  };

  const handleCaseClick = (caseItem: Case) => {
    navigate("progress", caseItem.id_user, caseItem.id, caseItem.title);
  };

  return (
    <section className="flex-1 h-full overflow-y-auto">
      <div className="p-12">
        {/* Header */}
        <div className="flex items-center gap-6 mb-8">
          <div className="w-20 h-20 rounded-2xl bg-primary text-white flex items-center justify-center text-3xl font-bold shadow-lg">
            {clientName.charAt(0).toUpperCase()}
          </div>
          <div>
            <h1 className="text-3xl font-black text-on-surface tracking-tight">
              {clientName}
            </h1>
            <p className="text-sm text-on-surface-variant font-medium">
              {selectedClient?.role || "Customer"}
            </p>
          </div>
        </div>

        {/* Details Grid */}
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-4xl">
          {/* Email */}
          <div className="bg-surface-container-low p-6 rounded-xl">
            <div className="flex items-center gap-3 mb-3">
              <span className="material-symbols-outlined text-primary text-xl">
                mail
              </span>
              <label className="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">
                Email
              </label>
            </div>
            <p className="text-sm text-on-surface font-medium">
              {selectedClient?.email || "N/A"}
            </p>
          </div>

          {/* Phone */}
          <div className="bg-surface-container-low p-6 rounded-xl">
            <div className="flex items-center gap-3 mb-3">
              <span className="material-symbols-outlined text-primary text-xl">
                phone
              </span>
              <label className="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">
                Phone
              </label>
            </div>
            <p className="text-sm text-on-surface font-medium">
              {selectedClient?.phone || "N/A"}
            </p>
          </div>

          {/* Cellphone */}
          <div className="bg-surface-container-low p-6 rounded-xl">
            <div className="flex items-center gap-3 mb-3">
              <span className="material-symbols-outlined text-primary text-xl">
                smartphone
              </span>
              <label className="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">
                Cellphone
              </label>
            </div>
            <p className="text-sm text-on-surface font-medium">
              {selectedClient?.cellphone || "N/A"}
            </p>
          </div>

          {/* Client Since */}
          <div className="bg-surface-container-low p-6 rounded-xl">
            <div className="flex items-center gap-3 mb-3">
              <span className="material-symbols-outlined text-primary text-xl">
                calendar_today
              </span>
              <label className="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">
                Client Since
              </label>
            </div>
            <p className="text-sm text-on-surface font-medium">
              {createdDate}
            </p>
          </div>
        </div>

        {/* Back to List Button */}
        <button
          onClick={() => navigate("clients", "", "", "")}
          className="mt-8 flex items-center gap-2 px-6 py-3 bg-surface-container-low hover:bg-surface-container-high rounded-xl transition-all text-on-surface-variant hover:text-on-surface font-medium"
        >
          <span className="material-symbols-outlined text-sm">arrow_back</span>
          Back to Clients List
        </button>

        {/* Client Cases Section */}
        <div className="mt-12">
          <div className="flex items-center justify-between mb-6">
            <h2 className="text-2xl font-black text-on-surface tracking-tight">
              Cases
            </h2>
            {total > 0 && (
              <p className="text-xs text-on-surface-variant">
                {total} case{total !== 1 ? "s" : ""} total
              </p>
            )}
          </div>

          {loadingCases ? (
            <div className="flex items-center justify-center py-12">
              <span className="material-symbols-outlined text-4xl text-primary animate-spin">
                progress_activity
              </span>
            </div>
          ) : clientCases.length === 0 ? (
            <div className="text-center py-12 bg-surface-container-low rounded-xl">
              <span className="material-symbols-outlined text-5xl text-outline-variant mb-4">
                folder_open
              </span>
              <p className="text-on-surface-variant text-sm font-medium">
                No cases for this client
              </p>
            </div>
          ) : (
            <>
              <div className="grid grid-cols-1 gap-4">
                {clientCases.map((caseItem: Case) => (
                  <div
                    key={caseItem.id}
                    className="group cursor-pointer p-6 bg-surface-container-lowest rounded-2xl shadow-[0px_12px_32px_rgba(11,28,48,0.06)] border-l-4 border-primary hover:bg-surface-container-high transition-all"
                  >
                    <div className="flex items-start justify-between mb-4">
                      <div className="flex-1">
                        <div className="flex items-center gap-3 mb-2">
                          <span
                            onClick={() => handleCaseClick(caseItem)}
                            className="text-xl font-bold text-on-surface cursor-pointer hover:text-primary transition-colors"
                          >
                            {caseItem.title}
                          </span>
                          <span
                            className={`px-3 py-1 text-[10px] font-black uppercase tracking-wider rounded-md ${getStatusColor(caseItem.status)}`}
                          >
                            {getStatusLabel(caseItem.status)}
                          </span>
                        </div>
                        <p className="text-xs text-outline">
                          Created:{" "}
                          {dateformat(
                            caseItem.created_at,
                            "mmm dd, yyyy, hh:MM TT"
                          )}
                        </p>
                      </div>

                      {/* Action Buttons */}
                      <div className="flex items-center gap-2">
                        <button
                          onClick={() => handleCaseClick(caseItem)}
                          className="p-2 rounded-lg hover:bg-surface-container-highest transition-colors text-on-surface-variant hover:text-primary"
                          data-tooltip-id="service-tracker"
                          data-tooltip-content="View Progress"
                        >
                          <span className="material-symbols-outlined text-sm">
                            visibility
                          </span>
                        </button>
                      </div>
                    </div>
                  </div>
                ))}
              </div>

              {/* Pagination */}
              {totalPages > 1 && (
                <div className="mt-6 flex items-center justify-between gap-2">
                  {/* Previous */}
                  <button
                    onClick={() => setPage(inViewState.userId, page - 1)}
                    disabled={page <= 1}
                    className="flex items-center gap-1 px-3 py-2 rounded-lg text-xs font-bold text-on-surface-variant hover:bg-surface-container-high disabled:opacity-30 disabled:cursor-not-allowed transition-all"
                  >
                    <span className="material-symbols-outlined text-sm">
                      chevron_left
                    </span>
                    Prev
                  </button>

                  {/* Page indicators */}
                  <div className="flex items-center gap-1">
                    {Array.from({ length: totalPages }, (_, i) => i + 1).map(
                      (p) => (
                        <button
                          key={p}
                          onClick={() => setPage(inViewState.userId, p)}
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
                    onClick={() => setPage(inViewState.userId, page + 1)}
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
      </div>
    </section>
  );
}
