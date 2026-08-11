/**
 * QuantityStepper Component
 * Increments/decrements cart item quantity with stock boundary limits.
 */

import React from 'react';
import { StyleSheet, TouchableOpacity, View } from 'react-native';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { colors } from '../../design-system/tokens/colors';
import { radii } from '../../design-system/tokens/radii';

export interface QuantityStepperProps {
  quantity: number;
  minQty?: number;
  maxQty?: number;
  onIncrement: () => void;
  onDecrement: () => void;
}

export const QuantityStepper: React.FC<QuantityStepperProps> = ({
  quantity,
  minQty = 1,
  maxQty = 99,
  onIncrement,
  onDecrement,
}) => {
  return (
    <View style={styles.stepperContainer} accessibilityLabel="Quantity Stepper">
      <TouchableOpacity
        style={[styles.stepperBtn, quantity <= minQty && styles.btnDisabled]}
        onPress={onDecrement}
        disabled={quantity <= minQty}
      >
        <MayushIcon name="minus" size={16} color={quantity <= minQty ? colors.neutral.gray500 : colors.brand.navy900} />
      </TouchableOpacity>

      <MayushText variant="strongBody" color={colors.brand.navy900} style={styles.countText}>
        {quantity}
      </MayushText>

      <TouchableOpacity
        style={[styles.stepperBtn, quantity >= maxQty && styles.btnDisabled]}
        onPress={onIncrement}
        disabled={quantity >= maxQty}
      >
        <MayushIcon name="plus" size={16} color={quantity >= maxQty ? colors.neutral.gray500 : colors.brand.navy900} />
      </TouchableOpacity>
    </View>
  );
};

const styles = StyleSheet.create({
  stepperContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
    borderWidth: 1,
    borderColor: colors.surface.borderWarm,
    borderRadius: radii.md,
    paddingHorizontal: 6,
    paddingVertical: 2,
    backgroundColor: colors.surface.white,
  },
  stepperBtn: { padding: 4 },
  btnDisabled: { opacity: 0.4 },
  countText: { fontSize: 14, minWidth: 16, textAlign: 'center' },
});
