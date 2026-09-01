import React, { useState } from 'react';
import { ScrollView, StyleSheet, TouchableOpacity, View } from 'react-native';
import { accountPreferencesState, AppLanguage } from '../../commerce/accountPreferencesState';
import { PrimaryButton } from '../../design-system/components/actions/PrimaryButton';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { useTheme } from '../../design-system/theme/useTheme';
import { colors } from '../../design-system/tokens/colors';
import { spacing } from '../../design-system/tokens/spacing';

export interface LanguageSelectionAccountScreenProps {
  onBack: () => void;
  onLanguageApplied: (selected: AppLanguage) => void;
}

interface LanguageOptionItem {
  code: AppLanguage;
  name: string;
  nativeName: string;
  flagSymbol: string;
}

const LANGUAGES: LanguageOptionItem[] = [
  { code: 'fr', name: 'Français', nativeName: 'Français (France)', flagSymbol: '🇫🇷' },
  { code: 'ar', name: 'العربية', nativeName: 'العربية (المغرب)', flagSymbol: '🇲🇦' },
  { code: 'en', name: 'English', nativeName: 'English (US)', flagSymbol: '🇺🇸' },
];

export const LanguageSelectionAccountScreen: React.FC<LanguageSelectionAccountScreenProps> = ({
  onBack,
  onLanguageApplied,
}) => {
  const { setLanguage: setGlobalLanguage } = useTheme();
  const currentPref = accountPreferencesState.getSelectedLanguage();
  const [selected, setSelected] = useState<AppLanguage>(currentPref);

  const isRTL = selected === 'ar';

  const copy = selected === 'ar'
    ? {
        headerTitle: 'اختر اللغة',
        title: 'حدد لغة التطبيق',
        subtitle: 'اختر إحدى اللغات الثلاث المتاحة لتجربة مخصصة.',
        saveButton: 'تطبيق التغييرات',
        notice: 'سيتم تحديث اتجاه الشاشة والنصوص فور تطبيق الاختيار.',
      }
    : selected === 'en'
    ? {
        headerTitle: 'Select Language',
        title: 'Choose App Language',
        subtitle: 'Select one of the three supported languages for a custom experience.',
        saveButton: 'Apply Changes',
        notice: 'Screen orientation and labels will update immediately upon applying.',
      }
    : {
        headerTitle: 'Choix de la langue',
        title: 'Sélectionnez votre langue',
        subtitle: 'Choisissez parmi les 3 langues proposées pour une expérience sur mesure.',
        saveButton: 'Appliquer la langue',
        notice: 'L\'orientation et les libellés de l\'application s\'adapteront immédiatement.',
      };

  const handleApply = () => {
    accountPreferencesState.setSelectedLanguage(selected);
    // Switch theme language for 'fr' / 'ar' (for 'en', fallback to 'fr' theme format with LTR)
    if (selected === 'ar') {
      setGlobalLanguage('ar');
    } else {
      setGlobalLanguage('fr');
    }
    onLanguageApplied(selected);
  };

  return (
    <View style={styles.screen}>
      {/* Header */}
      <View style={[styles.header, isRTL && styles.rowReverse]}>
        <TouchableOpacity accessibilityRole="button" onPress={onBack} style={styles.backButton}>
          <MayushIcon name={isRTL ? 'chevron-right' : 'chevron-left'} size={24} color={colors.brand.navy900} />
        </TouchableOpacity>
        <MayushText variant="pageTitle" color={colors.brand.navy900} style={styles.headerTitle}>
          {copy.headerTitle}
        </MayushText>
        <View style={styles.headerSpacer} />
      </View>

      <ScrollView contentContainerStyle={styles.content} showsVerticalScrollIndicator={false}>
        <View style={[styles.titleRow, isRTL && styles.rowReverse]}>
          <View style={styles.flex1}>
            <MayushText variant="pageTitle" color={colors.brand.navy900} style={[styles.title, isRTL && styles.rtlText]}>
              {copy.title}
            </MayushText>
            <MayushText variant="caption" color={colors.neutral.gray700} style={[styles.subtitle, isRTL && styles.rtlText]}>
              {copy.subtitle}
            </MayushText>
          </View>
          <View style={styles.globeCircle}>
            <MayushIcon name="globe" size={24} color={colors.brand.navy900} />
          </View>
        </View>

        {/* 3 Languages Options (Figma Node 309:770) */}
        <View style={styles.languageList}>
          {LANGUAGES.map((lang) => {
            const isSelected = selected === lang.code;
            return (
              <TouchableOpacity
                key={lang.code}
                accessibilityRole="radio"
                accessibilityState={{ selected: isSelected }}
                onPress={() => setSelected(lang.code)}
                style={[styles.langCard, isSelected && styles.langCardSelected]}
              >
                <View style={[styles.langRow, isRTL && styles.rowReverse]}>
                  <View style={styles.flagBox}>
                    <MayushText variant="sectionTitle" color={colors.brand.navy900} style={styles.flagSymbol}>
                      {lang.flagSymbol}
                    </MayushText>
                  </View>
                  <View style={styles.flex1}>
                    <MayushText variant="sectionTitle" color={colors.brand.navy900} style={isRTL && styles.rtlText}>
                      {lang.nativeName}
                    </MayushText>
                    <MayushText variant="caption" color={colors.neutral.gray500} style={isRTL && styles.rtlText}>
                      {lang.name}
                    </MayushText>
                  </View>
                  <View style={[styles.radio, isSelected && styles.radioSelected]}>
                    {isSelected && <MayushIcon name="check" size={14} color={colors.surface.white} strokeWidth={3} />}
                  </View>
                </View>
              </TouchableOpacity>
            );
          })}
        </View>

        {/* Notice */}
        <View style={[styles.noticeBox, isRTL && styles.rowReverse]}>
          <MayushIcon name="shield" size={16} color={colors.brand.orange500} />
          <MayushText variant="caption" color={colors.neutral.gray700} style={styles.noticeText}>
            {copy.notice}
          </MayushText>
        </View>

        {/* Apply CTA */}
        <PrimaryButton
          label={copy.saveButton}
          onPress={handleApply}
          style={styles.applyBtn}
        />
      </ScrollView>
    </View>
  );
};

