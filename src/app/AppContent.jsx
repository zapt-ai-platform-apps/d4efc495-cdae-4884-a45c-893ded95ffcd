import React from 'react';
import { api as uiApi } from '@/modules/ui/api';

/**
 * Main application content
 * This is the core component that renders the app UI
 */
export function AppContent() {
  const { Button, ZaptBadge } = uiApi;
  
  return (
    <div className="min-h-screen flex items-center justify-center bg-gray-100 text-gray-800">
      <div className="p-6 bg-white rounded shadow">
        <h1 className="text-2xl font-bold mb-4">App Template</h1>
        <p className="mb-4">Welcome to the modular React app template</p>
        <Button 
          label="Click Me" 
          onClick={() => alert('Button clicked!')}
        />
      </div>
      <ZaptBadge />
    </div>
  );
}