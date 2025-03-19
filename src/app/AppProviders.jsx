import React from 'react';

/**
 * Application providers component
 * Wraps the app with context providers for global state and services
 * 
 * @param {Object} props
 * @param {React.ReactNode} props.children - Child components to render
 */
export function AppProviders({ children }) {
  // Add any providers here (e.g., theme, auth, etc.)
  return (
    <>
      {children}
    </>
  );
}