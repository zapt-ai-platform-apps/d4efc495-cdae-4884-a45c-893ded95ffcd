import { z } from 'zod';
import { createValidator } from '@/modules/core/validators';

/**
 * Button props schema for validation
 */
export const buttonPropsSchema = z.object({
  label: z.string(),
  onClick: z.function().optional(),
  disabled: z.boolean().optional(),
  className: z.string().optional(),
});

export const validateButtonProps = createValidator(buttonPropsSchema, 'ButtonProps');

/**
 * ZaptBadge props schema for validation
 */
export const zaptBadgePropsSchema = z.object({
  className: z.string().optional(),
});

export const validateZaptBadgeProps = createValidator(zaptBadgePropsSchema, 'ZaptBadgeProps');