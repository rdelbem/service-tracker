// Route path constants
export const ROUTES = {
  INITIAL: "/",
  CASES: "/cases",
  CASES_FOR_CLIENT: "/cases/:userId",
  PROGRESS: "/progress/:userId/:caseId",
  HOW_TO_USE: "/how-to-use",
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

  // Match /cases/:userId
  if (segments[0] === "cases" && segments.length >= 2) {
    return {
      view: "cases",
      userId: segments[1],
      caseId: "",
      name: "",
    };
  }

  // Match /cases
  if (segments[0] === "cases") {
    return { view: "cases", userId: "", caseId: "", name: "" };
  }

  // Match /how-to-use
  if (segments[0] === "how-to-use") {
    return { view: "howToUse", userId: "", caseId: "", name: "" };
  }

  // Default to initial view
  return { view: "init", userId: "", caseId: "", name: "" };
}

// Build hash URL from route params
export function buildHash(view: string, userId: string | number = "", caseId: string | number = ""): string {
  switch (view) {
    case "init":
      return "#/";
    case "cases":
      return userId ? `#/cases/${userId}` : "#/cases";
    case "progress":
      return `#/progress/${userId}/${caseId}`;
    case "howToUse":
      return "#/how-to-use";
    default:
      return "#/";
  }
}
