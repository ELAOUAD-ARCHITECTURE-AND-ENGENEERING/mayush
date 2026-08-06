/**
 * SCR-ENT-001: Splash Screen
 *
 * The reference artwork supplied a flattened whole-screen export. This keeps
 * the composition native and editable: the shared transparent wordmark and
 * loader are real views, not pixels embedded in a screenshot.
 */

import React, { useEffect, useRef } from 'react';
import { Animated, Easing, StyleSheet, useWindowDimensions, View } from 'react-native';
import { MayushLogo } from '../../design-system/components/brand/MayushLogo';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { colors } from '../../design-system/tokens/colors';

export interface SplashScreenProps {
  onFinish?: (nextScreen: 'language' | 'home') => void;
}

export const SplashScreen: React.FC<SplashScreenProps> = ({ onFinish }) => {
  const progress = useRef(new Animated.Value(0)).current;
  const { width } = useWindowDimensions();
  const logoWidth = Math.min(Math.max(width * 0.407, 152), 220);

  useEffect(() => {
    const timer = setTimeout(() => onFinish?.('language'), 2200);
    return () => clearTimeout(timer);
  }, [onFinish]);

  useEffect(() => {
    const animation = Animated.loop(
      Animated.sequence([
        Animated.timing(progress, { toValue: 1, duration: 900, easing: Easing.inOut(Easing.cubic), useNativeDriver: true }),
        Animated.timing(progress, { toValue: 0, duration: 900, easing: Easing.inOut(Easing.cubic), useNativeDriver: true }),
      ]),
    );
    animation.start();
    return () => {
      animation.stop();
      progress.setValue(0);
    };
  }, [progress]);

  const dotTravel = progress.interpolate({ inputRange: [0, 1], outputRange: ['0px', '68px'] });

  return (
    <View style={styles.container} accessibilityLabel="Mayush Design">
      <View pointerEvents="none" style={styles.lampCord}><View style={styles.lampShade} /></View>
      <View pointerEvents="none" style={styles.topCurve}><View style={styles.topCurveInner} /></View>
      <View pointerEvents="none" style={styles.bottomCurve}><View style={styles.bottomCurveInner} /></View>

      <View pointerEvents="none" style={styles.logoAnchor}>
        <MayushLogo width={logoWidth} height={logoWidth * 0.4} />
      </View>

      <View pointerEvents="none" style={styles.loaderAnchor} accessibilityRole="progressbar" accessibilityLabel="Chargement">
        <View style={styles.dotTrack}>
          <View style={styles.inactiveDot} />
          <View style={styles.inactiveDot} />
          <View style={styles.inactiveDot} />
          <Animated.View style={[styles.activeDot, { transform: [{ translateX: dotTravel }] }]} />
        </View>
        <View style={styles.progressLine} />
      </View>
      <MayushText variant="body" color={colors.brand.navy900} align="center" style={styles.loadingLabel}>
        Préparation de votre expérience...
      </MayushText>
    </View>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, overflow: 'hidden', backgroundColor: '#FAF6F0' },
  lampCord: { position: 'absolute', top: 0, right: 34, width: 1, height: 205, backgroundColor: '#A9783F' },
  lampShade: { position: 'absolute', top: 196, left: -38, width: 78, height: 34, borderTopLeftRadius: 40, borderTopRightRadius: 40, borderWidth: 1, borderColor: '#C89D64', backgroundColor: '#F3DEC2', opacity: 0.92 },
  topCurve: { position: 'absolute', left: -190, top: -140, width: 470, height: 470, borderRadius: 235, borderWidth: 1, borderColor: '#F0DDBF', opacity: 0.82 },
  topCurveInner: { position: 'absolute', left: 14, top: 14, width: 440, height: 440, borderRadius: 220, borderWidth: 1, borderColor: '#F0DDBF' },
  bottomCurve: { position: 'absolute', right: -196, bottom: -155, width: 500, height: 500, borderRadius: 250, borderWidth: 1, borderColor: '#F0DDBF', opacity: 0.82 },
  bottomCurveInner: { position: 'absolute', right: 14, bottom: 14, width: 470, height: 470, borderRadius: 235, borderWidth: 1, borderColor: '#F0DDBF' },
  logoAnchor: { position: 'absolute', top: '34.5%', left: 0, right: 0, alignItems: 'center' },
  loaderAnchor: { position: 'absolute', top: '47.3%', left: 0, right: 0, alignItems: 'center', height: 40 },
  dotTrack: { position: 'relative', width: 78, height: 12, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' },
  inactiveDot: { width: 10, height: 10, borderRadius: 5, backgroundColor: '#DCD6CC' },
  activeDot: { position: 'absolute', top: 1, left: 0, width: 10, height: 10, borderRadius: 5, backgroundColor: colors.brand.orange500, shadowColor: colors.brand.orange500, shadowOpacity: 0.34, shadowRadius: 5, shadowOffset: { width: 0, height: 1 }, elevation: 3 },
  progressLine: { width: 40, height: 2, marginTop: 12, borderRadius: 1, backgroundColor: colors.brand.orange500 },
  loadingLabel: { position: 'absolute', top: '53.4%', left: 20, right: 20, fontSize: 15, lineHeight: 22 },
});
