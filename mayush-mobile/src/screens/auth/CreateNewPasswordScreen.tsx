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

export interface CreateNewPasswordScreenProps {
  onBack?: () => void;
  onSuccess?: () => void;
}

export const CreateNewPasswordScreen: React.FC<CreateNewPasswordScreenProps> = ({
  onBack,
  onSuccess,
}) => {
  const { isRTL, language } = useTheme();
  const [password, setPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [showConfirmPassword, setShowConfirmPassword] = useState(false);
  const [errorMessage, setErrorMessage] = useState<string | null>(null);

  const hasLength = password.length >= 8;
  const hasLetter = /[a-zA-Z]/.test(password);
  const hasNumber = /[0-9]/.test(password);
  const isMatch = password.length > 0 && password === confirmPassword;

  const handleSubmit = () => {
    if (!hasLength || !hasLetter || !hasNumber) {
      setErrorMessage(
        language === 'ar'
          ? 'يرجى احترام شروط كلمة السر'
          : 'Le mot de passe ne respecte pas les critères de sécurité.'
      );
      return;
    }

    if (!isMatch) {
      setErrorMessage(
        language === 'ar'
          ? 'كلمات السر غير متطابقة'
          : 'Les mots de passe ne correspondent pas.'
      );
      return;
    }

    setErrorMessage(null);
    authState.updateNewPasswordDraft({ password, confirmPassword });
    authState.completePasswordReset();
    onSuccess?.();
  };

  const copy =
    language === 'ar'
      ? {
          title: 'إنشاء كلمة سر جديدة',
          subtitle: 'حدد كلمة سر آمنة لحماية حسابك.',
          newPasswordLabel: 'كلمة السر الجديدة',
          confirmPasswordLabel: 'تأكيد كلمة السر الجديدة',
          placeholder: '••••••••',
          reqTitle: 'متطلبات كلمة السر:',
          reqLength: '8 أحرف على الأقل',
          reqLetter: 'يحتوي على حروف',
          reqNumber: 'يحتوي على أرقام',
          reqMatch: 'كلمات السر متطابقة',
          submit: 'حفظ كلمة السر',
          back: 'العودة',
        }
      : {
          title: 'Créer un nouveau mot de passe',
          subtitle: 'Définissez un mot de passe sécurisé pour protéger votre compte.',
          newPasswordLabel: 'Nouveau mot de passe',
          confirmPasswordLabel: 'Confirmer le nouveau mot de passe',
          placeholder: '••••••••',
          reqTitle: 'Exigences du mot de passe :',
          reqLength: 'Au moins 8 caractères',
          reqLetter: 'Contient des lettres',
          reqNumber: 'Contient des chiffres',
          reqMatch: 'Les mots de passe correspondent',
          submit: 'Enregistrer le nouveau mot de passe',
          back: 'Retour à la connexion',
        };

  return (
    <KeyboardAvoidingView
      style={styles.screen}
      behavior={Platform.OS === 'ios' ? 'padding' : undefined}
      accessibilityLabel={copy.title}
    >
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
            <MayushIcon name="lock" size={28} color={colors.brand.orange500} />
          </View>
        </View>

        <MayushText variant="display" color={colors.brand.navy900} align="center" style={styles.title}>
          {copy.title}
        </MayushText>
        <MayushText variant="caption" color={colors.neutral.gray700} align="center" style={styles.subtitle}>
          {copy.subtitle}
        </MayushText>

        <View style={styles.card}>
          <TextField
            label={copy.newPasswordLabel}
            placeholder={copy.placeholder}
            value={password}
            onChangeText={(text) => {
              setPassword(text);
              if (errorMessage) setErrorMessage(null);
            }}
            secureTextEntry={!showPassword}
            rightIcon={showPassword ? 'eye-off' : 'eye'}
            onRightIconPress={() => setShowPassword(!showPassword)}
          />

          <View style={styles.inputSpacing} />

          <TextField
            label={copy.confirmPasswordLabel}
            placeholder={copy.placeholder}
            value={confirmPassword}
            onChangeText={(text) => {
              setConfirmPassword(text);
              if (errorMessage) setErrorMessage(null);
            }}
            secureTextEntry={!showConfirmPassword}
            error={errorMessage || undefined}
            rightIcon={showConfirmPassword ? 'eye-off' : 'eye'}
            onRightIconPress={() => setShowConfirmPassword(!showConfirmPassword)}
          />

          <View style={styles.requirementsBox}>
            <MayushText variant="caption" color={colors.brand.navy900} style={styles.reqTitle}>
              {copy.reqTitle}
            </MayushText>
            <RequirementItem label={copy.reqLength} fulfilled={hasLength} isRTL={isRTL} />
            <RequirementItem label={copy.reqLetter} fulfilled={hasLetter} isRTL={isRTL} />
            <RequirementItem label={copy.reqNumber} fulfilled={hasNumber} isRTL={isRTL} />
            {confirmPassword.length > 0 ? (
              <RequirementItem label={copy.reqMatch} fulfilled={isMatch} isRTL={isRTL} />
            ) : null}
          </View>

          <View style={styles.buttonSpacing} />

          <PrimaryButton label={copy.submit} onPress={handleSubmit} />

          <TouchableOpacity onPress={onBack} style={styles.backLink} accessibilityRole="button">
            <MayushText variant="button" color={colors.brand.navy900} align="center">
              {copy.back}
            </MayushText>
          </TouchableOpacity>
        </View>
      </ScrollView>
    </KeyboardAvoidingView>
  );
};

const RequirementItem: React.FC<{ label: string; fulfilled: boolean; isRTL: boolean }> = ({
  label,
  fulfilled,
  isRTL,
}) => (
  <View style={[styles.reqRow, isRTL && styles.rowReverse]}>
    <MayushIcon
      name={fulfilled ? 'check' : 'circle'}
      size={16}
      color={fulfilled ? colors.semantic.success : colors.neutral.gray700}
    />
    <MayushText
      variant="caption"
      color={fulfilled ? colors.semantic.success : colors.neutral.gray700}
      style={fulfilled ? styles.boldText : undefined}
    >
      {label}
    </MayushText>
  </View>
);

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
  badgeContainer: { alignItems: 'center', marginTop: 18 },
  badgeCircle: {
    width: 60,
    height: 60,
    borderRadius: 30,
    backgroundColor: '#FFF3E6',
    alignItems: 'center',
    justifyContent: 'center',
    borderWidth: 1,
    borderColor: '#FFE0B2',
  },
  title: { marginTop: 14, fontSize: 24, lineHeight: 30 },
  subtitle: { marginTop: 6, marginHorizontal: 12, lineHeight: 18 },
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
  inputSpacing: { height: 14 },
  requirementsBox: {
    marginTop: 16,
    padding: 12,
    borderRadius: 12,
    backgroundColor: '#FAF7F2',
    gap: 6,
  },
  reqTitle: { fontWeight: '700', marginBottom: 2 },
  reqRow: { flexDirection: 'row', alignItems: 'center', gap: 8 },
  boldText: { fontWeight: '600' },
  buttonSpacing: { height: 18 },
  backLink: { marginTop: 16, paddingVertical: 6 },
});
