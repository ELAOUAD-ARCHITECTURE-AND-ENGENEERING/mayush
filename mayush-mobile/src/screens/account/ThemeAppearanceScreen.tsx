import React, { useState } from 'react';
import { ScrollView, StyleSheet, TouchableOpacity, View } from 'react-native';
import { MayushIcon, MayushIconName } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { useTheme } from '../../design-system/theme/useTheme';
import { colors } from '../../design-system/tokens/colors';
import { radii } from '../../design-system/tokens/radii';
import { spacing } from '../../design-system/tokens/spacing';

export interface ThemeAppearanceScreenProps {
  onBack?: () => void;
}

type ThemeMode = 'light' | 'dark' | 'system';

interface ThemeOption {
  id: ThemeMode;
  icon: MayushIconName;
  titleFr: string;
  titleAr: string;
  subtitleFr: string;
  subtitleAr: string;
}

const THEME_OPTIONS: ThemeOption[] = [
  {
    id: 'light',
    icon: 'sun',
    titleFr: 'Mode Clair',
    titleAr: 'الوضع الفاتح',
    subtitleFr: 'Palette chaleureuse et lumineuse Mayush Design (Recommandé)',
    subtitleAr: 'لوحة ألوان مايوش الدافئة والمشرقة (موصى به)',
  },
  {
    id: 'dark',
    icon: 'moon',
    titleFr: 'Mode Sombre',
    titleAr: 'الوضع الداكن',
    subtitleFr: 'Contraste élégant pour un confort visuel de nuit',
    subtitleAr: 'تباين أنيق لراحة بصرية أثناء الليل',
  },
  {
    id: 'system',
    icon: 'smartphone',
    titleFr: 'Automatique (Système)',
    titleAr: 'تلقائي (حسب النظام)',
    subtitleFr: "S'adapte automatiquement aux réglages de votre appareil",
    subtitleAr: 'يتكيف تلقائياً مع إعدادات جهازك',
  },
];

export const ThemeAppearanceScreen: React.FC<ThemeAppearanceScreenProps> = ({ onBack }) => {
  const { isRTL, language } = useTheme();
  const [selectedTheme, setSelectedTheme] = useState<ThemeMode>('light');

  return (
    <View style={styles.screen} accessibilityLabel="Thème et Apparence">
      <ScrollView showsVerticalScrollIndicator={false} contentContainerStyle={styles.scrollContent}>
        {/* Header */}
        <View style={[styles.header, isRTL && styles.rowReverse]}>
          <TouchableOpacity
            accessibilityRole="button"
            accessibilityLabel="Retour"
            onPress={onBack}
            style={styles.backButton}
          >
            <MayushIcon name={isRTL ? 'chevron-right' : 'chevron-left'} size={24} color={colors.brand.navy900} />
          </TouchableOpacity>
          <MayushText variant="sectionTitle" color={colors.brand.navy900}>
            {language === 'ar' ? 'المظهر والثيم' : 'Thème & Apparence'}
          </MayushText>
          <View style={styles.headerSpacer} />
        </View>

        {/* Info Banner */}
        <View style={[styles.infoCard, isRTL && styles.rowReverse]}>
          <MayushIcon name="eye" size={20} color={colors.brand.orange500} />
          <MayushText variant="caption" color={colors.neutral.gray700} style={[styles.infoText, isRTL && styles.rtlText]}>
            {language === 'ar'
              ? 'خصص المظهر البصري لتطبيق مايوش بما يناسب تفضيلاتك وراحتك البصرية.'
              : "Personnalisez l'apparence visuelle de votre application Mayush pour un confort optimal."}
          </MayushText>
        </View>

        {/* Options List */}
        <View style={styles.optionsContainer}>
          {THEME_OPTIONS.map((option) => {
            const isSelected = selectedTheme === option.id;
            return (
              <TouchableOpacity
                key={option.id}
                style={[
                  styles.optionCard,
                  isSelected && styles.optionCardSelected,
                  isRTL && styles.rowReverse,
                ]}
                onPress={() => setSelectedTheme(option.id)}
                activeOpacity={0.7}
              >
                <View style={[styles.iconContainer, isSelected && styles.iconContainerSelected]}>
                  <MayushIcon
                    name={option.icon}
                    size={22}
                    color={isSelected ? colors.brand.orange500 : colors.neutral.gray700}
                  />
                </View>
                <View style={[styles.optionContent, isRTL && styles.alignRight]}>
                  <MayushText
                    variant="strongBody"
                    color={isSelected ? colors.brand.navy900 : colors.neutral.gray900}
                    style={isRTL && styles.rtlText}
                  >
                    {language === 'ar' ? option.titleAr : option.titleFr}
                  </MayushText>
                  <MayushText
                    variant="caption"
                    color={colors.neutral.gray700}
                    style={[styles.subtitleText, isRTL && styles.rtlText]}
                  >
                    {language === 'ar' ? option.subtitleAr : option.subtitleFr}
                  </MayushText>
                </View>
                <View style={[styles.radioCircle, isSelected && styles.radioCircleSelected]}>
                  {isSelected ? <View style={styles.radioInner} /> : null}
                </View>
              </TouchableOpacity>
            );
          })}
        </View>
      </ScrollView>
    </View>
  );
};

