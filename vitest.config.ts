import { defineConfig } from 'vitest/config';
import react from '@vitejs/plugin-react';

export default defineConfig({
  plugins: [react()],
  test: {
    environment: 'jsdom',
    setupFiles: ['./vitest.setup.ts'],
    globals: true,
    coverage: {
      provider: 'v8',
      reporter: ['text', 'json', 'html'],
      exclude: [
        'node_modules/',
        'react-app/main.tsx',
        'react-app/App.tsx',
        '**/*.d.ts',
        '**/{vite,vitest,tailwind,postcss,webpack}.config.*',
        '**/.{eslint,mocha,prettier}rc.{?(c|m)js,yml}',
        '**/*.{test,spec}.{ts,tsx}',
        'react-app/stores/**',
        'react-app/types/**'
      ]
    },
    include: ['**/*.{test,spec}.{ts,tsx}'],
    exclude: [
      'node_modules',
      'dist',
      'coverage',
      '**/node_modules/**',
      '**/dist/**'
    ]
  }
});
