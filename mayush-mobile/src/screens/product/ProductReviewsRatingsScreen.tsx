/**
 * ProductReviewsRatingsScreen (Figma Node 309:610 - 03-product-customer-reviews-ratings)
 * Verified buyer reviews, rating distribution, rating breakdown, and helpful votes.
 */

import React, { useState, useEffect, useCallback } from 'react';
import {
  ScrollView,
  StyleSheet,
  TouchableOpacity,
  View,
} from 'react-native';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { Skeleton } from '../../design-system/components/feedback/Skeleton';
import { useTheme } from '../../design-system/theme/useTheme';
import { colors } from '../../design-system/tokens/colors';
import { radii } from '../../design-system/tokens/radii';
import { apiClient } from '../../services/api/apiClient';

// ---------------------------------------------------------------------------
// Types
// ---------------------------------------------------------------------------

interface ReviewDto {
  id: number;
  user_id: number;
  product_id: number;
  rating: number;
  comment: string;
  status: number;
  created_at: string;
  updated_at: string;
  user?: { name: string; avatar?: string };
}

interface RatingDistribution {
  stars: number;
  pct: number;
}

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

function formatDate(isoString: string, language: string): string {
  try {
    const date = new Date(isoString);
    return date.toLocaleDateString(language === 'ar' ? 'ar-MA' : 'fr-MA', {
      day: 'numeric',
      month: 'long',
      year: 'numeric',
    });
  } catch {
    return isoString;
  }
}

function computeAggregates(reviews: ReviewDto[]): {
  average: string;
  count: number;
  distribution: RatingDistribution[];
} {
  const count = reviews.length;
  if (count === 0) {
    return {
      average: '0.0',
      count: 0,
      distribution: [5, 4, 3, 2, 1].map((stars) => ({ stars, pct: 0 })),
    };
  }

  const sum = reviews.reduce((acc, r) => acc + r.rating, 0);
  const average = (sum / count).toFixed(1);

  const distribution = [5, 4, 3, 2, 1].map((stars) => {
    const matched = reviews.filter((r) => Math.round(r.rating) === stars).length;
    return { stars, pct: Math.round((matched / count) * 100) };
  });

  return { average, count, distribution };
}

// ---------------------------------------------------------------------------
// Skeleton for loading state
// ---------------------------------------------------------------------------

const ReviewsSkeleton: React.FC = () => (
  <View style={skeletonStyles.container}>
    {/* Overview card skeleton */}
    <View style={skeletonStyles.overviewCard}>
      <View style={skeletonStyles.scoreCol}>
        <Skeleton width={60} height={40} borderRadius="lg" style={skeletonStyles.item} />
        <Skeleton width={90} height={14} borderRadius="sm" style={skeletonStyles.item} />
        <Skeleton width={70} height={10} borderRadius="sm" />
      </View>
      <View style={skeletonStyles.barsCol}>
        {[1, 2, 3, 4, 5].map((i) => (
          <View key={i} style={skeletonStyles.barRow}>
            <Skeleton width={28} height={10} borderRadius="sm" />
            <Skeleton width="100%" height={6} borderRadius="sm" style={skeletonStyles.barFill} />
            <Skeleton width={28} height={10} borderRadius="sm" />
          </View>
        ))}
      </View>
    </View>
    {/* Filter row skeleton */}
    <View style={skeletonStyles.filterRow}>
      <Skeleton width={60} height={30} borderRadius="full" />
      <Skeleton width={80} height={30} borderRadius="full" style={skeletonStyles.filterGap} />
      <Skeleton width={100} height={30} borderRadius="full" style={skeletonStyles.filterGap} />
    </View>
    {/* Review card skeletons */}
    {[1, 2].map((i) => (
      <View key={i} style={skeletonStyles.reviewCard}>
        <View style={skeletonStyles.revHeader}>
          <Skeleton width={120} height={14} borderRadius="sm" />
          <Skeleton width={80} height={10} borderRadius="sm" />
        </View>
        <Skeleton width={80} height={12} borderRadius="sm" style={skeletonStyles.item} />
        <Skeleton width="100%" height={14} borderRadius="sm" style={skeletonStyles.item} />
        <Skeleton width="80%" height={14} borderRadius="sm" style={skeletonStyles.item} />
        <Skeleton width={70} height={12} borderRadius="sm" style={skeletonStyles.item} />
      </View>
    ))}
  </View>
);

