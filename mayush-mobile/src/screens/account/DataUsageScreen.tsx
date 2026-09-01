import React, { useEffect, useState } from 'react';
import { ScrollView, StyleSheet, Switch, TouchableOpacity, View } from 'react-native';
import { accountPreferencesState } from '../../commerce/accountPreferencesState';
import { appSettingsState, ImageQualityOption } from '../../commerce/appSettingsState';
import { BottomTabBar, TabKey } from '../../design-system/components/navigation/BottomTabBar';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { colors } from '../../design-system/tokens/colors';
import { spacing } from '../../design-system/tokens/spacing';

export interface DataUsageScreenProps {
  onNavigateTab?: (tab: TabKey) => void;
  onBack?: () => void;
  onNavigateStorageCache?: () => void;
}

export const DataUsageScreen: React.FC<DataUsageScreenProps> = ({
  onNavigateTab,
  onBack,
  onNavigateStorageCache,
}) => {
  const isRTL = accountPreferencesState.getSelectedLanguage() === 'ar';
  const [dataUsage, setDataUsage] = useState(appSettingsState.getDataUsage());

  useEffect(() => {
    return appSettingsState.subscribe(() => {
      setDataUsage(appSettingsState.getDataUsage());
    });
  }, []);

  const qualityOptions: { id: ImageQualityOption; labelFr: string; labelAr: string; descFr: string; descAr: string }[] = [
    { id: 'standard', labelFr: 'Standard', labelAr: 'قياسي', descFr: 'Équilibre parfait entre qualité d\'image et consommation.', descAr: 'توازن بين الجودة واستهلاك البيانات.' },
    { id: 'high', labelFr: 'Haute Qualité', labelAr: 'جودة عالية', descFr: 'Résolution maximale pour les visuels 3D et galeries.', descAr: 'أعلى دقة للصور ثلاثية الأبعاد ومعرض الأثاث.' },
    { id: 'data-saver', labelFr: 'Économiseur', labelAr: 'توفير البيانات', descFr: 'Compression des images pour économiser votre forfait.', descAr: 'ضغط الصور لتقليل استهلاك رصيد الإنترنت.' },
  ];

  return (
    <View style={styles.container}>
      {/* Header */}
      <View style={[styles.header, isRTL && styles.rtlRow]}>
        <TouchableOpacity style={styles.backButton} onPress={onBack} activeOpacity={0.7}>
          <MayushIcon name={isRTL ? 'chevron-right' : 'chevron-left'} size={24} color={colors.brand.navy900} />
        </TouchableOpacity>
        <MayushText variant="pageTitle" color={colors.brand.navy900} style={styles.headerTitle}>
          {isRTL ? 'استهلاك البيانات' : 'Utilisation des données'}
        </MayushText>
        <View style={{ width: 40 }} />
      </View>

      <ScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
        {/* Image Quality Card */}
        <View style={styles.card}>
          <MayushText variant="sectionTitle" color={colors.brand.navy900} style={[styles.cardLabel, isRTL && styles.rtlText]}>
            {isRTL ? 'جودة عرض الصور' : 'Qualité d\'affichage des images'}
          </MayushText>

          {qualityOptions.map((opt, idx) => (
            <TouchableOpacity
              key={opt.id}
              style={[
                styles.qualityRow,
                isRTL && styles.rtlRow,
                dataUsage.imageQuality === opt.id && styles.qualityRowActive,
              ]}
              onPress={() => appSettingsState.setImageQuality(opt.id)}
              activeOpacity={0.8}
            >
              <View style={styles.radioOuter}>
                {dataUsage.imageQuality === opt.id && <View style={styles.radioInner} />}
              </View>
              <View style={styles.textCol}>
                <MayushText variant="strongBody" color={colors.brand.navy900} style={isRTL && styles.rtlText}>
                  {isRTL ? opt.labelAr : opt.labelFr}
                </MayushText>
                <MayushText variant="smallBody" color={colors.neutral.gray500} style={isRTL && styles.rtlText}>
                  {isRTL ? opt.descAr : opt.descFr}
                </MayushText>
              </View>
            </TouchableOpacity>
          ))}
        </View>

        {/* Network & Data Toggles */}
        <View style={styles.card}>
          <MayushText variant="sectionTitle" color={colors.brand.navy900} style={[styles.cardLabel, isRTL && styles.rtlText]}>
            {isRTL ? 'إعدادات الاتصال والشبكة' : 'Réseau et téléchargements'}
          </MayushText>

          {/* Wi-Fi Only */}
          <View style={[styles.toggleRow, isRTL && styles.rtlRow]}>
            <View style={styles.toggleTextCol}>
              <MayushText variant="strongBody" color={colors.brand.navy900} style={isRTL && styles.rtlText}>
                {isRTL ? 'تنزيل عبر Wi-Fi فقط' : 'Téléchargements Wi-Fi uniquement'}
              </MayushText>
              <MayushText variant="smallBody" color={colors.neutral.gray500} style={isRTL && styles.rtlText}>
                {isRTL ? 'تحديث الكتالوج والكتالوجات ثلاثية الأبعاد عبر الواي فاي حصراً' : 'Télécharger les modèles HD uniquement en Wi-Fi'}
              </MayushText>
            </View>
            <Switch
              value={dataUsage.wifiOnlyDownloads}
              onValueChange={() => appSettingsState.toggleWifiOnlyDownloads()}
              trackColor={{ false: colors.neutral.gray300, true: colors.brand.orange500 }}
              thumbColor={colors.neutral.white}
            />
          </View>

          <View style={styles.divider} />

          {/* Data Saver */}
          <View style={[styles.toggleRow, isRTL && styles.rtlRow]}>
            <View style={styles.toggleTextCol}>
              <MayushText variant="strongBody" color={colors.brand.navy900} style={isRTL && styles.rtlText}>
                {isRTL ? 'وضع توفير رصيد الهاتف' : 'Mode économiseur de données'}
              </MayushText>
              <MayushText variant="smallBody" color={colors.neutral.gray500} style={isRTL && styles.rtlText}>
                {isRTL ? 'تقليل حجم البيانات عند استخدام شبكة المحمول' : 'Réduire automatiquement l\'utilisation sur réseau mobile'}
              </MayushText>
            </View>
            <Switch
              value={dataUsage.dataSaverMode}
              onValueChange={() => appSettingsState.toggleDataSaverMode()}
              trackColor={{ false: colors.neutral.gray300, true: colors.brand.orange500 }}
              thumbColor={colors.neutral.white}
            />
          </View>

          <View style={styles.divider} />

          {/* Auto Play Media */}
          <View style={[styles.toggleRow, isRTL && styles.rtlRow]}>
            <View style={styles.toggleTextCol}>
              <MayushText variant="strongBody" color={colors.brand.navy900} style={isRTL && styles.rtlText}>
                {isRTL ? 'تشغيل الوسائط تلقائياً' : 'Lecture automatique des vidéos'}
              </MayushText>
              <MayushText variant="smallBody" color={colors.neutral.gray500} style={isRTL && styles.rtlText}>
                {isRTL ? 'عرض الفيديوهات والعروض التقديمية تلقائياً' : 'Lire les vidéos de démonstration sans appuyer'}
              </MayushText>
            </View>
            <Switch
              value={dataUsage.autoPlayMedia}
              onValueChange={() => appSettingsState.toggleAutoPlayMedia()}
              trackColor={{ false: colors.neutral.gray300, true: colors.brand.orange500 }}
              thumbColor={colors.neutral.white}
            />
          </View>
        </View>

        {/* Next Card to Storage */}
        <TouchableOpacity
          style={[styles.nextCard, isRTL && styles.rtlRow]}
          onPress={onNavigateStorageCache}
          activeOpacity={0.85}
        >
          <View style={styles.nextIconBox}>
            <MayushIcon name="sliders" size={20} color={colors.brand.navy900} />
          </View>
          <View style={styles.nextTextCol}>
            <MayushText variant="strongBody" color={colors.brand.navy900} style={isRTL && styles.rtlText}>
              {isRTL ? 'إدارة التخزين والذاكرة المؤقتة' : 'Gestion du stockage & cache'}
            </MayushText>
            <MayushText variant="smallBody" color={colors.neutral.gray500} style={isRTL && styles.rtlText}>
              {isRTL ? 'عرض المساحة المستخدمة ومسح Cache' : 'Consulter l\'espace occupé et vider le cache'}
            </MayushText>
          </View>
          <MayushIcon name={isRTL ? 'chevron-left' : 'chevron-right'} size={18} color={colors.neutral.gray500} />
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
  card: {
    backgroundColor: colors.neutral.white, borderRadius: 16, padding: spacing.md,
    borderWidth: 1, borderColor: colors.neutral.gray300, gap: spacing.xs,
  },
  cardLabel: { fontSize: 16, fontWeight: '700', marginBottom: spacing.xs },
  qualityRow: {
    flexDirection: 'row', alignItems: 'center', gap: spacing.md,
    padding: spacing.md, borderRadius: 12, borderWidth: 1, borderColor: colors.neutral.gray300,
    backgroundColor: colors.neutral.gray100, marginVertical: 4,
  },
  qualityRowActive: { borderColor: colors.brand.orange500, backgroundColor: 'rgba(217,116,52,0.06)' },
  radioOuter: {
    width: 20, height: 20, borderRadius: 10, borderWidth: 2, borderColor: colors.brand.navy900,
    alignItems: 'center', justifyContent: 'center',
  },
  radioInner: { width: 10, height: 10, borderRadius: 5, backgroundColor: colors.brand.orange500 },
  textCol: { flex: 1 },
  toggleRow: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', paddingVertical: 6 },
  toggleTextCol: { flex: 1, paddingRight: spacing.sm },
  divider: { height: 1, backgroundColor: colors.neutral.gray300 },
  nextCard: {
    flexDirection: 'row', alignItems: 'center', gap: spacing.md,
    backgroundColor: colors.neutral.white, borderRadius: 16, padding: spacing.md,
    borderWidth: 1, borderColor: colors.neutral.gray300,
  },
  nextIconBox: {
    width: 40, height: 40, borderRadius: 10, backgroundColor: colors.neutral.gray100,
    alignItems: 'center', justifyContent: 'center',
  },
  nextTextCol: { flex: 1 },
  rtlRow: { flexDirection: 'row-reverse' },
  rtlText: { textAlign: 'right' },
});
