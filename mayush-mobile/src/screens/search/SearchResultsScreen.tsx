/**
 * SearchResultsScreen (Figma Node 309:601 - 02-search-results-grid-fauteuil)
 * Grid of search results with live Laravel API query search, pagination, and product detail routing.
 */

import React, { useCallback, useEffect, useState } from 'react';
import {
  ActivityIndicator,
  Image,
  ScrollView,
  StyleSheet,
  TouchableOpacity,
  View,
} from 'react-native';
import { ProductMiniDto } from '../../contracts/api/dto';
import { normalizeImageUrl } from '../../contracts/mappers/imageNormalizer';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { useTheme } from '../../design-system/theme/useTheme';
import { colors } from '../../design-system/tokens/colors';
import { radii } from '../../design-system/tokens/radii';
import { catalogService } from '../../services/api/catalogService';
import { ProductGridSkeleton } from '../../presentation/catalog/skeletons';
import { SearchFilterSheet } from './SearchFilterSheet';

export interface SearchResultsScreenProps {
  searchQuery: string;
  onBack: () => void;
  onOpenFilter: () => void;
  onSelectProduct: (product: ProductMiniDto) => void;
  onToggleWishlist: (product: ProductMiniDto) => void;
  wishlistedProductIds?: number[];
}

export const SearchResultsScreen: React.FC<SearchResultsScreenProps> = ({
  searchQuery,
  onBack,
  onOpenFilter,
  onSelectProduct,
  onToggleWishlist,
  wishlistedProductIds = [],
}) => {
  const { isRTL, language } = useTheme();
  const [loading, setLoading] = useState(true);
  const [loadingMore, setLoadingMore] = useState(false);
  const [products, setProducts] = useState<ProductMiniDto[]>([]);
  const [totalCount, setTotalCount] = useState(0);
  const [currentPage, setCurrentPage] = useState(1);
  const [lastPage, setLastPage] = useState(1);
  const [filterVisible, setFilterVisible] = useState(false);
  const [selectedCategory, setSelectedCategory] = useState<string | undefined>();
  const [selectedBrand, setSelectedBrand] = useState<string | undefined>();
  const [minPrice, setMinPrice] = useState<number | undefined>();
  const [maxPrice, setMaxPrice] = useState<number | undefined>();
  const [sortKey, setSortKey] = useState<string | undefined>();

  const loadResults = useCallback((page: number = 1, append: boolean = false) => {
    if (append) {
      setLoadingMore(true);
    } else {
      setLoading(true);
    }

    catalogService
      .searchProducts({
        name: searchQuery,
        page,
        category: selectedCategory,
        brand: selectedBrand,
        min: minPrice,
        max: maxPrice,
        sort_key: sortKey,
      }, language)
      .then((res) => {
        const newProducts = res.data || [];
        setProducts(prev => append ? [...prev, ...newProducts] : newProducts);
        setTotalCount(res.meta?.total || newProducts.length);
        setCurrentPage(res.meta?.current_page || page);
        setLastPage(res.meta?.last_page || 1);
        setLoading(false);
        setLoadingMore(false);
      })
      .catch(() => {
        setLoading(false);
        setLoadingMore(false);
      });
  }, [searchQuery, language, selectedCategory, selectedBrand, minPrice, maxPrice, sortKey]);

  useEffect(() => {
    setProducts([]);
    setCurrentPage(1);
    loadResults(1, false);
  }, [loadResults]);

  const handleLoadMore = () => {
    if (!loadingMore && currentPage < lastPage) {
      loadResults(currentPage + 1, true);
    }
  };

  const hasMore = currentPage < lastPage;

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
            {totalCount} {language === 'ar' ? 'نتائج' : 'résultats trouvés'}
          </MayushText>
        </View>
        <TouchableOpacity accessibilityRole="button" onPress={onOpenFilter} style={styles.iconBtn}>
          <MayushIcon name="sliders" size={22} color={colors.brand.orange500} />
        </TouchableOpacity>
      </View>

      <View style={styles.subHeaderBar}>
        <TouchableOpacity style={styles.filterChip} onPress={() => setFilterVisible(true)}>
          <MayushIcon name="sliders" size={16} color={colors.brand.navy900} />
          <MayushText variant="smallBody" color={colors.brand.navy900}>
            {language === 'ar' ? 'تصفية' : 'Filtrer'}
          </MayushText>
        </TouchableOpacity>
        <TouchableOpacity style={styles.filterChip} onPress={() => setFilterVisible(true)}>
          <MayushIcon name="chevron-down" size={16} color={colors.brand.navy900} />
          <MayushText variant="smallBody" color={colors.brand.navy900}>
            {language === 'ar' ? 'ترتيب حسب' : 'Trier par'}
          </MayushText>
        </TouchableOpacity>
      </View>

      {loading ? (
        <View style={styles.content}>
          <ProductGridSkeleton count={6} />
        </View>
      ) : (
        <ScrollView contentContainerStyle={styles.content} showsVerticalScrollIndicator={false}>
          {products.length === 0 ? (
            <View style={styles.emptyContainer}>
              <MayushText variant="strongBody" color={colors.neutral.gray700}>
                {language === 'ar' ? 'لم يتم العثور على نتائج' : 'Aucun produit trouvé'}
              </MayushText>
            </View>
          ) : (
            <>
              <View style={styles.grid}>
                {products.map((product) => {
                  const imageUrl = product.thumbnail_image ? normalizeImageUrl(product.thumbnail_image) : '';

                  return (
                    <TouchableOpacity
                      key={product.id}
                      style={styles.card}
                      onPress={() => onSelectProduct(product)}
                      activeOpacity={0.8}
                    >
                      <View style={styles.imgBox}>
                        {imageUrl ? (
                          <Image source={{ uri: imageUrl }} style={styles.productImg} resizeMode="cover" />
                        ) : (
                          <View style={[styles.productImg, styles.noImagePlaceholder]}>
                            <MayushIcon name="image" size={28} color={colors.neutral.gray500} />
                          </View>
                        )}
                        <TouchableOpacity
                          style={styles.wishlistBtn}
                          onPress={() => onToggleWishlist(product)}
                        >
                          <MayushIcon name="heart" size={18} color={wishlistedProductIds.includes(product.id) ? colors.brand.orange500 : colors.neutral.gray500} />
                        </TouchableOpacity>
                      </View>
                      <View style={styles.cardBody}>
                        <MayushText
                          variant="strongBody"
                          color={colors.brand.navy900}
                          numberOfLines={2}
                          style={styles.prodName}
                        >
                          {product.name}
                        </MayushText>
                        <MayushText variant="display" color={colors.brand.orange500} style={styles.prodPrice}>
                          {product.main_price || product.formattedPrice || `${product.base_discounted_price || 0} MAD`}
                        </MayushText>
                      </View>
                    </TouchableOpacity>
                  );
                })}
              </View>

              {hasMore && (
                <TouchableOpacity
                  onPress={handleLoadMore}
                  disabled={loadingMore}
                  activeOpacity={0.82}
                  style={styles.loadMoreButton}
                >
                  {loadingMore ? (
                    <ActivityIndicator size="small" color={colors.brand.orange500} />
                  ) : (
                    <MayushText variant="strongBody" color={colors.brand.orange500}>
                      {language === 'ar'
                        ? `عرض المزيد (${products.length}/${totalCount})`
                        : `Voir plus (${products.length}/${totalCount})`}
                    </MayushText>
                  )}
                </TouchableOpacity>
              )}

              {!hasMore && products.length > 0 && (
                <MayushText variant="caption" color={colors.neutral.gray500} align="center" style={styles.endText}>
                  {language === 'ar' ? `${totalCount} نتيجة` : `${totalCount} résultats affichés`}
                </MayushText>
              )}
            </>
          )}
        </ScrollView>
      )}

      <SearchFilterSheet
        visible={filterVisible}
        language={language}
        currentFilters={{ category: selectedCategory, brand: selectedBrand, min: minPrice, max: maxPrice, sortKey }}
        onApply={(filters) => {
          setSelectedCategory(filters.category);
          setSelectedBrand(filters.brand);
          setMinPrice(filters.min);
          setMaxPrice(filters.max);
          setSortKey(filters.sortKey);
          setFilterVisible(false);
        }}
        onClose={() => setFilterVisible(false)}
      />
    </View>
  );
};

