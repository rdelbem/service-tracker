import { useReducer, useEffect, ReactNode } from "react";
import AppReducer from "../AppReducer";
import InViewContext from "../../context/inView/inViewContext";
import type { InViewState as InViewStateType } from "../types";

interface InViewStateProps {
  children: ReactNode;
}

export default function InViewState({ children }: InViewStateProps) {
  const initialState: InViewStateType = {
    view: "init",
    userId: "",
    caseId: "",
    name: "",
  };

  const [state, dispatch] = useReducer(AppReducer, initialState);

  const updateIdView = (userId: string | number, caseId: string | number, view: string, name: string) => {
    dispatch({
      type: "IN_VIEW",
      payload: { view, userId, caseId, name },
    });
  };

  useEffect(() => {
    const savedView = localStorage.getItem("view");
    const savedUserId = localStorage.getItem("userId");
    const savedCaseId = localStorage.getItem("caseId");
    const savedName = localStorage.getItem("name");

    if (savedView && savedUserId && savedCaseId && savedName) {
      updateIdView(savedUserId, savedCaseId, savedView, savedName);
    }
  }, []);

  useEffect(() => {
    localStorage.setItem("view", state.view);
    localStorage.setItem("userId", String(state.userId));
    localStorage.setItem("caseId", String(state.caseId));
    localStorage.setItem("name", state.name);
  }, [state]);

  return (
    <InViewContext.Provider
      value={{
        state,
        updateIdView,
      }}
    >
      {children}
    </InViewContext.Provider>
  );
}
