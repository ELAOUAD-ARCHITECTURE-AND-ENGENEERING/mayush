/**
 * Mayush Design System - Theme Instance Creator
 */

import { Theme } from './types';
import { colors } from '../tokens/colors';
import { typographyStyles, fontFamilies } from '../tokens/typography';
import { spacing } from '../tokens/spacing';
import { radii } from '../tokens/radii';
import { borders } from '../tokens/borders';
import { shadows } from '../tokens/shadows';
import { opacity } from '../tokens/opacity';
import { sizing } from '../tokens/sizing';
import { motion } from '../tokens/motion';
import { zIndex } from '../tokens/zIndex';
import { MvpAppLanguage } from '../../contracts/api/dto';

export const createTheme = (
  language: MvpAppLanguage = 'fr',
  logoUrl?: string
): Theme => {
  const isRTL = language === 'ar';
  return {
    colors,
    typography: typographyStyles,
    fontFamilies,
    spacing,
    radii,
    borders,
    shadows,
    opacity,
    sizing,
    motion,
    zIndex,
    language,
    isRTL,
    logoUrl,
  };
};

export const defaultTheme = createTheme('fr');
