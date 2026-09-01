import React, { useEffect, useState, useRef, useCallback } from 'react';
import {
  View,
  ScrollView,
  Image,
  TouchableOpacity,
  StyleSheet,
  useWindowDimensions,
  LayoutChangeEvent,
} from 'react-native';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { ProductCard } from '../../design-system/components/commerce/ProductCard';
import { Skeleton } from '../../design-system/components/feedback/Skeleton';
import { colors } from '../../design-system/tokens/colors';
import { useTheme } from '../../design-system/theme/useTheme';
import { TabKey } from '../../design-system/components/navigation/BottomTabBar';
import { ProductMiniDto } from '../../contracts/api/dto';
import { inspirationService, InspirationDetail, InspirationDetailItem } from '../../services/api/inspirationService';

interface InspirationDetailScreenProps {
  activeTab: TabKey;
  onBack: () => void;
  onNavigateTab: (tab: TabKey) => void;
  slug: string;
  onSelectProduct?: (product: ProductMiniDto) => void;
}

const MARKER_SIZE = 28;

const InspirationDetailScreen: React.FC<InspirationDetailScreenProps> = ({
  onBack,
  slug,
  onSelectProduct,
}) => {
  const { language, isRTL } = useTheme();
  const { width: screenWidth } = useWindowDimensions();
  const scrollRef = useRef<ScrollView>(null);
  const [data, setData] = useState<InspirationDetail | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(false);
  const [imageLayout, setImageLayout] = useState({ width: 0, height: 0 });
  const [highlightedItemId, setHighlightedItemId] = useState<number | null>(null);
  const [highlightedMarkerIdx, setHighlightedMarkerIdx] = useState<number | null>(null);
  const cardPositions = useRef<Record<number, number>>({});

  const heading = (fr: string, ar: string) => (isRTL ? ar : fr);
  const contentPadding = Math.max(16, Math.round(screenWidth * 0.04));
  const cardWidth = Math.floor((screenWidth - contentPadding * 2 - 12) / 2);

  useEffect(() => {
    let mounted = true;
    setLoading(true);
    setError(false);

    inspirationService.getBySlug(slug, language).then((result) => {
      if (mounted) {
        if (result) {
          setData(result);
        } else {
          setError(true);
        }
        setLoading(false);
      }
    });

    return () => { mounted = false; };
  }, [slug, language]);

  const handleImageLayout = useCallback((event: LayoutChangeEvent) => {
    const { width, height } = event.nativeEvent.layout;
    setImageLayout({ width, height });
  }, []);

  const handleMarkerPress = useCallback((item: InspirationDetailItem, _index: number) => {
    setHighlightedItemId(item.id);
    const yPos = cardPositions.current[item.id];
    if (yPos !== undefined) {
      scrollRef.current?.scrollTo({ y: yPos - 100, animated: true });
    }
    setTimeout(() => setHighlightedItemId(null), 800);
  }, []);

  const handleProductPress = useCallback((item: InspirationDetailItem, index: number) => {
    setHighlightedMarkerIdx(index);
    setTimeout(() => setHighlightedMarkerIdx(null), 800);

    if (onSelectProduct && item.product) {
      onSelectProduct({
        id: item.product.id,
        slug: item.product.slug,
        name: item.product.name,
        thumbnail_image: item.product.image,
        has_discount: !!item.product.discount_price,
        discount: item.product.discount_price ?? null,
        stroked_price: item.product.discount_price ?? item.product.price,
        main_price: item.product.price,
        rating: 0,
        sales: 0,
        links: { details: '' },
      });
    }
  }, [onSelectProduct]);

  // Loading skeleton
  if (loading) {
    return (
      <View style={[styles.container, { paddingHorizontal: contentPadding }]}>
        <View style={styles.header}>
          <TouchableOpacity onPress={onBack} style={styles.backButton}>
            <MayushIcon name={isRTL ? 'chevron-right' : 'chevron-left'} size={24} color={colors.brand.navy900} />
          </TouchableOpacity>
        </View>
        <Skeleton width="60%" height={24} borderRadius="sm" />
        <View style={{ height: 8 }} />
        <Skeleton width="80%" height={16} borderRadius="sm" />
        <View style={{ height: 16 }} />
        <Skeleton width="100%" height={Math.round(screenWidth * 0.65)} borderRadius="lg" />
        <View style={{ height: 16 }} />
        <View style={styles.productGrid}>
          {Array.from({ length: 4 }).map((_, i) => (
            <Skeleton key={i} width={cardWidth} height={cardWidth * 1.4} borderRadius="md" />
          ))}
        </View>
      </View>
    );
  }

  // Error state
  if (error || !data) {
    return (
      <View style={[styles.container, styles.centered, { paddingHorizontal: contentPadding }]}>
        <View style={styles.header}>
          <TouchableOpacity onPress={onBack} style={styles.backButton}>
            <MayushIcon name={isRTL ? 'chevron-right' : 'chevron-left'} size={24} color={colors.brand.navy900} />
          </TouchableOpacity>
        </View>
        <MayushText variant="body" color={colors.neutral.gray500} align="center">
          {heading("Impossible de charger l\u2019inspiration", '\u062a\u0639\u0630\u0631 \u062a\u062d\u0645\u064a\u0644 \u0627\u0644\u0625\u0644\u0647\u0627\u0645')}
        </MayushText>
        <TouchableOpacity
          style={styles.retryButton}
          onPress={() => {
            setLoading(true);
            setError(false);
            inspirationService.getBySlug(slug, language).then((r) => {
              setData(r);
              setLoading(false);
            }).catch(() => {
              setError(true);
              setLoading(false);
            });
          }}
        >
          <MayushText variant="body" color={colors.brand.orange500}>
            {heading('R\u00e9essayer', '\u0625\u0639\u0627\u062f\u0629 \u0627\u0644\u0645\u062d\u0627\u0648\u0644\u0629')}
          </MayushText>
        </TouchableOpacity>
      </View>
    );
  }

  // Main content
  const items = data.items || [];
  const imageAspect = data.image.width && data.image.height
    ? data.image.height / data.image.width
    : 0.65;
  const imageDisplayHeight = Math.round(screenWidth * imageAspect);

  return (
    <View style={styles.container}>
      <View style={[styles.header, { paddingHorizontal: contentPadding }]}>
        <TouchableOpacity onPress={onBack} style={styles.backButton}>
          <MayushIcon name={isRTL ? 'chevron-right' : 'chevron-left'} size={24} color={colors.brand.navy900} />
        </TouchableOpacity>
      </View>

      <ScrollView ref={scrollRef} showsVerticalScrollIndicator={false}>
        <View style={{ paddingHorizontal: contentPadding, marginBottom: 12 }}>
          <MayushText variant="sectionTitle" color={colors.brand.navy900} style={{ fontSize: 22 }}>
            {data.title}
          </MayushText>
          {data.subtitle ? (
            <MayushText variant="body" color={colors.neutral.gray700} style={{ marginTop: 4 }}>
              {data.subtitle}
            </MayushText>
          ) : null}
        </View>

        {/* Scene image with hotspot markers */}
        <View
          style={[styles.imageContainer, { height: imageDisplayHeight }]}
          onLayout={handleImageLayout}
        >
          <Image
            source={{ uri: data.image.url }}
            style={{ width: screenWidth, height: imageDisplayHeight }}
            resizeMode="cover"
          />
          {imageLayout.width > 0 && items.map((item, idx) => {
            if (!item.hotspot) return null;
            const isHighlighted = highlightedMarkerIdx === idx;
            return (
              <TouchableOpacity
                key={item.id}
                activeOpacity={0.8}
                style={[
                  styles.marker,
                  {
                    left: item.hotspot.x * imageLayout.width - MARKER_SIZE / 2,
                    top: item.hotspot.y * imageLayout.height - MARKER_SIZE / 2,
                    backgroundColor: item.product.available ? colors.brand.navy900 : colors.neutral.gray500,
                    transform: isHighlighted ? [{ scale: 1.3 }] : [{ scale: 1 }],
                  },
                ]}
                onPress={() => handleMarkerPress(item, idx)}
              >
                <MayushText variant="smallBody" color={colors.interactive.textInverse} style={styles.markerText}>
                  {idx + 1}
                </MayushText>
              </TouchableOpacity>
            );
          })}
        </View>

        <View style={{ paddingHorizontal: contentPadding, marginTop: 16, marginBottom: 12 }}>
          <MayushText variant="body" color={colors.brand.navy900} style={{ fontWeight: '700' }}>
            {items.length} {heading('articles dans cette ambiance', '\u0645\u0646\u062a\u062c\u0627\u062a \u0641\u064a \u0647\u0630\u0627 \u0627\u0644\u0625\u0644\u0647\u0627\u0645')}
          </MayushText>
        </View>

        {/* Product grid - 2 columns */}
        <View style={[styles.productGrid, { paddingHorizontal: contentPadding }]}>
          {items.map((item, idx) => (
            <View
              key={item.id}
              style={{ width: cardWidth, marginBottom: 12, opacity: item.product.available ? 1 : 0.5 }}
              onLayout={(e) => { cardPositions.current[item.id] = e.nativeEvent.layout.y; }}
            >
              <ProductCard
                name={item.product.name}
                thumbnailUrl={item.product.image}
                currentPriceFormatted={item.product.price}
                originalPriceFormatted={item.product.discount_price ?? undefined}
                hasDiscount={!!item.product.discount_price}
                inStock={item.product.available}
                width={cardWidth}
                variant="grid"
                onPress={() => handleProductPress(item, idx)}
                style={highlightedItemId === item.id ? styles.highlightedCard : undefined}
              />
              {!item.product.available && (
                <View style={styles.unavailableBadge}>
                  <MayushText variant="smallBody" color={colors.interactive.textInverse} style={{ fontSize: 10, fontWeight: '600' }}>
                    {heading('Indisponible', '\u063a\u064a\u0631 \u0645\u062a\u0627\u062d')}
                  </MayushText>
                </View>
              )}
            </View>
          ))}
        </View>

        <View style={{ height: 40 }} />
      </ScrollView>
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: colors.surface.white,
  },
  centered: {
    justifyContent: 'center',
    alignItems: 'center',
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 12,
  },
  backButton: {
    width: 40,
    height: 40,
    justifyContent: 'center',
  },
  imageContainer: {
    position: 'relative',
    width: '100%',
    overflow: 'hidden',
  },
  marker: {
    position: 'absolute',
    width: MARKER_SIZE,
    height: MARKER_SIZE,
    borderRadius: MARKER_SIZE / 2,
    borderWidth: 2,
    borderColor: colors.surface.white,
    justifyContent: 'center',
    alignItems: 'center',
    elevation: 4,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.25,
    shadowRadius: 4,
  },
  markerText: {
    fontSize: 11,
    fontWeight: '700',
  },
  productGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    justifyContent: 'space-between',
  },
  highlightedCard: {
    borderWidth: 2,
    borderColor: colors.brand.orange500,
    borderRadius: 12,
  },
  unavailableBadge: {
    position: 'absolute',
    top: 8,
    left: 8,
    backgroundColor: colors.neutral.gray700,
    paddingHorizontal: 8,
    paddingVertical: 2,
    borderRadius: 4,
  },
  retryButton: {
    marginTop: 16,
    padding: 12,
  },
});

export default InspirationDetailScreen;
