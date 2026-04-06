import { useContext, useState, Fragment } from "react";
import ClientsContext from "../../context/clients/clientsContext";
import InViewContext from "../../context/inView/inViewContext";
import Spinner from "./Spinner";
import Search from "./Search";
import { ClientsContextType, InViewContextType, User } from "../../types";

export default function ClientsView() {
  const clientsContext = useContext(ClientsContext) as ClientsContextType;
  const inViewContext = useContext(InViewContext) as InViewContextType;
  const { state, searchUsers } = clientsContext;
  const { state: inViewState, updateIdView } = inViewContext;
  const [newClientName, setNewClientName] = useState("");
  const [newClientEmail, setNewClientEmail] = useState("");
  const [showAddForm, setShowAddForm] = useState(false);
  const [selectedClient, setSelectedClient] = useState<User | null>(null);

  // Only show this view when view === 'clients'
  if (inViewState.view !== "clients") {
    return <Fragment></Fragment>;
  }

  const handleSelectClient = (client: User) => {
    setSelectedClient(client);
    updateIdView(client.id, "", "clients", client.name);
  };

  const handleAddClient = (e: React.FormEvent) => {
    e.preventDefault();
    // TODO: Implement actual client creation
    // For now, just log and reset form
    console.log("Adding client:", { name: newClientName, email: newClientEmail });
    setNewClientName("");
    setNewClientEmail("");
    setShowAddForm(false);
  };

  return (
    <section className="flex-shrink-0 w-[420px] bg-surface-container-low flex flex-col border-r border-outline-variant/10 h-full">
      {/* Header with Search */}
      <div className="p-8 pb-4">
        <div className="flex items-center justify-between mb-6">
          <h2 className="text-2xl font-black text-on-surface tracking-tighter">
            Clients
          </h2>
          <button
            onClick={() => setShowAddForm(!showAddForm)}
            className="flex items-center gap-2 px-4 py-2 bg-gradient-to-br from-primary to-primary-container text-white text-xs font-bold rounded-lg shadow-lg active:scale-95 transition-all"
          >
            <span className="material-symbols-outlined text-sm">
              {showAddForm ? "close" : "person_add"}
            </span>
            {showAddForm ? "Cancel" : "Add Client"}
          </button>
        </div>

        {/* Add Client Form */}
        {showAddForm && (
          <form onSubmit={handleAddClient} className="mb-6 p-4 bg-surface-container-lowest rounded-xl shadow-[0px_4px_16px_rgba(11,28,48,0.06)] space-y-3">
            <div>
              <label className="block text-xs font-bold text-on-surface-variant mb-1 uppercase tracking-wider">
                Name *
              </label>
              <input
                type="text"
                value={newClientName}
                onChange={(e) => setNewClientName(e.target.value)}
                placeholder="Client name..."
                className="w-full bg-surface-container-low border border-outline-variant/20 rounded-lg py-2 px-3 text-sm focus:ring-2 focus:ring-primary/10 transition-all placeholder:text-outline-variant"
                required
              />
            </div>
            <div>
              <label className="block text-xs font-bold text-on-surface-variant mb-1 uppercase tracking-wider">
                Email *
              </label>
              <input
                type="email"
                value={newClientEmail}
                onChange={(e) => setNewClientEmail(e.target.value)}
                placeholder="client@example.com"
                className="w-full bg-surface-container-low border border-outline-variant/20 rounded-lg py-2 px-3 text-sm focus:ring-2 focus:ring-primary/10 transition-all placeholder:text-outline-variant"
                required
              />
            </div>
            <button
              type="submit"
              className="w-full flex items-center justify-center gap-2 px-4 py-2 bg-gradient-to-br from-primary to-primary-container text-white text-xs font-bold rounded-lg shadow-lg active:scale-95 transition-all"
            >
              <span className="material-symbols-outlined text-sm">person_add</span>
              Create Client
            </button>
          </form>
        )}

        <Search onSearch={searchUsers} />
      </div>

      {/* Client List */}
      <div className="flex-1 overflow-y-auto px-4 space-y-2 pb-8">
        {state.loadingUsers && <Spinner />}
        {!state.loadingUsers && state.users.length === 0 && (
          <div className="text-center py-12">
            <span className="material-symbols-outlined text-6xl text-outline-variant mb-4">
              group
            </span>
            <p className="text-on-surface-variant text-sm font-medium">
              No clients found
            </p>
            <p className="text-on-surface-variant/60 text-xs mt-1">
              Add your first client to get started
            </p>
          </div>
        )}
        {state.users.map((client: User) => {
          const isSelected = selectedClient?.id === client.id;
          return (
            <div
              key={client.id}
              onClick={() => handleSelectClient(client)}
              className={`group cursor-pointer p-4 rounded-xl transition-all ${
                isSelected
                  ? "bg-surface-container-lowest shadow-[0px_8px_24px_rgba(11,28,48,0.08)] border-l-4 border-primary"
                  : "hover:bg-surface-container-high/50"
              }`}
            >
              <div className="flex items-center gap-3">
                {/* Avatar */}
                <div
                  className={`w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0 ${
                    isSelected
                      ? "bg-primary text-white"
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

      {/* Selected Client Details */}
      {selectedClient && (
        <div className="border-t border-outline-variant/10 p-6 bg-surface-container-low">
          <h3 className="text-lg font-black text-on-surface tracking-tight mb-4">
            Client Details
          </h3>
          <div className="space-y-3">
            <div>
              <label className="block text-[10px] font-bold text-on-surface-variant uppercase tracking-wider mb-1">
                Name
              </label>
              <p className="text-sm font-semibold text-on-surface">
                {selectedClient.name || "N/A"}
              </p>
            </div>
            {selectedClient.email && (
              <div>
                <label className="block text-[10px] font-bold text-on-surface-variant uppercase tracking-wider mb-1">
                  Email
                </label>
                <p className="text-sm text-on-surface">{selectedClient.email}</p>
              </div>
            )}
            {selectedClient.phone && (
              <div>
                <label className="block text-[10px] font-bold text-on-surface-variant uppercase tracking-wider mb-1">
                  Phone
                </label>
                <p className="text-sm text-on-surface">{selectedClient.phone}</p>
              </div>
            )}
            {selectedClient.cellphone && (
              <div>
                <label className="block text-[10px] font-bold text-on-surface-variant uppercase tracking-wider mb-1">
                  Cellphone
                </label>
                <p className="text-sm text-on-surface">{selectedClient.cellphone}</p>
              </div>
            )}
            <div>
              <label className="block text-[10px] font-bold text-on-surface-variant uppercase tracking-wider mb-1">
                Role
              </label>
              <p className="text-sm text-on-surface">
                {selectedClient.role || "Customer"}
              </p>
            </div>
            {selectedClient.created_at && (
              <div>
                <label className="block text-[10px] font-bold text-on-surface-variant uppercase tracking-wider mb-1">
                  Client Since
                </label>
                <p className="text-sm text-on-surface-variant">
                  {new Date(selectedClient.created_at).toLocaleDateString()}
                </p>
              </div>
            )}
          </div>
        </div>
      )}
    </section>
  );
}
