/**
 * First-launch transition shown after language selection.
 * The indicator is intentionally animated rather than being part of a static image.
 */

import React, { useEffect, useRef } from 'react';
import { Animated, Easing, StyleSheet, View } from 'react-native';
import { MayushLogo } from '../../design-system/components/brand/MayushLogo';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { colors } from '../../design-system/tokens/colors';
import { useTheme } from '../../design-system/theme/useTheme';

const PREPARING_DURATION = 3600;

export interface PreparingExperienceScreenProps {
  onFinish?: () => void;
}

export const PreparingExperienceScreen: React.FC<PreparingExperienceScreenProps> = ({ onFinish }) => {
  const logoReveal = useRef(new Animated.Value(0)).current;
  const progress = useRef(new Animated.Value(0)).current;
  const dotOne = useRef(new Animated.Value(0.3)).current;
  const dotTwo = useRef(new Animated.Value(0.3)).current;
  const dotThree = useRef(new Animated.Value(0.3)).current;
  const ambience = useRef(new Animated.Value(0.45)).current;
  const { language } = useTheme();

  useEffect(() => {
    const reveal = Animated.timing(logoReveal, {
      toValue: 1,
      duration: 620,
      easing: Easing.out(Easing.cubic),
      useNativeDriver: true,
    });
    const progressAnimation = Animated.timing(progress, {
      toValue: 1,
      duration: PREPARING_DURATION - 360,
      easing: Easing.inOut(Easing.cubic),
      useNativeDriver: false,
    });
    const dotAnimation = Animated.loop(
      Animated.sequence([
        Animated.parallel([
          Animated.timing(dotOne, { toValue: 1, duration: 180, easing: Easing.out(Easing.quad), useNativeDriver: true }),
          Animated.timing(dotTwo, { toValue: 0.3, duration: 150, useNativeDriver: true }),
          Animated.timing(dotThree, { toValue: 0.3, duration: 150, useNativeDriver: true }),
        ]),
        Animated.parallel([
          Animated.timing(dotOne, { toValue: 0.3, duration: 150, useNativeDriver: true }),
          Animated.timing(dotTwo, { toValue: 1, duration: 180, easing: Easing.out(Easing.quad), useNativeDriver: true }),
        ]),
        Animated.parallel([
          Animated.timing(dotTwo, { toValue: 0.3, duration: 150, useNativeDriver: true }),
          Animated.timing(dotThree, { toValue: 1, duration: 180, easing: Easing.out(Easing.quad), useNativeDriver: true }),
        ]),
        Animated.timing(dotThree, { toValue: 0.3, duration: 170, useNativeDriver: true }),
        Animated.delay(90),
      ]),
    );
    const ambienceAnimation = Animated.loop(
      Animated.sequence([
        Animated.timing(ambience, { toValue: 0.9, duration: 1200, easing: Easing.inOut(Easing.sin), useNativeDriver: true }),
        Animated.timing(ambience, { toValue: 0.45, duration: 1200, easing: Easing.inOut(Easing.sin), useNativeDriver: true }),
      ]),
    );
    const timer = setTimeout(() => onFinish?.(), PREPARING_DURATION);

    reveal.start();
    progressAnimation.start();
    dotAnimation.start();
    ambienceAnimation.start();

    return () => {
      clearTimeout(timer);
      reveal.stop();
      progressAnimation.stop();
      dotAnimation.stop();
      ambienceAnimation.stop();
    };
  }, [ambience, dotOne, dotThree, dotTwo, logoReveal, onFinish, progress]);

  const label = language === 'ar'
    ? '\u0646\u062d\u0636\u0651\u0631 \u062a\u062c\u0631\u0628\u062a\u0643\u2026'
    : 'Pr\u00e9paration de votre exp\u00e9rience\u2026';

  return (
    <View style={styles.screen} accessibilityLabel={label}>
      <View pointerEvents="none" style={styles.leftArch} />
      <Animated.View pointerEvents="none" style={[styles.rightLeaf, { opacity: ambience }]} />
      <View pointerEvents="none" style={styles.bottomCurve} />
      <View style={styles.content}>
        <Animated.View style={[styles.logoLockup, {
          opacity: logoReveal,
          transform: [{ translateY: logoReveal.interpolate({ inputRange: [0, 1], outputRange: [14, 0] }) }],
        }]}>
          <MayushLogo width={242} height={72} />
        </Animated.View>
        <View accessibilityRole="progressbar" accessibilityLabel={label} style={styles.progressGroup}>
          <View style={styles.dots}>
            <Animated.View style={[styles.dot, styles.dotActive, { opacity: dotOne, transform: [{ scale: dotOne.interpolate({ inputRange: [0.3, 1], outputRange: [0.8, 1.24] }) }] }]} />
            <Animated.View style={[styles.dot, styles.dotActive, { opacity: dotTwo, transform: [{ scale: dotTwo.interpolate({ inputRange: [0.3, 1], outputRange: [0.8, 1.24] }) }] }]} />
            <Animated.View style={[styles.dot, styles.dotActive, { opacity: dotThree, transform: [{ scale: dotThree.interpolate({ inputRange: [0.3, 1], outputRange: [0.8, 1.24] }) }] }]} />
          </View>
          <View style={styles.progressLine}><Animated.View style={[styles.progressFill, { width: progress.interpolate({ inputRange: [0, 1], outputRange: ['0%', '100%'] }) }]} /></View>
        </View>
        <MayushText variant="body" color={colors.neutral.gray700} align="center" style={styles.label}>{label}</MayushText>
      </View>
    </View>
  );
};

const styles = StyleSheet.create({
  screen: { flex: 1, overflow: 'hidden', backgroundColor: '#FCF2E8' },
  content: { flex: 1, alignItems: 'center', justifyContent: 'center', paddingHorizontal: 28, paddingBottom: 20, zIndex: 1 },
  leftArch: { position: 'absolute', left: -104, top: 134, width: 223, height: 438, borderTopRightRadius: 112, borderTopLeftRadius: 112, backgroundColor: '#F4E5D2', borderRightWidth: 10, borderColor: '#E6CFAE', opacity: 0.86 },
  rightLeaf: { position: 'absolute', right: -82, top: 158, width: 166, height: 300, borderTopLeftRadius: 150, borderBottomLeftRadius: 150, backgroundColor: '#F3E1CB', borderLeftWidth: 1, borderColor: '#E6CFAE', transform: [{ rotate: '-20deg' }] },
  bottomCurve: { position: 'absolute', right: -98, bottom: -118, width: 360, height: 255, borderRadius: 180, borderWidth: 1, borderColor: '#E7CFAF' },
  logoLockup: { alignItems: 'center' },
  progressGroup: { alignItems: 'center', marginTop: 70 },
  dots: { flexDirection: 'row', alignItems: 'center', gap: 14 },
  dot: { width: 10, height: 10, borderRadius: 5 },
  dotActive: { backgroundColor: colors.brand.orange500 },
  progressLine: { width: 116, height: 2, marginTop: 14, borderRadius: 1, backgroundColor: '#F0DDC8', overflow: 'hidden' },
  progressFill: { width: '68%', height: '100%', borderRadius: 1, backgroundColor: colors.brand.orange500 },
  label: { marginTop: 22, fontSize: 16, lineHeight: 24 },
});