const styles = StyleSheet.create({
  screen: {
    flex: 1,
    backgroundColor: colors.surface.creamLight,
  },
  header: {
    height: 60,
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 16,
    borderBottomWidth: 1,
    borderBottomColor: colors.surface.borderWarm,
    backgroundColor: colors.surface.white,
  },
  iconBtn: {
    width: 36,
    height: 36,
    alignItems: 'center',
    justifyContent: 'center',
  },
  headerTitleBox: {
    flex: 1,
    alignItems: 'center',
  },
  subHeaderBar: {
    flexDirection: 'row',
    paddingHorizontal: 16,
    paddingVertical: 10,
    gap: 10,
    backgroundColor: colors.surface.white,
    borderBottomWidth: 1,
    borderBottomColor: colors.surface.borderWarm,
  },
  filterChip: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: radii.full,
    borderWidth: 1,
    borderColor: colors.surface.borderWarm,
    backgroundColor: colors.surface.creamLight,
  },
  emptyContainer: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 60,
  },
  content: {
    padding: 16,
  },
  grid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    justifyContent: 'space-between',
    gap: 12,
  },
  card: {
    width: '48%',
    backgroundColor: colors.surface.white,
    borderRadius: radii.lg,
    overflow: 'hidden',
    borderWidth: 1,
    borderColor: colors.surface.borderWarm,
  },
  imgBox: {
    height: 140,
    position: 'relative',
    backgroundColor: colors.neutral.gray100,
  },
  productImg: {
    width: '100%',
    height: '100%',
  },
  noImagePlaceholder: {
    backgroundColor: '#F5ECE0',
    alignItems: 'center',
    justifyContent: 'center',
  },
  wishlistBtn: {
    position: 'absolute',
    top: 8,
    right: 8,
    width: 30,
    height: 30,
    borderRadius: 15,
    backgroundColor: colors.surface.white,
    alignItems: 'center',
    justifyContent: 'center',
  },
  cardBody: {
    padding: 10,
    gap: 4,
  },
  prodName: {
    fontSize: 13,
    lineHeight: 17,
  },
  prodPrice: {
    fontSize: 15,
    marginTop: 2,
  },
  loadMoreButton: {
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 14,
    marginTop: 12,
    borderWidth: 1.5,
    borderColor: colors.brand.orange500,
    borderRadius: 12,
    backgroundColor: colors.surface.white,
  },
  endText: {
    marginTop: 8,
    paddingVertical: 10,
  },
});
