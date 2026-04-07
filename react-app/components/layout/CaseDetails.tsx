import { useState, Fragment, useEffect } from "react";
import { useInViewStore } from "../../stores/inViewStore";
import { useClientsStore } from "../../stores/clientsStore";
import { get as fetchGet, post, put, del } from "../../utils/fetch";
import { toast } from "react-toastify";
import Spinner from "./Spinner";
import type { User } from "../../types";

declare const data: Record<string, any>;

interface CaseData {
  id: string | number;
  id_user: string | number;
  title: string;
  status: "open" | "close" | string;
  description?: string;
  created_at: string;
  [key: string]: any;
}

export default function CaseDetails() {
  const inViewState = useInViewStore((state) => state);
  const { navigate } = useInViewStore();
  const [caseData, setCaseData] = useState<CaseData | null>(null);
  const [client, setClient] = useState<User | null>(null);
  const [loading, setLoading] = useState(true);
  const [isEditing, setIsEditing] = useState(false);
  const [editTitle, setEditTitle] = useState("");

  useEffect(() => {
    // Only load if we're in the right view
    if (inViewState.view !== "cases" || !inViewState.caseId) {
      return;
    }

    const loadCase = async () => {
      setLoading(true);
      const apiUrlCases = `${data.root_url}/wp-json/${data.api_url}/cases`;

      try {
        // Get the client's cases from the clients store
        const { users } = useClientsStore.getState();

        // Find the case by searching through each user's cases
        for (const user of users) {
          const casesRes = await fetchGet(`${apiUrlCases}/${user.id}`, {
            headers: { "X-WP-Nonce": data.nonce },
          });

          if (casesRes.data) {
            const foundCase = casesRes.data.find((c: CaseData) => String(c.id) === String(inViewState.caseId));
            if (foundCase) {
              setCaseData(foundCase);
              setClient(user);
              setEditTitle(foundCase.title);
              break;
            }
          }
        }
      } catch (error) {
        console.error("Error loading case:", error);
        toast.error("Failed to load case details");
      } finally {
        setLoading(false);
      }
    };

    loadCase();
  }, [inViewState.caseId, inViewState.view]);

  const handleSaveCase = async () => {
    if (!caseData || !editTitle.trim()) {
      toast.error("Case title cannot be empty");
      return;
    }

    const apiUrlCases = `${data.root_url}/wp-json/${data.api_url}/cases`;

    try {
      await put(
        `${apiUrlCases}/${caseData.id}`,
        { id_user: caseData.id_user, title: editTitle.trim() },
        {
          headers: {
            "X-WP-Nonce": data.nonce,
            "Content-type": "application/json",
          },
        }
      );

      setCaseData({ ...caseData, title: editTitle.trim() });
      setIsEditing(false);
      toast.success("Case updated successfully");
    } catch (error) {
      console.error("Error updating case:", error);
      toast.error("Failed to update case");
    }
  };

  const handleToggleStatus = async () => {
    if (!caseData) return;

    const apiUrlCases = `${data.root_url}/wp-json/${data.api_url}/cases-status`;

    try {
      await post(
        `${apiUrlCases}/${caseData.id}`,
        null,
        { headers: { "X-WP-Nonce": data.nonce } }
      );

      const newStatus = caseData.status === "open" ? "close" : "open";
      setCaseData({ ...caseData, status: newStatus });
      toast.success(`Case is now ${newStatus}`);
    } catch (error) {
      console.error("Error toggling status:", error);
      toast.error("Failed to update status");
    }
  };

  const handleDeleteCase = async () => {
    if (!caseData) return;
    if (!confirm(`Are you sure you want to delete "${caseData.title}"?`)) return;

    const apiUrlCases = `${data.root_url}/wp-json/${data.api_url}/cases`;

    try {
      await del(`${apiUrlCases}/${caseData.id}`, {
        headers: { "X-WP-Nonce": data.nonce },
      });

      toast.success("Case deleted successfully");
      navigate("cases", "", "", "");
    } catch (error) {
      console.error("Error deleting case:", error);
      toast.error("Failed to delete case");
    }
  };

  // Required for navigation purposes - MUST be after all hooks
  if (inViewState.view !== "cases" || !inViewState.caseId) {
    return <Fragment></Fragment>;
  }

  if (loading) {
    return <Spinner />;
  }

  if (!caseData) {
    return (
      <section className="flex-1 flex items-center justify-center h-full">
        <div className="text-center">
          <span className="material-symbols-outlined text-6xl text-outline-variant mb-4">
            folder_off
          </span>
          <p className="text-on-surface-variant text-sm font-medium">Case not found</p>
        </div>
      </section>
    );
  }

  return (
    <section className="flex-1 h-full overflow-y-auto">
      <div className="p-12 max-w-4xl">
        {/* Header */}
        <div className="flex items-start justify-between mb-8">
          <div>
            {isEditing ? (
              <input
                type="text"
                value={editTitle}
                onChange={(e) => setEditTitle(e.target.value)}
                className="text-3xl font-black text-on-surface tracking-tight bg-surface-container-low border border-outline-variant/20 rounded-lg py-2 px-4 focus:ring-2 focus:ring-primary/10"
                autoFocus
              />
            ) : (
              <h1 className="text-3xl font-black text-on-surface tracking-tight">
                {caseData.title}
              </h1>
            )}
            {client && (
              <p className="text-on-surface-variant text-sm mt-2">
                Client: {client.name}
              </p>
            )}
          </div>
          <div className="flex gap-2">
            {isEditing ? (
              <>
                <button
                  onClick={handleSaveCase}
                  className="flex items-center gap-1 px-4 py-2 bg-primary text-white text-xs font-bold rounded-lg"
                >
                  <span className="material-symbols-outlined text-sm">check</span>
                  Save
                </button>
                <button
                  onClick={() => { setIsEditing(false); setEditTitle(caseData.title); }}
                  className="flex items-center gap-1 px-4 py-2 bg-surface-container-high text-on-surface-variant text-xs font-bold rounded-lg"
                >
                  <span className="material-symbols-outlined text-sm">close</span>
                  Cancel
                </button>
              </>
            ) : (
              <>
                <button
                  onClick={() => setIsEditing(true)}
                  className="flex items-center gap-1 px-4 py-2 bg-surface-container-high text-on-surface-variant text-xs font-bold rounded-lg hover:bg-surface-container"
                  title="Edit case"
                >
                  <span className="material-symbols-outlined text-sm">edit</span>
                </button>
                <button
                  onClick={handleToggleStatus}
                  className={`flex items-center gap-1 px-4 py-2 text-xs font-bold rounded-lg ${
                    caseData.status === "open"
                      ? "bg-orange-100 text-orange-700"
                      : "bg-green-100 text-green-700"
                  }`}
                >
                  <span className="material-symbols-outlined text-sm">
                    {caseData.status === "open" ? "toggle_off" : "toggle_on"}
                  </span>
                  {caseData.status === "open" ? "Open" : "Closed"}
                </button>
                <button
                  onClick={handleDeleteCase}
                  className="flex items-center gap-1 px-4 py-2 bg-red-100 text-red-700 text-xs font-bold rounded-lg hover:bg-red-200"
                  title="Delete case"
                >
                  <span className="material-symbols-outlined text-sm">delete</span>
                </button>
              </>
            )}
          </div>
        </div>

        {/* Details Grid */}
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
          {/* Status */}
          <div className="bg-surface-container-low p-6 rounded-xl">
            <div className="flex items-center gap-3 mb-3">
              <span className="material-symbols-outlined text-primary text-xl">
                {caseData.status === "open" ? "radio_button_checked" : "check_circle"}
              </span>
              <label className="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">
                Status
              </label>
            </div>
            <p className="text-sm font-medium text-on-surface">
              {caseData.status === "open" ? "Open" : "Closed"}
            </p>
          </div>

          {/* Created */}
          <div className="bg-surface-container-low p-6 rounded-xl">
            <div className="flex items-center gap-3 mb-3">
              <span className="material-symbols-outlined text-primary text-xl">
                calendar_today
              </span>
              <label className="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">
                Created
              </label>
            </div>
            <p className="text-sm text-on-surface">
              {caseData.created_at
                ? new Date(caseData.created_at).toLocaleDateString("en-US", {
                    year: "numeric",
                    month: "long",
                    day: "numeric",
                  })
                : "N/A"}
            </p>
          </div>
        </div>

        {/* Description */}
        <div className="bg-surface-container-low p-6 rounded-xl">
          <div className="flex items-center gap-3 mb-3">
            <span className="material-symbols-outlined text-primary text-xl">
              description
            </span>
            <label className="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">
              Description
            </label>
          </div>
          <p className="text-sm text-on-surface whitespace-pre-wrap">
            {caseData.description || "No description provided."}
          </p>
        </div>

        {/* Back Button */}
        <button
          onClick={() => navigate("cases", "", "", "")}
          className="mt-8 flex items-center gap-2 px-6 py-3 bg-surface-container-low hover:bg-surface-container-high rounded-xl transition-all text-on-surface-variant hover:text-on-surface font-medium"
        >
          <span className="material-symbols-outlined text-sm">arrow_back</span>
          Back to Cases List
        </button>
      </div>
    </section>
  );
}
