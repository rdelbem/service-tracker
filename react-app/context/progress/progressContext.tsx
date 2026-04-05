import { createContext } from "react";
import type { ProgressContextType } from "../types";

const ProgressContext = createContext<ProgressContextType>({
  state: { status: [], caseTitle: "", loadingStatus: false },
  getStatus: async () => {},
  postStatus: async () => {},
  deleteStatus: async () => {},
  editStatus: async () => {},
});

export default ProgressContext;
