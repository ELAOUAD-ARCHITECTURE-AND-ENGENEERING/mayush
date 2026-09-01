/**
 * Mayush Design System - Border Tokens
 */

import { colors } from './colors';

export const borders = {
  width: {
    none: 0,
    thin: 1,
    thick: 2,
  },
  color: {
    light: colors.neutral.gray300,
    warm: colors.surface.borderWarm,
    primary: colors.brand.orange500,
    error: colors.semantic.error,
  }
} as const;

export type BordersToken = typeof borders;
