import { createContext } from "react";
import type { ClientsContextType } from "../types";

const ClientsContext = createContext<ClientsContextType>({
  state: { users: [], loadingUsers: true },
  searchUsers: () => {},
});

export default ClientsContext;
