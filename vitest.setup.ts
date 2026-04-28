import '@testing-library/jest-dom/vitest';
import { vi } from 'vitest';
import { Text } from './react-app/i18n';

// Mock WordPress data object
vi.stubGlobal('data', {
  users_api_url: 'http://localhost/wp-json/service-tracker-stolmc/v1/users',
  root_url: 'http://localhost',
  nonce: 'test-nonce',
  create_user_api_url: 'http://localhost/wp-json/service-tracker-stolmc/v1/users/create',
  cases_api_url: 'http://localhost/wp-json/service-tracker-stolmc/v1/cases'
});

// Mock stolmcData (WordPress localized data) with config and all i18n text keys.
// Text values default to their enum key so stolmc_text() returns predictable strings in tests.
(globalThis as Record<string, any>).stolmcData = {
  root_url: 'http://localhost',
  api_url: 'api',
  users_api_url: 'http://localhost/api/users',
  create_user_api_url: 'http://localhost/api/users/create',
  nonce: 'test-nonce',
  ...Object.fromEntries(Object.values(Text).map((key) => [key, key])),
};

// Mock fetch globally
global.fetch = vi.fn();

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

// Mock localStorage
const localStorageMock = (() => {
  let store: Record<string, string> = {};
  return {
    getItem: (key: string) => (key in store ? store[key] : null),
    setItem: (key: string, value: string) => {
      store[key] = value.toString();
    },
    removeItem: (key: string) => {
      delete store[key];
    },
    clear: () => {
      store = {};
    }
  };
})();

Object.defineProperty(window, 'localStorage', {
  value: localStorageMock,
  configurable: true,
});
