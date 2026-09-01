import React, { useState } from 'react';
import { View, StyleSheet, TouchableOpacity, useWindowDimensions } from 'react-native';
import { useTheme } from '../../design-system/theme/useTheme';
import { MayushLogo } from '../../design-system/components/brand/MayushLogo';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { colors } from '../../design-system/tokens/colors';
import { MvpAppLanguage } from '../../contracts/api/dto';

export interface LanguageSelectionScreenProps { onContinue?: (language: MvpAppLanguage) => void; }

export const LanguageSelectionScreen: React.FC<LanguageSelectionScreenProps> = ({ onContinue }) => {
  const { language, setLanguage } = useTheme();
  const [selected, setSelected] = useState<MvpAppLanguage>(language);
  const choose = (next: MvpAppLanguage) => { setSelected(next); setLanguage(next); };
  const arabic = selected === 'ar';
  const { height } = useWindowDimensions();
  const compact = height < 760;

  return (
    <View style={styles.screen}>
      <View pointerEvents="none" style={styles.arch} />
      <View pointerEvents="none" style={styles.lampLine}><View style={styles.lampShade} /></View>
      <View style={[styles.content, compact && styles.contentCompact]}>
        <MayushLogo width={190} height={55} />
        <View style={[styles.titleBlock, compact && styles.titleBlockCompact]}>
          <MayushText variant="display" color={colors.brand.navy900} align="center" style={styles.title}>Choisissez votre langue</MayushText>
          <MayushText variant="display" color={colors.brand.navy900} align="center" style={styles.arabicTitle}>{'\u0627\u062e\u062a\u0631 \u0644\u063a\u062a\u0643'}</MayushText>
          <View style={styles.sparkleDivider}><View style={styles.dividerLine} /><MayushText variant="smallBody" color={colors.brand.orange500}>✦</MayushText><View style={styles.dividerLine} /></View>
          <MayushText variant="body" color={colors.neutral.gray700} align="center" style={styles.description}>Sélectionnez votre langue préférée pour{`\n`}profiter pleinement de l’expérience Mayush.</MayushText>
          <MayushText variant="body" color={colors.neutral.gray700} align="center" style={styles.description}>{'\u0627\u062e\u062a\u0631 \u0644\u063a\u062a\u0643 \u0627\u0644\u0645\u0641\u0636\u0644\u0629 \u0644\u0644\u0627\u0633\u062a\u0645\u062a\u0627\u0639 \u0628\u062a\u062c\u0631\u0628\u0629 \u0645\u0627\u064a\u0648\u0634.'}</MayushText>
        </View>

        <View style={[styles.options, compact && styles.optionsCompact]}>
          <LanguageOption label="Français" active={selected === 'fr'} onPress={() => choose('fr')} flag="fr" />
          <LanguageOption label={'\u0627\u0644\u0639\u0631\u0628\u064a\u0629'} active={selected === 'ar'} onPress={() => choose('ar')} flag="ar" />
        </View>

        <TouchableOpacity style={[styles.continue, compact && styles.continueCompact]} activeOpacity={0.82} onPress={() => onContinue?.(selected)}>
          <MayushText variant="button" color={colors.surface.white} style={styles.continueLabel}>{arabic ? '\u0645\u062a\u0627\u0628\u0639\u0629' : 'Continuer'}</MayushText>
          <MayushIcon name={arabic ? 'arrow-left' : 'arrow-right'} size={28} color={colors.surface.white} />
        </TouchableOpacity>
        <View style={[styles.securityLine, compact && styles.securityLineCompact]}><MayushIcon name="shield" size={20} color={colors.neutral.gray700} /><MayushText variant="smallBody" color={colors.neutral.gray700}>{arabic ? '\u0644\u063a\u062a\u0643\u060c \u062a\u062c\u0631\u0628\u062a\u0643.' : 'Votre expérience, votre langue.'}</MayushText></View>
      </View>
      <View pointerEvents="none" style={styles.wave} />
    </View>
  );
};

