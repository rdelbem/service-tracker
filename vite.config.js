import { defineConfig } from "vite";
import react from "@vitejs/plugin-react";
import path from "path";

/**
 * Vite config for the Service Tracker React admin SPA.
 *
 * Key decisions:
 * - Output goes to admin/js/prod/ matching the old Webpack output path.
 * - Single bundle file (no code splitting) for WordPress compatibility.
 * - No index.html in output - WordPress loads the JS directly.
 * - The global `data` object (from wp_localize_script) is left as-is — it's
 *   available on `window` at runtime and Vite won't touch bare identifiers.
 */
export default defineConfig(({ mode }) => {
  const isProd = mode === "production";

  return {
    root: "react-app",
    base: "./",
    plugins: [
      react({
        // Use classic JSX runtime (React.createElement)
        jsxRuntime: "classic",
      }),
    ],
    server: {
      port: 3000,
      proxy: {
        // Proxy API requests to your WordPress/PHP server
        "/api": "http://localhost:8088",
      },
    },
    build: {
      outDir: path.resolve(__dirname, "admin/js/prod"),
      emptyOutDir: true,
      sourcemap: !isProd,
      minify: isProd,
      // Inline CSS into JS for single-file output
      cssCodeSplit: false,
      // Disable code splitting for WordPress compatibility
      rollupOptions: {
        input: path.resolve(__dirname, "react-app/index.jsx"),
        output: {
          // Single file output
          entryFileNames: "App.js",
          // Inline dynamic imports
          inlineDynamicImports: true,
          // Manual chunks disabled
          manualChunks: undefined,
        },
      },
    },
  };
});
