/**
 * First-launch onboarding kept fully native: only the supplied interior
 * artwork is bundled, while navigation, progress, copy, and CTAs remain live.
 */

import React from 'react';
import { Image, ImageSourcePropType, StyleProp, StyleSheet, TouchableOpacity, useWindowDimensions, View, ViewStyle } from 'react-native';
import { MayushLogo } from '../../design-system/components/brand/MayushLogo';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { useTheme } from '../../design-system/theme/useTheme';
import { colors } from '../../design-system/tokens/colors';

export type OnboardingStep = 1 | 2 | 3;

export interface OnboardingScreenProps {
  step: OnboardingStep;
  onNext?: () => void;
  onSkip?: () => void;
}

interface OnboardingCopy {
  title: string;
  body: string;
  next: string;
  skip: string;
}

const SCENES: Record<'fr' | 'ar', Record<OnboardingStep, ImageSourcePropType>> = {
  fr: {
    1: require('../../../assets/reference-art/onboarding-step-1-scene.png'),
    2: require('../../../assets/reference-art/onboarding-step-2-scene.png'),
    3: require('../../../assets/reference-art/onboarding-step-3-scene.png'),
  },
  ar: {
    1: require('../../../assets/reference-art/onboarding-step-1-scene-ar.png'),
    2: require('../../../assets/reference-art/onboarding-step-2-scene-ar.png'),
    3: require('../../../assets/reference-art/onboarding-step-3-scene-ar.png'),
  },
};

const SCENE_ASPECT_RATIOS: Record<OnboardingStep, number> = {
  1: 943 / 745,
  2: 943 / 826,
  3: 943 / 796,
};

const COPY: Record<'fr' | 'ar', Record<OnboardingStep, OnboardingCopy>> = {
  fr: {
    1: { title: 'D\u00e9couvrez un int\u00e9rieur\nqui vous ressemble', body: 'Mobilier et d\u00e9coration s\u00e9lectionn\u00e9s pour\nleur style, leur qualit\u00e9 et leur caract\u00e8re.', next: 'Continuer', skip: 'Passer' },
    2: { title: 'Choisissez en toute\nconfiance', body: 'Explorez les d\u00e9tails, comparez les styles\net retrouvez facilement vos pi\u00e8ces pr\u00e9f\u00e9r\u00e9es.', next: 'Continuer', skip: 'Passer' },
    3: { title: 'Commandez simplement', body: 'Une exp\u00e9rience fluide, du choix de votre\nproduit jusqu\u2019\u00e0 sa livraison.', next: 'Commencer', skip: 'Passer' },
  },
  ar: {
    1: { title: '\u0627\u0643\u062a\u0634\u0641 \u062f\u064a\u0643\u0648\u0631\n\u0623\u062d\u0644\u0627\u0645\u0643', body: '\u0627\u0643\u062a\u0634\u0641 \u0645\u062c\u0645\u0648\u0639\u0629 \u0645\u062e\u062a\u0627\u0631\u0629 \u0628\u0639\u0646\u0627\u064a\u0629\n\u0644\u0645\u0646\u0632\u0644\u0643.', next: '\u0645\u062a\u0627\u0628\u0639\u0629', skip: '\u062a\u062e\u0637\u064a' },
    2: { title: '\u0627\u062e\u062a\u0631 \u0628\u0643\u0644 \u062b\u0642\u0629', body: '\u0627\u0633\u062a\u0643\u0634\u0641 \u0627\u0644\u062a\u0641\u0627\u0635\u064a\u0644 \u0648\u0642\u0627\u0631\u0646 \u0627\u0644\u062a\u0635\u0627\u0645\u064a\u0645\n\u0648\u0627\u062d\u062a\u0641\u0638 \u0628\u0645\u0646\u062a\u062c\u0627\u062a\u0643 \u0627\u0644\u0645\u0641\u0636\u0644\u0629 \u0628\u0633\u0647\u0648\u0644\u0629.', next: '\u0645\u062a\u0627\u0628\u0639\u0629', skip: '\u062a\u062e\u0637\u064a' },
    3: { title: '\u0627\u0637\u0644\u0628 \u0628\u0628\u0633\u0627\u0637\u0629', body: '\u062a\u062c\u0631\u0628\u0629 \u0633\u0644\u0633\u0629 \u0645\u0646 \u0627\u0644\u0627\u062e\u062a\u064a\u0627\u0631 \u062d\u062a\u0649 \u0627\u0644\u062a\u0648\u0635\u064a\u0644.', next: '\u0627\u0628\u062f\u0623', skip: '\u062a\u062e\u0637\u064a' },
  },
};

