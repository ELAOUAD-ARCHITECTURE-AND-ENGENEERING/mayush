/**
 * CartErrorState Component (Figma Node 309:669 - 05-cart-error-loading-state-fr)
 * Network or server loading error state with retry action.
 */

import React from 'react';
import { StyleSheet, TouchableOpacity, View } from 'react-native';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { colors } from '../../design-system/tokens/colors';
import { radii } from '../../design-system/tokens/radii';

export interface CartErrorStateProps {
  onRetry: () => void;
  errorMessage?: string;
}

export const CartErrorState: React.FC<CartErrorStateProps> = ({
  onRetry,
  errorMessage = "Impossible de récupérer votre panier. Vérifiez votre connexion internet.",
}) => {
  return (
    <View style={styles.errorCard} accessibilityLabel="Cart Error Loading State">
      <View style={styles.iconCircle}>
        <MayushIcon name="x-circle" size={36} color={colors.semantic.error} />
      </View>
      <MayushText variant="sectionTitle" color={colors.brand.navy900} align="center" style={styles.title}>
        Erreur de chargement du panier
      </MayushText>
      <MayushText variant="body" color={colors.neutral.gray700} align="center" style={styles.copy}>
        {errorMessage}
      </MayushText>
      <TouchableOpacity style={styles.retryBtn} onPress={onRetry}>
        <MayushIcon name="refresh-cw" size={18} color={colors.surface.white} />
        <MayushText variant="button" color={colors.surface.white}>
          Réessayer
        </MayushText>
      </TouchableOpacity>
    </View>
  );
};

const styles = StyleSheet.create({
  errorCard: {
    padding: 24,
    borderRadius: radii.xl,
    backgroundColor: colors.surface.creamLight,
    borderWidth: 1,
    borderColor: colors.surface.borderWarm,
    alignItems: 'center',
    margin: 16,
  },
  iconCircle: {
    width: 64,
    height: 64,
    borderRadius: 32,
    backgroundColor: colors.surface.white,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 12,
  },
  title: { marginBottom: 8 },
  copy: { lineHeight: 20, textAlign: 'center', marginBottom: 20, maxWidth: 280 },
  retryBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    paddingHorizontal: 20,
    paddingVertical: 12,
    borderRadius: radii.lg,
    backgroundColor: colors.brand.orange500,
  },
});
