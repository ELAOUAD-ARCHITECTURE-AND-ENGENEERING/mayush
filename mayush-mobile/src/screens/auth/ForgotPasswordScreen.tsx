import React, { useState } from 'react';
import {
  KeyboardAvoidingView,
  Platform,
  ScrollView,
  StyleSheet,
  TouchableOpacity,
  View,
} from 'react-native';
import { MayushLogo } from '../../design-system/components/brand/MayushLogo';
import { PrimaryButton } from '../../design-system/components/actions/PrimaryButton';
import { TextField } from '../../design-system/components/forms/TextField';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { useTheme } from '../../design-system/theme/useTheme';
import { colors } from '../../design-system/tokens/colors';
import { authState } from '../../commerce/authState';

export interface ForgotPasswordScreenProps {
  onBack?: () => void;
  onSubmitSuccess?: (email: string) => void;
}

export const ForgotPasswordScreen: React.FC<ForgotPasswordScreenProps> = ({
  onBack,
  onSubmitSuccess,
}) => {
  const { isRTL, language } = useTheme();
  const [email, setEmail] = useState('youssef@example.ma');
  const [error, setError] = useState<string | null>(null);

  const isValidEmail = (val: string) => /\S+@\S+\.\S+/.test(val);

  const handleSubmit = () => {
    if (!email.trim() || !isValidEmail(email.trim())) {
      setError(
        language === 'ar'
          ? 'يرجى إدخال البريد الإلكتروني بشكل صحيح'
          : 'Veuillez saisir une adresse email valide.'
      );
      return;
    }
    setError(null);
    authState.startPasswordRecovery(email.trim(), 'email');
    onSubmitSuccess?.(email.trim());
  };

  const copy =
    language === 'ar'
      ? {
          title: 'نسيت كلمة السر؟',
          subtitle: 'أدخل بريدك الإلكتروني لتلقي رابط إعادة تعيين كلمة السر.',
          emailLabel: 'البريد الإلكتروني',
          placeholder: 'exemple@domaine.ma',
          submit: 'إرسال رابط إعادة التعيين',
          backToLogin: 'العودة إلى تسجيل الدخول',
        }
      : {
          title: 'Mot de passe oublié ?',
          subtitle:
            'Saisissez votre adresse email pour recevoir un lien de réinitialisation sécurisé.',
          emailLabel: 'Adresse email',
          placeholder: 'nom@domaine.ma',
          submit: 'Envoyer le lien de réinitialisation',
          backToLogin: 'Retour à la connexion',
        };

  return (
    <KeyboardAvoidingView
      style={styles.screen}
      behavior={Platform.OS === 'ios' ? 'padding' : undefined}
      accessibilityLabel={copy.title}
    >
      <ScrollView
        contentContainerStyle={styles.scrollContent}
        keyboardShouldPersistTaps="handled"
        showsVerticalScrollIndicator={false}
      >
        <View style={[styles.header, isRTL && styles.rowReverse]}>
          <TouchableOpacity
            onPress={onBack}
            style={styles.backButton}
            accessibilityRole="button"
            accessibilityLabel="Retour"
          >
            <MayushIcon name={isRTL ? 'chevron-right' : 'chevron-left'} size={24} color={colors.brand.navy900} />
          </TouchableOpacity>
          <MayushLogo width={150} height={45} />
          <View style={styles.headerSpacer} />
        </View>

        <View style={styles.badgeContainer}>
          <View style={styles.badgeCircle}>
            <MayushIcon name="lock" size={28} color={colors.brand.orange500} />
          </View>
        </View>

        <MayushText variant="display" color={colors.brand.navy900} align="center" style={styles.title}>
          {copy.title}
        </MayushText>
        <MayushText variant="body" color={colors.neutral.gray700} align="center" style={styles.subtitle}>
          {copy.subtitle}
        </MayushText>

        <View style={styles.formCard}>
          <TextField
            label={copy.emailLabel}
            placeholder={copy.placeholder}
            value={email}
            onChangeText={(text) => {
              setEmail(text);
              if (error) setError(null);
            }}
            keyboardType="email-address"
            autoCapitalize="none"
            error={error || undefined}
          />

          <View style={styles.buttonSpacing} />

          <PrimaryButton label={copy.submit} onPress={handleSubmit} />

          <TouchableOpacity onPress={onBack} style={styles.backLink} accessibilityRole="button">
            <MayushText variant="button" color={colors.brand.navy900} align="center">
              {copy.backToLogin}
            </MayushText>
          </TouchableOpacity>
        </View>
      </ScrollView>
    </KeyboardAvoidingView>
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
    backgroundColor: '#FFF3E6',
    alignItems: 'center',
    justifyContent: 'center',
    borderWidth: 1,
    borderColor: '#FFE0B2',
  },
  title: { marginTop: 18, fontSize: 26, lineHeight: 32 },
  subtitle: { marginTop: 10, marginHorizontal: 12, lineHeight: 22 },
  formCard: {
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
  buttonSpacing: { height: 20 },
  backLink: { marginTop: 18, paddingVertical: 8 },
});
