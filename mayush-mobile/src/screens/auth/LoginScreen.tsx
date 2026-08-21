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
import { colors } from '../../design-system/tokens/colors';
import { authState } from '../../commerce/authState';
import { useTheme } from '../../design-system/theme/useTheme';

export interface LoginScreenProps {
  onLoginSubmit: (emailOrPhone: string, pass: string) => void;
  onForgotPassword: () => void;
  onCreateAccount: () => void;
  onBack: () => void;
  initialEmailOrPhone?: string;
  initialPassword?: string;
}

export const LoginScreen: React.FC<LoginScreenProps> = ({
  onLoginSubmit,
  onForgotPassword,
  onCreateAccount,
  onBack,
  initialEmailOrPhone = '',
  initialPassword = '',
}) => {
  const { isRTL } = useTheme();
  const heading = (fr: string, ar: string) => (isRTL ? ar : fr);

  const [emailOrPhone, setEmailOrPhone] = useState(initialEmailOrPhone);
  const [password, setPassword] = useState(initialPassword);
  const [showPassword, setShowPassword] = useState(false);
  const [rememberMe, setRememberMe] = useState(true);
  const [errorMessage, setErrorMessage] = useState('');

  const handleLogin = () => {
    if (!emailOrPhone.trim()) {
      setErrorMessage(heading('Veuillez saisir votre email ou téléphone.', 'يرجى إدخال بريدك الإلكتروني أو رقم هاتفك.'));
      return;
    }
    if (!password) {
      setErrorMessage(heading('Veuillez saisir votre mot de passe.', 'يرجى إدخال كلمة المرور.'));
      return;
    }
    setErrorMessage('');
    onLoginSubmit(emailOrPhone.trim(), password);
  };

  return (
    <KeyboardAvoidingView
      style={styles.container}
      behavior={Platform.OS === 'ios' ? 'padding' : undefined}
      accessibilityLabel={heading('Ecran de connexion', 'شاشة تسجيل الدخول')}
    >
      <ScrollView
        contentContainerStyle={styles.scrollContent}
        keyboardShouldPersistTaps="handled"
        showsVerticalScrollIndicator={false}
      >
        <View style={styles.headerRow}>
          <TouchableOpacity
            onPress={onBack}
            style={styles.backButton}
            accessibilityRole="button"
            accessibilityLabel={heading('Retour', 'رجوع')}
          >
            <MayushIcon name="chevron-left" size={24} color={colors.brand.navy900} />
          </TouchableOpacity>
          <MayushLogo width={160} height={48} />
          <View style={styles.headerSpacer} />
        </View>

        <View style={styles.titleSection}>
          <MayushText variant="pageTitle" color={colors.brand.navy900} style={styles.title}>
            {heading('Connexion', 'تسجيل الدخول')}
          </MayushText>
          <MayushText variant="caption" color={colors.neutral.gray700} style={styles.subtitle}>
            {heading(
              'Accédez à votre espace client Mayush Design avec votre adresse email ou votre numéro de téléphone marocain (+212).',
              'ادخل إلى حسابك في Mayush Design بعنوان بريدك الإلكتروني أو رقم هاتفك المغربي (+212).'
            )}
          </MayushText>
        </View>

        {errorMessage ? (
          <View style={styles.errorBox} accessibilityLabel="Message d'erreur">
            <MayushIcon name="alert-circle" size={20} color={colors.semantic.error} />
            <MayushText variant="caption" color={colors.semantic.error} style={styles.errorText}>
              {errorMessage}
            </MayushText>
          </View>
        ) : null}

        <View style={styles.formGroup}>
          <TextField
            label={heading('Email ou téléphone (+212)', 'البريد الإلكتروني أو الهاتف (+212)')}
            placeholder={heading('exemple@mayush.ma ou +212 661-234567', 'exemple@mayush.ma أو +212 661-234567')}
            value={emailOrPhone}
            onChangeText={(txt) => {
              setEmailOrPhone(txt);
              if (errorMessage) setErrorMessage('');
            }}
            keyboardType="email-address"
            autoCapitalize="none"
            leftIcon="user"
          />

          <TextField
            label={heading('Mot de passe', 'كلمة المرور')}
            placeholder={heading('Votre mot de passe', 'كلمة المرور الخاصة بك')}
            value={password}
            onChangeText={(txt) => {
              setPassword(txt);
              if (errorMessage) setErrorMessage('');
            }}
            secureTextEntry={!showPassword}
            leftIcon="lock"
            rightIcon={showPassword ? 'eye-off' : 'eye'}
            onRightIconPress={() => setShowPassword(!showPassword)}
            containerStyle={styles.fieldSpacing}
          />

          <View style={styles.optionsRow}>
            <TouchableOpacity
              style={styles.rememberBox}
              onPress={() => setRememberMe(!rememberMe)}
              accessibilityRole="checkbox"
              accessibilityState={{ checked: rememberMe }}
            >
              <View style={[styles.checkbox, rememberMe && styles.checkboxActive]}>
                {rememberMe ? (
                  <MayushIcon name="check" size={14} color={colors.surface.white} />
                ) : null}
              </View>
              <MayushText variant="caption" color={colors.brand.navy900}>
                {heading('Se souvenir de moi', 'تذكرني')}
              </MayushText>
            </TouchableOpacity>

            <TouchableOpacity
              onPress={onForgotPassword}
              accessibilityRole="button"
              accessibilityLabel={heading('Mot de passe oublié', 'نسيت كلمة المرور')}
            >
              <MayushText variant="caption" color={colors.brand.orange500} style={styles.forgotText}>
                {heading('Mot de passe oublié ?', 'نسيت كلمة المرور؟')}
              </MayushText>
            </TouchableOpacity>
          </View>

          <PrimaryButton
            label={heading('Se connecter', 'تسجيل الدخول')}
            onPress={handleLogin}
            style={styles.submitBtn}
          />
        </View>

        <View style={styles.footerSection}>
          <MayushText variant="caption" color={colors.neutral.gray700}>
            {heading('Vous n\'avez pas encore de compte ?', 'ليس لديك حساب؟')}
          </MayushText>
          <TouchableOpacity
            onPress={onCreateAccount}
            style={styles.createAccountBtn}
            accessibilityRole="button"
          >
            <MayushText variant="button" color={colors.brand.orange500}>
              {heading('Créer un compte', 'إنشاء حساب')}
            </MayushText>
          </TouchableOpacity>
        </View>
      </ScrollView>
    </KeyboardAvoidingView>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#FFF8F0',
  },
  scrollContent: {
    paddingHorizontal: 24,
    paddingTop: 48,
    paddingBottom: 40,
  },
  headerRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    marginBottom: 24,
  },
  backButton: {
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: colors.surface.white,
    alignItems: 'center',
    justifyContent: 'center',
    shadowColor: colors.brand.navy900,
    shadowOpacity: 0.08,
    shadowRadius: 6,
    elevation: 2,
  },
  headerSpacer: {
    width: 40,
  },
  titleSection: {
    marginBottom: 24,
  },
  title: {
    fontSize: 26,
    lineHeight: 32,
    marginBottom: 8,
  },
  subtitle: {
    lineHeight: 20,
  },
  errorBox: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    backgroundColor: '#FEE2E2',
    borderColor: '#FCA5A5',
    borderWidth: 1,
    borderRadius: 12,
    padding: 12,
    marginBottom: 16,
  },
  errorText: {
    flex: 1,
    lineHeight: 18,
  },
  formGroup: {
    backgroundColor: colors.surface.white,
    borderRadius: 20,
    padding: 20,
    shadowColor: colors.brand.navy900,
    shadowOpacity: 0.08,
    shadowRadius: 12,
    elevation: 3,
  },
  fieldSpacing: {
    marginTop: 16,
  },
  optionsRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    marginVertical: 18,
  },
  rememberBox: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
  },
  checkbox: {
    width: 20,
    height: 20,
    borderRadius: 6,
    borderWidth: 1.5,
    borderColor: colors.surface.borderWarm,
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: colors.surface.white,
  },
  checkboxActive: {
    backgroundColor: colors.brand.orange500,
    borderColor: colors.brand.orange500,
  },
  forgotText: {
    fontWeight: '600',
  },
  submitBtn: {
    marginTop: 6,
  },
  footerSection: {
    marginTop: 32,
    alignItems: 'center',
    gap: 8,
  },
  createAccountBtn: {
    paddingVertical: 8,
    paddingHorizontal: 16,
  },
});