const skeletonStyles = StyleSheet.create({
  container: { padding: 20 },
  overviewCard: {
    flexDirection: 'row',
    padding: 16,
    borderRadius: radii.xl,
    backgroundColor: colors.surface.creamLight,
    borderWidth: 1,
    borderColor: colors.surface.borderWarm,
    marginBottom: 20,
  },
  scoreCol: { alignItems: 'center', justifyContent: 'center', paddingRight: 16, borderRightWidth: 1, borderRightColor: colors.surface.borderWarm },
  barsCol: { flex: 1, paddingLeft: 16, justifyContent: 'center' },
  barRow: { flexDirection: 'row', alignItems: 'center', marginBottom: 6, gap: 6 },
  barFill: { flex: 1 },
  filterRow: { flexDirection: 'row', marginBottom: 16 },
  filterGap: { marginLeft: 8 },
  reviewCard: {
    padding: 16,
    borderRadius: radii.lg,
    backgroundColor: colors.surface.white,
    borderWidth: 1,
    borderColor: colors.surface.borderWarm,
    marginBottom: 12,
  },
  revHeader: { flexDirection: 'row', justifyContent: 'space-between', marginBottom: 8 },
  item: { marginBottom: 6 },
});

// ---------------------------------------------------------------------------
// Props & component
// ---------------------------------------------------------------------------

export interface ProductReviewsRatingsScreenProps {
  productId?: number;
  productTitle?: string;
  onBack: () => void;
}

