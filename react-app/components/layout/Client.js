import React, { useContext } from "react";
import InViewContext from "../../context/inView/inViewContext";
import CasesContext from "../../context/cases/casesContext";
import { IoPersonOutline } from "react-icons/io5";

//TODO: inserir lastname
export default function Client({ id, name }) {
  const inViewContext = useContext(InViewContext);
  const { updateIdView } = inViewContext;
  const casesContext = useContext(CasesContext);
  const { getCases } = casesContext;

  return (
    <div
      onClick={() => {
        updateIdView(id, "cases", name);
        getCases(id, false);
      }}
      className={
        inViewContext.state.id.toString() === id.toString()
          ? "client-active"
          : "client"
      }
    >
      <div className="name-and-icon">
        <h3>
          <IoPersonOutline className="icon-client" />
          id: {id} | {name}
        </h3>
      </div>
    </div>
  );
}
