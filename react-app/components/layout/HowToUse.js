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
          <h3>How to use this plugin</h3>
        </center>
        <div className="accordion-title">
          <h3
            onClick={() => {
              accordion !== 1 ? setAccordion(1) : setAccordion(0);
            }}
          >
            1. Display info for customers access{" "}
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
              <li>
                Create a secured page, one that is only available after login.{" "}
                <br />
                (there are some approaches in order to achieve this result, find
                one that suits you website better)
              </li>
              <li>
                Copy and paste the following short code to the restricted page,
                <b> [service-tracker-cases-progress]</b>
              </li>
              <li>
                Now, every new status registered in a case/service will be
                displayed for that respective customer.
              </li>
              <li>
                If you do not want to have a restricted customer page that is
                perfectly fine. <br /> Every new status triggers a email send
                which contains such status.
              </li>
            </ul>
          </div>
        </CSSTransition>
        <div className="accordion-title">
          <h3
            onClick={() => {
              accordion !== 2 ? setAccordion(2) : setAccordion(0);
            }}
          >
            2. Customers' notifications{" "}
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
              <li>
                Everytime a new status is registered for a case, an email is
                sent to its respective customer.
              </li>
              <li>
                This plugin uses the default wp_mail function to send its
                emails. So, it is highly recomended to use WP Mail SMTP OR other
                smtp plugin alongside Service Tracker, in order to avoid lost
                emails. (The standard wp_mail from WordPress is notorius for
                sending emails straight to spam box. However, with the third
                party smtp plugins this can be easily avoided, as wp_mail is
                overwritten by them.)
              </li>
            </ul>
          </div>
        </CSSTransition>
        <div className="accordion-title">
          <h3
            onClick={() => {
              accordion !== 3 ? setAccordion(3) : setAccordion(0);
            }}
          >
            3. Service Tracker plugin updates, support and warranty{" "}
            <BsFillCaretDownFill
              className="open-accordion"
              onClick={() => {
                accordion !== 3 ? setAccordion(3) : setAccordion(0);
              }}
            />
          </h3>
        </div>
        <CSSTransition
          in={accordion === 3}
          timeout={400}
          classNames="editing"
          unmountOnExit
        >
          <div className="spec-container">
            <ul>
              <li>
                Official support will ALWAYS be under the email of
                servicetracker@delbem.net.
              </li>
              <li>
                ANY modification of the source code of this plugin will cause
                loss of warranty, which means no refound.
              </li>
              <li>A refound order MUST be made within seven business days.</li>
              <li>A license is valid for one site only.</li>
              <li>
                Updates must be on point. If not, Service Tracker may not work
                properly, as any WordPress plugin.
              </li>
            </ul>
          </div>
        </CSSTransition>
      </div>
      <hr />
      <p>
        This plugin was coded and is maintained by Rodrigo Vieira Del Bem.{" "}
        <br /> Do you need any help? Contact me at servicetracker@delbem.net,
        or, alternatively, at rodrigo@delbem.net
      </p>
    </Fragment>
  );
}
