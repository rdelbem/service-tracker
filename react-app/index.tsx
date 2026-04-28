
import { createRoot } from "react-dom/client";
import App from "./App";
import "./index.css";

// Immediate execution - don't wait for DOMContentLoaded
const rootElement = document.getElementById("stolmc-root");
if (rootElement) {
  const root = createRoot(rootElement);
  root.render(<App />);
}
