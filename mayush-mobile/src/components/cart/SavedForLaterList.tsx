/**
 * SavedForLaterList Component (Figma Node 309:676 - 05-saved-for-later-items-list-fr)
 * Dedicated list component for products saved for later.
 */

import React from 'react';
import { Image, StyleSheet, TouchableOpacity, View } from 'react-native';
import { CartLine, formatMadPrice } from '../../commerce/cartState';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { colors } from '../../design-system/tokens/colors';
import { radii } from '../../design-system/tokens/radii';

const FALLBACK_IMAGE = require('../../../assets/reference-art/home-new-luna.png');

export interface SavedForLaterListProps {
  items: CartLine[];
  onMoveToCart: (line: CartLine) => void;
  onRemove: (lineId: string) => void;
  onSelectProduct?: (productId: number) => void;
}

export const SavedForLaterList: React.FC<SavedForLaterListProps> = ({
  items,
  onMoveToCart,
  onRemove,
  onSelectProduct,
}) => {
  if (!items.length) {
    return (
      <View style={styles.emptySavedBox} accessibilityLabel="Saved For Later Empty State">
        <MayushText variant="caption" color={colors.neutral.gray700} align="center">
          Aucun article enregistré pour plus tard.
        </MayushText>
      </View>
    );
  }

  return (
    <View style={styles.container} accessibilityLabel="Saved For Later List">
      <MayushText variant="sectionTitle" color={colors.brand.navy900} style={styles.heading}>
        Enregistrés pour plus tard ({items.length})
      </MayushText>

      {items.map((line) => (
        <View key={line.id} style={styles.card}>
          <TouchableOpacity
            activeOpacity={0.8}
            onPress={() => onSelectProduct?.(typeof line.productId === 'number' ? line.productId : 101)}
          >
            <Image source={FALLBACK_IMAGE} style={styles.thumb} resizeMode="cover" />
          </TouchableOpacity>

          <View style={styles.metaCol}>
            <TouchableOpacity
              onPress={() => onSelectProduct?.(typeof line.productId === 'number' ? line.productId : 101)}
            >
              <MayushText variant="strongBody" color={colors.brand.navy900} numberOfLines={1}>
                {line.productName || line.name}
              </MayushText>
            </TouchableOpacity>

            <MayushText variant="caption" color={colors.neutral.gray700} style={styles.variantText}>
              {line.selectedVariantText || line.variant || 'Standard'}
            </MayushText>

            <MayushText variant="priceRegular" color={colors.brand.orange500}>
              {formatMadPrice(line.unitPriceMad)}
            </MayushText>

            <View style={styles.actionsRow}>
              <TouchableOpacity style={styles.moveBtn} onPress={() => onMoveToCart(line)}>
                <MayushIcon name="shopping-bag" size={14} color={colors.brand.orange500} />
                <MayushText variant="caption" color={colors.brand.orange500} style={styles.moveText}>
                  Déplacer vers le panier
                </MayushText>
              </TouchableOpacity>

              <TouchableOpacity style={styles.removeBtn} onPress={() => onRemove(line.id)}>
                <MayushIcon name="trash-2" size={14} color={colors.neutral.gray500} />
              </TouchableOpacity>
            </View>
          </View>
        </View>
      ))}
    </View>
  );
};

const styles = StyleSheet.create({
  container: { marginTop: 16 },
  heading: { fontSize: 17, marginBottom: 12 },
  emptySavedBox: { padding: 16, borderRadius: radii.lg, backgroundColor: colors.surface.creamLight, marginTop: 16 },
  card: {
    flexDirection: 'row',
    padding: 10,
    borderRadius: radii.lg,
    borderWidth: 1,
    borderColor: colors.surface.borderWarm,
    backgroundColor: colors.surface.white,
    marginBottom: 8,
    gap: 12,
  },
  thumb: { width: 64, height: 64, borderRadius: radii.md },
  metaCol: { flex: 1 },
  variantText: { marginTop: 2, marginBottom: 4 },
  actionsRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginTop: 6 },
  moveBtn: { flexDirection: 'row', alignItems: 'center', gap: 6 },
  moveText: { fontWeight: '700' },
  removeBtn: { padding: 4 },
});
