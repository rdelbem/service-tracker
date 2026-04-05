
//libs
import { lazy } from "react";
import { ToastContainer } from "react-toastify";
import "react-toastify/dist/ReactToastify.css";

//contexts
import ClientsState from "./context/clients/ClientsState";
import CasesState from "./context/cases/CasesState";
import InViewState from "./context/inView/InViewState";
import ProgressState from "./context/progress/ProgressState";

//components
import Wrapper from "./components/layout/Wrapper";
import Clients from "./components/layout/Clients";
import CasesContainer from "./components/layout/CasesContainer";
import LazyView from "./components/layout/LazyView";

// Lazy-loaded view components (code-split into separate chunks)
const Initial = lazy(() => import("./components/layout/Initial"));
const HowToUse = lazy(() => import("./components/layout/HowToUse"));
const Cases = lazy(() => import("./components/layout/Cases"));
const Progress = lazy(() => import("./components/layout/Progress"));

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
                <LazyView><Initial /></LazyView>
                <LazyView><HowToUse /></LazyView>
                <LazyView><Cases /></LazyView>
                <LazyView><Progress /></LazyView>
              </CasesContainer>
            </Wrapper>
          </ProgressState>
        </CasesState>
      </ClientsState>
    </InViewState>
  );
}
