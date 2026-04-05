import { useContext } from "react";
import Client from "./Client";
import Search from "./Search";
import ClientsContext from "../../context/clients/clientsContext";
import Spinner from "./Spinner";
import { ClientsContextType, User } from "../../types";

export default function Clients() {
  const clientsContext = useContext(ClientsContext) as ClientsContextType;
  const { state } = clientsContext;
  const clientsArr = [...state.users];

  return (
    <section className="flex-shrink-0 w-[380px] bg-surface-container-low flex flex-col border-r border-outline-variant/10 h-full">
      {/* Header with Search */}
      <div className="p-8">
        <h2 className="text-2xl font-black text-on-surface tracking-tighter mb-6">
          Clients
        </h2>
        <Search />
      </div>

      {/* Client List */}
      <div className="flex-1 overflow-y-auto px-4 space-y-3 pb-8">
        {state.loadingUsers && <Spinner />}
        {clientsArr.map((client: User) => (
          <Client key={client.id} {...client} caseCount={0} activeSince={`Active Since ${new Date().getFullYear()}`} />
        ))}
      </div>
    </section>
  );
}
