import React from "react";

export default function CasesContainer(props) {
  return (
    <section className="flex-1 relative h-full overflow-hidden">
      {props.children}
    </section>
  );
}
