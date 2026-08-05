/**
 * Mayush Design System - VariantChip Component
 */

import React from 'react';
import { TouchableOpacity } from 'react-native';
import { MayushText } from '../typography/MayushText';
import { colors } from '../../tokens/colors';
import { radii } from '../../tokens/radii';
import { spacing } from '../../tokens/spacing';

export interface VariantChipProps {
  label: string;
  selected: boolean;
  onPress: () => void;
  disabled?: boolean;
}

export const VariantChip: React.FC<VariantChipProps> = ({
  label,
  selected,
  onPress,
  disabled = false,
}) => {
  const backgroundColor = selected
    ? colors.brand.orange100
    : colors.surface.white;
  const borderColor = selected
    ? colors.brand.orange500
    : colors.neutral.gray300;
  const textColor = selected
    ? colors.brand.orange500
    : colors.brand.navy900;

  return (
    <TouchableOpacity
      activeOpacity={0.8}
      onPress={onPress}
      disabled={disabled}
      style={{
        paddingHorizontal: spacing.md,
        paddingVertical: spacing.xs,
        borderRadius: radii.md,
        borderWidth: selected ? 2 : 1,
        borderColor,
        backgroundColor,
        opacity: disabled ? 0.4 : 1,
      }}
    >
      <MayushText variant="smallBody" color={textColor} style={{ fontWeight: selected ? '600' : '400' }}>
        {label}
      </MayushText>
    </TouchableOpacity>
  );
};
