/**
 * Mayush Design System - CountBadge Component
 */

import React from 'react';
import { View } from 'react-native';
import { MayushText } from '../typography/MayushText';
import { colors } from '../../tokens/colors';
import { radii } from '../../tokens/radii';

export interface CountBadgeProps {
  count: number;
}

export const CountBadge: React.FC<CountBadgeProps> = ({ count }) => {
  if (count <= 0) return null;

  const displayCount = count > 99 ? '99+' : String(count);

  return (
    <View
      style={{
        backgroundColor: colors.semantic.error,
        borderRadius: radii.full,
        minWidth: 18,
        height: 18,
        paddingHorizontal: 4,
        alignItems: 'center',
        justifyContent: 'center',
      }}
    >
      <MayushText variant="caption" color={colors.surface.white} style={{ fontSize: 10, lineHeight: 12 }}>
        {displayCount}
      </MayushText>
    </View>
  );
};
