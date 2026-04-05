import { useContext, useState } from "react";
import InViewContext from "../../context/inView/inViewContext";
import { InViewContextType } from "../../types";

export default function HowToUse() {
  const inViewContext = useContext(InViewContext) as InViewContextType;
  const { state } = inViewContext;

  const [accordion, setAccordion] = useState(1);

  // Required for navigation purposes
  if (state.view !== "howToUse") {
    return <></>;
  }

  return (
    <section className="flex-1 overflow-y-auto bg-background p-12 h-full">
      <div className="max-w-4xl mx-auto">
        <div className="text-center mb-12">
          <span className="material-symbols-outlined text-6xl text-primary mb-4">
            help_outline
          </span>
          <h1 className="text-3xl font-black text-on-surface tracking-tighter">
            {data.instructions_page_title || "How to Use Service Tracker"}
          </h1>
        </div>

        <div className="space-y-6">
          {/* First Accordion */}
          <div className="bg-surface-container-lowest rounded-2xl shadow-[0px_12px_32px_rgba(11,28,48,0.06)] overflow-hidden">
            <button
              onClick={() => setAccordion(accordion === 1 ? 0 : 1)}
              className="w-full flex items-center justify-between p-8 text-left"
            >
              <h3 className="text-lg font-bold text-on-surface">
                1. {data.accordion_first_title || "Getting Started"}
              </h3>
              <span
                className="material-symbols-outlined text-on-surface-variant transition-transform"
                style={{
                  transform: accordion === 1 ? "rotate(180deg)" : "rotate(0deg)",
                }}
              >
                expand_more
              </span>
            </button>
            {accordion === 1 && (
              <div className="px-8 pb-8 animate-fade-in">
                <ul className="space-y-3 text-on-surface-variant">
                  <li className="flex items-start gap-3">
                    <span className="material-symbols-outlined text-primary text-sm mt-0.5">
                      check_circle
                    </span>
                    <span>{data.first_accordion_first_li_item || "Select a client from the left sidebar"}</span>
                  </li>
                  <li className="flex items-start gap-3">
                    <span className="material-symbols-outlined text-primary text-sm mt-0.5">
                      check_circle
                    </span>
                    <span>{data.first_accordion_second_li_item || "Create or view cases for the selected client"}</span>
                  </li>
                  <li className="flex items-start gap-3">
                    <span className="material-symbols-outlined text-primary text-sm mt-0.5">
                      check_circle
                    </span>
                    <span>{data.first_accordion_third_li_item || "Track progress and add status updates"}</span>
                  </li>
                  <li className="flex items-start gap-3">
                    <span className="material-symbols-outlined text-primary text-sm mt-0.5">
                      check_circle
                    </span>
                    <span>{data.first_accordion_forth_li_item || "Manage case details and settings"}</span>
                  </li>
                </ul>
              </div>
            )}
          </div>

          {/* Second Accordion */}
          <div className="bg-surface-container-lowest rounded-2xl shadow-[0px_12px_32px_rgba(11,28,48,0.06)] overflow-hidden">
            <button
              onClick={() => setAccordion(accordion === 2 ? 0 : 2)}
              className="w-full flex items-center justify-between p-8 text-left"
            >
              <h3 className="text-lg font-bold text-on-surface">
                2. {data.accordion_second_title || "Advanced Features"}
              </h3>
              <span
                className="material-symbols-outlined text-on-surface-variant transition-transform"
                style={{
                  transform: accordion === 2 ? "rotate(180deg)" : "rotate(0deg)",
                }}
              >
                expand_more
              </span>
            </button>
            {accordion === 2 && (
              <div className="px-8 pb-8 animate-fade-in">
                <ul className="space-y-3 text-on-surface-variant">
                  <li className="flex items-start gap-3">
                    <span className="material-symbols-outlined text-primary text-sm mt-0.5">
                      check_circle
                    </span>
                    <span>{data.second_accordion_firt_li_item || "Toggle case status between open and closed"}</span>
                  </li>
                  <li className="flex items-start gap-3">
                    <span className="material-symbols-outlined text-primary text-sm mt-0.5">
                      check_circle
                    </span>
                    <span>{data.second_accordion_second_li_item || "Edit case details and status updates"}</span>
                  </li>
                </ul>
              </div>
            )}
          </div>
        </div>
      </div>
    </section>
  );
}
