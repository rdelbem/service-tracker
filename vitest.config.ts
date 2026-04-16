import { defineConfig } from 'vitest/config';
import react from '@vitejs/plugin-react';

export default defineConfig({
  plugins: [react()],
  test: {
    environment: 'jsdom',
    setupFiles: ['./vitest.setup.ts'],
    globals: true,
    include: ['react-app/**/*.{test,spec}.{js,jsx,ts,tsx}'],
    exclude: [
      'node_modules',
      'dist',
      'coverage',
      '**/node_modules/**',
      '**/dist/**'
    ],
    coverage: {
      provider: 'v8',
      reporter: ['text', 'json', 'html'],
      include: ['react-app/**/*.{js,jsx,ts,tsx}'],
      exclude: [
        'node_modules/',
        'react-app/App.tsx',
        'react-app/main.tsx',
        'react-app/setupTests.ts',
        '**/*.d.ts',
        '**/{vite,vitest,tailwind,postcss,webpack}.config.*',
        '**/.{eslint,mocha,prettier}rc.{?(c|m)js,yml}',
        '**/*.{test,spec}.{js,jsx,ts,tsx}',
        'react-app/stores/**',
        'react-app/types/**'
      ]
    },
  }
});
