import { ReactNode } from "react";

interface CasesContainerProps {
  children: ReactNode;
}

export default function CasesContainer({ children }: CasesContainerProps) {
  return (
    <section className="flex-1 relative h-full overflow-hidden">
      {children}
    </section>
  );
}
