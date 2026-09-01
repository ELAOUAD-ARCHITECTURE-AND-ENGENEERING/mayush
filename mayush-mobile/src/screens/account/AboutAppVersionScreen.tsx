import React, { useState } from 'react';
import { ScrollView, StyleSheet, TouchableOpacity, View } from 'react-native';
import { accountPreferencesState } from '../../commerce/accountPreferencesState';
import { MayushLogo } from '../../design-system/components/brand/MayushLogo';
import { BottomTabBar, TabKey } from '../../design-system/components/navigation/BottomTabBar';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { colors } from '../../design-system/tokens/colors';
import { spacing } from '../../design-system/tokens/spacing';

export interface AboutAppVersionScreenProps {
  onNavigateTab?: (tab: TabKey) => void;
  onBack?: () => void;
  onNavigateAboutMayush?: () => void;
}

export const AboutAppVersionScreen: React.FC<AboutAppVersionScreenProps> = ({
  onNavigateTab,
  onBack,
  onNavigateAboutMayush,
}) => {
  const isRTL = accountPreferencesState.getSelectedLanguage() === 'ar';
  const [updateChecked, setUpdateChecked] = useState(false);

  return (
    <View style={styles.container}>
      {/* Header */}
      <View style={[styles.header, isRTL && styles.rtlRow]}>
        <TouchableOpacity style={styles.backButton} onPress={onBack} activeOpacity={0.7}>
          <MayushIcon name={isRTL ? 'chevron-right' : 'chevron-left'} size={24} color={colors.brand.navy900} />
        </TouchableOpacity>
        <MayushText variant="pageTitle" color={colors.brand.navy900} style={styles.headerTitle}>
          {isRTL ? 'حول التطبيق' : 'À propos de l\'application'}
        </MayushText>
        <View style={{ width: 40 }} />
      </View>

      <ScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
        {/* Brand Card */}
        <View style={styles.brandCard}>
          <View style={styles.logoContainer}>
            <MayushLogo width={160} height={48} />
          </View>
          <MayushText variant="cardTitle" color={colors.brand.navy900} align="center" style={styles.appTitle}>
            Mayush Mobile
          </MayushText>
          <MayushText variant="smallBody" color={colors.neutral.gray500} align="center">
            {isRTL ? 'L’Élégance au Cœur de Votre Espace' : 'L\'Élégance au Cœur de Votre Espace'}
          </MayushText>

          <View style={styles.versionBadge}>
            <MayushText variant="caption" color={colors.brand.orange500}>
              v1.0.0 (Build 2026.08.07)
            </MayushText>
          </View>
        </View>

        {/* Technical Info Card */}
        <View style={styles.infoCard}>
          <MayushText variant="sectionTitle" color={colors.brand.navy900} style={[styles.sectionLabel, isRTL && styles.rtlText]}>
            {isRTL ? 'معلومات النظام' : 'Informations système'}
          </MayushText>

          <View style={[styles.infoRow, isRTL && styles.rtlRow]}>
            <MayushText variant="smallBody" color={colors.neutral.gray500}>{isRTL ? 'حالة الإصدار' : 'Statut d\'Édition'}</MayushText>
            <MayushText variant="strongBody" color={colors.semantic.success}>{isRTL ? 'نسخة مستقرة' : 'Version Stable'}</MayushText>
          </View>

          <View style={styles.divider} />

          <View style={[styles.infoRow, isRTL && styles.rtlRow]}>
            <MayushText variant="smallBody" color={colors.neutral.gray500}>{isRTL ? 'إطار العمل' : 'Framework'}</MayushText>
            <MayushText variant="strongBody" color={colors.brand.navy900}>Expo SDK 57 / React Native</MayushText>
          </View>

          <View style={styles.divider} />

          <View style={[styles.infoRow, isRTL && styles.rtlRow]}>
            <MayushText variant="smallBody" color={colors.neutral.gray500}>{isRTL ? 'المحرك' : 'Moteur de Rendu'}</MayushText>
            <MayushText variant="strongBody" color={colors.brand.navy900}>Metro Web & Native Bridge</MayushText>
          </View>
        </View>

        {/* Action CTAs */}
        <TouchableOpacity
          style={[styles.actionCard, isRTL && styles.rtlRow]}
          onPress={onNavigateAboutMayush}
          activeOpacity={0.85}
        >
          <View style={styles.actionIconBox}>
            <MayushIcon name="info" size={20} color={colors.brand.orange500} />
          </View>
          <View style={styles.actionTextCol}>
            <MayushText variant="strongBody" color={colors.brand.navy900} style={isRTL && styles.rtlText}>
              {isRTL ? 'عن شركة مايووش ديزاين' : 'À propos de Mayush Design'}
            </MayushText>
            <MayushText variant="smallBody" color={colors.neutral.gray500} style={isRTL && styles.rtlText}>
              {isRTL ? 'رؤيتنا، مهمتنا وقيمنا' : 'Notre vision, mission et valeurs'}
            </MayushText>
          </View>
          <MayushIcon name={isRTL ? 'chevron-left' : 'chevron-right'} size={18} color={colors.neutral.gray500} />
        </TouchableOpacity>

        <TouchableOpacity
          style={[styles.updateButton, isRTL && styles.rtlRow]}
          onPress={() => setUpdateChecked(true)}
          activeOpacity={0.85}
        >
          <MayushIcon name="refresh-cw" size={18} color={colors.brand.navy900} />
          <MayushText variant="button" color={colors.brand.navy900}>
            {updateChecked
              ? (isRTL ? 'التطبيق محدّث لأحدث إصدار' : 'Application à jour')
              : (isRTL ? 'التحقق من التحديثات' : 'Vérifier les mises à jour')}
          </MayushText>
        </TouchableOpacity>
      </ScrollView>

      <BottomTabBar activeTab="account" onTabPress={(tab) => onNavigateTab?.(tab)} />
    </View>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: colors.neutral.gray100 },
  header: {
    height: 56, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between',
    paddingHorizontal: spacing.md, backgroundColor: colors.neutral.white,
    borderBottomWidth: 1, borderBottomColor: colors.neutral.gray300,
  },
  headerTitle: { fontSize: 18, fontWeight: '700' },
  backButton: { width: 40, height: 40, alignItems: 'center', justifyContent: 'center' },
  scrollContent: { padding: spacing.md, gap: spacing.md, paddingBottom: 100 },
  brandCard: {
    backgroundColor: colors.neutral.white, borderRadius: 16, padding: spacing.lg,
    alignItems: 'center', borderWidth: 1, borderColor: colors.neutral.gray300,
  },
  logoContainer: { marginBottom: spacing.sm },
  appTitle: { fontSize: 20, fontWeight: '700', marginTop: spacing.xs, marginBottom: 2 },
  versionBadge: {
    backgroundColor: 'rgba(217,116,52,0.1)', paddingHorizontal: 12, paddingVertical: 4,
    borderRadius: 8, marginTop: spacing.md,
  },
  infoCard: {
    backgroundColor: colors.neutral.white, borderRadius: 16, padding: spacing.md,
    borderWidth: 1, borderColor: colors.neutral.gray300, gap: spacing.xs,
  },
  sectionLabel: { fontSize: 15, fontWeight: '700', marginBottom: spacing.xs },
  infoRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingVertical: 4 },
  divider: { height: 1, backgroundColor: colors.neutral.gray300 },
  actionCard: {
    flexDirection: 'row', alignItems: 'center', gap: spacing.md,
    backgroundColor: colors.neutral.white, borderRadius: 16, padding: spacing.md,
    borderWidth: 1, borderColor: colors.brand.orange500,
  },
  actionIconBox: {
    width: 42, height: 42, borderRadius: 12, backgroundColor: 'rgba(217,116,52,0.1)',
    alignItems: 'center', justifyContent: 'center',
  },
  actionTextCol: { flex: 1 },
  updateButton: {
    flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: spacing.xs,
    backgroundColor: colors.neutral.white, borderRadius: 14, padding: spacing.md,
    borderWidth: 1, borderColor: colors.neutral.gray300,
  },
  rtlRow: { flexDirection: 'row-reverse' },
  rtlText: { textAlign: 'right' },
});
