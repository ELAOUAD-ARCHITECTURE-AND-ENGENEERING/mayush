/**
 * PromotionsCampaignsScreen (Figma Node 309:598 - 02-promotions-campaigns-offers)
 * Promotional campaigns, seasonal vouchers, and discount codes screen.
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

export interface PromoCode {
  code: string;
  discount: string;
  label: string;
}

const FALLBACK_PROMO_CODES = [
  { code: 'WELCOME10', discount: '10% de réduction', minOrder: '1 500 MAD', desc: 'Valable sur votre première commande' },
  { code: 'SALON2026', discount: '15% de réduction', minOrder: '4 000 MAD', desc: 'Valable sur la catégorie Salon' },
];

export interface PromotionsCampaignsScreenProps {
  promoCodes?: PromoCode[];
  onBack: () => void;
  onExploreDeals: () => void;
}

export const PromotionsCampaignsScreen: React.FC<PromotionsCampaignsScreenProps> = ({
  promoCodes: promoCodesProp,
  onBack,
  onExploreDeals,
}) => {
  const { isRTL, language } = useTheme();
  const [copiedCode, setCopiedCode] = useState<string | null>(null);

  const promoCodes = promoCodesProp && promoCodesProp.length > 0
    ? promoCodesProp.map((p) => ({ code: p.code, discount: p.discount, minOrder: '', desc: p.label }))
    : FALLBACK_PROMO_CODES;

  const handleCopy = (code: string) => {
    setCopiedCode(code);
    setTimeout(() => setCopiedCode(null), 2000);
  };

  return (
    <View style={styles.screen} accessibilityLabel="Promotions Campaigns Screen">
      <View style={styles.header}>
        <TouchableOpacity accessibilityRole="button" onPress={onBack} style={styles.iconBtn}>
          <MayushIcon name={isRTL ? 'arrow-right' : 'arrow-left'} size={24} color={colors.brand.navy900} />
        </TouchableOpacity>
        <MayushText variant="sectionTitle" color={colors.brand.navy900} style={styles.headerTitle}>
          {language === 'ar' ? 'العروض والتخفيضات' : 'Offres & Promotions'}
        </MayushText>
        <View style={styles.iconBtn} />
      </View>

      <ScrollView contentContainerStyle={styles.content} showsVerticalScrollIndicator={false}>
        <View style={styles.bannerCard}>
          <MayushText variant="caption" color={colors.brand.orange500} style={styles.bannerTag}>
            Offre Spéciale
          </MayushText>
          <MayushText variant="pageTitle" color={colors.brand.navy900} style={styles.bannerTitle}>
            Livraison offerte au Maroc
          </MayushText>
          <MayushText variant="smallBody" color={colors.neutral.gray700} style={styles.bannerDesc}>
            Pour toute commande supérieure à 3 000 MAD à Casablanca, Rabat et Marrakech.
          </MayushText>
          <TouchableOpacity style={styles.bannerBtn} onPress={onExploreDeals}>
            <MayushText variant="button" color={colors.surface.white}>
              Découvrir les offres
            </MayushText>
          </TouchableOpacity>
        </View>

        <MayushText variant="sectionTitle" color={colors.brand.navy900} style={styles.sectionHeading}>
          {language === 'ar' ? 'قسائم الخصم' : 'Codes promo disponibles'}
        </MayushText>

        {promoCodes.map((item) => (
          <View key={item.code} style={styles.voucherCard}>
            <View style={styles.voucherLeft}>
              <MayushText variant="strongBody" color={colors.brand.navy900}>
                {item.discount}
              </MayushText>
              <MayushText variant="caption" color={colors.neutral.gray700}>
                {item.desc}
              </MayushText>
              <MayushText variant="caption" color={colors.brand.orange500} style={styles.minOrder}>
                Min. d'achat: {item.minOrder}
              </MayushText>
            </View>
            <TouchableOpacity style={styles.codeBtn} onPress={() => handleCopy(item.code)}>
              <MayushText variant="caption" color={colors.brand.navy900} style={styles.codeText}>
                {copiedCode === item.code ? 'Copié !' : item.code}
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
  content: { padding: 16 },
  bannerCard: {
    padding: 20,
    borderRadius: radii.xl,
    backgroundColor: colors.brand.orange100,
    marginBottom: 24,
    borderWidth: 1,
    borderColor: colors.surface.borderWarm,
  },
  bannerTag: { textTransform: 'uppercase', fontWeight: '700', marginBottom: 4 },
  bannerTitle: { fontSize: 20, lineHeight: 26, marginBottom: 6 },
  bannerDesc: { lineHeight: 18, marginBottom: 16 },
  bannerBtn: {
    alignSelf: 'flex-start',
    height: 40,
    paddingHorizontal: 16,
    borderRadius: radii.lg,
    backgroundColor: colors.brand.orange500,
    alignItems: 'center',
    justifyContent: 'center',
  },
  sectionHeading: { marginBottom: 14 },
  voucherCard: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    padding: 16,
    borderRadius: radii.lg,
    backgroundColor: colors.surface.creamLight,
    borderWidth: 1,
    borderStyle: 'dashed',
    borderColor: colors.brand.orange500,
    marginBottom: 12,
  },
  voucherLeft: { flex: 1, marginRight: 12 },
  minOrder: { marginTop: 4, fontWeight: '600' },
  codeBtn: {
    paddingHorizontal: 12,
    paddingVertical: 8,
    borderRadius: radii.md,
    backgroundColor: colors.surface.white,
    borderWidth: 1,
    borderColor: colors.surface.borderWarm,
  },
  codeText: { fontWeight: '700', letterSpacing: 1 },
});
