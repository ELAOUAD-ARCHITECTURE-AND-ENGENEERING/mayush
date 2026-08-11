import React from 'react';
import { ScrollView, StyleSheet, TouchableOpacity, View } from 'react-native';
import { BottomTabBar, TabKey } from '../../design-system/components/navigation/BottomTabBar';
import { MayushIcon, MayushIconName } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { colors } from '../../design-system/tokens/colors';
import { spacing } from '../../design-system/tokens/spacing';

export interface SecurityPrivacyMenuScreenProps {
  onNavigateTab: (tab: TabKey) => void;
  onBack: () => void;
  onNavigateChangePassword: () => void;
  onNavigateTwoFactor: () => void;
  onNavigateActiveSessions: () => void;
  language?: 'fr' | 'ar';
}

export const SecurityPrivacyMenuScreen: React.FC<SecurityPrivacyMenuScreenProps> = ({
  onNavigateTab,
  onBack,
  onNavigateChangePassword,
  onNavigateTwoFactor,
  onNavigateActiveSessions,
  language = 'fr',
}) => {
  const isRTL = language === 'ar';

  const menuItems: {
    key: string;
    icon: MayushIconName;
    titleFr: string;
    titleAr: string;
    subFr: string;
    subAr: string;
    onPress: () => void;
  }[] = [
    {
      key: 'password',
      icon: 'check-circle',
      titleFr: 'Mot de passe & Sécurité',
      titleAr: 'كلمة المرور والأمان',
      subFr: 'Changer votre mot de passe principal',
      subAr: 'تغيير كلمة المرور الرئيسية الخاصة بك',
      onPress: onNavigateChangePassword,
    },
    {
      key: '2fa',
      icon: 'shield',
      titleFr: 'Authentification à 2 facteurs (2FA)',
      titleAr: 'المصادقة الثنائية (2FA)',
      subFr: 'Sécuriser la connexion avec un code SMS',
      subAr: 'تأمين تسجيل الدخول باستخدام رمز SMS',
      onPress: onNavigateTwoFactor,
    },
    {
      key: 'sessions',
      icon: 'grid',
      titleFr: 'Sessions actives & appareils',
      titleAr: 'الجلسات والأجهزة النشطة',
      subFr: 'Gérer les appareils connectés à votre compte',
      subAr: 'إدارة الأجهزة المتصلة بحسابك',
      onPress: onNavigateActiveSessions,
    },
    {
      key: 'privacy',
      icon: 'info',
      titleFr: 'Confidentialité & Données',
      titleAr: 'الخصوصية والبيانات',
      subFr: 'Gérer le consentement et vos données personnelles',
      subAr: 'إدارة الموافقة وبياناتك الشخصية',
      onPress: () => {},
    },
  ];

  return (
    <View style={styles.screen}>
      {/* Top Header */}
      <View style={[styles.header, isRTL && styles.rowReverse]}>
        <TouchableOpacity accessibilityRole="button" onPress={onBack} style={styles.backButton}>
          <MayushIcon name={isRTL ? 'chevron-right' : 'chevron-left'} size={24} color={colors.brand.navy900} />
        </TouchableOpacity>
        <MayushText variant="pageTitle" color={colors.brand.navy900} style={styles.headerTitle}>
          {language === 'ar' ? 'Sécurité & Confidentialité' : 'Sécurité & Confidentialité'}
        </MayushText>
        <View style={styles.headerRightPlaceholder} />
      </View>

      <ScrollView contentContainerStyle={styles.content} showsVerticalScrollIndicator={false}>
        <MayushText variant="caption" color={colors.neutral.gray500} style={[styles.sectionHeader, isRTL && styles.rtlText]}>
          {language === 'ar' ? 'إعدادات الحساب والأمان' : 'PARAMÈTRES DE SÉCURITÉ'}
        </MayushText>

        <View style={styles.menuContainer}>
          {menuItems.map((item, index) => (
            <TouchableOpacity
              key={item.key}
              accessibilityRole="button"
              onPress={item.onPress}
              style={[
                styles.menuRow,
                isRTL && styles.rowReverse,
                index < menuItems.length - 1 && styles.borderBottom,
              ]}
            >
              <MayushIcon name={item.icon} size={22} color={colors.brand.navy900} />
              <View style={styles.menuTextGroup}>
                <MayushText variant="cardTitle" color={colors.brand.navy900} style={isRTL && styles.rtlText}>
                  {language === 'ar' ? item.titleAr : item.titleFr}
                </MayushText>
                <MayushText variant="body" color={colors.neutral.gray500} style={isRTL && styles.rtlText}>
                  {language === 'ar' ? item.subAr : item.subFr}
                </MayushText>
              </View>
              <MayushIcon name={isRTL ? 'chevron-left' : 'chevron-right'} size={20} color={colors.neutral.gray500} />
            </TouchableOpacity>
          ))}
        </View>

        <MayushText variant="caption" color={colors.neutral.gray500} style={[styles.sectionHeader, isRTL && styles.rtlText]}>
          {language === 'ar' ? 'منطقة الخطر' : 'ZONE DE DANGER'}
        </MayushText>

        <TouchableOpacity accessibilityRole="button" style={[styles.dangerCard, isRTL && styles.rowReverse]}>
          <MayushIcon name="trash-2" size={22} color={colors.semantic.error} />
          <View style={styles.menuTextGroup}>
            <MayushText variant="cardTitle" color={colors.semantic.error} style={isRTL && styles.rtlText}>
              {language === 'ar' ? 'حذف الحساب نهائياً' : 'Supprimer mon compte'}
            </MayushText>
            <MayushText variant="body" color={colors.neutral.gray500} style={isRTL && styles.rtlText}>
              {language === 'ar' ? 'حذف جميع البيانات بشكل دائم' : 'Supprimer définitivement votre compte et vos données'}
            </MayushText>
          </View>
        </TouchableOpacity>
      </ScrollView>

      <BottomTabBar activeTab="account" onTabPress={onNavigateTab} />
    </View>
  );
};

const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: colors.surface.creamLight },
  header: {
    height: 56,
    paddingHorizontal: spacing.md,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    backgroundColor: colors.surface.white,
    borderBottomWidth: 1,
    borderBottomColor: '#E7DED3',
  },
  backButton: { padding: 4 },
  headerTitle: { fontSize: 18, fontWeight: '700' },
  headerRightPlaceholder: { width: 32 },
  rowReverse: { flexDirection: 'row-reverse' },
  rtlText: { writingDirection: 'rtl' },
  content: { padding: spacing.md, paddingBottom: 100, gap: 12 },
  sectionHeader: { fontSize: 11, fontWeight: '700', marginTop: 8 },
  menuContainer: {
    borderRadius: 16,
    backgroundColor: colors.surface.white,
    borderWidth: 1,
    borderColor: '#E7DED3',
    paddingHorizontal: 16,
  },
  menuRow: {
    paddingVertical: 14,
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
  },
  borderBottom: { borderBottomWidth: 1, borderBottomColor: '#F0E8DF' },
  menuTextGroup: { flex: 1, gap: 2 },
  dangerCard: {
    padding: 16,
    borderRadius: 16,
    backgroundColor: '#FFEEEC',
    borderWidth: 1,
    borderColor: '#FFCDD2',
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
  },
});
