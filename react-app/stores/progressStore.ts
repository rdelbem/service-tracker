import { create } from "zustand";
import { get as fetchGet, post, put, del } from "../utils/fetch";
import { toast } from "react-toastify";
import dateformat from "dateformat";
import type { Status } from "../types";

declare const data: Record<string, any>;

export interface ProgressState {
  status: Status[];
  caseTitle: string;
  loadingStatus: boolean;
}

export interface ProgressActions {
  getStatus: (id: string | number, onlyFetch: boolean, caseTitle?: string) => Promise<Status[] | void>;
  postStatus: (id_user: string | number, id_case: string | number, text: string) => Promise<void>;
  deleteStatus: (id: string | number, createdAt: string) => Promise<void>;
  editStatus: (id: string | number, _id_user: string | number, newText: string) => Promise<void>;
}

export interface ProgressStore extends ProgressState, ProgressActions {}

export const useProgressStore = create<ProgressStore>((set, get) => {
  const apiUrlProgress = `${data.root_url}/wp-json/${data.api_url}/progress`;

  return {
    status: [],
    caseTitle: "",
    loadingStatus: false,
    getStatus: async (id: string | number, onlyFetch: boolean, caseTitle?: string): Promise<Status[] | void> => {
      try {
        if (!onlyFetch) {
          set({ loadingStatus: true });
        }

        const res = await fetchGet(`${apiUrlProgress}/${id}`, {
          headers: { "X-WP-Nonce": data.nonce },
        });

        if (!onlyFetch) {
          set({ status: res.data, caseTitle: caseTitle || get().caseTitle, loadingStatus: false });
        }

        return res.data;
      } catch (error) {
        console.error("Error fetching status:", error);
        if (!onlyFetch) {
          set({ loadingStatus: false });
        }
      }
    },
    postStatus: async (id_user: string | number, id_case: string | number, text: string): Promise<void> => {
      const dataToPost = { id_user, id_case, text };

      try {
        await post(`${apiUrlProgress}/${id_case}`, dataToPost, {
          headers: {
            "X-WP-Nonce": data.nonce,
            "Content-type": "application/json",
          },
        });

        const getAllStatus = await get().getStatus(id_case, true, get().caseTitle);
        const statusArray = Array.isArray(getAllStatus) ? getAllStatus : [];

        const { id, created_at } = statusArray[statusArray.length - 1];

        const newStatus = {
          id,
          id_case,
          id_user,
          created_at,
          text,
        };
        const currentStatus = get().status;
        const newStatusArray = [...currentStatus, newStatus];

        set({ status: newStatusArray, loadingStatus: false });

        toast.success(data.toast_status_added, {
          position: "bottom-right",
          autoClose: 5000,
          hideProgressBar: true,
          closeOnClick: true,
          pauseOnHover: true,
          draggable: true,
          progress: undefined,
        });
      } catch (error) {
        alert(data.alert_error_base + error);
      }
    },
    deleteStatus: async (id: string | number, createdAt: string): Promise<void> => {
      const status = [...get().status];

      const sureToDelete = confirm(
        `Are you sure you want to delete status from ${dateformat(createdAt, "dd/mm/yyyy, HH:MM")}?`
      );

      if (!sureToDelete) return;

      try {
        await del(`${apiUrlProgress}/${id}`, {
          headers: { "X-WP-Nonce": data.nonce },
        });

        const filteredStatuses = status.filter((status: Status) => {
          return dateformat(status.created_at, "dd/mm/yyyy, HH:MM") !== dateformat(createdAt, "dd/mm/yyyy, HH:MM");
        });

        set({ status: filteredStatuses, loadingStatus: false });

        toast.success(data.toast_status_deleted, {
          position: "bottom-right",
          autoClose: 5000,
          hideProgressBar: true,
          closeOnClick: true,
          pauseOnHover: true,
          draggable: true,
          progress: undefined,
        });
      } catch (error) {
        alert(data.alert_error_base + error);
      }
    },
    editStatus: async (id: string | number, _id_user: string | number, newText: string): Promise<void> => {
      if (newText === "") {
        alert(data.alert_blank_status_title);
        return;
      }

      try {
        await put(`${apiUrlProgress}/${id}`, { text: newText }, {
          headers: {
            "X-WP-Nonce": data.nonce,
            "Content-type": "application/json",
          },
        });

        const newStatuses = get().status.map((status: Status) => {
          if (status.id === id) {
            return { ...status, text: newText };
          }
          return status;
        });

        set({ status: newStatuses, loadingStatus: false });

        toast.success(data.toast_status_edited, {
          position: "bottom-right",
          autoClose: 5000,
          hideProgressBar: true,
          closeOnClick: true,
          pauseOnHover: true,
          draggable: true,
          progress: undefined,
        });
      } catch (error) {
        alert(data.alert_error_base + error);
      }
    },
  };
});
