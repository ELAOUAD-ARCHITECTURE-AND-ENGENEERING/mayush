/**
 * RecentlyViewedScreen (Figma Node 309:599 - 02-recently-viewed-products)
 * Grid/list of products recently viewed by the buyer with clear-history action.
 */

import React from 'react';
import {
  Image,
  ScrollView,
  StyleSheet,
  TouchableOpacity,
  View,
} from 'react-native';
import { ProductMiniDto } from '../../contracts/api/dto';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { useTheme } from '../../design-system/theme/useTheme';
import { colors } from '../../design-system/tokens/colors';
import { radii } from '../../design-system/tokens/radii';

const CANAPE_IMG = require('../../../assets/reference-art/home-new-luna.png');
const FAUTEUIL_IMG = require('../../../assets/reference-art/home-new-nori.png');

export interface RecentlyViewedScreenProps {
  onBack: () => void;
  onSelectProduct: (product: ProductMiniDto) => void;
  onClearHistory?: () => void;
}

export const RecentlyViewedScreen: React.FC<RecentlyViewedScreenProps> = ({
  onBack,
  onSelectProduct,
  onClearHistory,
}) => {
  const { isRTL, language } = useTheme();

  const recentItems: ProductMiniDto[] = [
    { id: 601, name: 'Canapé Luna 3 Places · Bouclé', priceMad: 4500, formattedPrice: '4 500 MAD', thumbnail_image: '', has_discount: false, discount: null, stroked_price: '4 500 MAD', main_price: '4 500 MAD', rating: 5, sales: 10, links: { details: '' } },
    { id: 602, name: 'Fauteuil Nori Accent · Vert', priceMad: 1800, formattedPrice: '1 800 MAD', thumbnail_image: '', has_discount: false, discount: null, stroked_price: '1 800 MAD', main_price: '1 800 MAD', rating: 5, sales: 8, links: { details: '' } },
    { id: 603, name: 'Table Basse Oval Plâtre', priceMad: 2200, formattedPrice: '2 200 MAD', thumbnail_image: '', has_discount: false, discount: null, stroked_price: '2 200 MAD', main_price: '2 200 MAD', rating: 5, sales: 12, links: { details: '' } },
  ];

  return (
    <View style={styles.screen} accessibilityLabel="Recently Viewed Screen">
      <View style={styles.header}>
        <TouchableOpacity accessibilityRole="button" onPress={onBack} style={styles.iconBtn}>
          <MayushIcon name={isRTL ? 'arrow-right' : 'arrow-left'} size={24} color={colors.brand.navy900} />
        </TouchableOpacity>
        <MayushText variant="sectionTitle" color={colors.brand.navy900} style={styles.headerTitle}>
          {language === 'ar' ? 'المنتجات المعروضة مؤخراً' : 'Consultés récemment'}
        </MayushText>
        <TouchableOpacity accessibilityRole="button" onPress={onClearHistory} style={styles.iconBtn}>
          <MayushIcon name="trash-2" size={20} color={colors.neutral.gray700} />
        </TouchableOpacity>
      </View>

      <ScrollView contentContainerStyle={styles.content} showsVerticalScrollIndicator={false}>
        <View style={styles.grid}>
          {recentItems.map((prod) => (
            <TouchableOpacity
              key={prod.id}
              style={styles.card}
              onPress={() => onSelectProduct(prod)}
              activeOpacity={0.8}
            >
              <Image source={prod.id % 2 === 0 ? FAUTEUIL_IMG : CANAPE_IMG} style={styles.cardImg} resizeMode="cover" />
              <View style={styles.cardBody}>
                <MayushText variant="caption" color={colors.neutral.gray500} style={styles.timeTag}>
                  Vu aujourd'hui
                </MayushText>
                <MayushText variant="strongBody" color={colors.brand.navy900} numberOfLines={2} style={styles.prodName}>
                  {prod.name}
                </MayushText>
                <MayushText variant="priceRegular" color={colors.brand.orange500}>
                  {prod.formattedPrice}
                </MayushText>
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
  header: {
    height: 64,
    paddingHorizontal: 16,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    borderBottomWidth: 1,
    borderBottomColor: colors.surface.borderWarm,
  },
  iconBtn: { width: 40, height: 40, alignItems: 'center', justifyContent: 'center' },
  headerTitle: { fontSize: 18, fontWeight: '700' },
  content: { padding: 16 },
  grid: { flexDirection: 'row', flexWrap: 'wrap', gap: 12 },
  card: {
    width: '48%',
    borderRadius: radii.lg,
    overflow: 'hidden',
    borderWidth: 1,
    borderColor: colors.surface.borderWarm,
    backgroundColor: colors.surface.white,
  },
  cardImg: { width: '100%', height: 130 },
  cardBody: { padding: 10 },
  timeTag: { fontSize: 11, marginBottom: 2 },
  prodName: { fontSize: 13, lineHeight: 18, marginBottom: 4 },
});
