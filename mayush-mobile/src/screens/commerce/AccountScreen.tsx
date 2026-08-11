import React, { useEffect, useState } from 'react';
import { Image, ScrollView, StyleSheet, TouchableOpacity, View } from 'react-native';
import { authState } from '../../commerce/authState';
import { MayushLogo } from '../../design-system/components/brand/MayushLogo';
import { BottomTabBar, TabKey } from '../../design-system/components/navigation/BottomTabBar';
import { MayushIcon, MayushIconName } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { useTheme } from '../../design-system/theme/useTheme';
import { colors } from '../../design-system/tokens/colors';

const ACCOUNT_ARTWORK = require('../../../assets/illustrations/account-guest-scene.png');

export interface AccountScreenProps {
  onNavigateTab?: (tab: TabKey) => void;
  onExplore?: () => void;
  onLogin?: () => void;
  onCreateAccount?: () => void;
  onNavigateSettings?: () => void;
  onNavigateMyInformation?: () => void;
  onNavigateOrders?: () => void;
  onNavigateWishlist?: () => void;
  onNavigateAddresses?: () => void;
  onNavigateSecurity?: () => void;
  onNavigatePaymentMethods?: () => void;
  onNavigateLanguageRegion?: () => void;
  onNavigateLanguageSelection?: () => void;
  onNavigateMarketingPreferences?: () => void;
  onNavigateNotificationManagement?: () => void;
  onNavigateHelpSupport?: () => void;
  onConfirmLogoutTrigger?: () => void;
}

