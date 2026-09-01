/**
 * Mayush Design System - MayushText Component
 */

import React from 'react';
import { Text, TextStyle, StyleProp, TextProps as RNTextProps } from 'react-native';
import { fontFamilies, typographyStyles } from '../../tokens/typography';
import { colors } from '../../tokens/colors';
import { useTheme } from '../../theme/useTheme';

export interface MayushTextProps extends RNTextProps {
  variant?: keyof typeof typographyStyles;
  color?: string;
  align?: 'auto' | 'left' | 'right' | 'center' | 'justify';
  style?: StyleProp<TextStyle>;
  children: React.ReactNode;
}

export const MayushText: React.FC<MayushTextProps> = ({
  variant = 'body',
  color = colors.brand.navy900,
  align,
  style,
  children,
  ...rest
}) => {
  const { isRTL } = useTheme();

  const defaultAlign = align || (isRTL ? 'right' : 'left');
  const baseStyle = typographyStyles[variant] || typographyStyles.body;
  const textWeight = baseStyle.fontWeight === '700'
    ? 'bold'
    : baseStyle.fontWeight === '600'
      ? 'semiBold'
      : baseStyle.fontWeight === '500'
        ? 'medium'
        : 'regular';
  const fontFamily = isRTL
    ? fontFamilies.arabic[textWeight]
    : variant === 'display' || variant === 'pageTitle'
      ? fontFamilies.display[textWeight === 'medium' ? 'regular' : textWeight === 'semiBold' ? 'semiBold' : textWeight === 'bold' ? 'bold' : 'regular']
      : fontFamilies.latin[textWeight];

  return (
    <Text
      style={[
        baseStyle,
        {
          color,
          textAlign: defaultAlign,
          fontFamily,
          // Each loaded font is already a weight-specific face. Avoid synthetic
          // weights on Android; explicit screen styles may still opt in later.
          fontWeight: undefined,
        },
        style,
      ]}
      {...rest}
    >
      {children}
    </Text>
  );
};
