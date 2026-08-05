/**
 * Mayush Design System - Typography Tokens
 * Supports Latin (French) and Arabic typography scales.
 */

export const fontFamilies = {
  latin: {
    regular: 'System', // System fallback or Inter
    medium: 'System',
    semiBold: 'System',
    bold: 'System',
  },
  arabic: {
    regular: 'System', // System fallback or Cairo/Tajawal
    medium: 'System',
    semiBold: 'System',
    bold: 'System',
  }
} as const;

export const fontSizes = {
  xs: 11,
  sm: 13,
  md: 15,
  lg: 17,
  xl: 20,
  xxl: 24,
  display: 30,
} as const;

export const fontWeights = {
  regular: '400' as const,
  medium: '500' as const,
  semiBold: '600' as const,
  bold: '700' as const,
};

export const lineHeights = {
  xs: 15,
  sm: 18,
  md: 22,
  lg: 24,
  xl: 28,
  xxl: 32,
  display: 38,
} as const;

export const typographyStyles = {
  display: {
    fontSize: fontSizes.display,
    lineHeight: lineHeights.display,
    fontWeight: fontWeights.bold,
  },
  pageTitle: {
    fontSize: fontSizes.xxl,
    lineHeight: lineHeights.xxl,
    fontWeight: fontWeights.bold,
  },
  sectionTitle: {
    fontSize: fontSizes.xl,
    lineHeight: lineHeights.xl,
    fontWeight: fontWeights.semiBold,
  },
  cardTitle: {
    fontSize: fontSizes.lg,
    lineHeight: lineHeights.lg,
    fontWeight: fontWeights.medium,
  },
  body: {
    fontSize: fontSizes.md,
    lineHeight: lineHeights.md,
    fontWeight: fontWeights.regular,
  },
  strongBody: {
    fontSize: fontSizes.md,
    lineHeight: lineHeights.md,
    fontWeight: fontWeights.semiBold,
  },
  smallBody: {
    fontSize: fontSizes.sm,
    lineHeight: lineHeights.sm,
    fontWeight: fontWeights.regular,
  },
  caption: {
    fontSize: fontSizes.xs,
    lineHeight: lineHeights.xs,
    fontWeight: fontWeights.regular,
  },
  button: {
    fontSize: fontSizes.md,
    lineHeight: lineHeights.md,
    fontWeight: fontWeights.semiBold,
  },
  priceLarge: {
    fontSize: fontSizes.xl,
    lineHeight: lineHeights.xl,
    fontWeight: fontWeights.bold,
  },
  priceRegular: {
    fontSize: fontSizes.md,
    lineHeight: lineHeights.md,
    fontWeight: fontWeights.bold,
  },
  badge: {
    fontSize: fontSizes.xs,
    lineHeight: lineHeights.xs,
    fontWeight: fontWeights.semiBold,
  },
  inputLabel: {
    fontSize: fontSizes.sm,
    lineHeight: lineHeights.sm,
    fontWeight: fontWeights.medium,
  },
} as const;

export type TypographyToken = typeof typographyStyles;
