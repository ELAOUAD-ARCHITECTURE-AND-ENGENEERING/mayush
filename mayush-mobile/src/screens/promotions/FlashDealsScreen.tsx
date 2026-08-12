/**
 * FlashDealsScreen (Figma Node 309:597 - 02-flash-deals-countdown-timer)
 * Time-limited promotional flash sales screen with live countdown timer.
 */

import React, { useEffect, useState } from 'react';
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
  const [timeLeft, setTimeLeft] = useState({ hours: 4, minutes: 15, seconds: 30 });

  useEffect(() => {
    const timer = setInterval(() => {
      setTimeLeft((prev) => {
        if (prev.seconds > 0) return { ...prev, seconds: prev.seconds - 1 };
        if (prev.minutes > 0) return { ...prev, minutes: 59, seconds: 59 };
        if (prev.hours > 0) return { hours: prev.hours - 1, minutes: 59, seconds: 59 };
        return prev;
      });
    }, 1000);
    return () => clearInterval(timer);
  }, []);

  const flashItems: (ProductMiniDto & { discountPercent: number; originalPriceMad: number })[] = [
    { id: 501, name: 'Canapé Luna 3 Places · Tissu Bouclé', priceMad: 3950, originalPriceMad: 4500, formattedPrice: '3 950 MAD', discountPercent: 12, thumbnail_image: '', has_discount: true, discount: '-12%', stroked_price: '4 500 MAD', main_price: '3 950 MAD', rating: 5, sales: 15, links: { details: '' } },
    { id: 502, name: 'Fauteuil Nori Accent · Vert Sauge', priceMad: 1440, originalPriceMad: 1800, formattedPrice: '1 440 MAD', discountPercent: 20, thumbnail_image: '', has_discount: true, discount: '-20%', stroked_price: '1 800 MAD', main_price: '1 440 MAD', rating: 5, sales: 20, links: { details: '' } },
  ];

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

      <ScrollView contentContainerStyle={styles.content} showsVerticalScrollIndicator={false}>
        {flashItems.map((item) => (
          <TouchableOpacity
            key={item.id}
            style={styles.dealCard}
            onPress={() => onSelectProduct(item)}
            activeOpacity={0.85}
          >
            <View style={styles.imgCol}>
              <Image source={item.id === 501 ? CANAPE_IMG : FAUTEUIL_IMG} style={styles.itemImg} resizeMode="cover" />
              <View style={styles.badge}>
                <MayushText variant="caption" color={colors.surface.white} style={styles.badgeText}>
                  -{item.discountPercent}%
                </MayushText>
              </View>
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
