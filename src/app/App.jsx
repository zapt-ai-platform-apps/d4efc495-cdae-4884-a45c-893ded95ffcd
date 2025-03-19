import React from 'react';
import { AppProviders } from './AppProviders';
import { AppContent } from './AppContent';

/**
 * Root App component
 * Wraps the app content with necessary providers
 */
export function App() {
  return (
    <AppProviders>
      <AppContent />
    </AppProviders>
  );
}