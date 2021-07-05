import React, { useContext, Fragment } from "react";
import InViewContext from "../../context/inView/inViewContext";
import ProgressContext from "../../context/progress/progressContext";
import Spinner from "./Spinner";
import Status from "./Status";

export default function Progress() {
  const inViewContext = useContext(InViewContext);
  const progressContext = useContext(ProgressContext);
  const { state } = progressContext;

  //necessary in order to show last entry first
  const reversedStatusesArr = [...state.status.reverse()];

  //Required for navigation purposes
  if (inViewContext.state.view !== "progress") {
    return <Fragment></Fragment>;
  }

  if (state.loadingStatus) {
    return <Spinner />;
  }

  return (
    <Fragment>
      <h3 style={{ marginTop: "0" }}>Progress for case {state.caseTitle}</h3>
      {reversedStatusesArr.map((item) => (
        <Status {...item} />
      ))}
    </Fragment>
  );
}
