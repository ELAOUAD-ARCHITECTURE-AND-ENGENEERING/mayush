import React, { useEffect, useState } from 'react';
import { ScrollView, StyleSheet, TouchableOpacity, View } from 'react-native';
import { authState } from '../../commerce/authState';
import { BottomTabBar, TabKey } from '../../design-system/components/navigation/BottomTabBar';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { colors } from '../../design-system/tokens/colors';
import { spacing } from '../../design-system/tokens/spacing';

export interface AccountSecurityScreenProps {
  onNavigateTab: (tab: TabKey) => void;
  onBack: () => void;
  onNavigateSecurityPrivacyMenu: () => void;
  onNavigateTwoFactor: () => void;
  onNavigateActiveSessions: () => void;
  onNavigateChangePassword: () => void;
  language?: 'fr' | 'ar';
}

export const AccountSecurityScreen: React.FC<AccountSecurityScreenProps> = ({
  onNavigateTab,
  onBack,
  onNavigateSecurityPrivacyMenu,
  onNavigateTwoFactor,
  onNavigateActiveSessions,
  onNavigateChangePassword,
  language = 'fr',
}) => {
  const isRTL = language === 'ar';
  const [is2FAEnabled, setIs2FAEnabled] = useState(authState.isTwoFactorEnabled());
  const [sessionsCount, setSessionsCount] = useState(authState.getActiveSessions().length);

  useEffect(() => {
    const unsub = authState.subscribe(() => {
      setIs2FAEnabled(authState.isTwoFactorEnabled());
      setSessionsCount(authState.getActiveSessions().length);
    });
    return unsub;
  }, []);

  return (
    <View style={styles.screen}>
      {/* Top Navigation Bar */}
      <View style={[styles.header, isRTL && styles.rowReverse]}>
        <TouchableOpacity accessibilityRole="button" onPress={onBack} style={styles.backButton}>
          <MayushIcon name={isRTL ? 'chevron-right' : 'chevron-left'} size={24} color={colors.brand.navy900} />
        </TouchableOpacity>
        <MayushText variant="pageTitle" color={colors.brand.navy900} style={styles.headerTitle}>
          {language === 'ar' ? 'أمان الحساب' : 'Sécurité du compte'}
        </MayushText>
        <View style={styles.headerRightPlaceholder} />
      </View>

      <ScrollView contentContainerStyle={styles.content} showsVerticalScrollIndicator={false}>
        {/* Overall Security Status Banner */}
        <View style={styles.statusCard}>
          <View style={[styles.statusHeaderRow, isRTL && styles.rowReverse]}>
            <View style={styles.shieldIconCircle}>
              <MayushIcon name="shield" size={24} color={colors.semantic.success} />
            </View>
            <View style={styles.statusTextGroup}>
              <MayushText variant="caption" color={colors.brand.navy900} style={[styles.statusLabel, isRTL && styles.rtlText]}>
                {language === 'ar' ? 'مستوى الأمان' : 'Niveau de sécurité'}
              </MayushText>
              <MayushText variant="cardTitle" color={colors.semantic.success} style={isRTL && styles.rtlText}>
                {language === 'ar' ? 'مرتفع وحمي' : 'Élevé & Protégé'}
              </MayushText>
            </View>
          </View>
          <MayushText variant="body" color={colors.neutral.gray700} style={[styles.statusSubtext, isRTL && styles.rtlText]}>
            {language === 'ar'
              ? 'كلمة المرور قوية والجلسات النشطة مراجعة.'
              : 'Votre mot de passe est fort et vos sessions actives sont sécurisées.'}
          </MayushText>
        </View>

        {/* Password Card */}
        <View style={styles.card}>
          <View style={[styles.cardRow, isRTL && styles.rowReverse]}>
            <MayushIcon name="check-circle" size={22} color={colors.brand.orange500} />
            <View style={styles.cardTextGroup}>
              <MayushText variant="cardTitle" color={colors.brand.navy900} style={isRTL && styles.rtlText}>
                {language === 'ar' ? 'كلمة المرور' : 'Mot de passe'}
              </MayushText>
              <MayushText variant="body" color={colors.neutral.gray500} style={isRTL && styles.rtlText}>
                {language === 'ar' ? 'تم التحديث مؤخراً' : 'Dernière modification récente'}
              </MayushText>
            </View>
            <TouchableOpacity accessibilityRole="button" onPress={onNavigateChangePassword} style={styles.actionPill}>
              <MayushText variant="caption" color={colors.brand.orange500} style={styles.pillText}>
                {language === 'ar' ? 'تعديل' : 'Modifier'}
              </MayushText>
            </TouchableOpacity>
          </View>
        </View>

        {/* 2FA Card */}
        <View style={styles.card}>
          <View style={[styles.cardRow, isRTL && styles.rowReverse]}>
            <MayushIcon name="shield" size={22} color={colors.brand.orange500} />
            <View style={styles.cardTextGroup}>
              <MayushText variant="cardTitle" color={colors.brand.navy900} style={isRTL && styles.rtlText}>
                {language === 'ar' ? 'المصادقة الثنائية (2FA)' : 'Validation en 2 étapes'}
              </MayushText>
              <MayushText
                variant="body"
                color={is2FAEnabled ? colors.semantic.success : colors.neutral.gray500}
                style={isRTL && styles.rtlText}
              >
                {is2FAEnabled
                  ? language === 'ar'
                    ? 'مفعلة عبر SMS (+212 6** *** **77)'
                    : 'Activée via SMS (+212 6** *** **77)'
                  : language === 'ar'
                  ? 'غير مفعلة'
                  : 'Désactivée'}
              </MayushText>
            </View>
            <TouchableOpacity accessibilityRole="button" onPress={onNavigateTwoFactor} style={styles.actionPill}>
              <MayushText variant="caption" color={colors.brand.orange500} style={styles.pillText}>
                {is2FAEnabled ? (language === 'ar' ? 'إدارة' : 'Gérer') : language === 'ar' ? 'تفعيل' : 'Activer'}
              </MayushText>
            </TouchableOpacity>
          </View>
        </View>

        {/* Active Sessions Card */}
        <TouchableOpacity
          accessibilityRole="button"
          onPress={onNavigateActiveSessions}
          style={styles.card}
        >
          <View style={[styles.cardRow, isRTL && styles.rowReverse]}>
            <MayushIcon name="grid" size={22} color={colors.brand.orange500} />
            <View style={styles.cardTextGroup}>
              <MayushText variant="cardTitle" color={colors.brand.navy900} style={isRTL && styles.rtlText}>
                {language === 'ar' ? 'الجلسات والأجهزة النشطة' : 'Sessions & appareils actifs'}
              </MayushText>
              <MayushText variant="body" color={colors.neutral.gray500} style={isRTL && styles.rtlText}>
                {language === 'ar'
                  ? `${sessionsCount} أجهزة متصلة حالياً`
                  : `${sessionsCount} appareil${sessionsCount > 1 ? 's' : ''} connecté${sessionsCount > 1 ? 's' : ''}`}
              </MayushText>
            </View>
            <MayushIcon name={isRTL ? 'chevron-left' : 'chevron-right'} size={20} color={colors.neutral.gray500} />
          </View>
        </TouchableOpacity>

        {/* Full Security & Privacy Menu Link */}
        <TouchableOpacity
          accessibilityRole="button"
          onPress={onNavigateSecurityPrivacyMenu}
          style={[styles.fullMenuCard, isRTL && styles.rowReverse]}
        >
          <MayushIcon name="sliders" size={22} color={colors.brand.navy900} />
          <View style={styles.fullMenuTextGroup}>
            <MayushText variant="cardTitle" color={colors.brand.navy900} style={isRTL && styles.rtlText}>
              {language === 'ar' ? 'قائمة الأمان والخصوصية' : 'Menu Sécurité & Confidentialité'}
            </MayushText>
            <MayushText variant="body" color={colors.neutral.gray500} style={isRTL && styles.rtlText}>
              {language === 'ar' ? 'عرض جميع الإعدادات المتقدمة' : 'Voir toutes les options avancées'}
            </MayushText>
          </View>
          <MayushIcon name={isRTL ? 'chevron-left' : 'chevron-right'} size={20} color={colors.brand.navy900} />
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
  content: { padding: spacing.md, paddingBottom: 100, gap: 14 },
  statusCard: {
    padding: 16,
    borderRadius: 18,
    backgroundColor: colors.surface.white,
    borderWidth: 1,
    borderColor: colors.semantic.success,
    gap: 10,
  },
  statusHeaderRow: { flexDirection: 'row', alignItems: 'center', gap: 12 },
  shieldIconCircle: {
    width: 44,
    height: 44,
    borderRadius: 22,
    backgroundColor: '#E8F5E9',
    alignItems: 'center',
    justifyContent: 'center',
  },
  statusTextGroup: { flex: 1, gap: 2 },
  statusLabel: { fontSize: 11, fontWeight: '600', textTransform: 'uppercase' },
  statusSubtext: { fontSize: 13, lineHeight: 18 },
  card: {
    padding: 16,
    borderRadius: 16,
    backgroundColor: colors.surface.white,
    borderWidth: 1,
    borderColor: '#E7DED3',
  },
  cardRow: { flexDirection: 'row', alignItems: 'center', gap: 12 },
  cardTextGroup: { flex: 1, gap: 2 },
  actionPill: {
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 12,
    backgroundColor: colors.surface.cream,
  },
  pillText: { fontSize: 12, fontWeight: '700' },
  fullMenuCard: {
    marginTop: 8,
    padding: 16,
    borderRadius: 16,
    backgroundColor: colors.surface.cream,
    borderWidth: 1,
    borderColor: '#E7DED3',
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
  },
  fullMenuTextGroup: { flex: 1, gap: 2 },
});
