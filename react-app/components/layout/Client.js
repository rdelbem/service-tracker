import React from "react";
import { IoPersonOutline } from "react-icons/io5";
import { FiEdit } from "react-icons/fi";

//possivelmente inserir lastname
export default function Client({ id, name }) {
  return (
    <div className="client">
      <div className="name-and-icon">
        <h3>
          <IoPersonOutline className="icon-client" />
          {name}
          <FiEdit className="icon-edit" />
        </h3>
      </div>
    </div>
  );
}
