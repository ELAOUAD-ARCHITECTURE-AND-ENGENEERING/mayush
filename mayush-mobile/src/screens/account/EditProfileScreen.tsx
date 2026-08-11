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

export interface EditProfileScreenProps {
  onNavigateTab?: (tab: TabKey) => void;
  onBack?: () => void;
  onSaveSuccess?: () => void;
}

export const EditProfileScreen: React.FC<EditProfileScreenProps> = ({
  onNavigateTab,
  onBack,
  onSaveSuccess,
}) => {
  const { isRTL, language } = useTheme();
  const currentDraft = authState.getProfileDraft();

  const [fullName, setFullName] = useState(currentDraft.fullName);
  const [phone, setPhone] = useState(currentDraft.phone);
  const [city, setCity] = useState(currentDraft.city);
  const [birthDate, setBirthDate] = useState(currentDraft.birthDate);
  const [gender, setGender] = useState<'m' | 'f' | 'other' | null>(currentDraft.gender);

  const [nameError, setNameError] = useState('');
  const [phoneError, setPhoneError] = useState('');

  const validate = () => {
    let isValid = true;
    if (!fullName.trim() || fullName.trim().length < 3) {
      setNameError(language === 'ar' ? 'يرجى إدخال اسم صحيح' : 'Veuillez saisir un nom d’au moins 3 caractères.');
      isValid = false;
    } else {
      setNameError('');
    }

    const cleanPhone = phone.replace(/\s+/g, '');
    if (cleanPhone && !cleanPhone.startsWith('+212') && !cleanPhone.startsWith('06') && !cleanPhone.startsWith('07')) {
      setPhoneError(language === 'ar' ? 'يرجى إدخال رقم هاتف مغربي صحيح (+212...)' : 'Numéro marocain valide requis (+212 6... ou 06...).');
      isValid = false;
    } else {
      setPhoneError('');
    }

    return isValid;
  };

  const handleSave = () => {
    if (!validate()) return;
    authState.updateProfileDraft({
      fullName,
      phone,
      city,
      birthDate,
      gender,
    });
    authState.saveProfileFromDraft();
    onSaveSuccess ? onSaveSuccess() : onBack?.();
  };

  return (
    <View style={styles.screen} accessibilityLabel="Modifier le profil">
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
            {language === 'ar' ? 'تعديل الملف الشخصي' : 'Modifier le profil'}
          </MayushText>
          <View style={styles.headerSpacer} />
        </View>

        {/* Form Fields Card */}
        <View style={styles.card}>
          <TextField
            label={language === 'ar' ? 'الاسم الكامل' : 'Nom complet'}
            value={fullName}
            onChangeText={(txt) => {
              setFullName(txt);
              if (nameError) setNameError('');
            }}
            placeholder="Karim Benjelloun"
            leftIcon="user"
            error={nameError}
            containerStyle={styles.fieldSpacing}
          />

          <TextField
            label={language === 'ar' ? 'رقم الهاتف' : 'Numéro de téléphone'}
            value={phone}
            onChangeText={(txt) => {
              setPhone(txt);
              if (phoneError) setPhoneError('');
            }}
            placeholder="+212 6 61 99 88 77"
            keyboardType="phone-pad"
            leftIcon="phone"
            error={phoneError}
            containerStyle={styles.fieldSpacing}
          />

          <TextField
            label={language === 'ar' ? 'المدينة' : 'Ville'}
            value={city}
            onChangeText={setCity}
            placeholder="Casablanca, Rabat, Marrakech..."
            leftIcon="search"
            containerStyle={styles.fieldSpacing}
          />

          <TextField
            label={language === 'ar' ? 'تاريخ الميلاد' : 'Date de naissance (JJ/MM/AAAA)'}
            value={birthDate}
            onChangeText={setBirthDate}
            placeholder="15/06/1992"
            containerStyle={styles.fieldSpacing}
          />

          {/* Gender Selector */}
          <MayushText variant="caption" color={colors.neutral.gray700} style={[styles.genderLabel, isRTL && styles.rtlText]}>
            {language === 'ar' ? 'الجنس' : 'Genre'}
          </MayushText>
          <View style={[styles.genderRow, isRTL && styles.rowReverse]}>
            <TouchableOpacity
              accessibilityRole="button"
              onPress={() => setGender('m')}
              style={[styles.genderChip, gender === 'm' && styles.genderChipSelected]}
            >
              <MayushText
                variant="button"
                color={gender === 'm' ? colors.surface.white : colors.brand.navy900}
                style={styles.chipText}
              >
                {language === 'ar' ? 'رجل' : 'Homme'}
              </MayushText>
            </TouchableOpacity>

            <TouchableOpacity
              accessibilityRole="button"
              onPress={() => setGender('f')}
              style={[styles.genderChip, gender === 'f' && styles.genderChipSelected]}
            >
              <MayushText
                variant="button"
                color={gender === 'f' ? colors.surface.white : colors.brand.navy900}
                style={styles.chipText}
              >
                {language === 'ar' ? 'امرأة' : 'Femme'}
              </MayushText>
            </TouchableOpacity>
          </View>
        </View>

        {/* Action Buttons */}
        <PrimaryButton
          label={language === 'ar' ? 'حفظ التغييرات' : 'Enregistrer les modifications'}
          onPress={handleSave}
          style={styles.saveButton}
        />

        <TouchableOpacity
          accessibilityRole="button"
          accessibilityLabel="Annuler"
          onPress={onBack}
          style={styles.cancelButton}
        >
          <MayushText variant="button" color={colors.neutral.gray700}>
            {language === 'ar' ? 'إلغاء' : 'Annuler'}
          </MayushText>
        </TouchableOpacity>
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
  fieldSpacing: { marginBottom: 16 },
  genderLabel: { marginTop: 4, marginBottom: 8, fontWeight: '600' },
  genderRow: { flexDirection: 'row', gap: 12 },
  genderChip: {
    flex: 1,
    height: 48,
    borderRadius: 12,
    borderWidth: 1.5,
    borderColor: colors.surface.borderWarm,
    backgroundColor: colors.surface.cream,
    alignItems: 'center',
    justifyContent: 'center',
  },
  genderChipSelected: {
    backgroundColor: colors.brand.navy900,
    borderColor: colors.brand.navy900,
  },
  chipText: { fontWeight: '600' },
  saveButton: { marginTop: 24, minHeight: 52 },
  cancelButton: {
    height: 48,
    marginTop: 12,
    alignItems: 'center',
    justifyContent: 'center',
  },
});
