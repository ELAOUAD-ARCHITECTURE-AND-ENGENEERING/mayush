/**
 * Mayush Design System - Color Tokens
 * Derived directly from foundation boards and figma handoff guidelines.
 */

export const colors = {
  // Brand Colors
  brand: {
    orange500: '#FF7900', // Primary Mayush Orange sampled from the reference set
    orange600: '#D96400', // Pressed / Active Dark Orange
    orange100: '#FFF0DE', // Soft Orange Tint / Badge Background
    navy900: '#101D35',   // Deep Navy / Primary Dark Text
    navy700: '#263653',   // Secondary Navy / Headers
  },

  // Surface Colors
  surface: {
    cream: '#FFF9F1',      // Warm Cream Background
    creamLight: '#FFFCF8', // Soft Card Cream
    white: '#FFFFFF',      // Primary White Surface
    borderWarm: '#EEE7DE', // Warm Border Accent
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
    success: '#12B76A',
    successBackground: '#ECFDF3',
    error: '#D92D20',
    errorBackground: '#FEF3F2',
    warning: '#F5B041',
    warningBackground: '#FEF0C7',
    info: '#079455',
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
  }
} as const;

export type ColorsToken = typeof colors;
