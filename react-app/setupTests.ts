import '@testing-library/jest-dom/vitest';
import { afterEach } from 'vitest';
import { cleanup } from '@testing-library/react';
import { Text } from './i18n';

// Mock global data object with config and all i18n text keys.
// Text values default to their enum key so stolmc_text() returns
// predictable strings in tests.
(globalThis as Record<string, any>).stolmcData = {
  root_url: 'http://localhost',
  api_url: 'api',
  users_api_url: 'http://localhost/api/users',
  nonce: 'test-nonce',
  ...Object.fromEntries(Object.values(Text).map((key) => [key, key])),
};

// Cleanup after each test
afterEach(() => {
  cleanup();
});

// Mock window.matchMedia
Object.defineProperty(window, 'matchMedia', {
  writable: true,
  value: vi.fn().mockImplementation(query => ({
    matches: false,
    media: query,
    onchange: null,
    addListener: vi.fn(),
    removeListener: vi.fn(),
    addEventListener: vi.fn(),
    removeEventListener: vi.fn(),
    dispatchEvent: vi.fn(),
  })),
});
