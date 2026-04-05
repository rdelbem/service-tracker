import { createContext } from "react";
import type { InViewContextType } from "../types";

const InViewContext = createContext<InViewContextType>({
  state: { view: "", userId: "", caseId: "", name: "" },
  updateIdView: () => {},
});

export default InViewContext;
