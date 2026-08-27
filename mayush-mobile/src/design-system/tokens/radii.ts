/**
 * Mayush Design System - Radii Tokens
 */

export const radii = {
  none: 0,
  xs: 4,
  sm: 8,
  md: 12,
  lg: 16,
  xl: 18,
  xxl: 24,
  pill: 9999,
  full: 9999,
} as const;

export type RadiiToken = typeof radii;
