import { useInViewStore } from "../../stores/inViewStore";
import { stolmc_text, Text } from "../../i18n";

export default function Initial() {
  const inViewState = useInViewStore((state) => state);

  // Required for navigation purposes
  if (inViewState.view !== "init") {
    return <></>;
  }

  return (
    <section className="flex-1 flex items-center justify-center bg-background h-full">
      <div className="text-center max-w-2xl p-12">
        <span className="material-symbols-outlined text-8xl text-primary mb-6">
          dashboard
        </span>
        <h1 className="text-4xl font-black text-on-surface tracking-tighter mb-4">
          {stolmc_text(Text.BrandName)}
        </h1>
        <p className="text-on-surface-variant text-lg leading-relaxed">
          {stolmc_text(Text.HomeScreen)}
        </p>
      </div>
    </section>
  );
}
