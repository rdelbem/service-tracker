import { useState, useEffect, Fragment } from "react";
import { useCasesStore } from "../../stores/casesStore";
import { useInViewStore } from "../../stores/inViewStore";
import { useProgressStore } from "../../stores/progressStore";
import dateformat from "dateformat";
import { showConfirm } from "../ui/Modal";
import type { Case as CaseType } from "../../types";

declare const data: Record<string, any>;

export default function Case({ id, id_user, status, created_at, title, onToggle }: CaseType & { onToggle?: (id: string | number) => void }) {
  const { deleteCase, toggleCase, editCase } = useCasesStore();
  const inViewState = useInViewStore((state) => state);
  const { navigate } = useInViewStore();
  const { getStatus } = useProgressStore();
  const [editing, setEditing] = useState(false);
  const [newTitle, setNewTitle] = useState("");
  const [toggling, setToggling] = useState(false);
  const [localStatus, setLocalStatus] = useState(status);

  // Sync localStatus when the parent re-renders with updated status from store
  useEffect(() => {
    setLocalStatus(status);
  }, [status]);

  const handleToggle = async () => {
    setToggling(true);
    const newStatus = localStatus === "open" ? "close" : "open";
    setLocalStatus(newStatus); // Optimistic update

    if (onToggle) {
      try {
        await onToggle(id);
      } catch {
        setLocalStatus(localStatus); // Rollback on error
      }
    } else {
      // Fallback to store method
      try {
        await toggleCase(id);
      } catch {
        setLocalStatus(localStatus); // Rollback on error
      }
    }
    setToggling(false);
  };

  const handleDelete = async () => {
    const confirmed = await showConfirm({
      title: "Delete Case",
      message: `Are you sure you want to delete "${title}"? This action cannot be undone.`,
      confirmText: "Delete",
    });

    if (confirmed) {
      deleteCase(id);
    }
  };

  const getStatusColor = () => {
    if (localStatus === "open") return "bg-secondary-container/40 text-on-secondary-container";
    if (localStatus === "close") return "bg-surface-dim/40 text-outline";
    return "bg-outline-variant/20 text-on-surface-variant";
  };

  const getStatusLabel = () => {
    if (localStatus === "open") return "Active";
    if (localStatus === "close") return "Closed";
    return "Unknown";
  };

  return (
    <Fragment>
      <div className="group cursor-pointer p-6 bg-surface-container-lowest rounded-2xl border border-outline-variant/20 border-l-4 border-l-primary shadow-sm hover:bg-surface-container-high/70 transition-all">
        <div className="flex items-start justify-between mb-4">
          <div className="flex-1">
            <div className="flex items-center gap-3 mb-2">
              <span
                onClick={() => {
                  navigate("progress", id_user, id, inViewState.name);
                  getStatus(id, false, title);
                }}
                className="text-xl font-bold text-on-surface cursor-pointer hover:text-primary transition-colors"
              >
                {title}
              </span>
              <span className={`px-3 py-1 text-[10px] font-black uppercase tracking-wider rounded-md ${getStatusColor()}`}>
                {getStatusLabel()}
              </span>
            </div>
            <p className="text-xs text-outline">
              Created: {dateformat(created_at, "mmm dd, yyyy, hh:MM TT")}
            </p>
          </div>

          {/* Action Buttons */}
          <div className="flex items-center gap-2">
            <button
              onClick={() => {
                navigate("progress", id_user, id, inViewState.name);
                getStatus(id, false, title);
              }}
              className="p-2 rounded-lg hover:bg-surface-container-highest transition-colors text-on-surface-variant hover:text-primary"
              data-tooltip-id="service-tracker"
              data-tooltip-content="View Progress"
            >
              <span className="material-symbols-outlined text-sm">visibility</span>
            </button>

            <button
              onClick={() => setEditing(!editing)}
              className="p-2 rounded-lg hover:bg-surface-container-highest transition-colors text-on-surface-variant hover:text-primary"
              data-tooltip-id="service-tracker"
              data-tooltip-content={data.tip_edit_case || "Edit Case"}
            >
              <span className="material-symbols-outlined text-sm">edit</span>
            </button>

            <button
              onClick={handleToggle}
              disabled={toggling}
              className={`p-2 rounded-lg transition-colors ${
                localStatus === "open"
                  ? "hover:bg-secondary-container/30 text-on-surface-variant hover:text-secondary"
                  : "hover:bg-primary-container/30 text-on-surface-variant hover:text-primary"
              } disabled:opacity-50`}
              data-tooltip-id="service-tracker"
              data-tooltip-content={localStatus === "open" ? data.tip_toggle_case_open || "Close Case" : data.tip_toggle_case_close || "Open Case"}
              title={localStatus === "open" ? "Close Case" : "Open Case"}
            >
              <span className="material-symbols-outlined text-sm">
                {toggling ? "hourglass_empty" : localStatus === "open" ? "toggle_on" : "toggle_off"}
              </span>
            </button>

            <button
              onClick={handleDelete}
              className="p-2 rounded-lg hover:bg-error-container/30 transition-colors text-on-surface-variant hover:text-error"
              data-tooltip-id="service-tracker"
              data-tooltip-content={data.tip_delete_case || "Delete Case"}
            >
              <span className="material-symbols-outlined text-sm">delete</span>
            </button>
          </div>
        </div>
      </div>

      {/* Editing Mode */}
      {editing && (
        <div className="bg-surface-container p-6 rounded-2xl shadow-lg mb-4 animate-fade-in">
          <form>
            <div className="flex gap-4 mb-4">
              <input
                onChange={(e) => setNewTitle(e.target.value)}
                className="flex-1 bg-surface-container-lowest border-0 rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-primary/10 transition-all placeholder:text-outline-variant"
                type="text"
                placeholder="Enter new title..."
                defaultValue={title}
              />
              <button
                onClick={(e) => {
                  e.preventDefault();
                  editCase(id, id_user, newTitle);
                  setEditing(false);
                }}
                className="px-6 py-3 bg-primary text-on-primary text-sm font-bold rounded-xl shadow-sm active:scale-95 transition-all hover:bg-primary-container"
              >
                {data.btn_save_case || "Save"}
              </button>
              <button
                onClick={(e) => {
                  e.preventDefault();
                  setEditing(false);
                }}
                className="px-6 py-3 bg-surface-container-highest text-on-surface text-sm font-bold rounded-xl shadow-sm active:scale-95 transition-all hover:bg-surface-container-high"
              >
                {data.btn_dismiss_edit || "Dismiss"}
              </button>
            </div>
          </form>
        </div>
      )}
    </Fragment>
  );
}
