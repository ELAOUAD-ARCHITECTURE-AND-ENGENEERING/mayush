/**
 * Mayush Design System - Card Component
 */

import React from 'react';
import { View, ViewStyle, StyleProp } from 'react-native';
import { colors } from '../../tokens/colors';
import { radii } from '../../tokens/radii';
import { spacing } from '../../tokens/spacing';
import { shadows } from '../../tokens/shadows';

export interface CardProps {
  padding?: keyof typeof spacing;
  radius?: keyof typeof radii;
  shadow?: keyof typeof shadows;
  backgroundColor?: string;
  borderColor?: string;
  borderWidth?: number;
  style?: StyleProp<ViewStyle>;
  children: React.ReactNode;
}

export const Card: React.FC<CardProps> = ({
  padding = 'lg',
  radius = 'xl',
  shadow = 'sm',
  backgroundColor = colors.surface.white,
  borderColor = colors.surface.borderWarm,
  borderWidth = 1,
  style,
  children,
}) => {
  return (
    <View
      style={[
        {
          padding: spacing[padding],
          borderRadius: radii[radius],
          backgroundColor,
          borderColor,
          borderWidth,
        },
        shadows[shadow],
        style,
      ]}
    >
      {children}
    </View>
  );
};
