import { useContext } from "react";
import InViewContext from "../../context/inView/inViewContext";
import CasesContext from "../../context/cases/casesContext";
import { User, InViewContextType, CasesContextType } from "../../types";

interface ClientProps extends User {
  caseCount?: number;
  activeSince?: string;
}

export default function Client({ id, name, caseCount, activeSince }: ClientProps) {
  const inViewContext = useContext(InViewContext) as InViewContextType;
  const { state, updateIdView } = inViewContext;
  const casesContext = useContext(CasesContext) as CasesContextType;
  const { getCases } = casesContext;

  const isActive = parseInt(String(state.userId)) === parseInt(String(id));

  return (
    <div
      onClick={() => {
        updateIdView(id, "", "cases", name);
        getCases(id, false);
      }}
      className={`group cursor-pointer p-4 rounded-xl transition-all ${
        isActive
          ? "bg-surface-container-lowest shadow-[0px_12px_32px_rgba(11,28,48,0.06)] border-l-4 border-primary"
          : "hover:bg-surface-container-high/50"
      }`}
    >
      <div className="flex items-center gap-4">
        {/* Avatar */}
        <div className={`w-12 h-12 rounded-xl flex items-center justify-center shadow-sm ${
          isActive
            ? "bg-primary text-white"
            : "bg-surface-container-highest text-on-surface-variant"
        }`}>
          <span className="text-lg font-bold">
            {name ? name.charAt(0).toUpperCase() : "C"}
          </span>
        </div>

        {/* Client Info */}
        <div className="flex-1">
          <h3 className="text-sm font-bold text-on-surface">{name}</h3>
          <p className="text-[10px] text-on-surface-variant/70 uppercase tracking-wider font-semibold">
            {activeSince || `Active Since ${new Date().getFullYear()}`}
          </p>
        </div>

        {/* Case Count */}
        <div className="text-right">
          <span className={`block text-xs font-black ${
            isActive ? "text-primary" : "text-on-surface-variant"
          }`}>
            {caseCount || 0}
          </span>
          <span className="block text-[8px] uppercase tracking-tighter text-outline">
            {caseCount === 1 ? "Case" : "Cases"}
          </span>
        </div>
      </div>
    </div>
  );
}
