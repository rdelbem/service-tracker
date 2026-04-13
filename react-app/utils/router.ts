// Route path constants
export const ROUTES = {
  INITIAL: "/",
  CASES: "/cases",
  CASES_FOR_CLIENT: "/cases/:userId",
  PROGRESS: "/progress/:userId/:caseId",
  HOW_TO_USE: "/how-to-use",
  CALENDAR: "/calendar",
} as const;

export type RoutePath = typeof ROUTES[keyof typeof ROUTES];

// Parsed route match result
export interface RouteMatch {
  view: string;
  userId: string;
  caseId: string;
  name: string; // Will be populated from context/data, not from URL
}

// Parse hash URL into route match
export function parseHash(hash: string): RouteMatch {
  // Remove leading # and /
  const path = hash.replace(/^#\/?/, "");
  
  if (!path || path === "") {
    return { view: "init", userId: "", caseId: "", name: "" };
  }

  const segments = path.split("/").filter(Boolean);

  // Match /progress/:userId/:caseId
  if (segments[0] === "progress" && segments.length >= 3) {
    return {
      view: "progress",
      userId: segments[1],
      caseId: segments[2],
      name: "",
    };
  }

  // Match /cases/add-new
  if (segments[0] === "cases" && segments[1] === "add-new") {
    return { view: "casesAddNew", userId: "", caseId: "", name: "" };
  }

  // Match /cases/:caseId (viewing a specific case)
  if (segments[0] === "cases" && segments.length >= 2 && segments[1] !== "add-new") {
    return { view: "cases", userId: "", caseId: segments[1], name: "" };
  }

  // Match /cases (list view)
  if (segments[0] === "cases") {
    return { view: "cases", userId: "", caseId: "", name: "" };
  }

  // Match /how-to-use
  if (segments[0] === "how-to-use") {
    return { view: "howToUse", userId: "", caseId: "", name: "" };
  }

  // Match /clients/:userId
  if (segments[0] === "clients" && segments.length >= 2) {
    return { view: "clients", userId: segments[1], caseId: "", name: "" };
  }

  // Match /clients
  if (segments[0] === "clients") {
    return { view: "clients", userId: "", caseId: "", name: "" };
  }

  // Match /calendar
  if (segments[0] === "calendar") {
    return { view: "calendar", userId: "", caseId: "", name: "" };
  }

  // Match /analytics
  if (segments[0] === "analytics") {
    return { view: "analytics", userId: "", caseId: "", name: "" };
  }

  // Match /settings
  if (segments[0] === "settings") {
    return { view: "settings", userId: "", caseId: "", name: "" };
  }

  // Match /support
  if (segments[0] === "support") {
    return { view: "support", userId: "", caseId: "", name: "" };
  }

  // Default to initial view
  return { view: "init", userId: "", caseId: "", name: "" };
}

// Build hash URL from route params
export function buildHash(view: string, userId: string | number = "", caseId: string | number = ""): string {
  switch (view) {
    case "init":
      return "#/";
    case "clients":
      return userId ? `#/clients/${userId}` : "#/clients";
    case "cases":
      return caseId ? `#/cases/${caseId}` : "#/cases";
    case "casesAddNew":
      return "#/cases/add-new";
    case "progress":
      return `#/progress/${userId}/${caseId}`;
    case "howToUse":
      return "#/how-to-use";
    case "calendar":
      return "#/calendar";
    case "analytics":
      return "#/analytics";
    case "settings":
      return "#/settings";
    case "support":
      return "#/support";
    default:
      return `#/${view}`;
  }
}
