/**
 * Mayush Mobile Buyer App - Entry Point
 * Renders RootNavigator for Phase 5 (entry-discovery-product-vertical-slice).
 */

import React, { useEffect } from 'react';
import { useFonts } from 'expo-font';
import { StatusBar } from 'expo-status-bar';
import {
  Inter_400Regular,
  Inter_500Medium,
  Inter_600SemiBold,
  Inter_700Bold,
} from '@expo-google-fonts/inter';
import {
  PlayfairDisplay_400Regular,
  PlayfairDisplay_600SemiBold,
  PlayfairDisplay_700Bold,
} from '@expo-google-fonts/playfair-display';
import {
  Tajawal_400Regular,
  Tajawal_500Medium,
  Tajawal_700Bold,
} from '@expo-google-fonts/tajawal';
import { SafeAreaProvider } from 'react-native-safe-area-context';
import { RootNavigator } from './src/navigation/RootNavigator';
import { VisualQaApp, getQaScreenFromEnvironment } from './src/dev/visual-qa';
import { enforceDeviceIntegrity } from './src/services/security/deviceIntegrity';
import { preventScreenCapture } from './src/services/security/screenProtection';

export default function App() {
  const [fontsLoaded] = useFonts({
    Inter_400Regular,
    Inter_500Medium,
    Inter_600SemiBold,
    Inter_700Bold,
    PlayfairDisplay_400Regular,
    PlayfairDisplay_600SemiBold,
    PlayfairDisplay_700Bold,
    Tajawal_400Regular,
    Tajawal_500Medium,
    Tajawal_700Bold,
  });

  if (!fontsLoaded) {
    return null;
  }

  useEffect(() => {
    enforceDeviceIntegrity();
    const cleanup = preventScreenCapture();
    return cleanup;
  }, []);

  const isVisualQaMode =
    process.env.EXPO_PUBLIC_VISUAL_QA === 'true' ||
    (typeof window !== 'undefined' && window.location && window.location.search.includes('qaScreen='));

  if (isVisualQaMode) {
    return (
      <SafeAreaProvider>
        <VisualQaApp screenKey={getQaScreenFromEnvironment()} />
      </SafeAreaProvider>
    );
  }

  return (
    <SafeAreaProvider>
      <StatusBar style="dark" />
      <RootNavigator />
    </SafeAreaProvider>
  );
}
