import React from 'react';
import { View, Image, ImageSourcePropType, TouchableOpacity, StyleProp, ViewStyle, StyleSheet } from 'react-native';
import { MayushText } from '../typography/MayushText';
import { MayushIcon } from '../navigation/MayushIcon';
import { colors } from '../../tokens/colors';

export type ProductCardVariant = 'compact' | 'grid';

export interface ProductCardProps {
  name: string;
  thumbnailUrl: string;
  thumbnailSource?: ImageSourcePropType;
  currentPriceFormatted: string;
  originalPriceFormatted?: string;
  hasDiscount?: boolean;
  discountPercentage?: string;
  rating?: number;
  salesCount?: number;
  subtitle?: string;
  inStock?: boolean;
  width?: number | string;
  variant?: ProductCardVariant;
  style?: StyleProp<ViewStyle>;
  onPress: () => void;
  onFavoritePress?: () => void;
  isFavorite?: boolean;
}

export const ProductCard: React.FC<ProductCardProps> = ({
  name,
  thumbnailUrl,
  thumbnailSource,
  currentPriceFormatted,
  originalPriceFormatted,
  hasDiscount = false,
  discountPercentage,
  rating,
  salesCount,
  subtitle,
  inStock = true,
  width,
  variant = 'compact',
  style,
  onPress,
  onFavoritePress,
  isFavorite = false,
}) => {
  const grid = variant === 'grid';
  const cardWidth = width ?? (grid ? '48%' : 164);

  return (
    <TouchableOpacity activeOpacity={0.85} onPress={onPress} style={[{ width: cardWidth as never }, style]}>
      <View style={[styles.card, grid && styles.gridCard]}>
        <View style={[styles.imageWrap, grid ? styles.gridImage : styles.compactImage]}>
          <Image source={thumbnailSource ?? { uri: thumbnailUrl || 'https://via.placeholder.com/320' }} style={styles.image} resizeMode="cover" />
          {hasDiscount && discountPercentage ? (
            <View style={styles.discountBadge}>
              <MayushText variant="caption" color={colors.surface.white} style={styles.discountText}>
                {discountPercentage.startsWith('-') ? discountPercentage : `-${discountPercentage}`}
              </MayushText>
            </View>
          ) : null}
          <TouchableOpacity
            activeOpacity={0.72}
            onPress={onFavoritePress}
            style={styles.favorite}
            accessibilityRole="button"
            accessibilityLabel="Ajouter aux favoris"
          >
            <MayushIcon name="heart" size={grid ? 22 : 20} color={isFavorite ? colors.brand.orange500 : colors.brand.navy900} />
          </TouchableOpacity>
        </View>

        <View style={[styles.details, grid && styles.gridDetails]}>
          <MayushText variant={grid ? 'strongBody' : 'smallBody'} color={colors.brand.navy900} numberOfLines={2} style={[styles.name, grid && styles.gridName]}>
            {name}
          </MayushText>
          {subtitle ? <MayushText variant="caption" color={colors.neutral.gray700} numberOfLines={1}>{subtitle}</MayushText> : null}
          <View style={styles.priceRow}>
            <MayushText variant={grid ? 'strongBody' : 'smallBody'} color={colors.brand.orange500} style={styles.price}>
              {currentPriceFormatted}
            </MayushText>
            {originalPriceFormatted ? <MayushText variant="caption" color={colors.neutral.gray500} style={styles.originalPrice}>{originalPriceFormatted}</MayushText> : null}
          </View>
          {grid ? (
            <View style={styles.stockRow}>
              <View style={[styles.stockDot, { backgroundColor: inStock ? colors.semantic.success : colors.semantic.error }]} />
              <MayushText variant="caption" color={inStock ? colors.semantic.success : colors.semantic.error}>{inStock ? 'En stock' : 'Rupture'}</MayushText>
            </View>
          ) : rating ? (
            <View style={styles.ratingRow}>
              <MayushText variant="caption" color={colors.brand.orange500}>{'★★★★★'}</MayushText>
              {salesCount ? <MayushText variant="caption" color={colors.neutral.gray700}>({salesCount})</MayushText> : null}
            </View>
          ) : null}
        </View>
      </View>
    </TouchableOpacity>
  );
};

const styles = StyleSheet.create({
  card: {
    overflow: 'hidden',
    backgroundColor: colors.surface.white,
    borderRadius: 14,
    borderWidth: 1,
    borderColor: colors.surface.borderWarm,
    shadowColor: '#101D35',
    shadowOpacity: 0.055,
    shadowRadius: 8,
    shadowOffset: { width: 0, height: 3 },
    elevation: 2,
  },
  gridCard: { borderRadius: 13 },
  imageWrap: { position: 'relative', overflow: 'hidden', backgroundColor: colors.surface.cream },
  compactImage: { aspectRatio: 1.48 },
  gridImage: { aspectRatio: 1.18 },
  image: { width: '100%', height: '100%' },
  discountBadge: {
    position: 'absolute',
    top: 8,
    left: 8,
    backgroundColor: '#E53935',
    borderRadius: 6,
    paddingHorizontal: 7,
    paddingVertical: 4,
  },
  discountText: { fontWeight: '700' },
  favorite: {
    position: 'absolute',
    right: 8,
    top: 8,
    width: 32,
    height: 32,
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: 'rgba(255,255,255,0.95)',
    borderRadius: 16,
  },
  details: { paddingHorizontal: 10, paddingTop: 8, paddingBottom: 9, gap: 3 },
  gridDetails: { paddingHorizontal: 11, paddingVertical: 10, gap: 4 },
  name: { minHeight: 18, fontWeight: '600' },
  gridName: { minHeight: 36, lineHeight: 18 },
  priceRow: { flexDirection: 'row', flexWrap: 'wrap', alignItems: 'baseline', gap: 7 },
  price: { fontWeight: '700' },
  originalPrice: { textDecorationLine: 'line-through' },
  ratingRow: { flexDirection: 'row', alignItems: 'center', gap: 3 },
  stockRow: { flexDirection: 'row', alignItems: 'center', gap: 5, marginTop: 1 },
  stockDot: { width: 7, height: 7, borderRadius: 4 },
});
