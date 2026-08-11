import React, { useEffect, useState } from 'react';
import { ScrollView, StyleSheet, Switch, TouchableOpacity, View } from 'react-native';
import { accountPreferencesState } from '../../commerce/accountPreferencesState';
import {
  NotificationChannels,
  notificationPreferencesState,
} from '../../commerce/notificationPreferencesState';
import { BottomTabBar, TabKey } from '../../design-system/components/navigation/BottomTabBar';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { colors } from '../../design-system/tokens/colors';
import { spacing } from '../../design-system/tokens/spacing';

export interface NotificationChannelsScreenProps {
  onNavigateTab?: (tab: TabKey) => void;
  onBack?: () => void;
  onNavigateNotificationSettings?: () => void;
}

export const NotificationChannelsScreen: React.FC<NotificationChannelsScreenProps> = ({
  onNavigateTab,
  onBack,
  onNavigateNotificationSettings,
}) => {
  const isRTL = accountPreferencesState.getSelectedLanguage() === 'ar';
  const [channels, setChannels] = useState<NotificationChannels>(
    notificationPreferencesState.getNotificationChannels(),
  );

  useEffect(() => {
    const unsubscribe = notificationPreferencesState.subscribe(() => {
      setChannels(notificationPreferencesState.getNotificationChannels());
    });
    return unsubscribe;
  }, []);

  const toggleEmail = () => {
    notificationPreferencesState.toggleNotificationChannel('emailChannel');
  };

  const toggleSms = () => {
    notificationPreferencesState.toggleNotificationChannel('smsChannel');
  };

  const togglePush = () => {
    notificationPreferencesState.toggleNotificationChannel('pushChannel');
  };

  const toggleInApp = () => {
    notificationPreferencesState.toggleNotificationChannel('inAppChannel');
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
          {isRTL ? 'إدارة قنوات الإشعارات' : 'Gestion des Notifications'}
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
            {isRTL ? 'قنوات الإشعارات العامة' : 'Canaux Principaux'}
          </MayushText>
          <MayushText
            variant="body"
            color={colors.neutral.gray700}
            style={[styles.infoDesc, isRTL && styles.rtlText]}
          >
            {isRTL
              ? 'تحكم في القنوات التي تستلم من خلالها إشعارات الطلبات والخدمات والتنبيهات.'
              : 'Gérez vos canaux de réception pour les notifications de commandes, livraisons et compte.'}
          </MayushText>
        </View>

        {/* Channels Card */}
        <View style={styles.sectionCard}>
          {/* Email Channel */}
          <View style={[styles.toggleRow, isRTL && styles.rtlRow]}>
            <View style={styles.iconCol}>
              <MayushIcon name="file-text" size={20} color={colors.brand.navy900} />
            </View>
            <View style={styles.toggleTextCol}>
              <MayushText
                variant="strongBody"
                color={colors.brand.navy900}
                style={isRTL && styles.rtlText}
              >
                {isRTL ? 'البريد الإلكتروني' : 'Canal E-mail'}
              </MayushText>
              <MayushText
                variant="smallBody"
                color={colors.neutral.gray500}
                style={isRTL && styles.rtlText}
              >
                {isRTL ? 'تأكيد الطلبات، الفواتير وتحديثات الحساب' : 'Confirmations de commande, factures et sécurité'}
              </MayushText>
            </View>
            <Switch
              value={channels.emailChannel}
              onValueChange={toggleEmail}
              trackColor={{ false: colors.neutral.gray300, true: colors.brand.orange500 }}
              thumbColor={colors.neutral.white}
            />
          </View>

          <View style={styles.divider} />

          {/* SMS Channel */}
          <View style={[styles.toggleRow, isRTL && styles.rtlRow]}>
            <View style={styles.iconCol}>
              <MayushIcon name="phone" size={20} color={colors.brand.navy900} />
            </View>
            <View style={styles.toggleTextCol}>
              <MayushText
                variant="strongBody"
                color={colors.brand.navy900}
                style={isRTL && styles.rtlText}
              >
                {isRTL ? 'الرسائل النصية SMS' : 'Canal SMS'}
              </MayushText>
              <MayushText
                variant="smallBody"
                color={colors.neutral.gray500}
                style={isRTL && styles.rtlText}
              >
                {isRTL ? 'تتبع التسليم الفوري ورمز التحقق' : 'Suivi de livraison en temps réel et codes OTP'}
              </MayushText>
            </View>
            <Switch
              value={channels.smsChannel}
              onValueChange={toggleSms}
              trackColor={{ false: colors.neutral.gray300, true: colors.brand.orange500 }}
              thumbColor={colors.neutral.white}
            />
          </View>

          <View style={styles.divider} />

          {/* Push Channel */}
          <View style={[styles.toggleRow, isRTL && styles.rtlRow]}>
            <View style={styles.iconCol}>
              <MayushIcon name="bell" size={20} color={colors.brand.navy900} />
            </View>
            <View style={styles.toggleTextCol}>
              <MayushText
                variant="strongBody"
                color={colors.brand.navy900}
                style={isRTL && styles.rtlText}
              >
                {isRTL ? 'إشعارات الهاتف (Push Mobile)' : 'Push Mobile'}
              </MayushText>
              <MayushText
                variant="smallBody"
                color={colors.neutral.gray500}
                style={isRTL && styles.rtlText}
              >
                {isRTL ? 'تنبيهات مباشرة على جهازك' : 'Alertes instantanées de l’application'}
              </MayushText>
            </View>
            <Switch
              value={channels.pushChannel}
              onValueChange={togglePush}
              trackColor={{ false: colors.neutral.gray300, true: colors.brand.orange500 }}
              thumbColor={colors.neutral.white}
            />
          </View>

          <View style={styles.divider} />

          {/* In-App Channel */}
          <View style={[styles.toggleRow, isRTL && styles.rtlRow]}>
            <View style={styles.iconCol}>
              <MayushIcon name="info" size={20} color={colors.brand.navy900} />
            </View>
            <View style={styles.toggleTextCol}>
              <MayushText
                variant="strongBody"
                color={colors.brand.navy900}
                style={isRTL && styles.rtlText}
              >
                {isRTL ? 'إشعارات داخل التطبيق' : 'Notifications In-App'}
              </MayushText>
              <MayushText
                variant="smallBody"
                color={colors.neutral.gray500}
                style={isRTL && styles.rtlText}
              >
                {isRTL ? 'رسائل وتنبيهات مركز الإشعارات في التطبيق' : 'Centre de messages et alertes internes'}
              </MayushText>
            </View>
            <Switch
              value={channels.inAppChannel}
              onValueChange={toggleInApp}
              trackColor={{ false: colors.neutral.gray300, true: colors.brand.orange500 }}
              thumbColor={colors.neutral.white}
            />
          </View>
        </View>

        {/* Link to Notification Settings Toggles (309:776) */}
        <TouchableOpacity
          style={[styles.navigationCard, isRTL && styles.rtlRow]}
          onPress={onNavigateNotificationSettings}
          activeOpacity={0.85}
        >
          <View style={styles.navIconBox}>
            <MayushIcon name="sliders-horizontal" size={22} color={colors.brand.orange500} />
          </View>
          <View style={styles.navTextCol}>
            <MayushText
              variant="strongBody"
              color={colors.brand.navy900}
              style={isRTL && styles.rtlText}
            >
              {isRTL ? 'إعدادات الفئات' : 'Paramètres par Catégorie'}
            </MayushText>
            <MayushText
              variant="smallBody"
              color={colors.neutral.gray500}
              style={isRTL && styles.rtlText}
            >
              {isRTL ? 'تخصيص تنبيهات الطلبات والتعزيزات والأمان' : 'Configurer les alertes de commandes, promotions et compte'}
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
