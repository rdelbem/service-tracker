import React, { useState } from "react";
import dateformat from "dateformat";
import TextareaAutosize from "react-textarea-autosize";
import { FiEdit } from "react-icons/fi";

export default function Status({ text, created_at }) {
  const [editable, setEditable] = useState(true);

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
      </div>
      <div className="record">
        <div className="status-text">
          <TextareaAutosize
            readOnly={editable}
            className={
              editable ? "status-textarea remove-border" : "status-textarea"
            }
            defaultValue={text}
          />
        </div>
      </div>
    </div>
  );
}
