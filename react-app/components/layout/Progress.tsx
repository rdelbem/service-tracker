import { useState, useContext, Fragment } from "react";
import { CSSTransition } from "react-transition-group";
import InViewContext from "../../context/inView/inViewContext";
import ProgressContext from "../../context/progress/progressContext";
import TextareaAutosize from "react-textarea-autosize";
import Spinner from "./Spinner";
import Status from "./Status";
import { InViewContextType, ProgressContextType } from "../../types";

export default function Progress() {
  const inViewContext = useContext(InViewContext) as InViewContextType;
  const progressContext = useContext(ProgressContext) as ProgressContextType;
  const { state, postStatus } = progressContext;
  const [writingStatus, setWritingStatus] = useState(false);
  const [newText, setNewText] = useState("");

  // Required for navigation purposes
  if (inViewContext.state.view !== "progress") {
    return <Fragment></Fragment>;
  }

  if (state.loadingStatus) {
    return <Spinner />;
  }

  // Case ID
  const idCase = inViewContext.state.caseId;
  // User ID
  const idUser = inViewContext.state.userId;

  const allStatuses = [...state.status];

  return (
    <section className="flex-1 flex flex-col bg-background relative h-full">
      {/* Top Action Header */}
      <header className="h-20 px-12 flex items-center justify-between border-b border-outline-variant/5 flex-shrink-0">
        <div className="flex items-center gap-4">
          <div className="w-2 h-2 rounded-full bg-secondary shadow-[0_0_8px_rgba(0,108,73,0.4)]"></div>
          <span className="text-[10px] font-black uppercase tracking-widest text-on-surface-variant">
            Progress: {state.caseTitle}
          </span>
        </div>
        <div className="flex items-center gap-3">
          <button
            onClick={() => {
              inViewContext.updateIdView(
                idUser,
                idCase,
                "cases",
                inViewContext.state.name
              );
            }}
            className="flex items-center gap-2 px-4 py-2 bg-surface-container-highest text-on-surface text-xs font-bold rounded-lg transition-all active:scale-95"
          >
            <span className="material-symbols-outlined text-sm">arrow_back</span>
            Back to Cases
          </button>
        </div>
      </header>

      {/* Progress Content */}
      <div className="flex-1 overflow-y-auto p-12">
        {/* Case Title Header */}
        <div className="mb-12">
          <div className="flex items-baseline gap-6">
            <h1 className="text-4xl font-black text-on-surface tracking-tighter">
              {state.caseTitle}
            </h1>
            <span className="px-3 py-1 bg-secondary-container/40 text-on-secondary-container text-[10px] font-black uppercase tracking-wider rounded-md">
              Active Status
            </span>
          </div>
          <p className="text-on-surface-variant mt-4 max-w-2xl leading-relaxed font-body">
            Track all updates and progress for this case
          </p>
        </div>

        {/* Status Update Input */}
        <div className="bg-surface-container-lowest rounded-2xl p-8 shadow-[0px_12px_32px_rgba(11,28,48,0.06)] relative overflow-hidden mb-8">
          <div className="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-secondary to-secondary-container"></div>
          <div className="flex items-center justify-between mb-6">
            <h3 className="text-xl font-headline font-bold text-primary">
              Progress Update
            </h3>
            <div className="flex items-center gap-2 bg-secondary-container/20 px-3 py-1.5 rounded-full border border-secondary-container/30">
              <span
                className="material-symbols-outlined text-[18px] text-on-secondary-container"
                style={{ fontVariationSettings: "'FILL' 1" }}
              >
                mail
              </span>
              <span className="text-[10px] font-bold text-on-secondary-container uppercase tracking-wider">
                Client will be notified
              </span>
            </div>
          </div>

          <CSSTransition
            in={writingStatus}
            timeout={400}
            classNames="editing"
            unmountOnExit
          >
            <div className="status-add-new-container">
              <div className="relative mb-6">
                <TextareaAutosize
                  onChange={(e) => setNewText(e.target.value)}
                  className="w-full p-4 bg-surface-container-low rounded-xl border-none focus:ring-2 focus:ring-primary/10 text-on-surface font-medium resize-none placeholder:text-on-primary-container"
                  placeholder="Type progress details here... (e.g., 'Initial site visit completed')"
                  rows={4}
                  value={newText}
                />
              </div>
              <div className="flex justify-between items-center">
                <div className="flex gap-2">
                  <button
                    type="button"
                    className="p-2 text-on-surface-variant hover:bg-surface-container-high rounded-lg transition-all"
                    title="Attach Files"
                  >
                    <span className="material-symbols-outlined">attach_file</span>
                  </button>
                  <button
                    type="button"
                    className="p-2 text-on-surface-variant hover:bg-surface-container-high rounded-lg transition-all"
                    title="Add Image"
                  >
                    <span className="material-symbols-outlined">image</span>
                  </button>
                </div>
                <button
                  onClick={(e) => {
                    e.preventDefault();
                    if (newText.trim() === "") {
                      alert(data.alert_blank_status_title || "Status title cannot be blank");
                      return;
                    }
                    postStatus(idUser, idCase, newText.trim());
                    setNewText("");
                  }}
                  className="px-8 py-3 bg-primary text-on-primary font-bold rounded-xl shadow-lg active:scale-95 transition-transform"
                >
                  {data.add_status_btn || "Post Update"}
                </button>
              </div>
            </div>
          </CSSTransition>

          <button
            onClick={(e) => {
              e.preventDefault();
              setWritingStatus(!writingStatus);
            }}
            className={`w-full py-3 rounded-xl font-bold text-sm transition-all ${
              writingStatus
                ? "bg-surface-container-highest text-on-surface"
                : "bg-gradient-to-br from-primary to-primary-container text-white shadow-lg"
            }`}
          >
            {!writingStatus ? (
              <span className="flex items-center justify-center gap-2">
                <span className="material-symbols-outlined text-sm">add_circle</span>
                {data.new_status_btn || "Add Status Update"}
              </span>
            ) : (
              data.close_box_btn || "Cancel"
            )}
          </button>
        </div>

        {/* Timeline / Activity Log */}
        <div className="space-y-6">
          <div className="flex items-center gap-4 mb-8">
            <div className="h-[1px] flex-1 bg-outline-variant/20"></div>
            <span className="text-[10px] font-bold text-on-primary-container uppercase tracking-widest px-4">
              Activity Log
            </span>
            <div className="h-[1px] flex-1 bg-outline-variant/20"></div>
          </div>

          {allStatuses.length <= 0 && (
            <div className="text-center py-12">
              <span className="material-symbols-outlined text-6xl text-outline-variant mb-4">
                timeline
              </span>
              <h3 className="text-xl font-bold text-on-surface-variant">
                {data.no_progress_yet || "No progress updates yet"}
              </h3>
              <p className="text-sm text-outline mt-2">
                Add your first status update to get started
              </p>
            </div>
          )}

          {allStatuses.length > 0 &&
            allStatuses.map((item, index) => <Status key={item.id || index} {...item} />)}
        </div>
      </div>
    </section>
  );
}
