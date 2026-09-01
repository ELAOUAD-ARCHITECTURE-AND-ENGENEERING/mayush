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

export interface ChangePhoneScreenProps {
  onNavigateTab?: (tab: TabKey) => void;
  onBack?: () => void;
  onContinueToOtp?: (newPhone: string) => void;
}

export const ChangePhoneScreen: React.FC<ChangePhoneScreenProps> = ({
  onNavigateTab,
  onBack,
  onContinueToOtp,
}) => {
  const { isRTL, language } = useTheme();
  const currentPhone = authState.getUser()?.phone || '+212 6 61 99 88 77';

  const [newPhone, setNewPhone] = useState('+212 ');
  const [error, setError] = useState('');

  const isValidMoroccanPhone = (phoneStr: string) => {
    const clean = phoneStr.replace(/\s+/g, '');
    return clean.startsWith('+2126') || clean.startsWith('+2127') || clean.startsWith('06') || clean.startsWith('07');
  };

  const handleSubmit = () => {
    if (!isValidMoroccanPhone(newPhone)) {
      setError(language === 'ar' ? 'يرجى إدخال رقم هاتف مغربي صحيح (+212 6...)' : 'Numéro marocain valide requis (+212 6... ou 06...).');
      return;
    }
    setError('');
    authState.updateContactChangeDraft({ newPhone });
    onContinueToOtp ? onContinueToOtp(newPhone) : onBack?.();
  };

  return (
    <View style={styles.screen} accessibilityLabel="Changer le numéro de téléphone">
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
            {language === 'ar' ? 'تغيير رقم الهاتف' : 'Changer de téléphone'}
          </MayushText>
          <View style={styles.headerSpacer} />
        </View>

        {/* Info Card */}
        <View style={styles.card}>
          <MayushText variant="caption" color={colors.neutral.gray500} style={isRTL && styles.rtlText}>
            {language === 'ar' ? 'رقم الهاتف الحالي' : 'Numéro actuel'}
          </MayushText>
          <MayushText variant="body" color={colors.brand.navy900} style={[styles.currentPhone, isRTL && styles.rtlText]}>
            {currentPhone}
          </MayushText>

          <View style={styles.divider} />

          <TextField
            label={language === 'ar' ? 'رقم الهاتف الجديد (المغرب +212)' : 'Nouveau numéro de téléphone (+212)'}
            value={newPhone}
            onChangeText={(txt) => {
              setNewPhone(txt);
              if (error) setError('');
            }}
            placeholder="+212 6 00 11 22 33"
            leftIcon="phone"
            keyboardType="phone-pad"
            error={error}
            containerStyle={styles.fieldSpacing}
          />
        </View>

        <PrimaryButton
          label={language === 'ar' ? 'إرسال رمز التحقق OTP' : 'Envoyer le code de vérification'}
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
  currentPhone: { fontWeight: '700', marginTop: 4, marginBottom: 16 },
  divider: { height: 1, backgroundColor: '#F0E8DF', marginBottom: 16 },
  fieldSpacing: { marginBottom: 16 },
  submitButton: { marginTop: 24, minHeight: 52 },
});
