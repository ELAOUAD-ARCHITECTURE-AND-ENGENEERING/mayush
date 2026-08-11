import React from 'react';
import { View, StyleSheet, ScrollView, TouchableOpacity } from 'react-native';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { colors } from '../../design-system/tokens/colors';
import { spacing } from '../../design-system/tokens/spacing';
import { accountPreferencesState } from '../../commerce/accountPreferencesState';
import { systemState } from '../../commerce/systemState';

interface ForcedUpdateRequiredScreenProps {
  onUpdateNow?: () => void;
  onNavigatePrototypeNext?: () => void;
}

export const ForcedUpdateRequiredScreen: React.FC<ForcedUpdateRequiredScreenProps> = ({
  onUpdateNow,
  onNavigatePrototypeNext,
}) => {
  const currentLanguage = accountPreferencesState.getSelectedLanguage();
  const isRTL = currentLanguage === 'ar';
  const updateInfo = systemState.getAppUpdateInfo();

  const handleUpdate = () => {
    if (onUpdateNow) {
      onUpdateNow();
    }
  };

  return (
    <View style={styles.container}>
      <View style={styles.header}>
        <MayushText variant="cardTitle" color={colors.brand.navy900}>
          Mayush Mobile
        </MayushText>
        {onNavigatePrototypeNext ? (
          <TouchableOpacity onPress={onNavigatePrototypeNext} style={styles.protoDevBtn}>
            <MayushText variant="caption" color={colors.neutral.gray500}>
              Next Prototype (309:826) →
            </MayushText>
          </TouchableOpacity>
        ) : null}
      </View>

      <ScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
        {/* Hero Artwork Box */}
        <View style={styles.artworkContainer}>
          <View style={styles.artworkOuterBox}>
            <View style={styles.syncCircleBg}>
              <MayushIcon name="shield-check" size={42} color={colors.neutral.white} />
            </View>
            <View style={styles.lockBadge}>
              <MayushIcon name="lock" size={16} color={colors.neutral.white} />
            </View>
          </View>
        </View>

        {/* Mandatory Badge & Title */}
        <View style={styles.textHeaderContainer}>
          <View style={styles.mandatoryBadge}>
            <MayushText variant="caption" color={colors.brand.orange500} style={styles.mandatoryBadgeText}>
              {isRTL ? 'تحديث إجباري' : 'MISE À JOUR OBLIGATOIRE'}
            </MayushText>
          </View>
          <MayushText variant="sectionTitle" color={colors.brand.navy900} style={[styles.title, isRTL && styles.rtlText]}>
            {isRTL ? 'التحديث مطلوب للمتابعة' : 'Mise à jour requise pour continuer'}
          </MayushText>
          <MayushText variant="body" color={colors.neutral.gray700} style={[styles.subtitle, isRTL && styles.rtlText]}>
            {isRTL
              ? 'يلزم إجراء تحديث هامن لضمان أمان التطبيق وتوافقه مع خدماتنا.'
              : 'Une mise à jour critique est nécessaire pour garantir la sécurité et le bon fonctionnement de l’application.'}
          </MayushText>
        </View>

        {/* Version Comparison Card */}
        <View style={styles.versionCard}>
          <View style={[styles.versionRow, isRTL && styles.rtlRow]}>
            <View style={styles.versionCol}>
              <MayushText variant="caption" color={colors.neutral.gray500}>
                {isRTL ? 'الإصدار الحالي' : 'Version actuelle'}
              </MayushText>
              <MayushText variant="strongBody" color={colors.neutral.gray700}>
                v{updateInfo.currentVersion}
              </MayushText>
            </View>

            <View style={styles.arrowIconBox}>
              <MayushIcon name={isRTL ? 'arrow-left' : 'arrow-right'} size={18} color={colors.brand.orange500} />
            </View>

            <View style={styles.versionCol}>
              <MayushText variant="caption" color={colors.neutral.gray500}>
                {isRTL ? 'الإصدار المطلوب' : 'Nouvelle version'}
              </MayushText>
              <View style={styles.newVersionBadgeRow}>
                <MayushText variant="strongBody" color={colors.brand.navy900}>
                  v{updateInfo.latestVersion}
                </MayushText>
                <View style={styles.critiqueBadge}>
                  <MayushText variant="caption" color={colors.neutral.white}>
                    {isRTL ? 'هام' : 'CRITIQUE'}
                  </MayushText>
                </View>
              </View>
            </View>
          </View>

          <View style={styles.divider} />

          {/* Bullet Points */}
          <MayushText variant="caption" color={colors.brand.navy900} style={[styles.bulletHeader, isRTL && styles.rtlText]}>
            {isRTL ? 'تعديلات رئيسية شاملة:' : 'Correctifs de sécurité obligatoires :'}
          </MayushText>
          {(isRTL ? updateInfo.releaseNotesAr : updateInfo.releaseNotes).map((note, idx) => (
            <View key={idx} style={[styles.bulletRow, isRTL && styles.rtlRow]}>
              <View style={styles.bulletDot} />
              <MayushText variant="smallBody" color={colors.neutral.gray700} style={[styles.bulletText, isRTL && styles.rtlText]}>
                {note}
              </MayushText>
            </View>
          ))}
        </View>

        {/* Action Button - Single Mandatory Primary CTA (No Skip / No Later) */}
        <View style={styles.actionsContainer}>
          <TouchableOpacity onPress={handleUpdate} activeOpacity={0.8} style={styles.primaryBtn}>
            <MayushIcon name="download" size={20} color={colors.neutral.white} />
            <MayushText variant="button" color={colors.neutral.white}>
              {isRTL ? 'تحديث التطبيق الآن' : 'Mettre à jour maintenant'}
            </MayushText>
          </TouchableOpacity>
        </View>

        {/* Legal Disclaimer Footer */}
        <View style={[styles.legalFooter, isRTL && styles.rtlRow]}>
          <MayushIcon name="shield-check" size={16} color={colors.neutral.gray500} />
          <MayushText variant="caption" color={colors.neutral.gray500} style={[styles.legalText, isRTL && styles.rtlText]}>
            {isRTL
              ? 'تحديث تطبيق مايووش ديزاين آمن ومجاني بشكل كامل عبر المتاجر الرسمية.'
              : 'La mise à jour de Mayush Mobile est sécurisée et gratuite sur le magasin d’applications.'}
          </MayushText>
        </View>
      </ScrollView>
    </View>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: colors.neutral.gray100 },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: spacing.md,
    paddingTop: 48,
    paddingBottom: spacing.sm,
    backgroundColor: colors.neutral.white,
    borderBottomWidth: 1,
    borderBottomColor: 'rgba(0,0,0,0.05)',
  },
  protoDevBtn: { padding: spacing.xs },
  scrollContent: { padding: spacing.md, alignItems: 'center' },
  artworkContainer: { marginVertical: spacing.lg, alignItems: 'center' },
  artworkOuterBox: {
    width: 140,
    height: 140,
    borderRadius: 24,
    backgroundColor: colors.neutral.white,
    alignItems: 'center',
    justifyContent: 'center',
    position: 'relative',
    borderWidth: 1,
    borderColor: 'rgba(0,0,0,0.05)',
    elevation: 2,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.08,
    shadowRadius: 8,
  },
  syncCircleBg: {
    width: 80,
    height: 80,
    borderRadius: 40,
    backgroundColor: colors.brand.orange500,
    alignItems: 'center',
    justifyContent: 'center',
  },
  lockBadge: {
    position: 'absolute',
    bottom: 16,
    right: 16,
    width: 32,
    height: 32,
    borderRadius: 16,
    backgroundColor: colors.brand.navy900,
    alignItems: 'center',
    justifyContent: 'center',
  },
  textHeaderContainer: { alignItems: 'center', marginBottom: spacing.lg },
  mandatoryBadge: {
    backgroundColor: 'rgba(217,116,52,0.15)',
    paddingHorizontal: spacing.sm,
    paddingVertical: 4,
    borderRadius: 8,
    marginBottom: spacing.xs,
  },
  mandatoryBadgeText: { fontWeight: '700', fontSize: 11 },
  title: { textAlign: 'center', marginBottom: spacing.xs, paddingHorizontal: spacing.sm },
  subtitle: { textAlign: 'center', lineHeight: 20, paddingHorizontal: spacing.md },
  versionCard: {
    width: '100%',
    backgroundColor: colors.neutral.white,
    borderRadius: 16,
    padding: spacing.md,
    marginBottom: spacing.lg,
    borderWidth: 1,
    borderColor: 'rgba(0,0,0,0.05)',
  },
  versionRow: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-around' },
  versionCol: { alignItems: 'center' },
  arrowIconBox: {
    width: 36,
    height: 36,
    borderRadius: 18,
    backgroundColor: 'rgba(232,125,62,0.1)',
    alignItems: 'center',
    justifyContent: 'center',
  },
  newVersionBadgeRow: { flexDirection: 'row', alignItems: 'center', gap: 6 },
  critiqueBadge: {
    backgroundColor: colors.brand.orange500,
    paddingHorizontal: 6,
    paddingVertical: 2,
    borderRadius: 6,
  },
  divider: { height: 1, backgroundColor: 'rgba(0,0,0,0.06)', marginVertical: spacing.sm },
  bulletHeader: { fontWeight: '700', marginBottom: spacing.xs },
  bulletRow: { flexDirection: 'row', alignItems: 'center', gap: spacing.xs, marginBottom: 6 },
  bulletDot: { width: 6, height: 6, borderRadius: 3, backgroundColor: colors.brand.orange500 },
  bulletText: { flex: 1 },
  actionsContainer: { width: '100%', marginBottom: spacing.lg },
  primaryBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: spacing.xs,
    backgroundColor: colors.brand.orange500,
    borderRadius: 14,
    paddingVertical: 14,
  },
  legalFooter: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.xs,
    paddingHorizontal: spacing.sm,
  },
  legalText: { flex: 1, fontSize: 11, lineHeight: 16 },
  rtlRow: { flexDirection: 'row-reverse' },
  rtlText: { textAlign: 'right' },
});
