/**
 * CartUpdateAlert Component (Figma Node 309:666 - 05-cart-update-needed-price-stock-changes-fr)
 * Alert banner and item status indicators for price changes, stock adjustments, or unavailable items.
 */

import React from 'react';
import { StyleSheet, TouchableOpacity, View } from 'react-native';
import { formatMadPrice } from '../../commerce/cartState';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { colors } from '../../design-system/tokens/colors';
import { radii } from '../../design-system/tokens/radii';

export interface PriceStockChangeItem {
  id: string;
  productName: string;
  oldPriceMad?: number;
  newPriceMad?: number;
  oldQuantity?: number;
  newQuantity?: number;
  isUnavailable?: boolean;
}

export interface CartUpdateAlertProps {
  changes: PriceStockChangeItem[];
  onAcceptChanges: () => void;
  onRemoveUnavailable: () => void;
}

export const CartUpdateAlert: React.FC<CartUpdateAlertProps> = ({
  changes,
  onAcceptChanges,
  onRemoveUnavailable,
}) => {
  if (!changes.length) return null;

  const hasUnavailable = changes.some((c) => c.isUnavailable);

  return (
    <View style={styles.alertCard} accessibilityLabel="Cart Update Needed Alert">
      <View style={styles.headerRow}>
        <MayushIcon name="info" size={20} color={colors.brand.orange500} />
        <MayushText variant="strongBody" color={colors.brand.navy900} style={styles.title}>
          Attention : Des modifications ont eu lieu sur vos articles
        </MayushText>
      </View>

      <View style={styles.changeList}>
        {changes.map((item) => (
          <View key={item.id} style={styles.changeRow}>
            <MayushText variant="caption" color={colors.brand.navy900} style={styles.itemTitle}>
              • {item.productName} :
            </MayushText>
            {item.isUnavailable ? (
              <MayushText variant="caption" color={colors.semantic.error} style={styles.changeDetail}>
                Article épuisé / indisponible
              </MayushText>
            ) : (
              <View style={styles.valDiffRow}>
                {item.oldPriceMad && item.newPriceMad ? (
                  <MayushText variant="caption" color={colors.neutral.gray700}>
                    Prix: <MayushText variant="caption" style={styles.strikethrough}>{formatMadPrice(item.oldPriceMad)}</MayushText> ➔ <MayushText variant="caption" color={colors.brand.orange500}>{formatMadPrice(item.newPriceMad)}</MayushText>
                  </MayushText>
                ) : null}
                {item.oldQuantity && item.newQuantity ? (
                  <MayushText variant="caption" color={colors.neutral.gray700}>
                    Qté dispo: {item.newQuantity} (au lieu de {item.oldQuantity})
                  </MayushText>
                ) : null}
              </View>
            )}
          </View>
        ))}
      </View>

      <View style={styles.actionsRow}>
        <TouchableOpacity style={styles.acceptBtn} onPress={onAcceptChanges}>
          <MayushText variant="caption" color={colors.surface.white} style={styles.btnText}>
            Accepter les modifications
          </MayushText>
        </TouchableOpacity>
        {hasUnavailable ? (
          <TouchableOpacity style={styles.removeBtn} onPress={onRemoveUnavailable}>
            <MayushText variant="caption" color={colors.semantic.error} style={styles.btnText}>
              Retirer indisponibles
            </MayushText>
          </TouchableOpacity>
        ) : null}
      </View>
    </View>
  );
};

const styles = StyleSheet.create({
  alertCard: {
    padding: 14,
    borderRadius: radii.xl,
    backgroundColor: colors.brand.orange100,
    borderWidth: 1,
    borderColor: colors.surface.borderWarm,
    marginBottom: 16,
    gap: 10,
  },
  headerRow: { flexDirection: 'row', alignItems: 'center', gap: 8 },
  title: { flex: 1, fontSize: 13, fontWeight: '700' },
  changeList: { gap: 6 },
  changeRow: { flexDirection: 'column', gap: 2 },
  itemTitle: { fontWeight: '700' },
  changeDetail: { fontWeight: '600' },
  valDiffRow: { flexDirection: 'row', flexWrap: 'wrap', gap: 10 },
  strikethrough: { textDecorationLine: 'line-through', color: colors.neutral.gray500 },
  actionsRow: { flexDirection: 'row', gap: 8, marginTop: 4 },
  acceptBtn: {
    paddingHorizontal: 12,
    paddingVertical: 8,
    borderRadius: radii.md,
    backgroundColor: colors.brand.orange500,
    alignItems: 'center',
  },
  removeBtn: {
    paddingHorizontal: 12,
    paddingVertical: 8,
    borderRadius: radii.md,
    borderWidth: 1,
    borderColor: colors.semantic.error,
    backgroundColor: colors.surface.white,
    alignItems: 'center',
  },
  btnText: { fontWeight: '700' },
});
