import React from "react";
import Wrapper from "./components/layout/Wrapper";
import Clients from "./components/layout/Clients";
import Cases from "./components/layout/Cases";
import ClientsState from "./context/clients/ClientsState";
import CasesState from "./context/cases/CasesState";
import InViewState from "./context/inView/InViewState";

//App bootstrap
export default function App() {
  return (
    <InViewState>
      <ClientsState>
        <CasesState>
          <Wrapper>
            <Clients />
            <Cases />
          </Wrapper>
        </CasesState>
      </ClientsState>
    </InViewState>
  );
}
