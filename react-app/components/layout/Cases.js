import React, { useContext, useState, Fragment } from "react";
import Case from "./Case";
import ReactTooltip from "react-tooltip";
import CasesContext from "../../context/cases/casesContext";
import InViewContext from "../../context/inView/inViewContext";
import Spinner from "../../components/layout/Spinner";

export default function Cases() {
  const inViewContext = useContext(InViewContext);

  const casesContext = useContext(CasesContext);
  const { state, postCase, currentUserInDisplay } = casesContext;

  const [caseTitle, setCaseTitle] = useState("");

  //Required for navigation purposes
  if (inViewContext.state.view !== "cases") {
    return <Fragment></Fragment>;
  }

  if (state.loadingCases) {
    return <Spinner />;
  }

  if (state.cases.length === 0 && !state.loadingCases && currentUserInDisplay) {
    return (
      <Fragment>
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
      </Fragment>
    );
  }

  return (
    <Fragment>
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
    </Fragment>
  );
}
