import React, { useEffect, useState } from 'react';
import { ScrollView, StyleSheet, TouchableOpacity, View } from 'react-native';
import { accountPreferencesState } from '../../commerce/accountPreferencesState';
import {
  NotificationFixture,
  notificationPreferencesState,
} from '../../commerce/notificationPreferencesState';
import { BottomTabBar, TabKey } from '../../design-system/components/navigation/BottomTabBar';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { colors } from '../../design-system/tokens/colors';
import { spacing } from '../../design-system/tokens/spacing';

export interface NotificationDetailShippedScreenProps {
  onNavigateTab?: (tab: TabKey) => void;
  onBack?: () => void;
  onNavigateOrderDetails?: () => void;
  onNavigateSilentHours?: () => void;
}

export const NotificationDetailShippedScreen: React.FC<NotificationDetailShippedScreenProps> = ({
  onNavigateTab,
  onBack,
  onNavigateOrderDetails,
  onNavigateSilentHours,
}) => {
  const isRTL = accountPreferencesState.getSelectedLanguage() === 'ar';
  const [notif, setNotif] = useState<NotificationFixture | undefined>(
    notificationPreferencesState.getNotificationFixtures().find((n) => n.id === 'notif-shipped'),
  );

  useEffect(() => {
    const unsubscribe = notificationPreferencesState.subscribe(() => {
      setNotif(
        notificationPreferencesState.getNotificationFixtures().find((n) => n.id === 'notif-shipped'),
      );
    });
    return unsubscribe;
  }, []);

  const titleText = isRTL ? 'تم شحن الطلب' : notif?.title || 'Commande expédiée';
  const orderNumber = notif?.orderNumber || '#MY-84920';
  const dateText = notif?.date || '06 Août 2026 à 09:15';
  const statusBadgeText = isRTL ? 'تم الشحن' : notif?.statusText || 'Expédiée';
  const carrierText = notif?.carrier || 'CTM Messagerie Express';
  const trackingNumber = notif?.trackingNumber || 'CTM-948201-MA';
  const estimatedDelivery = notif?.estimatedDelivery || '08 Août 2026 (Entre 09h et 18h)';
  const descriptionText = isRTL
    ? 'غادر طلبك مستودعنا اللوجستي. سيتصل بك الموزع هاتفيا يوم التسليم.'
    : notif?.description ||
      'Votre commande a quitté notre entrepôt logistique. Le livreur vous contactera par téléphone le jour de la livraison.';

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
          {isRTL ? 'تفاصيل الشحن' : 'Détail d’expédition'}
        </MayushText>
        <View style={{ width: 40 }} />
      </View>

      <ScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
        {/* Status Header Card */}
        <View style={styles.card}>
          <View style={[styles.cardHeaderRow, isRTL && styles.rtlRow]}>
            <View style={styles.iconBoxSuccess}>
              <MayushIcon name="truck" size={24} color={colors.semantic.success} />
            </View>
            <View style={styles.headerTextCol}>
              <View style={[styles.badgeContainer, isRTL && styles.rtlRow]}>
                <View style={styles.successBadge}>
                  <MayushText variant="caption" color={colors.semantic.success}>
                    {statusBadgeText}
                  </MayushText>
                </View>
                <MayushText variant="caption" color={colors.neutral.gray500}>
                  {dateText}
                </MayushText>
              </View>
              <MayushText
                variant="cardTitle"
                color={colors.brand.navy900}
                style={isRTL && styles.rtlText}
              >
                {titleText}
              </MayushText>
            </View>
          </View>

          <View style={styles.divider} />

          {/* Tracking Details */}
          <View style={styles.detailRow}>
            <MayushText variant="smallBody" color={colors.neutral.gray500}>
              {isRTL ? 'رقم الطلب:' : 'N° de commande :'}
            </MayushText>
            <MayushText variant="strongBody" color={colors.brand.navy900}>
              {orderNumber}
            </MayushText>
          </View>

          <View style={styles.detailRow}>
            <MayushText variant="smallBody" color={colors.neutral.gray500}>
              {isRTL ? 'شركة الشحن:' : 'Transporteur :'}
            </MayushText>
            <MayushText variant="strongBody" color={colors.brand.navy900}>
              {carrierText}
            </MayushText>
          </View>

          <View style={styles.detailRow}>
            <MayushText variant="smallBody" color={colors.neutral.gray500}>
              {isRTL ? 'رقم التتبع:' : 'N° de suivi :'}
            </MayushText>
            <MayushText variant="strongBody" color={colors.brand.orange500}>
              {trackingNumber}
            </MayushText>
          </View>

          <View style={styles.detailRow}>
            <MayushText variant="smallBody" color={colors.neutral.gray500}>
              {isRTL ? 'التسليم المتوقع:' : 'Livraison estimée :'}
            </MayushText>
            <MayushText variant="strongBody" color={colors.semantic.success}>
              {estimatedDelivery}
            </MayushText>
          </View>

          <MayushText
            variant="body"
            color={colors.neutral.gray700}
            style={[styles.descriptionText, isRTL && styles.rtlText]}
          >
            {descriptionText}
          </MayushText>
        </View>

        {/* Primary CTA Button */}
        <TouchableOpacity
          style={styles.primaryButton}
          onPress={onNavigateOrderDetails}
          activeOpacity={0.85}
        >
          <MayushIcon name="truck" size={20} color={colors.surface.white} />
          <MayushText variant="button" color={colors.surface.white}>
            {isRTL ? 'تتبع الشحنة والطلب' : 'Suivre mon colis'}
          </MayushText>
        </TouchableOpacity>

        {/* Link to Silent Hours (309:779) */}
        {onNavigateSilentHours && (
          <TouchableOpacity
            style={[styles.secondaryButton, isRTL && styles.rtlRow]}
            onPress={onNavigateSilentHours}
            activeOpacity={0.85}
          >
            <MayushIcon name="clock" size={18} color={colors.brand.navy900} />
            <MayushText variant="button" color={colors.brand.navy900}>
              {isRTL ? 'إعداد ساعات الهدوء' : 'Configurer les Heures de Silence'}
            </MayushText>
          </TouchableOpacity>
        )}
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
  card: {
    backgroundColor: colors.neutral.white,
    borderRadius: 16,
    padding: spacing.md,
    borderWidth: 1,
    borderColor: colors.neutral.gray300,
  },
  cardHeaderRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.sm,
  },
  iconBoxSuccess: {
    width: 48,
    height: 48,
    borderRadius: 14,
    backgroundColor: colors.semantic.successBackground,
    alignItems: 'center',
    justifyContent: 'center',
  },
  headerTextCol: {
    flex: 1,
  },
  badgeContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.xs,
    marginBottom: spacing.xxs,
  },
  successBadge: {
    backgroundColor: colors.semantic.successBackground,
    paddingHorizontal: 8,
    paddingVertical: 2,
    borderRadius: 6,
  },
  divider: {
    height: 1,
    backgroundColor: colors.neutral.gray300,
    marginVertical: spacing.md,
  },
  detailRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: spacing.xs,
  },
  descriptionText: {
    lineHeight: 22,
    marginTop: spacing.sm,
  },
  primaryButton: {
    height: 50,
    backgroundColor: colors.brand.navy900,
    borderRadius: 14,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: spacing.xs,
  },
  secondaryButton: {
    height: 48,
    backgroundColor: colors.neutral.white,
    borderWidth: 1,
    borderColor: colors.neutral.gray300,
    borderRadius: 14,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: spacing.xs,
  },
  rtlRow: {
    flexDirection: 'row-reverse',
  },
  rtlText: {
    textAlign: 'right',
  },
});
