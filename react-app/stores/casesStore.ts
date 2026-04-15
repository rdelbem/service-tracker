import { create } from "zustand";
import { get as fetchGet, post, put, del } from "../utils/fetch";
import { toast } from "react-toastify";
import type { Case } from "../types";

declare const data: Record<string, any>;

export interface CasesState {
  user: string | number;
  cases: Case[];
  loadingCases: boolean;
  page: number;
  perPage: number;
  total: number;
  totalPages: number;
}

export interface CasesActions {
  getCases: (id: string | number, onlyFetch: boolean, page?: number) => Promise<Case[] | void>;
  postCase: (id: string | number, title: string, extraData?: Record<string, any>) => Promise<void>;
  toggleCase: (id: string | number) => Promise<void>;
  deleteCase: (id: string | number, title: string) => Promise<void>;
  editCase: (
    id: string | number,
    id_user: string | number,
    newTitle: string,
    start_at?: string | null,
    due_at?: string | null,
  ) => Promise<void>;
  setPage: (id: string | number, page: number) => Promise<void>;
}

export interface CasesStore extends CasesState, CasesActions {}

export const useCasesStore = create<CasesStore>((set, get) => {
  const apiUrlCases = `${data.root_url}/wp-json/${data.api_url}/cases`;

  return {
    user: "",
    cases: [],
    loadingCases: false,
    page: 1,
    perPage: 6,
    total: 0,
    totalPages: 1,

    getCases: async (id: string | number, onlyFetch: boolean, page?: number): Promise<Case[] | void> => {
      try {
        if (!onlyFetch) {
          set({ loadingCases: true });
        }

        const currentPage = page ?? get().page;
        const perPage = get().perPage;

        const url = `${apiUrlCases}/${id}?page=${currentPage}&per_page=${perPage}`;
        const res = await fetchGet(url, {
          headers: { "X-WP-Nonce": data.nonce },
        });

        // API now returns a paginated envelope: { data, total, page, per_page, total_pages }
        const envelope = res.data;
        const casesArray: Case[] = envelope.data ?? [];

        if (!onlyFetch) {
          set({
            user: id,
            cases: casesArray,
            page: envelope.page ?? currentPage,
            perPage: envelope.per_page ?? perPage,
            total: envelope.total ?? 0,
            totalPages: envelope.total_pages ?? 1,
            loadingCases: false,
          });
        }

        return casesArray;
      } catch (error) {
        console.error("Error fetching cases:", error);
        if (!onlyFetch) {
          set({ loadingCases: false });
        }
      }
    },

    setPage: async (id: string | number, page: number): Promise<void> => {
      const { totalPages } = get();
      const clamped = Math.max(1, Math.min(page, totalPages));
      set({ page: clamped });
      await get().getCases(id, false, clamped);
    },

    postCase: async (id: string | number, title: string, extraData?: Record<string, any>): Promise<void> => {
      if (title === "") {
        alert("The title can not be blank!");
        return;
      }

      const dataToPost: Record<string, any> = { id_user: id, title: title };
      if (extraData) {
        if (extraData.status) dataToPost.status = extraData.status;
        if (extraData.description) dataToPost.description = extraData.description;
        if (extraData.start_at) dataToPost.start_at = extraData.start_at;
        if (extraData.due_at) dataToPost.due_at = extraData.due_at;
      }

      try {
        await post(`${apiUrlCases}/${id}`, dataToPost, {
          headers: {
            "X-WP-Nonce": data.nonce,
            "Content-type": "application/json",
          },
        });

        // Refresh the current page from the server so the new case appears.
        await get().getCases(id, false, get().page);

        toast.success(data.toast_case_added);
      } catch (error) {
        alert(data.alert_error_base + error);
      }
    },

    toggleCase: async (id: string | number): Promise<void> => {
      const currentCases = get().cases;
      const theCase = currentCases.find((c: Case) => String(c.id) === String(id));
      const targetStatus = theCase?.status === "open" ? "close" : "open";

      try {
        await post(`${apiUrlCases}-status/${id}`, null, {
          headers: { "X-WP-Nonce": data.nonce },
        });

        const updatedCases = currentCases.map((c: Case) => {
          if (String(c.id) === String(id)) {
            return { ...c, status: targetStatus };
          }
          return c;
        });

        set({ cases: updatedCases });
        toast.success(`Case is now ${targetStatus === "open" ? "open" : "closed"}`);
      } catch (error) {
        console.error("Error toggling case:", error);
        toast.error("Failed to update case status");
      }
    },

    deleteCase: async (id: string | number): Promise<void> => {
      try {
        await del(`${apiUrlCases}/${id}`, {
          headers: { "X-WP-Nonce": data.nonce },
        });

        const currentCases = get().cases;
        const newCases = currentCases.filter(
          (theCase: Case) => theCase.id.toString() !== id.toString()
        );

        set({ cases: newCases });

        toast.success(data.toast_case_deleted);
      } catch (error) {
        alert(data.alert_error_base + error);
      }
    },

    editCase: async (
      id: string | number,
      id_user: string | number,
      newTitle: string,
      start_at?: string | null,
      due_at?: string | null,
    ): Promise<void> => {
      if (newTitle === "") {
        alert(data.alert_error_base + data.alert_blank_case_title);
        return;
      }

      const idTitleObj: Record<string, any> = { id_user, title: newTitle };
      if (start_at !== undefined) idTitleObj.start_at = start_at;
      if (due_at !== undefined) idTitleObj.due_at = due_at;

      try {
        await put(`${apiUrlCases}/${id}`, idTitleObj, {
          headers: {
            "X-WP-Nonce": data.nonce,
            "Content-type": "application/json",
          },
        });

        const currentCases = get().cases;
        const newCases = currentCases.map((theCase: Case) => {
          if (theCase.id.toString() === id.toString()) {
            const updated: Case = { ...theCase, title: newTitle };
            if (start_at !== undefined) updated.start_at = start_at;
            if (due_at !== undefined) updated.due_at = due_at;
            return updated;
          }
          return theCase;
        });

        set({ user: id_user, cases: newCases });

        toast.success(data.toast_case_edited);
      } catch (error) {
        alert(data.alert_error_base + error);
      }
    },
  };
});
