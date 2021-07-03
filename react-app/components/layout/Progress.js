import React, { useContext, Fragment } from "react";
import InViewContext from "../../context/inView/inViewContext";

export default function Progress() {
  const inViewContext = useContext(InViewContext);
  const { state } = inViewContext;

  //Required for navigation purposes
  if (state.view !== "progress") {
    return <Fragment></Fragment>;
  }

  return <div className="progress">oi</div>;
}
