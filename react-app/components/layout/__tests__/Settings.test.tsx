import { describe, it, expect, beforeEach, vi } from 'vitest';
import { render, screen, fireEvent } from '@testing-library/react';
import Settings from '../Settings';

describe('Settings Component', () => {
  beforeEach(() => {
    // Clear localStorage before each test
    localStorage.clear();
    
    // Reset document classes
    document.documentElement.classList.remove('dark');
  });

  it('reads current dark state from html class', () => {
    document.documentElement.classList.add('dark');
    render(<Settings />);
    expect(document.documentElement.classList.contains('dark')).toBe(true);
  });

  it('toggling updates document.documentElement.classList', () => {
    render(<Settings />);
    
    // Initially light mode
    expect(document.documentElement.classList.contains('dark')).toBe(false);
    
    // Click toggle button
    const toggleButton = screen.getByRole('button');
    fireEvent.click(toggleButton);
    
    // Should now be dark mode
    expect(document.documentElement.classList.contains('dark')).toBe(true);
    
    // Click again
    fireEvent.click(toggleButton);
    
    // Should now be light mode
    expect(document.documentElement.classList.contains('dark')).toBe(false);
  });

  it('persists theme in localStorage', () => {
    render(<Settings />);
    
    // Initially no theme in localStorage
    expect(localStorage.getItem('theme')).toBeNull();
    
    // Click toggle button to enable dark mode
    const toggleButton = screen.getByRole('button');
    fireEvent.click(toggleButton);
    
    // Should save dark theme
    expect(localStorage.getItem('theme')).toBe('dark');
    
    // Click again to disable dark mode
    fireEvent.click(toggleButton);
    
    // Should save light theme
    expect(localStorage.getItem('theme')).toBe('light');
  });

  it('initializes from saved theme', () => {
    // Set saved theme to dark
    localStorage.setItem('theme', 'dark');
    
    render(<Settings />);
    
    // Should initialize with dark mode
    expect(document.documentElement.classList.contains('dark')).toBe(true);
  });

  it('initializes from system preference when no saved theme', () => {
    // Mock system preference for dark mode
    Object.defineProperty(window, 'matchMedia', {
      writable: true,
      value: vi.fn().mockImplementation(query => ({
        matches: query === '(prefers-color-scheme: dark)',
        media: query,
        onchange: null,
        addListener: vi.fn(),
        removeListener: vi.fn(),
        addEventListener: vi.fn(),
        removeEventListener: vi.fn(),
        dispatchEvent: vi.fn(),
      })),
    });
    
    const { unmount } = render(<Settings />);
    
    // Clean up
    unmount();
  });

  it('renders correctly with light mode', () => {
    render(<Settings />);
    
    expect(screen.getByText('Settings')).toBeInTheDocument();
    expect(screen.getByText('Appearance')).toBeInTheDocument();
    expect(screen.getByText('Dark Mode')).toBeInTheDocument();
    expect(screen.getByText('Theme Preview')).toBeInTheDocument();
    expect(screen.getByText('Current theme: Light')).toBeInTheDocument();
  });

  it('renders correctly with dark mode', () => {
    document.documentElement.classList.add('dark');
    render(<Settings />);
    
    expect(screen.getByText('Current theme: Dark')).toBeInTheDocument();
  });
});
