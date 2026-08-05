/**
 * Mayush Design System - Token & Theme Unit Assertions
 */

import { colors } from '../tokens/colors';
import { typographyStyles } from '../tokens/typography';
import { createTheme } from '../theme/theme';

export const runTokenAssertions = () => {
  if (colors.brand.orange500 !== '#FF7900') {
    throw new Error('Assertion failed: orange500 color mismatch');
  }
  if (colors.brand.navy900 !== '#101D35') {
    throw new Error('Assertion failed: navy900 color mismatch');
  }
  if (colors.surface.cream !== '#FFF9F1') {
    throw new Error('Assertion failed: cream color mismatch');
  }

  if (typographyStyles.display.fontSize !== 30) {
    throw new Error('Assertion failed: display font size mismatch');
  }

  const themeFr = createTheme('fr');
  if (themeFr.language !== 'fr' || themeFr.isRTL !== false) {
    throw new Error('Assertion failed: French theme mismatch');
  }

  const themeAr = createTheme('ar');
  if (themeAr.language !== 'ar' || themeAr.isRTL !== true) {
    throw new Error('Assertion failed: Arabic theme mismatch');
  }

  return true;
};
