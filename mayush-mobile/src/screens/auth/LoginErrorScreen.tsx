import React from 'react';
import { StyleSheet, TouchableOpacity, View } from 'react-native';
import { MayushLogo } from '../../design-system/components/brand/MayushLogo';
import { PrimaryButton } from '../../design-system/components/actions/PrimaryButton';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { colors } from '../../design-system/tokens/colors';

export interface LoginErrorScreenProps {
  errorMessage?: string;
  onRetry: () => void;
  onForgotPassword: () => void;
  onBack: () => void;
}

export const LoginErrorScreen: React.FC<LoginErrorScreenProps> = ({
  errorMessage = 'Email, téléphone ou mot de passe incorrect. Veuillez vérifier vos identifiants.',
  onRetry,
  onForgotPassword,
  onBack,
}) => {
  return (
    <View style={styles.container} accessibilityLabel="Erreur de connexion">
      <View style={styles.headerRow}>
        <TouchableOpacity
          onPress={onBack}
          style={styles.backBtn}
          accessibilityRole="button"
          accessibilityLabel="Retour"
        >
          <MayushIcon name="chevron-left" size={24} color={colors.brand.navy900} />
        </TouchableOpacity>
        <MayushLogo width={160} height={48} />
        <View style={styles.spacer} />
      </View>

      <View style={styles.card}>
        <View style={styles.iconCircle}>
          <MayushIcon name="alert-circle" size={32} color={colors.semantic.error} />
        </View>

        <MayushText variant="pageTitle" color={colors.brand.navy900} align="center" style={styles.title}>
          Échec de la connexion
        </MayushText>

        <MayushText variant="caption" color={colors.neutral.gray700} align="center" style={styles.subtitle}>
          {errorMessage}
        </MayushText>

        <View style={styles.badgeBox}>
          <MayushIcon name="shield" size={16} color={colors.brand.navy900} />
          <MayushText variant="caption" color={colors.brand.navy900}>
            Format accepté: email ou numéro marocain (+212)
          </MayushText>
        </View>

        <PrimaryButton
          label="Réessayer la connexion"
          onPress={onRetry}
          style={styles.retryBtn}
        />

        <TouchableOpacity
          onPress={onForgotPassword}
          style={styles.forgotBtn}
          accessibilityRole="button"
        >
          <MayushText variant="button" color={colors.brand.orange500}>
            Mot de passe oublié ?
          </MayushText>
        </TouchableOpacity>
      </View>
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#FFF8F0',
    paddingHorizontal: 24,
    paddingTop: 48,
  },
  headerRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    marginBottom: 36,
  },
  backBtn: {
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: colors.surface.white,
    alignItems: 'center',
    justifyContent: 'center',
  },
  spacer: {
    width: 40,
  },
  card: {
    backgroundColor: colors.surface.white,
    borderRadius: 24,
    padding: 24,
    alignItems: 'center',
    shadowColor: colors.brand.navy900,
    shadowOpacity: 0.1,
    shadowRadius: 16,
    elevation: 4,
  },
  iconCircle: {
    width: 64,
    height: 64,
    borderRadius: 32,
    backgroundColor: '#FEE2E2',
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 16,
  },
  title: {
    fontSize: 22,
    lineHeight: 28,
    marginBottom: 8,
  },
  subtitle: {
    lineHeight: 20,
    marginBottom: 20,
  },
  badgeBox: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    backgroundColor: '#FFF3E6',
    borderRadius: 12,
    paddingVertical: 8,
    paddingHorizontal: 12,
    marginBottom: 24,
  },
  retryBtn: {
    width: '100%',
    marginBottom: 12,
  },
  forgotBtn: {
    paddingVertical: 8,
  },
});
