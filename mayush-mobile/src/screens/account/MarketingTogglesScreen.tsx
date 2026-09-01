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

export interface MarketingTogglesScreenProps {
  onNavigateTab?: (tab: TabKey) => void;
  onBack?: () => void;
  onNavigateNotificationManagement?: () => void;
}

export const MarketingTogglesScreen: React.FC<MarketingTogglesScreenProps> = ({
  onNavigateTab,
  onBack,
  onNavigateNotificationManagement,
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

  const toggleEmail = () => {
    notificationPreferencesState.toggleMarketingPreference('emailMarketing');
  };

  const toggleSms = () => {
    notificationPreferencesState.toggleMarketingPreference('smsMarketing');
  };

  const togglePush = () => {
    notificationPreferencesState.toggleMarketingPreference('pushMarketing');
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
          {isRTL ? 'قنوات التسويق' : 'Canaux Marketing'}
        </MayushText>
        <View style={{ width: 40 }} />
      </View>

      <ScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
        {/* Intro */}
        <View style={styles.infoCard}>
          <MayushText
            variant="sectionTitle"
            color={colors.brand.navy900}
            style={[styles.infoTitle, isRTL && styles.rtlText]}
          >
            {isRTL ? 'تخصيص وسائط التواصل' : 'Modes de Réception'}
          </MayushText>
          <MayushText
            variant="body"
            color={colors.neutral.gray700}
            style={[styles.infoDesc, isRTL && styles.rtlText]}
          >
            {isRTL
              ? 'حدد الوسائط التي تفضلها لاستلام الإشعارات التسويقية والخصومات الحصرية.'
              : 'Activez ou désactivez les canaux par lesquels vous souhaitez être contacté.'}
          </MayushText>
        </View>

        {/* Channel Toggles Card */}
        <View style={styles.sectionCard}>
          {/* Email Marketing */}
          <View style={[styles.toggleRow, isRTL && styles.rtlRow]}>
            <View style={styles.iconCol}>
              <MayushIcon name="file-text" size={20} color={colors.brand.orange500} />
            </View>
            <View style={styles.toggleTextCol}>
              <MayushText
                variant="strongBody"
                color={colors.brand.navy900}
                style={isRTL && styles.rtlText}
              >
                {isRTL ? 'البريد الإلكتروني' : 'E-mail Marketing'}
              </MayushText>
              <MayushText
                variant="smallBody"
                color={colors.neutral.gray500}
                style={isRTL && styles.rtlText}
              >
                {isRTL ? 'كتالوجات، عروض حصرية ونشرات أسبوعية' : 'Newsletters, offres exclusives et catalogues'}
              </MayushText>
            </View>
            <Switch
              value={prefs.emailMarketing}
              onValueChange={toggleEmail}
              trackColor={{ false: colors.neutral.gray300, true: colors.brand.orange500 }}
              thumbColor={colors.neutral.white}
            />
          </View>

          <View style={styles.divider} />

          {/* SMS Marketing */}
          <View style={[styles.toggleRow, isRTL && styles.rtlRow]}>
            <View style={styles.iconCol}>
              <MayushIcon name="phone" size={20} color={colors.brand.orange500} />
            </View>
            <View style={styles.toggleTextCol}>
              <MayushText
                variant="strongBody"
                color={colors.brand.navy900}
                style={isRTL && styles.rtlText}
              >
                {isRTL ? 'رسائل SMS النصية' : 'SMS Marketing'}
              </MayushText>
              <MayushText
                variant="smallBody"
                color={colors.neutral.gray500}
                style={isRTL && styles.rtlText}
              >
                {isRTL ? 'تنبيهات سريعة للتصفيات والمواعيد الهامة' : 'SMS d’urgence pour ventes flash et codes promo'}
              </MayushText>
            </View>
            <Switch
              value={prefs.smsMarketing}
              onValueChange={toggleSms}
              trackColor={{ false: colors.neutral.gray300, true: colors.brand.orange500 }}
              thumbColor={colors.neutral.white}
            />
          </View>

          <View style={styles.divider} />

          {/* Push Marketing */}
          <View style={[styles.toggleRow, isRTL && styles.rtlRow]}>
            <View style={styles.iconCol}>
              <MayushIcon name="bell" size={20} color={colors.brand.orange500} />
            </View>
            <View style={styles.toggleTextCol}>
              <MayushText
                variant="strongBody"
                color={colors.brand.navy900}
                style={isRTL && styles.rtlText}
              >
                {isRTL ? 'إشعارات الهاتف (Push)' : 'Notifications Push'}
              </MayushText>
              <MayushText
                variant="smallBody"
                color={colors.neutral.gray500}
                style={isRTL && styles.rtlText}
              >
                {isRTL ? 'إشعارات على شاشة القفل عند وجود عروض جديدة' : 'Alertes directes sur votre téléphone'}
              </MayushText>
            </View>
            <Switch
              value={prefs.pushMarketing}
              onValueChange={togglePush}
              trackColor={{ false: colors.neutral.gray300, true: colors.brand.orange500 }}
              thumbColor={colors.neutral.white}
            />
          </View>
        </View>

        {/* Link to Notification Management (309:775) */}
        <TouchableOpacity
          style={[styles.navigationCard, isRTL && styles.rtlRow]}
          onPress={onNavigateNotificationManagement}
          activeOpacity={0.85}
        >
          <View style={styles.navIconBox}>
            <MayushIcon name="shield-check" size={22} color={colors.brand.orange500} />
          </View>
          <View style={styles.navTextCol}>
            <MayushText
              variant="strongBody"
              color={colors.brand.navy900}
              style={isRTL && styles.rtlText}
            >
              {isRTL ? 'إدارة الإشعارات العامة' : 'Gestion des Notifications'}
            </MayushText>
            <MayushText
              variant="smallBody"
              color={colors.neutral.gray500}
              style={isRTL && styles.rtlText}
            >
              {isRTL ? 'قنوات الإشعارات وفئات التنبيهات' : 'Gérer les canaux généraux et catégories de notifications'}
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
  iconCol: {
    width: 32,
    alignItems: 'center',
  },
  toggleTextCol: {
    flex: 1,
    paddingHorizontal: spacing.sm,
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
