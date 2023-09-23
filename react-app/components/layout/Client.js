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
        //false is necessery, because no case is actually in view yet
        updateIdView(id, "", "cases", name);
        getCases(id, false);
      }}
      className={
        parseInt(inViewContext.state.userId) === parseInt(id)
          ? "client-active"
          : "client"
      }
    >
      <div className="name-and-icon">
        <h3>
          <IoPersonOutline className="icon-client" />| {name}
        </h3>
      </div>
    </div>
  );
}
