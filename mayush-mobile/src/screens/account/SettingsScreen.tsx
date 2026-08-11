import React from 'react';
import { ScrollView, StyleSheet, TouchableOpacity, View } from 'react-native';
import { accountPreferencesState } from '../../commerce/accountPreferencesState';
import { BottomTabBar, TabKey } from '../../design-system/components/navigation/BottomTabBar';
import { MayushIcon, MayushIconName } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { colors } from '../../design-system/tokens/colors';
import { spacing } from '../../design-system/tokens/spacing';

export interface SettingsScreenProps {
  onNavigateTab?: (tab: TabKey) => void;
  onBack?: () => void;
  onNavigateLanguage?: () => void;
  onNavigateNotificationChannels?: () => void;
  onNavigateMarketingPreferences?: () => void;
  onNavigateSilentHours?: () => void;
  onNavigateHelpCenter?: () => void;
  onNavigateAboutApp?: () => void;
  onNavigateAccessibility?: () => void;
  onNavigateAppPermissions?: () => void;
  onNavigateDataUsage?: () => void;
  onNavigateStorageCache?: () => void;
  onNavigateOfflineMode?: () => void;
  onNavigateLegalPrivacy?: () => void;
}

export const SettingsScreen: React.FC<SettingsScreenProps> = ({
  onNavigateTab,
  onBack,
  onNavigateLanguage,
  onNavigateNotificationChannels,
  onNavigateMarketingPreferences,
  onNavigateSilentHours,
  onNavigateHelpCenter,
  onNavigateAboutApp,
  onNavigateAccessibility,
  onNavigateAppPermissions,
  onNavigateDataUsage,
  onNavigateStorageCache,
  onNavigateOfflineMode,
  onNavigateLegalPrivacy,
}) => {
  const isRTL = accountPreferencesState.getSelectedLanguage() === 'ar';
  const selectedLang = accountPreferencesState.getSelectedLanguage();

  const settingsSections = [
    {
      title: isRTL ? 'تفضيلات اللغة والعرض' : "Préférences d'Affichage & Langue",
      rows: [
        {
          id: 'language',
          icon: 'globe' as MayushIconName,
          label: isRTL ? 'اللغة والإقليم' : 'Langue & Région',
          detail: selectedLang === 'ar' ? 'العربية (المغرب)' : 'Français (Maroc)',
          onPress: onNavigateLanguage,
          isImplemented: true,
        },
      ],
    },
    {
      title: isRTL ? 'الإشعارات والتواصل' : 'Notifications & Communication',
      rows: [
        {
          id: 'notification-channels',
          icon: 'bell' as MayushIconName,
          label: isRTL ? 'قنوات الإشعارات' : 'Canaux de notification',
          detail: isRTL ? 'البريد، الرسائل النصية، الإشعارات' : 'Email, SMS, Push',
          onPress: onNavigateNotificationChannels,
          isImplemented: true,
        },
        {
          id: 'marketing',
          icon: 'sliders' as MayushIconName,
          label: isRTL ? 'تفضيلات التسويق' : 'Préférences marketing',
          detail: isRTL ? 'تنبيهات السلة والعروض' : 'Rappels de panier et offres',
          onPress: onNavigateMarketingPreferences,
          isImplemented: true,
        },
        {
          id: 'silent-hours',
          icon: 'clock' as MayushIconName,
          label: isRTL ? 'ساعات الهدوء (عدم الإزعاج)' : 'Mode Ne Pas Déranger',
          detail: isRTL ? 'جدولة التنبيهات الصامتة' : 'Heures silencieuses',
          onPress: onNavigateSilentHours,
          isImplemented: true,
        },
      ],
    },
    {
      title: isRTL ? 'استخدام البيانات والتخزين' : 'Données & Stockage',
      rows: [
        {
          id: 'data-usage',
          icon: 'box' as MayushIconName,
          label: isRTL ? 'استخدام البيانات وجودة الصور' : 'Utilisation des données',
          detail: isRTL ? 'جودة عالية (Wi-Fi)' : 'Qualité des images',
          onPress: onNavigateDataUsage,
          isImplemented: true,
        },
        {
          id: 'storage',
          icon: 'trash-2' as MayushIconName,
          label: isRTL ? 'التخزين المؤقت والذاكرة' : 'Gestion du stockage',
          detail: isRTL ? 'مسح الذاكرة المؤقتة' : 'Vider le cache',
          onPress: onNavigateStorageCache,
          isImplemented: true,
        },
        {
          id: 'offline',
          icon: 'grid' as MayushIconName,
          label: isRTL ? 'الوضع غير المتصل بالإنترنت' : 'Mode hors-ligne & limitations',
          detail: isRTL ? 'عرض الميزات المتاحة بدون شبكة' : 'Tester le mode sans réseau',
          onPress: onNavigateOfflineMode,
          isImplemented: true,
        },
      ],
    },
    {
      title: isRTL ? 'المساعدة والمعلومات' : 'Assistance & Informations',
      rows: [
        {
          id: 'help-center',
          icon: 'life-buoy' as MayushIconName,
          label: isRTL ? 'مركز المساعدة والأسئلة الشائعة' : 'Centre d\'Aide & FAQ',
          detail: isRTL ? 'الأسئلة والطلبات والدعم' : 'FAQ, demandes et contact',
          onPress: onNavigateHelpCenter,
          isImplemented: true,
        },
        {
          id: 'about',
          icon: 'info' as MayushIconName,
          label: isRTL ? 'حول التطبيق' : 'À propos de l\'application',
          detail: 'Mayush Mobile v1.0.0',
          onPress: onNavigateAboutApp,
          isImplemented: true,
        },
        {
          id: 'accessibility',
          icon: 'user' as MayushIconName,
          label: isRTL ? 'إمكانية الوصول والتباين' : 'Accessibilité & Contraste',
          detail: isRTL ? 'حجم الخط والتباين' : 'Taille de texte & contraste',
          onPress: onNavigateAccessibility,
          isImplemented: true,
        },
        {
          id: 'permissions',
          icon: 'shield' as MayushIconName,
          label: isRTL ? 'أذونات التطبيق' : 'Autorisations de l\'application',
          detail: isRTL ? 'الكاميرا والصور والموقع' : 'Caméra, photos, localisation',
          onPress: onNavigateAppPermissions,
          isImplemented: true,
        },
        {
          id: 'legal',
          icon: 'file-text' as MayushIconName,
          label: isRTL ? 'الشروط والخصوصية' : 'Mentions légales & Confidentialité',
          detail: isRTL ? 'سياسة الخصوصية والشروط' : 'CGU et politique de données',
          onPress: onNavigateLegalPrivacy,
          isImplemented: true,
        },
      ],
    },
  ];

  return (
    <View style={styles.container}>
      {/* Header */}
      <View style={[styles.header, isRTL && styles.rtlRow]}>
        <TouchableOpacity style={styles.backButton} onPress={onBack} activeOpacity={0.7}>
          <MayushIcon name={isRTL ? 'chevron-right' : 'chevron-left'} size={24} color={colors.brand.navy900} />
        </TouchableOpacity>
        <MayushText variant="pageTitle" color={colors.brand.navy900} style={styles.headerTitle}>
          {isRTL ? 'إعدادات التطبيق' : "Paramètres de l'application"}
        </MayushText>
        <View style={{ width: 40 }} />
      </View>

      <ScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
        {settingsSections.map((section, idx) => (
          <View key={idx} style={styles.sectionBlock}>
            <MayushText variant="sectionTitle" color={colors.brand.navy900} style={[styles.sectionHeader, isRTL && styles.rtlText]}>
              {section.title}
            </MayushText>

            <View style={styles.card}>
              {section.rows.map((row, rIdx) => (
                <React.Fragment key={row.id}>
                  {rIdx > 0 && <View style={styles.divider} />}
                  <TouchableOpacity
                    style={[styles.menuRow, isRTL && styles.rtlRow]}
                    onPress={row.onPress}
                    activeOpacity={0.7}
                  >
                    <View style={styles.iconBox}>
                      <MayushIcon name={row.icon} size={20} color={colors.brand.navy900} />
                    </View>
                    <View style={styles.rowTextCol}>
                      <MayushText variant="strongBody" color={colors.brand.navy900} style={isRTL && styles.rtlText}>
                        {row.label}
                      </MayushText>
                      <MayushText variant="smallBody" color={colors.neutral.gray500} style={isRTL && styles.rtlText}>
                        {row.detail}
                      </MayushText>
                    </View>
                    {!row.isImplemented && (
                      <View style={styles.upcomingBadge}>
                        <MayushText variant="caption" color={colors.brand.orange500}>
                          {isRTL ? 'قريباً' : 'Bientôt'}
                        </MayushText>
                      </View>
                    )}
                    <MayushIcon name={isRTL ? 'chevron-left' : 'chevron-right'} size={18} color={colors.neutral.gray500} />
                  </TouchableOpacity>
                </React.Fragment>
              ))}
            </View>
          </View>
        ))}
      </ScrollView>

      <BottomTabBar activeTab="account" onTabPress={(tab) => onNavigateTab?.(tab)} />
    </View>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: colors.neutral.gray100 },
  header: {
    height: 56, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between',
    paddingHorizontal: spacing.md, backgroundColor: colors.neutral.white,
    borderBottomWidth: 1, borderBottomColor: colors.neutral.gray300,
  },
  headerTitle: { fontSize: 18, fontWeight: '700' },
  backButton: { width: 40, height: 40, alignItems: 'center', justifyContent: 'center' },
  scrollContent: { padding: spacing.md, gap: spacing.md, paddingBottom: 100 },
  sectionBlock: { gap: spacing.xs },
  sectionHeader: { fontSize: 15, fontWeight: '700', paddingHorizontal: 4 },
  card: {
    backgroundColor: colors.neutral.white, borderRadius: 16,
    borderWidth: 1, borderColor: colors.neutral.gray300, overflow: 'hidden',
  },
  menuRow: {
    flexDirection: 'row', alignItems: 'center', gap: spacing.sm,
    padding: spacing.md,
  },
  iconBox: {
    width: 36, height: 36, borderRadius: 10, backgroundColor: colors.neutral.gray100,
    alignItems: 'center', justifyContent: 'center',
  },
  rowTextCol: { flex: 1 },
  divider: { height: 1, backgroundColor: colors.neutral.gray300, marginHorizontal: spacing.md },
  upcomingBadge: {
    backgroundColor: 'rgba(217,116,52,0.1)', paddingHorizontal: 8, paddingVertical: 2,
    borderRadius: 6, marginRight: 4,
  },
  rtlRow: { flexDirection: 'row-reverse' },
  rtlText: { textAlign: 'right' },
});
