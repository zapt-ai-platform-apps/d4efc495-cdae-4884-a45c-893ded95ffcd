import React from 'react';
import { validateZaptBadgeProps } from '../../validators';

/**
 * ZAPT badge component
 * Displays a "Made on ZAPT" badge with link to ZAPT website
 * 
 * @param {Object} props
 * @param {string} [props.className] - Additional CSS classes
 */
export function ZaptBadge(props) {
  // Validate props in development
  if (process.env.NODE_ENV === 'development') {
    validateZaptBadgeProps(props, {
      actionName: 'render',
      location: 'ZaptBadge component',
      direction: 'incoming',
      moduleFrom: 'client',
      moduleTo: 'ui'
    });
  }
  
  const { className = '' } = props;
  
  return (
    <a 
      href="https://www.zapt.ai"
      target="_blank"
      rel="noopener noreferrer"
      className={`fixed bottom-4 right-4 text-sm text-gray-600 hover:text-gray-800 transition-colors ${className}`}
    >
      Made on ZAPT
    </a>
  );
}