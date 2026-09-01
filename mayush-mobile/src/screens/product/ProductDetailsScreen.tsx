import React, { useEffect, useState } from 'react';
import { Image, ScrollView, StyleSheet, TouchableOpacity, View } from 'react-native';
import { STORE_CURRENCY_CODE } from '../../config/currency';
import { ProductDetailDto, ProductMiniDto } from '../../contracts/api/dto';
import { MayushLogo } from '../../design-system/components/brand/MayushLogo';
import { Skeleton } from '../../design-system/components/feedback/Skeleton';
import { BottomTabBar, TabKey } from '../../design-system/components/navigation/BottomTabBar';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { useTheme } from '../../design-system/theme/useTheme';
import { colors } from '../../design-system/tokens/colors';
import { catalogService } from '../../services/api/catalogService';

export interface ProductDetailsScreenProps {
  productId?: number;
  initialProduct?: ProductMiniDto;
  onBack?: () => void;
  onOpenGallery?: () => void;
  onOpenVariantSheet?: (product: ProductDetailDto) => void;
  onOpenDescription?: () => void;
  onOpenSpecifications?: () => void;
  onOpenDeliveryReturns?: () => void;
  onOpenReviews?: () => void;
  onNavigateTab?: (tab: TabKey) => void;
  activeTab?: TabKey;
}