export const OnboardingScreen: React.FC<OnboardingScreenProps> = ({ step, onNext, onSkip }) => {
  const { language, isRTL } = useTheme();
  const { width, height } = useWindowDimensions();
  const copy = COPY[language][step];
  const horizontalInset = Math.max(18, Math.round(width * 0.052));
  const sceneHeight = Math.min((width - horizontalInset * 2) / SCENE_ASPECT_RATIOS[step], height * 0.51);
  const logoWidth = Math.max(154, Math.min(265, Math.round(width * 0.36)));
  const sceneCurrencyTextSize = Math.max(11, Math.min(18, Math.round(width * 0.018)));

  return (
    <View style={[styles.screen, { paddingHorizontal: horizontalInset }]} accessibilityLabel={`${step} / 3`}>
      <View pointerEvents="none" style={styles.topGlow} />
      <View pointerEvents="none" style={styles.bottomArc} />

      <View style={styles.header}>
        <MayushLogo width={logoWidth} height={Math.round(logoWidth * 0.31)} style={styles.logo} />
        <TouchableOpacity accessibilityRole="button" accessibilityLabel={copy.skip} hitSlop={12} onPress={onSkip} style={[styles.skipButton, isRTL && styles.skipButtonRtl]}>
          <MayushText variant="body" color={colors.brand.navy900} style={[styles.skipLabel, isRTL && styles.rtlText]}>{copy.skip}</MayushText>
        </TouchableOpacity>
      </View>

      <View style={styles.progress} accessibilityRole="progressbar" accessibilityLabel={`${step} / 3`}>
        <View style={[styles.progressDots, isRTL && styles.rowReverse]}>
          {[1, 2, 3].map((index) => <View key={index} style={[styles.progressDot, index === step && styles.progressDotActive]} />)}
        </View>
        <MayushText variant="caption" color={colors.brand.navy900} align="center" style={styles.stepLabel}>{step} / 3</MayushText>
      </View>

      <View style={[styles.sceneFrame, { height: sceneHeight, marginHorizontal: -horizontalInset }]}>
        <Image source={SCENES[language][step]} resizeMode="cover" style={styles.sceneImage} />
        {language === 'ar' && step === 2 ? <View pointerEvents="none" style={styles.sceneCurrencyFixes}>
          <ScenePriceCorrection value="650 MAD" style={styles.scenePriceLeft} fontSize={sceneCurrencyTextSize} />
          <ScenePriceCorrection value="1 850 MAD" style={styles.scenePriceCenter} fontSize={sceneCurrencyTextSize} />
          <ScenePriceCorrection value="1 250 MAD" style={styles.scenePriceRight} fontSize={sceneCurrencyTextSize} />
        </View> : null}
      </View>

      <View style={styles.copyBlock}>
        <MayushText variant="display" color={colors.brand.navy900} align="center" style={[styles.title, isRTL && styles.rtlText]}>{copy.title}</MayushText>
        <View style={styles.sparkleDivider}><View style={styles.dividerLine} /><MayushText variant="body" color={colors.brand.orange500} style={styles.sparkle}>{'\u2726'}</MayushText><View style={styles.dividerLine} /></View>
        <MayushText variant="body" color={colors.neutral.gray700} align="center" style={[styles.body, isRTL && styles.rtlText]}>{copy.body}</MayushText>
      </View>

      <TouchableOpacity accessibilityRole="button" accessibilityLabel={copy.next} activeOpacity={0.84} onPress={onNext} style={styles.nextButton}>
        <MayushText variant="button" color={colors.surface.white} style={styles.nextLabel}>{copy.next}</MayushText>
      </TouchableOpacity>
    </View>
  );
};

