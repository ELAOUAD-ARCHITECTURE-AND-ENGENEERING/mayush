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

export interface MaintenanceModeServicesImpactedScreenProps {
  onNavigateTab?: (tab: TabKey) => void;
  onBack?: () => void;
  onRetry?: () => void;
  onContactSupport?: () => void;
  onNavigateAppUpdate?: () => void;
}

export const MaintenanceModeServicesImpactedScreen: React.FC<MaintenanceModeServicesImpactedScreenProps> = ({
  onNavigateTab,
  onBack,
  onRetry,
  onContactSupport,
  onNavigateAppUpdate,
}) => {
  const language = accountPreferencesState.getSelectedLanguage();
  const isRTL = language === 'ar';

  const maintenanceInfo = systemState.getMaintenanceInfo();
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
        <TouchableOpacity
          style={styles.bellButton}
          onPress={onNavigateAppUpdate}
          activeOpacity={0.7}
          testID="prototype-nav-update"
        >
          <MayushIcon name="bell" size={22} color={colors.brand.navy900} />
        </TouchableOpacity>
      </View>

      <ScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
        {/* Maintenance Artwork */}
        <View style={styles.artworkSection}>
          <View style={styles.artworkOuterBox}>
            <View style={styles.wrenchCircleBg}>
              <MayushIcon name="wrench" size={40} color={colors.neutral.white} />
            </View>
            <View style={styles.barrierBadge}>
              <MayushIcon name="alert-triangle" size={18} color={colors.brand.orange500} />
            </View>
          </View>
        </View>

        {/* Headings */}
        <MayushText variant="pageTitle" color={colors.brand.navy900} align="center" style={styles.title}>
          {isRTL ? maintenanceInfo.titleAr : maintenanceInfo.title}
        </MayushText>

        <MayushText variant="smallBody" color={colors.neutral.gray500} align="center" style={styles.subtitle}>
          {isRTL ? maintenanceInfo.descriptionAr : maintenanceInfo.description}
        </MayushText>

        {/* Impacted Services Card */}
        <View style={styles.card}>
          <MayushText
            variant="strongBody"
            color={colors.brand.navy900}
            align={isRTL ? 'right' : 'left'}
            style={styles.cardTitle}
          >
            {isRTL ? 'الخدمات المتأثرة' : 'Services impactés'}
          </MayushText>

          {maintenanceInfo.impactedServices.map((service, idx) => (
            <View key={service.id}>
              {idx > 0 ? <View style={styles.divider} /> : null}
              <View style={[styles.serviceRow, isRTL && styles.rtlRow]}>
                <View style={styles.serviceIconCircle}>
                  <MayushIcon name={service.icon as any} size={18} color={colors.brand.orange500} />
                </View>
                <MayushText
                  variant="body"
                  color={colors.brand.navy900}
                  style={[styles.serviceName, isRTL && styles.rtlText]}
                >
                  {isRTL ? service.nameAr : service.name}
                </MayushText>
                <View style={styles.badgeUnavailable}>
                  <MayushText variant="caption" color="#D97706" style={{ fontWeight: '700' }}>
                    {isRTL ? service.statusLabelAr : service.statusLabel}
                  </MayushText>
                </View>
              </View>
            </View>
          ))}
        </View>

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
            onPress={onContactSupport}
            activeOpacity={0.7}
            testID="contact-support-button"
          >
            <MayushIcon name="headphones" size={18} color={colors.brand.navy900} />
            <MayushText variant="strongBody" color={colors.brand.navy900}>
              {isRTL ? 'التواصل مع الدعم' : 'Contacter le support'}
            </MayushText>
          </TouchableOpacity>
        </View>

        {/* Footer Timestamp (Last Checked Only - No invented ETA) */}
        <View style={[styles.timestampRow, isRTL && styles.rtlRow]}>
          <MayushIcon name="clock" size={14} color={colors.neutral.gray500} />
          <MayushText variant="caption" color={colors.neutral.gray500}>
            {isRTL
              ? `آخر تحقق: ${maintenanceInfo.lastCheckedTimestamp}`
              : `Dernière vérification : ${maintenanceInfo.lastCheckedTimestamp}`}
          </MayushText>
        </View>

        {/* Dev Prototype Navigation Link */}
        {onNavigateAppUpdate ? (
          <TouchableOpacity
            style={styles.protoNavBtn}
            onPress={onNavigateAppUpdate}
            activeOpacity={0.8}
            testID="proto-next-309-824"
          >
            <MayushText variant="smallBody" color={colors.neutral.gray500}>
              {isRTL ? 'اختبار تحديث التطبيق (309:824)' : 'Simuler Mise à jour disponible (309:824) →'}
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
  wrenchCircleBg: {
    width: 76,
    height: 76,
    borderRadius: 38,
    backgroundColor: colors.brand.orange500,
    alignItems: 'center',
    justifyContent: 'center',
  },
  barrierBadge: {
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
  cardTitle: { fontSize: 16, fontWeight: '700', marginBottom: spacing.sm },
  serviceRow: { flexDirection: 'row', alignItems: 'center', gap: spacing.xs, paddingVertical: 6 },
  serviceIconCircle: {
    width: 36,
    height: 36,
    borderRadius: 18,
    backgroundColor: '#FFF8E6',
    alignItems: 'center',
    justifyContent: 'center',
  },
  serviceName: { flex: 1, fontSize: 14, fontWeight: '600' },
  badgeUnavailable: {
    backgroundColor: '#FFF8E6',
    paddingHorizontal: 10,
    paddingVertical: 5,
    borderRadius: 12,
  },
  divider: { height: 1, backgroundColor: colors.neutral.gray300, marginVertical: 4 },
  actionsContainer: { width: '100%', gap: spacing.sm, marginBottom: spacing.lg },
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
  timestampRow: { flexDirection: 'row', alignItems: 'center', gap: 6 },
  protoNavBtn: { marginTop: spacing.md, paddingVertical: spacing.xs },
  rtlRow: { flexDirection: 'row-reverse' },
  rtlText: { textAlign: 'right' },
});
