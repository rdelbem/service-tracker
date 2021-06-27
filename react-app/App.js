import React from "react";
import Wrapper from "./components/layout/Wrapper";
import Clients from "./components/layout/Clients";
import Cases from "./components/layout/Cases";

//App reducer
function AppReducer(state, action) {
  switch (action.type) {
    case value:
      break;

    default:
      return state;
  }
}

//App bootstrap
export default function App() {
  return (
    <Wrapper>
      <Clients />
      <Cases />
    </Wrapper>
  );
}
