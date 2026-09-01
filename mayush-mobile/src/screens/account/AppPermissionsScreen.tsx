import React, { useEffect, useState } from 'react';
import { ScrollView, StyleSheet, Switch, TouchableOpacity, View } from 'react-native';
import { accountPreferencesState } from '../../commerce/accountPreferencesState';
import { appSettingsState, AppPermissionSettings, PermissionStatus } from '../../commerce/appSettingsState';
import { BottomTabBar, TabKey } from '../../design-system/components/navigation/BottomTabBar';
import { MayushIcon, MayushIconName } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { colors } from '../../design-system/tokens/colors';
import { spacing } from '../../design-system/tokens/spacing';

export interface AppPermissionsScreenProps {
  onNavigateTab?: (tab: TabKey) => void;
  onBack?: () => void;
  onNavigateDataUsage?: () => void;
}

export const AppPermissionsScreen: React.FC<AppPermissionsScreenProps> = ({
  onNavigateTab,
  onBack,
}) => {
  const isRTL = accountPreferencesState.getSelectedLanguage() === 'ar';
  const [permissions, setPermissions] = useState<AppPermissionSettings>(appSettingsState.getPermissions());

  useEffect(() => {
    return appSettingsState.subscribe(() => {
      setPermissions(appSettingsState.getPermissions());
    });
  }, []);

  const permissionItems: {
    key: keyof AppPermissionSettings;
    icon: MayushIconName;
    titleFr: string;
    titleAr: string;
    descFr: string;
    descAr: string;
  }[] = [
    {
      key: 'camera',
      icon: 'camera',
      titleFr: 'Appareil photo (Caméra)',
      titleAr: 'الكاميرا والتقاط الصور',
      descFr: 'Nécessaire pour scanner des produits et prévisualiser l\'ar-room 3D.',
      descAr: 'مطلوب لمسح المنتجات ومعاينة الغرفة ثلاثية الأبعاد.',
    },
    {
      key: 'photos',
      icon: 'bookmark',
      titleFr: 'Photos & Galerie',
      titleAr: 'الصور ومعرض الملفات',
      descFr: 'Permet de sauvegarder vos inspirations et télécharger une photo de profil.',
      descAr: 'حفظ الهام الديكور وتحديث صورة الحساب.',
    },
    {
      key: 'location',
      icon: 'map-pin',
      titleFr: 'Géolocalisation',
      titleAr: 'الموقع الجغرافي',
      descFr: 'Calcul automatique des frais de livraison et détection du magasin Mayush le plus proche.',
      descAr: 'حساب رسوم التوصيل تلقائياً وتحديد أقرب فرع.',
    },
    {
      key: 'notifications',
      icon: 'bell',
      titleFr: 'Notifications Push',
      titleAr: 'الإشعارات والتنبيهات',
      descFr: 'Suivi de commande en temps réel et offres exclusives réservées aux membres.',
      descAr: 'متابعة الطلبات وتنبيهات العروض الحصرية.',
    },
  ];

  const getStatusBadge = (status: PermissionStatus) => {
    switch (status) {
      case 'granted':
        return { labelFr: 'Autorisé', labelAr: 'مسموح', color: colors.semantic.success, bg: colors.semantic.successBackground };
      case 'denied':
        return { labelFr: 'Refusé', labelAr: 'مرفوض', color: colors.semantic.error, bg: colors.semantic.errorBackground };
      default:
        return { labelFr: 'Non demandé', labelAr: 'غير مطلوب', color: colors.neutral.gray500, bg: colors.neutral.gray300 };
    }
  };

  return (
    <View style={styles.container}>
      {/* Header */}
      <View style={[styles.header, isRTL && styles.rtlRow]}>
        <TouchableOpacity style={styles.backButton} onPress={onBack} activeOpacity={0.7}>
          <MayushIcon name={isRTL ? 'chevron-right' : 'chevron-left'} size={24} color={colors.brand.navy900} />
        </TouchableOpacity>
        <MayushText variant="pageTitle" color={colors.brand.navy900} style={styles.headerTitle}>
          {isRTL ? 'أذونات التطبيق' : 'Autorisations l\'application'}
        </MayushText>
        <View style={{ width: 40 }} />
      </View>

      <ScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
        {/* Notice Banner */}
        <View style={styles.noticeCard}>
          <MayushIcon name="info" size={20} color={colors.brand.orange500} />
          <MayushText variant="smallBody" color={colors.neutral.gray700} style={[styles.noticeText, isRTL && styles.rtlText]}>
            {isRTL
              ? 'يتم التحكم في الأذونات الفعلية من خلال إعدادات نظام الجهاز (Android/iOS). يمكنك تفعيل أو تعطيل الأذونات هنا لأغراض العرض والتجربة.'
              : 'Les autorisations réelles sont gérées par les paramètres système de votre appareil. Vous pouvez basculer les états ci-dessous pour tester l\'interface.'}
          </MayushText>
        </View>

        {/* Permissions List */}
        <View style={styles.card}>
          <MayushText variant="sectionTitle" color={colors.brand.navy900} style={[styles.cardLabel, isRTL && styles.rtlText]}>
            {isRTL ? 'الأذونات المطلوبة' : 'Autorisations requises'}
          </MayushText>

          {permissionItems.map((item, idx) => {
            const statusInfo = getStatusBadge(permissions[item.key]);
            const isGranted = permissions[item.key] === 'granted';

            return (
              <React.Fragment key={item.key}>
                {idx > 0 && <View style={styles.divider} />}
                <View style={[styles.itemRow, isRTL && styles.rtlRow]}>
                  <View style={styles.iconBox}>
                    <MayushIcon name={item.icon} size={20} color={colors.brand.navy900} />
                  </View>

                  <View style={styles.textCol}>
                    <View style={[styles.titleBadgeRow, isRTL && styles.rtlRow]}>
                      <MayushText variant="strongBody" color={colors.brand.navy900} style={isRTL && styles.rtlText}>
                        {isRTL ? item.titleAr : item.titleFr}
                      </MayushText>
                      <View style={[styles.badge, { backgroundColor: statusInfo.bg }]}>
                        <MayushText variant="caption" color={statusInfo.color}>
                          {isRTL ? statusInfo.labelAr : statusInfo.labelFr}
                        </MayushText>
                      </View>
                    </View>
                    <MayushText variant="smallBody" color={colors.neutral.gray500} style={[styles.descText, isRTL && styles.rtlText]}>
                      {isRTL ? item.descAr : item.descFr}
                    </MayushText>
                  </View>

                  <Switch
                    value={isGranted}
                    onValueChange={() => appSettingsState.togglePermission(item.key)}
                    trackColor={{ false: colors.neutral.gray300, true: colors.brand.orange500 }}
                    thumbColor={colors.neutral.white}
                  />
                </View>
              </React.Fragment>
            );
          })}
        </View>

        {/* Open System Settings Button */}
        <TouchableOpacity
          style={[styles.systemButton, isRTL && styles.rtlRow]}
          onPress={() => {}}
          activeOpacity={0.85}
        >
          <MayushIcon name="sliders" size={18} color={colors.brand.navy900} />
          <MayushText variant="button" color={colors.brand.navy900}>
            {isRTL ? 'فتح إعدادات الجهاز النظامية' : 'Ouvrir les paramètres système'}
          </MayushText>
        </TouchableOpacity>
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
  noticeCard: {
    flexDirection: 'row', gap: spacing.sm, alignItems: 'flex-start',
    backgroundColor: 'rgba(217,116,52,0.08)', borderRadius: 14, padding: spacing.md,
    borderWidth: 1, borderColor: 'rgba(217,116,52,0.2)',
  },
  noticeText: { flex: 1, lineHeight: 20 },
  card: {
    backgroundColor: colors.neutral.white, borderRadius: 16, padding: spacing.md,
    borderWidth: 1, borderColor: colors.neutral.gray300, gap: spacing.xs,
  },
  cardLabel: { fontSize: 16, fontWeight: '700', marginBottom: spacing.xs },
  itemRow: { flexDirection: 'row', alignItems: 'center', gap: spacing.sm, paddingVertical: 6 },
  iconBox: {
    width: 36, height: 36, borderRadius: 10, backgroundColor: colors.neutral.gray100,
    alignItems: 'center', justifyContent: 'center',
  },
  textCol: { flex: 1, paddingRight: spacing.xs },
  titleBadgeRow: { flexDirection: 'row', alignItems: 'center', gap: spacing.xs, flexWrap: 'wrap', marginBottom: 2 },
  badge: { paddingHorizontal: 8, paddingVertical: 2, borderRadius: 6 },
  descText: { lineHeight: 18 },
  divider: { height: 1, backgroundColor: colors.neutral.gray300 },
  systemButton: {
    flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: spacing.xs,
    backgroundColor: colors.neutral.white, borderRadius: 14, padding: spacing.md,
    borderWidth: 1, borderColor: colors.neutral.gray300,
  },
  rtlRow: { flexDirection: 'row-reverse' },
  rtlText: { textAlign: 'right' },
});
