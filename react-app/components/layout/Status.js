import React, { useState, useContext } from "react";
import dateformat from "dateformat";
import InViewContext from "../../context/inView/inViewContext";
import ProgressContext from "../../context/progress/progressContext";
import TextareaAutosize from "react-textarea-autosize";
import { FiEdit } from "react-icons/fi";
import { MdDeleteForever } from "react-icons/md";

export default function Status({ id, id_case, id_user, created_at, text }) {
  const inViewContext = useContext(InViewContext);
  const progressContext = useContext(ProgressContext);
  const { deleteStatus, editStatus } = progressContext;

  const [editable, setEditable] = useState(false);
  const [editedText, setEditedText] = useState(text);

  return (
    <div className="status">
      <div className="date-edit">
        <div className="date">
          <small>{dateformat(created_at, "dd/mm/yyyy, HH:MM")}</small>
        </div>
        <div className="edit">
          <FiEdit
            onClick={() => setEditable(!editable)}
            className="status-icon"
          />
        </div>
        <div className="delete">
          <MdDeleteForever
            onClick={() => deleteStatus(id, created_at)}
            className="status-icon"
          />
        </div>
      </div>
      <div className="record">
        <div className="status-text">
          <form>
            <TextareaAutosize
              onChange={(e) => setEditedText(e.target.value)}
              readOnly={!editable}
              className={
                editable ? "status-textarea" : "status-textarea remove-border"
              }
              defaultValue={text}
            />
            {editable && (
              <button
                className="btn btn-save"
                onClick={(e) => {
                  e.preventDefault();
                  editStatus(id, id_user, editedText);
                  setEditable(!editable);
                }}
              >
                Save changes
              </button>
            )}
          </form>
        </div>
      </div>
    </div>
  );
}
