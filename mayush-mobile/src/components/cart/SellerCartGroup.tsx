/**
 * SellerCartGroup Component (Figma Node 309:661 - 05-cart-multi-vendor-grouped-by-seller-fr)
 * Renders cart items grouped by artisan seller shop with seller-level subtotal calculations.
 */

import React from 'react';
import { StyleSheet, View } from 'react-native';
import { CartLine, formatMadPrice } from '../../commerce/cartState';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { useTheme } from '../../design-system/theme/useTheme';
import { colors } from '../../design-system/tokens/colors';
import { radii } from '../../design-system/tokens/radii';

export interface SellerCartGroupProps {
  sellerName: string;
  lines: CartLine[];
  children: React.ReactNode;
}

export const SellerCartGroup: React.FC<SellerCartGroupProps> = ({
  sellerName,
  lines,
  children,
}) => {
  const { isRTL, language } = useTheme();
  const sellerSubtotal = lines.reduce((acc, line) => acc + (line.unitPriceMad * line.quantity), 0);
  const direction = isRTL ? styles.rowReverse : styles.row;

  return (
    <View style={styles.groupCard} accessibilityLabel={`Seller Group ${sellerName}`}>
      <View style={[styles.sellerHeader, direction]}>
        <View style={styles.badgeCircle}>
          <MayushIcon name="shopping-bag" size={16} color={colors.brand.navy900} />
        </View>
        <MayushText variant="strongBody" color={colors.brand.navy900} style={styles.sellerTitle}>
          {language === 'ar' ? `يباع ويشحن بواسطة: ${sellerName}` : `Vendu et expédié par : ${sellerName}`}
        </MayushText>
      </View>

      <View style={styles.body}>{children}</View>

      <View style={[styles.sellerFooter, direction]}>
        <MayushText variant="caption" color={colors.neutral.gray700}>
          {language === 'ar' ? `المجموع الفرعي للبائع (${lines.length})` : `Sous-total vendeur (${lines.length} art.) :`}
        </MayushText>
        <MayushText variant="strongBody" color={colors.brand.orange500}>
          {formatMadPrice(sellerSubtotal)}
        </MayushText>
      </View>
    </View>
  );
};

const styles = StyleSheet.create({
  row: { flexDirection: 'row' },
  rowReverse: { flexDirection: 'row-reverse' },
  groupCard: {
    borderRadius: radii.xl,
    borderWidth: 1,
    borderColor: colors.surface.borderWarm,
    backgroundColor: colors.surface.white,
    overflow: 'hidden',
    marginBottom: 16,
  },
  sellerHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 14,
    paddingVertical: 10,
    backgroundColor: colors.surface.creamLight,
    borderBottomWidth: 1,
    borderBottomColor: colors.surface.borderWarm,
    gap: 8,
  },
  badgeCircle: {
    width: 26,
    height: 26,
    borderRadius: 13,
    backgroundColor: colors.surface.white,
    alignItems: 'center',
    justifyContent: 'center',
  },
  sellerTitle: { fontSize: 13, fontWeight: '700' },
  body: { padding: 12 },
  sellerFooter: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: 14,
    paddingVertical: 8,
    backgroundColor: colors.surface.creamLight,
    borderTopWidth: 1,
    borderTopColor: colors.surface.borderWarm,
  },
});
