import { z } from 'zod';
import { createValidator } from '@/modules/core/validators';

/**
 * App-specific validation schemas and validators
 */

// Example schema for app configuration
export const appConfigSchema = z.object({
  title: z.string(),
  version: z.string(),
  environment: z.enum(['development', 'production']),
});

export const validateAppConfig = createValidator(appConfigSchema, 'AppConfig');