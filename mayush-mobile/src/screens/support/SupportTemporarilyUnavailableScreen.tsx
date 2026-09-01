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

export interface SupportTemporarilyUnavailableScreenProps {
  onNavigateTab?: (tab: TabKey) => void;
  onBack?: () => void;
  onRetry?: () => void;
  onNavigateFaq?: () => void;
  onNavigateMaintenanceMode?: () => void;
}

export const SupportTemporarilyUnavailableScreen: React.FC<SupportTemporarilyUnavailableScreenProps> = ({
  onNavigateTab,
  onBack,
  onRetry,
  onNavigateFaq,
  onNavigateMaintenanceMode,
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
          onPress={onNavigateMaintenanceMode}
          activeOpacity={0.7}
          testID="prototype-nav-maintenance"
        >
          <MayushIcon name="bell" size={22} color={colors.brand.navy900} />
        </TouchableOpacity>
      </View>

      <ScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
        {/* Headset Unavailable Artwork */}
        <View style={styles.artworkSection}>
          <View style={styles.artworkOuterBox}>
            <View style={styles.headsetCircleBg}>
              <MayushIcon name="headphones" size={44} color={colors.brand.navy900} />
            </View>
            <View style={styles.crossCircleBadge}>
              <MayushIcon name="x" size={22} color={colors.neutral.white} />
            </View>
            <View style={styles.speechBubbleBadge}>
              <MayushIcon name="message-square" size={16} color={colors.neutral.white} />
            </View>
          </View>
        </View>

        {/* Headings */}
        <MayushText variant="pageTitle" color={colors.brand.navy900} align="center" style={styles.title}>
          {isRTL ? 'الدعم غير متاح حالياً' : 'Assistance temporairement indisponible'}
        </MayushText>

        <MayushText variant="smallBody" color={colors.neutral.gray500} align="center" style={styles.subtitle}>
          {isRTL
            ? 'نواجه حالياً مشكلة فنية وفريقنا غير متاح في الوقت الحالي.\nيرجى إعادة المحاولة بعد لحظات.'
            : 'Nous rencontrons actuellement un problème technique et notre équipe n\'est pas disponible pour le moment.\nNous vous prions de bien vouloir réessayer dans quelques instants.'}
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
            onPress={onNavigateFaq}
            activeOpacity={0.7}
            testID="consult-faq-button"
          >
            <MayushIcon name="help-circle" size={18} color={colors.brand.navy900} />
            <MayushText variant="strongBody" color={colors.brand.navy900}>
              {isRTL ? 'الاطلاع على الأسئلة الشائعة' : 'Consulter la FAQ'}
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
              {isRTL ? 'هل تحتاج إلى مساعدة عاجلة؟' : 'Besoin d\'aide urgente ?'}
            </MayushText>
            <MayushText variant="smallBody" color={colors.neutral.gray500} style={{ fontSize: 12 }}>
              {isRTL
                ? 'يمكنك الاطلاع على أسئلتنا الشائعة للعثور على إجابات.'
                : 'Vous pouvez consulter notre FAQ pour trouver des réponses.'}
            </MayushText>
          </View>
        </View>

        {/* Dev Prototype Navigation Link */}
        {onNavigateMaintenanceMode ? (
          <TouchableOpacity
            style={styles.protoNavBtn}
            onPress={onNavigateMaintenanceMode}
            activeOpacity={0.8}
            testID="proto-next-309-823"
          >
            <MayushText variant="smallBody" color={colors.neutral.gray500}>
              {isRTL ? 'اختبار وضع الصيانة (309:823)' : 'Simuler Mode Maintenance (309:823) →'}
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
  headsetCircleBg: {
    width: 80,
    height: 80,
    borderRadius: 40,
    backgroundColor: 'rgba(15,23,42,0.05)',
    alignItems: 'center',
    justifyContent: 'center',
  },
  crossCircleBadge: {
    position: 'absolute',
    top: 30,
    right: 30,
    width: 32,
    height: 32,
    borderRadius: 16,
    backgroundColor: colors.brand.orange500,
    alignItems: 'center',
    justifyContent: 'center',
  },
  speechBubbleBadge: {
    position: 'absolute',
    bottom: 12,
    right: 12,
    width: 28,
    height: 28,
    borderRadius: 14,
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
