/**
 * Mayush Design System - Token & Theme Unit Assertions
 */

import { colors } from '../tokens/colors';
import { typographyStyles } from '../tokens/typography';
import { createTheme } from '../theme/theme';
import { decodeHtmlEntities } from '../components/typography/HtmlContent';

export const runTokenAssertions = () => {
  if (colors.brand.orange500 !== '#D97434') {
    throw new Error('Assertion failed: orange500 color mismatch');
  }
  if (colors.brand.navy900 !== '#1F2A3A') {
    throw new Error('Assertion failed: navy900 color mismatch');
  }
  if (colors.surface.cream !== '#F2E8DA') {
    throw new Error('Assertion failed: cream color mismatch');
  }

  if (typographyStyles.display.fontSize !== 32) {
    throw new Error('Assertion failed: display font size mismatch');
  }

  const themeFr = createTheme('fr');
  if (themeFr.language !== 'fr' || themeFr.isRTL !== false) {
    throw new Error('Assertion failed: French theme should be LTR');
  }

  const themeAr = createTheme('ar');
  if (themeAr.language !== 'ar' || themeAr.isRTL !== true) {
    throw new Error('Assertion failed: Arabic theme should be RTL');
  }

  const decoded = decodeHtmlEntities('&Eacute;l&eacute;gance &amp; Confort &bull; 100&euro;');
  if (decoded !== 'Élégance & Confort • 100€') {
    throw new Error(`Assertion failed: decodeHtmlEntities failed, got "${decoded}"`);
  }

  console.log('All token assertions passed');
};

