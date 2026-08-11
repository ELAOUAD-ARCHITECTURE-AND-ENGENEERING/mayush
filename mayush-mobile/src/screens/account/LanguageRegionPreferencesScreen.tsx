import React, { useEffect, useState } from 'react';
import { ScrollView, StyleSheet, TouchableOpacity, View } from 'react-native';
import { accountPreferencesState, AppLanguage, RegionInfo } from '../../commerce/accountPreferencesState';
import { BottomTabBar, TabKey } from '../../design-system/components/navigation/BottomTabBar';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { colors } from '../../design-system/tokens/colors';
import { spacing } from '../../design-system/tokens/spacing';

export interface LanguageRegionPreferencesScreenProps {
  onNavigateTab: (tab: TabKey) => void;
  onBack: () => void;
  onNavigateLanguageSelection: () => void;
  language?: AppLanguage;
}

const LANGUAGE_LABELS: Record<AppLanguage, { name: string; nativeName: string }> = {
  fr: { name: 'Français', nativeName: 'Français (France)' },
  ar: { name: 'العربية', nativeName: 'العربية (المغرب)' },
  en: { name: 'English', nativeName: 'English (US)' },
};

export const LanguageRegionPreferencesScreen: React.FC<LanguageRegionPreferencesScreenProps> = ({
  onNavigateTab,
  onBack,
  onNavigateLanguageSelection,
  language = 'fr',
}) => {
  const isRTL = language === 'ar';
  const [currentLang, setCurrentLang] = useState<AppLanguage>(accountPreferencesState.getSelectedLanguage());
  const [regionInfo] = useState<RegionInfo>(accountPreferencesState.getRegionInfo());

  useEffect(() => {
    const unsub = accountPreferencesState.subscribe(() => {
      setCurrentLang(accountPreferencesState.getSelectedLanguage());
    });
    return unsub;
  }, []);

  const copy = language === 'ar'
    ? {
        title: 'الغة والمنطقة',
        subtitle: 'تخصيص لغة التطبيق، البلد والعملة المستعملة.',
        langSection: 'اللغة المعتمدة',
        regionSection: 'البلد والمنطقة',
        currencySection: 'العملة والتسعير',
        changeLang: 'تغيير اللغة',
        countryName: 'المغرب (Maroc)',
        currencyName: 'الدرهم المغربي (MAD)',
        notice: 'تضمن تفضيلات المنطقة عرض الأسعار والتوصيل بدقة حسب بلدك.',
      }
    : language === 'en'
    ? {
        title: 'Language & Region',
        subtitle: 'Customize application language, country and currency.',
        langSection: 'Current Language',
        regionSection: 'Country & Region',
        currencySection: 'Currency & Pricing',
        changeLang: 'Change Language',
        countryName: 'Morocco (Maroc)',
        currencyName: 'Moroccan Dirham (MAD)',
        notice: 'Region preferences ensure accurate localized pricing and delivery choices.',
      }
    : {
        title: 'Langue & Région',
        subtitle: 'Personnalisez la langue de l\'application, le pays et la devise.',
        langSection: 'Langue actuelle',
        regionSection: 'Pays & Région',
        currencySection: 'Devise & Tarification',
        changeLang: 'Changer la langue',
        countryName: 'Maroc',
        currencyName: 'Dirham marocain (MAD)',
        notice: 'Les préférences régionales garantissent un affichage exact des prix et des options de livraison.',
      };

  return (
    <View style={styles.screen}>
      {/* Header */}
      <View style={[styles.header, isRTL && styles.rowReverse]}>
        <TouchableOpacity accessibilityRole="button" onPress={onBack} style={styles.backButton}>
          <MayushIcon name={isRTL ? 'chevron-right' : 'chevron-left'} size={24} color={colors.brand.navy900} />
        </TouchableOpacity>
        <MayushText variant="pageTitle" color={colors.brand.navy900} style={styles.headerTitle}>
          {copy.title}
        </MayushText>
        <View style={styles.headerSpacer} />
      </View>

      <ScrollView contentContainerStyle={styles.content} showsVerticalScrollIndicator={false}>
        <View style={[styles.titleRow, isRTL && styles.rowReverse]}>
          <View style={styles.flex1}>
            <MayushText variant="pageTitle" color={colors.brand.navy900} style={[styles.title, isRTL && styles.rtlText]}>
              {copy.title}
            </MayushText>
            <MayushText variant="caption" color={colors.neutral.gray700} style={[styles.subtitle, isRTL && styles.rtlText]}>
              {copy.subtitle}
            </MayushText>
          </View>
          <View style={styles.globeCircle}>
            <MayushIcon name="globe" size={24} color={colors.brand.navy900} />
          </View>
        </View>

        {/* Current Language Card */}
        <TouchableOpacity accessibilityRole="button" onPress={onNavigateLanguageSelection} style={styles.prefCard}>
          <View style={[styles.cardRow, isRTL && styles.rowReverse]}>
            <View style={styles.iconBox}>
              <MayushIcon name="globe" size={20} color={colors.brand.orange500} />
            </View>
            <View style={styles.flex1}>
              <MayushText variant="caption" color={colors.neutral.gray500} style={isRTL && styles.rtlText}>
                {copy.langSection}
              </MayushText>
              <MayushText variant="sectionTitle" color={colors.brand.navy900} style={isRTL && styles.rtlText}>
                {LANGUAGE_LABELS[currentLang]?.nativeName || 'Français'}
              </MayushText>
            </View>
            <View style={[styles.actionPill, isRTL && styles.rowReverse]}>
              <MayushText variant="caption" color={colors.brand.orange500}>
                {copy.changeLang}
              </MayushText>
              <MayushIcon name={isRTL ? 'chevron-left' : 'chevron-right'} size={16} color={colors.brand.orange500} />
            </View>
          </View>
        </TouchableOpacity>

        {/* Region Card */}
        <View style={styles.prefCard}>
          <View style={[styles.cardRow, isRTL && styles.rowReverse]}>
            <View style={styles.iconBox}>
              <MayushIcon name="map-pin" size={20} color={colors.brand.navy900} />
            </View>
            <View style={styles.flex1}>
              <MayushText variant="caption" color={colors.neutral.gray500} style={isRTL && styles.rtlText}>
                {copy.regionSection}
              </MayushText>
              <MayushText variant="sectionTitle" color={colors.brand.navy900} style={isRTL && styles.rtlText}>
                {`${regionInfo.flag} ${copy.countryName}`}
              </MayushText>
            </View>
          </View>
        </View>

        {/* Currency Card */}
        <View style={styles.prefCard}>
          <View style={[styles.cardRow, isRTL && styles.rowReverse]}>
            <View style={styles.iconBox}>
              <MayushIcon name="tag" size={20} color={colors.brand.navy900} />
            </View>
            <View style={styles.flex1}>
              <MayushText variant="caption" color={colors.neutral.gray500} style={isRTL && styles.rtlText}>
                {copy.currencySection}
              </MayushText>
              <MayushText variant="sectionTitle" color={colors.brand.navy900} style={isRTL && styles.rtlText}>
                {copy.currencyName}
              </MayushText>
            </View>
          </View>
        </View>

        {/* Info Notice */}
        <View style={[styles.noticeBox, isRTL && styles.rowReverse]}>
          <MayushIcon name="info" size={16} color={colors.brand.navy900} />
          <MayushText variant="caption" color={colors.neutral.gray700} style={styles.noticeText}>
            {copy.notice}
          </MayushText>
        </View>
      </ScrollView>

      <BottomTabBar activeTab="account" onTabPress={onNavigateTab} />
    </View>
  );
};

