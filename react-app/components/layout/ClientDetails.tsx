import { useInViewStore } from "../../stores/inViewStore";
import { useClientsStore } from "../../stores/clientsStore";
import type { User } from "../../types";

export default function ClientDetails() {
  const inViewState = useInViewStore((state) => state);
  const { navigate } = useInViewStore();
  const { users } = useClientsStore();

  // Only show this view when view === 'clients' and a user is selected
  if (inViewState.view !== "clients" || !inViewState.userId) {
    return null;
  }

  // Find the selected user from the clients list
  const selectedClient = users.find(
    (user: User) => String(user.id) === String(inViewState.userId)
  );

  // If the user is in the URL but not in state yet (e.g., direct link),
  // show a loading or not found state
  const clientName = selectedClient?.name || name || "Client";

  // Format the created date
  const createdDate = selectedClient?.created_at 
    ? new Date(selectedClient.created_at).toLocaleDateString("en-US", {
        year: "numeric",
        month: "long",
        day: "numeric",
      })
    : "N/A";

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
      </div>
    </section>
  );
}
