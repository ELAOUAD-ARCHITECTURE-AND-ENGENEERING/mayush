import React, { useState } from 'react';
import { ScrollView, StyleSheet, TouchableOpacity, View } from 'react-native';
import { PrimaryButton } from '../../design-system/components/actions/PrimaryButton';
import { TextField } from '../../design-system/components/forms/TextField';
import { BottomTabBar, TabKey } from '../../design-system/components/navigation/BottomTabBar';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { useTheme } from '../../design-system/theme/useTheme';
import { colors } from '../../design-system/tokens/colors';

export interface ChangePasswordFormScreenProps {
  onNavigateTab?: (tab: TabKey) => void;
  onBack?: () => void;
  onSuccess?: () => void;
}

export const ChangePasswordFormScreen: React.FC<ChangePasswordFormScreenProps> = ({
  onNavigateTab,
  onBack,
  onSuccess,
}) => {
  const { isRTL, language } = useTheme();

  const [currentPassword, setCurrentPassword] = useState('');
  const [newPassword, setNewPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');

  const [showCurrent, setShowCurrent] = useState(false);
  const [showNew, setShowNew] = useState(false);
  const [showConfirm, setShowConfirm] = useState(false);

  const [error, setError] = useState('');

  const hasLength = newPassword.length >= 8;
  const hasLetter = /[a-zA-Z]/.test(newPassword);
  const hasNumber = /[0-9]/.test(newPassword);
  const isMatch = newPassword.length > 0 && newPassword === confirmPassword;

  const handleSubmit = () => {
    if (!currentPassword) {
      setError(language === 'ar' ? 'يرجى إدخال كلمة المرور الحالية' : 'Veuillez saisir votre mot de passe actuel.');
      return;
    }
    if (!hasLength || !hasLetter || !hasNumber) {
      setError(
        language === 'ar'
          ? 'كلمة المرور يجب أن تحوي 8 أحرف على الأقل، حرفًا ورقمًا'
          : 'Le mot de passe doit respecter toutes les exigences de sécurité.'
      );
      return;
    }
    if (!isMatch) {
      setError(language === 'ar' ? 'كلمات المرور غير متطابقة' : 'Les mots de passe ne correspondent pas.');
      return;
    }

    setError('');
    onSuccess ? onSuccess() : onBack?.();
  };

  return (
    <View style={styles.screen} accessibilityLabel="Changer le mot de passe">
      <ScrollView showsVerticalScrollIndicator={false} contentContainerStyle={styles.scrollContent}>
        {/* Header */}
        <View style={[styles.header, isRTL && styles.rowReverse]}>
          <TouchableOpacity
            accessibilityRole="button"
            accessibilityLabel="Retour"
            onPress={onBack}
            style={styles.backButton}
          >
            <MayushIcon name={isRTL ? 'chevron-right' : 'chevron-left'} size={24} color={colors.brand.navy900} />
          </TouchableOpacity>
          <MayushText variant="sectionTitle" color={colors.brand.navy900}>
            {language === 'ar' ? 'تغيير كلمة المرور' : 'Changer le mot de passe'}
          </MayushText>
          <View style={styles.headerSpacer} />
        </View>

        <View style={styles.card}>
          <TextField
            label={language === 'ar' ? 'كلمة المرور الحالية' : 'Mot de passe actuel'}
            value={currentPassword}
            onChangeText={(txt) => {
              setCurrentPassword(txt);
              if (error) setError('');
            }}
            secureTextEntry={!showCurrent}
            leftIcon="lock"
            rightIcon={showCurrent ? 'eye-off' : 'eye'}
            onRightIconPress={() => setShowCurrent(!showCurrent)}
            containerStyle={styles.fieldSpacing}
          />

          <View style={styles.divider} />

          <TextField
            label={language === 'ar' ? 'كلمة المرور الجديدة' : 'Nouveau mot de passe'}
            value={newPassword}
            onChangeText={(txt) => {
              setNewPassword(txt);
              if (error) setError('');
            }}
            secureTextEntry={!showNew}
            leftIcon="lock"
            rightIcon={showNew ? 'eye-off' : 'eye'}
            onRightIconPress={() => setShowNew(!showNew)}
            containerStyle={styles.fieldSpacing}
          />

          <TextField
            label={language === 'ar' ? 'تأكيد كلمة المرور الجديدة' : 'Confirmer le nouveau mot de passe'}
            value={confirmPassword}
            onChangeText={(txt) => {
              setConfirmPassword(txt);
              if (error) setError('');
            }}
            secureTextEntry={!showConfirm}
            leftIcon="lock"
            rightIcon={showConfirm ? 'eye-off' : 'eye'}
            onRightIconPress={() => setShowConfirm(!showConfirm)}
            containerStyle={styles.fieldSpacing}
          />

          {error ? (
            <MayushText variant="caption" color="#D9381E" style={styles.errorText}>
              {error}
            </MayushText>
          ) : null}

          {/* Requirements List */}
          <View style={styles.rulesCard}>
            <RuleRow active={hasLength} text={language === 'ar' ? '8 أحرف على الأقل' : 'Au moins 8 caractères'} isRTL={isRTL} />
            <RuleRow active={hasLetter} text={language === 'ar' ? 'حرف واحد على الأقل' : 'Au moins 1 lettre (a-z)'} isRTL={isRTL} />
            <RuleRow active={hasNumber} text={language === 'ar' ? 'رقم واحد على الأقل' : 'Au moins 1 chiffre (0-9)'} isRTL={isRTL} />
            <RuleRow active={isMatch} text={language === 'ar' ? 'مطابقة كلمة المرور' : 'Confirmation identique'} isRTL={isRTL} />
          </View>
        </View>

        <PrimaryButton
          label={language === 'ar' ? 'تحديث كلمة المرور' : 'Enregistrer le nouveau mot de passe'}
          onPress={handleSubmit}
          style={styles.submitButton}
        />
      </ScrollView>
      <BottomTabBar activeTab="account" onTabPress={(tab) => onNavigateTab?.(tab)} />
    </View>
  );
};

const RuleRow: React.FC<{ active: boolean; text: string; isRTL: boolean }> = ({ active, text, isRTL }) => (
  <View style={[styles.ruleRow, isRTL && styles.rowReverse]}>
    <MayushIcon
      name={active ? 'check' : 'user'}
      size={16}
      color={active ? '#2E7D32' : colors.neutral.gray500}
    />
    <MayushText
      variant="caption"
      color={active ? '#2E7D32' : colors.neutral.gray700}
      style={[styles.ruleText, isRTL && styles.rtlText]}
    >
      {text}
    </MayushText>
  </View>
);

const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: '#FBF2EF' },
  scrollContent: { paddingHorizontal: 22, paddingBottom: 28, paddingTop: 12 },
  rowReverse: { flexDirection: 'row-reverse' },
  rtlText: { writingDirection: 'rtl' },
  header: { height: 56, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' },
  backButton: { width: 40, height: 40, alignItems: 'center', justifyContent: 'center' },
  headerSpacer: { width: 40 },
  card: {
    marginTop: 20,
    borderRadius: 20,
    backgroundColor: colors.surface.white,
    borderWidth: 1,
    borderColor: '#E7DED3',
    padding: 20,
  },
  fieldSpacing: { marginBottom: 16 },
  divider: { height: 1, backgroundColor: '#F0E8DF', marginBottom: 16 },
  errorText: { marginBottom: 12, fontWeight: '600' },
  rulesCard: {
    padding: 12,
    borderRadius: 12,
    backgroundColor: colors.surface.cream,
    gap: 8,
  },
  ruleRow: { flexDirection: 'row', alignItems: 'center', gap: 8 },
  ruleText: { fontSize: 13 },
  submitButton: { marginTop: 24, minHeight: 52 },
});
