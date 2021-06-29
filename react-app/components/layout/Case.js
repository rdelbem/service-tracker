import React from "react";
import { BsToggleOn, BsToggleOff } from "react-icons/bs";

export default function Case({ id, id_user, status, created_at, title }) {
  let borderStatus = {};
  if (status === "open") borderStatus = { borderLeft: "4px solid green" };
  if (status === "close") borderStatus = { borderLeft: "4px solid blue" };

  return (
    <div className="case-title" style={borderStatus}>
      <h3>
        {title}
        {status === "open" && (
          <BsToggleOn
            data-tip="This case is open! Click to close it."
            className="case-icon"
          />
        )}
        {status === "close" && (
          <BsToggleOff
            data-tip="This case is close! Click to open it."
            className="case-icon"
          />
        )}
      </h3>
    </div>
  );
}
