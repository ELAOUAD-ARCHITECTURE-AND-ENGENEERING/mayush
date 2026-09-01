import React, { useEffect, useRef, useState } from 'react';
import {
  KeyboardAvoidingView,
  Platform,
  ScrollView,
  StyleSheet,
  TextInput,
  TouchableOpacity,
  View,
} from 'react-native';
import { MayushLogo } from '../../design-system/components/brand/MayushLogo';
import { PrimaryButton } from '../../design-system/components/actions/PrimaryButton';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { useTheme } from '../../design-system/theme/useTheme';
import { colors } from '../../design-system/tokens/colors';
import { authState } from '../../commerce/authState';

export interface PhoneOtpVerificationScreenProps {
  onBack?: () => void;
  onSuccess?: () => void;
  onError?: (errCode: string) => void;
}

export const PhoneOtpVerificationScreen: React.FC<PhoneOtpVerificationScreenProps> = ({
  onBack,
  onSuccess,
  onError,
}) => {
  const { isRTL, language } = useTheme();
  const [digits, setDigits] = useState<string[]>(['', '', '', '', '', '']);
  const [timerSeconds, setTimerSeconds] = useState(30);
  const [canResend, setCanResend] = useState(false);
  const inputsRef = useRef<(TextInput | null)[]>([]);

  const phone = authState.getRegistrationDraft().emailOrPhone || '+212 6 61 99 88 77';

  useEffect(() => {
    let interval: any = null;
    if (timerSeconds > 0) {
      interval = setInterval(() => {
        setTimerSeconds((prev) => prev - 1);
      }, 1000);
    } else {
      setCanResend(true);
    }
    return () => {
      if (interval) clearInterval(interval);
    };
  }, [timerSeconds]);

  const handleDigitChange = (index: number, val: string) => {
    const clean = val.replace(/[^0-9]/g, '');
    const newDigits = [...digits];

    if (clean.length > 1) {
      // Handle paste
      const pasted = clean.slice(0, 6).split('');
      for (let i = 0; i < 6; i++) {
        newDigits[i] = pasted[i] || '';
      }
      setDigits(newDigits);
      inputsRef.current[5]?.focus();
      return;
    }

    newDigits[index] = clean;
    setDigits(newDigits);

    if (clean && index < 5) {
      inputsRef.current[index + 1]?.focus();
    }
  };

  const handleKeyPress = (index: number, key: string) => {
    if (key === 'Backspace' && !digits[index] && index > 0) {
      inputsRef.current[index - 1]?.focus();
    }
  };

  const handleVerify = () => {
    const code = digits.join('');
    authState.setOtpCode(code);

    if (code === '999999' || (code.length === 6 && code.startsWith('000'))) {
      authState.failOtp('Code OTP incorrect ou expiré.');
      onError?.(code);
      return;
    }

    if (code.length === 6 || code === '123456') {
      authState.completeRegistration();
      onSuccess?.();
    } else {
      authState.failOtp('Veuillez saisir un code OTP à 6 chiffres.');
      onError?.(code);
    }
  };

  const handleResend = () => {
    setDigits(['', '', '', '', '', '']);
    setTimerSeconds(30);
    setCanResend(false);
    inputsRef.current[0]?.focus();
  };

  const copy =
    language === 'ar'
      ? {
          title: 'التحقق من رمز OTP',
          subtitle: `أدخل الرمز المكون من 6 أرقام المرسل إلى ${phone}`,
          verifyCTA: 'تأكيد الرمز',
          resendMsg: canResend ? 'إعادة إرسال الرمز' : `إعادة الإرسال خلال ${timerSeconds} ثانية`,
          back: 'العودة',
        }
      : {
          title: 'Vérification OTP',
          subtitle: `Saisissez le code à 6 chiffres envoyé au ${phone}`,
          verifyCTA: 'Vérifier le code',
          resendMsg: canResend ? 'Renvoyer le code SMS' : `Renvoyer le code dans ${timerSeconds}s`,
          back: 'Retour',
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
            <MayushIcon name="shield" size={30} color={colors.brand.orange500} />
          </View>
        </View>

        <MayushText variant="display" color={colors.brand.navy900} align="center" style={styles.title}>
          {copy.title}
        </MayushText>
        <MayushText variant="caption" color={colors.neutral.gray700} align="center" style={styles.subtitle}>
          {copy.subtitle}
        </MayushText>

        <View style={styles.card}>
          <View style={styles.otpGrid}>
            {digits.map((digit, i) => (
              <TextInput
                key={i}
                ref={(ref) => {
                  inputsRef.current[i] = ref;
                }}
                style={[styles.otpBox, digit ? styles.otpBoxActive : null]}
                keyboardType="number-pad"
                maxLength={1}
                value={digit}
                onChangeText={(val) => handleDigitChange(i, val)}
                onKeyPress={({ nativeEvent }) => handleKeyPress(i, nativeEvent.key)}
                selectTextOnFocus
              />
            ))}
          </View>

          <TouchableOpacity
            disabled={!canResend}
            onPress={handleResend}
            style={styles.resendContainer}
            accessibilityRole="button"
          >
            <MayushText
              variant="caption"
              color={canResend ? colors.brand.orange500 : colors.neutral.gray700}
              align="center"
              style={canResend ? styles.boldText : undefined}
            >
              {copy.resendMsg}
            </MayushText>
          </TouchableOpacity>

          <View style={styles.buttonSpacing} />

          <PrimaryButton label={copy.verifyCTA} onPress={handleVerify} />

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
  badgeContainer: { alignItems: 'center', marginTop: 20 },
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
  title: { marginTop: 16, fontSize: 25, lineHeight: 30 },
  subtitle: { marginTop: 8, marginHorizontal: 16, lineHeight: 20 },
  card: {
    marginTop: 24,
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
  otpGrid: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    gap: 8,
    marginVertical: 12,
  },
  otpBox: {
    width: 44,
    height: 52,
    borderRadius: 12,
    borderWidth: 1.5,
    borderColor: colors.surface.borderWarm,
    backgroundColor: '#FAF7F2',
    textAlign: 'center',
    fontSize: 22,
    fontWeight: '700',
    color: colors.brand.navy900,
  },
  otpBoxActive: {
    borderColor: colors.brand.orange500,
    backgroundColor: colors.surface.white,
  },
  resendContainer: { marginTop: 14, paddingVertical: 6 },
  boldText: { fontWeight: '600' },
  buttonSpacing: { height: 16 },
  backLink: { marginTop: 16, paddingVertical: 6 },
});
