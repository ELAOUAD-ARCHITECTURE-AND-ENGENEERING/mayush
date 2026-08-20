/**
 * FlashDealsScreen (Figma Node 309:597 - 02-flash-deals-countdown-timer)
 * Time-limited promotional flash sales screen with live countdown timer.
 */

import React, { useEffect, useRef, useState } from 'react';
import {
  ActivityIndicator,
  Image,
  ScrollView,
  StyleSheet,
  TouchableOpacity,
  View,
} from 'react-native';
import { ProductMiniDto } from '../../contracts/api/dto';
import { apiClient } from '../../services/api/apiClient';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { useTheme } from '../../design-system/theme/useTheme';
import { colors } from '../../design-system/tokens/colors';
import { radii } from '../../design-system/tokens/radii';

// ── API shapes ────────────────────────────────────────────────────────────────

interface FlashDealItem {
  id: number;
  title: string;
  end_date: string;   // ISO date string
  banner_image?: string;
  slug?: string;
}

interface FlashDealResponse {
  data: FlashDealItem[];
}

interface FlashDealProductRaw {
  id: number;
  name: string;
  thumbnail_image: string;
  base_price: string;
  base_discounted_price: string;
  discount: number;
  slug: string;
}

interface FlashDealProductsResponse {
  data: FlashDealProductRaw[];
}

// ── Local extended type ───────────────────────────────────────────────────────

type FlashItem = ProductMiniDto & { discountPercent: number; originalPriceMad: number };

// ── Helpers ───────────────────────────────────────────────────────────────────

function msToHMS(ms: number): { hours: number; minutes: number; seconds: number } {
  if (ms <= 0) return { hours: 0, minutes: 0, seconds: 0 };
  const totalSeconds = Math.floor(ms / 1000);
  const hours = Math.floor(totalSeconds / 3600);
  const minutes = Math.floor((totalSeconds % 3600) / 60);
  const seconds = totalSeconds % 60;
  return { hours, minutes, seconds };
}

function formatMAD(value: string | number): string {
  const n = typeof value === 'string' ? parseFloat(value) : value;
  if (isNaN(n)) return `${value} MAD`;
  return `${n.toLocaleString('fr-FR')} MAD`;
}

// ── Component ─────────────────────────────────────────────────────────────────

export interface FlashDealsScreenProps {
  onBack: () => void;
  onSelectProduct: (product: ProductMiniDto) => void;
  onOpenProductDetails?: (productId: number) => void;
}

