import { useContext, useState, Fragment } from "react";
import Case from "./Case";
import CasesContext from "../../context/cases/casesContext";
import InViewContext from "../../context/inView/inViewContext";
import Spinner from "../../components/layout/Spinner";
import { CasesContextType, InViewContextType, Case as CaseType } from "../../types";

export default function Cases() {
  const inViewContext = useContext(InViewContext) as InViewContextType;
  const casesContext = useContext(CasesContext) as CasesContextType;
  const { state, postCase, currentUserInDisplay } = casesContext;
  const [caseTitle, setCaseTitle] = useState("");

  // Required for navigation purposes
  if (inViewContext.state.view !== "cases") {
    return <Fragment></Fragment>;
  }

  if (state.loadingCases) {
    return <Spinner />;
  }

  // Empty state - no cases yet
  if (state.cases.length === 0 && !state.loadingCases && currentUserInDisplay) {
    return (
      <Fragment>
        {/* Top Action Header */}
        <header className="h-20 px-12 flex items-center justify-between border-b border-outline-variant/5">
          <div className="flex items-center gap-4">
            <div className="w-2 h-2 rounded-full bg-secondary shadow-[0_0_8px_rgba(0,108,73,0.4)]"></div>
            <span className="text-[10px] font-black uppercase tracking-widest text-on-surface-variant">
              Currently Viewing: {inViewContext.state.name}
            </span>
          </div>
          <div className="flex items-center gap-3">
            <button className="flex items-center gap-2 px-6 py-2 bg-gradient-to-br from-primary to-primary-container text-white text-xs font-bold rounded-lg shadow-lg active:scale-95 transition-all">
              <span className="material-symbols-outlined text-sm">add_circle</span>
              Create Case
            </button>
          </div>
        </header>

        {/* Empty State */}
        <div className="flex items-center justify-center p-12">
          <form className="w-full max-w-md">
            <input
              className="w-full bg-surface-container-lowest border-2 border-outline-variant/20 rounded-xl py-4 px-6 text-sm focus:ring-2 focus:ring-primary/10 transition-all placeholder:text-outline-variant mb-4"
              placeholder={data.case_name || "Enter case name..."}
              onChange={(e) => setCaseTitle(e.target.value)}
              type="text"
              value={caseTitle}
            />
            <button
              onClick={(e) => {
                e.preventDefault();
                if (caseTitle.trim() !== "") {
                  postCase(currentUserInDisplay, caseTitle);
                  setCaseTitle("");
                }
              }}
              className="w-full flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-br from-primary to-primary-container text-white text-sm font-bold rounded-xl shadow-lg active:scale-95 transition-all"
            >
              <span className="material-symbols-outlined text-sm">add_circle</span>
              {data.btn_add_case || "Add Case"}
            </button>
          </form>
        </div>
      </Fragment>
    );
  }

  return (
    <Fragment>
      {/* Top Action Header */}
      <header className="h-20 px-12 flex items-center justify-between border-b border-outline-variant/5 flex-shrink-0">
        <div className="flex items-center gap-4">
          <div className="w-2 h-2 rounded-full bg-secondary shadow-[0_0_8px_rgba(0,108,73,0.4)]"></div>
          <span className="text-[10px] font-black uppercase tracking-widest text-on-surface-variant">
            Currently Viewing: {inViewContext.state.name}
          </span>
        </div>
        <div className="flex items-center gap-3">
          <button
            onClick={() => {
              setCaseTitle("");
              document.getElementById("new-case-input")?.focus();
            }}
            className="flex items-center gap-2 px-6 py-2 bg-gradient-to-br from-primary to-primary-container text-white text-xs font-bold rounded-lg shadow-lg active:scale-95 transition-all"
          >
            <span className="material-symbols-outlined text-sm">add_circle</span>
            Create Case
          </button>
        </div>
      </header>

      {/* Cases List */}
      <div className="flex-1 overflow-y-auto p-12">
        <div className="mb-8">
          <h1 className="text-4xl font-black text-on-surface tracking-tighter mb-2">
            Cases
          </h1>
          <p className="text-on-surface-variant text-sm">
            Manage and track all active cases
          </p>
        </div>

        {/* New Case Input */}
        <form className="mb-8 bg-surface-container-lowest p-6 rounded-2xl shadow-[0px_12px_32px_rgba(11,28,48,0.06)]">
          <div className="flex gap-4">
            <input
              id="new-case-input"
              className="flex-1 bg-surface-container-low border-0 rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-primary/10 transition-all placeholder:text-outline-variant"
              placeholder={data.case_name || "Enter new case name..."}
              onChange={(e) => setCaseTitle(e.target.value)}
              type="text"
              value={caseTitle}
            />
            <button
              onClick={(e) => {
                e.preventDefault();
                if (caseTitle.trim() !== "") {
                  postCase(currentUserInDisplay ?? "", caseTitle);
                  setCaseTitle("");
                }
              }}
              className="px-6 py-3 bg-gradient-to-br from-primary to-primary-container text-white text-sm font-bold rounded-xl shadow-lg active:scale-95 transition-all"
            >
              {data.btn_add_case || "Add"}
            </button>
          </div>
        </form>

        {/* Cases Grid */}
        <div className="grid grid-cols-1 gap-4">
          {state.cases.map((item: CaseType) => (
            <Case key={item.id} {...item} />
          ))}
        </div>
      </div>

      {/* Floating Quick Action */}
      <div className="absolute bottom-10 right-10">
        <button
          onClick={() => document.getElementById("new-case-input")?.focus()}
          className="w-16 h-16 rounded-full bg-primary text-white shadow-2xl flex items-center justify-center active:scale-90 transition-all hover:rotate-90"
        >
          <span className="material-symbols-outlined text-3xl">add</span>
        </button>
      </div>
    </Fragment>
  );
}
