import React, { useEffect, useRef } from 'react';
import { Animated, Easing, StyleSheet, View } from 'react-native';
import { MayushLogo } from '../../design-system/components/brand/MayushLogo';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { colors } from '../../design-system/tokens/colors';

export interface SplashScreenProps {
  onFinish?: () => void;
  logoWidth?: number;
}

export const SplashScreen: React.FC<SplashScreenProps> = ({ onFinish, logoWidth = 260 }) => {
  const progress = useRef(new Animated.Value(0)).current;

  useEffect(() => {
    const animation = Animated.loop(
      Animated.sequence([
        Animated.timing(progress, {
          toValue: 1,
          duration: 700,
          easing: Easing.bezier(0.2, 0.8, 0.2, 1),
          useNativeDriver: true,
        }),
        Animated.timing(progress, {
          toValue: 0,
          duration: 700,
          easing: Easing.bezier(0.2, 0.8, 0.2, 1),
          useNativeDriver: true,
        }),
      ])
    );
    animation.start();

    const finishTimer = setTimeout(() => {
      onFinish?.();
    }, 1600);

    return () => {
      animation.stop();
      clearTimeout(finishTimer);
      progress.setValue(0);
    };
  }, [progress, onFinish]);

  const dotTravel = progress.interpolate({ inputRange: [0, 1], outputRange: [0, 68] });

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
  topCurveInner: { position: 'absolute', left: 40, top: 40, width: 390, height: 390, borderRadius: 195, borderWidth: 1, borderColor: '#F0DDBF' },
  bottomCurve: { position: 'absolute', right: -210, bottom: -160, width: 490, height: 490, borderRadius: 245, borderWidth: 1, borderColor: '#F0DDBF', opacity: 0.82 },
  bottomCurveInner: { position: 'absolute', right: 40, bottom: 40, width: 410, height: 410, borderRadius: 205, borderWidth: 1, borderColor: '#F0DDBF' },
  logoAnchor: { position: 'absolute', top: 350, left: 0, right: 0, alignItems: 'center' },
  loaderAnchor: { position: 'absolute', top: 458, left: 0, right: 0, alignItems: 'center' },
  dotTrack: { width: 92, height: 24, borderRadius: 12, borderWidth: 1, borderColor: '#E5D5C2', backgroundColor: '#FAF3E8', flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', paddingHorizontal: 6, position: 'relative' },
  inactiveDot: { width: 10, height: 10, borderRadius: 5, backgroundColor: '#D8C5B0' },
  activeDot: { position: 'absolute', left: 6, top: 6, width: 12, height: 12, borderRadius: 6, backgroundColor: '#C66528' },
  progressLine: { marginTop: 14, width: 132, height: 2, borderRadius: 1, backgroundColor: '#DFCEB9' },
  loadingLabel: { position: 'absolute', bottom: 64, left: 24, right: 24, fontSize: 14 },
});