const ScenePriceCorrection: React.FC<{ value: string; style: StyleProp<ViewStyle>; fontSize: number }> = ({ value, style, fontSize }) => (
  <View style={[styles.scenePriceCorrection, style]}>
    <MayushText variant="caption" color={colors.brand.orange500} style={{ fontSize, fontWeight: '700' }}>{value}</MayushText>
  </View>
);

const styles = StyleSheet.create({
  screen: { flex: 1, overflow: 'hidden', paddingTop: 28, paddingBottom: 24, backgroundColor: '#FFFCF8' },
  topGlow: { position: 'absolute', top: -190, right: -100, width: 380, height: 360, borderRadius: 190, backgroundColor: '#F9EDE0' },
  bottomArc: { position: 'absolute', left: -200, bottom: -260, width: 520, height: 470, borderRadius: 260, borderWidth: 1, borderColor: '#F0D9C0' },
  header: { minHeight: 62, alignItems: 'center', justifyContent: 'flex-start', position: 'relative' },
  logo: { alignSelf: 'center' },
  skipButton: { position: 'absolute', right: 0, top: 4, minWidth: 58, minHeight: 42, justifyContent: 'center', alignItems: 'flex-end' },
  skipButtonRtl: { right: undefined, left: 0, alignItems: 'flex-start' },
  skipLabel: { fontSize: 17, lineHeight: 22, fontWeight: '600' },
  progress: { alignItems: 'center', marginTop: 23 },
  progressDots: { flexDirection: 'row', alignItems: 'center', gap: 16 },
  rowReverse: { flexDirection: 'row-reverse' },
  progressDot: { width: 10, height: 10, borderRadius: 5, backgroundColor: '#E1DBD4' },
  progressDotActive: { backgroundColor: colors.brand.orange500 },
  stepLabel: { marginTop: 13, fontSize: 16, lineHeight: 20, fontWeight: '600' },
  sceneFrame: { width: 'auto', marginTop: 5, overflow: 'hidden' },
  sceneImage: { width: '100%', height: '100%' },
  sceneCurrencyFixes: { ...StyleSheet.absoluteFill, zIndex: 2 },
  scenePriceCorrection: { position: 'absolute', backgroundColor: '#FFFDFC', borderRadius: 4, paddingHorizontal: 4, paddingVertical: 1 },
  scenePriceLeft: { left: '12%', top: '53%' },
  scenePriceCenter: { left: '32%', top: '58%' },
  scenePriceRight: { right: '9%', top: '53%' },
  copyBlock: { alignItems: 'center', marginTop: 7, paddingHorizontal: 7, zIndex: 1 },
  title: { fontFamily: 'Georgia', fontSize: 30, lineHeight: 36, fontWeight: '700', letterSpacing: -0.4 },
  rtlText: { writingDirection: 'rtl', textAlign: 'center' },
  sparkleDivider: { flexDirection: 'row', alignItems: 'center', gap: 10, marginTop: 15, marginBottom: 13 },
  dividerLine: { width: 52, height: 1, backgroundColor: '#EFB06C' },
  sparkle: { fontSize: 20, lineHeight: 20 },
  body: { fontSize: 16, lineHeight: 24 },
  nextButton: { width: '67%', minHeight: 62, marginTop: 'auto', alignSelf: 'center', alignItems: 'center', justifyContent: 'center', borderRadius: 18, backgroundColor: colors.brand.orange500, shadowColor: colors.brand.orange500, shadowOpacity: 0.28, shadowRadius: 13, shadowOffset: { width: 0, height: 7 }, elevation: 4, zIndex: 1 },
  nextLabel: { fontSize: 20, fontWeight: '700' },
});
