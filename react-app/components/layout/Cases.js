import React, { useContext } from "react";
import Case from "./Case";
import ReactTooltip from "react-tooltip";
import CasesContext from "../../context/cases/casesContext";
import Spinner from "../../components/layout/Spinner";

export default function Cases() {
  const casesContext = useContext(CasesContext);
  const { state, postCase, currentUserInDisplay } = casesContext;

  if (state.loadingCases) {
    return (
      <div className="cases-container">
        <Spinner />
      </div>
    );
  }

  if (
    state.cases.length === 0 &&
    !state.loadingCases &&
    !currentUserInDisplay
  ) {
    return (
      <div className="cases-container">
        <div>
          <center>
            <h3>Click on a client name, to se hers/his cases!</h3>
          </center>
        </div>
      </div>
    );
  }

  if (state.cases.length === 0 && !state.loadingCases && currentUserInDisplay) {
    return (
      <div className="cases-container">
        <button
          onClick={(e) => {
            e.preventDefault();
            postCase(currentUserInDisplay, "dindo");
          }}
          className="add-case"
        >
          Add case
        </button>
        <div>
          <center>
            <h3>No cases yet! Include a new one!</h3>
          </center>
        </div>
      </div>
    );
  }

  return (
    <div className="cases-container">
      <button
        onClick={(e) => {
          e.preventDefault();
          postCase(currentUserInDisplay, "teste");
        }}
        className="add-case"
      >
        Add case
      </button>
      {state.cases.map((item) => (
        <Case {...item} />
      ))}
      <ReactTooltip
        place="left"
        type="dark"
        effect="solid"
        data-delay-show="1000"
      />
    </div>
  );
}
