/**
 * CartMergeSummary Component (Figma Node 309:677 - 05-cart-merge-guest-account-fusion-fr)
 * Guest & Account cart fusion presentation card/modal.
 */

import React from 'react';
import { Modal, StyleSheet, TouchableOpacity, View } from 'react-native';
import { CartLine, CartState, formatMadPrice, getCartTotals } from '../../commerce/cartState';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { colors } from '../../design-system/tokens/colors';
import { radii } from '../../design-system/tokens/radii';

export interface CartMergeSummaryProps {
  visible: boolean;
  guestCart: CartState;
  accountCart: CartState;
  onMergeBoth: (mergedLines: CartLine[]) => void;
  onKeepAccount: () => void;
  onKeepGuest: () => void;
  onClose: () => void;
}

export const mergeCartsDeduplicated = (guestLines: CartLine[], accountLines: CartLine[]): CartLine[] => {
  const mergedMap = new Map<string, CartLine>();

  [...accountLines, ...guestLines].forEach((line) => {
    const key = `${line.productId}:${line.variant || 'default'}`;
    if (mergedMap.has(key)) {
      const existing = mergedMap.get(key)!;
      mergedMap.set(key, { ...existing, quantity: existing.quantity + line.quantity });
    } else {
      mergedMap.set(key, { ...line });
    }
  });

  return Array.from(mergedMap.values());
};

export const CartMergeSummary: React.FC<CartMergeSummaryProps> = ({
  visible,
  guestCart,
  accountCart,
  onMergeBoth,
  onKeepAccount,
  onKeepGuest,
  onClose,
}) => {
  if (!visible) return null;

  const guestTotals = getCartTotals(guestCart);
  const accountTotals = getCartTotals(accountCart);
  const mergedLines = mergeCartsDeduplicated(guestCart.lines, accountCart.lines);
  const mergedTotals = getCartTotals({ lines: mergedLines });

  return (
    <Modal visible={visible} transparent animationType="slide" onRequestClose={onClose}>
      <View style={styles.overlay}>
        <View style={styles.sheetCard} accessibilityLabel="Guest Account Cart Merge Fusion">
          <View style={styles.header}>
            <MayushIcon name="refresh-cw" size={24} color={colors.brand.orange500} />
            <MayushText variant="sectionTitle" color={colors.brand.navy900} style={styles.headerTitle}>
              Paniers détectés : Fusion des articles
            </MayushText>
            <TouchableOpacity onPress={onClose}>
              <MayushIcon name="x" size={22} color={colors.brand.navy900} />
            </TouchableOpacity>
          </View>

          <MayushText variant="body" color={colors.neutral.gray700} style={styles.copy}>
            Vous avez des articles dans votre panier invité et dans le panier de votre compte. Choisissez comment continuer :
          </MayushText>

          <View style={styles.compareRow}>
            <View style={styles.compareBox}>
              <MayushText variant="caption" color={colors.neutral.gray700} style={styles.boxTag}>
                Panier Invité
              </MayushText>
              <MayushText variant="strongBody" color={colors.brand.navy900}>
                {guestTotals.itemCount} article(s)
              </MayushText>
              <MayushText variant="priceRegular" color={colors.brand.orange500}>
                {formatMadPrice(guestTotals.subtotalMad)}
              </MayushText>
            </View>

            <View style={styles.compareBox}>
              <MayushText variant="caption" color={colors.neutral.gray700} style={styles.boxTag}>
                Panier Compte
              </MayushText>
              <MayushText variant="strongBody" color={colors.brand.navy900}>
                {accountTotals.itemCount} article(s)
              </MayushText>
              <MayushText variant="priceRegular" color={colors.brand.orange500}>
                {formatMadPrice(accountTotals.subtotalMad)}
              </MayushText>
            </View>
          </View>

          <View style={styles.mergedPreviewBox}>
            <MayushText variant="caption" color={colors.brand.navy900} style={styles.mergedTag}>
              Résultat après fusion :
            </MayushText>
            <MayushText variant="strongBody" color={colors.brand.navy900}>
              {mergedLines.length} ligne(s) unique(s) · Total : {formatMadPrice(mergedTotals.subtotalMad)}
            </MayushText>
          </View>

          <View style={styles.actionsGroup}>
            <TouchableOpacity style={styles.primaryMergeBtn} onPress={() => onMergeBoth(mergedLines)}>
              <MayushText variant="button" color={colors.surface.white}>
                Fusionner les deux paniers ({formatMadPrice(mergedTotals.subtotalMad)})
              </MayushText>
            </TouchableOpacity>

            <TouchableOpacity style={styles.secondaryBtn} onPress={onKeepAccount}>
              <MayushText variant="caption" color={colors.brand.navy900}>
                Conserver uniquement le panier du compte
              </MayushText>
            </TouchableOpacity>

            <TouchableOpacity style={styles.secondaryBtn} onPress={onKeepGuest}>
              <MayushText variant="caption" color={colors.brand.navy900}>
                Conserver uniquement le panier invité
              </MayushText>
            </TouchableOpacity>
          </View>
        </View>
      </View>
    </Modal>
  );
};

const styles = StyleSheet.create({
  overlay: { flex: 1, backgroundColor: 'rgba(0,0,0,0.5)', justifyContent: 'flex-end' },
  sheetCard: {
    backgroundColor: colors.surface.white,
    borderTopLeftRadius: radii.xl,
    borderTopRightRadius: radii.xl,
    padding: 20,
    gap: 14,
  },
  header: { flexDirection: 'row', alignItems: 'center', gap: 10 },
  headerTitle: { flex: 1, fontSize: 16 },
  copy: { lineHeight: 18 },
  compareRow: { flexDirection: 'row', gap: 12 },
  compareBox: {
    flex: 1,
    padding: 12,
    borderRadius: radii.lg,
    backgroundColor: colors.surface.creamLight,
    borderWidth: 1,
    borderColor: colors.surface.borderWarm,
    gap: 4,
  },
  boxTag: { fontWeight: '700', textTransform: 'uppercase', fontSize: 10 },
  mergedPreviewBox: {
    padding: 12,
    borderRadius: radii.lg,
    backgroundColor: colors.brand.orange100,
    borderWidth: 1,
    borderColor: colors.brand.orange500,
    gap: 2,
  },
  mergedTag: { fontWeight: '700', fontSize: 11 },
  actionsGroup: { gap: 8, marginTop: 4 },
  primaryMergeBtn: {
    height: 48,
    borderRadius: radii.xl,
    backgroundColor: colors.brand.orange500,
    alignItems: 'center',
    justifyContent: 'center',
  },
  secondaryBtn: {
    height: 40,
    borderRadius: radii.lg,
    borderWidth: 1,
    borderColor: colors.surface.borderWarm,
    alignItems: 'center',
    justifyContent: 'center',
  },
});
