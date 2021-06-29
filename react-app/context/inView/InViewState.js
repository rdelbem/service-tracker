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

  const updateId = (id) => {
    dispatch({ type: IN_VIEW, payload: { view: state.view, id: id } });
  };

  return (
    <InViewContext.Provider
      value={{
        state,
        updateId,
      }}
    >
      {props.children}
    </InViewContext.Provider>
  );
}
