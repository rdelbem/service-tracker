import { useContext } from "react";
import InViewContext from "../../context/inView/inViewContext";

export default function Sidebar() {
  const inViewContext = useContext(InViewContext);
  const { state, updateIdView } = inViewContext;

  const navItems = [
    { icon: "dashboard", label: "Dashboard", view: "init" },
    { icon: "group", label: "Clients", view: "init" },
    { icon: "folder_open", label: "Cases", view: "cases" },
    { icon: "event", label: "Calendar", view: "init" },
    { icon: "analytics", label: "Analytics", view: "init" },
  ];

  const bottomNavItems = [
    { icon: "settings", label: "Settings" },
    { icon: "help_outline", label: "Support" },
  ];

  return (
    <aside className="flex-shrink-0 w-64 h-[calc(100vh-32px)] flex flex-col py-8 px-4 bg-slate-50/80 backdrop-blur-xl shadow-[12px_0_32px_rgba(11,28,48,0.06)] z-40">
      {/* Brand Header */}
      <div className="mb-10 px-4">
        <h1 className="text-xl font-black text-slate-900 tracking-tighter uppercase">
          Service Tracker
        </h1>
        <p className="font-['Manrope'] font-semibold tracking-tight text-xs text-slate-500 uppercase mt-1">
          Administrator
        </p>
      </div>

      {/* Main Navigation */}
      <nav className="flex-1 space-y-1">
        {navItems.map((item) => {
          const isActive = state.view === item.view;
          return (
            <a
              key={item.label}
              onClick={() => item.view !== "init" && updateIdView("", "", item.view, "")}
              className={`flex items-center gap-3 px-4 py-3 rounded-xl transition-all cursor-pointer ${
                isActive
                  ? "text-slate-900 font-bold bg-slate-200/50"
                  : "text-slate-500 hover:bg-slate-200/50 font-medium"
              }`}
            >
              <span
                className="material-symbols-outlined"
                style={{ fontVariationSettings: isActive ? "'FILL' 1" : "'FILL' 0" }}
              >
                {item.icon}
              </span>
              <span className="font-['Manrope'] font-semibold tracking-tight">
                {item.label}
              </span>
            </a>
          );
        })}
      </nav>

      {/* Bottom Navigation & User Profile */}
      <div className="mt-auto border-t border-slate-200/50 pt-6 space-y-1">
        {bottomNavItems.map((item) => (
          <a
            key={item.label}
            className="flex items-center gap-3 px-4 py-3 rounded-xl transition-colors text-slate-500 hover:bg-slate-200/50 font-medium cursor-pointer"
          >
            <span className="material-symbols-outlined">{item.icon}</span>
            <span className="font-['Manrope'] font-semibold tracking-tight">
              {item.label}
            </span>
          </a>
        ))}

        {/* User Profile */}
        <div className="flex items-center gap-3 px-4 py-4 mt-4">
          <div className="w-10 h-10 rounded-full bg-primary flex items-center justify-center text-white font-bold">
            {state.name ? state.name.charAt(0).toUpperCase() : "A"}
          </div>
          <div className="overflow-hidden">
            <p className="text-sm font-bold text-slate-900 truncate">
              {state.name || "Admin User"}
            </p>
            <p className="text-[10px] text-slate-500 uppercase tracking-widest">
              Master Admin
            </p>
          </div>
        </div>
      </div>
    </aside>
  );
}
