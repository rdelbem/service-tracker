import React, { useContext, useState } from "react";
import Case from "./Case";
import ReactTooltip from "react-tooltip";
import CasesContext from "../../context/cases/casesContext";
import Spinner from "../../components/layout/Spinner";

export default function Cases() {
  const casesContext = useContext(CasesContext);
  const { state, postCase, currentUserInDisplay } = casesContext;

  const [caseTitle, setCaseTitle] = useState("");

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
        <form>
          <input
            className="case-input"
            placeholder="Case name"
            onChange={(e) => {
              let title = e.target.value;
              setCaseTitle(title);
            }}
            type="text"
            value={caseTitle}
          />
          <button
            onClick={(e) => {
              e.preventDefault();
              caseTitle !== "" && postCase(currentUserInDisplay, caseTitle);
              setCaseTitle("");
            }}
            className="add-case"
          >
            Add case
          </button>
        </form>
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
      <form>
        <input
          className="case-input"
          placeholder="Case name"
          onChange={(e) => {
            let title = e.target.value;
            setCaseTitle(title);
          }}
          type="text"
          value={caseTitle}
        />
        <button
          onClick={(e) => {
            e.preventDefault();
            caseTitle !== "" && postCase(currentUserInDisplay, caseTitle);
            setCaseTitle("");
          }}
          className="add-case"
        >
          Add case
        </button>
      </form>
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
