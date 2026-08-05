/**
 * Mayush Design System - zIndex Tokens
 */

export const zIndex = {
  base: 0,
  card: 10,
  header: 100,
  overlay: 500,
  modal: 1000,
  toast: 2000,
} as const;

export type ZIndexToken = typeof zIndex;