const styles = StyleSheet.create({
  screen: {
    flex: 1,
    backgroundColor: colors.surface.creamLight,
  },
  scrollContent: {
    paddingBottom: 40,
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: spacing.lg,
    paddingTop: 54,
    paddingBottom: spacing.md,
    backgroundColor: colors.surface.creamLight,
  },
  rowReverse: {
    flexDirection: 'row-reverse',
  },
  backButton: {
    width: 40,
    height: 40,
    borderRadius: radii.full,
    backgroundColor: '#FFFFFF',
    justifyContent: 'center',
    alignItems: 'center',
    borderWidth: 1,
    borderColor: '#E7DED3',
  },
  headerSpacer: {
    width: 40,
  },
  infoCard: {
    flexDirection: 'row',
    alignItems: 'center',
    marginHorizontal: spacing.lg,
    marginTop: spacing.sm,
    marginBottom: spacing.lg,
    padding: spacing.md,
    backgroundColor: '#FFF8F0',
    borderRadius: radii.md,
    borderWidth: 1,
    borderColor: '#FEE2D5',
  },
  infoText: {
    flex: 1,
    marginHorizontal: spacing.sm,
    lineHeight: 18,
  },
  rtlText: {
    textAlign: 'right',
  },
  alignRight: {
    alignItems: 'flex-end',
  },
  optionsContainer: {
    paddingHorizontal: spacing.lg,
    gap: spacing.md,
  },
  optionCard: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#FFFFFF',
    borderRadius: radii.lg,
    padding: spacing.lg,
    borderWidth: 1.5,
    borderColor: '#E7DED3',
  },
  optionCardSelected: {
    borderColor: colors.brand.orange500,
    backgroundColor: '#FFFAF6',
  },
  iconContainer: {
    width: 44,
    height: 44,
    borderRadius: radii.md,
    backgroundColor: colors.surface.creamLight,
    justifyContent: 'center',
    alignItems: 'center',
  },
  iconContainerSelected: {
    backgroundColor: '#FFF1E8',
  },
  optionContent: {
    flex: 1,
    marginHorizontal: spacing.md,
  },
  subtitleText: {
    marginTop: 2,
    lineHeight: 16,
  },
  radioCircle: {
    width: 22,
    height: 22,
    borderRadius: radii.full,
    borderWidth: 2,
    borderColor: colors.neutral.gray500,
    justifyContent: 'center',
    alignItems: 'center',
  },
  radioCircleSelected: {
    borderColor: colors.brand.orange500,
  },
  radioInner: {
    width: 10,
    height: 10,
    borderRadius: radii.full,
    backgroundColor: colors.brand.orange500,
  },
});
