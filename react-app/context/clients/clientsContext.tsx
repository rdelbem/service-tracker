import { createContext } from "react";
import type { ClientsContextType } from "../types";

const ClientsContext = createContext<ClientsContextType>({
  state: { users: [], loadingUsers: true },
  searchUsers: () => {},
  createUser: async () => ({ success: false, message: "Not implemented" }),
});

export default ClientsContext;
