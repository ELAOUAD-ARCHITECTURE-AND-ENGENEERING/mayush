/**
 * ProductReviewsRatingsScreen (Figma Node 309:610 - 03-product-customer-reviews-ratings)
 * Verified buyer reviews, rating distribution, rating breakdown, and helpful votes.
 */

import React, { useState } from 'react';
import {
  ScrollView,
  StyleSheet,
  TouchableOpacity,
  View,
} from 'react-native';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { useTheme } from '../../design-system/theme/useTheme';
import { colors } from '../../design-system/tokens/colors';
import { radii } from '../../design-system/tokens/radii';

export interface ProductReviewsRatingsScreenProps {
  productTitle?: string;
  onBack: () => void;
}

export const ProductReviewsRatingsScreen: React.FC<ProductReviewsRatingsScreenProps> = ({
  productTitle = 'Fauteuil Lounge Luna',
  onBack,
}) => {
  const { isRTL, language } = useTheme();
  const [selectedFilter, setSelectedFilter] = useState('Tous');

  const reviews = [
    {
      id: 1,
      author: 'Youssef E.',
      date: '14 Janvier 2026',
      rating: 5,
      comment: language === 'ar' ? 'أريكة ممتازة، جودة الخشب والقماش عالية جداً. التوصيل كان سريعاً في الدار البيضاء.' : 'Qualité exceptionnelle ! Le tissu bouclé est d’une grande douceur et l’assise est extrêmement confortable. Livraison très soignée à Casablanca.',
      helpfulCount: 14,
      verified: true,
    },
    {
      id: 2,
      author: 'Salma B.',
      date: '28 Décembre 2025',
      rating: 5,
      comment: language === 'ar' ? 'تصميم رائع يجمع بين البساطة والأناقة. أنصح به بشدة.' : 'Magnifique fauteuil qui sublime mon salon. Conforme en tous points aux photos de la présentation.',
      helpfulCount: 8,
      verified: true,
    },
  ];

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

      <ScrollView contentContainerStyle={styles.content} showsVerticalScrollIndicator={false}>
        <View style={styles.ratingOverviewCard}>
          <View style={styles.scoreCol}>
            <MayushText variant="display" color={colors.brand.navy900} style={styles.scoreText}>
              4.9
            </MayushText>
            <View style={styles.starsRow}>
              {[1, 2, 3, 4, 5].map((star) => (
                <MayushIcon key={star} name="star" size={16} color={colors.semantic.warning} />
              ))}
            </View>
            <MayushText variant="caption" color={colors.neutral.gray700} style={styles.countText}>
              Sur 32 avis vérifiés
            </MayushText>
          </View>

          <View style={styles.barsCol}>
            {[
              { stars: 5, pct: 90 },
              { stars: 4, pct: 10 },
              { stars: 3, pct: 0 },
              { stars: 2, pct: 0 },
              { stars: 1, pct: 0 },
            ].map((row) => (
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

        {reviews.map((rev) => (
          <View key={rev.id} style={styles.reviewCard}>
            <View style={styles.revHeader}>
              <View style={styles.authorCol}>
                <MayushText variant="strongBody" color={colors.brand.navy900}>
                  {rev.author}
                </MayushText>
                {rev.verified ? (
                  <MayushText variant="caption" color={colors.brand.orange500} style={styles.verifiedTag}>
                    ✓ Achat vérifié
                  </MayushText>
                ) : null}
              </View>
              <MayushText variant="caption" color={colors.neutral.gray500}>
                {rev.date}
              </MayushText>
            </View>

            <View style={styles.starsRowCompact}>
              {[1, 2, 3, 4, 5].map((star) => (
                <MayushIcon key={star} name="star" size={14} color={star <= rev.rating ? colors.semantic.warning : colors.neutral.gray300} />
              ))}
            </View>

            <MayushText variant="body" color={colors.brand.navy900} style={styles.revComment}>
              {rev.comment}
            </MayushText>

            <TouchableOpacity style={styles.helpfulBtn}>
              <MayushIcon name="thumbs-up" size={14} color={colors.neutral.gray700} />
              <MayushText variant="caption" color={colors.neutral.gray700}>
                Utile ({rev.helpfulCount})
              </MayushText>
            </TouchableOpacity>
          </View>
        ))}
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
});
