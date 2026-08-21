/**
 * RecentlyViewedScreen (Figma Node 309:599 - 02-recently-viewed-products)
 * Fetches authenticated user's last-viewed products from GET /api/v2/products/last-viewed.
 * Falls back to hardcoded fixtures when the API returns an empty list.
 */

import React, { useEffect, useState } from 'react';
import { ActivityIndicator, Image, ScrollView, StyleSheet, TouchableOpacity, View } from 'react-native';
import { ProductMiniDto } from '../../contracts/api/dto';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { useTheme } from '../../design-system/theme/useTheme';
import { colors } from '../../design-system/tokens/colors';
import { radii } from '../../design-system/tokens/radii';
import { catalogService } from '../../services/api/catalogService';

const CANAPE_IMG = require('../../../assets/reference-art/home-new-luna.png');
const FAUTEUIL_IMG = require('../../../assets/reference-art/home-new-nori.png');

const FALLBACK_PRODUCTS_FR: ProductMiniDto[] = [
  {
    id: 101,
    name: 'Canapé Luna Bouclé',
    slug: 'canape-luna-boucle',
    thumbnail_image: '',
    has_discount: true,
    discount: '-15%',
    stroked_price: '14 500 MAD',
    main_price: '12 325 MAD',
    rating: 4.9,
    sales: 124,
    links: { details: '' },
  },
  {
    id: 102,
    name: 'Fauteuil Nori Velours',
    slug: 'fauteuil-nori-velours',
    thumbnail_image: '',
    has_discount: false,
    discount: null,
    stroked_price: '',
    main_price: '8 900 MAD',
    rating: 4.7,
    sales: 89,
    links: { details: '' },
  },
];

const FALLBACK_PRODUCTS_AR: ProductMiniDto[] = [
  {
    id: 101,
    name: 'أريكة لونا الفاخرة',
    slug: 'canape-luna-boucle',
    thumbnail_image: '',
    has_discount: true,
    discount: '-15%',
    stroked_price: '14 500 د.م.',
    main_price: '12 325 د.م.',
    rating: 4.9,
    sales: 124,
    links: { details: '' },
  },
  {
    id: 102,
    name: 'كرسي نوري المخملي',
    slug: 'fauteuil-nori-velours',
    thumbnail_image: '',
    has_discount: false,
    discount: null,
    stroked_price: '',
    main_price: '8 900 د.م.',
    rating: 4.7,
    sales: 89,
    links: { details: '' },
  },
];

export interface RecentlyViewedScreenProps {
  onBack: () => void;
  onSelectProduct: (product: ProductMiniDto) => void;
}

export const RecentlyViewedScreen: React.FC<RecentlyViewedScreenProps> = ({ onBack, onSelectProduct }) => {
  const { isRTL, language } = useTheme();
  const [products, setProducts] = useState<ProductMiniDto[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    let cancelled = false;
    setLoading(true);
    catalogService.getLastViewedProducts(language).then((data) => {
      if (cancelled) return;
      setProducts(data && data.length > 0 ? data : []);
      setLoading(false);
    }).catch(() => {
      if (cancelled) return;
      setProducts([]);
      setLoading(false);
    });
    return () => { cancelled = true; };
  }, [language]);

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
        {loading ? (
          <ActivityIndicator size="large" color={colors.brand.orange500} style={styles.loader} />
        ) : products.length === 0 ? (
          <View style={styles.emptyState}>
            <MayushText variant="body" color={colors.neutral.gray500} align="center">
              {language === 'ar' ? 'لا توجد منتجات معروضة مؤخراً' : 'Aucun produit consulté récemment'}
            </MayushText>
          </View>
        ) : (
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
        )}
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
  loader: { marginTop: 48 },
  emptyState: { marginTop: 48, alignItems: 'center', paddingHorizontal: 24 },
  grid: { flexDirection: 'row', flexWrap: 'wrap', gap: 12 },
  card: { width: '48%', borderRadius: radii.lg, overflow: 'hidden', borderWidth: 1, borderColor: colors.surface.borderWarm, backgroundColor: colors.surface.white },
  cardImg: { width: '100%', height: 130 },
  cardBody: { padding: 10 },
  prodName: { fontSize: 13, lineHeight: 18, marginBottom: 4 },
  rowReverse: { flexDirection: 'row-reverse' },
  rtlText: { writingDirection: 'rtl', textAlign: 'right' },
  ltrValue: { writingDirection: 'ltr' },
});
