/**
 * SCR-ENT-001: Splash Screen
 * Native launch composition measured from 01-entry/01-splash-screen-logo.png.
 * The supplied decorative backdrop is retained while the logo and loader remain
 * live React Native elements.
 */

import React, { useEffect, useRef } from 'react';
import { Animated, Easing, ImageBackground, StyleSheet, useWindowDimensions, View } from 'react-native';
import { MayushLogo } from '../../design-system/components/brand/MayushLogo';

const SPLASH_BACKGROUND = require('../../../design-reference/mayush-mobile-design/01-entry/01-splash-screen-logo.png');

export interface SplashScreenProps {
  onFinish?: (nextScreen: 'language' | 'home') => void;
}

export const SplashScreen: React.FC<SplashScreenProps> = ({ onFinish }) => {
  const spin = useRef(new Animated.Value(0)).current;
  const { width } = useWindowDimensions();
  const logoWidth = Math.min(Math.max(width * 0.54, 190), 300);

  useEffect(() => {
    const timer = setTimeout(() => {
      if (onFinish) {
        onFinish('language');
      }
    }, 2200);

    return () => clearTimeout(timer);
  }, [onFinish]);

  useEffect(() => {
    const animation = Animated.loop(
      Animated.timing(spin, {
        toValue: 1,
        duration: 1100,
        easing: Easing.linear,
        useNativeDriver: true,
      }),
    );

    animation.start();
    return () => {
      animation.stop();
      spin.setValue(0);
    };
  }, [spin]);

  const rotation = spin.interpolate({ inputRange: [0, 1], outputRange: ['0deg', '360deg'] });

  return (
    <ImageBackground source={SPLASH_BACKGROUND} resizeMode="stretch" style={styles.container} accessibilityLabel="Mayush Design">
      <View pointerEvents="none" style={styles.logoAnchor}>
        <MayushLogo width={logoWidth} height={logoWidth * (54 / 154)} />
      </View>
      <View pointerEvents="none" style={styles.loaderAnchor}>
        <Animated.View
          accessibilityRole="progressbar"
          accessibilityLabel="Chargement"
          style={[styles.loaderOrbit, { transform: [{ rotate: rotation }] }]}
        >
          <View style={styles.loaderTrailFar} />
          <View style={styles.loaderTrailMid} />
          <View style={styles.loaderTrailNear} />
          <View style={styles.loaderHeadGlow} />
          <View style={styles.loaderHead} />
        </Animated.View>
      </View>
    </ImageBackground>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
  logoAnchor: {
    position: 'absolute',
    top: '38.5%',
    left: 0,
    right: 0,
    alignSelf: 'center',
    alignItems: 'center',
    justifyContent: 'center',
  },
  loaderAnchor: {
    position: 'absolute',
    top: '70.8%',
    alignSelf: 'center',
    width: 64,
    height: 64,
    borderRadius: 32,
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#FCF2E9',
  },
  loaderOrbit: {
    width: 48,
    height: 48,
    borderRadius: 24,
    borderWidth: 1.2,
    borderTopColor: 'transparent',
    borderLeftColor: 'rgba(255,121,0,0.82)',
    borderBottomColor: 'rgba(255,121,0,0.28)',
    borderRightColor: 'transparent',
  },
  loaderHead: {
    position: 'absolute',
    left: 18,
    top: -4,
    width: 10,
    height: 10,
    borderRadius: 5,
    backgroundColor: '#FF7900',
    shadowColor: '#FF7900',
    shadowOpacity: 0.42,
    shadowRadius: 7,
    shadowOffset: { width: 0, height: 1 },
    elevation: 4,
  },
  loaderHeadGlow: {
    position: 'absolute',
    left: 13,
    top: -9,
    width: 20,
    height: 20,
    borderRadius: 10,
    backgroundColor: 'rgba(255,121,0,0.12)',
  },
  loaderTrailNear: {
    position: 'absolute',
    left: 7,
    top: 0,
    width: 9,
    height: 9,
    borderRadius: 5,
    backgroundColor: 'rgba(255,121,0,0.35)',
    shadowColor: '#FF7900',
    shadowOpacity: 0.18,
    shadowRadius: 6,
    shadowOffset: { width: 0, height: 1 },
  },
  loaderTrailMid: {
    position: 'absolute',
    left: 1,
    top: 9,
    width: 8,
    height: 8,
    borderRadius: 4,
    backgroundColor: 'rgba(255,121,0,0.18)',
  },
  loaderTrailFar: {
    position: 'absolute',
    left: -2,
    top: 19,
    width: 6,
    height: 6,
    borderRadius: 3,
    backgroundColor: 'rgba(255,121,0,0.08)',
  },
});
