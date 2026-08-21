import React, { useState } from 'react';
import {
  ActivityIndicator,
  Alert,
  Image,
  ScrollView,
  StyleSheet,
  TouchableOpacity,
  View,
} from 'react-native';
import * as ImagePicker from 'expo-image-picker';
import { authState } from '../../commerce/authState';
import { PrimaryButton } from '../../design-system/components/actions/PrimaryButton';
import { TextField } from '../../design-system/components/forms/TextField';
import { DatePickerModal } from '../../design-system/components/forms/DatePickerModal';
import { TabKey } from '../../design-system/components/navigation/BottomTabBar';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { useTheme } from '../../design-system/theme/useTheme';
import { colors } from '../../design-system/tokens/colors';
import { fontFamilies } from '../../design-system/tokens/typography';

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
  const currentUser = authState.getUser();
  const currentDraft = authState.getProfileDraft();

  const [avatarUrl, setAvatarUrl] = useState<string | null>(currentUser?.avatarUrl || null);
  const [uploadingAvatar, setUploadingAvatar] = useState(false);
  const [fullName, setFullName] = useState(currentDraft.fullName || currentUser?.fullName || '');
  const [phone, setPhone] = useState(currentDraft.phone || currentUser?.phone || '');
  const [city, setCity] = useState(currentDraft.city || currentUser?.city || '');
  const [birthDate, setBirthDate] = useState(currentDraft.birthDate || currentUser?.birthDate || '');
  const [showDatePicker, setShowDatePicker] = useState(false);
  const [gender, setGender] = useState<'m' | 'f' | 'other' | null>(currentDraft.gender || currentUser?.gender || null);

  const [nameError, setNameError] = useState('');
  const [phoneError, setPhoneError] = useState('');
  const [saving, setSaving] = useState(false);
  const [generalError, setGeneralError] = useState('');

  React.useEffect(() => {
    return authState.subscribe(() => {
      const u = authState.getUser();
      if (u?.avatarUrl) {
        setAvatarUrl(u.avatarUrl);
      }
    });
  }, []);

  const handlePickAvatar = async () => {
    try {
      const permission = await ImagePicker.requestMediaLibraryPermissionsAsync();
      if (!permission.granted) {
        Alert.alert(
          language === 'ar' ? 'الإذن مطلوب' : 'Permission requise',
          language === 'ar'
            ? 'يرجى السماح بالوصول إلى مكتبة الصور لتغيير صورتك الشخصية.'
            : 'Veuillez autoriser l’accès à la galerie pour modifier votre photo de profil.'
        );
        return;
      }

      const result = await ImagePicker.launchImageLibraryAsync({
        mediaTypes: ['images'],
        allowsEditing: true,
        aspect: [1, 1],
        quality: 0.7,
        base64: true,
      });

      if (!result.canceled && result.assets && result.assets[0]?.base64) {
        const asset = result.assets[0];
        setUploadingAvatar(true);
        setGeneralError('');

        const ext = asset.uri.split('.').pop() || 'jpg';
        const filename = `avatar_${Date.now()}.${ext}`;

        const uploadRes = await authState.uploadAvatar(asset.base64, filename);
        setUploadingAvatar(false);

        if (uploadRes.success && uploadRes.path) {
          setAvatarUrl(`${uploadRes.path}?t=${Date.now()}`);
        } else {
          setGeneralError(uploadRes.error || (language === 'ar' ? 'فشل تحميل الصورة' : 'Échec du téléchargement de l’image.'));
        }
      }
    } catch (err: any) {
      setUploadingAvatar(false);
      setGeneralError(err.message || (language === 'ar' ? 'حدث خطأ أثناء اختيار الصورة' : 'Erreur lors du choix de l’image.'));
    }
  };

  const handlePhotoChange = async () => {
    const result = await ImagePicker.launchImageLibraryAsync({
      mediaTypes: ['images'],
      allowsEditing: true,
      aspect: [1, 1],
      quality: 0.8,
    });

    if (!result.canceled && result.assets[0]?.uri) {
      const success = await authState.updateProfilePhoto(result.assets[0].uri);
      if (!success) {
        // Could show error toast
      }
    }
  };

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

  const handleSave = async () => {
    if (!validate()) return;
    setSaving(true);
    setGeneralError('');
    authState.updateProfileDraft({
      fullName,
      phone,
      city,
      birthDate,
      gender,
    });
    const result = await authState.saveProfileFromDraft();
    setSaving(false);
    if (result.success) {
      onSaveSuccess ? onSaveSuccess() : onBack?.();
    } else {
      setGeneralError(result.error || (language === 'ar' ? 'حدث خطأ' : 'Une erreur est survenue.'));
    }
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
            <MayushIcon name={isRTL ? 'chevron-right' : 'chevron-left'} size={22} color={colors.brand.navy900} />
          </TouchableOpacity>
          <MayushText variant="sectionTitle" color={colors.brand.navy900} style={styles.headerTitle}>
            {language === 'ar' ? 'تعديل الملف الشخصي' : 'Modifier le profil'}
          </MayushText>
          <View style={styles.headerSpacer} />
        </View>

        {/* Profile Avatar Upload Section */}
        <View style={styles.avatarSection}>
          <TouchableOpacity
            onPress={handlePhotoChange}
            activeOpacity={0.85}
            style={styles.avatarContainer}
            accessibilityRole="button"
            accessibilityLabel={language === 'ar' ? 'تغيير الصورة الشخصية' : 'Modifier la photo de profil'}
          >
            {avatarUrl ? (
              <Image source={{ uri: avatarUrl }} style={styles.avatarImage} />
            ) : (
              <View style={styles.avatarPlaceholder}>
                <MayushText variant="sectionTitle" color={colors.brand.orange500} style={styles.avatarInitial}>
                  {(fullName || 'M').charAt(0).toUpperCase()}
                </MayushText>
              </View>
            )}

            {uploadingAvatar ? (
              <View style={styles.avatarLoadingOverlay}>
                <ActivityIndicator size="small" color={colors.surface.white} />
              </View>
            ) : (
              <View style={styles.cameraBadge}>
                <MayushIcon name="camera" size={13} color={colors.surface.white} />
              </View>
            )}
          </TouchableOpacity>
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

          {/* Row with City & BirthDate side by side */}
          <View style={[styles.inlineRow, isRTL && styles.rowReverse]}>
            <View style={styles.inlineCol}>
              <TextField
                label={language === 'ar' ? 'المدينة' : 'Ville'}
                value={city}
                onChangeText={setCity}
                placeholder="Casablanca..."
                leftIcon="search"
                containerStyle={styles.compactField}
              />
            </View>

            <View style={styles.inlineCol}>
              <TouchableOpacity
                activeOpacity={0.88}
                onPress={() => setShowDatePicker(true)}
                accessibilityRole="button"
                accessibilityLabel={language === 'ar' ? 'تحديد تاريخ الميلاد' : 'Sélectionner la date de naissance'}
              >
                <View pointerEvents="none">
                  <TextField
                    label={language === 'ar' ? 'تاريخ الميلاد' : 'Naissance (JJ/MM/AA)'}
                    value={birthDate}
                    onChangeText={setBirthDate}
                    placeholder="15/06/1992"
                    leftIcon="calendar"
                    containerStyle={styles.compactField}
                  />
                </View>
              </TouchableOpacity>
            </View>
          </View>

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
                variant="caption"
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
                variant="caption"
                color={gender === 'f' ? colors.surface.white : colors.brand.navy900}
                style={styles.chipText}
              >
                {language === 'ar' ? 'امرأة' : 'Femme'}
              </MayushText>
            </TouchableOpacity>
          </View>
        </View>

        {/* Error */}
        {generalError ? (
          <MayushText variant="caption" color="#D9381E" style={styles.errorText}>
            {generalError}
          </MayushText>
        ) : null}

        {/* Action Buttons */}
        <PrimaryButton
          label={saving ? (language === 'ar' ? 'جاري الحفظ...' : 'Enregistrement...') : (language === 'ar' ? 'حفظ التغييرات' : 'Enregistrer')}
          onPress={handleSave}
          disabled={saving || uploadingAvatar}
          style={styles.saveButton}
        />

        <TouchableOpacity
          accessibilityRole="button"
          accessibilityLabel="Annuler"
          onPress={onBack}
          style={styles.cancelButton}
        >
          <MayushText variant="caption" color={colors.neutral.gray700} style={styles.cancelText}>
            {language === 'ar' ? 'إلغاء' : 'Annuler'}
          </MayushText>
        </TouchableOpacity>
      </ScrollView>

      <DatePickerModal
        visible={showDatePicker}
        value={birthDate}
        onClose={() => setShowDatePicker(false)}
        onConfirm={(formattedDate) => setBirthDate(formattedDate)}
      />
    </View>
  );
};

