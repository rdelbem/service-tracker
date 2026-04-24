import type { User } from "../types";

export function normalizeUser(raw: any): User {
  const id = raw?.id ?? raw?.ID ?? "";
  const name = raw?.name ?? raw?.display_name ?? "";
  const email = raw?.email ?? raw?.user_email ?? "";
  const createdAt = raw?.created_at ?? raw?.user_registered ?? "";
  const roles = Array.isArray(raw?.roles) ? raw.roles : [];
  const role = raw?.role ?? roles[0];

  return {
    ...raw,
    id,
    name,
    email,
    created_at: createdAt,
    role,
  };
}

export function normalizeUsers(input: any): User[] {
  if (!Array.isArray(input)) return [];

  return input
    .map(normalizeUser)
    .filter((u) => u.id !== "" && u.id !== undefined && u.id !== null);
}
