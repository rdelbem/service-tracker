import { createContext } from "react";
import type { CasesContextType } from "../types";

const CasesContext = createContext<CasesContextType>({
  state: { user: "", cases: [], loadingCases: false },
  getCases: async () => {},
  postCase: async () => {},
  toggleCase: async () => {},
  deleteCase: async () => {},
  editCase: async () => {},
  currentUserInDisplay: undefined,
  navigate: () => {},
});

export default CasesContext;
