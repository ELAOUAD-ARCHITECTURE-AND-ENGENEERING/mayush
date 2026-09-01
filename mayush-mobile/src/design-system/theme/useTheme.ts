/**
 * Mayush Design System - useTheme Hook
 */

import { useContext } from 'react';
import { ThemeContext, ThemeContextValue } from './ThemeProvider';

export const useTheme = (): ThemeContextValue => {
  const context = useContext(ThemeContext);
  if (!context) {
    throw new Error('useTheme must be used within a Mayush ThemeProvider');
  }
  return context;
};
