import React, { useReducer, useEffect } from "react";
import AppReducer from "../AppReducer";
import axios from "axios";
import ClientsContext from "./clientsContext";
import { GET_USERS } from "../types";

export default function ClientsState(props) {
  const api_url_users = `${data.root_url}/wp-json/wp/v2/users?roles=client`;

  const initialState = {
    users: [],
    loadingUsers: true,
  };

  const [state, dispatch] = useReducer(AppReducer, initialState);

  //search users
  const searchUsers = async (text) => {
    //initial search, before any typed search
    if (typeof text === "undefined") {
      const res = await axios.get(api_url_users, {
        headers: {
          "X-WP-Nonce": data.nonce,
        },
      });

      dispatch({
        type: GET_USERS,
        payload: res.data,
      });
    } else {
      //TODO: search by user input
    }
  };

  useEffect(() => {
    searchUsers();
  }, []);

  return (
    <ClientsContext.Provider
      value={{
        state,
      }}
    >
      {props.children}
    </ClientsContext.Provider>
  );
}
