import { useState } from "react";
import { useInViewStore } from "../../stores/inViewStore";
import { stolmc_text, Text } from "../../i18n";

export default function HowToUse() {
  const inViewState = useInViewStore((state) => state);

  const [accordion, setAccordion] = useState(1);

  // Required for navigation purposes
  if (inViewState.view !== "howToUse") {
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
            {stolmc_text(Text.InstructionsPageTitle)}
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
                1. {stolmc_text(Text.AccordionFirstTitle)}
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
                    <span>{stolmc_text(Text.FirstAccordionFirstLiItem)}</span>
                  </li>
                  <li className="flex items-start gap-3">
                    <span className="material-symbols-outlined text-primary text-sm mt-0.5">
                      check_circle
                    </span>
                    <span>{stolmc_text(Text.FirstAccordionSecondLiItem)}</span>
                  </li>
                  <li className="flex items-start gap-3">
                    <span className="material-symbols-outlined text-primary text-sm mt-0.5">
                      check_circle
                    </span>
                    <span>{stolmc_text(Text.FirstAccordionThirdLiItem)}</span>
                  </li>
                  <li className="flex items-start gap-3">
                    <span className="material-symbols-outlined text-primary text-sm mt-0.5">
                      check_circle
                    </span>
                    <span>{stolmc_text(Text.FirstAccordionForthLiItem)}</span>
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
                2. {stolmc_text(Text.AccordionSecondTitle)}
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
                    <span>{stolmc_text(Text.SecondAccordionFirtLiItem)}</span>
                  </li>
                  <li className="flex items-start gap-3">
                    <span className="material-symbols-outlined text-primary text-sm mt-0.5">
                      check_circle
                    </span>
                    <span>{stolmc_text(Text.SecondAccordionSecondLiItem)}</span>
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
