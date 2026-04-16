import { describe, it, expect } from 'vitest';
import { parseHash, buildHash } from '../router';

describe('Router utilities', () => {
  describe('parseHash', () => {
    it('should parse #/clients/123 correctly', () => {
      const result = parseHash('#/clients/123');
      expect(result).toEqual({
        view: 'clients',
        userId: '123',
        caseId: '',
        name: ''
      });
    });

    it('should parse #/settings correctly', () => {
      const result = parseHash('#/settings');
      expect(result).toEqual({
        view: 'settings',
        userId: '',
        caseId: '',
        name: ''
      });
    });

    it('should parse #/progress/1/99 correctly', () => {
      const result = parseHash('#/progress/1/99');
      expect(result).toEqual({
        view: 'progress',
        userId: '1',
        caseId: '99',
        name: ''
      });
    });

    it('should handle empty hash correctly', () => {
      const result = parseHash('');
      expect(result).toEqual({
        view: 'init',
        userId: '',
        caseId: '',
        name: ''
      });
    });

    it('should handle unknown routes correctly', () => {
      const result = parseHash('#/unknown-route');
      expect(result).toEqual({
        view: 'init',
        userId: '',
        caseId: '',
        name: ''
      });
    });
  });

  describe('buildHash', () => {
    it('should build hash for clients view with userId', () => {
      const result = buildHash('clients', '123');
      expect(result).toBe('#/clients/123');
    });

    it('should build hash for clients view without userId', () => {
      const result = buildHash('clients');
      expect(result).toBe('#/clients');
    });

    it('should build hash for progress view', () => {
      const result = buildHash('progress', '1', '99');
      expect(result).toBe('#/progress/1/99');
    });

    it('should build hash for settings view', () => {
      const result = buildHash('settings');
      expect(result).toBe('#/settings');
    });

    it('should build hash for init view', () => {
      const result = buildHash('init');
      expect(result).toBe('#/');
    });

    it('should build hash for unknown view', () => {
      const result = buildHash('unknown');
      expect(result).toBe('#/unknown');
    });
  });
});
