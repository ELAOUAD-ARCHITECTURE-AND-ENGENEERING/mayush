import React from 'react';
import { ScrollView, StyleSheet, TouchableOpacity, View } from 'react-native';
import { MayushLogo } from '../../design-system/components/brand/MayushLogo';
import { PrimaryButton } from '../../design-system/components/actions/PrimaryButton';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { useTheme } from '../../design-system/theme/useTheme';
import { colors } from '../../design-system/tokens/colors';
import { authState } from '../../commerce/authState';

export interface OtpErrorScreenProps {
  onBack?: () => void;
  onRetry?: () => void;
  onRequestNewPassword?: () => void;
}

export const OtpErrorScreen: React.FC<OtpErrorScreenProps> = ({
  onBack,
  onRetry,
  onRequestNewPassword,
}) => {
  const { isRTL, language } = useTheme();
  const errorMessage = authState.getOtpError() || 'Code OTP incorrect ou expiré. Veuillez vérifier votre SMS.';

  const copy =
    language === 'ar'
      ? {
          title: 'رمز OTP غير صحيح',
          retryCTA: 'إعادة محاولة التحقق',
          requestNewCTA: 'متابعة لإنشاء كلمة السر',
          back: 'العودة',
        }
      : {
          title: 'Code OTP incorrect',
          retryCTA: 'Réessayer la vérification',
          requestNewCTA: 'Définir un nouveau mot de passe',
          back: 'Retour',
        };

  return (
    <View style={styles.screen} accessibilityLabel={copy.title}>
      <ScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
        <View style={[styles.header, isRTL && styles.rowReverse]}>
          <TouchableOpacity onPress={onBack} style={styles.backButton} accessibilityRole="button">
            <MayushIcon name={isRTL ? 'chevron-right' : 'chevron-left'} size={24} color={colors.brand.navy900} />
          </TouchableOpacity>
          <MayushLogo width={150} height={45} />
          <View style={styles.headerSpacer} />
        </View>

        <View style={styles.badgeContainer}>
          <View style={styles.badgeCircle}>
            <MayushIcon name="alert-circle" size={32} color={colors.semantic.error} />
          </View>
        </View>

        <MayushText variant="display" color={colors.brand.navy900} align="center" style={styles.title}>
          {copy.title}
        </MayushText>

        <View style={styles.errorAlertCard}>
          <MayushIcon name="alert-circle" size={20} color={colors.semantic.error} />
          <MayushText variant="body" color={colors.semantic.error} style={styles.errorText}>
            {errorMessage}
          </MayushText>
        </View>

        <View style={styles.card}>
          <PrimaryButton label={copy.retryCTA} onPress={onRetry || onBack || (() => undefined)} />

          {onRequestNewPassword ? (
            <TouchableOpacity
              onPress={onRequestNewPassword}
              style={styles.secondaryButton}
              accessibilityRole="button"
            >
              <MayushText variant="button" color={colors.brand.navy900} align="center">
                {copy.requestNewCTA}
              </MayushText>
            </TouchableOpacity>
          ) : null}

          <TouchableOpacity onPress={onBack} style={styles.backLink} accessibilityRole="button">
            <MayushText variant="caption" color={colors.neutral.gray700} align="center">
              {copy.back}
            </MayushText>
          </TouchableOpacity>
        </View>
      </ScrollView>
    </View>
  );
};

const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: '#FFF8F0' },
  scrollContent: { paddingHorizontal: 22, paddingBottom: 36, paddingTop: 16 },
  rowReverse: { flexDirection: 'row-reverse' },
  header: {
    height: 56,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
  },
  backButton: { width: 40, height: 40, alignItems: 'center', justifyContent: 'center' },
  headerSpacer: { width: 40 },
  badgeContainer: { alignItems: 'center', marginTop: 24 },
  badgeCircle: {
    width: 64,
    height: 64,
    borderRadius: 32,
    backgroundColor: '#FFEBEE',
    alignItems: 'center',
    justifyContent: 'center',
    borderWidth: 1,
    borderColor: '#FFCDD2',
  },
  title: { marginTop: 18, fontSize: 26, lineHeight: 32 },
  errorAlertCard: {
    marginTop: 20,
    padding: 14,
    borderRadius: 14,
    backgroundColor: '#FFEBEE',
    borderWidth: 1,
    borderColor: '#FFCDD2',
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
  },
  errorText: { flex: 1, fontSize: 14, lineHeight: 20 },
  card: {
    marginTop: 20,
    padding: 20,
    borderRadius: 20,
    backgroundColor: colors.surface.white,
    borderWidth: 1,
    borderColor: colors.surface.borderWarm,
    shadowColor: colors.brand.navy900,
    shadowOpacity: 0.06,
    shadowRadius: 12,
    shadowOffset: { width: 0, height: 4 },
    elevation: 3,
  },
  secondaryButton: {
    minHeight: 48,
    marginTop: 12,
    borderRadius: 12,
    borderWidth: 1.5,
    borderColor: colors.brand.navy900,
    alignItems: 'center',
    justifyContent: 'center',
  },
  backLink: { marginTop: 16, paddingVertical: 6 },
});
