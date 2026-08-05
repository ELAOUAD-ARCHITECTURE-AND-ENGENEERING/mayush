/**
 * Mayush Design System - Theme Types
 */

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

export interface Theme {
  colors: typeof colors;
  typography: typeof typographyStyles;
  fontFamilies: typeof fontFamilies;
  spacing: typeof spacing;
  radii: typeof radii;
  borders: typeof borders;
  shadows: typeof shadows;
  opacity: typeof opacity;
  sizing: typeof sizing;
  motion: typeof motion;
  zIndex: typeof zIndex;
  language: MvpAppLanguage;
  isRTL: boolean;
  logoUrl?: string;
}
