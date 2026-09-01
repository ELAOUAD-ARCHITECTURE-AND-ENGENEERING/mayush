/**
 * Mayush Design System - Divider Component
 */

import React from 'react';
import { View, ViewStyle, StyleProp } from 'react-native';
import { colors } from '../../tokens/colors';
import { spacing } from '../../tokens/spacing';

export interface DividerProps {
  color?: string;
  thickness?: number;
  marginVertical?: keyof typeof spacing;
  style?: StyleProp<ViewStyle>;
}

export const Divider: React.FC<DividerProps> = ({
  color = colors.surface.borderWarm,
  thickness = 1,
  marginVertical = 'md',
  style,
}) => {
  return (
    <View
      style={[
        {
          height: thickness,
          backgroundColor: color,
          marginVertical: spacing[marginVertical],
          width: '100%',
        },
        style,
      ]}
    />
  );
};
