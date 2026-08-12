import React, { useEffect, useState } from 'react';
import { ScrollView, StyleSheet, Switch, TouchableOpacity, View } from 'react-native';
import { accountPreferencesState } from '../../commerce/accountPreferencesState';
import {
  NotificationCategorySettings,
  notificationPreferencesState,
} from '../../commerce/notificationPreferencesState';
import { BottomTabBar, TabKey } from '../../design-system/components/navigation/BottomTabBar';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { colors } from '../../design-system/tokens/colors';
import { spacing } from '../../design-system/tokens/spacing';

export interface NotificationSettingsTogglesScreenProps {
  onNavigateTab?: (tab: TabKey) => void;
  onBack?: () => void;
  onNavigateNotificationDetails?: () => void;
}

export const NotificationSettingsTogglesScreen: React.FC<NotificationSettingsTogglesScreenProps> = ({
  onNavigateTab,
  onBack,
  onNavigateNotificationDetails,
}) => {
  const isRTL = accountPreferencesState.getSelectedLanguage() === 'ar';
  const [settings, setSettings] = useState<NotificationCategorySettings>(
    notificationPreferencesState.getNotificationSettings(),
  );

  useEffect(() => {
    const unsubscribe = notificationPreferencesState.subscribe(() => {
      setSettings(notificationPreferencesState.getNotificationSettings());
    });
    return unsubscribe;
  }, []);

  const toggleOrders = () => {
    notificationPreferencesState.toggleNotificationSetting('orders');
  };

  const toggleDelivery = () => {
    notificationPreferencesState.toggleNotificationSetting('delivery');
  };

  const togglePromotions = () => {
    notificationPreferencesState.toggleNotificationSetting('promotions');
  };

  const toggleWishlist = () => {
    notificationPreferencesState.toggleNotificationSetting('wishlist');
  };

  const toggleSecurity = () => {
    notificationPreferencesState.toggleNotificationSetting('accountSecurity');
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
          {isRTL ? 'إعدادات الإشعارات' : 'Paramètres des Notifications'}
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
            {isRTL ? 'تنبيهات الفئات' : 'Notifications par Catégorie'}
          </MayushText>
          <MayushText
            variant="body"
            color={colors.neutral.gray700}
            style={[styles.infoDesc, isRTL && styles.rtlText]}
          >
            {isRTL
              ? 'خصص أنواع التنبيهات التي ترغب في استلامها لكل فئة من الخدمات.'
              : 'Personnalisez le type d’alertes que vous souhaitez recevoir pour chaque service.'}
          </MayushText>
        </View>

        {/* Category Toggles Card */}
        <View style={styles.sectionCard}>
          {/* Orders */}
          <View style={[styles.toggleRow, isRTL && styles.rtlRow]}>
            <View style={styles.iconCol}>
              <MayushIcon name="shopping-bag" size={20} color={colors.brand.navy900} />
            </View>
            <View style={styles.toggleTextCol}>
              <MayushText
                variant="strongBody"
                color={colors.brand.navy900}
                style={isRTL && styles.rtlText}
              >
                {isRTL ? 'الطلبات والمشتريات' : 'Commandes & Suivi'}
              </MayushText>
              <MayushText
                variant="smallBody"
                color={colors.neutral.gray500}
                style={isRTL && styles.rtlText}
              >
                {isRTL ? 'تأكيد الشراء، المعالجة والفواتير' : 'Validation, préparation et confirmation d’achat'}
              </MayushText>
            </View>
            <Switch
              value={settings.orders}
              onValueChange={toggleOrders}
              trackColor={{ false: colors.neutral.gray300, true: colors.brand.orange500 }}
              thumbColor={colors.neutral.white}
            />
          </View>

          <View style={styles.divider} />

          {/* Delivery */}
          <View style={[styles.toggleRow, isRTL && styles.rtlRow]}>
            <View style={styles.iconCol}>
              <MayushIcon name="truck" size={20} color={colors.brand.navy900} />
            </View>
            <View style={styles.toggleTextCol}>
              <MayushText
                variant="strongBody"
                color={colors.brand.navy900}
                style={isRTL && styles.rtlText}
              >
                {isRTL ? 'الحديث والتسليم' : 'Livraison & Expédition'}
              </MayushText>
              <MayushText
                variant="smallBody"
                color={colors.neutral.gray500}
                style={isRTL && styles.rtlText}
              >
                {isRTL ? 'خروج الطلب، موعد الموزع والاستلام' : 'Départ de l’entrepôt, horaire du livreur'}
              </MayushText>
            </View>
            <Switch
              value={settings.delivery}
              onValueChange={toggleDelivery}
              trackColor={{ false: colors.neutral.gray300, true: colors.brand.orange500 }}
              thumbColor={colors.neutral.white}
            />
          </View>

          <View style={styles.divider} />

          {/* Promotions */}
          <View style={[styles.toggleRow, isRTL && styles.rtlRow]}>
            <View style={styles.iconCol}>
              <MayushIcon name="tag" size={20} color={colors.brand.navy900} />
            </View>
            <View style={styles.toggleTextCol}>
              <MayushText
                variant="strongBody"
                color={colors.brand.navy900}
                style={isRTL && styles.rtlText}
              >
                {isRTL ? 'العروض الحصرية' : 'Promotions & Ventes Flash'}
              </MayushText>
              <MayushText
                variant="smallBody"
                color={colors.neutral.gray500}
                style={isRTL && styles.rtlText}
              >
                {isRTL ? 'تخفيضات موسمية وكود الخصم' : 'Codes promos, réductions VIP et soldes'}
              </MayushText>
            </View>
            <Switch
              value={settings.promotions}
              onValueChange={togglePromotions}
              trackColor={{ false: colors.neutral.gray300, true: colors.brand.orange500 }}
              thumbColor={colors.neutral.white}
            />
          </View>

          <View style={styles.divider} />

          {/* Wishlist */}
          <View style={[styles.toggleRow, isRTL && styles.rtlRow]}>
            <View style={styles.iconCol}>
              <MayushIcon name="heart" size={20} color={colors.brand.navy900} />
            </View>
            <View style={styles.toggleTextCol}>
              <MayushText
                variant="strongBody"
                color={colors.brand.navy900}
                style={isRTL && styles.rtlText}
              >
                {isRTL ? 'تنبيهات المفضلة' : 'Alertes Prix & Wishlist'}
              </MayushText>
              <MayushText
                variant="smallBody"
                color={colors.neutral.gray500}
                style={isRTL && styles.rtlText}
              >
                {isRTL ? 'انخفاض أسعار وتوفر مخزون العناصر المفضلة' : 'Baisses de prix et retour en stock de vos favoris'}
              </MayushText>
            </View>
            <Switch
              value={settings.wishlist}
              onValueChange={toggleWishlist}
              trackColor={{ false: colors.neutral.gray300, true: colors.brand.orange500 }}
              thumbColor={colors.neutral.white}
            />
          </View>

          <View style={styles.divider} />

          {/* Account & Security */}
          <View style={[styles.toggleRow, isRTL && styles.rtlRow]}>
            <View style={styles.iconCol}>
              <MayushIcon name="shield-check" size={20} color={colors.brand.navy900} />
            </View>
            <View style={styles.toggleTextCol}>
              <MayushText
                variant="strongBody"
                color={colors.brand.navy900}
                style={isRTL && styles.rtlText}
              >
                {isRTL ? 'الحساب والأمان' : 'Compte & Sécurité'}
              </MayushText>
              <MayushText
                variant="smallBody"
                color={colors.neutral.gray500}
                style={isRTL && styles.rtlText}
              >
                {isRTL ? 'تنبيهات تسجيل الدخول، كلمة السر و2FA' : 'Connexions suspectes, 2FA et modification mot de passe'}
              </MayushText>
            </View>
            <Switch
              value={settings.accountSecurity}
              onValueChange={toggleSecurity}
              trackColor={{ false: colors.neutral.gray300, true: colors.brand.orange500 }}
              thumbColor={colors.neutral.white}
            />
          </View>
        </View>

        {/* Link toward Notification Details (Step 5D.2 target) */}
        <TouchableOpacity
          style={[styles.navigationCard, isRTL && styles.rtlRow]}
          onPress={onNavigateNotificationDetails}
          activeOpacity={0.85}
        >
          <View style={styles.navIconBox}>
            <MayushIcon name="clock" size={22} color={colors.brand.orange500} />
          </View>
          <View style={styles.navTextCol}>
            <MayushText
              variant="strongBody"
              color={colors.brand.navy900}
              style={isRTL && styles.rtlText}
            >
              {isRTL ? 'تفاصيل وساعات الهدوء' : 'Détails & Heures de silence'}
            </MayushText>
            <MayushText
              variant="smallBody"
              color={colors.neutral.gray500}
              style={isRTL && styles.rtlText}
            >
              {isRTL ? 'تخصيص تفاصيل الإشعار وجدولة عدم الإزعاج' : 'Consulter les détails des notifications et mode Ne Pas Déranger'}
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
