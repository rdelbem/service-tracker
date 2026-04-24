import { describe, it, expect, beforeEach } from 'vitest';
import { renderHook, act } from '@testing-library/react';
import { useHashRouter } from '../useHashRouter';

describe('useHashRouter', () => {
  beforeEach(() => {
    localStorage.clear();
    window.location.hash = '';
  });

  it('initializes route from hash and syncs localStorage', () => {
    window.location.hash = '#/progress/10/20';

    const { result } = renderHook(() => useHashRouter());

    expect(result.current.route).toEqual({
      view: 'progress',
      userId: '10',
      caseId: '20',
      name: '',
    });
    expect(localStorage.getItem('view')).toBe('progress');
    expect(localStorage.getItem('userId')).toBe('10');
    expect(localStorage.getItem('caseId')).toBe('20');
  });

  it('falls back to localStorage when hash is empty', () => {
    localStorage.setItem('view', 'clients');
    localStorage.setItem('userId', '5');
    localStorage.setItem('caseId', '');
    localStorage.setItem('name', 'John');

    const { result } = renderHook(() => useHashRouter());

    expect(result.current.route).toEqual({
      view: 'clients',
      userId: '5',
      caseId: '',
      name: 'John',
    });
  });

  it('navigate updates hash, state and localStorage', () => {
    const { result } = renderHook(() => useHashRouter());

    act(() => {
      result.current.navigate('clients', '12', '', 'Jane');
    });

    expect(window.location.hash).toBe('#/clients/12');
    expect(result.current.route).toEqual({
      view: 'clients',
      userId: '12',
      caseId: '',
      name: 'Jane',
    });
    expect(localStorage.getItem('view')).toBe('clients');
    expect(localStorage.getItem('userId')).toBe('12');
    expect(localStorage.getItem('name')).toBe('Jane');
  });

  it('reacts to hashchange events', () => {
    const { result } = renderHook(() => useHashRouter());

    act(() => {
      window.location.hash = '#/progress/7/88';
      window.dispatchEvent(new HashChangeEvent('hashchange'));
    });

    expect(result.current.route).toEqual({
      view: 'progress',
      userId: '7',
      caseId: '88',
      name: '',
    });
  });
});
