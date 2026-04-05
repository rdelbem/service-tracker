import React from "react";

export default function Spinner() {
  return (
    <div className="spinner-container">
      <div className="lds-ellipsis">
        <div></div>
        <div></div>
        <div></div>
        <div></div>
      </div>
    </div>
  );
}