const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: colors.surface.white },
  header: {
    height: 56, paddingHorizontal: spacing.md, flexDirection: 'row', alignItems: 'center',
    justifyContent: 'space-between', borderBottomWidth: 1, borderBottomColor: '#E7DED3',
  },
  backButton: { padding: 4 },
  headerTitle: { fontSize: 18, fontWeight: '700' },
  headerSpacer: { width: 32 },
  rowReverse: { flexDirection: 'row-reverse' },
  rtlText: { writingDirection: 'rtl' },
  flex1: { flex: 1 },
  content: { padding: spacing.md, paddingBottom: 60, gap: 16 },
  titleRow: { flexDirection: 'row', gap: 12, alignItems: 'flex-start', marginTop: 4 },
  title: { fontSize: 22, lineHeight: 28 },
  subtitle: { fontSize: 12, marginTop: 2 },
  globeCircle: {
    width: 44, height: 44, borderRadius: 12, alignItems: 'center',
    justifyContent: 'center', backgroundColor: '#FFF7EF',
  },
  languageList: { gap: 12, marginTop: 6 },
  langCard: {
    borderWidth: 1.5, borderColor: '#F0E3D7', borderRadius: 14, padding: 14,
    backgroundColor: colors.surface.white,
  },
  langCardSelected: { borderColor: colors.brand.orange500, backgroundColor: '#FFFCF7' },
  langRow: { flexDirection: 'row', alignItems: 'center', gap: 12 },
  flagBox: {
    width: 42, height: 42, borderRadius: 12, backgroundColor: '#FFF6E8',
    alignItems: 'center', justifyContent: 'center',
  },
  flagSymbol: { fontSize: 22 },
  radio: {
    width: 22, height: 22, borderRadius: 11, borderWidth: 1.5,
    borderColor: colors.neutral.gray300, alignItems: 'center', justifyContent: 'center',
  },
  radioSelected: { borderColor: colors.brand.orange500, backgroundColor: colors.brand.orange500 },
  noticeBox: {
    flexDirection: 'row', alignItems: 'center', gap: 8, padding: 12,
    borderRadius: 10, backgroundColor: '#FFFCF7', borderWidth: 1, borderColor: '#F0E3D7', marginTop: 4,
  },
  noticeText: { flex: 1, fontSize: 11, lineHeight: 15 },
  applyBtn: { marginTop: 10 },
});
