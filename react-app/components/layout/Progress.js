import React, { useState, useContext, Fragment } from "react";
import { CSSTransition } from "react-transition-group";
import InViewContext from "../../context/inView/inViewContext";
import ProgressContext from "../../context/progress/progressContext";
import TextareaAutosize from "react-textarea-autosize";
import Spinner from "./Spinner";
import Status from "./Status";

export default function Progress() {
  const inViewContext = useContext(InViewContext);
  const progressContext = useContext(ProgressContext);
  const { state, postStatus } = progressContext;
  const [writingStatus, setWritingStatus] = useState(false);
  const [newText, setNewText] = useState("");

  //Required for navigation purposes
  if (inViewContext.state.view !== "progress") {
    return <Fragment></Fragment>;
  }

  if (state.loadingStatus) {
    return <Spinner />;
  }

  //case id
  const idCase = inViewContext.state.caseId;
  //user id
  const idUser = inViewContext.state.userId;

  const allStatuses = [...state.status];

  return (
    <Fragment>
      <h3 style={{ marginTop: "0" }}>Progress for case {state.caseTitle}</h3>
      <button
        onClick={(e) => {
          e.preventDefault();
          setWritingStatus(!writingStatus);
        }}
        className={!writingStatus ? "btn btn-save" : "btn btn-dismiss"}
      >
        {!writingStatus ? `New Status` : `Close Box`}
      </button>

      <CSSTransition
        in={writingStatus}
        timeout={400}
        classNames="editing"
        unmountOnExit
      >
        <div className="status-add-new-container">
          <form>
            <TextareaAutosize
              onChange={(e) => {
                setNewText(e.target.value);
              }}
              className="status-add-new-textarea"
            />
            <button
              className="btn btn-save"
              onClick={(e) => {
                e.preventDefault();
                postStatus(idUser, idCase, newText);
              }}
            >
              Add this status
            </button>
          </form>
        </div>
      </CSSTransition>

      <div className="statuses-container">
        {allStatuses.length <= 0 && (
          <h3>No progress is registered for this case.</h3>
        )}
        {allStatuses.length > 0 &&
          allStatuses.map((item) => <Status {...item} />)}
      </div>
    </Fragment>
  );
}