export const FlashDealsScreen: React.FC<FlashDealsScreenProps> = ({
  onBack,
  onSelectProduct,
}) => {
  const { isRTL, language } = useTheme();

  const [timeLeft, setTimeLeft] = useState({ hours: 0, minutes: 0, seconds: 0 });
  const [flashItems, setFlashItems] = useState<FlashItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  // Keep end date in a ref so the timer interval always has the latest value
  const endDateRef = useRef<Date | null>(null);

  // ── Fetch flash deals then fetch products for the first active deal ──────
  useEffect(() => {
    let cancelled = false;

    async function fetchData() {
      try {
        setLoading(true);
        setError(null);

        const dealsResp = await apiClient<FlashDealResponse>('/api/v2/flash-deals', {
          language,
        });

        if (cancelled) return;

        const deals = Array.isArray(dealsResp?.data) ? dealsResp.data : [];

        if (deals.length === 0) {
          setFlashItems([]);
          setLoading(false);
          return;
        }

        // Use the first deal for the countdown and product list
        const deal = deals[0];
        const endDate = new Date(deal.end_date);
        endDateRef.current = endDate;
        setTimeLeft(msToHMS(endDate.getTime() - Date.now()));

        // Fetch products for this deal
        const productsResp = await apiClient<FlashDealProductsResponse>(
          `/api/v2/flash-deal-products/${deal.id}`,
          { language }
        );

        if (cancelled) return;

        const rawProducts: FlashDealProductRaw[] = Array.isArray(productsResp?.data)
          ? productsResp.data
          : [];

        const mapped: FlashItem[] = rawProducts.map((p) => {
          const basePrice = parseFloat(p.base_price) || 0;
          const discountedPrice = parseFloat(p.base_discounted_price) || basePrice;
          return {
            id: p.id,
            slug: p.slug,
            name: p.name,
            thumbnail_image: p.thumbnail_image,
            priceMad: discountedPrice,
            originalPriceMad: basePrice,
            formattedPrice: formatMAD(discountedPrice),
            base_price: basePrice,
            base_discounted_price: discountedPrice,
            has_discount: p.discount > 0,
            discount: p.discount > 0 ? `-${p.discount}%` : null,
            discountPercent: p.discount,
            stroked_price: formatMAD(basePrice),
            main_price: formatMAD(discountedPrice),
            rating: 0,
            sales: 0,
            links: { details: '' },
          };
        });

        setFlashItems(mapped);
      } catch (err: any) {
        if (!cancelled) {
          setError(err?.message || 'Erreur de chargement');
        }
      } finally {
        if (!cancelled) setLoading(false);
      }
    }

    fetchData();
    return () => { cancelled = true; };
  }, [language]);

  // ── Countdown timer — ticks every second, reads endDateRef ───────────────
  useEffect(() => {
    const timer = setInterval(() => {
      if (endDateRef.current) {
        setTimeLeft(msToHMS(endDateRef.current.getTime() - Date.now()));
      } else {
        // Fallback decrement (before data loads)
        setTimeLeft((prev) => {
          if (prev.seconds > 0) return { ...prev, seconds: prev.seconds - 1 };
          if (prev.minutes > 0) return { ...prev, minutes: prev.minutes - 1, seconds: 59 };
          if (prev.hours > 0) return { hours: prev.hours - 1, minutes: 59, seconds: 59 };
          return prev;
        });
      }
    }, 1000);
    return () => clearInterval(timer);
  }, []);

  // ── Render ────────────────────────────────────────────────────────────────

  return (
    <View style={styles.screen} accessibilityLabel="Flash Deals Screen">
      <View style={styles.header}>
        <TouchableOpacity accessibilityRole="button" onPress={onBack} style={styles.iconBtn}>
          <MayushIcon name={isRTL ? 'arrow-right' : 'arrow-left'} size={24} color={colors.brand.navy900} />
        </TouchableOpacity>
        <MayushText variant="sectionTitle" color={colors.brand.navy900} style={styles.headerTitle}>
          {language === 'ar' ? 'العروض السريعة' : 'Ventes Flash'}
        </MayushText>
        <View style={styles.iconBtn} />
      </View>

      <View style={styles.timerBanner}>
        <MayushIcon name="clock" size={20} color={colors.surface.white} />
        <MayushText variant="strongBody" color={colors.surface.white}>
          {language === 'ar' ? 'ينتهي العرض خلال:' : 'Se termine dans :'}
        </MayushText>
        <View style={styles.timeBox}>
          <MayushText variant="strongBody" color={colors.brand.orange500}>
            {String(timeLeft.hours).padStart(2, '0')}h : {String(timeLeft.minutes).padStart(2, '0')}m : {String(timeLeft.seconds).padStart(2, '0')}s
          </MayushText>
        </View>
      </View>

      {loading ? (
        <View style={styles.centered}>
          <ActivityIndicator size="large" color={colors.brand.orange500} />
        </View>
      ) : error ? (
        <View style={styles.centered}>
          <MayushText variant="body" color={colors.semantic.error}>
            {error}
          </MayushText>
        </View>
      ) : flashItems.length === 0 ? (
        <View style={styles.centered}>
          <MayushText variant="body" color={colors.neutral.gray500}>
            {language === 'ar' ? 'لا توجد عروض سريعة حالياً' : 'Aucune vente flash en cours'}
          </MayushText>
        </View>
      ) : (
        <ScrollView contentContainerStyle={styles.content} showsVerticalScrollIndicator={false}>
          {flashItems.map((item) => (
            <TouchableOpacity
              key={item.id}
              style={styles.dealCard}
              onPress={() => onSelectProduct(item)}
              activeOpacity={0.85}
            >
              <View style={styles.imgCol}>
                <Image
                  source={item.thumbnail_image ? { uri: item.thumbnail_image } : require('../../../assets/reference-art/home-new-luna.png')}
                  style={styles.itemImg}
                  resizeMode="cover"
                />
                {item.discountPercent > 0 && (
                  <View style={styles.badge}>
                    <MayushText variant="caption" color={colors.surface.white} style={styles.badgeText}>
                      -{item.discountPercent}%
                    </MayushText>
                  </View>
                )}
              </View>
              <View style={styles.metaCol}>
                <MayushText variant="strongBody" color={colors.brand.navy900} numberOfLines={2}>
                  {item.name}
                </MayushText>
                <View style={styles.priceRow}>
                  <MayushText variant="priceRegular" color={colors.brand.orange500}>
                    {item.formattedPrice}
                  </MayushText>
                  <MayushText variant="caption" color={colors.neutral.gray500} style={styles.originalPrice}>
                    {item.originalPriceMad} MAD
                  </MayushText>
                </View>
              </View>
            </TouchableOpacity>
          ))}
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
  timerBanner: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 8,
    paddingVertical: 12,
    paddingHorizontal: 16,
    backgroundColor: colors.brand.navy900,
  },
  timeBox: {
    backgroundColor: colors.surface.white,
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: radii.sm,
  },
  content: { padding: 16 },
  centered: { flex: 1, alignItems: 'center', justifyContent: 'center', padding: 32 },
  dealCard: {
    flexDirection: 'row',
    padding: 12,
    borderRadius: radii.xl,
    borderWidth: 1,
    borderColor: colors.surface.borderWarm,
    backgroundColor: colors.surface.white,
    marginBottom: 16,
  },
  imgCol: { position: 'relative', width: 100, height: 100, borderRadius: radii.lg, overflow: 'hidden' },
  itemImg: { width: '100%', height: '100%' },
  badge: {
    position: 'absolute',
    top: 6,
    left: 6,
    backgroundColor: colors.semantic.error,
    paddingHorizontal: 6,
    paddingVertical: 2,
    borderRadius: radii.sm,
  },
  badgeText: { fontSize: 11, fontWeight: '700' },
  metaCol: { flex: 1, marginLeft: 12, justifyContent: 'center' },
  priceRow: { flexDirection: 'row', alignItems: 'baseline', gap: 8, marginTop: 8 },
  originalPrice: { textDecorationLine: 'line-through' },
});
