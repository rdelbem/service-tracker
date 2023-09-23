import React, { useContext } from "react";
import Client from "./Client";
import Search from "./Search";
import TopIcons from "./TopIcons";
import ClientsContext from "../../context/clients/clientsContext";
import Spinner from "./Spinner";

export default function Clients() {
  const clientsContext = useContext(ClientsContext);
  const { state } = clientsContext;
  const clientsArr = [...state.users];

  return (
    <div className="clients-list-container">
      <Search />
      <TopIcons />

      {state.loadingUsers && <Spinner />}
      {clientsArr.map((client) => (
        <Client {...client} />
      ))}
    </div>
  );
}
