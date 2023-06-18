import React, { Fragment, useContext, useState } from "react";
import { CSSTransition } from "react-transition-group";
import InViewContext from "../../context/inView/inViewContext";
import { BsFillCaretDownFill } from "react-icons/bs";

export default function HowToUse() {
  const inViewContext = useContext(InViewContext);
  const { state } = inViewContext;

  const [accordion, setAccordion] = useState(1);

  //Required for navigation purposes
  if (state.view !== "howToUse") {
    return <Fragment></Fragment>;
  }

  return (
    <Fragment>
      <div className="how-to-use-container">
        <center>
          <h3>{data.instructions_page_title}</h3>
        </center>
        <div className="accordion-title">
          <h3
            onClick={() => {
              accordion !== 1 ? setAccordion(1) : setAccordion(0);
            }}
          >
            1. {data.accordion_first_title}
            <BsFillCaretDownFill
              className="open-accordion"
              onClick={() => {
                accordion !== 1 ? setAccordion(1) : setAccordion(0);
              }}
            />
          </h3>
        </div>
        <CSSTransition
          in={accordion === 1}
          timeout={400}
          classNames="editing"
          unmountOnExit
        >
          <div className="spec-container">
            <ul>
              <li>{data.first_accordion_first_li_item}</li>
              <li>{data.first_accordion_second_li_item}</li>
              <li>{data.first_accordion_third_li_item}</li>
              <li>{data.first_accordion_forth_li_item}</li>
            </ul>
          </div>
        </CSSTransition>
        <div className="accordion-title">
          <h3
            onClick={() => {
              accordion !== 2 ? setAccordion(2) : setAccordion(0);
            }}
          >
            2.{data.accordion_second_title}
            <BsFillCaretDownFill
              className="open-accordion"
              onClick={() => {
                accordion !== 2 ? setAccordion(2) : setAccordion(0);
              }}
            />
          </h3>
        </div>
        <CSSTransition
          in={accordion === 2}
          timeout={400}
          classNames="editing"
          unmountOnExit
        >
          <div className="spec-container">
            <ul>
              <li>{data.second_accordion_firt_li_item}</li>
              <li>{data.second_accordion_second_li_item}</li>
            </ul>
          </div>
        </CSSTransition>
      </div>
    </Fragment>
  );
}
