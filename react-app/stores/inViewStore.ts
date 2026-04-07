import { create } from "zustand";
import { buildHash, parseHash } from "../utils/router";

declare const data: Record<string, any>;

export interface InViewState {
  view: string;
  userId: string;
  caseId: string;
  name: string;
}

export interface InViewActions {
  navigate: (view: string, userId: string | number, caseId: string | number, name: string) => void;
  updateIdView: (userId: string | number, caseId: string | number, view: string, name: string) => void;
}

export interface InViewStore extends InViewState, InViewActions {}

function getInitialRoute(): InViewState {
  const hash = window.location.hash;
  if (hash) {
    const parsed = parseHash(hash);
    localStorage.setItem("view", parsed.view);
    localStorage.setItem("userId", parsed.userId);
    localStorage.setItem("caseId", parsed.caseId);
    localStorage.setItem("name", parsed.name);
    return parsed;
  }

  return {
    view: localStorage.getItem("view") || "init",
    userId: localStorage.getItem("userId") || "",
    caseId: localStorage.getItem("caseId") || "",
    name: localStorage.getItem("name") || "",
  };
}

export const useInViewStore = create<InViewStore>((set, get) => {
  const initial = getInitialRoute();

  return {
    ...initial,
    navigate: (view, userId = "", caseId = "", name = "") => {
      const hash = buildHash(view, String(userId), String(caseId));
      if (window.location.hash !== hash) {
        window.location.hash = hash;
      }
      set({ view, userId: String(userId), caseId: String(caseId), name });
      localStorage.setItem("view", view);
      localStorage.setItem("userId", String(userId));
      localStorage.setItem("caseId", String(caseId));
      localStorage.setItem("name", name);
    },
    updateIdView: (userId, caseId, view, name) => {
      get().navigate(view, userId, caseId, name);
    },
  };
});

// Listen for hash changes (browser back/forward buttons)
if (typeof window !== "undefined") {
  window.addEventListener("hashchange", () => {
    const hash = window.location.hash;
    if (hash) {
      const parsed = parseHash(hash);
      useInViewStore.setState(parsed);
      localStorage.setItem("view", parsed.view);
      localStorage.setItem("userId", parsed.userId);
      localStorage.setItem("caseId", parsed.caseId);
      localStorage.setItem("name", parsed.name);
    }
  });
}
