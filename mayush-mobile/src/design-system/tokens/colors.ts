/**
 * Mayush Design System - Color Tokens
 * Aligned directly with MAYUSH_FIGMA_DESIGN_GUIDELINES.md Section 5.
 */

export const colors = {
  // Brand Colors
  brand: {
    orange500: '#D97434', // Primary Mayush Orange (Figma guidelines source of truth)
    orange600: '#C66528', // Pressed / Active Dark Orange
    orange100: '#F8E6D7', // Soft Orange Tint / Badge Background
    navy900: '#1F2A3A',   // Deep Navy / Primary Dark Text
    navy700: '#344154',   // Secondary Navy / Headers
  },

  // Surface Colors
  surface: {
    cream: '#F2E8DA',      // Warm Cream Background
    creamLight: '#FAF6F0', // Soft Card Cream
    white: '#FFFFFF',      // Primary White Surface
    borderWarm: '#E7DED3', // Warm Border Accent
  },

  // Neutral Colors
  neutral: {
    black: '#111111',
    gray900: '#1F2A3A',
    gray700: '#475467',
    gray500: '#A7AFBA',
    gray300: '#D9DEE4',
    gray100: '#F3F5F7',
    white: '#FFFFFF',
  },

  // Semantic Feedback Colors
  semantic: {
    success: '#2E8B57',
    successBackground: '#E7F4EC',
    error: '#C7473A',
    errorBackground: '#F8E5E2',
    warning: '#D98A24',
    warningBackground: '#FAEFD9',
    info: '#3F6D94',
  },

  // Interactive Component States
  interactive: {
    primaryDefault: '#D97434',
    primaryPressed: '#C66528',
    primaryDisabled: '#F8E6D7',
    textPrimary: '#1F2A3A',
    textSecondary: '#475467',
    textMuted: '#A7AFBA',
    textInverse: '#FFFFFF',
  },
} as const;

export type ColorsToken = typeof colors;
