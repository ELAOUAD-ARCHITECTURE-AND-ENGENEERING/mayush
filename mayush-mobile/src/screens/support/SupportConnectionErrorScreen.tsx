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

export interface SupportConnectionErrorScreenProps {
  onNavigateTab?: (tab: TabKey) => void;
  onBack?: () => void;
  onRetry?: () => void;
  onContinueInApp?: () => void;
  onNavigateTemporarilyUnavailable?: () => void;
}

export const SupportConnectionErrorScreen: React.FC<SupportConnectionErrorScreenProps> = ({
  onNavigateTab,
  onBack,
  onRetry,
  onContinueInApp,
  onNavigateTemporarilyUnavailable,
}) => {
  const language = accountPreferencesState.getSelectedLanguage();
  const isRTL = language === 'ar';
  const [retrying, setRetrying] = useState(false);

  const handleRetryPress = () => {
    setRetrying(true);
    setTimeout(() => {
      setRetrying(false);
      onRetry?.();
    }, 400);
  };

  return (
    <View style={styles.container}>
      {/* Top Header */}
      <View style={[styles.header, isRTL && styles.rtlRow]}>
        <TouchableOpacity style={styles.backButton} onPress={onBack} activeOpacity={0.7} testID="back-button">
          <MayushIcon name={isRTL ? 'chevron-right' : 'chevron-left'} size={24} color={colors.brand.navy900} />
        </TouchableOpacity>
        <View style={styles.logoContainer}>
          <MayushLogo width={120} height={32} />
        </View>
        <TouchableOpacity
          style={styles.bellButton}
          onPress={onNavigateTemporarilyUnavailable}
          activeOpacity={0.7}
          testID="prototype-nav-unavailable"
        >
          <MayushIcon name="bell" size={22} color={colors.brand.navy900} />
        </TouchableOpacity>
      </View>

      <ScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
        {/* Connection Error Artwork */}
        <View style={styles.artworkSection}>
          <View style={styles.artworkOuterBox}>
            <View style={styles.cloudBg}>
              <MayushIcon name="wifi-off" size={40} color={colors.brand.orange500} />
            </View>
            <View style={styles.warningTriangle}>
              <MayushIcon name="alert-triangle" size={20} color={colors.neutral.white} />
            </View>
          </View>
        </View>

        {/* Headings */}
        <MayushText variant="pageTitle" color={colors.brand.navy900} align="center" style={styles.title}>
          {isRTL ? 'تعذر تحميل الدعم الفني' : 'Impossible de charger l\'assistance'}
        </MayushText>

        <MayushText variant="smallBody" color={colors.neutral.gray500} align="center" style={styles.subtitle}>
          {isRTL
            ? 'نواجه مشكلة في الاتصال. يرجى التحقق من اتصال الإنترنت وإعادة المحاولة.'
            : 'Nous rencontrons un problème de connexion. Veuillez vérifier votre connexion Internet et réessayer.'}
        </MayushText>

        {/* Action Buttons */}
        <View style={styles.actionsContainer}>
          <TouchableOpacity
            style={styles.primaryBtn}
            onPress={handleRetryPress}
            activeOpacity={0.85}
            testID="retry-button"
          >
            <MayushIcon name="refresh-cw" size={18} color={colors.neutral.white} />
            <MayushText variant="strongBody" color={colors.neutral.white}>
              {retrying
                ? isRTL
                  ? 'جاري التحقق...'
                  : 'Vérification...'
                : isRTL
                ? 'إعادة المحاولة'
                : 'Réessayer'}
            </MayushText>
          </TouchableOpacity>

          <TouchableOpacity
            style={styles.secondaryBtn}
            onPress={onContinueInApp}
            activeOpacity={0.7}
            testID="continue-in-app-button"
          >
            <MayushIcon name="shopping-bag" size={18} color={colors.brand.navy900} />
            <MayushText variant="strongBody" color={colors.brand.navy900}>
              {isRTL ? 'المتابعة في التطبيق' : 'Continuer dans l\'application'}
            </MayushText>
          </TouchableOpacity>
        </View>

        {/* Info Card Footer */}
        <View style={[styles.infoCard, isRTL && styles.rtlRow]}>
          <View style={styles.infoIconCircle}>
            <MayushIcon name="info" size={18} color={colors.brand.navy900} />
          </View>
          <View style={[styles.infoTextContainer, isRTL && styles.rtlTextCol]}>
            <MayushText variant="strongBody" color={colors.brand.navy900} style={{ fontSize: 13 }}>
              {isRTL ? 'هل تحتاج إلى مساعدة؟' : 'Besoin d\'aide ?'}
            </MayushText>
            <MayushText variant="smallBody" color={colors.neutral.gray500} style={{ fontSize: 12 }}>
              {isRTL ? 'راسلنا عبر البريد الإلكتروني ' : 'Envoyez-nous un e-mail à '}
              <MayushText variant="smallBody" color={colors.brand.orange500} style={{ fontWeight: '600' }}>
                support@mayushdesign.ma
              </MayushText>
            </MayushText>
          </View>
        </View>

        {/* Dev Prototype Navigation Link */}
        {onNavigateTemporarilyUnavailable ? (
          <TouchableOpacity
            style={styles.protoNavBtn}
            onPress={onNavigateTemporarilyUnavailable}
            activeOpacity={0.8}
            testID="proto-next-309-822"
          >
            <MayushText variant="smallBody" color={colors.neutral.gray500}>
              {isRTL ? 'اختبار حالة الدعم غير المتاح (309:822)' : 'Simuler Assistance indisponible (309:822) →'}
            </MayushText>
          </TouchableOpacity>
        ) : null}
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
  cloudBg: {
    width: 80,
    height: 80,
    borderRadius: 40,
    backgroundColor: 'rgba(232,125,62,0.1)',
    alignItems: 'center',
    justifyContent: 'center',
  },
  warningTriangle: {
    position: 'absolute',
    bottom: -4,
    right: -4,
    width: 36,
    height: 36,
    borderRadius: 18,
    backgroundColor: colors.brand.navy900,
    alignItems: 'center',
    justifyContent: 'center',
  },
  title: { marginBottom: spacing.xs, paddingHorizontal: spacing.md },
  subtitle: { lineHeight: 20, paddingHorizontal: spacing.lg, marginBottom: spacing.xl },
  actionsContainer: { width: '100%', gap: spacing.sm, marginBottom: spacing.xl },
  primaryBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: spacing.xs,
    backgroundColor: colors.brand.orange500,
    borderRadius: 14,
    paddingVertical: 14,
  },
  secondaryBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: spacing.xs,
    backgroundColor: colors.neutral.white,
    borderRadius: 14,
    paddingVertical: 14,
    borderWidth: 1,
    borderColor: colors.brand.navy900,
  },
  infoCard: {
    width: '100%',
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.sm,
    backgroundColor: 'rgba(15,23,42,0.04)',
    borderRadius: 14,
    padding: spacing.md,
  },
  infoIconCircle: {
    width: 32,
    height: 32,
    borderRadius: 16,
    backgroundColor: colors.neutral.white,
    alignItems: 'center',
    justifyContent: 'center',
  },
  infoTextContainer: { flex: 1 },
  protoNavBtn: { marginTop: spacing.md, paddingVertical: spacing.xs },
  rtlRow: { flexDirection: 'row-reverse' },
  rtlTextCol: { alignItems: 'flex-end' },
});
