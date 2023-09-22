import React, { Fragment, useContext } from "react";
import InViewContext from "../../context/inView/inViewContext";

export default function Initial() {
  const inViewContext = useContext(InViewContext);
  const { state } = inViewContext;

  //Required for navigation purposes
  if (state.view !== "init") {
    return <Fragment></Fragment>;
  }

  return (
    <Fragment>
      <div>
        <center>
          <h3>{data.home_screen}</h3>
        </center>
      </div>
    </Fragment>
  );
}
