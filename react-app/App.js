import React from "react";
import Wrapper from "./components/layout/Wrapper";
import Clients from "./components/layout/Clients";
import Cases from "./components/layout/Cases";
import Progress from "./components/layout/Progress";
import ClientsState from "./context/clients/ClientsState";
import CasesState from "./context/cases/CasesState";
import InViewState from "./context/inView/InViewState";
import ProgressState from "./context/progress/progressState";
import CasesContainer from "./components/layout/CasesContainer";
import Initial from "./components/layout/Initial";
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
