import { create } from "zustand";
import { get as fetchGet, post } from "../utils/fetch";
import type { User } from "../types";

declare const data: Record<string, any>;

export interface ClientsState {
  users: User[];
  loadingUsers: boolean;
}

export interface ClientsActions {
  getUsers: () => Promise<void>;
  searchUsers: (query: string) => void;
  createUser: (userData: { name: string; email: string; phone?: string; cellphone?: string }) => Promise<{ success: boolean; message: string; user?: User }>;
}

export interface ClientsStore extends ClientsState, ClientsActions {}

export const useClientsStore = create<ClientsStore>((set, get) => {
  const api_url_users = data.users_api_url;
  const create_user_api_url = data.create_user_api_url;

  return {
    users: [],
    loadingUsers: true,
    getUsers: async () => {
      try {
        const res = await fetchGet(api_url_users, {
          headers: { "X-WP-Nonce": data.nonce },
        });
        set({ users: res.data, loadingUsers: false });
      } catch (error) {
        console.error("Error fetching users:", error);
        set({ loadingUsers: false });
      }
    },
    searchUsers: (query: string) => {
      const specialChar = /[-!$%^&*()_+|~=`{}\[\]:";'<>?,.\/]/;
      const specialCharRegEx = new RegExp(specialChar, "g");
      if (specialCharRegEx.test(query)) return;

      if (query === "") {
        get().getUsers();
        return;
      }

      const usersInState = get().users;
      const regex = new RegExp(query, "gi");
      const foundUsers: User[] = [];

      usersInState.forEach((user: User) => {
        if (regex.test(user.name)) {
          foundUsers.push(user);
        }
      });

      if (foundUsers.length > 0) {
        set({ users: foundUsers, loadingUsers: false });
      }
    },
    createUser: async (userData) => {
      try {
        const res = await post(create_user_api_url, userData, {
          headers: { "X-WP-Nonce": data.nonce },
        });

        if (res.data.success) {
          await get().getUsers();
        }

        return res.data;
      } catch (error) {
        console.error("Error creating user:", error);
        return { success: false, message: "Failed to create user. Please try again." };
      }
    },
  };
});

// Auto-fetch users on store creation
useClientsStore.getState().getUsers();