export const ProductDetailsScreen: React.FC<ProductDetailsScreenProps> = ({
  productId = 1,
  initialProduct,
  onBack,
  onOpenGallery,
  onOpenVariantSheet,
  onOpenDescription,
  onOpenSpecifications,
  onOpenDeliveryReturns,
  onOpenReviews,
  onNavigateTab,
  activeTab = 'home',
}) => {
  const { language, isRTL } = useTheme();
  const [loading, setLoading] = useState(true);
  const [product, setProduct] = useState<ProductDetailDto | null>(null);
  const [activeImage, setActiveImage] = useState(0);
  const fallbackImage = initialProduct?.thumbnail_image || 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?q=80&w=1000&auto=format&fit=crop';

  useEffect(() => {
    let mounted = true;
    catalogService.getProductDetails(productId, language).then((data) => { if (mounted) { setProduct(data); setLoading(false); } });
    return () => { mounted = false; };
  }, [productId, language]);

  const imageItems = product?.photos?.length ? product.photos.map((item) => typeof item === 'string' ? item : (item as unknown as { path: string }).path) : [fallbackImage];
  const title = product?.name || initialProduct?.name || 'Fauteuil Lounge Luna';
  const price = product?.main_price || initialProduct?.main_price || `2 950 ${STORE_CURRENCY_CODE}`;
  const originalPrice = product?.stroked_price || initialProduct?.stroked_price || `3 490 ${STORE_CURRENCY_CODE}`;
  const compactPrice = (value: string) => value.replace(new RegExp(`(?:\\.00|,00)(?=\\s*${STORE_CURRENCY_CODE}\\b)`, 'i'), '');
  const displayPrice = compactPrice(price);
  const displayOriginalPrice = compactPrice(originalPrice);
  const heading = (fr: string, ar: string) => isRTL ? ar : fr;

  return (
    <View style={styles.screen}>
      <View style={[styles.header, isRTL && styles.rowReverse]}>
        <TouchableOpacity onPress={onBack} style={styles.squareButton}><MayushIcon name={isRTL ? 'arrow-right' : 'arrow-left'} size={30} color={colors.brand.navy900} /></TouchableOpacity>
        <MayushLogo width={132} height={38} />
        <View style={[styles.headerActions, isRTL && styles.rowReverse]}>
          <TouchableOpacity style={styles.squareButton}><MayushIcon name="share" size={25} color={colors.brand.navy900} /></TouchableOpacity>
          <TouchableOpacity style={styles.squareButton}><MayushIcon name="heart" size={25} color={colors.brand.navy900} /></TouchableOpacity>
          <TouchableOpacity style={styles.squareButton}><MayushIcon name="shopping-cart" size={25} color={colors.brand.navy900} /><View style={styles.cartBadge}><MayushText variant="caption" color={colors.surface.white}>3</MayushText></View></TouchableOpacity>
        </View>
      </View>
      <ScrollView showsVerticalScrollIndicator={false} contentContainerStyle={styles.content}>
        <View style={styles.gallery}>
          <ScrollView horizontal pagingEnabled showsHorizontalScrollIndicator={false} onMomentumScrollEnd={(event) => setActiveImage(Math.round(event.nativeEvent.contentOffset.x / event.nativeEvent.layoutMeasurement.width))}>
            {imageItems.map((url, index) => <Image key={`${url}-${index}`} source={{ uri: url }} style={styles.galleryImage} resizeMode="cover" />)}
          </ScrollView>
          {product?.has_discount || initialProduct?.has_discount ? <View style={styles.discount}><MayushText variant="strongBody" color={colors.surface.white}>{product?.discount || initialProduct?.discount || '-15%'}</MayushText></View> : null}
          <TouchableOpacity accessibilityRole="button" accessibilityLabel={heading('Ouvrir la galerie produit', 'فتح معرض المنتج')} onPress={onOpenGallery} style={styles.imageCount}><MayushText variant="body" color={colors.surface.white}>{activeImage + 1} / {Math.max(imageItems.length, 6)}</MayushText></TouchableOpacity>
          <View style={styles.galleryDots}>{imageItems.slice(0, 6).map((_, index) => <View key={index} style={[styles.dot, index === activeImage && styles.dotActive]} />)}</View>
        </View>

        {loading ? <View style={styles.skeletonStack}><Skeleton height={34} width="70%" /><Skeleton height={24} width="45%" /><Skeleton height={72} borderRadius="xl" /></View> : <View style={styles.info}>
          <MayushText variant="display" color={colors.brand.navy900} style={styles.productTitle}>{title}</MayushText>
          <MayushText variant="body" color={colors.neutral.gray700}>{heading('Tissu bouclé • Beige', 'نسيج بوكليه • بيج')}</MayushText>

          <TouchableOpacity onPress={onOpenReviews} style={[styles.ratingRow, isRTL && styles.rowReverse]}>
            <MayushText variant="sectionTitle" color={colors.brand.orange500}>★★★★★</MayushText>
            <MayushText variant="body" color={colors.neutral.gray700}>4,9 (32 avis)</MayushText>
            <View style={styles.seller}>
              <View style={styles.sellerCircle}><MayushIcon name="shopping-bag" size={17} color={colors.brand.navy900} /></View>
              <MayushText variant="strongBody" color={colors.brand.navy900}>{product?.shop_name || 'Mayush Design'}</MayushText>
              <MayushIcon name={isRTL ? 'chevron-left' : 'chevron-right'} size={21} color={colors.brand.orange500} />
            </View>
          </TouchableOpacity>

          <View style={[styles.priceStock, isRTL && styles.rowReverse]}><View style={styles.priceBlock}><MayushText variant="display" color={colors.brand.orange500} numberOfLines={1} style={styles.price}>{displayPrice}</MayushText><MayushText variant="body" color={colors.neutral.gray500} numberOfLines={1} style={styles.struck}>{displayOriginalPrice}</MayushText></View><View style={styles.stockPill}><MayushIcon name="check-circle" size={18} color="#3C7544" /><MayushText variant="strongBody" color="#3C7544">{heading('En stock', 'متوفر')}</MayushText></View></View>

          <TouchableOpacity activeOpacity={0.8} onPress={() => product && onOpenVariantSheet?.(product)} style={[styles.optionCard, isRTL && styles.rowReverse]}><View style={styles.variantImage}><Image source={{ uri: imageItems[0] }} style={{ width: '100%', height: '100%' }} /></View><View style={{ flex: 1 }}><MayushText variant="smallBody" color={colors.neutral.gray700}>{heading('Variation sélectionnée', 'الخيار المحدد')}</MayushText><MayushText variant="strongBody" color={colors.brand.navy900} style={{ marginTop: 3 }}>{heading('Tissu bouclé • Beige', 'نسيج بوكليه • بيج')}</MayushText><MayushText variant="smallBody" color={colors.neutral.gray700}>{heading('Pieds bois naturel', 'أرجل خشبية طبيعية')}</MayushText></View><MayushIcon name={isRTL ? 'chevron-left' : 'chevron-right'} size={22} color={colors.brand.orange500} /></TouchableOpacity>

          <View style={[styles.quantityCard, isRTL && styles.rowReverse]}><MayushText variant="strongBody" color={colors.brand.navy900}>{heading('Quantité', 'الكمية')}</MayushText><View style={styles.stepper}><TouchableOpacity style={styles.stepperButton}><MayushIcon name="minus" size={23} color={colors.brand.navy900} /></TouchableOpacity><MayushText variant="sectionTitle" color={colors.brand.navy900}>1</MayushText><TouchableOpacity style={styles.stepperButton}><MayushIcon name="plus" size={26} color={colors.brand.navy900} /></TouchableOpacity></View></View>

          <TouchableOpacity activeOpacity={0.8} onPress={onOpenDescription} style={[styles.detailLinkCard, isRTL && styles.rowReverse]}>
            <MayushIcon name="file-text" size={22} color={colors.brand.navy900} />
            <MayushText variant="strongBody" color={colors.brand.navy900} style={styles.detailLinkTitle}>
              {heading('Description détaillée & Artisanat', 'الوصف التفصيلي والحرفية')}
            </MayushText>
            <MayushIcon name={isRTL ? 'chevron-left' : 'chevron-right'} size={22} color={colors.brand.orange500} />
          </TouchableOpacity>

          <TouchableOpacity activeOpacity={0.8} onPress={onOpenSpecifications} style={[styles.detailLinkCard, isRTL && styles.rowReverse]}>
            <MayushIcon name="sliders" size={22} color={colors.brand.navy900} />
            <MayushText variant="strongBody" color={colors.brand.navy900} style={styles.detailLinkTitle}>
              {heading('Fiche technique & Dimensions', 'المواصفات التقنية والأبعاد')}
            </MayushText>
            <MayushIcon name={isRTL ? 'chevron-left' : 'chevron-right'} size={22} color={colors.brand.orange500} />
          </TouchableOpacity>

          <TouchableOpacity activeOpacity={0.8} onPress={onOpenDeliveryReturns} style={[styles.deliveryCard, isRTL && styles.rowReverse]}>
            <MayushIcon name="truck-outline" size={33} color={colors.brand.orange500} />
            <View style={{ flex: 1 }}>
              <MayushText variant="strongBody" color={colors.brand.navy900}>{heading('Livraison & Retours au Maroc', 'التوصيل والإرجاع بالمغرب')}</MayushText>
              <MayushText variant="smallBody" color={colors.neutral.gray700}>{heading('Gratuit dès 3 000 MAD • Retours 14j', 'مجاني فوق 3000 درهم • إرجاع خلال 14 يوماً')}</MayushText>
            </View>
            <MayushIcon name={isRTL ? 'chevron-left' : 'chevron-right'} size={22} color={colors.brand.orange500} />
          </TouchableOpacity>
        </View>}
      </ScrollView>
      <View style={styles.ctaShell}><TouchableOpacity disabled={loading} onPress={() => product && onOpenVariantSheet?.(product)} style={styles.cta}><MayushIcon name="shopping-bag" size={27} color={colors.surface.white} /><MayushText variant="sectionTitle" color={colors.surface.white} style={styles.ctaLabel}>{heading('Ajouter au panier', 'أضف إلى السلة')}</MayushText><View style={styles.ctaDivider} /><MayushText variant="sectionTitle" color={colors.surface.white}>{displayPrice}</MayushText></TouchableOpacity></View>
      <BottomTabBar activeTab={activeTab} onTabPress={(tab) => onNavigateTab?.(tab)} cartBadgeCount={3} />
    </View>
  );
};

