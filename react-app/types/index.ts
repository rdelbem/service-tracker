// Action type constants
export const GET_USERS = "GET_USERS";
export const GET_CASES = "GET_CASES";
export const IN_VIEW = "IN_VIEW";
export const GET_STATUS = "GET_STATUS";

// Global data object from WordPress wp_localize_script
declare const data: Record<string, any>;

// Common types
export interface User {
  id: string | number;
  name: string;
  [key: string]: any;
}

export interface Case {
  id: string | number;
  id_user: string | number;
  title: string;
  status: "open" | "close" | string;
  description?: string;
  created_at: string;
  start_at?: string | null;
  due_at?: string | null;
  [key: string]: any;
}

export interface Attachment {
  url: string;
  type: string;
  name: string;
  size: number;
}

export interface Status {
  id: string | number;
  id_case: string | number;
  id_user: string | number;
  text: string;
  created_at: string;
  attachments?: Attachment[] | null;
  [key: string]: any;
}

// Context types
export interface ClientsState {
  users: User[];
  loadingUsers: boolean;
}

export interface ClientsContextType {
  state: ClientsState;
  searchUsers: (query: string) => void;
  createUser: (userData: { name: string; email: string; phone?: string; cellphone?: string }) => Promise<{ success: boolean; message: string; user?: User }>;
}

export interface CasesState {
  user: string | number;
  cases: Case[];
  loadingCases: boolean;
}

export interface CasesContextType {
  state: CasesState;
  getCases: (id: string | number, onlyFetch: boolean) => Promise<Case[] | void>;
  postCase: (id: string | number, title: string) => Promise<void>;
  toggleCase: (id: string | number) => Promise<void>;
  deleteCase: (id: string | number, title: string) => Promise<void>;
  editCase: (id: string | number, id_user: string | number, newTitle: string) => Promise<void>;
  currentUserInDisplay: string | number | undefined;
  navigate: (view: string, userId: string | number, caseId: string | number, name: string) => void;
}

export interface InViewState {
  view: string;
  userId: string | number;
  caseId: string | number;
  name: string;
}

export interface InViewContextType {
  state: InViewState;
  updateIdView: (userId: string | number, caseId: string | number, view: string, name: string) => void;
}

export interface ProgressState {
  status: Status[];
  caseTitle: string;
  loadingStatus: boolean;
}

export interface ProgressContextType {
  state: ProgressState;
  getStatus: (id: string | number, onlyFetch: boolean, caseTitle?: string) => Promise<Status[] | void>;
  postStatus: (id_user: string | number, id_case: string | number, text: string) => Promise<void>;
  deleteStatus: (id: string | number, createdAt: string) => Promise<void>;
  editStatus: (id: string | number, id_user: string | number, newText: string) => Promise<void>;
}

// Action types
export interface AppAction {
  type: string;
  payload: any;
}
