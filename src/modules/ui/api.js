/**
 * Public API for the UI module
 * Exports UI components and utilities that can be used by other modules
 */

import { Button } from './internal/components/Button';
import { ZaptBadge } from './internal/components/ZaptBadge';

export const api = {
  // UI components that are publicly available to other modules
  Button,
  ZaptBadge,
};