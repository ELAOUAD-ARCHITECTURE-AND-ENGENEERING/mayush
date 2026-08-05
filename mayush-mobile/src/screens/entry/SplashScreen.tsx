/**
 * SCR-ENT-001: Splash Screen
 * Exact visual match for 01-entry/01-splash-screen-logo.png.
 * Warm cream background (#F2E8DA), centered Mayush logo.
 */

import React, { useEffect, useRef } from 'react';
import { Animated, Easing, Image, StyleSheet, View } from 'react-native';

const SPLASH_ARTWORK = require('../../../design-reference/mayush-mobile-design/01-entry/01-splash-screen-logo.png');

export interface SplashScreenProps {
  onFinish?: (nextScreen: 'language' | 'home') => void;
}

export const SplashScreen: React.FC<SplashScreenProps> = ({ onFinish }) => {
  const spin = useRef(new Animated.Value(0)).current;

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
    <View style={styles.container} accessibilityLabel="Mayush Design">
      <Image source={SPLASH_ARTWORK} resizeMode="stretch" style={styles.artwork} />
      <View pointerEvents="none" style={styles.loaderMask}>
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
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
  artwork: { width: '100%', height: '100%' },
  loaderMask: {
    position: 'absolute',
    top: '70.8%',
    alignSelf: 'center',
    width: 64,
    height: 64,
    borderRadius: 32,
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#FCF2E8',
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