const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: colors.surface.creamLight },
  header: { minHeight: 68, paddingHorizontal: 20, paddingTop: 8, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', backgroundColor: colors.surface.creamLight },
  rowReverse: { flexDirection: 'row-reverse' },
  headerActions: { flexDirection: 'row', gap: 7 },
  squareButton: { width: 48, height: 48, borderRadius: 12, borderWidth: 1, borderColor: colors.surface.borderWarm, alignItems: 'center', justifyContent: 'center', backgroundColor: colors.surface.white, position: 'relative' },
  cartBadge: { position: 'absolute', top: -6, right: -6, minWidth: 20, height: 20, borderRadius: 10, alignItems: 'center', justifyContent: 'center', backgroundColor: colors.brand.orange500 },
  content: { paddingBottom: 15 },
  gallery: { marginHorizontal: 20, height: 236, overflow: 'hidden', borderRadius: 18, position: 'relative', backgroundColor: colors.surface.cream },
  galleryImage: { width: 353, height: 236 },
  discount: { position: 'absolute', top: 14, left: 14, paddingHorizontal: 12, paddingVertical: 8, borderRadius: 12, backgroundColor: colors.brand.orange500 },
  imageCount: { position: 'absolute', top: 14, right: 14, paddingHorizontal: 12, paddingVertical: 8, borderRadius: 12, backgroundColor: 'rgba(16,29,53,0.86)' },
  galleryDots: { position: 'absolute', bottom: 14, left: 0, right: 0, flexDirection: 'row', justifyContent: 'center', gap: 8 },
  dot: { width: 8, height: 8, borderRadius: 4, backgroundColor: 'rgba(255,255,255,0.55)' },
  dotActive: { backgroundColor: colors.surface.white },
  skeletonStack: { padding: 20, gap: 14 },
  info: { paddingHorizontal: 20, paddingTop: 18, gap: 12 },
  productTitle: { fontSize: 29, lineHeight: 35 },
  ratingRow: { flexDirection: 'row', alignItems: 'center', gap: 8 },
  seller: { flexDirection: 'row', alignItems: 'center', gap: 6, marginLeft: 'auto' },
  sellerCircle: { width: 28, height: 28, borderRadius: 14, backgroundColor: colors.surface.cream, alignItems: 'center', justifyContent: 'center' },
  priceStock: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', marginTop: 4 },
  priceBlock: { flexDirection: 'row', alignItems: 'baseline', gap: 8 },
  price: { fontSize: 26, fontWeight: '800' },
  struck: { textDecorationLine: 'line-through', fontSize: 16 },
  stockPill: { flexDirection: 'row', alignItems: 'center', gap: 6, paddingHorizontal: 10, paddingVertical: 4, borderRadius: 12, backgroundColor: '#E8F5E9' },
  optionCard: { padding: 12, borderRadius: 16, borderWidth: 1, borderColor: colors.surface.borderWarm, backgroundColor: colors.surface.white, flexDirection: 'row', alignItems: 'center', gap: 12 },
  variantImage: { width: 44, height: 44, borderRadius: 8, overflow: 'hidden' },
  quantityCard: { padding: 12, borderRadius: 16, borderWidth: 1, borderColor: colors.surface.borderWarm, backgroundColor: colors.surface.white, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' },
  stepper: { flexDirection: 'row', alignItems: 'center', gap: 16 },
  stepperButton: { width: 36, height: 36, borderRadius: 18, borderWidth: 1, borderColor: colors.surface.borderWarm, alignItems: 'center', justifyContent: 'center' },
  deliveryCard: { padding: 14, borderRadius: 16, borderWidth: 1, borderColor: colors.surface.borderWarm, backgroundColor: colors.surface.white, flexDirection: 'row', alignItems: 'center', gap: 12 },
  detailLinkCard: { padding: 14, borderRadius: 16, borderWidth: 1, borderColor: colors.surface.borderWarm, backgroundColor: colors.surface.white, flexDirection: 'row', alignItems: 'center', gap: 12 },
  detailLinkTitle: { flex: 1 },
  ctaShell: { paddingHorizontal: 20, paddingVertical: 10, backgroundColor: colors.surface.creamLight },
  cta: { height: 54, borderRadius: 16, backgroundColor: colors.brand.orange500, flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 12, paddingHorizontal: 16 },
  ctaLabel: { fontSize: 17, fontWeight: '700' },
  ctaDivider: { width: 1, height: 20, backgroundColor: 'rgba(255,255,255,0.4)' },
});
