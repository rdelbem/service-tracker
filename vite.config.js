import { defineConfig } from "vite";
import react from "@vitejs/plugin-react-swc";
import path from "path";

/**
 * Emits a stable JSON file that maps logical bundle keys to hashed file names.
 * Example:
 * {
 *   "entrypoint": "App-ABC123.js"
 * }
 */
function entrypointMapPlugin() {
  return {
    name: "stolmc-entrypoint-map",
    generateBundle(_options, bundle) {
      const entryChunk = Object.values(bundle).find(
        (item) => item.type === "chunk" && item.isEntry
      );

      if (!entryChunk) {
        this.error("Could not find Vite entry chunk to build entrypoints.json");
        return;
      }

      this.emitFile({
        type: "asset",
        fileName: "entrypoints.json",
        source: `${JSON.stringify(
          { entrypoint: entryChunk.fileName },
          null,
          2
        )}\n`,
      });
    },
  };
}

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
      entrypointMapPlugin(),
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
      // Disable CSS code splitting — all CSS goes into one file for WordPress
      // JS code splitting via React.lazy() still works
      cssCodeSplit: false,
      // Allow code splitting for React.lazy() dynamic imports
      rollupOptions: {
        input: path.resolve(__dirname, "react-app/index.tsx"),
        output: {
          // Main entry file name
          entryFileNames: "App-[hash].js",
          // Chunk file names for lazy-loaded components
          chunkFileNames: "assets/[name]-[hash].js",
          // Keep CSS in a predictable location
          assetFileNames: (assetInfo) => {
            if (assetInfo.name && assetInfo.name.endsWith('.css')) {
              return 'style.css';
            }
            return 'assets/[name]-[hash][extname]';
          },
          // Separate vendor libraries into their own chunk
          manualChunks: (id) => {
            if (id.includes("node_modules")) {
              if (id.includes("react-toastify")) return "toastify";
              if (id.includes("react-icons")) return "icons";
              if (id.includes("react-textarea-autosize")) return "textarea";
              return "vendor";
            }
          },
        },
      },
    },
  };
});
