import React, { lazy } from "react";
//libs
import { ToastContainer } from "react-toastify";
import "react-toastify/dist/ReactToastify.css";

//contexts
const ClientsState = lazy(() => import("./context/clients/ClientsState"));
const CasesState = lazy(() => import("./context/cases/CasesState"));
const InViewState = lazy(() => import("./context/inView/InViewState"));
const ProgressState = lazy(() => import("./context/progress/ProgressState"));

//compoenents
const Wrapper = lazy(() => import("./components/layout/Wrapper"));
const Clients = lazy(() => import("./components/layout/Clients"));
const HowToUse = lazy(() => import("./components/layout/HowToUse"));
const Cases = lazy(() => import("./components/layout/Cases"));
const Progress = lazy(() => import("./components/layout/Progress"));
const CasesContainer = lazy(() => import("./components/layout/CasesContainer"));
const Initial = lazy(() => import("./components/layout/Initial"));

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
