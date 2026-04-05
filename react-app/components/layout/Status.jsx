import React, { useState, useContext, Fragment } from "react";
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
          <small>
            {dateformat(created_at, "dd/mm/yyyy, HH:MM")} id {id}
          </small>
        </div>
        <div className="edit">
          <FiEdit
            data-tip={data.tip_edit_status}
            onClick={() => setEditable(!editable)}
            className="status-icon"
          />
        </div>
        <div className="delete">
          <MdDeleteForever
            data-tip={data.tip_delete_status}
            onClick={() => deleteStatus(id, created_at)}
            className="status-icon"
          />
        </div>
      </div>
      <div className="record">
        <div className="status-text">
          {!editable && <p>{text}</p>}
          {editable && (
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
                <Fragment>
                  <button
                    className="btn btn-save"
                    onClick={(e) => {
                      e.preventDefault();
                      editStatus(id, id_user, editedText);
                      setEditable(!editable);
                    }}
                  >
                    {data.btn_save_changes_status}
                  </button>
                  <button
                    className="btn btn-dismiss"
                    onClick={(e) => {
                      setEditable(!editable);
                    }}
                  >
                    {data.btn_dismiss_edit}
                  </button>
                </Fragment>
              )}
            </form>
          )}
        </div>
      </div>
    </div>
  );
}
