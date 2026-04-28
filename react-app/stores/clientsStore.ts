import { create } from "zustand";
import { get as fetchGet, post, put } from "../utils/fetch";
import { normalizeUsers } from "../utils/users";
import { stolmc_text, Text } from "../i18n";
import type { User } from "../types";

declare const stolmcData: Record<string, any>;

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
  const api_url_users = stolmcData.users_api_url;
  const create_user_api_url = stolmcData.create_user_api_url;
  const search_url = `${stolmcData.root_url}/wp-json/service-tracker-stolmc/v1/users/search`;
  const update_user_api_url = `${stolmcData.root_url}/wp-json/service-tracker-stolmc/v1/users`;

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
          headers: { "X-WP-Nonce": stolmcData.nonce },
        });

        const envelope = res.data;
        const pagination = envelope.meta?.pagination ?? {};
        const users = normalizeUsers(envelope.data);

        set({
          users,
          total: pagination.total ?? 0,
          page: pagination.page ?? currentPage,
          perPage: pagination.per_page ?? perPage,
          totalPages: pagination.total_pages ?? 1,
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
          headers: { "X-WP-Nonce": stolmcData.nonce },
        });

        const envelope = res.data;
        const pagination = envelope.meta?.pagination ?? {};
        const users = normalizeUsers(envelope.data);

        set({
          users,
          total: pagination.total ?? 0,
          page: pagination.page ?? 1,
          perPage: pagination.per_page ?? perPage,
          totalPages: pagination.total_pages ?? 1,
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
            headers: { "X-WP-Nonce": stolmcData.nonce },
          });

          const envelope = res.data;
          const pagination = envelope.meta?.pagination ?? {};
          const users = normalizeUsers(envelope.data);

          set({
            users,
            total: pagination.total ?? 0,
            page: pagination.page ?? clamped,
            perPage: pagination.per_page ?? perPage,
            totalPages: pagination.total_pages ?? 1,
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
          headers: { "X-WP-Nonce": stolmcData.nonce },
        });

        if (res.data.success) {
          // Refresh current page so the new user appears.
          // Also bust the search index by going back to page 1.
          await get().getUsers(1);
        }

        const payload = res.data;
        return {
          success: Boolean(payload?.success),
          message: payload?.message ?? (payload?.success ? stolmc_text(Text.ToastUserCreated) : stolmc_text(Text.ToastUserCreateFailed)),
          user: payload?.data,
        };
      } catch (error) {
        console.error("Error creating user:", error);
        return { success: false, message: stolmc_text(Text.ToastUserCreateError) };
      }
    },

    updateUser: async (id, userData) => {
      set({ loading: true });
      try {
        const url = `${update_user_api_url}/${id}`;
        const res = await put(url, userData, {
          headers: { "X-WP-Nonce": stolmcData.nonce },
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
