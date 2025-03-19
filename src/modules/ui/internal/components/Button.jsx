import React from 'react';
import { validateButtonProps } from '../../validators';

/**
 * Button component
 * 
 * @param {Object} props
 * @param {string} props.label - Button text
 * @param {Function} [props.onClick] - Click handler
 * @param {boolean} [props.disabled] - Whether button is disabled
 * @param {string} [props.className] - Additional CSS classes
 */
export function Button(props) {
  // Validate props in development
  if (process.env.NODE_ENV === 'development') {
    validateButtonProps(props, {
      actionName: 'render',
      location: 'Button component',
      direction: 'incoming',
      moduleFrom: 'client',
      moduleTo: 'ui'
    });
  }
  
  const { label, onClick, disabled = false, className = '' } = props;
  
  return (
    <button 
      onClick={onClick}
      disabled={disabled}
      className={`px-4 py-2 bg-blue-500 text-white rounded cursor-pointer hover:bg-blue-600 
        ${disabled ? 'opacity-50 cursor-not-allowed' : ''} ${className}`}
    >
      {label}
    </button>
  );
}