import React, { useEffect, useState } from 'react';
import { ScrollView, StyleSheet, Switch, TouchableOpacity, View } from 'react-native';
import { accountPreferencesState } from '../../commerce/accountPreferencesState';
import { notificationPreferencesState } from '../../commerce/notificationPreferencesState';
import { BottomTabBar, TabKey } from '../../design-system/components/navigation/BottomTabBar';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { colors } from '../../design-system/tokens/colors';
import { spacing } from '../../design-system/tokens/spacing';

export interface SilentHoursDoNotDisturbScreenProps {
  onNavigateTab?: (tab: TabKey) => void;
  onBack?: () => void;
  onEditSchedule?: () => void;
}

export const SilentHoursDoNotDisturbScreen: React.FC<SilentHoursDoNotDisturbScreenProps> = ({
  onNavigateTab,
  onBack,
  onEditSchedule,
}) => {
  const isRTL = accountPreferencesState.getSelectedLanguage() === 'ar';
  const [enabled, setEnabled] = useState(notificationPreferencesState.getQuietHoursEnabled());
  const [selectedDays, setSelectedDays] = useState<string[]>(
    notificationPreferencesState.getQuietHoursDays(),
  );
  const [timeRange, setTimeRange] = useState(
    notificationPreferencesState.getQuietHoursTimeRange(),
  );

  useEffect(() => {
    const unsubscribe = notificationPreferencesState.subscribe(() => {
      setEnabled(notificationPreferencesState.getQuietHoursEnabled());
      setSelectedDays(notificationPreferencesState.getQuietHoursDays());
      setTimeRange(notificationPreferencesState.getQuietHoursTimeRange());
    });
    return unsubscribe;
  }, []);

  const toggleDND = () => {
    notificationPreferencesState.toggleQuietHours();
  };

  const daysSummaryText =
    selectedDays.length === 7
      ? isRTL
        ? 'كل الأيام'
        : 'Tous les jours'
      : selectedDays.length === 0
      ? isRTL
        ? 'لا يوجد يوم محدد'
        : 'Aucun jour sélectionné'
      : selectedDays.join(', ');

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
          {isRTL ? 'عدم الإزعاج' : 'Ne Pas Déranger'}
        </MayushText>
        <View style={{ width: 40 }} />
      </View>

      <ScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
        {/* DND Master Card */}
        <View style={[styles.card, !enabled && styles.disabledCard]}>
          <View style={[styles.toggleRow, isRTL && styles.rtlRow]}>
            <View style={styles.iconBox}>
              <MayushIcon
                name="clock"
                size={24}
                color={enabled ? colors.brand.orange500 : colors.neutral.gray500}
              />
            </View>
            <View style={styles.toggleTextCol}>
              <MayushText
                variant="cardTitle"
                color={colors.brand.navy900}
                style={isRTL && styles.rtlText}
              >
                {isRTL ? 'وضع عدم الإزعاج' : 'Mode Ne Pas Déranger'}
              </MayushText>
              <MayushText
                variant="smallBody"
                color={colors.neutral.gray500}
                style={isRTL && styles.rtlText}
              >
                {enabled
                  ? isRTL
                    ? 'نشط — كتم الصوت والتنبيهات المباشرة'
                    : 'Actif — Sons et alertes suspendus'
                  : isRTL
                  ? 'غير نشط'
                  : 'Inactif'}
              </MayushText>
            </View>
            <Switch
              value={enabled}
              onValueChange={toggleDND}
              trackColor={{ false: colors.neutral.gray300, true: colors.brand.orange500 }}
              thumbColor={colors.neutral.white}
            />
          </View>
        </View>

        {/* Active Schedule Summary */}
        <View style={styles.card}>
          <MayushText
            variant="sectionTitle"
            color={colors.brand.navy900}
            style={[styles.sectionTitle, isRTL && styles.rtlText]}
          >
            {isRTL ? 'الجدول الزمني المحدد' : 'Calendrier des Heures de Silence'}
          </MayushText>

          <View style={styles.divider} />

          <View style={[styles.infoRow, isRTL && styles.rtlRow]}>
            <MayushText variant="smallBody" color={colors.neutral.gray500}>
              {isRTL ? 'النطاق الزمني:' : 'Plage horaire :'}
            </MayushText>
            <MayushText variant="strongBody" color={colors.brand.navy900}>
              {`${timeRange.start} — ${timeRange.end}`}
            </MayushText>
          </View>

          <View style={[styles.infoRow, isRTL && styles.rtlRow]}>
            <MayushText variant="smallBody" color={colors.neutral.gray500}>
              {isRTL ? 'الأيام النشطة:' : 'Jours actifs :'}
            </MayushText>
            <MayushText variant="strongBody" color={colors.brand.orange500}>
              {daysSummaryText}
            </MayushText>
          </View>
        </View>

        {/* Edit Schedule Button */}
        <TouchableOpacity
          style={[styles.navigationCard, isRTL && styles.rtlRow]}
          onPress={onEditSchedule}
          activeOpacity={0.85}
        >
          <View style={styles.navIconBox}>
            <MayushIcon name="edit-2" size={20} color={colors.brand.orange500} />
          </View>
          <View style={styles.navTextCol}>
            <MayushText
              variant="strongBody"
              color={colors.brand.navy900}
              style={isRTL && styles.rtlText}
            >
              {isRTL ? 'تعديل الجدول الزمني' : 'Modifier le calendrier'}
            </MayushText>
            <MayushText
              variant="smallBody"
              color={colors.neutral.gray500}
              style={isRTL && styles.rtlText}
            >
              {isRTL ? 'تغيير الساعات أو أيام الأسبوع' : 'Changer les heures ou les jours sélectionnés'}
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
  card: {
    backgroundColor: colors.neutral.white,
    borderRadius: 16,
    padding: spacing.md,
    borderWidth: 1,
    borderColor: colors.neutral.gray300,
  },
  disabledCard: {
    opacity: 0.7,
  },
  toggleRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.sm,
  },
  iconBox: {
    width: 44,
    height: 44,
    borderRadius: 12,
    backgroundColor: 'rgba(217, 116, 52, 0.1)',
    alignItems: 'center',
    justifyContent: 'center',
  },
  toggleTextCol: {
    flex: 1,
  },
  sectionTitle: {
    fontSize: 16,
    fontWeight: '700',
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
    marginBottom: spacing.xs,
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
