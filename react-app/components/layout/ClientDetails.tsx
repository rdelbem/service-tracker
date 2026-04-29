import { useEffect } from "react";
import { useInViewStore } from "../../stores/inViewStore";
import { useClientsStore } from "../../stores/clientsStore";
import { useCasesStore } from "../../stores/casesStore";
import type { User } from "../../types";
import Spinner from "./Spinner";
import Case from "./Case";
import { stolmc_text, Text } from "../../i18n";

export default function ClientDetails() {
  const inViewState = useInViewStore((state) => state);
  const { navigate } = useInViewStore();
  const { users } = useClientsStore();
  const { cases, loadingCases, page, totalPages, total, getCases } = useCasesStore();

  // Find the selected user from the clients list.
  const selectedClient = users.find(
    (user: User) => String(user.id) === String(inViewState.userId)
  );

  const clientName = selectedClient?.name || inViewState.name || stolmc_text(Text.ClientLabel);

  const createdDate = selectedClient?.created_at
    ? new Date(selectedClient.created_at).toLocaleDateString("en-US", {
        year: "numeric",
        month: "long",
        day: "numeric",
      })
    : stolmc_text(Text.Na);

  // Fetch cases for this client when component mounts or userId changes
  useEffect(() => {
    if (inViewState.userId) {
      getCases(inViewState.userId as string, false, 1);
    }
  }, [inViewState.userId, getCases]);

  // Keep hook order stable across route transitions.
  if (inViewState.view !== "clients" || !inViewState.userId) {
    return null;
  }

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
              {selectedClient?.role || stolmc_text(Text.ClientLabel)}
            </p>
          </div>
        </div>

        {/* Contact Information (read-only) */}
        <div className="bg-surface-container-lowest rounded-2xl border border-outline-variant/20 shadow-sm p-6 mb-8">
          <div className="border-b border-outline-variant pb-4 mb-6">
            <h2 className="text-xl font-bold text-on-surface">{stolmc_text(Text.ClientContactHeading)}</h2>
          </div>

          <div className="space-y-6">
            <div>
              <label className="block text-sm font-bold text-on-surface mb-2">
                {stolmc_text(Text.LabelEmail)}
              </label>
              <p className="text-on-surface-variant">
                {selectedClient?.email || stolmc_text(Text.Na)}
              </p>
            </div>

            <div>
              <label className="block text-sm font-bold text-on-surface mb-2">
                {stolmc_text(Text.LabelPhone)}
              </label>
              <p className="text-on-surface-variant">
                {selectedClient?.phone || stolmc_text(Text.Na)}
              </p>
            </div>

            <div>
              <label className="block text-sm font-bold text-on-surface mb-2">
                {stolmc_text(Text.LabelCellphone)}
              </label>
              <p className="text-on-surface-variant">
                {selectedClient?.cellphone || stolmc_text(Text.Na)}
              </p>
            </div>

            <div>
              <label className="block text-sm font-bold text-on-surface mb-2">
                {stolmc_text(Text.ClientSince)}
              </label>
              <p className="text-on-surface-variant">{createdDate}</p>
            </div>
          </div>
        </div>

        {/* Client Cases Section */}
        <div className="bg-surface-container-lowest rounded-2xl border border-outline-variant/20 shadow-sm p-6 mb-8">
          <div className="border-b border-outline-variant pb-4 mb-6">
            <h2 className="text-xl font-bold text-on-surface">{stolmc_text(Text.ClientCasesHeading)}</h2>
            <p className="text-on-surface-variant text-sm mt-1">
              {total} {stolmc_text(total === 1 ? Text.CaseSingular : Text.CasePlural)} {stolmc_text(Text.Found)}
            </p>
          </div>

          {loadingCases ? (
            <Spinner />
          ) : cases.length > 0 ? (
            <>
              <div className="grid grid-cols-1 gap-4 mb-6">
                {cases.map((c) => (
                  <Case
                    key={c.id}
                    id={c.id}
                    id_user={c.id_user}
                    title={c.title}
                    status={c.status}
                    created_at={c.created_at}
                  />
                ))}
              </div>

              {/* Pagination */}
              {totalPages > 1 && (
                <div className="flex items-center justify-between border-t border-outline-variant/20 pt-6">
                  <button
                    onClick={() => getCases(inViewState.userId as string, false, page - 1)}
                    disabled={page === 1}
                    className="px-4 py-2 bg-surface-container-high text-on-surface rounded-xl disabled:opacity-50"
                  >
                    {stolmc_text(Text.BtnPrev)}
                  </button>
                  <span className="text-on-surface-variant">
                    {stolmc_text(Text.Page)} {page} {stolmc_text(Text.Of)} {totalPages}
                  </span>
                  <button
                    onClick={() => getCases(inViewState.userId as string, false, page + 1)}
                    disabled={page === totalPages}
                    className="px-4 py-2 bg-surface-container-high text-on-surface rounded-xl disabled:opacity-50"
                  >
                    {stolmc_text(Text.BtnNext)}
                  </button>
                </div>
              )}
            </>
          ) : (
            <div className="text-center py-12">
              <p className="text-on-surface-variant">{stolmc_text(Text.ClientNoCases)}</p>
            </div>
          )}
        </div>

        {/* Back to List Button */}
        <button
          onClick={() => navigate("clients", "", "", "")}
          className="flex items-center gap-2 px-6 py-3 bg-surface-container-low hover:bg-surface-container-high rounded-xl transition-all text-on-surface-variant hover:text-on-surface font-medium"
        >
          <span className="material-symbols-outlined text-sm">arrow_back</span>
          {stolmc_text(Text.ClientBackToList)}
        </button>
      </div>
    </section>
  );
}
