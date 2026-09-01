/**
 * CartEmptyState Component (Figma Node 309:668 - 05-cart-empty-state-fr)
 * Empty cart view with discovery CTA.
 */

import React from 'react';
import { StyleSheet, TouchableOpacity, View } from 'react-native';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { colors } from '../../design-system/tokens/colors';
import { radii } from '../../design-system/tokens/radii';

export interface CartEmptyStateProps {
  onStartShopping?: () => void;
}

export const CartEmptyState: React.FC<CartEmptyStateProps> = ({ onStartShopping }) => {
  return (
    <View style={styles.emptyCard} accessibilityLabel="Cart Empty State">
      <View style={styles.iconCircle}>
        <MayushIcon name="shopping-cart" size={48} color={colors.brand.orange500} />
      </View>
      <MayushText variant="pageTitle" color={colors.brand.navy900} align="center" style={styles.emptyTitle}>
        Votre panier est vide
      </MayushText>
      <MayushText variant="body" color={colors.neutral.gray700} align="center" style={styles.emptyCopy}>
        Vous n’avez encore rien ajouté à votre panier. Découvrez nos collections artisanales d'exception.
      </MayushText>
      <TouchableOpacity onPress={onStartShopping} style={styles.browseBtn}>
        <MayushText variant="button" color={colors.surface.white}>
          Commencer mes achats
        </MayushText>
      </TouchableOpacity>
    </View>
  );
};

const styles = StyleSheet.create({
  emptyCard: { alignItems: 'center', paddingVertical: 48, paddingHorizontal: 20 },
  iconCircle: {
    width: 80,
    height: 80,
    borderRadius: 40,
    backgroundColor: colors.brand.orange100,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 16,
  },
  emptyTitle: { fontSize: 20, marginBottom: 8 },
  emptyCopy: { lineHeight: 20, maxWidth: 300, marginBottom: 24, textAlign: 'center' },
  browseBtn: { paddingHorizontal: 28, paddingVertical: 14, borderRadius: radii.xl, backgroundColor: colors.brand.orange500 },
});
