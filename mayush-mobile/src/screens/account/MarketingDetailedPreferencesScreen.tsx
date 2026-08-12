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

export interface MarketingDetailedPreferencesScreenProps {
  onNavigateTab?: (tab: TabKey) => void;
  onBack?: () => void;
  onNavigateToggles?: () => void;
  onNavigateSilentHours?: () => void;
}

export const MarketingDetailedPreferencesScreen: React.FC<MarketingDetailedPreferencesScreenProps> = ({
  onNavigateTab,
  onBack,
  onNavigateToggles,
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

  const toggleRecs = () => {
    notificationPreferencesState.toggleMarketingPreference('personalizedRecommendations');
  };

  const toggleNews = () => {
    notificationPreferencesState.toggleMarketingPreference('productNewsUpdates');
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
          {isRTL ? 'تفضيلات تفصيلية' : 'Préférences Détaillées'}
        </MayushText>
        <View style={{ width: 40 }} />
      </View>

      <ScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
        {/* Intro Info */}
        <View style={styles.infoCard}>
          <MayushText
            variant="sectionTitle"
            color={colors.brand.navy900}
            style={[styles.infoTitle, isRTL && styles.rtlText]}
          >
            {isRTL ? 'محتوى مخصص لاحتياجاتك' : 'Contenu adapté à vos besoins'}
          </MayushText>
          <MayushText
            variant="body"
            color={colors.neutral.gray700}
            style={[styles.infoDesc, isRTL && styles.rtlText]}
          >
            {isRTL
              ? 'اختر المواضيع والتوصيات التي ترغب في استلامها لجعل تجربتك أكثر ملاءمة لمشاريع الديكور الخاصة بك.'
              : 'Choisissez les thématiques et recommandations que vous souhaitez recevoir pour rendre votre expérience idéale.'}
          </MayushText>
        </View>

        {/* Preferences Toggles */}
        <View style={styles.sectionCard}>
          <View style={[styles.toggleRow, isRTL && styles.rtlRow]}>
            <View style={styles.toggleTextCol}>
              <MayushText
                variant="strongBody"
                color={colors.brand.navy900}
                style={isRTL && styles.rtlText}
              >
                {isRTL ? 'توصيات شخصية' : 'Recommandations Personnalisées'}
              </MayushText>
              <MayushText
                variant="smallBody"
                color={colors.neutral.gray500}
                style={isRTL && styles.rtlText}
              >
                {isRTL
                  ? 'مقترحات للأثاث والديكور بناءً على تصفحك'
                  : 'Suggestions basées sur votre navigation et vos coups de cœur'}
              </MayushText>
            </View>
            <Switch
              value={prefs.personalizedRecommendations}
              onValueChange={toggleRecs}
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
                {isRTL ? 'النشرات والنصائح' : 'Nouveautés & Conseils Déco'}
              </MayushText>
              <MayushText
                variant="smallBody"
                color={colors.neutral.gray500}
                style={isRTL && styles.rtlText}
              >
                {isRTL
                  ? 'اتجاهات الموضة والأثاث والديكور المنزلي'
                  : 'Tendances design, guides d’aménagement et nouveautés'}
              </MayushText>
            </View>
            <Switch
              value={prefs.productNewsUpdates}
              onValueChange={toggleNews}
              trackColor={{ false: colors.neutral.gray300, true: colors.brand.orange500 }}
              thumbColor={colors.neutral.white}
            />
          </View>
        </View>

        {/* Link to Channel Toggles */}
        <TouchableOpacity
          style={[styles.navigationCard, isRTL && styles.rtlRow]}
          onPress={onNavigateToggles}
          activeOpacity={0.85}
        >
          <View style={styles.navIconBox}>
            <MayushIcon name="bell" size={22} color={colors.brand.orange500} />
          </View>
          <View style={styles.navTextCol}>
            <MayushText
              variant="strongBody"
              color={colors.brand.navy900}
              style={isRTL && styles.rtlText}
            >
              {isRTL ? 'قنوات الاتصال' : 'Canaux de communication'}
            </MayushText>
            <MayushText
              variant="smallBody"
              color={colors.neutral.gray500}
              style={isRTL && styles.rtlText}
            >
              {isRTL ? 'إدارة البريد الإلكتروني والرسائل القصيرة والإشعارات' : 'Gérer Email, SMS et notifications Push'}
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
  infoCard: {
    backgroundColor: colors.neutral.white,
    borderRadius: 16,
    padding: spacing.md,
    borderWidth: 1,
    borderColor: colors.neutral.gray300,
  },
  infoTitle: {
    fontSize: 16,
    fontWeight: '700',
    marginBottom: spacing.xs,
  },
  infoDesc: {
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
