import React, { useState, useContext, Fragment } from "react";
import { Tooltip } from "react-tooltip";
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
      <h3 style={{ marginTop: "0" }}>
        {data.title_progress_page} {state.caseTitle}
      </h3>
      <button
        onClick={(e) => {
          e.preventDefault();
          setWritingStatus(!writingStatus);
        }}
        className={!writingStatus ? "btn btn-save" : "btn btn-dismiss"}
      >
        {!writingStatus ? data.new_status_btn : data.close_box_btn}
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
              value={newText}
            />
            <button
              className="btn btn-save"
              onClick={(e) => {
                e.preventDefault();
                if (newText.trim() === "") {
                  alert(data.alert_blank_status_title);
                  return;
                }
                postStatus(idUser, idCase, newText.trim());
                setNewText("");
              }}
            >
              {data.add_status_btn}
            </button>
          </form>
        </div>
      </CSSTransition>

      <div className="statuses-container">
        {allStatuses.length <= 0 && <h3>{data.no_progress_yet}</h3>}
        {allStatuses.length > 0 &&
          allStatuses.map((item, index) => <Status key={index} {...item} />)}
      </div>
      <Tooltip place="left" type="dark" effect="solid" data-delay-show="1000" />
    </Fragment>
  );
}