const LanguageOption: React.FC<{ label: string; active: boolean; onPress: () => void; flag: 'fr' | 'ar' }> = ({ label, active, onPress, flag }) => (
  <TouchableOpacity activeOpacity={0.82} onPress={onPress} style={[styles.option, active && styles.optionActive]}>
    <View style={[styles.flag, flag === 'ar' ? styles.moroccoFlag : styles.frenchFlag]}>
      {flag === 'fr' ? <><View style={[styles.stripe, { backgroundColor: '#173A79' }]} /><View style={[styles.stripe, { backgroundColor: '#FFFFFF' }]} /><View style={[styles.stripe, { backgroundColor: '#EF3340' }]} /></> : <MayushText variant="caption" color="#0B7A54" style={{ fontSize: 18, lineHeight: 20 }}>{'\u2606'}</MayushText>}
    </View>
    <MayushText variant="sectionTitle" color={colors.brand.navy900} style={styles.optionLabel}>{label}</MayushText>
    <View style={[styles.radio, active && styles.radioActive]}>{active ? <MayushIcon name="check" size={18} color={colors.surface.white} strokeWidth={3} /> : null}</View>
  </TouchableOpacity>
);

const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: colors.surface.creamLight, overflow: 'hidden' },
  content: { flex: 1, alignItems: 'center', paddingHorizontal: 28, paddingTop: 84, zIndex: 1 },
  contentCompact: { paddingTop: 42 },
  arch: { position: 'absolute', left: -118, top: 96, width: 238, height: 540, borderTopRightRadius: 118, borderTopLeftRadius: 118, backgroundColor: '#F3E5D3', borderRightWidth: 12, borderColor: '#EAD7C1', opacity: 0.76 },
  lampLine: { position: 'absolute', right: 35, top: 0, width: 1, height: 180, backgroundColor: '#AA7B3F' },
  lampShade: { position: 'absolute', top: 174, left: -42, width: 85, height: 38, borderTopLeftRadius: 43, borderTopRightRadius: 43, backgroundColor: '#EBD9C1', borderWidth: 1, borderColor: '#C89D64' },
  titleBlock: { alignItems: 'center', marginTop: 78, gap: 7 },
  titleBlockCompact: { marginTop: 38, gap: 3 },
  title: { fontSize: 29, lineHeight: 36, fontFamily: 'Georgia' },
  arabicTitle: { fontSize: 29, lineHeight: 40 },
  sparkleDivider: { flexDirection: 'row', alignItems: 'center', gap: 10, marginVertical: 12 },
  dividerLine: { width: 48, height: 1, backgroundColor: '#EFB06C' },
  description: { fontSize: 15, lineHeight: 23 },
  options: { width: '100%', marginTop: 36, gap: 14 },
  optionsCompact: { marginTop: 24, gap: 10 },
  option: { minHeight: 78, flexDirection: 'row', alignItems: 'center', gap: 17, paddingHorizontal: 20, backgroundColor: colors.surface.white, borderWidth: 1.5, borderColor: colors.surface.borderWarm, borderRadius: 18, shadowColor: '#101D35', shadowOpacity: 0.04, shadowRadius: 9, shadowOffset: { width: 0, height: 4 }, elevation: 1 },
  optionActive: { borderColor: colors.brand.orange500, borderWidth: 2, backgroundColor: '#FFFDFC' },
  flag: { width: 42, height: 42, borderRadius: 21, overflow: 'hidden', alignItems: 'center', justifyContent: 'center' },
  frenchFlag: { flexDirection: 'row', borderWidth: 1, borderColor: '#E9E9E9' },
  stripe: { flex: 1, height: '100%' },
  moroccoFlag: { backgroundColor: '#C73535', borderWidth: 1, borderColor: '#A72424' },
  optionLabel: { flex: 1, fontSize: 18 },
  radio: { width: 29, height: 29, borderRadius: 15, borderWidth: 1.5, borderColor: '#B5B1AA', alignItems: 'center', justifyContent: 'center' },
  radioActive: { borderColor: colors.brand.orange500, backgroundColor: colors.brand.orange500 },
  continue: { width: '100%', height: 62, marginTop: 'auto', marginBottom: 30, flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 20, borderRadius: 17, backgroundColor: colors.brand.orange500, shadowColor: colors.brand.orange500, shadowOpacity: 0.28, shadowRadius: 14, shadowOffset: { width: 0, height: 7 }, elevation: 4 },
  continueCompact: { height: 56, marginBottom: 18 },
  continueLabel: { fontSize: 21, fontWeight: '700' },
  securityLine: { flexDirection: 'row', alignItems: 'center', gap: 10, marginBottom: 32 },
  securityLineCompact: { marginBottom: 20 },
  wave: { position: 'absolute', right: -110, bottom: -150, width: 380, height: 330, borderRadius: 190, borderWidth: 1, borderColor: '#F4DCC4', opacity: 0.8 },
});
