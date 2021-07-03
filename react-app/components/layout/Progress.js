import React, { useContext, Fragment } from "react";
import InViewContext from "../../context/inView/inViewContext";
import ProgressContext from "../../context/progress/progressContext";
import Spinner from "./Spinner";

export default function Progress() {
  const inViewContext = useContext(InViewContext);
  const progressContext = useContext(ProgressContext);
  const { state } = progressContext;

  //Required for navigation purposes
  if (inViewContext.state.view !== "progress") {
    return <Fragment></Fragment>;
  }

  if (state.loadingStatus) {
    return <Spinner />;
  }

  return <div className="progress">oi</div>;
}
