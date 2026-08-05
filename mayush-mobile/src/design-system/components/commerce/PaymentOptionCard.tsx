/**
 * Mayush Design System - PaymentOptionCard Component
 */

import React from 'react';
import { View, TouchableOpacity } from 'react-native';
import { Card } from '../layout/Card';
import { Inline } from '../layout/Inline';
import { MayushText } from '../typography/MayushText';
import { colors } from '../../tokens/colors';
import { radii } from '../../tokens/radii';
import { spacing } from '../../tokens/spacing';

export interface PaymentOptionCardProps {
  title: string;
  description: string;
  selected: boolean;
  onSelect: () => void;
  disabled?: boolean;
}

export const PaymentOptionCard: React.FC<PaymentOptionCardProps> = ({
  title,
  description,
  selected,
  onSelect,
  disabled = false,
}) => {
  return (
    <TouchableOpacity
      activeOpacity={0.85}
      onPress={onSelect}
      disabled={disabled}
      style={{ width: '100%', opacity: disabled ? 0.4 : 1 }}
    >
      <Card
        padding="md"
        radius="lg"
        borderColor={selected ? colors.brand.orange500 : colors.surface.borderWarm}
        borderWidth={selected ? 2 : 1}
        backgroundColor={selected ? colors.surface.creamLight : colors.surface.white}
      >
        <Inline space="md" align="center" justify="space-between">
          <View style={{ flex: 1 }}>
            <MayushText variant="strongBody" color={colors.brand.navy900}>
              {title}
            </MayushText>
            <MayushText variant="smallBody" color={colors.neutral.gray700} style={{ marginTop: spacing.xxs }}>
              {description}
            </MayushText>
          </View>

          <View
            style={{
              width: 20,
              height: 20,
              borderRadius: radii.full,
              borderWidth: selected ? 6 : 2,
              borderColor: selected ? colors.brand.orange500 : colors.neutral.gray500,
            }}
          />
        </Inline>
      </Card>
    </TouchableOpacity>
  );
};