const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: colors.surface.white },
  header: {
    height: 56, paddingHorizontal: spacing.md, flexDirection: 'row', alignItems: 'center',
    justifyContent: 'space-between', borderBottomWidth: 1, borderBottomColor: '#E7DED3',
  },
  backButton: { padding: 4 },
  headerTitle: { fontSize: 18, fontWeight: '700' },
  headerSpacer: { width: 32 },
  rowReverse: { flexDirection: 'row-reverse' },
  rtlText: { writingDirection: 'rtl' },
  flex1: { flex: 1 },
  content: { padding: spacing.md, paddingBottom: 100, gap: 14 },
  titleRow: { flexDirection: 'row', gap: 12, alignItems: 'flex-start', marginTop: 4 },
  title: { fontSize: 22, lineHeight: 28 },
  subtitle: { fontSize: 12, marginTop: 2 },
  globeCircle: {
    width: 44, height: 44, borderRadius: 12, alignItems: 'center',
    justifyContent: 'center', backgroundColor: '#FFF7EF',
  },
  prefCard: {
    borderWidth: 1, borderColor: '#F0E3D7', borderRadius: 14, padding: 14,
    backgroundColor: colors.surface.white, shadowColor: colors.brand.navy900,
    shadowOpacity: 0.03, shadowRadius: 5, elevation: 1,
  },
  cardRow: { flexDirection: 'row', alignItems: 'center', gap: 12 },
  iconBox: {
    width: 40, height: 40, borderRadius: 10, backgroundColor: '#FFF6E8',
    alignItems: 'center', justifyContent: 'center',
  },
  actionPill: {
    flexDirection: 'row', alignItems: 'center', gap: 4, paddingHorizontal: 10,
    paddingVertical: 4, borderRadius: 8, backgroundColor: '#FFF6E8',
  },
  noticeBox: {
    flexDirection: 'row', alignItems: 'flex-start', gap: 8, padding: 12,
    borderRadius: 10, backgroundColor: '#F8F4EE', marginTop: 6,
  },
  noticeText: { flex: 1, fontSize: 11, lineHeight: 15 },
});
