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
          <h3>Click on a client name, to se hers/his cases!</h3>
        </center>
      </div>
    </Fragment>
  );
}
