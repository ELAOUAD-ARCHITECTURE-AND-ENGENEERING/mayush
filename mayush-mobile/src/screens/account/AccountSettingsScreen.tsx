import React, { useEffect, useState } from 'react';
import { ScrollView, StyleSheet, TouchableOpacity, View } from 'react-native';
import { authState } from '../../commerce/authState';
import { BottomTabBar, TabKey } from '../../design-system/components/navigation/BottomTabBar';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { useTheme } from '../../design-system/theme/useTheme';
import { colors } from '../../design-system/tokens/colors';

export interface AccountSettingsScreenProps {
  onNavigateTab?: (tab: TabKey) => void;
  onBack?: () => void;
  onNavigateMyInformation?: () => void;
  onNavigateEditProfile?: () => void;
  onNavigateChangeEmail?: () => void;
  onNavigateChangePhone?: () => void;
  onNavigateChangePassword?: () => void;
  onNavigatePaymentMethods?: () => void;
  onNavigateLanguageRegion?: () => void;
  onNavigateMarketingPreferences?: () => void;
  onNavigateNotificationManagement?: () => void;
  onLogout?: () => void;
}

export const AccountSettingsScreen: React.FC<AccountSettingsScreenProps> = ({
  onNavigateTab,
  onBack,
  onNavigateMyInformation,
  onNavigateEditProfile,
  onNavigateChangeEmail,
  onNavigateChangePhone,
  onNavigateChangePassword,
  onNavigatePaymentMethods,
  onNavigateLanguageRegion,
  onNavigateMarketingPreferences,
  onNavigateNotificationManagement,
  onLogout,
}) => {
  const { isRTL, language } = useTheme();
  const [user, setUser] = useState(authState.getUser());

  useEffect(() => {
    return authState.subscribe(() => {
      setUser(authState.getUser());
    });
  }, []);

  const fullName = user?.fullName || 'Karim Benjelloun';
  const email = user?.email || user?.emailOrPhone || 'karim.benjelloun@example.ma';
  const initials = fullName
    .split(' ')
    .map((n) => n[0])
    .join('')
    .substring(0, 2)
    .toUpperCase();

  const handleLogout = () => {
    authState.logout();
    onLogout ? onLogout() : onBack?.();
  };

  return (
    <View style={styles.screen} accessibilityLabel="Paramètres du compte">
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
            {language === 'ar' ? 'إعدادات الحساب' : 'Paramètres du compte'}
          </MayushText>
          <View style={styles.headerSpacer} />
        </View>

        {/* Profile Avatar & Header Card */}
        <View style={styles.avatarCard}>
          <View style={styles.avatarCircle}>
            <MayushText style={styles.initialsText}>{initials}</MayushText>
          </View>
          <View style={styles.avatarDetails}>
            <MayushText variant="sectionTitle" color={colors.brand.navy900} style={isRTL && styles.rtlText}>
              {fullName}
            </MayushText>
            <MayushText variant="caption" color={colors.neutral.gray700} style={isRTL && styles.rtlText}>
              {email}
            </MayushText>
          </View>
          <TouchableOpacity
            accessibilityRole="button"
            accessibilityLabel="Modifier la photo"
            onPress={onNavigateEditProfile}
            style={styles.editPhotoButton}
          >
            <MayushIcon name="user" size={18} color={colors.brand.orange500} />
            <MayushText variant="caption" color={colors.brand.orange500} style={styles.editPhotoLabel}>
              {language === 'ar' ? 'تعديل' : 'Modifier'}
            </MayushText>
          </TouchableOpacity>
        </View>

        {/* Settings Group 1: Profil & Informations */}
        <View style={styles.sectionHeader}>
          <MayushText variant="caption" color={colors.neutral.gray700} style={isRTL && styles.rtlText}>
            {language === 'ar' ? 'الملف الشخصي والمعلومات' : 'PROFIL & INFORMATIONS'}
          </MayushText>
        </View>
        <View style={styles.menuCard}>
          <TouchableOpacity
            accessibilityRole="button"
            onPress={onNavigateMyInformation}
            style={[styles.menuRow, isRTL && styles.rowReverse]}
          >
            <MayushIcon name="user" size={22} color={colors.brand.navy900} />
            <MayushText variant="body" color={colors.brand.navy900} style={[styles.menuLabel, isRTL && styles.rtlText]}>
              {language === 'ar' ? 'معلوماتي الشخصية' : 'Mes informations personnelles'}
            </MayushText>
            <MayushIcon name={isRTL ? 'chevron-left' : 'chevron-right'} size={20} color={colors.neutral.gray500} />
          </TouchableOpacity>

          <View style={styles.divider} />

          <TouchableOpacity
            accessibilityRole="button"
            onPress={onNavigateEditProfile}
            style={[styles.menuRow, isRTL && styles.rowReverse]}
          >
            <MayushIcon name="heart" size={22} color={colors.brand.navy900} />
            <MayushText variant="body" color={colors.brand.navy900} style={[styles.menuLabel, isRTL && styles.rtlText]}>
              {language === 'ar' ? 'تعديل الملف الشخصي' : 'Modifier le profil'}
            </MayushText>
            <MayushIcon name={isRTL ? 'chevron-left' : 'chevron-right'} size={20} color={colors.neutral.gray500} />
          </TouchableOpacity>
        </View>

        {/* Settings Group 2: Coordonnées & Identifiants */}
        <View style={styles.sectionHeader}>
          <MayushText variant="caption" color={colors.neutral.gray700} style={isRTL && styles.rtlText}>
            {language === 'ar' ? 'معلومات الاتصال والأمان' : 'COORDONNÉES & SÉCURITÉ'}
          </MayushText>
        </View>
        <View style={styles.menuCard}>
          <TouchableOpacity
            accessibilityRole="button"
            onPress={onNavigateChangeEmail}
            style={[styles.menuRow, isRTL && styles.rowReverse]}
          >
            <MayushIcon name="user" size={22} color={colors.brand.navy900} />
            <View style={styles.menuTextGroup}>
              <MayushText variant="body" color={colors.brand.navy900} style={isRTL && styles.rtlText}>
                {language === 'ar' ? 'البريد الإلكتروني' : 'Adresse e-mail'}
              </MayushText>
              <MayushText variant="caption" color={colors.neutral.gray500} style={isRTL && styles.rtlText}>
                {email}
              </MayushText>
            </View>
            <MayushIcon name={isRTL ? 'chevron-left' : 'chevron-right'} size={20} color={colors.neutral.gray500} />
          </TouchableOpacity>

          <View style={styles.divider} />

          <TouchableOpacity
            accessibilityRole="button"
            onPress={onNavigateChangePhone}
            style={[styles.menuRow, isRTL && styles.rowReverse]}
          >
            <MayushIcon name="phone" size={22} color={colors.brand.navy900} />
            <View style={styles.menuTextGroup}>
              <MayushText variant="body" color={colors.brand.navy900} style={isRTL && styles.rtlText}>
                {language === 'ar' ? 'رقم الهاتف' : 'Numéro de téléphone'}
              </MayushText>
              <MayushText variant="caption" color={colors.neutral.gray500} style={isRTL && styles.rtlText}>
                {user?.phone || '+212 6 61 99 88 77'}
              </MayushText>
            </View>
            <MayushIcon name={isRTL ? 'chevron-left' : 'chevron-right'} size={20} color={colors.neutral.gray500} />
          </TouchableOpacity>

          <View style={styles.divider} />

          <TouchableOpacity
            accessibilityRole="button"
            onPress={onNavigateChangePassword}
            style={[styles.menuRow, isRTL && styles.rowReverse]}
          >
            <MayushIcon name="lock" size={22} color={colors.brand.navy900} />
            <MayushText variant="body" color={colors.brand.navy900} style={[styles.menuLabel, isRTL && styles.rtlText]}>
              {language === 'ar' ? 'تغيير كلمة المرور' : 'Changer le mot de passe'}
            </MayushText>
            <MayushIcon name={isRTL ? 'chevron-left' : 'chevron-right'} size={20} color={colors.neutral.gray500} />
          </TouchableOpacity>
        </View>

        {/* Settings Group 3: Préférences & Paiement */}
        <View style={styles.sectionHeader}>
          <MayushText variant="caption" color={colors.neutral.gray700} style={isRTL && styles.rtlText}>
            {language === 'ar' ? 'التفضيلات والدفع' : 'PRÉFÉRENCES & PAIEMENT'}
          </MayushText>
        </View>
        <View style={styles.menuCard}>
          <TouchableOpacity
            accessibilityRole="button"
            onPress={onNavigatePaymentMethods}
            style={[styles.menuRow, isRTL && styles.rowReverse]}
          >
            <MayushIcon name="credit-card" size={22} color={colors.brand.navy900} />
            <MayushText variant="body" color={colors.brand.navy900} style={[styles.menuLabel, isRTL && styles.rtlText]}>
              {language === 'ar' ? 'طرق الدفع' : 'Moyens de paiement'}
            </MayushText>
            <MayushIcon name={isRTL ? 'chevron-left' : 'chevron-right'} size={20} color={colors.neutral.gray500} />
          </TouchableOpacity>

          <View style={styles.divider} />

          <TouchableOpacity
            accessibilityRole="button"
            onPress={onNavigateLanguageRegion}
            style={[styles.menuRow, isRTL && styles.rowReverse]}
          >
            <MayushIcon name="globe" size={22} color={colors.brand.navy900} />
            <MayushText variant="body" color={colors.brand.navy900} style={[styles.menuLabel, isRTL && styles.rtlText]}>
              {language === 'ar' ? 'الغة والمنطقة' : 'Langue & Région'}
            </MayushText>
            <MayushIcon name={isRTL ? 'chevron-left' : 'chevron-right'} size={20} color={colors.neutral.gray500} />
          </TouchableOpacity>

          <View style={styles.divider} />

          <TouchableOpacity
            accessibilityRole="button"
            onPress={onNavigateMarketingPreferences}
            style={[styles.menuRow, isRTL && styles.rowReverse]}
          >
            <MayushIcon name="sliders" size={22} color={colors.brand.navy900} />
            <MayushText variant="body" color={colors.brand.navy900} style={[styles.menuLabel, isRTL && styles.rtlText]}>
              {language === 'ar' ? 'تفضيلات التسويق' : 'Préférences marketing'}
            </MayushText>
            <MayushIcon name={isRTL ? 'chevron-left' : 'chevron-right'} size={20} color={colors.neutral.gray500} />
          </TouchableOpacity>

          <View style={styles.divider} />

          <TouchableOpacity
            accessibilityRole="button"
            onPress={onNavigateNotificationManagement}
            style={[styles.menuRow, isRTL && styles.rowReverse]}
          >
            <MayushIcon name="bell" size={22} color={colors.brand.navy900} />
            <MayushText variant="body" color={colors.brand.navy900} style={[styles.menuLabel, isRTL && styles.rtlText]}>
              {language === 'ar' ? 'إدارة الإشعارات' : 'Gestion des notifications'}
            </MayushText>
            <MayushIcon name={isRTL ? 'chevron-left' : 'chevron-right'} size={20} color={colors.neutral.gray500} />
          </TouchableOpacity>
        </View>

        {/* Logout Button */}
        <TouchableOpacity
          accessibilityRole="button"
          accessibilityLabel="Déconnexion"
          onPress={handleLogout}
          style={styles.logoutButton}
        >
          <MayushIcon name="lock" size={20} color="#D9381E" />
          <MayushText variant="button" color="#D9381E">
            {language === 'ar' ? 'تسجيل الخروج' : 'Se déconnecter'}
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
  avatarCard: {
    marginTop: 16,
    padding: 16,
    borderRadius: 20,
    backgroundColor: colors.surface.white,
    borderWidth: 1,
    borderColor: '#E7DED3',
    flexDirection: 'row',
    alignItems: 'center',
    gap: 14,
  },
  avatarCircle: {
    width: 56,
    height: 56,
    borderRadius: 28,
    backgroundColor: colors.brand.navy900,
    alignItems: 'center',
    justifyContent: 'center',
  },
  initialsText: { color: colors.surface.white, fontSize: 20, fontWeight: '700' },
  avatarDetails: { flex: 1, gap: 2 },
  editPhotoButton: {
    paddingHorizontal: 12,
    paddingVertical: 8,
    borderRadius: 12,
    backgroundColor: '#FFF3E6',
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
  },
  editPhotoLabel: { fontWeight: '600' },
  sectionHeader: { marginTop: 24, marginBottom: 8, paddingHorizontal: 4 },
  menuCard: {
    borderRadius: 20,
    backgroundColor: colors.surface.white,
    borderWidth: 1,
    borderColor: '#E7DED3',
    paddingHorizontal: 16,
  },
  menuRow: {
    minHeight: 56,
    flexDirection: 'row',
    alignItems: 'center',
    gap: 14,
  },
  menuLabel: { flex: 1 },
  menuTextGroup: { flex: 1, gap: 2 },
  divider: { height: 1, backgroundColor: '#F0E8DF' },
  logoutButton: {
    height: 54,
    marginTop: 28,
    borderRadius: 16,
    backgroundColor: '#FFEEEC',
    borderWidth: 1,
    borderColor: '#FFCDD2',
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 8,
  },
});
