import { useState, useEffect, useCallback } from "react";
import { parseHash, buildHash, type RouteMatch } from "../utils/router";

/**
 * Custom hook for hash-based routing
 * Manages the current route state and provides navigation functions
 * Falls back to localStorage if no hash is present
 */
export function useHashRouter() {
  const [route, setRoute] = useState<RouteMatch>(() => {
    // Try to parse from hash first
    const hash = window.location.hash;
    if (hash) {
      const parsed = parseHash(hash);
      // Persist to localStorage for fallback
      localStorage.setItem("view", parsed.view);
      localStorage.setItem("userId", parsed.userId);
      localStorage.setItem("caseId", parsed.caseId);
      return parsed;
    }

    // Fallback to localStorage
    const savedView = localStorage.getItem("view") || "init";
    const savedUserId = localStorage.getItem("userId") || "";
    const savedCaseId = localStorage.getItem("caseId") || "";
    const savedName = localStorage.getItem("name") || "";

    return {
      view: savedView,
      userId: savedUserId,
      caseId: savedCaseId,
      name: savedName,
    };
  });

  // Listen for hash changes (browser back/forward buttons)
  useEffect(() => {
    const handleHashChange = () => {
      const hash = window.location.hash;
      if (hash) {
        const parsed = parseHash(hash);
        setRoute(parsed);
        // Sync to localStorage
        localStorage.setItem("view", parsed.view);
        localStorage.setItem("userId", parsed.userId);
        localStorage.setItem("caseId", parsed.caseId);
      }
    };

    window.addEventListener("hashchange", handleHashChange);
    return () => window.removeEventListener("hashchange", handleHashChange);
  }, []);

  // Navigate to a new route
  const navigate = useCallback((view: string, userId: string | number = "", caseId: string | number = "", name: string = "") => {
    const hash = buildHash(view, userId, caseId);
    
    // Only update if route actually changed
    if (window.location.hash !== hash) {
      window.location.hash = hash;
    }

    // Update state
    setRoute({
      view,
      userId: String(userId),
      caseId: String(caseId),
      name,
    });

    // Sync to localStorage for fallback
    localStorage.setItem("view", view);
    localStorage.setItem("userId", String(userId));
    localStorage.setItem("caseId", String(caseId));
    localStorage.setItem("name", name);
  }, []);

  return {
    route,
    navigate,
  };
}
