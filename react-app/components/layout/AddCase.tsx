import { useState, Fragment, useEffect } from "react";
import { useInViewStore } from "../../stores/inViewStore";
import { useClientsStore } from "../../stores/clientsStore";
import { toast } from "react-toastify";
import type { User } from "../../types";

declare const data: Record<string, any>;

export default function AddCase() {
  const inViewState = useInViewStore((state) => state);
  const { navigate } = useInViewStore();
  const clientsState = useClientsStore((state) => state);

  const [selectedUser, setSelectedUser] = useState<User | null>(null);
  const [caseTitle, setCaseTitle] = useState("");
  const [caseStatus, setCaseStatus] = useState("open");
  const [caseDescription, setCaseDescription] = useState("");
  const [searchQuery, setSearchQuery] = useState("");
  const [filteredUsers, setFilteredUsers] = useState<User[]>([]);
  const [showUserDropdown, setShowUserDropdown] = useState(false);
  const [isSubmitting, setIsSubmitting] = useState(false);

  // Filter users based on search query
  useEffect(() => {
    // Only run if we're in this view
    if (inViewState.view !== "casesAddNew") {
      setFilteredUsers([]);
      setShowUserDropdown(false);
      return;
    }

    if (searchQuery.trim() === "") {
      setFilteredUsers([]);
      setShowUserDropdown(false);
      return;
    }

    const filtered = clientsState.users.filter((user: User) =>
      user.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
      user.email?.toLowerCase().includes(searchQuery.toLowerCase())
    );
    setFilteredUsers(filtered);
    setShowUserDropdown(filtered.length > 0);
  }, [searchQuery, clientsState.users, inViewState.view]);

  const handleSelectUser = (user: User) => {
    setSelectedUser(user);
    setSearchQuery(user.name);
    setShowUserDropdown(false);
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();

    if (!selectedUser) {
      toast.error("Please select a client");
      return;
    }

    if (!caseTitle.trim()) {
      toast.error("Please enter a case title");
      return;
    }

    setIsSubmitting(true);

    try {
      const { useCasesStore } = await import("../../stores/casesStore");
      const { postCase, getCases } = useCasesStore.getState();
      
      // Post the case directly using the store's postCase function
      await postCase(selectedUser.id, caseTitle.trim());
      
      // Refresh cases and navigate
      await getCases(selectedUser.id, false);
      navigate("cases", "", "", "");
    } catch (error: any) {
      console.error("Error creating case:", error);
      toast.error(error?.message || "Failed to create case. Please try again.");
    } finally {
      setIsSubmitting(false);
    }
  };

  // Required for navigation purposes - MUST be after all hooks
  if (inViewState.view !== "casesAddNew") {
    return <Fragment></Fragment>;
  }

  return (
    <section className="flex-1 h-full overflow-y-auto">
      <div className="p-12 max-w-4xl">
        {/* Header */}
        <div className="mb-8">
          <h1 className="text-3xl font-black text-on-surface tracking-tight">
            Add New Case
          </h1>
          <p className="text-on-surface-variant text-sm mt-2">
            Create a new service case for a client
          </p>
        </div>

        <form onSubmit={handleSubmit} className="space-y-6">
          {/* Client Selection */}
          <div className="bg-surface-container-low p-6 rounded-xl relative">
            <label className="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-2">
              Client *
            </label>
            <div className="relative">
              <input
                type="text"
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                onFocus={() => {
                  if (filteredUsers.length > 0) setShowUserDropdown(true);
                }}
                placeholder="Search for a client..."
                className="w-full bg-surface-container-lowest border border-outline-variant/20 rounded-lg py-3 px-4 text-sm focus:ring-2 focus:ring-primary/10 transition-all placeholder:text-outline-variant"
              />
              {showUserDropdown && (
                <div className="absolute z-50 mt-2 w-full bg-surface-container-lowest border border-outline-variant/20 rounded-lg shadow-xl max-h-60 overflow-y-auto">
                  {filteredUsers.map((user: User) => (
                    <button
                      key={user.id}
                      type="button"
                      onClick={() => handleSelectUser(user)}
                      className="w-full text-left px-4 py-3 hover:bg-surface-container-high border-b border-outline-variant/10 last:border-b-0"
                    >
                      <div className="flex items-center gap-3">
                        <div className="w-8 h-8 rounded-full bg-primary text-white flex items-center justify-center text-xs font-bold flex-shrink-0">
                          {user.name.charAt(0).toUpperCase()}
                        </div>
                        <div className="min-w-0">
                          <p className="text-sm font-medium text-on-surface truncate">
                            {user.name}
                          </p>
                          <p className="text-xs text-on-surface-variant truncate">
                            {user.email}
                          </p>
                        </div>
                      </div>
                    </button>
                  ))}
                </div>
              )}
            </div>
            {selectedUser && (
              <div className="mt-2 flex items-center gap-2 text-sm text-on-surface-variant">
                <span className="material-symbols-outlined text-sm text-primary">check_circle</span>
                Selected: {selectedUser.name}
              </div>
            )}
          </div>

          {/* Case Title */}
          <div className="bg-surface-container-low p-6 rounded-xl">
            <label className="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-2">
              Case Title *
            </label>
            <input
              type="text"
              value={caseTitle}
              onChange={(e) => setCaseTitle(e.target.value)}
              placeholder="Enter case title..."
              className="w-full bg-surface-container-lowest border border-outline-variant/20 rounded-lg py-3 px-4 text-sm focus:ring-2 focus:ring-primary/10 transition-all placeholder:text-outline-variant"
              required
            />
          </div>

          {/* Status */}
          <div className="bg-surface-container-low p-6 rounded-xl">
            <label className="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-2">
              Status
            </label>
            <select
              value={caseStatus}
              onChange={(e) => setCaseStatus(e.target.value)}
              className="w-full bg-surface-container-lowest border border-outline-variant/20 rounded-lg py-3 px-4 text-sm focus:ring-2 focus:ring-primary/10 transition-all"
            >
              <option value="open">Open</option>
              <option value="close">Closed</option>
            </select>
          </div>

          {/* Description */}
          <div className="bg-surface-container-low p-6 rounded-xl">
            <label className="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-2">
              Description
            </label>
            <textarea
              value={caseDescription}
              onChange={(e) => setCaseDescription(e.target.value)}
              placeholder="Enter case description..."
              rows={4}
              className="w-full bg-surface-container-lowest border border-outline-variant/20 rounded-lg py-3 px-4 text-sm focus:ring-2 focus:ring-primary/10 transition-all placeholder:text-outline-variant resize-none"
            />
          </div>

          {/* Actions */}
          <div className="flex gap-4">
            <button
              type="submit"
              disabled={isSubmitting}
              className="flex-1 flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-br from-primary to-primary-container text-white text-sm font-bold rounded-xl shadow-lg active:scale-95 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span className="material-symbols-outlined text-sm">
                {isSubmitting ? "progress_activity" : "check"}
              </span>
              {isSubmitting ? "Creating..." : "Create Case"}
            </button>
            <button
              type="button"
              onClick={() => navigate("cases", "", "", "")}
              className="px-6 py-3 bg-surface-container-high text-on-surface-variant text-sm font-bold rounded-xl hover:bg-surface-container transition-all"
            >
              Cancel
            </button>
          </div>
        </form>
      </div>
    </section>
  );
}
