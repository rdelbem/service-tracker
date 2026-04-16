import { create } from "zustand";
import { get as fetchGet, post, put } from "../utils/fetch";
import type { User } from "../types";

declare const data: Record<string, any>;

export interface ClientsState {
  users: User[];
  loadingUsers: boolean;
  page: number;
  perPage: number;
  total: number;
  totalPages: number;
  searchQuery: string;
  loading: boolean;
}

export interface ClientsActions {
  getUsers: (page?: number) => Promise<void>;
  searchUsers: (query: string) => Promise<void>;
  setPage: (page: number) => Promise<void>;
  createUser: (userData: { name: string; email: string; phone?: string; cellphone?: string }) => Promise<{ success: boolean; message: string; user?: User }>;
  updateUser: (id: string | number, userData: Partial<User>) => Promise<void>;
}

export interface ClientsStore extends ClientsState, ClientsActions {}

export const useClientsStore = create<ClientsStore>((set, get) => {
  const api_url_users = data.users_api_url;
  const create_user_api_url = data.create_user_api_url;
  const search_url = `${data.root_url}/wp-json/service-tracker-stolmc/v1/users/search`;
  const update_user_api_url = `${data.root_url}/wp-json/service-tracker-stolmc/v1/users`;

  return {
    users: [],
    loadingUsers: true,
    page: 1,
    perPage: 6,
    total: 0,
    totalPages: 1,
    searchQuery: "",
    loading: false,

    getUsers: async (page?: number) => {
      const currentPage = page ?? get().page;
      const perPage = get().perPage;

      try {
        set({ loadingUsers: true, searchQuery: "" });

        const url = `${api_url_users}?page=${currentPage}&per_page=${perPage}`;
        const res = await fetchGet(url, {
          headers: { "X-WP-Nonce": data.nonce },
        });

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

    searchUsers: async (query: string) => {
      // Clear search — restore the normal paginated list.
      if (query.trim() === "") {
        await get().getUsers(1);
        return;
      }

      const perPage = get().perPage;

      try {
        set({ loadingUsers: true, searchQuery: query, page: 1 });

        const url = `${search_url}?q=${encodeURIComponent(query.trim())}&page=1&per_page=${perPage}`;
        const res = await fetchGet(url, {
          headers: { "X-WP-Nonce": data.nonce },
        });

        const envelope = res.data;

        set({
          users: envelope.data ?? [],
          total: envelope.total ?? 0,
          page: envelope.page ?? 1,
          perPage: envelope.per_page ?? perPage,
          totalPages: envelope.total_pages ?? 1,
          loadingUsers: false,
        });
      } catch (error) {
        console.error("Error searching users:", error);
        set({ loadingUsers: false });
      }
    },

    setPage: async (page: number) => {
      const { totalPages, searchQuery, perPage } = get();
      const clamped = Math.max(1, Math.min(page, totalPages));
      set({ page: clamped });

      // If we are in search mode, paginate through search results.
      if (searchQuery.trim() !== "") {
        try {
          set({ loadingUsers: true });

          const url = `${search_url}?q=${encodeURIComponent(searchQuery.trim())}&page=${clamped}&per_page=${perPage}`;
          const res = await fetchGet(url, {
            headers: { "X-WP-Nonce": data.nonce },
          });

          const envelope = res.data;

          set({
            users: envelope.data ?? [],
            total: envelope.total ?? 0,
            page: envelope.page ?? clamped,
            perPage: envelope.per_page ?? perPage,
            totalPages: envelope.total_pages ?? 1,
            loadingUsers: false,
          });
        } catch (error) {
          console.error("Error paginating search results:", error);
          set({ loadingUsers: false });
        }
        return;
      }

      await get().getUsers(clamped);
    },

    createUser: async (userData) => {
      try {
        const res = await post(create_user_api_url, userData, {
          headers: { "X-WP-Nonce": data.nonce },
        });

        if (res.data.success) {
          // Refresh current page so the new user appears.
          // Also bust the search index by going back to page 1.
          await get().getUsers(1);
        }

        return res.data;
      } catch (error) {
        console.error("Error creating user:", error);
        return { success: false, message: "Failed to create user. Please try again." };
      }
    },

    updateUser: async (id, userData) => {
      set({ loading: true });
      try {
        const url = `${update_user_api_url}/${id}`;
        const res = await put(url, userData, {
          headers: { "X-WP-Nonce": data.nonce },
        });

        if (res.data.success) {
          // Update the user in the store
          const updatedUsers = get().users.map(user => 
            user.id === id ? { ...user, ...userData } : user
          );
          set({ users: updatedUsers });
        } else {
          throw new Error(res.data.message || "Failed to update user");
        }
      } catch (error) {
        console.error("Error updating user:", error);
        throw error;
      } finally {
        set({ loading: false });
      }
    },
  };
});

// Auto-fetch users on store creation (page 1).
useClientsStore.getState().getUsers(1);
