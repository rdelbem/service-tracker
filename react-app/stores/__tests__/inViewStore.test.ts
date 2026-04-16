import { describe, it, expect, beforeEach, vi } from 'vitest';
import { create } from 'zustand';

// We need to mock localStorage and window.location for testing
const localStorageMock = (() => {
  let store: Record<string, string> = {};
  
  return {
    getItem(key: string) {
      return store[key] || null;
    },
    setItem(key: string, value: string) {
      store[key] = value.toString();
    },
    removeItem(key: string) {
      delete store[key];
    },
    clear() {
      store = {};
    }
  };
})();

describe('inViewStore', () => {
  beforeEach(() => {
    // Reset mocks
    localStorage.clear();
    Object.defineProperty(window, 'location', {
      value: {
        ...window.location,
        hash: '',
      },
      writable: true,
    });
  });

  it('navigates to clients view with userId', async () => {
    // Dynamically import the store to get fresh state
    const { useInViewStore } = await import('../inViewStore');
    
    useInViewStore.getState().navigate('clients', '123', '', 'Test Client');
    
    expect(useInViewStore.getState()).toEqual({
      view: 'clients',
      userId: '123',
      caseId: '',
      name: 'Test Client'
    });
    
    expect(window.location.hash).toBe('#/clients/123');
  });

  it('navigates to progress view with userId and caseId', async () => {
    const { useInViewStore } = await import('../inViewStore');
    
    useInViewStore.getState().navigate('progress', '1', '99', 'Test Case');
    
    expect(useInViewStore.getState()).toEqual({
      view: 'progress',
      userId: '1',
      caseId: '99',
      name: 'Test Case'
    });
    
    expect(window.location.hash).toBe('#/progress/1/99');
  });

  it('navigates to settings view', async () => {
    const { useInViewStore } = await import('../inViewStore');
    
    useInViewStore.getState().navigate('settings', '', '', '');
    
    expect(useInViewStore.getState()).toEqual({
      view: 'settings',
      userId: '',
      caseId: '',
      name: ''
    });
    
    expect(window.location.hash).toBe('#/settings');
  });

  it('updates state when hash changes', async () => {
    const { useInViewStore } = await import('../inViewStore');
    
    // Simulate hash change
    window.location.hash = '#/clients/456';
    window.dispatchEvent(new HashChangeEvent('hashchange'));
    
    expect(useInViewStore.getState()).toEqual({
      view: 'clients',
      userId: '456',
      caseId: '',
      name: ''
    });
  });

  it('persists state to localStorage', async () => {
    const { useInViewStore } = await import('../inViewStore');
    
    useInViewStore.getState().navigate('clients', '789', '', 'Another Client');
    
    expect(localStorage.getItem('view')).toBe('clients');
    expect(localStorage.getItem('userId')).toBe('789');
    expect(localStorage.getItem('caseId')).toBe('');
    expect(localStorage.getItem('name')).toBe('Another Client');
  });
});
