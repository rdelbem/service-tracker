import React, { useContext } from "react";
import InViewContext from "../../context/inView/inViewContext";
import { IoPersonOutline } from "react-icons/io5";
import { FiEdit } from "react-icons/fi";
import CasesContext from "../../context/cases/casesContext";

//TODO: inserir lastname
export default function Client({ id, name }) {
  const inViewContext = useContext(InViewContext);
  const { updateId } = inViewContext;
  const casesContext = useContext(CasesContext);
  const { getCases } = casesContext;

  return (
    <div
      onClick={() => {
        updateId(id);
        getCases(id, false);
      }}
      className="client"
    >
      <div className="name-and-icon">
        <h3>
          <IoPersonOutline className="icon-client" />
          id: {id} | {name}
          <FiEdit className="icon-edit" />
        </h3>
      </div>
    </div>
  );
}
