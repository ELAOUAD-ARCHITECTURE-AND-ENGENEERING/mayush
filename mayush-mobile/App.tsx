/**
 * Mayush Mobile Buyer App - Entry Point
 * Renders RootNavigator for Phase 5 (entry-discovery-product-vertical-slice).
 */

import React from 'react';
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
import { RootNavigator } from './src/navigation/RootNavigator';
import { VisualQaApp, getQaScreenFromEnvironment } from './src/dev/visual-qa';

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

  const isVisualQaMode =
    process.env.EXPO_PUBLIC_VISUAL_QA === 'true' ||
    (typeof window !== 'undefined' && window.location && window.location.search.includes('qaScreen='));

  if (isVisualQaMode) {
    return <VisualQaApp screenKey={getQaScreenFromEnvironment()} />;
  }

  return (
    <>
      <StatusBar style="dark" />
      <RootNavigator />
    </>
  );
}
