import React, { useEffect } from 'react';
import { ActivityIndicator, StyleSheet, View } from 'react-native';
import { MayushLogo } from '../../design-system/components/brand/MayushLogo';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { colors } from '../../design-system/tokens/colors';

export interface LoginLoadingScreenProps {
  onComplete?: () => void;
  autoProgress?: boolean;
}

export const LoginLoadingScreen: React.FC<LoginLoadingScreenProps> = ({
  onComplete,
  autoProgress = true,
}) => {
  useEffect(() => {
    if (autoProgress && onComplete) {
      const timer = setTimeout(() => {
        onComplete();
      }, 1200);
      return () => clearTimeout(timer);
    }
  }, [autoProgress, onComplete]);

  return (
    <View style={styles.container} accessibilityLabel="Chargement de la connexion">
      <MayushLogo width={200} height={60} style={styles.logo} />
      <View style={styles.content}>
        <ActivityIndicator size="large" color={colors.brand.orange500} style={styles.spinner} />
        <MayushText variant="pageTitle" color={colors.brand.navy900} align="center" style={styles.title}>
          Connexion en cours...
        </MayushText>
        <MayushText variant="caption" color={colors.neutral.gray700} align="center" style={styles.subtitle}>
          Veuillez patienter pendant la vérification de vos informations.
        </MayushText>
      </View>
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#FFF8F0',
    alignItems: 'center',
    justifyContent: 'center',
    paddingHorizontal: 24,
  },
  logo: {
    marginBottom: 40,
  },
  content: {
    backgroundColor: colors.surface.white,
    borderRadius: 24,
    padding: 32,
    alignItems: 'center',
    width: '100%',
    shadowColor: colors.brand.navy900,
    shadowOpacity: 0.1,
    shadowRadius: 16,
    elevation: 4,
  },
  spinner: {
    marginBottom: 20,
  },
  title: {
    fontSize: 22,
    lineHeight: 28,
    marginBottom: 8,
  },
  subtitle: {
    lineHeight: 20,
  },
});
