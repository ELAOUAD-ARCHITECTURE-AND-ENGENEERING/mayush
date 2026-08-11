import React, { useEffect, useState } from 'react';
import { ScrollView, StyleSheet, Switch, TouchableOpacity, View } from 'react-native';
import { accountPreferencesState } from '../../commerce/accountPreferencesState';
import { notificationPreferencesState } from '../../commerce/notificationPreferencesState';
import { BottomTabBar, TabKey } from '../../design-system/components/navigation/BottomTabBar';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { colors } from '../../design-system/tokens/colors';
import { spacing } from '../../design-system/tokens/spacing';

export interface SilentHoursDaySelectionScreenProps {
  onNavigateTab?: (tab: TabKey) => void;
  onBack?: () => void;
  onSaveSchedule?: () => void;
}

const ALL_WEEKDAYS = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];

export const SilentHoursDaySelectionScreen: React.FC<SilentHoursDaySelectionScreenProps> = ({
  onNavigateTab,
  onBack,
  onSaveSchedule,
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

  const toggleDay = (day: string) => {
    notificationPreferencesState.toggleQuietHoursDay(day);
  };

  const handleSave = () => {
    notificationPreferencesState.setQuietHoursEnabled(enabled);
    if (onSaveSchedule) {
      onSaveSchedule();
    } else if (onBack) {
      onBack();
    }
  };

  const dayLabels: Record<string, { fr: string; ar: string }> = {
    Lun: { fr: 'Lundi', ar: 'الإثنين' },
    Mar: { fr: 'Mardi', ar: 'الثلاثاء' },
    Mer: { fr: 'Mercredi', ar: 'الأربعاء' },
    Jeu: { fr: 'Jeudi', ar: 'الخميس' },
    Ven: { fr: 'Vendredi', ar: 'الجمعة' },
    Sam: { fr: 'Samedi', ar: 'السبت' },
    Dim: { fr: 'Dimanche', ar: 'الأحد' },
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
          {isRTL ? 'برمجة ساعات الهدوء' : 'Sélection des jours'}
        </MayushText>
        <View style={{ width: 40 }} />
      </View>

      <ScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
        {/* Intro */}
        <View style={styles.card}>
          <MayushText
            variant="sectionTitle"
            color={colors.brand.navy900}
            style={[styles.title, isRTL && styles.rtlText]}
          >
            {isRTL ? 'ساعات الصمت وعدم الإزعاج' : 'Heures de Silence'}
          </MayushText>
          <MayushText
            variant="body"
            color={colors.neutral.gray700}
            style={[styles.desc, isRTL && styles.rtlText]}
          >
            {isRTL
              ? 'كتم الإشعارات الصوتية والمباشرة خلال الفترة المحددة للحفاظ على هدوئك.'
              : 'Désactivez les sons et notifications Push pendant vos heures de repos.'}
          </MayushText>
        </View>

        {/* Enable Toggle */}
        <View style={styles.card}>
          <View style={[styles.toggleRow, isRTL && styles.rtlRow]}>
            <View style={styles.toggleTextCol}>
              <MayushText
                variant="strongBody"
                color={colors.brand.navy900}
                style={isRTL && styles.rtlText}
              >
                {isRTL ? 'تفعيل ساعات الهدوء' : 'Activer les Heures de Silence'}
              </MayushText>
              <MayushText
                variant="smallBody"
                color={colors.neutral.gray500}
                style={isRTL && styles.rtlText}
              >
                {isRTL ? 'جدول تلقائي يومي' : 'Programme automatique'}
              </MayushText>
            </View>
            <Switch
              value={enabled}
              onValueChange={(val) => {
                setEnabled(val);
                notificationPreferencesState.setQuietHoursEnabled(val);
              }}
              trackColor={{ false: colors.neutral.gray300, true: colors.brand.orange500 }}
              thumbColor={colors.neutral.white}
            />
          </View>
        </View>

        {/* Time Range Display */}
        <View style={styles.card}>
          <MayushText
            variant="sectionTitle"
            color={colors.brand.navy900}
            style={[styles.sectionTitle, isRTL && styles.rtlText]}
          >
            {isRTL ? 'نطاق الساعات' : 'Plage horaire'}
          </MayushText>

          <View style={[styles.timeRangeRow, isRTL && styles.rtlRow]}>
            <View style={styles.timeBox}>
              <MayushText variant="smallBody" color={colors.neutral.gray500}>
                {isRTL ? 'من' : 'Début'}
              </MayushText>
              <MayushText variant="cardTitle" color={colors.brand.navy900}>
                {timeRange.start}
              </MayushText>
            </View>
            <MayushIcon name="arrow-right" size={20} color={colors.neutral.gray500} />
            <View style={styles.timeBox}>
              <MayushText variant="smallBody" color={colors.neutral.gray500}>
                {isRTL ? 'إلى' : 'Fin'}
              </MayushText>
              <MayushText variant="cardTitle" color={colors.brand.navy900}>
                {timeRange.end}
              </MayushText>
            </View>
          </View>
        </View>

        {/* Weekday Chips Card */}
        <View style={styles.card}>
          <MayushText
            variant="sectionTitle"
            color={colors.brand.navy900}
            style={[styles.sectionTitle, isRTL && styles.rtlText]}
          >
            {isRTL ? 'أيام الأسبوع النشطة' : 'Jours de la semaine'}
          </MayushText>

          <View style={styles.daysList}>
            {ALL_WEEKDAYS.map((day) => {
              const isSelected = selectedDays.includes(day);
              const label = isRTL ? dayLabels[day]?.ar : dayLabels[day]?.fr;
              return (
                <TouchableOpacity
                  key={day}
                  style={[
                    styles.dayRow,
                    isSelected ? styles.dayRowActive : styles.dayRowInactive,
                    isRTL && styles.rtlRow,
                  ]}
                  onPress={() => toggleDay(day)}
                  activeOpacity={0.7}
                >
                  <MayushText
                    variant="strongBody"
                    color={isSelected ? colors.brand.orange500 : colors.brand.navy900}
                  >
                    {label}
                  </MayushText>
                  <View style={styles.checkCircle}>
                    {isSelected && (
                      <MayushIcon name="check" size={16} color={colors.brand.orange500} />
                    )}
                  </View>
                </TouchableOpacity>
              );
            })}
          </View>
        </View>

        {/* Save Button */}
        <TouchableOpacity style={styles.primaryButton} onPress={handleSave} activeOpacity={0.85}>
          <MayushText variant="button" color={colors.surface.white}>
            {isRTL ? 'حفظ الجدول' : 'Enregistrer le calendrier'}
          </MayushText>
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
  title: {
    fontSize: 16,
    fontWeight: '700',
    marginBottom: spacing.xs,
  },
  desc: {
    lineHeight: 20,
  },
  toggleRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
  },
  toggleTextCol: {
    flex: 1,
    paddingRight: spacing.sm,
  },
  sectionTitle: {
    fontSize: 16,
    fontWeight: '700',
    marginBottom: spacing.sm,
  },
  timeRangeRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-around',
    backgroundColor: colors.surface.creamLight,
    padding: spacing.sm,
    borderRadius: 12,
  },
  timeBox: {
    alignItems: 'center',
  },
  daysList: {
    gap: spacing.xs,
  },
  dayRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingVertical: spacing.sm,
    paddingHorizontal: spacing.md,
    borderRadius: 12,
    borderWidth: 1,
  },
  dayRowActive: {
    borderColor: colors.brand.orange500,
    backgroundColor: 'rgba(217, 116, 52, 0.08)',
  },
  dayRowInactive: {
    borderColor: colors.neutral.gray300,
    backgroundColor: colors.neutral.white,
  },
  checkCircle: {
    width: 24,
    height: 24,
    borderRadius: 12,
    alignItems: 'center',
    justifyContent: 'center',
  },
  primaryButton: {
    height: 50,
    backgroundColor: colors.brand.navy900,
    borderRadius: 14,
    alignItems: 'center',
    justifyContent: 'center',
  },
  rtlRow: {
    flexDirection: 'row-reverse',
  },
  rtlText: {
    textAlign: 'right',
  },
});
