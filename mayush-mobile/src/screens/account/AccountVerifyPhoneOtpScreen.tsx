import React, { useRef, useState } from 'react';
import { ScrollView, StyleSheet, TextInput, TouchableOpacity, View } from 'react-native';
import { authState } from '../../commerce/authState';
import { PrimaryButton } from '../../design-system/components/actions/PrimaryButton';
import { BottomTabBar, TabKey } from '../../design-system/components/navigation/BottomTabBar';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { useTheme } from '../../design-system/theme/useTheme';
import { colors } from '../../design-system/tokens/colors';

export interface AccountVerifyPhoneOtpScreenProps {
  onNavigateTab?: (tab: TabKey) => void;
  onBack?: () => void;
  onSuccess?: () => void;
}

export const AccountVerifyPhoneOtpScreen: React.FC<AccountVerifyPhoneOtpScreenProps> = ({
  onNavigateTab,
  onBack,
  onSuccess,
}) => {
  const { isRTL, language } = useTheme();
  const newPhone = authState.getContactChangeDraft().newPhone || '+212 6 00 11 22 33';

  const [digits, setDigits] = useState<string[]>(['', '', '', '', '', '']);
  const [error, setError] = useState<string | null>(null);
  const inputsRef = useRef<(TextInput | null)[]>([]);

  const handleDigitChange = (text: string, index: number) => {
    const clean = text.replace(/[^0-9]/g, '');
    const newDigits = [...digits];
    newDigits[index] = clean.slice(-1);
    setDigits(newDigits);
    if (error) setError(null);

    if (clean && index < 5) {
      inputsRef.current[index + 1]?.focus();
    }
  };

  const handleKeyPress = (e: any, index: number) => {
    if (e.nativeEvent.key === 'Backspace' && !digits[index] && index > 0) {
      inputsRef.current[index - 1]?.focus();
    }
  };

  const handleVerify = () => {
    const code = digits.join('');
    if (code.length < 6) {
      setError(language === 'ar' ? 'يرجى إدخال الرمز المكون من 6 أرقام' : 'Veuillez saisir les 6 chiffres du code.');
      return;
    }
    if (code === '999999') {
      setError(language === 'ar' ? 'رمز OTP غير صحيح' : 'Code OTP incorrect. Veuillez réessayer.');
      return;
    }
    authState.changePhone(newPhone);
    onSuccess ? onSuccess() : onBack?.();
  };

  return (
    <View style={styles.screen} accessibilityLabel="Vérification OTP numéro de téléphone">
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
            {language === 'ar' ? 'التحقق من الهاتف' : 'Vérification du numéro'}
          </MayushText>
          <View style={styles.headerSpacer} />
        </View>

        <View style={styles.card}>
          <MayushText variant="body" color={colors.brand.navy900} align="center" style={isRTL && styles.rtlText}>
            {language === 'ar'
              ? `أدخل رمز التحقق (OTP) المرسل إلى ${newPhone}`
              : `Saisissez le code SMS (OTP) envoyé au ${newPhone}`}
          </MayushText>

          {/* 6-Digit Grid */}
          <View style={[styles.otpGrid, isRTL && styles.rowReverse]}>
            {digits.map((digit, i) => (
              <TextInput
                key={i}
                ref={(ref) => {
                  inputsRef.current[i] = ref;
                }}
                style={[styles.otpBox, digit ? styles.otpBoxFilled : null, error ? styles.otpBoxError : null]}
                value={digit}
                onChangeText={(val) => handleDigitChange(val, i)}
                onKeyPress={(e) => handleKeyPress(e, i)}
                keyboardType="number-pad"
                maxLength={1}
                selectTextOnFocus
              />
            ))}
          </View>

          {error ? (
            <MayushText variant="caption" color="#D9381E" align="center" style={styles.errorText}>
              {error}
            </MayushText>
          ) : null}
        </View>

        <PrimaryButton
          label={language === 'ar' ? 'تأكيد تغيير الرقم' : 'Valider le nouveau numéro'}
          onPress={handleVerify}
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
  otpGrid: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    gap: 8,
    marginVertical: 20,
  },
  otpBox: {
    width: 44,
    height: 52,
    borderRadius: 12,
    borderWidth: 1.5,
    borderColor: colors.surface.borderWarm,
    backgroundColor: colors.surface.cream,
    textAlign: 'center',
    fontSize: 22,
    fontWeight: '700',
    color: colors.brand.navy900,
  },
  otpBoxFilled: {
    borderColor: colors.brand.orange500,
    backgroundColor: colors.surface.white,
  },
  otpBoxError: {
    borderColor: '#D9381E',
    backgroundColor: '#FFEBEE',
  },
  errorText: { marginTop: 4 },
  submitButton: { marginTop: 24, minHeight: 52 },
});
