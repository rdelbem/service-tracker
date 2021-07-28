import React from "react";
//components
import Wrapper from "./components/layout/Wrapper";
import Clients from "./components/layout/Clients";
import Cases from "./components/layout/Cases";
import Progress from "./components/layout/Progress";
import CasesContainer from "./components/layout/CasesContainer";
import Initial from "./components/layout/Initial";
import HowToUse from "./components/layout/HowToUse";
//contexts
import ClientsState from "./context/clients/ClientsState";
import CasesState from "./context/cases/CasesState";
import InViewState from "./context/inView/InViewState";
import ProgressState from "./context/progress/progressState";
//libs
import { ToastContainer } from "react-toastify";

//App bootstrap
export default function App() {
  return (
    <InViewState>
      <ClientsState>
        <CasesState>
          <ProgressState>
            <ToastContainer />
            <Wrapper>
              <Clients />
              <CasesContainer>
                <Initial />
                <HowToUse />
                <Cases />
                <Progress />
              </CasesContainer>
            </Wrapper>
          </ProgressState>
        </CasesState>
      </ClientsState>
    </InViewState>
  );
}
