/**
 * Mayush Design System - Theme Provider
 * Dynamically resolves theme state, LTR/RTL locale, and admin backend logo URL.
 */

import React, { createContext, useState, useEffect, ReactNode } from 'react';
import { Theme } from './types';
import { createTheme } from './theme';
import { MvpAppLanguage } from '../../contracts/api/dto';

export interface ThemeContextValue {
  theme: Theme;
  language: MvpAppLanguage;
  setLanguage: (lang: MvpAppLanguage) => void;
  isRTL: boolean;
  logoUrl?: string;
  setLogoUrl: (url?: string) => void;
}

export const ThemeContext = createContext<ThemeContextValue | undefined>(undefined);

export interface ThemeProviderProps {
  initialLanguage?: MvpAppLanguage;
  initialLogoUrl?: string;
  children: ReactNode;
}

export const ThemeProvider: React.FC<ThemeProviderProps> = ({
  initialLanguage = 'fr',
  initialLogoUrl,
  children,
}) => {
  const [language, setLanguage] = useState<MvpAppLanguage>(initialLanguage);
  const [logoUrl, setLogoUrl] = useState<string | undefined>(initialLogoUrl);
  const [theme, setTheme] = useState<Theme>(() => createTheme(initialLanguage, initialLogoUrl));

  useEffect(() => {
    setTheme(createTheme(language, logoUrl));
  }, [language, logoUrl]);

  useEffect(() => {
    setLanguage(initialLanguage);
  }, [initialLanguage]);

  const value: ThemeContextValue = {
    theme,
    language,
    setLanguage,
    isRTL: language === 'ar',
    logoUrl,
    setLogoUrl,
  };

  return (
    <ThemeContext.Provider value={value}>
      {children}
    </ThemeContext.Provider>
  );
};
