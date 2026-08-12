/**
 * RecentlyViewedScreen (Figma Node 309:599 - 02-recently-viewed-products)
 * Uses the same deterministic catalog fallback as authenticated Home until a
 * durable recently-viewed history domain exists.
 */

import React from 'react';
import { Image, ScrollView, StyleSheet, TouchableOpacity, View } from 'react-native';
import { ProductMiniDto } from '../../contracts/api/dto';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { useTheme } from '../../design-system/theme/useTheme';
import { colors } from '../../design-system/tokens/colors';
import { radii } from '../../design-system/tokens/radii';

const CANAPE_IMG = require('../../../assets/reference-art/home-new-luna.png');
const FAUTEUIL_IMG = require('../../../assets/reference-art/home-new-nori.png');

export interface RecentlyViewedScreenProps {
  products: ProductMiniDto[];
  onBack: () => void;
  onSelectProduct: (product: ProductMiniDto) => void;
  onOpenSearch?: () => void;
}

export const RecentlyViewedScreen: React.FC<RecentlyViewedScreenProps> = ({ products, onBack, onSelectProduct }) => {
  const { isRTL, language } = useTheme();

  return (
    <View style={styles.screen} accessibilityLabel="Recently Viewed Screen">
      <View style={[styles.header, isRTL && styles.rowReverse]}>
        <TouchableOpacity accessibilityRole="button" onPress={onBack} style={styles.iconBtn}>
          <MayushIcon name={isRTL ? 'arrow-right' : 'arrow-left'} size={24} color={colors.brand.navy900} />
        </TouchableOpacity>
        <MayushText variant="sectionTitle" color={colors.brand.navy900} style={[styles.headerTitle, isRTL && styles.rtlText]}>
          {language === 'ar' ? '\u0627\u0644\u0645\u0646\u062a\u062c\u0627\u062a \u0627\u0644\u0645\u0639\u0631\u0648\u0636\u0629 \u0645\u0624\u062e\u0631\u0627\u064b' : 'Consult\u00e9s r\u00e9cemment'}
        </MayushText>
        <View style={styles.iconBtn} />
      </View>

      <ScrollView contentContainerStyle={styles.content} showsVerticalScrollIndicator={false}>
        <MayushText variant="caption" color={colors.neutral.gray700} style={[styles.dataBoundary, isRTL && styles.rtlText]}>
          {language === 'ar'
            ? '\u0627\u062e\u062a\u064a\u0627\u0631 \u0645\u062d\u0644\u064a \u062b\u0627\u0628\u062a \u062d\u062a\u0649 \u064a\u062a\u0648\u0641\u0631 \u0633\u062c\u0644 \u0645\u0634\u0627\u0647\u062f\u0629 \u062d\u0642\u064a\u0642\u064a.'
            : 'S\u00e9lection locale d\u00e9terministe en attendant un historique r\u00e9el.'}
        </MayushText>
        <View style={[styles.grid, isRTL && styles.rowReverse]}>
          {products.map((product) => (
            <TouchableOpacity key={product.id} accessibilityRole="button" accessibilityLabel={product.name} style={styles.card} onPress={() => onSelectProduct(product)} activeOpacity={0.8}>
              <Image source={Number(product.id) % 2 === 0 ? FAUTEUIL_IMG : CANAPE_IMG} style={styles.cardImg} resizeMode="cover" />
              <View style={styles.cardBody}>
                <MayushText variant="strongBody" color={colors.brand.navy900} numberOfLines={2} style={[styles.prodName, isRTL && styles.rtlText]}>{product.name}</MayushText>
                <MayushText variant="priceRegular" color={colors.brand.orange500} style={styles.ltrValue}>{product.main_price}</MayushText>
              </View>
            </TouchableOpacity>
          ))}
        </View>
      </ScrollView>
    </View>
  );
};

const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: colors.surface.white },
  header: { height: 64, paddingHorizontal: 16, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', borderBottomWidth: 1, borderBottomColor: colors.surface.borderWarm },
  iconBtn: { width: 40, height: 40, alignItems: 'center', justifyContent: 'center' },
  headerTitle: { fontSize: 18, fontWeight: '700' },
  content: { padding: 16 },
  dataBoundary: { marginBottom: 14, lineHeight: 17 },
  grid: { flexDirection: 'row', flexWrap: 'wrap', gap: 12 },
  card: { width: '48%', borderRadius: radii.lg, overflow: 'hidden', borderWidth: 1, borderColor: colors.surface.borderWarm, backgroundColor: colors.surface.white },
  cardImg: { width: '100%', height: 130 },
  cardBody: { padding: 10 },
  prodName: { fontSize: 13, lineHeight: 18, marginBottom: 4 },
  rowReverse: { flexDirection: 'row-reverse' },
  rtlText: { writingDirection: 'rtl', textAlign: 'right' },
  ltrValue: { writingDirection: 'ltr' },
});