export const ProductReviewsRatingsScreen: React.FC<ProductReviewsRatingsScreenProps> = ({
  productId,
  productTitle = 'Fauteuil Lounge Luna',
  onBack,
}) => {
  const { isRTL, language } = useTheme();
  const [selectedFilter, setSelectedFilter] = useState('Tous');
  const [reviews, setReviews] = useState<ReviewDto[]>([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const fetchReviews = useCallback(async () => {
    if (!productId) return;
    setLoading(true);
    setError(null);
    try {
      const response = await apiClient<{ data: ReviewDto[] }>(
        `/api/v2/reviews/product/${productId}`
      );
      setReviews(response.data ?? []);
    } catch (err: unknown) {
      const message = err instanceof Error ? err.message : 'Erreur de chargement';
      setError(message);
      setReviews([]);
    } finally {
      setLoading(false);
    }
  }, [productId]);

  useEffect(() => {
    fetchReviews();
  }, [fetchReviews]);

  // Compute aggregates from real data
  const { average, count, distribution } = computeAggregates(reviews);

  // Apply filter
  const filteredReviews = reviews.filter((rev) => {
    if (selectedFilter === '5 étoiles') return Math.round(rev.rating) === 5;
    // "Avec photos" — backend may add photos later; for now show all
    return true;
  });

  // Determine filled stars count for overview
  const filledStars = Math.round(parseFloat(average));

  // ---------------------------------------------------------------------------
  // Render
  // ---------------------------------------------------------------------------

  return (
    <View style={styles.screen} accessibilityLabel="Product Reviews Ratings Screen">
      <View style={styles.header}>
        <TouchableOpacity accessibilityRole="button" onPress={onBack} style={styles.iconBtn}>
          <MayushIcon name={isRTL ? 'arrow-right' : 'arrow-left'} size={24} color={colors.brand.navy900} />
        </TouchableOpacity>
        <MayushText variant="sectionTitle" color={colors.brand.navy900} style={styles.headerTitle}>
          {language === 'ar' ? 'تقييمات العملاء' : 'Avis clients'}
        </MayushText>
        <View style={styles.iconBtn} />
      </View>

      {loading ? (
        <ReviewsSkeleton />
      ) : (
        <ScrollView contentContainerStyle={styles.content} showsVerticalScrollIndicator={false}>
          {/* Rating overview */}
          <View style={styles.ratingOverviewCard}>
            <View style={styles.scoreCol}>
              <MayushText variant="display" color={colors.brand.navy900} style={styles.scoreText}>
                {average}
              </MayushText>
              <View style={styles.starsRow}>
                {[1, 2, 3, 4, 5].map((star) => (
                  <MayushIcon
                    key={star}
                    name="star"
                    size={16}
                    color={star <= filledStars ? colors.semantic.warning : colors.neutral.gray300}
                  />
                ))}
              </View>
              <MayushText variant="caption" color={colors.neutral.gray700} style={styles.countText}>
                {language === 'ar'
                  ? `من ${count} تقييم موثق`
                  : `Sur ${count} avis vérifiés`}
              </MayushText>
            </View>

            <View style={styles.barsCol}>
              {distribution.map((row) => (
                <View key={row.stars} style={styles.barRow}>
                  <MayushText variant="caption" color={colors.neutral.gray700} style={styles.starLabel}>
                    {row.stars} ★
                  </MayushText>
                  <View style={styles.barTrack}>
                    <View style={[styles.barFill, { width: `${row.pct}%` }]} />
                  </View>
                  <MayushText variant="caption" color={colors.neutral.gray500} style={styles.pctLabel}>
                    {row.pct}%
                  </MayushText>
                </View>
              ))}
            </View>
          </View>

          {/* Filter chips */}
          <View style={styles.filterRow}>
            {['Tous', '5 étoiles', 'Avec photos'].map((f) => {
              const selected = selectedFilter === f;
              return (
                <TouchableOpacity
                  key={f}
                  style={[styles.filterChip, selected && styles.filterChipSelected]}
                  onPress={() => setSelectedFilter(f)}
                >
                  <MayushText variant="caption" color={selected ? colors.brand.orange500 : colors.brand.navy900}>
                    {f}
                  </MayushText>
                </TouchableOpacity>
              );
            })}
          </View>

          {/* Error state */}
          {error ? (
            <View style={styles.emptyState}>
              <MayushText variant="body" color={colors.neutral.gray500} style={styles.emptyText}>
                {language === 'ar' ? 'حدث خطأ أثناء التحميل' : 'Erreur lors du chargement des avis'}
              </MayushText>
            </View>
          ) : filteredReviews.length === 0 ? (
            <View style={styles.emptyState}>
              <MayushText variant="body" color={colors.neutral.gray500} style={styles.emptyText}>
                {language === 'ar' ? 'لا توجد تقييمات بعد' : 'Aucun avis pour le moment'}
              </MayushText>
            </View>
          ) : (
            filteredReviews.map((rev) => {
              const authorName = rev.user?.name ?? `User #${rev.user_id}`;
              const dateStr = formatDate(rev.created_at, language);

              return (
                <View key={rev.id} style={styles.reviewCard}>
                  <View style={styles.revHeader}>
                    <View style={styles.authorCol}>
                      <MayushText variant="strongBody" color={colors.brand.navy900}>
                        {authorName}
                      </MayushText>
                      <MayushText variant="caption" color={colors.brand.orange500} style={styles.verifiedTag}>
                        ✓ {language === 'ar' ? 'شراء موثق' : 'Achat vérifié'}
                      </MayushText>
                    </View>
                    <MayushText variant="caption" color={colors.neutral.gray500}>
                      {dateStr}
                    </MayushText>
                  </View>

                  <View style={styles.starsRowCompact}>
                    {[1, 2, 3, 4, 5].map((star) => (
                      <MayushIcon
                        key={star}
                        name="star"
                        size={14}
                        color={star <= Math.round(rev.rating) ? colors.semantic.warning : colors.neutral.gray300}
                      />
                    ))}
                  </View>

                  <MayushText variant="body" color={colors.brand.navy900} style={styles.revComment}>
                    {rev.comment}
                  </MayushText>

                  <TouchableOpacity style={styles.helpfulBtn}>
                    <MayushIcon name="thumbs-up" size={14} color={colors.neutral.gray700} />
                    <MayushText variant="caption" color={colors.neutral.gray700}>
                      {language === 'ar' ? 'مفيد' : 'Utile'}
                    </MayushText>
                  </TouchableOpacity>
                </View>
              );
            })
          )}
        </ScrollView>
      )}
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
  content: { padding: 20 },
  ratingOverviewCard: {
    flexDirection: 'row',
    padding: 16,
    borderRadius: radii.xl,
    backgroundColor: colors.surface.creamLight,
    borderWidth: 1,
    borderColor: colors.surface.borderWarm,
    marginBottom: 20,
  },
  scoreCol: { alignItems: 'center', justifyContent: 'center', paddingRight: 16, borderRightWidth: 1, borderRightColor: colors.surface.borderWarm },
  scoreText: { fontSize: 36, fontWeight: '800', lineHeight: 42 },
  starsRow: { flexDirection: 'row', gap: 2, marginVertical: 4 },
  countText: { fontSize: 11 },
  barsCol: { flex: 1, paddingLeft: 16, justifyContent: 'center' },
  barRow: { flexDirection: 'row', alignItems: 'center', marginBottom: 4 },
  starLabel: { width: 28, fontSize: 11 },
  barTrack: { flex: 1, height: 6, borderRadius: 3, backgroundColor: colors.surface.borderWarm, overflow: 'hidden', marginHorizontal: 6 },
  barFill: { height: '100%', backgroundColor: colors.semantic.warning },
  pctLabel: { width: 30, fontSize: 11, textAlign: 'right' },
  filterRow: { flexDirection: 'row', gap: 8, marginBottom: 16 },
  filterChip: {
    paddingHorizontal: 14,
    paddingVertical: 6,
    borderRadius: radii.full,
    backgroundColor: colors.surface.creamLight,
    borderWidth: 1,
    borderColor: colors.surface.borderWarm,
  },
  filterChipSelected: { backgroundColor: colors.brand.orange100, borderColor: colors.brand.orange500 },
  reviewCard: {
    padding: 16,
    borderRadius: radii.lg,
    backgroundColor: colors.surface.white,
    borderWidth: 1,
    borderColor: colors.surface.borderWarm,
    marginBottom: 12,
  },
  revHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'flex-start' },
  authorCol: { flexDirection: 'row', alignItems: 'center', gap: 6 },
  verifiedTag: { fontSize: 11, fontWeight: '600' },
  starsRowCompact: { flexDirection: 'row', gap: 2, marginVertical: 6 },
  revComment: { lineHeight: 20, marginBottom: 12 },
  helpfulBtn: { flexDirection: 'row', alignItems: 'center', gap: 6, alignSelf: 'flex-start' },
  emptyState: { alignItems: 'center', paddingVertical: 40 },
  emptyText: { textAlign: 'center' },
});
