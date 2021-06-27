import React from "react";
import { BsToggleOn, BsToggleOff } from "react-icons/bs";

export default function Case({ id, id_user, status, created_at, title }) {
  return (
    <div className="case-title">
      <h3>
        {title}
        {status === "open" && <BsToggleOn className="case-icon" />}
        {status === "close" && <BsToggleOff className="case-icon" />}
      </h3>
    </div>
  );
}
