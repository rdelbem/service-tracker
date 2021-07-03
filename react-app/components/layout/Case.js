import React, { useContext, useState, Fragment } from "react";
import { CSSTransition } from "react-transition-group";
import CasesContext from "../../context/cases/casesContext";
import InViewContext from "../../context/inView/inViewContext";
import ProgressContext from "../../context/progress/progressContext";
import dateformat from "dateformat";
import { BsToggleOn, BsToggleOff } from "react-icons/bs";
import { FiEdit } from "react-icons/fi";
import { MdDeleteForever } from "react-icons/md";

export default function Case({ id, id_user, status, created_at, title }) {
  const casesContext = useContext(CasesContext);
  const { deleteCase, toggleCase, editCase } = casesContext;

  const inViewContext = useContext(InViewContext);
  const { updateIdView } = inViewContext;

  const progressContext = useContext(ProgressContext);
  const { getStatus } = progressContext;

  const [editing, setEditing] = useState(false);
  const [newTitle, setNewTitle] = useState("");

  let borderStatus = {};
  if (status === "open") borderStatus = { borderLeft: "4px solid green" };
  if (status === "close") borderStatus = { borderLeft: "4px solid blue" };

  return (
    <Fragment>
      <div className="case-title" style={borderStatus}>
        <small>{dateformat(created_at, "dd/mm/yyyy, HH:MM")}</small>
        <h3>
          <span
            className="the-title"
            onClick={() => {
              updateIdView(inViewContext.state.id, "progress");
              getStatus(id, false);
            }}
          >
            {title}
          </span>

          <MdDeleteForever
            onClick={() => deleteCase(id, title)}
            data-tip="Delete this case"
            className="case-icon"
          />

          {status === "open" && (
            <BsToggleOn
              onClick={() => toggleCase(id)}
              data-tip="This case is open! Click to close it."
              className="case-icon"
            />
          )}
          {status === "close" && (
            <BsToggleOff
              onClick={() => toggleCase(id)}
              data-tip="This case is close! Click to open it."
              className="case-icon"
            />
          )}

          <FiEdit
            onClick={() => setEditing(!editing)}
            data-tip="Edit the name of this case"
            className="case-icon"
          />
        </h3>
      </div>
      <CSSTransition
        in={editing}
        timeout={400}
        classNames="editing"
        unmountOnExit
      >
        <div className="editing-title">
          <form>
            <input
              onChange={(e) => {
                let theNewTitle = e.target.value;
                setNewTitle(theNewTitle);
              }}
              className="edit-input"
              type="text"
            />
            <button
              onClick={(e) => {
                e.preventDefault();
                editCase(id, id_user, newTitle);
              }}
              className="btn btn-save"
            >
              Save
            </button>
            <button
              onClick={(e) => {
                e.preventDefault();
                setEditing(!editing);
              }}
              className="btn btn-dismiss"
            >
              Dismiss
            </button>
          </form>
        </div>
      </CSSTransition>
    </Fragment>
  );
}
