/**
 * Mayush Design System - Skeleton Component
 */

import React from 'react';
import { View, ViewStyle, StyleProp } from 'react-native';
import { colors } from '../../tokens/colors';
import { radii } from '../../tokens/radii';

export interface SkeletonProps {
  width?: number | `${number}%`;
  height?: number;
  borderRadius?: keyof typeof radii;
  style?: StyleProp<ViewStyle>;
}

export const Skeleton: React.FC<SkeletonProps> = ({
  width = '100%',
  height = 20,
  borderRadius = 'sm',
  style,
}) => {
  return (
    <View
      style={[
        {
          width,
          height,
          borderRadius: radii[borderRadius],
          backgroundColor: colors.surface.borderWarm,
          opacity: 0.6,
        },
        style,
      ]}
    />
  );
};
