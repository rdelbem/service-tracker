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

  const searchUsers = (query) => {
    //escape special characters
    const specialChar = /[-!$%^&*()_+|~=`{}\[\]:";'<>?,.\/]/;
    const specialCharRegEx = new RegExp(specialChar, "g");
    if (specialCharRegEx.test(query)) return;

    if (query === "") getUsers();

    const usersInState = state.users;
    const regex = new RegExp(query, "gi");
    let foundUsers = [];

    usersInState.forEach((user, index) => {
      if (regex.test(user.name) && query !== "") {
        foundUsers.push(usersInState[index]);
      }
    });

    if (foundUsers.length > 0) {
      dispatch({
        type: GET_USERS,
        payload: { users: foundUsers, loadingUsers: false },
      });
    }
  };

  const getUsers = async () => {
    const res = await axios.get(api_url_users, {
      headers: {
        "X-WP-Nonce": data.nonce,
      },
    });

    dispatch({
      type: GET_USERS,
      payload: { users: res.data, loadingUsers: false },
    });
  };

  useEffect(() => {
    getUsers();
  }, []);

  return (
    <ClientsContext.Provider
      value={{
        state,
        searchUsers,
      }}
    >
      {props.children}
    </ClientsContext.Provider>
  );
}
