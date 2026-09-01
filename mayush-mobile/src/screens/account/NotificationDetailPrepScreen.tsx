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

export interface NotificationDetailPrepScreenProps {
  onNavigateTab?: (tab: TabKey) => void;
  onBack?: () => void;
  onNavigateOrderDetails?: () => void;
  onNavigateShippedNotif?: () => void;
}

export const NotificationDetailPrepScreen: React.FC<NotificationDetailPrepScreenProps> = ({
  onNavigateTab,
  onBack,
  onNavigateOrderDetails,
  onNavigateShippedNotif,
}) => {
  const isRTL = accountPreferencesState.getSelectedLanguage() === 'ar';
  const [notif, setNotif] = useState<NotificationFixture | undefined>(
    notificationPreferencesState.getNotificationFixtures().find((n) => n.id === 'notif-prep'),
  );

  useEffect(() => {
    const unsubscribe = notificationPreferencesState.subscribe(() => {
      setNotif(
        notificationPreferencesState.getNotificationFixtures().find((n) => n.id === 'notif-prep'),
      );
    });
    return unsubscribe;
  }, []);

  const titleText = isRTL ? 'الطلب قيد التحضير' : notif?.title || 'Commande en cours de préparation';
  const orderNumber = notif?.orderNumber || '#MY-84920';
  const dateText = notif?.date || '05 Août 2026 à 14:30';
  const statusBadgeText = isRTL ? 'قيد التحضير' : notif?.statusText || 'En préparation';
  const descriptionText = isRTL
    ? 'يجري تحضير طلبك من أريكة لونا وطاولة القهوة بكل عناية في ورشنا بالدار البيضاء.'
    : notif?.description ||
      'Votre commande de Canapé Luna Velvet 3 Places et Table Basse Marble est actuellement préparée avec soin dans nos ateliers de Casablanca.';
  const itemsText = notif?.itemsSummary || '1× Canapé Luna Velvet 3 Places, 1× Table Basse Marble';

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
          {isRTL ? 'تفاصيل الإشعار' : 'Détail de la notification'}
        </MayushText>
        <View style={{ width: 40 }} />
      </View>

      <ScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
        {/* Status Header Card */}
        <View style={styles.card}>
          <View style={[styles.cardHeaderRow, isRTL && styles.rtlRow]}>
            <View style={styles.iconBoxWarning}>
              <MayushIcon name="clock" size={24} color={colors.semantic.warning} />
            </View>
            <View style={styles.headerTextCol}>
              <View style={[styles.badgeContainer, isRTL && styles.rtlRow]}>
                <View style={styles.warningBadge}>
                  <MayushText variant="caption" color={colors.semantic.warning}>
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

          <View style={[styles.infoRow, isRTL && styles.rtlRow]}>
            <MayushText variant="smallBody" color={colors.neutral.gray500}>
              {isRTL ? 'رقم الطلب:' : 'N° de commande :'}
            </MayushText>
            <MayushText variant="strongBody" color={colors.brand.navy900}>
              {orderNumber}
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

        {/* Order Items Card */}
        <View style={styles.card}>
          <MayushText
            variant="sectionTitle"
            color={colors.brand.navy900}
            style={[styles.sectionHeading, isRTL && styles.rtlText]}
          >
            {isRTL ? 'العناصر المشمولة' : 'Articles inclus'}
          </MayushText>
          <View style={[styles.itemsBox, isRTL && styles.rtlRow]}>
            <MayushIcon name="shopping-bag" size={20} color={colors.brand.orange500} />
            <MayushText
              variant="body"
              color={colors.brand.navy900}
              style={[styles.itemsText, isRTL && styles.rtlText]}
            >
              {itemsText}
            </MayushText>
          </View>
        </View>

        {/* Order Details CTA Button */}
        <TouchableOpacity
          style={styles.primaryButton}
          onPress={onNavigateOrderDetails}
          activeOpacity={0.85}
        >
          <MayushIcon name="file-text" size={20} color={colors.surface.white} />
          <MayushText variant="button" color={colors.surface.white}>
            {isRTL ? 'عرض تفاصيل الطلب' : 'Voir ma commande'}
          </MayushText>
        </TouchableOpacity>

        {/* Next Notification Link (309:778) */}
        {onNavigateShippedNotif && (
          <TouchableOpacity
            style={[styles.secondaryButton, isRTL && styles.rtlRow]}
            onPress={onNavigateShippedNotif}
            activeOpacity={0.85}
          >
            <MayushText variant="button" color={colors.brand.navy900}>
              {isRTL ? 'إشعار الشحن التالي' : 'Notification Expédiée (Suivante)'}
            </MayushText>
            <MayushIcon
              name={isRTL ? 'chevron-left' : 'chevron-right'}
              size={18}
              color={colors.brand.navy900}
            />
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
  iconBoxWarning: {
    width: 48,
    height: 48,
    borderRadius: 14,
    backgroundColor: colors.semantic.warningBackground,
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
  warningBadge: {
    backgroundColor: colors.semantic.warningBackground,
    paddingHorizontal: 8,
    paddingVertical: 2,
    borderRadius: 6,
  },
  divider: {
    height: 1,
    backgroundColor: colors.neutral.gray300,
    marginVertical: spacing.md,
  },
  infoRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: spacing.sm,
  },
  descriptionText: {
    lineHeight: 22,
  },
  sectionHeading: {
    fontSize: 16,
    fontWeight: '700',
    marginBottom: spacing.xs,
  },
  itemsBox: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.sm,
    backgroundColor: colors.surface.creamLight,
    padding: spacing.sm,
    borderRadius: 12,
  },
  itemsText: {
    flex: 1,
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
