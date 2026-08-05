/**
 * Mayush Design System - QuantityStepper Component
 */

import React from 'react';
import { TouchableOpacity } from 'react-native';
import { Inline } from '../layout/Inline';
import { MayushText } from '../typography/MayushText';
import { colors } from '../../tokens/colors';
import { radii } from '../../tokens/radii';
import { spacing } from '../../tokens/spacing';

export interface QuantityStepperProps {
  value: number;
  min?: number;
  max?: number;
  onIncrement: () => void;
  onDecrement: () => void;
  disabled?: boolean;
}

export const QuantityStepper: React.FC<QuantityStepperProps> = ({
  value,
  min = 1,
  max = 99,
  onIncrement,
  onDecrement,
  disabled = false,
}) => {
  const canDecrement = !disabled && value > min;
  const canIncrement = !disabled && value < max;

  return (
    <Inline
      space="xs"
      align="center"
      style={{
        backgroundColor: colors.neutral.gray100,
        borderRadius: radii.sm,
        padding: spacing.xxs,
        borderWidth: 1,
        borderColor: colors.neutral.gray300,
      }}
    >
      <TouchableOpacity
        onPress={onDecrement}
        disabled={!canDecrement}
        style={{
          width: 32,
          height: 32,
          alignItems: 'center',
          justifyContent: 'center',
          opacity: canDecrement ? 1 : 0.4,
        }}
      >
        <MayushText variant="strongBody" color={colors.brand.navy900}>
          -
        </MayushText>
      </TouchableOpacity>

      <MayushText variant="strongBody" color={colors.brand.navy900} style={{ paddingHorizontal: spacing.sm }}>
        {value}
      </MayushText>

      <TouchableOpacity
        onPress={onIncrement}
        disabled={!canIncrement}
        style={{
          width: 32,
          height: 32,
          alignItems: 'center',
          justifyContent: 'center',
          opacity: canIncrement ? 1 : 0.4,
        }}
      >
        <MayushText variant="strongBody" color={colors.brand.navy900}>
          +
        </MayushText>
      </TouchableOpacity>
    </Inline>
  );
};
