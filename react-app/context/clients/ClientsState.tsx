import { useReducer, useEffect, ReactNode } from "react";
import AppReducer from "../AppReducer";
import { get, post } from "../../utils/fetch";
import ClientsContext from "./clientsContext";
import { GET_USERS } from "../types";
import type { ClientsState as ClientsStateType, User } from "../types";

interface ClientsStateProps {
  children: ReactNode;
}

export default function ClientsState({ children }: ClientsStateProps) {
  const api_url_users = data.users_api_url;
  const create_user_api_url = data.create_user_api_url;

  const initialState: ClientsStateType = {
    users: [],
    loadingUsers: true,
  };

  const [state, dispatch] = useReducer(AppReducer, initialState);

  const searchUsers = (query: string) => {
    //escape special characters
    const specialChar = /[-!$%^&*()_+|~=`{}\[\]:";'<>?,.\/]/;
    const specialCharRegEx = new RegExp(specialChar, "g");
    if (specialCharRegEx.test(query)) return;

    if (query === "") getUsers();

    const usersInState = state.users;
    const regex = new RegExp(query, "gi");
    const foundUsers: User[] = [];

    usersInState.forEach((user: User) => {
      if (regex.test(user.name) && query !== "") {
        foundUsers.push(user);
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
    const res = await get(api_url_users, {
      headers: {
        "X-WP-Nonce": data.nonce,
      },
    });

    dispatch({
      type: GET_USERS,
      payload: { users: res.data.data, loadingUsers: false },
    });
  };

  const createUser = async (userData: { name: string; email: string; phone?: string; cellphone?: string }) => {
    try {
      const res = await post(
        create_user_api_url,
        userData,
        {
          headers: {
            "X-WP-Nonce": data.nonce,
          },
        }
      );

      // If successful, refresh the users list
      if (res.data.success) {
        await getUsers();
      }

      return res.data;
    } catch (error) {
      console.error("Error creating user:", error);
      return {
        success: false,
        message: "Failed to create user. Please try again.",
      };
    }
  };

  useEffect(() => {
    getUsers();
  }, []);

  return (
    <ClientsContext.Provider
      value={{
        state,
        searchUsers,
        createUser,
      }}
    >
      {children}
    </ClientsContext.Provider>
  );
}
