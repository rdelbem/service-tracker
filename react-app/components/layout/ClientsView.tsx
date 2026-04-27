import { useState, useEffect, useRef } from "react";
import { toast } from "react-toastify";
import { useInViewStore } from "../../stores/inViewStore";
import { useClientsStore } from "../../stores/clientsStore";
import Spinner from "./Spinner";
import type { User } from "../../types";
import { stolmc_text, Text } from "../../i18n";

export default function ClientsView() {
  const inViewState = useInViewStore((state) => state);
  const { navigate } = useInViewStore();
  const {
    users,
    loadingUsers,
    searchUsers,
    createUser,
    page,
    totalPages,
    total,
    setPage,
    searchQuery,
  } = useClientsStore();

  const [newClientName, setNewClientName] = useState("");
  const [newClientEmail, setNewClientEmail] = useState("");
  const [newClientPhone, setNewClientPhone] = useState("");
  const [newClientCellphone, setNewClientCellphone] = useState("");
  const [showAddForm, setShowAddForm] = useState(false);
  const [isCreating, setIsCreating] = useState(false);
  const [localQuery, setLocalQuery] = useState("");

  // Debounce search — fire the API call 350 ms after the user stops typing.
  const debounceRef = useRef<ReturnType<typeof setTimeout> | null>(null);

  useEffect(() => {
    if (debounceRef.current) clearTimeout(debounceRef.current);

    debounceRef.current = setTimeout(() => {
      searchUsers(localQuery);
    }, 350);

    return () => {
      if (debounceRef.current) clearTimeout(debounceRef.current);
    };
  }, [localQuery]);

  // Only show this view when view === 'clients'
  if (inViewState.view !== "clients") {
    return null;
  }

  const handleSelectClient = (client: User) => {
    navigate("clients", client.id, "", client.name);
  };

  const handleAddClient = async (e: React.FormEvent) => {
    e.preventDefault();

    if (!newClientName.trim() || !newClientEmail.trim()) {
      toast.error(stolmc_text(Text.ClientsNameEmailRequired));
      return;
    }

    setIsCreating(true);

    const result = await createUser({
      name: newClientName.trim(),
      email: newClientEmail.trim(),
      phone: newClientPhone.trim() || undefined,
      cellphone: newClientCellphone.trim() || undefined,
    });

    setIsCreating(false);

    if (result.success) {
      toast.success(result.message);
      setNewClientName("");
      setNewClientEmail("");
      setNewClientPhone("");
      setNewClientCellphone("");
      setShowAddForm(false);
      setLocalQuery("");
    } else {
      toast.error(result.message);
    }
  };

  return (
    <section className="flex-shrink-0 w-[420px] bg-surface-container-low flex flex-col border-r border-outline-variant/20 h-full">
      {/* Header */}
      <div className="p-8 pb-4">
        <div className="flex items-center justify-between mb-6">
          <div>
            <h2 className="text-2xl font-black text-on-surface tracking-tighter">
              {stolmc_text(Text.ClientsHeading)}
            </h2>
            {total > 0 && (
              <p className="text-xs text-on-surface-variant mt-0.5">
                {searchQuery.trim() !== ""
                  ? stolmc_text(Text.ClientsResultsFor).replace('%d', String(total)).replace('%s', searchQuery)
                  : stolmc_text(Text.ClientsTotal).replace('%d', String(total))}
              </p>
            )}
          </div>
          <button
            onClick={() => setShowAddForm(!showAddForm)}
            className="flex items-center gap-2 px-4 py-2 bg-primary text-on-primary text-xs font-bold rounded-lg shadow-sm active:scale-95 transition-all hover:bg-primary-container"
          >
            <span className="material-symbols-outlined text-sm">
              {showAddForm ? "close" : "person_add"}
            </span>
            {showAddForm ? stolmc_text(Text.BtnCancel) : stolmc_text(Text.ClientsAddBtn)}
          </button>
        </div>

        {/* Add Client Form */}
        {showAddForm && (
          <form
            onSubmit={handleAddClient}
            className="mb-6 p-4 bg-surface-container-lowest rounded-xl border border-outline-variant/20 shadow-sm space-y-3"
          >
            <div>
              <label className="block text-xs font-bold text-on-surface-variant mb-1 uppercase tracking-wider">
                {stolmc_text(Text.LabelName)} *
              </label>
              <input
                type="text"
                value={newClientName}
                onChange={(e) => setNewClientName(e.target.value)}
                placeholder={stolmc_text(Text.PlaceholderName)}
                className="w-full bg-surface-container-low border border-outline-variant/20 rounded-lg py-2 px-3 text-sm focus:ring-2 focus:ring-primary/10 transition-all placeholder:text-outline-variant"
                required
                disabled={isCreating}
              />
            </div>
            <div>
              <label className="block text-xs font-bold text-on-surface-variant mb-1 uppercase tracking-wider">
                {stolmc_text(Text.LabelEmail)} *
              </label>
              <input
                type="email"
                value={newClientEmail}
                onChange={(e) => setNewClientEmail(e.target.value)}
                placeholder={stolmc_text(Text.PlaceholderEmail)}
                className="w-full bg-surface-container-low border border-outline-variant/20 rounded-lg py-2 px-3 text-sm focus:ring-2 focus:ring-primary/10 transition-all placeholder:text-outline-variant"
                required
                disabled={isCreating}
              />
            </div>
            <div>
              <label className="block text-xs font-bold text-on-surface-variant mb-1 uppercase tracking-wider">
                {stolmc_text(Text.LabelPhone)}
              </label>
              <input
                type="tel"
                value={newClientPhone}
                onChange={(e) => setNewClientPhone(e.target.value)}
                placeholder={stolmc_text(Text.PlaceholderPhone)}
                className="w-full bg-surface-container-low border border-outline-variant/20 rounded-lg py-2 px-3 text-sm focus:ring-2 focus:ring-primary/10 transition-all placeholder:text-outline-variant"
                disabled={isCreating}
              />
            </div>
            <div>
              <label className="block text-xs font-bold text-on-surface-variant mb-1 uppercase tracking-wider">
                {stolmc_text(Text.LabelCellphone)}
              </label>
              <input
                type="tel"
                value={newClientCellphone}
                onChange={(e) => setNewClientCellphone(e.target.value)}
                placeholder={stolmc_text(Text.PlaceholderPhone)}
                className="w-full bg-surface-container-low border border-outline-variant/20 rounded-lg py-2 px-3 text-sm focus:ring-2 focus:ring-primary/10 transition-all placeholder:text-outline-variant"
                disabled={isCreating}
              />
            </div>
            <button
              type="submit"
              disabled={isCreating}
              className="w-full flex items-center justify-center gap-2 px-4 py-2 bg-primary text-on-primary text-xs font-bold rounded-lg shadow-sm active:scale-95 transition-all hover:bg-primary-container disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span className="material-symbols-outlined text-sm">
                {isCreating ? "progress_activity" : "person_add"}
              </span>
              {isCreating ? stolmc_text(Text.ClientsCreating) : stolmc_text(Text.ClientsCreateBtn)}
            </button>
          </form>
        )}

        {/* Search input — inline, replaces the Search component so we can
            control the value and debounce it ourselves. */}
        <div className="relative">
          <span className="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-lg pointer-events-none">
            search
          </span>
          <input
            type="text"
            value={localQuery}
            onChange={(e) => setLocalQuery(e.target.value)}
            placeholder={stolmc_text(Text.ClientsSearchPlaceholder)}
            className="w-full bg-surface-container-lowest border border-outline-variant/20 rounded-xl py-2.5 pl-10 pr-10 text-sm focus:ring-2 focus:ring-primary/10 transition-all placeholder:text-outline-variant"
          />
          {localQuery && (
            <button
              onClick={() => setLocalQuery("")}
              className="absolute right-3 top-1/2 -translate-y-1/2 text-outline hover:text-on-surface transition-colors"
            >
              <span className="material-symbols-outlined text-lg">close</span>
            </button>
          )}
        </div>
      </div>

      {/* Client List */}
      <div className="flex-1 overflow-y-auto px-4 space-y-2 pb-4">
        {loadingUsers && <Spinner />}
        {!loadingUsers && users.length === 0 && (
          <div className="text-center py-12">
            <span className="material-symbols-outlined text-6xl text-outline-variant mb-4">
              {localQuery ? "search_off" : "group"}
            </span>
            <p className="text-on-surface-variant text-sm font-medium">
              {localQuery
                ? stolmc_text(Text.ClientsEmptySearch)
                : stolmc_text(Text.ClientsEmpty)}
            </p>
            {!localQuery && (
              <p className="text-on-surface-variant/60 text-xs mt-1">
                {stolmc_text(Text.ClientsAddFirst)}
              </p>
            )}
          </div>
        )}
        {!loadingUsers &&
          users.map((client: User) => {
            const isSelected =
              String(inViewState.userId) === String(client.id);
            return (
              <div
                key={client.id}
                onClick={() => handleSelectClient(client)}
                className={`group cursor-pointer p-4 rounded-xl transition-all ${
                  isSelected
                    ? "bg-surface-container-lowest border border-outline-variant/30 border-l-4 border-l-primary shadow-sm"
                    : "hover:bg-surface-container-high/50"
                }`}
              >
                <div className="flex items-center gap-3">
                  {/* Avatar */}
                  <div
                    className={`w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0 ${
                      isSelected
                        ? "bg-primary text-on-primary"
                        : "bg-surface-container-highest text-on-surface-variant"
                    }`}
                  >
                    <span className="text-sm font-bold">
                      {client.name ? client.name.charAt(0).toUpperCase() : "C"}
                    </span>
                  </div>

                  {/* Client Info */}
                  <div className="flex-1 min-w-0">
                    <h3 className="text-sm font-bold text-on-surface truncate">
                      {client.name}
                    </h3>
                    {client.email && (
                      <p className="text-[11px] text-on-surface-variant/70 truncate">
                        {client.email}
                      </p>
                    )}
                  </div>
                </div>
              </div>
            );
          })}
      </div>

      {/* Pagination */}
      {!loadingUsers && totalPages > 1 && (
        <div className="flex-shrink-0 px-4 py-4 border-t border-outline-variant/20">
          <div className="flex items-center justify-between gap-2">
            {/* Previous */}
            <button
              onClick={() => setPage(page - 1)}
              disabled={page <= 1}
              className="flex items-center gap-1 px-3 py-2 rounded-lg text-xs font-bold text-on-surface-variant hover:bg-surface-container-high disabled:opacity-30 disabled:cursor-not-allowed transition-all"
            >
              <span className="material-symbols-outlined text-sm">
                chevron_left
              </span>
              {stolmc_text(Text.BtnPrev)}
            </button>

            {/* Page indicators */}
            <div className="flex items-center gap-1">
              {Array.from({ length: totalPages }, (_, i) => i + 1).map((p) => (
                <button
                  key={p}
                  onClick={() => setPage(p)}
                  className={`w-7 h-7 rounded-lg text-xs font-bold transition-all ${
                    p === page
                      ? "bg-primary text-on-primary shadow-sm"
                      : "text-on-surface-variant hover:bg-surface-container-high"
                  }`}
                >
                  {p}
                </button>
              ))}
            </div>

            {/* Next */}
            <button
              onClick={() => setPage(page + 1)}
              disabled={page >= totalPages}
              className="flex items-center gap-1 px-3 py-2 rounded-lg text-xs font-bold text-on-surface-variant hover:bg-surface-container-high disabled:opacity-30 disabled:cursor-not-allowed transition-all"
            >
              {stolmc_text(Text.BtnNext)}
              <span className="material-symbols-outlined text-sm">
                chevron_right
              </span>
            </button>
          </div>
        </div>
      )}
    </section>
  );
}
