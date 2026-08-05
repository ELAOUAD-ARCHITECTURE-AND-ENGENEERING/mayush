/**
 * Mayush Design System - Motion Tokens
 */

export const motion = {
  duration: {
    fast: 150,
    normal: 250,
    slow: 400,
  }
} as const;

export type MotionToken = typeof motion;
