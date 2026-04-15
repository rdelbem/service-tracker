import { create } from "zustand";
import { get as fetchGet, post } from "../utils/fetch";
import type { User } from "../types";

declare const data: Record<string, any>;

export interface ClientsState {
  users: User[];
  loadingUsers: boolean;
  page: number;
  perPage: number;
  total: number;
  totalPages: number;
}

export interface ClientsActions {
  getUsers: (page?: number) => Promise<void>;
  searchUsers: (query: string) => void;
  setPage: (page: number) => Promise<void>;
  createUser: (userData: { name: string; email: string; phone?: string; cellphone?: string }) => Promise<{ success: boolean; message: string; user?: User }>;
}

export interface ClientsStore extends ClientsState, ClientsActions {}

export const useClientsStore = create<ClientsStore>((set, get) => {
  const api_url_users = data.users_api_url;
  const create_user_api_url = data.create_user_api_url;

  return {
    users: [],
    loadingUsers: true,
    page: 1,
    perPage: 6,
    total: 0,
    totalPages: 1,

    getUsers: async (page?: number) => {
      const currentPage = page ?? get().page;
      const perPage = get().perPage;

      try {
        set({ loadingUsers: true });

        const url = `${api_url_users}?page=${currentPage}&per_page=${perPage}`;
        const res = await fetchGet(url, {
          headers: { "X-WP-Nonce": data.nonce },
        });

        // API returns a paginated envelope: { data, total, page, per_page, total_pages }
        const envelope = res.data;

        set({
          users: envelope.data ?? [],
          total: envelope.total ?? 0,
          page: envelope.page ?? currentPage,
          perPage: envelope.per_page ?? perPage,
          totalPages: envelope.total_pages ?? 1,
          loadingUsers: false,
        });
      } catch (error) {
        console.error("Error fetching users:", error);
        set({ loadingUsers: false });
      }
    },

    setPage: async (page: number) => {
      const { totalPages } = get();
      const clamped = Math.max(1, Math.min(page, totalPages));
      set({ page: clamped });
      await get().getUsers(clamped);
    },

    searchUsers: (query: string) => {
      const specialChar = /[-!$%^&*()_+|~=`{}\[\]:";'<>?,.\/]/;
      const specialCharRegEx = new RegExp(specialChar, "g");
      if (specialCharRegEx.test(query)) return;

      if (query === "") {
        get().getUsers(1);
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
          // Refresh current page so the new user appears.
          await get().getUsers(get().page);
        }

        return res.data;
      } catch (error) {
        console.error("Error creating user:", error);
        return { success: false, message: "Failed to create user. Please try again." };
      }
    },
  };
});

// Auto-fetch users on store creation (page 1).
useClientsStore.getState().getUsers(1);
