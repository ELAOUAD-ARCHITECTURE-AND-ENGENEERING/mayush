/**
 * Mayush Design System - Radii Tokens
 */

export const radii = {
  none: 0,
  xs: 4,
  sm: 8,
  md: 10,
  lg: 12,
  xl: 16,
  xxl: 24,
  full: 9999,
} as const;

export type RadiiToken = typeof radii;
