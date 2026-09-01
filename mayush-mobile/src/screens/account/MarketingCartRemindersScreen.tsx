import React, { useEffect, useState } from 'react';
import { ScrollView, StyleSheet, Switch, TouchableOpacity, View } from 'react-native';
import { accountPreferencesState } from '../../commerce/accountPreferencesState';
import {
  MarketingPreferences,
  notificationPreferencesState,
} from '../../commerce/notificationPreferencesState';
import { BottomTabBar, TabKey } from '../../design-system/components/navigation/BottomTabBar';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { colors } from '../../design-system/tokens/colors';
import { spacing } from '../../design-system/tokens/spacing';

export interface MarketingCartRemindersScreenProps {
  onNavigateTab?: (tab: TabKey) => void;
  onBack?: () => void;
  onNavigateDetailedPreferences?: () => void;
}

export const MarketingCartRemindersScreen: React.FC<MarketingCartRemindersScreenProps> = ({
  onNavigateTab,
  onBack,
  onNavigateDetailedPreferences,
}) => {
  const isRTL = accountPreferencesState.getSelectedLanguage() === 'ar';
  const [prefs, setPrefs] = useState<MarketingPreferences>(
    notificationPreferencesState.getMarketingPreferences(),
  );

  useEffect(() => {
    const unsubscribe = notificationPreferencesState.subscribe(() => {
      setPrefs(notificationPreferencesState.getMarketingPreferences());
    });
    return unsubscribe;
  }, []);

  const toggleCartReminders = () => {
    notificationPreferencesState.toggleMarketingPreference('abandonedCartReminders');
  };

  const togglePromotions = () => {
    notificationPreferencesState.toggleMarketingPreference('promotionsAndOffers');
  };

  return (
    <View style={styles.container}>
      {/* Header */}
      <View style={[styles.header, isRTL && styles.rtlRow]}>
        <TouchableOpacity style={styles.backButton} onPress={onBack} activeOpacity={0.7}>
          <MayushIcon
            name={isRTL ? 'chevron-right' : 'chevron-left'}
            size={24}
            color={colors.brand.navy900}
          />
        </TouchableOpacity>
        <MayushText variant="pageTitle" color={colors.brand.navy900} style={styles.headerTitle}>
          {isRTL ? 'تفضيلات التسويق' : 'Préférences Marketing'}
        </MayushText>
        <View style={{ width: 40 }} />
      </View>

      <ScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
        {/* Banner Card */}
        <View style={styles.bannerCard}>
          <View style={[styles.bannerHeader, isRTL && styles.rtlRow]}>
            <MayushIcon name="shopping-cart" size={28} color={colors.brand.orange500} />
            <MayushText
              variant="sectionTitle"
              color={colors.brand.navy900}
              style={[styles.bannerTitle, isRTL && styles.rtlText]}
            >
              {isRTL ? 'تذكير السلة غير المكتملة' : 'Rappels de Panier Abandonné'}
            </MayushText>
          </View>
          <MayushText
            variant="body"
            color={colors.neutral.gray700}
            style={[styles.bannerDesc, isRTL && styles.rtlText]}
          >
            {isRTL
              ? 'احصل على تذكيرات مفيدة عند ترك عناصر في سلتك للحفاظ على اختيارك وعروضك.'
              : 'Recevez des rappels utiles lorsque vous laissez des articles dans votre panier pour conserver votre sélection et profiter des offres en cours.'}
          </MayushText>
        </View>

        {/* Toggle Items */}
        <View style={styles.sectionCard}>
          <View style={[styles.toggleRow, isRTL && styles.rtlRow]}>
            <View style={styles.toggleTextCol}>
              <MayushText
                variant="strongBody"
                color={colors.brand.navy900}
                style={isRTL && styles.rtlText}
              >
                {isRTL ? 'تذكيرات السلة' : 'Rappels de panier'}
              </MayushText>
              <MayushText
                variant="smallBody"
                color={colors.neutral.gray500}
                style={isRTL && styles.rtlText}
              >
                {isRTL
                  ? 'إشعارات لتذكيرك بالمأكولات والأثاث في سلتك'
                  : 'Notifications pour vos articles de mobilier sauvegardés'}
              </MayushText>
            </View>
            <Switch
              value={prefs.abandonedCartReminders}
              onValueChange={toggleCartReminders}
              trackColor={{ false: colors.neutral.gray300, true: colors.brand.orange500 }}
              thumbColor={colors.neutral.white}
            />
          </View>

          <View style={styles.divider} />

          <View style={[styles.toggleRow, isRTL && styles.rtlRow]}>
            <View style={styles.toggleTextCol}>
              <MayushText
                variant="strongBody"
                color={colors.brand.navy900}
                style={isRTL && styles.rtlText}
              >
                {isRTL ? 'العروض الخاصة والتخفيضات' : 'Offres & Ventes Privées'}
              </MayushText>
              <MayushText
                variant="smallBody"
                color={colors.neutral.gray500}
                style={isRTL && styles.rtlText}
              >
                {isRTL
                  ? 'تنبيهات فورية عند وجود خصومات حصريّة'
                  : 'Alertes immédiates pour les promotions exclusives'}
              </MayushText>
            </View>
            <Switch
              value={prefs.promotionsAndOffers}
              onValueChange={togglePromotions}
              trackColor={{ false: colors.neutral.gray300, true: colors.brand.orange500 }}
              thumbColor={colors.neutral.white}
            />
          </View>
        </View>

        {/* Link to Detailed Preferences */}
        <TouchableOpacity
          style={[styles.navigationCard, isRTL && styles.rtlRow]}
          onPress={onNavigateDetailedPreferences}
          activeOpacity={0.85}
        >
          <View style={styles.navIconBox}>
            <MayushIcon name="sliders" size={22} color={colors.brand.orange500} />
          </View>
          <View style={styles.navTextCol}>
            <MayushText
              variant="strongBody"
              color={colors.brand.navy900}
              style={isRTL && styles.rtlText}
            >
              {isRTL ? 'التفضيلات التفصيلية' : 'Préférences détaillées'}
            </MayushText>
            <MayushText
              variant="smallBody"
              color={colors.neutral.gray500}
              style={isRTL && styles.rtlText}
            >
              {isRTL
                ? 'تخصيص التوصيات وتحديثات المنتجات'
                : 'Personnaliser les recommandations et nouveautés'}
            </MayushText>
          </View>
          <MayushIcon
            name={isRTL ? 'chevron-left' : 'chevron-right'}
            size={20}
            color={colors.neutral.gray500}
          />
        </TouchableOpacity>
      </ScrollView>

      <BottomTabBar activeTab="account" onTabPress={(tab) => onNavigateTab?.(tab)} />
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: colors.neutral.gray100,
  },
  header: {
    height: 56,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: spacing.md,
    backgroundColor: colors.neutral.white,
    borderBottomWidth: 1,
    borderBottomColor: colors.neutral.gray300,
  },
  headerTitle: {
    fontSize: 18,
    fontWeight: '700',
  },
  backButton: {
    width: 40,
    height: 40,
    alignItems: 'center',
    justifyContent: 'center',
  },
  scrollContent: {
    padding: spacing.md,
    gap: spacing.md,
    paddingBottom: 100,
  },
  bannerCard: {
    backgroundColor: colors.neutral.white,
    borderRadius: 16,
    padding: spacing.md,
    borderWidth: 1,
    borderColor: colors.neutral.gray300,
  },
  bannerHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.sm,
    marginBottom: spacing.xs,
  },
  bannerTitle: {
    fontSize: 16,
    fontWeight: '700',
  },
  bannerDesc: {
    lineHeight: 20,
  },
  sectionCard: {
    backgroundColor: colors.neutral.white,
    borderRadius: 16,
    padding: spacing.md,
    borderWidth: 1,
    borderColor: colors.neutral.gray300,
  },
  toggleRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingVertical: spacing.xs,
  },
  toggleTextCol: {
    flex: 1,
    paddingRight: spacing.sm,
  },
  divider: {
    height: 1,
    backgroundColor: colors.neutral.gray300,
    marginVertical: spacing.sm,
  },
  navigationCard: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: colors.neutral.white,
    borderRadius: 16,
    padding: spacing.md,
    borderWidth: 1,
    borderColor: colors.neutral.gray300,
    gap: spacing.md,
  },
  navIconBox: {
    width: 44,
    height: 44,
    borderRadius: 12,
    backgroundColor: 'rgba(217, 116, 52, 0.1)',
    alignItems: 'center',
    justifyContent: 'center',
  },
  navTextCol: {
    flex: 1,
  },
  rtlRow: {
    flexDirection: 'row-reverse',
  },
  rtlText: {
    textAlign: 'right',
  },
});