const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: '#FBF2EF' },
  scrollContent: { paddingHorizontal: 16, paddingBottom: 14, paddingTop: 4 },
  rowReverse: { flexDirection: 'row-reverse' },
  rtlText: { writingDirection: 'rtl' },
  header: { height: 44, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' },
  headerTitle: { fontSize: 17 },
  backButton: { width: 36, height: 36, alignItems: 'center', justifyContent: 'center' },
  headerSpacer: { width: 36 },
  avatarSection: {
    alignItems: 'center',
    marginTop: 4,
    marginBottom: 4,
  },
  avatarContainer: {
    width: 68,
    height: 68,
    borderRadius: 34,
    backgroundColor: '#FFF0E5',
    alignItems: 'center',
    justifyContent: 'center',
    position: 'relative',
    borderWidth: 2.5,
    borderColor: colors.surface.white,
    shadowColor: '#000',
    shadowOpacity: 0.06,
    shadowRadius: 6,
    shadowOffset: { width: 0, height: 2 },
    elevation: 2,
  },
  avatarImage: {
    width: 63,
    height: 63,
    borderRadius: 31.5,
  },
  avatarPlaceholder: {
    width: 63,
    height: 63,
    borderRadius: 31.5,
    backgroundColor: '#FFF0E5',
    alignItems: 'center',
    justifyContent: 'center',
  },
  avatarInitial: {
    fontSize: 24,
    fontFamily: fontFamilies.latin.bold,
  },
  cameraBadge: {
    position: 'absolute',
    bottom: -2,
    right: -2,
    width: 24,
    height: 24,
    borderRadius: 12,
    backgroundColor: colors.brand.orange500,
    borderWidth: 1.5,
    borderColor: colors.surface.white,
    alignItems: 'center',
    justifyContent: 'center',
    shadowColor: '#000',
    shadowOpacity: 0.12,
    shadowRadius: 3,
    elevation: 3,
  },
  avatarLoadingOverlay: {
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
    bottom: 0,
    borderRadius: 34,
    backgroundColor: 'rgba(0, 0, 0, 0.45)',
    alignItems: 'center',
    justifyContent: 'center',
  },
  card: {
    marginTop: 6,
    borderRadius: 16,
    backgroundColor: colors.surface.white,
    borderWidth: 1,
    borderColor: '#E7DED3',
    paddingHorizontal: 14,
    paddingVertical: 10,
  },
  fieldSpacing: { marginBottom: 8 },
  inlineRow: {
    flexDirection: 'row',
    gap: 8,
    marginBottom: 8,
  },
  inlineCol: {
    flex: 1,
  },
  compactField: {
    marginBottom: 0,
  },
  genderLabel: { marginTop: 2, marginBottom: 4, fontWeight: '600', fontSize: 12 },
  genderRow: { flexDirection: 'row', gap: 8 },
  genderChip: {
    flex: 1,
    height: 38,
    borderRadius: 10,
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
  chipText: { fontWeight: '700', fontSize: 13 },
  errorText: { marginTop: 8, textAlign: 'center', fontSize: 12 },
  saveButton: { marginTop: 12, minHeight: 46, height: 46, borderRadius: 12 },
  cancelButton: {
    height: 32,
    marginTop: 4,
    alignItems: 'center',
    justifyContent: 'center',
  },
  cancelText: {
    fontWeight: '600',
    fontSize: 13,
  },
});
