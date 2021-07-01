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

  //get users
  const getUsers = async () => {
    const res = await axios.get(api_url_users, {
      headers: {
        "X-WP-Nonce": data.nonce,
      },
    });

    dispatch({
      type: GET_USERS,
      payload: res.data,
    });
  };

  useEffect(() => {
    getUsers();
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