export const AccountScreen: React.FC<AccountScreenProps> = ({
  onNavigateTab,
  onExplore,
  onLogin,
  onCreateAccount,
  onNavigateSettings,
  onNavigateMyInformation,
  onNavigateOrders,
  onNavigateWishlist,
  onNavigateAddresses,
  onNavigateSecurity,
  onNavigatePaymentMethods,
  onNavigateLanguageRegion,
  onNavigateLanguageSelection,
  onNavigateMarketingPreferences,
  onNavigateNotificationManagement,
  onNavigateHelpSupport,
  onConfirmLogoutTrigger,
}) => {
  const { isRTL, language, setLanguage } = useTheme();
  const [authStatus, setAuthStatus] = useState(authState.getStatus());
  const [user, setUser] = useState(authState.getUser());

  useEffect(() => {
    return authState.subscribe(() => {
      setAuthStatus(authState.getStatus());
      setUser(authState.getUser());
    });
  }, []);

  const isAuthenticated = authStatus === 'authenticated' || authStatus === 'registration-success';

  if (isAuthenticated && user) {
    const fullName = user.fullName || 'Karim Benjelloun';
    const email = user.email || user.emailOrPhone || 'karim.benjelloun@example.ma';
    const initials = fullName
      .split(' ')
      .map((n) => n[0])
      .join('')
      .substring(0, 2)
      .toUpperCase();
    const completionPercent = user.profileCompletionPercent || 60;

    return (
      <View style={styles.screen} accessibilityLabel="Tableau de bord Mon compte">
        <ScrollView showsVerticalScrollIndicator={false} contentContainerStyle={styles.content}>
          {/* Header */}
          <View style={[styles.header, isRTL && styles.rowReverse]}>
            <MayushLogo width={160} height={48} />
            <TouchableOpacity
              accessibilityRole="button"
              accessibilityLabel="Paramètres"
              onPress={onNavigateSettings}
              style={styles.headerButton}
            >
              <MayushIcon name="user" size={24} color={colors.brand.navy900} />
            </TouchableOpacity>
          </View>

          {/* Profile Card */}
          <View style={styles.profileCard}>
            <View style={[styles.profileHeaderRow, isRTL && styles.rowReverse]}>
              <View style={styles.avatarCircle}>
                <MayushText style={styles.avatarInitials}>{initials}</MayushText>
              </View>
              <View style={styles.profileInfo}>
                <MayushText variant="sectionTitle" color={colors.brand.navy900} style={isRTL && styles.rtlText}>
                  {fullName}
                </MayushText>
                <MayushText variant="caption" color={colors.neutral.gray700} style={isRTL && styles.rtlText}>
                  {email}
                </MayushText>
              </View>
              <TouchableOpacity accessibilityRole="button" onPress={onNavigateSettings} style={styles.settingsBadge}>
                <MayushIcon name="chevron-right" size={20} color={colors.brand.navy900} />
              </TouchableOpacity>
            </View>

            {/* Profile Completion Progress Banner (309:751) */}
            <TouchableOpacity
              accessibilityRole="button"
              onPress={onNavigateMyInformation}
              style={styles.progressBanner}
            >
              <View style={styles.progressTextRow}>
                <MayushText variant="caption" color={colors.brand.navy900} style={styles.progressLabel}>
                  {language === 'ar' ? `إكمال الملف الشخصي (${completionPercent}%)` : `Profil complété à ${completionPercent}%`}
                </MayushText>
                <MayushText variant="caption" color={colors.brand.orange500} style={styles.progressCta}>
                  {language === 'ar' ? 'إكمال' : 'Compléter'}
                </MayushText>
              </View>
              <View style={styles.trackBar}>
                <View style={[styles.fillBar, { width: `${completionPercent}%` }]} />
              </View>
            </TouchableOpacity>
          </View>

          {/* Shortcuts Grid */}
          <View style={styles.shortcutsGrid}>
            <TouchableOpacity accessibilityRole="button" onPress={onNavigateOrders} style={styles.shortcutCard}>
              <MayushIcon name="shopping-bag" size={24} color={colors.brand.orange500} />
              <MayushText variant="button" color={colors.brand.navy900} style={styles.shortcutLabel}>
                {language === 'ar' ? 'طلباتي' : 'Commandes'}
              </MayushText>
            </TouchableOpacity>

            <TouchableOpacity accessibilityRole="button" onPress={onNavigateWishlist} style={styles.shortcutCard}>
              <MayushIcon name="heart" size={24} color={colors.brand.orange500} />
              <MayushText variant="button" color={colors.brand.navy900} style={styles.shortcutLabel}>
                {language === 'ar' ? 'مفضلاتي' : 'Favoris'}
              </MayushText>
            </TouchableOpacity>

            <TouchableOpacity accessibilityRole="button" onPress={onNavigateMyInformation} style={styles.shortcutCard}>
              <MayushIcon name="user" size={24} color={colors.brand.orange500} />
              <MayushText variant="button" color={colors.brand.navy900} style={styles.shortcutLabel}>
                {language === 'ar' ? 'معلوماتي' : 'Infos'}
              </MayushText>
            </TouchableOpacity>

            <TouchableOpacity accessibilityRole="button" onPress={onNavigateSettings} style={styles.shortcutCard}>
              <MayushIcon name="search" size={24} color={colors.brand.orange500} />
              <MayushText variant="button" color={colors.brand.navy900} style={styles.shortcutLabel}>
                {language === 'ar' ? 'الإعدادات' : 'Réglages'}
              </MayushText>
            </TouchableOpacity>
          </View>

          {/* Account Dashboard Menu Card */}
          <View style={styles.menuCard}>
            <TouchableOpacity accessibilityRole="button" onPress={onNavigateMyInformation} style={[styles.menuRow, isRTL && styles.rowReverse]}>
              <MayushIcon name="user" size={24} color={colors.brand.navy900} />
              <View style={styles.menuCopy}>
                <MayushText variant="sectionTitle" color={colors.brand.navy900} style={isRTL && styles.rtlText}>
                  {language === 'ar' ? 'معلوماتي الشخصية' : 'Mes informations'}
                </MayushText>
                <MayushText variant="smallBody" color={colors.neutral.gray700} style={isRTL && styles.rtlText}>
                  {language === 'ar' ? 'تعديل الاسم والهاتف والبريد' : 'Nom, email, téléphone et ville'}
                </MayushText>
              </View>
              <MayushIcon name={isRTL ? 'chevron-left' : 'chevron-right'} size={22} color={colors.brand.navy900} />
            </TouchableOpacity>

            <View style={styles.divider} />

            <TouchableOpacity accessibilityRole="button" onPress={onNavigateSettings} style={[styles.menuRow, isRTL && styles.rowReverse]}>
              <MayushIcon name="heart" size={24} color={colors.brand.navy900} />
              <View style={styles.menuCopy}>
                <MayushText variant="sectionTitle" color={colors.brand.navy900} style={isRTL && styles.rtlText}>
                  {language === 'ar' ? 'إعدادات الحساب' : 'Paramètres du compte'}
                </MayushText>
                <MayushText variant="smallBody" color={colors.neutral.gray700} style={isRTL && styles.rtlText}>
                  {language === 'ar' ? 'إدارة الحساب والأمان' : 'Profil, sécurité et identifiants'}
                </MayushText>
              </View>
              <MayushIcon name={isRTL ? 'chevron-left' : 'chevron-right'} size={22} color={colors.brand.navy900} />
            </TouchableOpacity>

            <View style={styles.divider} />

            <TouchableOpacity accessibilityRole="button" onPress={onNavigateAddresses || onNavigateSettings} style={[styles.menuRow, isRTL && styles.rowReverse]}>
              <MayushIcon name="search" size={24} color={colors.brand.navy900} />
              <View style={styles.menuCopy}>
                <MayushText variant="sectionTitle" color={colors.brand.navy900} style={isRTL && styles.rtlText}>
                  {language === 'ar' ? 'عناويني والتوصيل' : 'Adresses et livraison'}
                </MayushText>
                <MayushText variant="smallBody" color={colors.neutral.gray700} style={isRTL && styles.rtlText}>
                  {language === 'ar' ? 'إدارة عناوين التوصيل' : 'Gérer mes adresses de livraison'}
                </MayushText>
              </View>
              <MayushIcon name={isRTL ? 'chevron-left' : 'chevron-right'} size={22} color={colors.brand.navy900} />
            </TouchableOpacity>

            <View style={styles.divider} />

            <TouchableOpacity accessibilityRole="button" onPress={onNavigateSecurity || onNavigateSettings} style={[styles.menuRow, isRTL && styles.rowReverse]}>
              <MayushIcon name="shield" size={24} color={colors.brand.navy900} />
              <View style={styles.menuCopy}>
                <MayushText variant="sectionTitle" color={colors.brand.navy900} style={isRTL && styles.rtlText}>
                  {language === 'ar' ? 'الأمان وكلمة المرور' : 'Sécurité et mot de passe'}
                </MayushText>
                <MayushText variant="smallBody" color={colors.neutral.gray700} style={isRTL && styles.rtlText}>
                  {language === 'ar' ? 'تغيير كلمة المرور والجلسات' : 'Mot de passe et connexions'}
                </MayushText>
              </View>
              <MayushIcon name={isRTL ? 'chevron-left' : 'chevron-right'} size={22} color={colors.brand.navy900} />
            </TouchableOpacity>
          </View>

          {/* Preferences Card */}
          <View style={styles.settingsCard}>
            <TouchableOpacity
              accessibilityRole="button"
              onPress={() => setLanguage(language === 'fr' ? 'ar' : 'fr')}
              style={[styles.settingRow, isRTL && styles.rowReverse]}
            >
              <MayushIcon name="arrow-down-up" size={24} color={colors.brand.navy900} />
              <View style={styles.settingCopy}>
                <MayushText variant="sectionTitle" color={colors.brand.navy900} style={isRTL && styles.rtlText}>
                  {language === 'ar' ? 'اللغة' : 'Langue'}
                </MayushText>
                <MayushText variant="smallBody" color={colors.neutral.gray700} style={isRTL && styles.rtlText}>
                  {language === 'ar' ? 'العربية' : 'Français'}
                </MayushText>
              </View>
              <MayushIcon name={isRTL ? 'chevron-left' : 'chevron-right'} size={22} color={colors.brand.navy900} />
            </TouchableOpacity>

            <View style={styles.divider} />

            <TouchableOpacity
              accessibilityRole="button"
              onPress={onNavigateMarketingPreferences}
              style={[styles.settingRow, isRTL && styles.rowReverse]}
            >
              <MayushIcon name="sliders" size={24} color={colors.brand.navy900} />
              <View style={styles.settingCopy}>
                <MayushText variant="sectionTitle" color={colors.brand.navy900} style={isRTL && styles.rtlText}>
                  {language === 'ar' ? 'تفضيلات التسويق' : 'Préférences marketing'}
                </MayushText>
                <MayushText variant="smallBody" color={colors.neutral.gray700} style={isRTL && styles.rtlText}>
                  {language === 'ar' ? 'عروض وتنبيهات السلة' : 'Offres et rappels de panier'}
                </MayushText>
              </View>
              <MayushIcon name={isRTL ? 'chevron-left' : 'chevron-right'} size={22} color={colors.brand.navy900} />
            </TouchableOpacity>

            <View style={styles.divider} />

            <TouchableOpacity
              accessibilityRole="button"
              onPress={onNavigateNotificationManagement}
              style={[styles.settingRow, isRTL && styles.rowReverse]}
            >
              <MayushIcon name="bell" size={24} color={colors.brand.navy900} />
              <View style={styles.settingCopy}>
                <MayushText variant="sectionTitle" color={colors.brand.navy900} style={isRTL && styles.rtlText}>
                  {language === 'ar' ? 'إدارة الإشعارات' : 'Gestion des notifications'}
                </MayushText>
                <MayushText variant="smallBody" color={colors.neutral.gray700} style={isRTL && styles.rtlText}>
                  {language === 'ar' ? 'قنوات وفئات التنبيهات' : 'Canaux et catégories d’alertes'}
                </MayushText>
              </View>
              <MayushIcon name={isRTL ? 'chevron-left' : 'chevron-right'} size={22} color={colors.brand.navy900} />
            </TouchableOpacity>

            <View style={styles.divider} />

            <TouchableOpacity
              accessibilityRole="button"
              onPress={onNavigateHelpSupport}
              style={[styles.settingRow, isRTL && styles.rowReverse]}
            >
              <MayushIcon name="help-circle" size={24} color={colors.brand.navy900} />
              <View style={styles.settingCopy}>
                <MayushText variant="sectionTitle" color={colors.brand.navy900} style={isRTL && styles.rtlText}>
                  {language === 'ar' ? 'المساعدة والدعم' : 'Aide & Support'}
                </MayushText>
                <MayushText variant="smallBody" color={colors.neutral.gray700} style={isRTL && styles.rtlText}>
                  {language === 'ar' ? 'الأسئلة الشائعة ومركز المساعدة' : 'FAQ, centre d\'aide et contact'}
                </MayushText>
              </View>
              <MayushIcon name={isRTL ? 'chevron-left' : 'chevron-right'} size={22} color={colors.brand.navy900} />
            </TouchableOpacity>
          </View>

          {/* Logout Action */}
          <TouchableOpacity
            accessibilityRole="button"
            accessibilityLabel="Se déconnecter"
            onPress={() => (onConfirmLogoutTrigger ? onConfirmLogoutTrigger() : authState.logout())}
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
  }

  // Guest State Experience
  const copy =
    language === 'ar'
      ? {
          title: 'مرحبًا بك في مايووش ديزاين',
          body: 'سجّل الدخول لمتابعة طلباتك وإدارة مفضلتك.',
          login: 'تسجيل الدخول',
          create: 'إنشاء حساب',
          explore: 'متابعة الاستكشاف',
          language: 'اللغة',
          support: 'المساعدة',
        }
      : {
          title: 'Bienvenue chez Mayush Design',
          body: 'Connectez-vous pour suivre vos commandes\net gérer vos favoris.',
          login: 'Se connecter',
          create: 'Créer un compte',
          explore: 'Continuer à explorer',
          language: 'Langue',
          support: 'Aide',
        };

  return (
    <View style={styles.screen} accessibilityLabel={language === 'ar' ? 'حسابي' : 'Mon compte'}>
      <ScrollView showsVerticalScrollIndicator={false} contentContainerStyle={styles.content}>
        <View style={[styles.header, isRTL && styles.rowReverse]}>
          <MayushLogo width={177} height={53} />
          <View style={styles.headerButton}>
            <MayushIcon name="bell" size={28} color={colors.brand.navy900} />
          </View>
        </View>
        <View pointerEvents="none" style={styles.scene}>
          <Image source={ACCOUNT_ARTWORK} resizeMode="contain" style={styles.referenceArtwork} />
        </View>
        <MayushText variant="display" color={colors.brand.navy900} align="center" style={[styles.title, isRTL && styles.rtlText]}>
          {copy.title}
        </MayushText>
        <MayushText variant="body" color={colors.brand.navy700} align="center" style={[styles.body, isRTL && styles.rtlText]}>
          {copy.body}
        </MayushText>
        <TouchableOpacity
          accessibilityRole="button"
          accessibilityLabel={copy.login}
          activeOpacity={0.84}
          onPress={onLogin || onExplore}
          style={styles.primaryButton}
        >
          <MayushIcon name="user" size={27} color={colors.surface.white} />
          <MayushText variant="button" color={colors.surface.white} style={styles.buttonLabel}>
            {copy.login}
          </MayushText>
          <MayushIcon name={isRTL ? 'arrow-left' : 'arrow-right'} size={24} color={colors.surface.white} />
        </TouchableOpacity>
        <TouchableOpacity
          accessibilityRole="button"
          accessibilityLabel={copy.create}
          activeOpacity={0.84}
          onPress={onCreateAccount || onExplore}
          style={styles.outlineButton}
        >
          <MayushIcon name="user" size={27} color={colors.brand.navy900} />
          <MayushText variant="button" color={colors.brand.navy900} style={styles.buttonLabel}>
            {copy.create}
          </MayushText>
          <MayushIcon name={isRTL ? 'arrow-left' : 'arrow-right'} size={24} color={colors.brand.navy900} />
        </TouchableOpacity>
        <TouchableOpacity
          accessibilityRole="button"
          accessibilityLabel={copy.explore}
          activeOpacity={0.84}
          onPress={onExplore}
          style={styles.exploreButton}
        >
          <MayushIcon name="search" size={24} color={colors.brand.navy900} />
          <MayushText variant="button" color={colors.brand.navy900} style={styles.buttonLabel}>
            {copy.explore}
          </MayushText>
          <MayushIcon name={isRTL ? 'arrow-left' : 'arrow-right'} size={24} color={colors.brand.navy900} />
        </TouchableOpacity>
        <View style={styles.settingsCard}>
          <TouchableOpacity
            accessibilityRole="button"
            onPress={() => setLanguage(language === 'fr' ? 'ar' : 'fr')}
          >
            <SettingRow
              icon="arrow-down-up"
              label={copy.language}
              detail={language === 'ar' ? 'العربية' : 'Français'}
              isRTL={isRTL}
            />
          </TouchableOpacity>
          <TouchableOpacity
            accessibilityRole="button"
            onPress={onNavigateHelpSupport}
          >
            <SettingRow icon="shield" label={copy.support} detail={language === 'ar' ? 'مركز المساعدة والدعم' : 'Centre d’aide et support'} isRTL={isRTL} />
          </TouchableOpacity>
          <View style={styles.divider} />
          <TouchableOpacity
            accessibilityRole="button"
            onPress={onNavigateSettings}
          >
            <SettingRow icon="sliders" label={language === 'ar' ? 'الإعدادات' : 'Paramètres'} detail={language === 'ar' ? 'إعدادات التطبيق' : 'Paramètres de l\'application'} isRTL={isRTL} />
          </TouchableOpacity>
        </View>
      </ScrollView>
      <BottomTabBar activeTab="account" onTabPress={(tab) => onNavigateTab?.(tab)} />
    </View>
  );
};

const SettingRow: React.FC<{ icon: MayushIconName; label: string; detail: string; isRTL: boolean }> = ({
  icon,
  label,
  detail,
  isRTL,
}) => (
  <View style={[styles.settingRow, isRTL && styles.rowReverse]}>
    <MayushIcon name={icon} size={26} color={colors.brand.navy900} />
    <View style={styles.settingCopy}>
      <MayushText variant="sectionTitle" color={colors.brand.navy900} style={isRTL && styles.rtlText}>
        {label}
      </MayushText>
      <MayushText variant="smallBody" color={colors.neutral.gray700} style={isRTL && styles.rtlText}>
        {detail}
      </MayushText>
    </View>
    <MayushIcon name={isRTL ? 'chevron-left' : 'chevron-right'} size={24} color={colors.brand.navy900} />
  </View>
);

const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: '#FBF2EF' },
  content: { paddingHorizontal: 22, paddingBottom: 28 },
  rowReverse: { flexDirection: 'row-reverse' },
  rtlText: { writingDirection: 'rtl' },
  header: { minHeight: 74, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' },
  headerButton: { width: 42, height: 42, alignItems: 'center', justifyContent: 'center' },
  scene: { height: 230, alignItems: 'center', justifyContent: 'center' },
  referenceArtwork: { width: '100%', height: '100%' },
  title: { marginHorizontal: 8, fontSize: 26, lineHeight: 33, fontWeight: '700', letterSpacing: -0.35 },
  body: { marginTop: 12, fontSize: 16, lineHeight: 25 },
  primaryButton: {
    height: 56,
    marginTop: 22,
    paddingHorizontal: 20,
    borderRadius: 16,
    backgroundColor: colors.brand.orange500,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
  },
  outlineButton: {
    height: 56,
    marginTop: 12,
    paddingHorizontal: 20,
    borderRadius: 16,
    borderWidth: 1.5,
    borderColor: colors.brand.navy900,
    backgroundColor: colors.surface.white,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
  },
  exploreButton: {
    height: 52,
    marginTop: 12,
    paddingHorizontal: 20,
    borderRadius: 16,
    backgroundColor: '#FFF4E9',
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
  },
  buttonLabel: { fontSize: 18, fontWeight: '700' },
  settingsCard: {
    marginTop: 20,
    paddingHorizontal: 18,
    borderRadius: 20,
    backgroundColor: colors.surface.white,
    borderWidth: 1,
    borderColor: '#F1E8DF',
    shadowColor: '#101D35',
    shadowOpacity: 0.05,
    shadowRadius: 12,
    shadowOffset: { width: 0, height: 4 },
    elevation: 2,
  },
  settingRow: { minHeight: 72, flexDirection: 'row', alignItems: 'center', gap: 15 },
  settingCopy: { flex: 1, gap: 3 },
  divider: { height: 1, backgroundColor: '#EEE7DE' },

  // Authenticated Dashboard Styles
  profileCard: {
    marginTop: 12,
    padding: 16,
    borderRadius: 20,
    backgroundColor: colors.surface.white,
    borderWidth: 1,
    borderColor: '#E7DED3',
  },
  profileHeaderRow: { flexDirection: 'row', alignItems: 'center', gap: 14 },
  avatarCircle: {
    width: 52,
    height: 52,
    borderRadius: 26,
    backgroundColor: colors.brand.navy900,
    alignItems: 'center',
    justifyContent: 'center',
  },
  avatarInitials: { color: colors.surface.white, fontSize: 18, fontWeight: '700' },
  profileInfo: { flex: 1, gap: 2 },
  settingsBadge: { width: 36, height: 36, alignItems: 'center', justifyContent: 'center' },
  progressBanner: {
    marginTop: 14,
    padding: 12,
    borderRadius: 14,
    backgroundColor: colors.surface.cream,
    gap: 8,
  },
  progressTextRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
  progressLabel: { fontWeight: '600' },
  progressCta: { fontWeight: '700' },
  trackBar: { height: 6, borderRadius: 3, backgroundColor: '#E7DED3', overflow: 'hidden' },
  fillBar: { height: '100%', backgroundColor: colors.brand.orange500, borderRadius: 3 },
  shortcutsGrid: { flexDirection: 'row', gap: 10, marginTop: 16 },
  shortcutCard: {
    flex: 1,
    height: 76,
    borderRadius: 16,
    backgroundColor: colors.surface.white,
    borderWidth: 1,
    borderColor: '#E7DED3',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 6,
  },
  shortcutLabel: { fontSize: 12, fontWeight: '700' },
  menuCard: {
    marginTop: 16,
    paddingHorizontal: 16,
    borderRadius: 20,
    backgroundColor: colors.surface.white,
    borderWidth: 1,
    borderColor: '#E7DED3',
  },
  menuRow: { minHeight: 68, flexDirection: 'row', alignItems: 'center', gap: 14 },
  menuCopy: { flex: 1, gap: 2 },
  logoutButton: {
    height: 52,
    marginTop: 20,
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
