import React, { useEffect, useState } from 'react';
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
import { TabKey } from '../../design-system/components/navigation/BottomTabBar';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { useTheme } from '../../design-system/theme/useTheme';
import { colors } from '../../design-system/tokens/colors';
import { fontFamilies } from '../../design-system/tokens/typography';

export interface MyInformationScreenProps {
  onNavigateTab?: (tab: TabKey) => void;
  onBack?: () => void;
  onEditProfile?: () => void;
  onChangeEmail?: () => void;
  onChangePhone?: () => void;
}

export const MyInformationScreen: React.FC<MyInformationScreenProps> = ({
  onNavigateTab,
  onBack,
  onEditProfile,
  onChangeEmail,
  onChangePhone,
}) => {
  const { isRTL, language } = useTheme();
  const [user, setUser] = useState(authState.getUser());
  const [uploadingAvatar, setUploadingAvatar] = useState(false);

  useEffect(() => {
    return authState.subscribe(() => {
      setUser(authState.getUser());
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

        const ext = asset.uri.split('.').pop() || 'jpg';
        const filename = `avatar_${Date.now()}.${ext}`;

        await authState.uploadAvatar(asset.base64 || '', filename);
        setUploadingAvatar(false);
      }
    } catch {
      setUploadingAvatar(false);
    }
  };

  const fullName = user?.fullName || '';
  const email = user?.email || user?.emailOrPhone || '';
  const phone = user?.phone || '';
  const city = user?.city || '';
  const birthDate = user?.birthDate || '';
  const genderLabel = user?.gender === 'f'
    ? (language === 'ar' ? 'امرأة' : 'Femme')
    : user?.gender === 'm'
      ? (language === 'ar' ? 'رجل' : 'Homme')
      : (language === 'ar' ? 'غير محدد' : 'Non spécifié');

  return (
    <View style={styles.screen} accessibilityLabel="Mes informations personnelles">
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
            {language === 'ar' ? 'معلوماتي الشخصية' : 'Mes Informations'}
          </MayushText>
          <View style={styles.headerSpacer} />
        </View>

        {/* Profile Avatar Card */}
        <View style={styles.avatarSection}>
          <TouchableOpacity
            onPress={handlePickAvatar}
            activeOpacity={0.85}
            style={styles.avatarContainer}
            accessibilityRole="button"
            accessibilityLabel={language === 'ar' ? 'تغيير الصورة الشخصية' : 'Modifier la photo de profil'}
          >
            {user?.avatarUrl ? (
              <Image source={{ uri: user.avatarUrl }} style={styles.avatarImage} />
            ) : (
              <View style={styles.avatarPlaceholder}>
                <MayushText variant="pageTitle" color={colors.brand.orange500} style={styles.avatarInitial}>
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
                <MayushIcon name="camera" size={16} color={colors.surface.white} />
              </View>
            )}
          </TouchableOpacity>
          <TouchableOpacity onPress={handlePickAvatar} activeOpacity={0.7} style={styles.avatarChangeLink}>
            <MayushText variant="smallBody" color={colors.brand.orange500} style={styles.avatarChangeText}>
              {language === 'ar' ? 'تغيير الصورة الشخصية' : 'Changer la photo'}
            </MayushText>
          </TouchableOpacity>
        </View>

        {/* Info Card */}
        <View style={styles.card}>
          <View style={[styles.infoRow, isRTL && styles.rowReverse]}>
            <View style={styles.infoTextGroup}>
              <MayushText variant="caption" color={colors.neutral.gray500} style={isRTL && styles.rtlText}>
                {language === 'ar' ? 'الاسم الكامل' : 'Nom complet'}
              </MayushText>
              <MayushText variant="body" color={colors.brand.navy900} style={[styles.infoValue, isRTL && styles.rtlText]}>
                {fullName || '—'}
              </MayushText>
            </View>
          </View>

          <View style={styles.divider} />

          <View style={[styles.infoRow, isRTL && styles.rowReverse]}>
            <View style={styles.infoTextGroup}>
              <MayushText variant="caption" color={colors.neutral.gray500} style={isRTL && styles.rtlText}>
                {language === 'ar' ? 'البريد الإلكتروني' : 'Adresse e-mail'}
              </MayushText>
              <MayushText variant="body" color={colors.brand.navy900} style={[styles.infoValue, isRTL && styles.rtlText]}>
                {email || '—'}
              </MayushText>
            </View>
            <TouchableOpacity accessibilityRole="button" onPress={onChangeEmail} style={styles.actionBadge}>
              <MayushText variant="caption" color={colors.brand.orange500} style={styles.actionBadgeText}>
                {language === 'ar' ? 'تعديل' : 'Modifier'}
              </MayushText>
            </TouchableOpacity>
          </View>

          <View style={styles.divider} />

          <View style={[styles.infoRow, isRTL && styles.rowReverse]}>
            <View style={styles.infoTextGroup}>
              <MayushText variant="caption" color={colors.neutral.gray500} style={isRTL && styles.rtlText}>
                {language === 'ar' ? 'رقم الهاتف' : 'Téléphone'}
              </MayushText>
              <MayushText variant="body" color={colors.brand.navy900} style={[styles.infoValue, isRTL && styles.rtlText]}>
                {phone || '—'}
              </MayushText>
            </View>
            <TouchableOpacity accessibilityRole="button" onPress={onChangePhone} style={styles.actionBadge}>
              <MayushText variant="caption" color={colors.brand.orange500} style={styles.actionBadgeText}>
                {language === 'ar' ? 'تعديل' : 'Modifier'}
              </MayushText>
            </TouchableOpacity>
          </View>

          <View style={styles.divider} />

          <View style={[styles.infoRow, isRTL && styles.rowReverse]}>
            <View style={styles.infoTextGroup}>
              <MayushText variant="caption" color={colors.neutral.gray500} style={isRTL && styles.rtlText}>
                {language === 'ar' ? 'المدينة' : 'Ville'}
              </MayushText>
              <MayushText variant="body" color={colors.brand.navy900} style={[styles.infoValue, isRTL && styles.rtlText]}>
                {city || '—'}
              </MayushText>
            </View>
          </View>

          <View style={styles.divider} />

          <View style={[styles.infoRow, isRTL && styles.rowReverse]}>
            <View style={styles.infoTextGroup}>
              <MayushText variant="caption" color={colors.neutral.gray500} style={isRTL && styles.rtlText}>
                {language === 'ar' ? 'تاريخ الميلاد' : 'Date de naissance'}
              </MayushText>
              <MayushText variant="body" color={colors.brand.navy900} style={[styles.infoValue, isRTL && styles.rtlText]}>
                {birthDate || '—'}
              </MayushText>
            </View>
          </View>

          <View style={styles.divider} />

          <View style={[styles.infoRow, isRTL && styles.rowReverse]}>
            <View style={styles.infoTextGroup}>
              <MayushText variant="caption" color={colors.neutral.gray500} style={isRTL && styles.rtlText}>
                {language === 'ar' ? 'الجنس' : 'Genre'}
              </MayushText>
              <MayushText variant="body" color={colors.brand.navy900} style={[styles.infoValue, isRTL && styles.rtlText]}>
                {genderLabel}
              </MayushText>
            </View>
          </View>
        </View>

        {/* Edit Button */}
        <PrimaryButton
          label={language === 'ar' ? 'تعديل معلوماتي' : 'Modifier mes informations'}
          onPress={onEditProfile || (() => {})}
          style={styles.editButton}
        />
      </ScrollView>
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
  avatarSection: {
    alignItems: 'center',
    marginTop: 12,
    marginBottom: 4,
  },
  avatarContainer: {
    width: 96,
    height: 96,
    borderRadius: 48,
    backgroundColor: '#FFF0E5',
    alignItems: 'center',
    justifyContent: 'center',
    position: 'relative',
    borderWidth: 3,
    borderColor: colors.surface.white,
    shadowColor: '#000',
    shadowOpacity: 0.08,
    shadowRadius: 8,
    shadowOffset: { width: 0, height: 3 },
    elevation: 3,
  },
  avatarImage: {
    width: 90,
    height: 90,
    borderRadius: 45,
  },
  avatarPlaceholder: {
    width: 90,
    height: 90,
    borderRadius: 45,
    backgroundColor: '#FFF0E5',
    alignItems: 'center',
    justifyContent: 'center',
  },
  avatarInitial: {
    fontSize: 34,
    fontFamily: fontFamilies.latin.bold,
  },
  cameraBadge: {
    position: 'absolute',
    bottom: 0,
    right: 0,
    width: 32,
    height: 32,
    borderRadius: 16,
    backgroundColor: colors.brand.orange500,
    borderWidth: 2,
    borderColor: colors.surface.white,
    alignItems: 'center',
    justifyContent: 'center',
    shadowColor: '#000',
    shadowOpacity: 0.15,
    shadowRadius: 4,
    shadowOffset: { width: 0, height: 2 },
    elevation: 4,
  },
  avatarLoadingOverlay: {
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
    bottom: 0,
    borderRadius: 48,
    backgroundColor: 'rgba(0, 0, 0, 0.45)',
    alignItems: 'center',
    justifyContent: 'center',
  },
  avatarChangeLink: {
    marginTop: 8,
    paddingVertical: 4,
  },
  avatarChangeText: {
    fontWeight: '600',
  },
  card: {
    marginTop: 16,
    borderRadius: 20,
    backgroundColor: colors.surface.white,
    borderWidth: 1,
    borderColor: '#E7DED3',
    paddingHorizontal: 18,
    paddingVertical: 8,
  },
  infoRow: {
    minHeight: 64,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
  },
  infoTextGroup: { flex: 1, gap: 4 },
  infoValue: { fontWeight: '600' },
  actionBadge: {
    paddingHorizontal: 10,
    paddingVertical: 6,
    borderRadius: 8,
    backgroundColor: '#FFF3E6',
  },
  actionBadgeText: { fontWeight: '700' },
  divider: { height: 1, backgroundColor: '#F0E8DF' },
  editButton: { marginTop: 24, minHeight: 52 },
});
