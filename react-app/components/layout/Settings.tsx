import { useState, useEffect } from "react";

export default function Settings() {
  // Check if we're in dark mode by looking at the class on the html element
  const [darkMode, setDarkMode] = useState(() => {
    if (typeof window !== 'undefined') {
      return document.documentElement.classList.contains('dark');
    }
    return false;
  });

  // Apply dark mode class to html element when state changes
  useEffect(() => {
    if (darkMode) {
      document.documentElement.classList.add('dark');
      localStorage.setItem('theme', 'dark');
    } else {
      document.documentElement.classList.remove('dark');
      localStorage.setItem('theme', 'light');
    }
  }, [darkMode]);

  // Initialize theme from localStorage or system preference
  useEffect(() => {
    const savedTheme = localStorage.getItem('theme');
    const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    
    if (savedTheme === 'dark' || (!savedTheme && systemPrefersDark)) {
      setDarkMode(true);
    }
  }, []);

  return (
    <div className="flex-1 flex flex-col bg-background h-full">
      <div className="p-8">
        <div className="max-w-3xl mx-auto">
          <div className="mb-8">
            <h1 className="text-3xl font-bold text-on-surface">Settings</h1>
            <p className="text-on-surface-variant mt-2">
              Manage your preferences and application settings
            </p>
          </div>

          <div className="bg-surface-container-lowest rounded-2xl shadow-[0px_12px_32px_rgba(11,28,48,0.06)] p-6">
            <div className="border-b border-outline-variant pb-4 mb-6">
              <h2 className="text-xl font-bold text-on-surface">Appearance</h2>
            </div>

            <div className="flex items-center justify-between py-4">
              <div>
                <h3 className="font-bold text-on-surface">Dark Mode</h3>
                <p className="text-on-surface-variant text-sm mt-1">
                  Toggle between light and dark themes
                </p>
              </div>
              <button
                onClick={() => setDarkMode(!darkMode)}
                className={`relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none ${
                  darkMode ? "bg-primary" : "bg-outline-variant"
                }`}
              >
                <span
                  className={`inline-block h-4 w-4 transform rounded-full bg-on-primary-container transition-transform ${
                    darkMode ? "translate-x-6" : "translate-x-1"
                  }`}
                />
              </button>
            </div>

            <div className="flex items-center justify-between py-4 border-t border-outline-variant mt-4">
              <div>
                <h3 className="font-bold text-on-surface">Theme Preview</h3>
                <p className="text-on-surface-variant text-sm mt-1">
                  Current theme: {darkMode ? "Dark" : "Light"}
                </p>
              </div>
              <div className="flex items-center gap-2">
                <div className={`w-8 h-8 rounded-full ${darkMode ? 'bg-surface-container-high' : 'bg-surface'}`}></div>
                <div className={`w-8 h-8 rounded-full ${darkMode ? 'bg-surface-container' : 'bg-surface-variant'}`}></div>
                <div className={`w-8 h-8 rounded-full ${darkMode ? 'bg-surface-container-lowest' : 'bg-background'}`}></div>
              </div>
            </div>
          </div>

          <div className="bg-surface-container-lowest rounded-2xl shadow-[0px_12px_32px_rgba(11,28,48,0.06)] p-6 mt-6">
            <div className="border-b border-outline-variant pb-4 mb-6">
              <h2 className="text-xl font-bold text-on-surface">Account</h2>
            </div>

            <div className="py-4">
              <h3 className="font-bold text-on-surface">User Information</h3>
              <div className="flex items-center gap-4 mt-4">
                <div className="w-16 h-16 rounded-full bg-primary flex items-center justify-center text-on-primary font-bold text-xl">
                  A
                </div>
                <div>
                  <p className="font-bold text-on-surface">
                    Admin User
                  </p>
                  <p className="text-on-surface-variant text-sm">
                    Master Admin
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
