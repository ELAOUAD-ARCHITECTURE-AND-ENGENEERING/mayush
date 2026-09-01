/**
 * CartToast Component (Figma Node 309:659 - 05-cart-quantity-update-toast-fr)
 * Floating toast feedback banner when item quantity or variant is updated in cart.
 */

import React from 'react';
import { ActivityIndicator, StyleSheet, View } from 'react-native';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { colors } from '../../design-system/tokens/colors';
import { radii } from '../../design-system/tokens/radii';

export interface CartToastProps {
  visible: boolean;
  message: string;
}

export const CartToast: React.FC<CartToastProps> = ({ visible, message }) => {
  if (!visible) return null;

  return (
    <View style={styles.toastCard} accessibilityLabel="Cart Update Toast">
      <ActivityIndicator size="small" color={colors.brand.orange500} />
      <MayushText variant="strongBody" color={colors.brand.navy900} style={styles.message}>
        {message}
      </MayushText>
    </View>
  );
};

const styles = StyleSheet.create({
  toastCard: {
    position: 'absolute',
    top: '42%',
    left: 62,
    right: 62,
    zIndex: 999,
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 16,
    paddingVertical: 12,
    borderRadius: radii.xl,
    backgroundColor: colors.surface.white,
    gap: 10,
    elevation: 6,
    shadowColor: '#000',
    shadowOpacity: 0.18,
    shadowRadius: 8,
    shadowOffset: { width: 0, height: 4 },
  },
  message: { flex: 1, fontWeight: '700', textAlign: 'center' },
});
