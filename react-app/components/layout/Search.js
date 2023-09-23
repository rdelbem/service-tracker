import React, { useContext } from "react";
import ClientsContext from "../../context/clients/clientsContext";

export default function Search() {
  const clientsContext = useContext(ClientsContext);
  const { searchUsers } = clientsContext;

  return (
    <div className="search">
      <input
        onChange={(e) => {
          searchUsers(e.target.value);
        }}
        type="text"
        placeholder={data.search_bar}
      />
    </div>
  );
}
