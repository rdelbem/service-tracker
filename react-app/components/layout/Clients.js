import React, { useContext } from "react";
import Client from "./Client";
import Search from "./Search";
import ClientsContext from "../../context/clients/clientsContext";
import Spinner from "./Spinner";

export default function Clients() {
  const clientsContext = useContext(ClientsContext);
  const { state } = clientsContext;

  return (
    <div className="clients-list-container">
      <Search />
      {state.loadingUsers && <Spinner />}
      {state.users.map((client) => (
        <Client {...client} />
      ))}
    </div>
  );
}
