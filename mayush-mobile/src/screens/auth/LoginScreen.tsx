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

export interface LoginScreenProps {
  onLoginSubmit: (emailOrPhone: string, pass: string) => void;
  onForgotPassword: () => void;
  onCreateAccount: () => void;
  onBack: () => void;
}

export const LoginScreen: React.FC<LoginScreenProps> = ({
  onLoginSubmit,
  onForgotPassword,
  onCreateAccount,
  onBack,
}) => {
  const [emailOrPhone, setEmailOrPhone] = useState('');
  const [password, setPassword] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [rememberMe, setRememberMe] = useState(true);
  const [errorMessage, setErrorMessage] = useState('');

  const handleLogin = () => {
    if (!emailOrPhone.trim()) {
      setErrorMessage('Veuillez saisir votre email ou téléphone.');
      return;
    }
    if (!password) {
      setErrorMessage('Veuillez saisir votre mot de passe.');
      return;
    }
    setErrorMessage('');
    onLoginSubmit(emailOrPhone.trim(), password);
  };

  return (
    <KeyboardAvoidingView
      style={styles.container}
      behavior={Platform.OS === 'ios' ? 'padding' : undefined}
      accessibilityLabel="Ecran de connexion"
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
            accessibilityLabel="Retour"
          >
            <MayushIcon name="chevron-left" size={24} color={colors.brand.navy900} />
          </TouchableOpacity>
          <MayushLogo width={160} height={48} />
          <View style={styles.headerSpacer} />
        </View>

        <View style={styles.titleSection}>
          <MayushText variant="pageTitle" color={colors.brand.navy900} style={styles.title}>
            Connexion
          </MayushText>
          <MayushText variant="caption" color={colors.neutral.gray700} style={styles.subtitle}>
            Accédez à votre espace client Mayush Design avec votre adresse email ou votre numéro de téléphone marocain (+212).
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
            label="Email ou téléphone (+212)"
            placeholder="exemple@mayush.ma ou +212 661-234567"
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
            label="Mot de passe"
            placeholder="Votre mot de passe"
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
                Se souvenir de moi
              </MayushText>
            </TouchableOpacity>

            <TouchableOpacity
              onPress={onForgotPassword}
              accessibilityRole="button"
              accessibilityLabel="Mot de passe oublié"
            >
              <MayushText variant="caption" color={colors.brand.orange500} style={styles.forgotText}>
                Mot de passe oublié ?
              </MayushText>
            </TouchableOpacity>
          </View>

          <PrimaryButton
            label="Se connecter"
            onPress={handleLogin}
            style={styles.submitBtn}
          />
        </View>

        <View style={styles.footerSection}>
          <MayushText variant="caption" color={colors.neutral.gray700}>
            Vous n'avez pas encore de compte ?
          </MayushText>
          <TouchableOpacity
            onPress={onCreateAccount}
            style={styles.createAccountBtn}
            accessibilityRole="button"
          >
            <MayushText variant="button" color={colors.brand.orange500}>
              Créer un compte
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
