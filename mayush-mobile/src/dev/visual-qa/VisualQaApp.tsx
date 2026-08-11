import React, { useEffect, useState } from 'react';
import { StyleSheet, View } from 'react-native';
import { ThemeProvider } from '../../design-system/theme/ThemeProvider';
import { renderVisualQaScreen } from './visualQaRegistry';
import { VisualQaScreenKey } from './visualQaTypes';

export interface VisualQaAppProps {
  screenKey?: VisualQaScreenKey;
}

export const getQaScreenFromEnvironment = (): VisualQaScreenKey => {
  if (typeof window !== 'undefined' && window.location) {
    const params = new URLSearchParams(window.location.search);
    const qaParam = params.get('qaScreen') as VisualQaScreenKey | null;
    if (qaParam) return qaParam;
  }
  if (process.env.EXPO_PUBLIC_QA_SCREEN) {
    return process.env.EXPO_PUBLIC_QA_SCREEN as VisualQaScreenKey;
  }
  return '06-checkout-summary-4step-overview-v2-fr';
};

export const VisualQaApp: React.FC<VisualQaAppProps> = ({ screenKey: propScreenKey }) => {
  const activeScreenKey = propScreenKey || getQaScreenFromEnvironment();
  const [ready, setReady] = useState(false);

  useEffect(() => {
    const timer = setTimeout(() => {
      setReady(true);
      if (typeof document !== 'undefined') {
        document.body.setAttribute('data-visual-qa-ready', 'true');
        const indicator = document.createElement('div');
        indicator.id = 'visual-qa-ready';
        indicator.setAttribute('data-screen-key', activeScreenKey);
        indicator.style.position = 'absolute';
        indicator.style.width = '0px';
        indicator.style.height = '0px';
        indicator.style.opacity = '0';
        document.body.appendChild(indicator);
      }
    }, 150);

    return () => clearTimeout(timer);
  }, [activeScreenKey]);

  return (
    <ThemeProvider initialLanguage="fr">
      <View style={styles.container} testID="visual-qa-container">
        {renderVisualQaScreen(activeScreenKey)}
        {ready ? (
          <View
            testID="visual-qa-ready"
            accessibilityLabel="visual-qa-ready"
            style={styles.readyMarker}
          />
        ) : null}
      </View>
    </ThemeProvider>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    width: 393,
    height: 852,
    backgroundColor: '#FFFFFF',
  },
  readyMarker: {
    position: 'absolute',
    width: 0,
    height: 0,
    opacity: 0,
  },
});
