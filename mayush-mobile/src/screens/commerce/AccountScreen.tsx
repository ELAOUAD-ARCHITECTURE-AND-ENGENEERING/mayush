import React from 'react';
import { Image, ScrollView, StyleSheet, TouchableOpacity, View } from 'react-native';
import { MayushLogo } from '../../design-system/components/brand/MayushLogo';
import { BottomTabBar, TabKey } from '../../design-system/components/navigation/BottomTabBar';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { useTheme } from '../../design-system/theme/useTheme';
import { colors } from '../../design-system/tokens/colors';

const ACCOUNT_ARTWORK = require('../../../assets/illustrations/account-guest-scene.png');

export interface AccountScreenProps { onNavigateTab?: (tab: TabKey) => void; onExplore?: () => void; }

export const AccountScreen: React.FC<AccountScreenProps> = ({ onNavigateTab, onExplore }) => {
  const { isRTL, language } = useTheme();
  const copy = language === 'ar'
    ? { title: '\u0645\u0631\u062d\u0628\u064b\u0627 \u0628\u0643 \u0641\u064a \u0645\u0627\u064a\u0648\u0634 \u062f\u064a\u0632\u0627\u064a\u0646', body: '\u0633\u062c\u0651\u0644 \u0627\u0644\u062f\u062e\u0648\u0644 \u0644\u0645\u062a\u0627\u0628\u0639\u0629 \u0637\u0644\u0628\u0627\u062a\u0643 \u0648\u0625\u062f\u0627\u0631\u0629 \u0645\u0641\u0636\u0644\u062a\u0643.', login: '\u062a\u0633\u062c\u064a\u0644 \u0627\u0644\u062f\u062e\u0648\u0644', create: '\u0625\u0646\u0634\u0627\u0621 \u062d\u0633\u0627\u0628', explore: '\u0645\u062a\u0627\u0628\u0639\u0629 \u0627\u0644\u0627\u0633\u062a\u0643\u0634\u0627\u0641', language: '\u0627\u0644\u0644\u063a\u0629', support: '\u0627\u0644\u0645\u0633\u0627\u0639\u062f\u0629' }
    : { title: 'Bienvenue chez Mayush Design', body: 'Connectez-vous pour suivre vos commandes\net g\u00e9rer vos favoris.', login: 'Se connecter', create: 'Cr\u00e9er un compte', explore: 'Continuer \u00e0 explorer', language: 'Langue', support: 'Aide' };

  return (
    <View style={styles.screen} accessibilityLabel={language === 'ar' ? '\u062d\u0633\u0627\u0628\u064a' : 'Mon compte'}>
      <ScrollView showsVerticalScrollIndicator={false} contentContainerStyle={styles.content}>
        <View style={[styles.header, isRTL && styles.rowReverse]}><MayushLogo width={177} height={53} /><View style={styles.headerButton}><MayushIcon name="bell" size={28} color={colors.brand.navy900} /></View></View>
        <View pointerEvents="none" style={styles.scene}><Image source={ACCOUNT_ARTWORK} resizeMode="contain" style={styles.referenceArtwork} /></View>
        <MayushText variant="display" color={colors.brand.navy900} align="center" style={[styles.title, isRTL && styles.rtlText]}>{copy.title}</MayushText>
        <MayushText variant="body" color={colors.brand.navy700} align="center" style={[styles.body, isRTL && styles.rtlText]}>{copy.body}</MayushText>
        <TouchableOpacity accessibilityRole="button" accessibilityLabel={copy.login} activeOpacity={0.84} onPress={onExplore} style={styles.primaryButton}><MayushIcon name="user" size={27} color={colors.surface.white} /><MayushText variant="button" color={colors.surface.white} style={styles.buttonLabel}>{copy.login}</MayushText><MayushIcon name={isRTL ? 'arrow-left' : 'arrow-right'} size={24} color={colors.surface.white} /></TouchableOpacity>
        <TouchableOpacity accessibilityRole="button" accessibilityLabel={copy.create} activeOpacity={0.84} onPress={onExplore} style={styles.outlineButton}><MayushIcon name="user" size={27} color={colors.brand.navy900} /><MayushText variant="button" color={colors.brand.navy900} style={styles.buttonLabel}>{copy.create}</MayushText><MayushIcon name={isRTL ? 'arrow-left' : 'arrow-right'} size={24} color={colors.brand.navy900} /></TouchableOpacity>
        <TouchableOpacity accessibilityRole="button" accessibilityLabel={copy.explore} activeOpacity={0.84} onPress={onExplore} style={styles.exploreButton}><MayushIcon name="search" size={24} color={colors.brand.navy900} /><MayushText variant="button" color={colors.brand.navy900} style={styles.buttonLabel}>{copy.explore}</MayushText><MayushIcon name={isRTL ? 'arrow-left' : 'arrow-right'} size={24} color={colors.brand.navy900} /></TouchableOpacity>
        <View style={styles.settingsCard}><SettingRow icon="arrow-down-up" label={copy.language} detail={language === 'ar' ? '\u0627\u0644\u0639\u0631\u0628\u064a\u0629' : 'Fran\u00e7ais'} isRTL={isRTL} /><View style={styles.divider} /><SettingRow icon="shield" label={copy.support} detail={language === 'ar' ? '\u0645\u0631\u0643\u0632 \u0627\u0644\u0645\u0633\u0627\u0639\u062f\u0629 \u0648\u0627\u0644\u062f\u0639\u0645' : 'Centre d\u2019aide et support'} isRTL={isRTL} /></View>
      </ScrollView>
      <BottomTabBar activeTab="account" onTabPress={(tab) => onNavigateTab?.(tab)} />
    </View>
  );
};

