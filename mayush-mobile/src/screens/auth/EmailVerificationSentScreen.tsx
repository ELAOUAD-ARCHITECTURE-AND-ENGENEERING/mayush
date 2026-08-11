import React, { useState } from 'react';
import { ScrollView, StyleSheet, TouchableOpacity, View } from 'react-native';
import { MayushLogo } from '../../design-system/components/brand/MayushLogo';
import { PrimaryButton } from '../../design-system/components/actions/PrimaryButton';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { useTheme } from '../../design-system/theme/useTheme';
import { colors } from '../../design-system/tokens/colors';
import { authState } from '../../commerce/authState';

export interface EmailVerificationSentScreenProps {
  onBack?: () => void;
  onContinueToNewPassword?: () => void;
}

export const EmailVerificationSentScreen: React.FC<EmailVerificationSentScreenProps> = ({
  onBack,
  onContinueToNewPassword,
}) => {
  const { isRTL, language } = useTheme();
  const [resent, setResent] = useState(false);
  const email = authState.getRecoveryIdentifier() || 'youssef@example.ma';

  const handleResend = () => {
    setResent(true);
  };

  const copy =
    language === 'ar'
      ? {
          title: 'تم إرسال البريد الإلكتروني!',
          subtitle: `تم إرسال رابط إعادة تعيين كلمة السر إلى ${email}.`,
          instruction: 'تحقق من صندوق الوارد الخاص بك واتبع التعليمات لإنشاء كلمة سر جديدة.',
          continueCTA: 'متابعة وإنشاء كلمة سر جديدة',
          resendCTA: resent ? 'تم إعادة الإرسال بنجاح ✓' : 'لم تستلم البريد؟ إعادة الإرسال',
          back: 'العودة',
        }
      : {
          title: 'Email envoyé !',
          subtitle: `Un lien de réinitialisation a été envoyé à ${email}.`,
          instruction:
            'Vérifiez votre boîte de réception et cliquez sur le lien pour définir votre nouveau mot de passe.',
          continueCTA: 'Saisir le nouveau mot de passe',
          resendCTA: resent ? 'Email renvoyé avec succès ✓' : 'Vous n’avez rien reçu ? Renvoyer',
          back: 'Retour à la connexion',
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
            <MayushIcon name="check" size={32} color={colors.semantic.success} />
          </View>
        </View>

        <MayushText variant="display" color={colors.brand.navy900} align="center" style={styles.title}>
          {copy.title}
        </MayushText>
        <MayushText variant="body" color={colors.brand.navy900} align="center" style={styles.emailText}>
          {copy.subtitle}
        </MayushText>
        <MayushText variant="caption" color={colors.neutral.gray700} align="center" style={styles.instruction}>
          {copy.instruction}
        </MayushText>

        <View style={styles.card}>
          <PrimaryButton label={copy.continueCTA} onPress={onContinueToNewPassword || (() => undefined)} />

          <TouchableOpacity onPress={handleResend} style={styles.resendButton} accessibilityRole="button">
            <MayushText variant="caption" color={colors.brand.orange500} align="center" style={styles.boldText}>
              {copy.resendCTA}
            </MayushText>
          </TouchableOpacity>

          <TouchableOpacity onPress={onBack} style={styles.backLink} accessibilityRole="button">
            <MayushText variant="button" color={colors.brand.navy900} align="center">
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
    backgroundColor: '#E8F5E9',
    alignItems: 'center',
    justifyContent: 'center',
    borderWidth: 1,
    borderColor: '#C8E6C9',
  },
  title: { marginTop: 18, fontSize: 26, lineHeight: 32 },
  emailText: { marginTop: 10, marginHorizontal: 8, fontSize: 16, fontWeight: '600', lineHeight: 24 },
  instruction: { marginTop: 8, marginHorizontal: 12, lineHeight: 20 },
  card: {
    marginTop: 28,
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
  resendButton: { marginTop: 16, paddingVertical: 8 },
  boldText: { fontWeight: '600' },
  backLink: { marginTop: 12, paddingVertical: 8 },
});
