import { defineConfig } from "vite";
import react from "@vitejs/plugin-react-swc";
import path from "path";

/**
 * Vite config for the Service Tracker React admin SPA.
 *
 * Key decisions:
 * - Output goes to admin/js/prod/ matching the old Webpack output path.
 * - Single bundle file (no code splitting) for WordPress compatibility.
 * - CSS is extracted to a separate file for WordPress to load.
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
        jsx: "classic",
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
      // Enable CSS code splitting so we get a separate CSS file
      cssCodeSplit: true,
      rollupOptions: {
        input: path.resolve(__dirname, "react-app/index.tsx"),
        output: {
          // Single JS file output
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
