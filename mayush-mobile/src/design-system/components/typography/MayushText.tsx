/**
 * Mayush Design System - MayushText Component
 */

import React from 'react';
import { Text, TextStyle, StyleProp, TextProps as RNTextProps } from 'react-native';
import { typographyStyles } from '../../tokens/typography';
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

  return (
    <Text
      style={[
        baseStyle,
        {
          color,
          textAlign: defaultAlign,
        },
        style,
      ]}
      {...rest}
    >
      {children}
    </Text>
  );
};
