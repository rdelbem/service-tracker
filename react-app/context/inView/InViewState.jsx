import React, { useReducer, useEffect } from "react";
import AppReducer from "../AppReducer";
import InViewContext from "./inViewContext";
import { IN_VIEW } from "../types";

export default function InViewState(props) {
  const initialState = {
    view: "",
    userId: "",
    caseId: "",
    name: "",
  };

  const [state, dispatch] = useReducer(AppReducer, initialState);

  const updateIdView = (userId, caseId, view, name) => {
    dispatch({
      type: IN_VIEW,
      payload: { view: view, userId: userId, caseId: caseId, name: name },
    });
  };

  useEffect(() => {
    //first render, initiate the app in a homescreen
    updateIdView(state.userId, state.caseId, "init", state.name);
  }, []);

  return (
    <InViewContext.Provider
      value={{
        state,
        updateIdView,
      }}
    >
      {props.children}
    </InViewContext.Provider>
  );
}
