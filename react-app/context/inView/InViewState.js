import React, { useReducer, useEffect } from "react";
import AppReducer from "../AppReducer";
import InViewContext from "./inViewContext";
import { IN_VIEW } from "../types";

export default function InViewState(props) {
  const initialState = {
    view: "",
    id: "",
  };

  const [state, dispatch] = useReducer(AppReducer, initialState);

  const updateIdView = (id, view) => {
    dispatch({ type: IN_VIEW, payload: { view: view, id: id } });
  };

  useEffect(() => {
    updateIdView(state.id, "init");
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
