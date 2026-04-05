import React, { useContext, useState, Fragment } from "react";
import { CSSTransition } from "react-transition-group";
import CasesContext from "../../context/cases/casesContext";
import InViewContext from "../../context/inView/inViewContext";
import ProgressContext from "../../context/progress/progressContext";
import dateformat from "dateformat";

export default function Case({ id, id_user, status, created_at, title }) {
  const casesContext = useContext(CasesContext);
  const { deleteCase, toggleCase, editCase } = casesContext;
  const inViewContext = useContext(InViewContext);
  const { updateIdView } = inViewContext;
  const progressContext = useContext(ProgressContext);
  const { getStatus } = progressContext;
  const [editing, setEditing] = useState(false);
  const [newTitle, setNewTitle] = useState("");

  const getStatusColor = () => {
    if (status === "open") return "bg-secondary-container/40 text-on-secondary-container";
    if (status === "close") return "bg-surface-dim/40 text-outline";
    return "bg-outline-variant/20 text-on-surface-variant";
  };

  const getStatusLabel = () => {
    if (status === "open") return "Active";
    if (status === "close") return "Closed";
    return "Unknown";
  };

  return (
    <Fragment>
      <div className="group cursor-pointer p-6 bg-surface-container-lowest rounded-2xl shadow-[0px_12px_32px_rgba(11,28,48,0.06)] border-l-4 border-primary hover:bg-surface-container-high transition-all">
        <div className="flex items-start justify-between mb-4">
          <div className="flex-1">
            <div className="flex items-center gap-3 mb-2">
              <span
                onClick={() => {
                  updateIdView(id_user, id, "progress", inViewContext.state.name);
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
                updateIdView(id_user, id, "progress", inViewContext.state.name);
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
              onClick={() => toggleCase(id)}
              className="p-2 rounded-lg hover:bg-surface-container-highest transition-colors text-on-surface-variant hover:text-secondary"
              data-tooltip-id="service-tracker"
              data-tooltip-content={status === "open" ? data.tip_toggle_case_open || "Close Case" : data.tip_toggle_case_close || "Open Case"}
            >
              <span className="material-symbols-outlined text-sm">
                {status === "open" ? "toggle_on" : "toggle_off"}
              </span>
            </button>

            <button
              onClick={() => deleteCase(id, title)}
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
      <CSSTransition
        in={editing}
        timeout={400}
        classNames="editing"
        unmountOnExit
      >
        <div className="bg-surface-container p-6 rounded-2xl shadow-lg mb-4">
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
                className="px-6 py-3 bg-primary text-white text-sm font-bold rounded-xl shadow-lg active:scale-95 transition-all"
              >
                {data.btn_save_case || "Save"}
              </button>
              <button
                onClick={(e) => {
                  e.preventDefault();
                  setEditing(false);
                }}
                className="px-6 py-3 bg-surface-container-highest text-on-surface text-sm font-bold rounded-xl shadow-lg active:scale-95 transition-all"
              >
                {data.btn_dismiss_edit || "Dismiss"}
              </button>
            </div>
          </form>
        </div>
      </CSSTransition>
    </Fragment>
  );
}
