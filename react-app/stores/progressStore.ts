import { create } from "zustand";
import { get as fetchGet, post, put, del, postMultipart } from "../utils/fetch";
import { toast } from "react-toastify";
import dateformat from "dateformat";
import type { Status, Attachment } from "../types";

declare const data: Record<string, any>;

export interface ProgressState {
  status: Status[];
  caseTitle: string;
  loadingStatus: boolean;
}

export interface ProgressActions {
  getStatus: (id: string | number, onlyFetch: boolean, caseTitle?: string) => Promise<Status[] | void>;
  postStatus: (id_user: string | number, id_case: string | number, text: string, attachments?: Attachment[]) => Promise<void>;
  deleteStatus: (id: string | number, createdAt: string) => Promise<void>;
  editStatus: (id: string | number, _id_user: string | number, newText: string) => Promise<void>;
  uploadFiles: (id_user: string | number, id_case: string | number, files: FileList | File[]) => Promise<Attachment[]>;
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
    postStatus: async (id_user: string | number, id_case: string | number, text: string, attachments?: Attachment[]): Promise<void> => {
      const dataToPost = { id_user, id_case, text, attachments };

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
          attachments: attachments || [],
        };
        const currentStatus = get().status;
        const newStatusArray = [...currentStatus, newStatus];

        set({ status: newStatusArray, loadingStatus: false });

        toast.success(data.toast_status_added);
      } catch (error) {
        alert(data.alert_error_base + error);
      }
    },
    deleteStatus: async (id: string | number): Promise<void> => {
      const status = [...get().status];

      try {
        await del(`${apiUrlProgress}/${id}`, {
          headers: { "X-WP-Nonce": data.nonce },
        });

        const filteredStatuses = status.filter((status: Status) => {
          return String(status.id) !== String(id);
        });

        set({ status: filteredStatuses, loadingStatus: false });

        toast.success(data.toast_status_deleted);
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

        toast.success(data.toast_status_edited);
      } catch (error) {
        alert(data.alert_error_base + error);
      }
    },
    uploadFiles: async (id_user: string | number, id_case: string | number, files: FileList | File[]): Promise<Attachment[]> => {
      const formData = new FormData();
      formData.append("id_user", String(id_user));
      formData.append("id_case", String(id_case));

      for (let i = 0; i < files.length; i++) {
        formData.append("files", files[i]);
      }

      try {
        const apiUrlUpload = `${data.root_url}/wp-json/${data.api_url}/progress/upload`;
        const res = await postMultipart(apiUrlUpload, formData, {
          headers: { "X-WP-Nonce": data.nonce },
        });

        if (res.data.success) {
          return res.data.files as Attachment[];
        } else {
          throw new Error(res.data.message || "Upload failed");
        }
      } catch (error) {
        alert(data.alert_error_base + error);
        return [];
      }
    },
  };
});
