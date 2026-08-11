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

export interface RegistrationScreenProps {
  onNextToConsent: () => void;
  onSignInClick: () => void;
  onBack: () => void;
}

export const RegistrationScreen: React.FC<RegistrationScreenProps> = ({
  onNextToConsent,
  onSignInClick,
  onBack,
}) => {
  const existingDraft = authState.getRegistrationDraft();
  const [fullName, setFullName] = useState(existingDraft.fullName || '');
  const [emailOrPhone, setEmailOrPhone] = useState(existingDraft.emailOrPhone || '');
  const [password, setPassword] = useState(existingDraft.password || '');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [errorMessage, setErrorMessage] = useState('');

  // Password requirements calculation
  const hasMinLength = password.length >= 8;
  const hasLetter = /[a-zA-Z]/.test(password);
  const hasNumber = /[0-9]/.test(password);
  const isPasswordValid = hasMinLength && hasLetter && hasNumber;

  const validatePhoneOrEmail = (input: string) => {
    const trimmed = input.trim();
    if (trimmed.includes('@')) {
      return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(trimmed);
    }
    // Moroccan phone format check (+212 6/7... or 06/07...)
    const phoneClean = trimmed.replace(/[\s\-\(\)]/g, '');
    return /^(\+212|0)[67]\d{8}$/.test(phoneClean);
  };

  const handleNext = () => {
    if (!fullName.trim()) {
      setErrorMessage('Veuillez entrer votre nom complet.');
      return;
    }
    if (!emailOrPhone.trim() || !validatePhoneOrEmail(emailOrPhone)) {
      setErrorMessage('Veuillez saisir un email valide ou un numéro marocain (+212 6/7).');
      return;
    }
    if (!isPasswordValid) {
      setErrorMessage('Le mot de passe ne respecte pas les exigences de sécurité.');
      return;
    }
    if (password !== confirmPassword) {
      setErrorMessage('Les mots de passe ne correspondent pas.');
      return;
    }

    setErrorMessage('');
    authState.updateRegistrationDraft({
      fullName: fullName.trim(),
      emailOrPhone: emailOrPhone.trim(),
      password,
    });
    onNextToConsent();
  };

  return (
    <KeyboardAvoidingView
      style={styles.container}
      behavior={Platform.OS === 'ios' ? 'padding' : undefined}
      accessibilityLabel="Formulaire d'inscription"
    >
      <ScrollView
        contentContainerStyle={styles.scrollContent}
        keyboardShouldPersistTaps="handled"
        showsVerticalScrollIndicator={false}
      >
        <View style={styles.headerRow}>
          <TouchableOpacity
            onPress={onBack}
            style={styles.backBtn}
            accessibilityRole="button"
            accessibilityLabel="Retour"
          >
            <MayushIcon name="chevron-left" size={24} color={colors.brand.navy900} />
          </TouchableOpacity>
          <MayushLogo width={160} height={48} />
          <View style={styles.spacer} />
        </View>

        <View style={styles.titleSection}>
          <MayushText variant="pageTitle" color={colors.brand.navy900} style={styles.title}>
            Créer un compte
          </MayushText>
          <MayushText variant="caption" color={colors.neutral.gray700} style={styles.subtitle}>
            Rejoignez la communauté Mayush Design pour réserver vos collections d'exception.
          </MayushText>
        </View>

        {errorMessage ? (
          <View style={styles.errorBox} accessibilityLabel="Message d'erreur d'inscription">
            <MayushIcon name="alert-circle" size={20} color={colors.semantic.error} />
            <MayushText variant="caption" color={colors.semantic.error} style={styles.errorText}>
              {errorMessage}
            </MayushText>
          </View>
        ) : null}

        <View style={styles.formCard}>
          <TextField
            label="Nom et prénom"
            placeholder="Ex: Karim Benjelloun"
            value={fullName}
            onChangeText={(txt) => {
              setFullName(txt);
              if (errorMessage) setErrorMessage('');
            }}
            leftIcon="user"
          />

          <TextField
            label="Email ou téléphone mobile (+212)"
            placeholder="+212 661-234567 ou karim@example.ma"
            value={emailOrPhone}
            onChangeText={(txt) => {
              setEmailOrPhone(txt);
              if (errorMessage) setErrorMessage('');
            }}
            keyboardType="email-address"
            autoCapitalize="none"
            leftIcon="phone"
            containerStyle={styles.fieldSpacing}
            helperText="Format marocain +212 (06/07) ou email valide."
          />

          <TextField
            label="Mot de passe"
            placeholder="Créer un mot de passe sécurisé"
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

          <View style={styles.rulesContainer}>
            <View style={styles.ruleRow}>
              <MayushIcon
                name={hasMinLength ? 'check' : 'circle'}
                size={14}
                color={hasMinLength ? colors.semantic.success : colors.neutral.gray700}
              />
              <MayushText
                variant="caption"
                color={hasMinLength ? colors.semantic.success : colors.neutral.gray700}
              >
                Au moins 8 caractères
              </MayushText>
            </View>
            <View style={styles.ruleRow}>
              <MayushIcon
                name={hasLetter ? 'check' : 'circle'}
                size={14}
                color={hasLetter ? colors.semantic.success : colors.neutral.gray700}
              />
              <MayushText
                variant="caption"
                color={hasLetter ? colors.semantic.success : colors.neutral.gray700}
              >
                Contient des lettres
              </MayushText>
            </View>
            <View style={styles.ruleRow}>
              <MayushIcon
                name={hasNumber ? 'check' : 'circle'}
                size={14}
                color={hasNumber ? colors.semantic.success : colors.neutral.gray700}
              />
              <MayushText
                variant="caption"
                color={hasNumber ? colors.semantic.success : colors.neutral.gray700}
              >
                Contient des chiffres
              </MayushText>
            </View>
          </View>

          <TextField
            label="Confirmer le mot de passe"
            placeholder="Répétez votre mot de passe"
            value={confirmPassword}
            onChangeText={(txt) => {
              setConfirmPassword(txt);
              if (errorMessage) setErrorMessage('');
            }}
            secureTextEntry={!showPassword}
            leftIcon="lock"
            containerStyle={styles.fieldSpacing}
          />

          <PrimaryButton
            label="Continuer (Conditions & Confidentialité)"
            onPress={handleNext}
            style={styles.submitBtn}
          />
        </View>

        <View style={styles.footerSection}>
          <MayushText variant="caption" color={colors.neutral.gray700}>
            Vous avez déjà un compte ?
          </MayushText>
          <TouchableOpacity onPress={onSignInClick} style={styles.signInBtn} accessibilityRole="button">
            <MayushText variant="button" color={colors.brand.orange500}>
              Se connecter
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
  backBtn: {
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: colors.surface.white,
    alignItems: 'center',
    justifyContent: 'center',
  },
  spacer: {
    width: 40,
  },
  titleSection: {
    marginBottom: 20,
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
  formCard: {
    backgroundColor: colors.surface.white,
    borderRadius: 20,
    padding: 20,
    shadowColor: colors.brand.navy900,
    shadowOpacity: 0.08,
    shadowRadius: 12,
    elevation: 3,
  },
  fieldSpacing: {
    marginTop: 14,
  },
  rulesContainer: {
    marginTop: 8,
    marginBottom: 14,
    gap: 4,
  },
  ruleRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
  },
  submitBtn: {
    marginTop: 20,
  },
  footerSection: {
    marginTop: 28,
    alignItems: 'center',
    gap: 6,
  },
  signInBtn: {
    paddingVertical: 6,
  },
});
