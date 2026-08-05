/**
 * Mayush Mobile Buyer App - Entry Point
 * Renders RootNavigator for Phase 5 (entry-discovery-product-vertical-slice).
 */

import React from 'react';
import { StatusBar } from 'expo-status-bar';
import { RootNavigator } from './src/navigation/RootNavigator';

export default function App() {
  return (
    <>
      <StatusBar style="dark" />
      <RootNavigator />
    </>
  );
}
