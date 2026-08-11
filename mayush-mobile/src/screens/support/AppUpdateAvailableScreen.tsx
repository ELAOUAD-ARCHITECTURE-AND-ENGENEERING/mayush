import React, { useState } from 'react';
import { ScrollView, StyleSheet, TouchableOpacity, View } from 'react-native';
import { accountPreferencesState } from '../../commerce/accountPreferencesState';
import { systemState } from '../../commerce/systemState';
import { MayushLogo } from '../../design-system/components/brand/MayushLogo';
import { TabKey } from '../../design-system/components/navigation/BottomTabBar';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { colors } from '../../design-system/tokens/colors';
import { spacing } from '../../design-system/tokens/spacing';

export interface AppUpdateAvailableScreenProps {
  onNavigateTab?: (tab: TabKey) => void;
  onBack?: () => void;
  onUpdateNow?: () => void;
  onLater?: () => void;
  onNavigateLegalCenter?: () => void;
  onNavigatePrivacyPolicy?: () => void;
  onNavigateForcedUpdate?: () => void;
}

export const AppUpdateAvailableScreen: React.FC<AppUpdateAvailableScreenProps> = ({
  onNavigateTab,
  onBack,
  onUpdateNow,
  onLater,
  onNavigateLegalCenter,
  onNavigatePrivacyPolicy,
  onNavigateForcedUpdate,
}) => {
  const language = accountPreferencesState.getSelectedLanguage();
  const isRTL = language === 'ar';

  const updateInfo = systemState.getAppUpdateInfo();
  const [updateStarted, setUpdateStarted] = useState(false);

  const handleUpdatePress = () => {
    setUpdateStarted(true);
    if (onUpdateNow) {
      onUpdateNow();
    }
  };

  return (
    <View style={styles.container}>
      {/* Top Header */}
      <View style={[styles.header, isRTL && styles.rtlRow]}>
        {onBack ? (
          <TouchableOpacity style={styles.backButton} onPress={onBack} activeOpacity={0.7} testID="back-button">
            <MayushIcon name={isRTL ? 'chevron-right' : 'chevron-left'} size={24} color={colors.brand.navy900} />
          </TouchableOpacity>
        ) : (
          <View style={{ width: 40 }} />
        )}
        <View style={styles.logoContainer}>
          <MayushLogo width={120} height={32} />
        </View>
        <TouchableOpacity style={styles.bellButton} onPress={onLater} activeOpacity={0.7}>
          <MayushIcon name="bell" size={22} color={colors.brand.navy900} />
        </TouchableOpacity>
      </View>

      <ScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
        {/* Sync / Update Artwork */}
        <View style={styles.artworkSection}>
          <View style={styles.artworkOuterBox}>
            <View style={styles.syncCircleBg}>
              <MayushIcon name="refresh-cw" size={40} color={colors.neutral.white} />
            </View>

            <View style={styles.cloudDownloadBadge}>
              <MayushIcon name="download" size={18} color={colors.brand.orange500} />
            </View>
          </View>
        </View>

        {/* Headings */}
        <MayushText variant="pageTitle" color={colors.brand.navy900} align="center" style={styles.title}>
          {isRTL ? 'تحديث متاح' : 'Mise à jour disponible'}
        </MayushText>

        <MayushText variant="smallBody" color={colors.neutral.gray500} align="center" style={styles.subtitle}>
          {isRTL
            ? 'تتوفر نسخة جديدة من التطبيق الآن.'
            : 'Une nouvelle version de l\'application est maintenant disponible.'}
        </MayushText>

        {/* Details Card */}
        <View style={styles.card}>
          {/* Row 1: Current Version */}
          <View style={[styles.detailRow, isRTL && styles.rtlRow]}>
            <View style={styles.iconCircleBg}>
              <MayushIcon name="info" size={18} color={colors.brand.orange500} />
            </View>
            <View style={[styles.detailTextCol, isRTL && styles.rtlTextCol]}>
              <MayushText variant="caption" color={colors.neutral.gray500}>
                {isRTL ? 'الإصدار الحالي' : 'Version actuelle'}
              </MayushText>
              <MayushText variant="strongBody" color={colors.brand.navy900} style={styles.versionNum}>
                {updateInfo.currentVersion}
              </MayushText>
            </View>
          </View>

          <View style={styles.divider} />

          {/* Row 2: New Version */}
          <View style={[styles.detailRow, isRTL && styles.rtlRow]}>
            <View style={styles.iconCircleBg}>
              <MayushIcon name="download" size={18} color={colors.brand.orange500} />
            </View>
            <View style={[styles.detailTextCol, isRTL && styles.rtlTextCol]}>
              <MayushText variant="caption" color={colors.neutral.gray500}>
                {isRTL ? 'الإصدار الجديد' : 'Nouvelle version'}
              </MayushText>
              <View style={[styles.newVerRow, isRTL && styles.rtlRow]}>
                <MayushText variant="strongBody" color={colors.brand.navy900} style={styles.versionNum}>
                  {updateInfo.latestVersion}
                </MayushText>
                <View style={styles.badgeNew}>
                  <MayushText variant="caption" color={colors.neutral.white} style={{ fontWeight: '800' }}>
                    {isRTL ? 'جديد' : 'NOUVEAU'}
                  </MayushText>
                </View>
              </View>
            </View>
          </View>

          <View style={styles.divider} />

          {/* Row 3: Release Notes */}
          <View style={[styles.detailRowTop, isRTL && styles.rtlRow]}>
            <View style={styles.iconCircleBg}>
              <MayushIcon name="list" size={18} color={colors.brand.orange500} />
            </View>
            <View style={[styles.detailTextCol, isRTL && styles.rtlTextCol]}>
              <MayushText variant="strongBody" color={colors.brand.navy900} style={{ marginBottom: 6 }}>
                {isRTL ? 'في هذا التحديث' : 'Dans cette mise à jour'}
              </MayushText>

              {(isRTL ? updateInfo.releaseNotesAr : updateInfo.releaseNotes).map((note, i) => (
                <View key={i} style={[styles.bulletRow, isRTL && styles.rtlRow]}>
                  <MayushText variant="smallBody" color={colors.brand.orange500}>
                    •
                  </MayushText>
                  <MayushText
                    variant="smallBody"
                    color={colors.neutral.gray500}
                    style={[styles.bulletText, isRTL && styles.rtlText]}
                  >
                    {note}
                  </MayushText>
                </View>
              ))}
            </View>
          </View>
        </View>

        {/* Action Buttons */}
        <View style={styles.actionsContainer}>
          <TouchableOpacity
            style={styles.primaryBtn}
            onPress={handleUpdatePress}
            activeOpacity={0.85}
            testID="update-now-button"
          >
            <MayushText variant="strongBody" color={colors.neutral.white}>
              {updateStarted
                ? isRTL
                  ? 'جاري تجهيز التحديث...'
                  : 'Préparation...'
                : isRTL
                ? 'التحديث الآن'
                : 'Mettre à jour maintenant'}
            </MayushText>
          </TouchableOpacity>

          <TouchableOpacity
            style={styles.secondaryBtn}
            onPress={onLater}
            activeOpacity={0.7}
            testID="later-button"
          >
            <MayushText variant="strongBody" color={colors.brand.navy900}>
              {isRTL ? 'لاحقاً' : 'Plus tard'}
            </MayushText>
          </TouchableOpacity>
        </View>

        {/* Footer Legal Note */}
        <View style={[styles.legalRow, isRTL && styles.rtlRow]}>
          <View style={styles.lockCircle}>
            <MayushIcon name="lock" size={14} color={colors.neutral.gray500} />
          </View>

          <MayushText variant="caption" color={colors.neutral.gray500} align={isRTL ? 'right' : 'left'} style={styles.legalText}>
            {isRTL ? (
              <>
                بالتحديث، أنت توافق على{' '}
                <MayushText
                  variant="caption"
                  color={colors.brand.navy900}
                  style={styles.linkText}
                  onPress={onNavigateLegalCenter}
                >
                  شروط الاستخدام
                </MayushText>{' '}
                و{' '}
                <MayushText
                  variant="caption"
                  color={colors.brand.navy900}
                  style={styles.linkText}
                  onPress={onNavigatePrivacyPolicy}
                >
                  سياسة الخصوصية
                </MayushText>
                .
              </>
            ) : (
              <>
                En mettant à jour, vous acceptez nos{' '}
                <MayushText
                  variant="caption"
                  color={colors.brand.navy900}
                  style={styles.linkText}
                  onPress={onNavigateLegalCenter}
                >
                  Conditions d'utilisation
                </MayushText>{' '}
                et notre{' '}
                <MayushText
                  variant="caption"
                  color={colors.brand.navy900}
                  style={styles.linkText}
                  onPress={onNavigatePrivacyPolicy}
                >
                  Politique de confidentialité
                </MayushText>
                .
              </>
            )}
          </MayushText>
        </View>
      </ScrollView>
    </View>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#FAF8F5' },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: spacing.md,
    paddingTop: 48,
    paddingBottom: spacing.sm,
    backgroundColor: '#FAF8F5',
  },
  backButton: { padding: spacing.xs },
  logoContainer: { flex: 1, alignItems: 'center' },
  bellButton: { padding: spacing.xs },
  scrollContent: { padding: spacing.md, paddingBottom: 40, alignItems: 'center' },
  artworkSection: { marginVertical: spacing.lg, alignItems: 'center' },
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
  },
  syncCircleBg: {
    width: 76,
    height: 76,
    borderRadius: 38,
    backgroundColor: colors.brand.orange500,
    alignItems: 'center',
    justifyContent: 'center',
  },
  cloudDownloadBadge: {
    position: 'absolute',
    bottom: 8,
    right: 8,
    width: 34,
    height: 34,
    borderRadius: 17,
    backgroundColor: colors.brand.navy900,
    alignItems: 'center',
    justifyContent: 'center',
  },
  title: { marginBottom: spacing.xs, paddingHorizontal: spacing.md },
  subtitle: { lineHeight: 20, paddingHorizontal: spacing.md, marginBottom: spacing.lg },
  card: {
    width: '100%',
    backgroundColor: colors.neutral.white,
    borderRadius: 16,
    padding: spacing.md,
    marginBottom: spacing.lg,
    borderWidth: 1,
    borderColor: 'rgba(0,0,0,0.05)',
  },
  detailRow: { flexDirection: 'row', alignItems: 'center', gap: spacing.xs, paddingVertical: 4 },
  detailRowTop: { flexDirection: 'row', alignItems: 'flex-start', gap: spacing.xs, paddingVertical: 4 },
  iconCircleBg: {
    width: 36,
    height: 36,
    borderRadius: 18,
    backgroundColor: 'rgba(232,125,62,0.1)',
    alignItems: 'center',
    justifyContent: 'center',
  },
  detailTextCol: { flex: 1 },
  versionNum: { fontSize: 16, fontWeight: '800' },
  newVerRow: { flexDirection: 'row', alignItems: 'center', gap: spacing.xs },
  badgeNew: {
    backgroundColor: colors.brand.orange500,
    paddingHorizontal: 8,
    paddingVertical: 3,
    borderRadius: 8,
  },
  bulletRow: { flexDirection: 'row', alignItems: 'center', gap: 6, marginTop: 2 },
  bulletText: { fontSize: 13, lineHeight: 18 },
  divider: { height: 1, backgroundColor: colors.neutral.gray300, marginVertical: spacing.xs },
  actionsContainer: { width: '100%', gap: spacing.sm, marginBottom: spacing.lg },
  primaryBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: colors.brand.orange500,
    borderRadius: 14,
    paddingVertical: 14,
  },
  secondaryBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: colors.neutral.white,
    borderRadius: 14,
    paddingVertical: 14,
    borderWidth: 1,
    borderColor: colors.brand.navy900,
  },
  legalRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    paddingHorizontal: spacing.sm,
  },
  lockCircle: {
    width: 24,
    height: 24,
    borderRadius: 12,
    backgroundColor: 'rgba(15,23,42,0.05)',
    alignItems: 'center',
    justifyContent: 'center',
  },
  legalText: { flex: 1, fontSize: 11, lineHeight: 16 },
  linkText: { textDecorationLine: 'underline', fontWeight: '700' },
  rtlRow: { flexDirection: 'row-reverse' },
  rtlText: { textAlign: 'right' },
  rtlTextCol: { alignItems: 'flex-end' },
});
