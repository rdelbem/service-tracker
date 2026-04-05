import { useContext } from "react";
import InViewContext from "../../context/inView/inViewContext";
import { InViewContextType } from "../../types";

export default function Initial() {
  const inViewContext = useContext(InViewContext) as InViewContextType;
  const { state } = inViewContext;

  // Required for navigation purposes
  if (state.view !== "init") {
    return <></>;
  }

  return (
    <section className="flex-1 flex items-center justify-center bg-background h-full">
      <div className="text-center max-w-2xl p-12">
        <span className="material-symbols-outlined text-8xl text-primary mb-6">
          dashboard
        </span>
        <h1 className="text-4xl font-black text-on-surface tracking-tighter mb-4">
          Welcome to Service Tracker
        </h1>
        <p className="text-on-surface-variant text-lg leading-relaxed">
          {data.home_screen || "Select a client from the sidebar to get started"}
        </p>
      </div>
    </section>
  );
}
