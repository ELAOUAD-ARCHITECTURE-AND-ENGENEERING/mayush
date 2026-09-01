/**
 * Mayush Design System - Opacity Tokens
 */

export const opacity = {
  transparent: 0,
  disabled: 0.4,
  subtle: 0.6,
  pressed: 0.8,
  full: 1,
} as const;

export type OpacityToken = typeof opacity;
