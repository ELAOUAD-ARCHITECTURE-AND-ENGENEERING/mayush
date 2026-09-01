/**
 * Mayush Design System - MayushToast Component
 */

import React from 'react';
import { View } from 'react-native';
import { MayushText } from '../typography/MayushText';
import { colors } from '../../tokens/colors';
import { radii } from '../../tokens/radii';
import { spacing } from '../../tokens/spacing';

export interface MayushToastProps {
  message: string;
  type?: 'success' | 'error' | 'info';
  visible: boolean;
}

export const MayushToast: React.FC<MayushToastProps> = ({
  message,
  type = 'info',
  visible,
}) => {
  if (!visible) return null;

  const backgroundColor =
    type === 'success'
      ? colors.semantic.success
      : type === 'error'
      ? colors.semantic.error
      : colors.brand.navy900;

  return (
    <View
      style={{
        position: 'absolute',
        bottom: 80,
        left: spacing.lg,
        right: spacing.lg,
        backgroundColor,
        borderRadius: radii.md,
        paddingHorizontal: spacing.lg,
        paddingVertical: spacing.md,
        alignItems: 'center',
        justifyContent: 'center',
        elevation: 6,
        zIndex: 2000,
      }}
    >
      <MayushText variant="smallBody" color={colors.surface.white} align="center">
        {message}
      </MayushText>
    </View>
  );
};
