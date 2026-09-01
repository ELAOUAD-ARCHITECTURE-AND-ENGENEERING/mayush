import React, { useEffect, useState } from 'react';
import { View, ScrollView, StyleSheet, TouchableOpacity } from 'react-native';
import { useTheme } from '../../design-system/theme/useTheme';
import { MayushLogo } from '../../design-system/components/brand/MayushLogo';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { TextField } from '../../design-system/components/forms/TextField';
import { ProductCard } from '../../design-system/components/commerce/ProductCard';
import { BottomTabBar, TabKey } from '../../design-system/components/navigation/BottomTabBar';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { Skeleton } from '../../design-system/components/feedback/Skeleton';
import { colors } from '../../design-system/tokens/colors';
import { catalogService } from '../../services/api/catalogService';
import { ProductMiniDto, CategoryDto } from '../../contracts/api/dto';

export interface CategoryProductListScreenProps {
  category?: CategoryDto;
  onBack?: () => void;
  onSelectProduct?: (product: ProductMiniDto) => void;
  onNavigateTab?: (tab: TabKey) => void;
  activeTab?: TabKey;
}

export const CategoryProductListScreen: React.FC<CategoryProductListScreenProps> = ({ category, onBack, onSelectProduct, onNavigateTab, activeTab = 'categories' }) => {
  const { language, isRTL } = useTheme();
  const [loading, setLoading] = useState(true);
  const [products, setProducts] = useState<ProductMiniDto[]>([]);
  const categorySlug = category?.name ? category.name.toLowerCase().replace(/\s+/g, '-') : 'canapes';
  const categoryName = category?.name || (isRTL ? '\u0643\u0646\u0628\u0627\u062a' : 'Canapés');
  const displayCategoryName = isRTL ? categoryName : categoryName.split('&')[0].trim();

  useEffect(() => {
    let mounted = true;
    catalogService.getCategoryProducts(categorySlug, 1, language).then((result) => {
      if (mounted) { setProducts(result.data || []); setLoading(false); }
    });
    return () => { mounted = false; };
  }, [categorySlug, language]);

  const heading = (fr: string, ar: string) => isRTL ? ar : fr;
  return (
    <View style={styles.container}>
      <ScrollView showsVerticalScrollIndicator={false} contentContainerStyle={styles.content}>
        <View style={[styles.topbar, isRTL && styles.rowReverse]}>
          <TouchableOpacity style={styles.iconButton}><MayushIcon name="menu" size={29} color={colors.brand.navy900} /></TouchableOpacity>
          <MayushLogo width={142} height={41} />
          <View style={[styles.rightActions, isRTL && styles.rowReverse]}><TouchableOpacity style={styles.cartAction}><MayushIcon name="shopping-cart" size={28} color={colors.brand.navy900} /><View style={styles.cartBadge}><MayushText variant="caption" color={colors.surface.white}>3</MayushText></View></TouchableOpacity><TouchableOpacity style={styles.iconButton}><MayushIcon name="user" size={27} color={colors.brand.navy900} /></TouchableOpacity></View>
        </View>
        <View style={[styles.titleRow, isRTL && styles.rowReverse]}>
          <TouchableOpacity onPress={onBack} style={styles.backButton}><MayushIcon name={isRTL ? 'arrow-right' : 'arrow-left'} size={33} color={colors.brand.navy900} /></TouchableOpacity>
          <View style={styles.titleContent}><MayushText variant="display" color={colors.brand.navy900} numberOfLines={1} style={styles.title}>{displayCategoryName}</MayushText><MayushText variant="body" color={colors.neutral.gray700}>{heading('128 produits trouvés', '128 \u0645\u0646\u062a\u062c\u0627\u064b \u0645\u062a\u0648\u0641\u0631\u0627\u064b')}</MayushText></View>
        </View>
        <TextField value="" placeholder={heading('Rechercher un canapé, une matière, une couleur...', '\u0627\u0628\u062d\u062b \u0639\u0646 \u0643\u0646\u0628\u0629 \u0623\u0648 \u0645\u0627\u062f\u0629...')} leftIcon="search" rightIcon="camera" />
        <View style={styles.filterRow}>
          <TouchableOpacity style={styles.filterControl}><MayushIcon name="sliders-horizontal" size={23} color={colors.brand.navy900} /><MayushText variant="strongBody" color={colors.brand.navy900}>{heading('Filtres', '\u062a\u0635\u0641\u064a\u0629')}</MayushText><View style={styles.filterCount}><MayushText variant="caption" color={colors.surface.white}>2</MayushText></View></TouchableOpacity>
          <TouchableOpacity style={styles.sortControl}><MayushIcon name="arrow-down-up" size={23} color={colors.brand.navy900} /><MayushText variant="strongBody" color={colors.brand.navy900}>{heading('Trier par', '\u062a\u0631\u062a\u064a\u0628')}</MayushText><MayushText variant="body" color={colors.brand.orange500}>{heading('Pertinence', '\u0627\u0644\u0623\u0643\u062b\u0631 \u0645\u0644\u0627\u0621\u0645\u0629')}</MayushText><MayushIcon name="chevron-down" size={19} color={colors.brand.navy900} /></TouchableOpacity>
        </View>
        <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.chipRow}>
          {[heading('Catégorie: Canapés', '\u0627\u0644\u0642\u0633\u0645: \u0643\u0646\u0628\u0627\u062a'), heading('Couleur: Gris', '\u0627\u0644\u0644\u0648\u0646: \u0631\u0645\u0627\u062f\u064a'), heading('En stock', '\u0641\u064a \u0627\u0644\u0645\u062e\u0632\u0648\u0646')].map((chip) => <View key={chip} style={styles.chip}><MayushText variant="smallBody" color={colors.brand.navy900}>{chip}</MayushText><MayushIcon name="x" size={18} color={colors.neutral.gray700} /></View>)}
          <MayushText variant="smallBody" color={colors.brand.orange500} style={styles.clear}>{heading('Tout effacer', '\u0645\u0633\u062d \u0627\u0644\u0643\u0644')}</MayushText>
        </ScrollView>
        {loading ? <View style={styles.grid}>{[1,2,3,4].map((item) => <Skeleton key={item} width="48%" height={280} borderRadius="xl" />)}</View> : <View style={styles.grid}>{products.map((product) => <ProductCard key={product.id} variant="grid" name={product.name} subtitle={heading('Tissu gris clair', '\u0642\u0645\u0627\u0634 \u0631\u0645\u0627\u062f\u064a')} thumbnailUrl={product.thumbnail_image} currentPriceFormatted={product.main_price} originalPriceFormatted={product.stroked_price} hasDiscount={product.has_discount} discountPercentage={product.discount || undefined} onPress={() => onSelectProduct?.(product)} />)}</View>}
      </ScrollView>
      <BottomTabBar activeTab={activeTab} onTabPress={(tab) => onNavigateTab?.(tab)} cartBadgeCount={3} />
    </View>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: colors.surface.creamLight },
  content: { paddingHorizontal: 20, paddingTop: 18, paddingBottom: 28, gap: 17 },
  topbar: { height: 66, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' },
  rowReverse: { flexDirection: 'row-reverse' },
  iconButton: { width: 40, height: 40, alignItems: 'center', justifyContent: 'center' },
  rightActions: { flexDirection: 'row', alignItems: 'center', gap: 5 },
  cartAction: { position: 'relative', width: 39, height: 40, alignItems: 'center', justifyContent: 'center' },
  cartBadge: { position: 'absolute', top: -3, right: -3, minWidth: 20, height: 20, borderRadius: 10, alignItems: 'center', justifyContent: 'center', backgroundColor: colors.brand.orange500 },
  titleRow: { flexDirection: 'row', alignItems: 'center', gap: 14 },
  backButton: { width: 38, height: 38, alignItems: 'center', justifyContent: 'center' },
  titleContent: { flex: 1, minWidth: 0 },
  title: { fontSize: 34, lineHeight: 40 },
  filterRow: { flexDirection: 'row', gap: 10 },
  filterControl: { height: 54, flex: 0.98, flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 8, borderWidth: 1.5, borderColor: colors.surface.borderWarm, borderRadius: 14, backgroundColor: colors.surface.white },
  filterCount: { width: 24, height: 24, borderRadius: 12, alignItems: 'center', justifyContent: 'center', backgroundColor: colors.brand.orange500 },
  sortControl: { height: 54, flex: 1.45, flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 7, borderWidth: 1.5, borderColor: colors.surface.borderWarm, borderRadius: 14, backgroundColor: colors.surface.white },
  chipRow: { gap: 8, alignItems: 'center', paddingRight: 10 },
  chip: { flexDirection: 'row', alignItems: 'center', gap: 7, borderWidth: 1.2, borderColor: colors.surface.borderWarm, borderRadius: 20, backgroundColor: colors.surface.white, paddingHorizontal: 11, paddingVertical: 8 },
  clear: { marginLeft: 4, fontWeight: '600' },
  grid: { flexDirection: 'row', flexWrap: 'wrap', justifyContent: 'space-between', rowGap: 15 },
});
