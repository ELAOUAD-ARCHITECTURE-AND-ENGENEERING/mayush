import React, { useState } from 'react';
import { ScrollView, StyleSheet, TouchableOpacity, View } from 'react-native';
import { authState } from '../../commerce/authState';
import { PrimaryButton } from '../../design-system/components/actions/PrimaryButton';
import { TextField } from '../../design-system/components/forms/TextField';
import { BottomTabBar, TabKey } from '../../design-system/components/navigation/BottomTabBar';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { useTheme } from '../../design-system/theme/useTheme';
import { colors } from '../../design-system/tokens/colors';

export interface ChangeEmailScreenProps {
  onNavigateTab?: (tab: TabKey) => void;
  onBack?: () => void;
  onSuccess?: () => void;
}

export const ChangeEmailScreen: React.FC<ChangeEmailScreenProps> = ({
  onNavigateTab,
  onBack,
  onSuccess,
}) => {
  const { isRTL, language } = useTheme();
  const currentEmail = authState.getUser()?.email || authState.getUser()?.emailOrPhone || 'karim.benjelloun@example.ma';

  const [newEmail, setNewEmail] = useState('');
  const [confirmEmail, setConfirmEmail] = useState('');
  const [error, setError] = useState('');

  const isValidEmail = (emailStr: string) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailStr.trim());

  const handleSubmit = () => {
    if (!newEmail.trim() || !isValidEmail(newEmail)) {
      setError(language === 'ar' ? 'يرجى إدخال بريد إلكتروني صحيح' : 'Veuillez saisir une adresse email valide.');
      return;
    }
    if (newEmail.trim().toLowerCase() !== confirmEmail.trim().toLowerCase()) {
      setError(language === 'ar' ? 'البريد الإلكتروني غير متطابق' : 'Les adresses e-mail ne correspondent pas.');
      return;
    }
    setError('');
    authState.changeEmail(newEmail.trim().toLowerCase());
    onSuccess ? onSuccess() : onBack?.();
  };

  return (
    <View style={styles.screen} accessibilityLabel="Changer l'adresse email">
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
            {language === 'ar' ? 'تغيير البريد الإلكتروني' : 'Changer d’e-mail'}
          </MayushText>
          <View style={styles.headerSpacer} />
        </View>

        {/* Info Card */}
        <View style={styles.card}>
          <MayushText variant="caption" color={colors.neutral.gray500} style={isRTL && styles.rtlText}>
            {language === 'ar' ? 'البريد الإلكتروني الحالي' : 'Adresse e-mail actuelle'}
          </MayushText>
          <MayushText variant="body" color={colors.brand.navy900} style={[styles.currentEmail, isRTL && styles.rtlText]}>
            {currentEmail}
          </MayushText>

          <View style={styles.divider} />

          <TextField
            label={language === 'ar' ? 'البريد الإلكتروني الجديد' : 'Nouvelle adresse e-mail'}
            value={newEmail}
            onChangeText={(txt) => {
              setNewEmail(txt);
              if (error) setError('');
            }}
            placeholder="nouveau.email@example.ma"
            leftIcon="user"
            autoCapitalize="none"
            keyboardType="email-address"
            error={error}
            containerStyle={styles.fieldSpacing}
          />

          <TextField
            label={language === 'ar' ? 'تأكيد البريد الإلكتروني' : 'Confirmer la nouvelle adresse'}
            value={confirmEmail}
            onChangeText={(txt) => {
              setConfirmEmail(txt);
              if (error) setError('');
            }}
            placeholder="nouveau.email@example.ma"
            leftIcon="user"
            autoCapitalize="none"
            keyboardType="email-address"
            containerStyle={styles.fieldSpacing}
          />
        </View>

        <PrimaryButton
          label={language === 'ar' ? 'تأكيد التغيير' : 'Confirmer la nouvelle adresse'}
          onPress={handleSubmit}
          style={styles.submitButton}
        />
      </ScrollView>
      <BottomTabBar activeTab="account" onTabPress={(tab) => onNavigateTab?.(tab)} />
    </View>
  );
};

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
  currentEmail: { fontWeight: '700', marginTop: 4, marginBottom: 16 },
  divider: { height: 1, backgroundColor: '#F0E8DF', marginBottom: 16 },
  fieldSpacing: { marginBottom: 16 },
  submitButton: { marginTop: 24, minHeight: 52 },
});
