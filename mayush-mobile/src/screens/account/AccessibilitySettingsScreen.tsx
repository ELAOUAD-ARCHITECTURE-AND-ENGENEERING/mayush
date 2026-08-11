import React, { useEffect, useState } from 'react';
import { ScrollView, StyleSheet, Switch, TouchableOpacity, View } from 'react-native';
import { accountPreferencesState } from '../../commerce/accountPreferencesState';
import { appSettingsState, TextSizeOption } from '../../commerce/appSettingsState';
import { BottomTabBar, TabKey } from '../../design-system/components/navigation/BottomTabBar';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { colors } from '../../design-system/tokens/colors';
import { spacing } from '../../design-system/tokens/spacing';

export interface AccessibilitySettingsScreenProps {
  onNavigateTab?: (tab: TabKey) => void;
  onBack?: () => void;
  onNavigateAppPermissions?: () => void;
}

export const AccessibilitySettingsScreen: React.FC<AccessibilitySettingsScreenProps> = ({
  onNavigateTab,
  onBack,
  onNavigateAppPermissions,
}) => {
  const isRTL = accountPreferencesState.getSelectedLanguage() === 'ar';
  const [accessibility, setAccessibility] = useState(appSettingsState.getAccessibility());

  useEffect(() => {
    return appSettingsState.subscribe(() => {
      setAccessibility(appSettingsState.getAccessibility());
    });
  }, []);

  const textSizes: { id: TextSizeOption; labelFr: string; labelAr: string }[] = [
    { id: 'normal', labelFr: 'Normale', labelAr: 'عادي' },
    { id: 'large', labelFr: 'Grande', labelAr: 'كبير' },
    { id: 'xlarge', labelFr: 'Très grande', labelAr: 'كبير جداً' },
  ];

  return (
    <View style={[styles.container, accessibility.highContrast && styles.highContrastBg]}>
      {/* Header */}
      <View style={[styles.header, isRTL && styles.rtlRow, accessibility.highContrast && styles.highContrastHeader]}>
        <TouchableOpacity style={styles.backButton} onPress={onBack} activeOpacity={0.7}>
          <MayushIcon name={isRTL ? 'chevron-right' : 'chevron-left'} size={24} color={accessibility.highContrast ? '#FFFFFF' : colors.brand.navy900} />
        </TouchableOpacity>
        <MayushText variant="pageTitle" color={accessibility.highContrast ? '#FFFFFF' : colors.brand.navy900} style={styles.headerTitle}>
          {isRTL ? 'إمكانية الوصول والتباين' : 'Accessibilité & Contraste'}
        </MayushText>
        <View style={{ width: 40 }} />
      </View>

      <ScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
        {/* Text Size Selector */}
        <View style={[styles.card, accessibility.highContrast && styles.highContrastCard]}>
          <MayushText variant="sectionTitle" color={accessibility.highContrast ? '#FFFFFF' : colors.brand.navy900} style={[styles.cardLabel, isRTL && styles.rtlText]}>
            {isRTL ? 'حجم الخط والتكبير' : 'Taille de texte'}
          </MayushText>
          <MayushText variant="smallBody" color={accessibility.highContrast ? '#D0D0D0' : colors.neutral.gray500} style={[styles.cardSub, isRTL && styles.rtlText]}>
            {isRTL ? 'اختر حجم الخط المناسب لقراءة أسهل' : 'Ajustez la taille d\'affichage du texte'}
          </MayushText>

          <View style={[styles.textSizeRow, isRTL && styles.rtlRow]}>
            {textSizes.map((opt) => (
              <TouchableOpacity
                key={opt.id}
                style={[
                  styles.sizeChip,
                  accessibility.textSize === opt.id && styles.sizeChipActive,
                  accessibility.highContrast && accessibility.textSize === opt.id && styles.sizeChipHighContrast,
                ]}
                onPress={() => appSettingsState.setTextSize(opt.id)}
                activeOpacity={0.7}
              >
                <MayushText
                  variant="smallBody"
                  color={accessibility.textSize === opt.id ? colors.surface.white : (accessibility.highContrast ? '#FFFFFF' : colors.brand.navy900)}
                >
                  {isRTL ? opt.labelAr : opt.labelFr}
                </MayushText>
              </TouchableOpacity>
            ))}
          </View>
        </View>

        {/* Display Toggles */}
        <View style={[styles.card, accessibility.highContrast && styles.highContrastCard]}>
          <MayushText variant="sectionTitle" color={accessibility.highContrast ? '#FFFFFF' : colors.brand.navy900} style={[styles.cardLabel, isRTL && styles.rtlText]}>
            {isRTL ? 'خيارات الرؤية والعرض' : 'Options d\'affichage'}
          </MayushText>

          {/* High Contrast */}
          <View style={[styles.toggleRow, isRTL && styles.rtlRow]}>
            <View style={styles.toggleTextCol}>
              <MayushText variant="strongBody" color={accessibility.highContrast ? '#FFFFFF' : colors.brand.navy900} style={isRTL && styles.rtlText}>
                {isRTL ? 'تباين عالي' : 'Contraste élevé'}
              </MayushText>
              <MayushText variant="smallBody" color={accessibility.highContrast ? '#D0D0D0' : colors.neutral.gray500} style={isRTL && styles.rtlText}>
                {isRTL ? 'زيادة تباين الألوان لقراءة أوضح' : 'Renforcer les contrastes des textes et cartes'}
              </MayushText>
            </View>
            <Switch
              value={accessibility.highContrast}
              onValueChange={() => appSettingsState.toggleHighContrast()}
              trackColor={{ false: colors.neutral.gray300, true: colors.brand.orange500 }}
              thumbColor={colors.neutral.white}
            />
          </View>

          <View style={[styles.divider, accessibility.highContrast && styles.highContrastDivider]} />

          {/* Reduced Motion */}
          <View style={[styles.toggleRow, isRTL && styles.rtlRow]}>
            <View style={styles.toggleTextCol}>
              <MayushText variant="strongBody" color={accessibility.highContrast ? '#FFFFFF' : colors.brand.navy900} style={isRTL && styles.rtlText}>
                {isRTL ? 'تقليل الحركة والأنيميشن' : 'Réduire les mouvements'}
              </MayushText>
              <MayushText variant="smallBody" color={accessibility.highContrast ? '#D0D0D0' : colors.neutral.gray500} style={isRTL && styles.rtlText}>
                {isRTL ? 'إيقاف التأثيرات الحركية الانتقالية' : 'Désactiver les animations de transition'}
              </MayushText>
            </View>
            <Switch
              value={accessibility.reducedMotion}
              onValueChange={() => appSettingsState.toggleReducedMotion()}
              trackColor={{ false: colors.neutral.gray300, true: colors.brand.orange500 }}
              thumbColor={colors.neutral.white}
            />
          </View>

          <View style={[styles.divider, accessibility.highContrast && styles.highContrastDivider]} />

          {/* Readable Font */}
          <View style={[styles.toggleRow, isRTL && styles.rtlRow]}>
            <View style={styles.toggleTextCol}>
              <MayushText variant="strongBody" color={accessibility.highContrast ? '#FFFFFF' : colors.brand.navy900} style={isRTL && styles.rtlText}>
                {isRTL ? 'خط عريض سهل القراءة' : 'Typographie renforcée'}
              </MayushText>
              <MayushText variant="smallBody" color={accessibility.highContrast ? '#D0D0D0' : colors.neutral.gray500} style={isRTL && styles.rtlText}>
                {isRTL ? 'استخدام خطوط أكثر وضوحاً وسماكة' : 'Utiliser une police lisible renforcée'}
              </MayushText>
            </View>
            <Switch
              value={accessibility.readableFont}
              onValueChange={() => appSettingsState.toggleReadableFont()}
              trackColor={{ false: colors.neutral.gray300, true: colors.brand.orange500 }}
              thumbColor={colors.neutral.white}
            />
          </View>
        </View>

        {/* Next Card to Permissions */}
        <TouchableOpacity
          style={[styles.nextCard, isRTL && styles.rtlRow, accessibility.highContrast && styles.highContrastCard]}
          onPress={onNavigateAppPermissions}
          activeOpacity={0.85}
        >
          <View style={styles.nextIconBox}>
            <MayushIcon name="shield" size={20} color={colors.brand.navy900} />
          </View>
          <View style={styles.nextTextCol}>
            <MayushText variant="strongBody" color={accessibility.highContrast ? '#FFFFFF' : colors.brand.navy900} style={isRTL && styles.rtlText}>
              {isRTL ? 'أذونات التطبيق' : 'Autorisations de l\'application'}
            </MayushText>
            <MayushText variant="smallBody" color={accessibility.highContrast ? '#D0D0D0' : colors.neutral.gray500} style={isRTL && styles.rtlText}>
              {isRTL ? 'إدارة الكاميرا والموقع والصور' : 'Gérer la caméra, la localisation et les photos'}
            </MayushText>
          </View>
          <MayushIcon name={isRTL ? 'chevron-left' : 'chevron-right'} size={18} color={accessibility.highContrast ? '#FFFFFF' : colors.neutral.gray500} />
        </TouchableOpacity>
      </ScrollView>

      <BottomTabBar activeTab="account" onTabPress={(tab) => onNavigateTab?.(tab)} />
    </View>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: colors.neutral.gray100 },
  highContrastBg: { backgroundColor: '#121212' },
  header: {
    height: 56, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between',
    paddingHorizontal: spacing.md, backgroundColor: colors.neutral.white,
    borderBottomWidth: 1, borderBottomColor: colors.neutral.gray300,
  },
  highContrastHeader: { backgroundColor: '#1E1E1E', borderBottomColor: '#333333' },
  headerTitle: { fontSize: 18, fontWeight: '700' },
  backButton: { width: 40, height: 40, alignItems: 'center', justifyContent: 'center' },
  scrollContent: { padding: spacing.md, gap: spacing.md, paddingBottom: 100 },
  card: {
    backgroundColor: colors.neutral.white, borderRadius: 16, padding: spacing.md,
    borderWidth: 1, borderColor: colors.neutral.gray300, gap: spacing.xs,
  },
  highContrastCard: { backgroundColor: '#1E1E1E', borderColor: '#444444' },
  cardLabel: { fontSize: 16, fontWeight: '700' },
  cardSub: { marginBottom: spacing.xs },
  textSizeRow: { flexDirection: 'row', gap: spacing.xs, marginTop: spacing.xs },
  sizeChip: {
    flex: 1, paddingVertical: spacing.sm, borderRadius: 10, alignItems: 'center',
    backgroundColor: colors.neutral.gray100, borderWidth: 1, borderColor: colors.neutral.gray300,
  },
  sizeChipActive: { backgroundColor: colors.brand.navy900, borderColor: colors.brand.navy900 },
  sizeChipHighContrast: { backgroundColor: colors.brand.orange500, borderColor: colors.brand.orange500 },
  toggleRow: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', paddingVertical: 6 },
  toggleTextCol: { flex: 1, paddingRight: spacing.sm },
  divider: { height: 1, backgroundColor: colors.neutral.gray300 },
  highContrastDivider: { backgroundColor: '#333333' },
  nextCard: {
    flexDirection: 'row', alignItems: 'center', gap: spacing.md,
    backgroundColor: colors.neutral.white, borderRadius: 16, padding: spacing.md,
    borderWidth: 1, borderColor: colors.neutral.gray300,
  },
  nextIconBox: {
    width: 40, height: 40, borderRadius: 10, backgroundColor: colors.neutral.gray100,
    alignItems: 'center', justifyContent: 'center',
  },
  nextTextCol: { flex: 1 },
  rtlRow: { flexDirection: 'row-reverse' },
  rtlText: { textAlign: 'right' },
});