const SettingRow: React.FC<{ icon: 'arrow-down-up' | 'shield'; label: string; detail: string; isRTL: boolean }> = ({ icon, label, detail, isRTL }) => <View style={[styles.settingRow, isRTL && styles.rowReverse]}><MayushIcon name={icon} size={26} color={colors.brand.navy900} /><View style={styles.settingCopy}><MayushText variant="sectionTitle" color={colors.brand.navy900} style={isRTL && styles.rtlText}>{label}</MayushText><MayushText variant="smallBody" color={colors.neutral.gray700} style={isRTL && styles.rtlText}>{detail}</MayushText></View><MayushIcon name={isRTL ? 'chevron-left' : 'chevron-right'} size={24} color={colors.brand.navy900} /></View>;

const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: '#FBF2EF' }, content: { paddingHorizontal: 22, paddingBottom: 28 }, rowReverse: { flexDirection: 'row-reverse' }, rtlText: { writingDirection: 'rtl' },
  header: { minHeight: 84, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' }, headerButton: { width: 42, height: 42, alignItems: 'center', justifyContent: 'center' },
  scene: { height: 258, alignItems: 'center', justifyContent: 'center' }, referenceArtwork: { width: '100%', height: '100%' },
  title: { marginHorizontal: 8, fontSize: 28, lineHeight: 35, fontWeight: '700', letterSpacing: -0.35 }, body: { marginTop: 15, fontSize: 17, lineHeight: 27 }, primaryButton: { height: 61, marginTop: 27, paddingHorizontal: 20, borderRadius: 17, backgroundColor: colors.brand.orange500, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' }, outlineButton: { height: 61, marginTop: 13, paddingHorizontal: 20, borderRadius: 17, borderWidth: 1.5, borderColor: colors.brand.navy900, backgroundColor: colors.surface.white, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' }, exploreButton: { height: 57, marginTop: 13, paddingHorizontal: 20, borderRadius: 17, backgroundColor: '#FFF4E9', flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' }, buttonLabel: { fontSize: 19, fontWeight: '700' }, settingsCard: { marginTop: 22, paddingHorizontal: 18, borderRadius: 20, backgroundColor: colors.surface.white, borderWidth: 1, borderColor: '#F1E8DF', shadowColor: '#101D35', shadowOpacity: 0.05, shadowRadius: 12, shadowOffset: { width: 0, height: 4 }, elevation: 2 }, settingRow: { minHeight: 80, flexDirection: 'row', alignItems: 'center', gap: 15 }, settingCopy: { flex: 1, gap: 3 }, divider: { height: 1, backgroundColor: '#EEE7DE' },
});
