import { ReactNode } from "react";
import { useHashRouter } from "../../hooks/useHashRouter";
import InViewContext from "../../context/inView/inViewContext";
import type { InViewState as InViewStateType } from "../types";

interface InViewStateProps {
  children: ReactNode;
}

export default function InViewState({ children }: InViewStateProps) {
  const { route, navigate } = useHashRouter();

  // Legacy updateIdView function for backward compatibility
  const updateIdView = (userId: string | number, caseId: string | number, view: string, name: string) => {
    navigate(view, userId, caseId, name);
  };

  return (
    <InViewContext.Provider
      value={{
        state: route as InViewStateType,
        updateIdView,
        navigate,
      }}
    >
      {children}
    </InViewContext.Provider>
  );
}
