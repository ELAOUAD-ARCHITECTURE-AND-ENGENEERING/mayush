/**
 * SearchResultsScreen (Figma Node 309:601 - 02-search-results-grid-fauteuil)
 * Grid of search results with sorting, filter trigger, and product detail routing.
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

export interface SearchResultsScreenProps {
  searchQuery: string;
  onBack: () => void;
  onOpenFilter: () => void;
  onSelectProduct: (product: ProductMiniDto) => void;
  onToggleWishlist: (productId: number) => void;
}

export const SearchResultsScreen: React.FC<SearchResultsScreenProps> = ({
  searchQuery,
  onBack,
  onOpenFilter,
  onSelectProduct,
  onToggleWishlist,
}) => {
  const { isRTL, language } = useTheme();

  const mockResults: ProductMiniDto[] = [
    { id: 401, name: 'Fauteuil Nori Accent · Vert Sauge', priceMad: 1800, formattedPrice: '1 800 MAD', thumbnail_image: '', has_discount: false, discount: null, stroked_price: '1 800 MAD', main_price: '1 800 MAD', rating: 5, sales: 8, links: { details: '' } },
    { id: 402, name: 'Fauteuil Velours Bouclé · Crème', priceMad: 2100, formattedPrice: '2 100 MAD', thumbnail_image: '', has_discount: false, discount: null, stroked_price: '2 100 MAD', main_price: '2 100 MAD', rating: 5, sales: 6, links: { details: '' } },
    { id: 403, name: 'Fauteuil Pivotant Moka', priceMad: 2450, formattedPrice: '2 450 MAD', thumbnail_image: '', has_discount: false, discount: null, stroked_price: '2 450 MAD', main_price: '2 450 MAD', rating: 5, sales: 9, links: { details: '' } },
    { id: 404, name: 'Fauteuil Minimaliste Bois', priceMad: 1650, formattedPrice: '1 650 MAD', thumbnail_image: '', has_discount: false, discount: null, stroked_price: '1 650 MAD', main_price: '1 650 MAD', rating: 5, sales: 11, links: { details: '' } },
  ];

  return (
    <View style={styles.screen} accessibilityLabel="Search Results Screen">
      <View style={styles.header}>
        <TouchableOpacity accessibilityRole="button" onPress={onBack} style={styles.iconBtn}>
          <MayushIcon name={isRTL ? 'arrow-right' : 'arrow-left'} size={24} color={colors.brand.navy900} />
        </TouchableOpacity>
        <View style={styles.headerTitleBox}>
          <MayushText variant="sectionTitle" color={colors.brand.navy900} numberOfLines={1}>
            "{searchQuery}"
          </MayushText>
          <MayushText variant="caption" color={colors.neutral.gray700}>
            {mockResults.length} {language === 'ar' ? 'نتائج' : 'résultats trouvés'}
          </MayushText>
        </View>
        <TouchableOpacity accessibilityRole="button" onPress={onOpenFilter} style={styles.iconBtn}>
          <MayushIcon name="sliders" size={22} color={colors.brand.orange500} />
        </TouchableOpacity>
      </View>

      <View style={styles.subHeaderBar}>
        <TouchableOpacity style={styles.filterChip} onPress={onOpenFilter}>
          <MayushIcon name="sliders" size={16} color={colors.brand.navy900} />
          <MayushText variant="smallBody" color={colors.brand.navy900}>
            {language === 'ar' ? 'تصفية' : 'Filtrer'}
          </MayushText>
        </TouchableOpacity>
        <TouchableOpacity style={styles.filterChip}>
          <MayushIcon name="chevron-down" size={16} color={colors.brand.navy900} />
          <MayushText variant="smallBody" color={colors.brand.navy900}>
            {language === 'ar' ? 'ترتيب حسب' : 'Trier par'}
          </MayushText>
        </TouchableOpacity>
      </View>

      <ScrollView contentContainerStyle={styles.content} showsVerticalScrollIndicator={false}>
        <View style={styles.grid}>
          {mockResults.map((product) => (
            <TouchableOpacity
              key={product.id}
              style={styles.card}
              onPress={() => onSelectProduct(product)}
              activeOpacity={0.8}
            >
              <View style={styles.imgBox}>
                <Image source={product.id % 2 === 0 ? FAUTEUIL_IMG : CANAPE_IMG} style={styles.productImg} resizeMode="cover" />
                <TouchableOpacity style={styles.wishlistBtn} onPress={() => onToggleWishlist(product.id)}>
                  <MayushIcon name="heart" size={18} color={colors.brand.orange500} />
                </TouchableOpacity>
              </View>
              <View style={styles.cardBody}>
                <MayushText variant="strongBody" color={colors.brand.navy900} numberOfLines={2} style={styles.prodName}>
                  {product.name}
                </MayushText>
                <MayushText variant="priceRegular" color={colors.brand.orange500}>
                  {product.formattedPrice}
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
  headerTitleBox: { flex: 1, alignItems: 'center' },
  subHeaderBar: {
    flexDirection: 'row',
    gap: 12,
    paddingHorizontal: 16,
    paddingVertical: 10,
    borderBottomWidth: 1,
    borderBottomColor: colors.surface.borderWarm,
    backgroundColor: colors.surface.creamLight,
  },
  filterChip: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: radii.full,
    backgroundColor: colors.surface.white,
    borderWidth: 1,
    borderColor: colors.surface.borderWarm,
  },
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
  imgBox: { position: 'relative', height: 140 },
  productImg: { width: '100%', height: '100%' },
  wishlistBtn: {
    position: 'absolute',
    top: 8,
    right: 8,
    width: 32,
    height: 32,
    borderRadius: 16,
    backgroundColor: 'rgba(255,255,255,0.9)',
    alignItems: 'center',
    justifyContent: 'center',
  },
  cardBody: { padding: 10 },
  prodName: { fontSize: 14, lineHeight: 18, marginBottom: 4 },
});
