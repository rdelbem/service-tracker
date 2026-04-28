import { create } from "zustand";
import { get as fetchGet, post, put, del, postMultipart } from "../utils/fetch";
import { toast } from "react-toastify";
import { stolmc_text, Text } from "../i18n";
import type { Status, Attachment } from "../types";

declare const stolmcData: Record<string, any>;

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
  const apiUrlProgress = `${stolmcData.root_url}/wp-json/${stolmcData.api_url}/progress`;

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
          headers: { "X-WP-Nonce": stolmcData.nonce },
        });

        const statusData = Array.isArray(res.data.data) ? res.data.data : [];

        if (!onlyFetch) {
          set({ status: statusData, caseTitle: caseTitle || get().caseTitle, loadingStatus: false });
        }

        return statusData;
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
            "X-WP-Nonce": stolmcData.nonce,
            "Content-type": "application/json",
          },
        });

        // Fetch the full updated list from the server so attachments and all
        // fields are sourced from the database rather than reconstructed locally.
        const getAllStatus = await get().getStatus(id_case, true, get().caseTitle);
        const statusArray = Array.isArray(getAllStatus) ? getAllStatus : [];

        set({ status: statusArray, loadingStatus: false });

        toast.success(stolmc_text(Text.ToastStatusAdded));
      } catch (error) {
        alert(stolmc_text(Text.AlertErrorBase) + error);
      }
    },
    deleteStatus: async (id: string | number): Promise<void> => {
      const status = [...get().status];

      try {
        await del(`${apiUrlProgress}/${id}`, {
          headers: { "X-WP-Nonce": stolmcData.nonce },
        });

        const filteredStatuses = status.filter((status: Status) => {
          return String(status.id) !== String(id);
        });

        set({ status: filteredStatuses, loadingStatus: false });

        toast.success(stolmc_text(Text.ToastStatusDeleted));
      } catch (error) {
        alert(stolmc_text(Text.AlertErrorBase) + error);
      }
    },
    editStatus: async (id: string | number, _id_user: string | number, newText: string): Promise<void> => {
      if (newText === "") {
        alert(stolmc_text(Text.AlertBlankStatusTitle));
        return;
      }

      try {
        await put(`${apiUrlProgress}/${id}`, { text: newText }, {
          headers: {
            "X-WP-Nonce": stolmcData.nonce,
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

        toast.success(stolmc_text(Text.ToastStatusEdited));
      } catch (error) {
        alert(stolmc_text(Text.AlertErrorBase) + error);
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
        const apiUrlUpload = `${stolmcData.root_url}/wp-json/${stolmcData.api_url}/progress/upload`;
        const res = await postMultipart(apiUrlUpload, formData, {
          headers: { "X-WP-Nonce": stolmcData.nonce },
        });

        if (res.data.success) {
          const files = Array.isArray(res.data.data?.files) ? res.data.data.files : [];
          return files as Attachment[];
        } else {
          throw new Error(res.data.message || stolmc_text(Text.ToastUploadFailed));
        }
      } catch (error) {
        alert(stolmc_text(Text.AlertErrorBase) + error);
        return [];
      }
    },
  };
});
