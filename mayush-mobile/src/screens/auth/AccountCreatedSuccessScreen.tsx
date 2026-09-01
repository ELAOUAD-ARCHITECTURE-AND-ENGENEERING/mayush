import React from 'react';
import { StyleSheet, View } from 'react-native';
import { MayushLogo } from '../../design-system/components/brand/MayushLogo';
import { PrimaryButton } from '../../design-system/components/actions/PrimaryButton';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { colors } from '../../design-system/tokens/colors';
import { authState } from '../../commerce/authState';

export interface AccountCreatedSuccessScreenProps {
  onContinue: () => void;
}

export const AccountCreatedSuccessScreen: React.FC<AccountCreatedSuccessScreenProps> = ({
  onContinue,
}) => {
  const user = authState.getUser();

  return (
    <View style={styles.container} accessibilityLabel="Compte créé avec succès">
      <MayushLogo width={200} height={60} style={styles.logo} />

      <View style={styles.card}>
        <View style={styles.iconCircle}>
          <MayushIcon name="check" size={36} color={colors.surface.white} />
        </View>

        <MayushText variant="pageTitle" color={colors.brand.navy900} align="center" style={styles.title}>
          Bienvenue chez Mayush !
        </MayushText>

        <MayushText variant="caption" color={colors.neutral.gray700} align="center" style={styles.subtitle}>
          Votre compte a été créé avec succès. Vous pouvez désormais profiter pleinement de tous vos privilèges d'acheteur.
        </MayushText>

        {user ? (
          <View style={styles.userBadge}>
            <MayushIcon name="user" size={18} color={colors.brand.orange500} />
            <View style={styles.userInfo}>
              <MayushText variant="sectionTitle" color={colors.brand.navy900} style={styles.userName}>
                {user.fullName}
              </MayushText>
              <MayushText variant="caption" color={colors.neutral.gray700}>
                {user.emailOrPhone}
              </MayushText>
            </View>
          </View>
        ) : null}

        <View style={styles.featuresList}>
          <View style={styles.featureItem}>
            <MayushIcon name="heart" size={16} color={colors.brand.orange500} />
            <MayushText variant="caption" color={colors.brand.navy900}>
              Sauvegarde synchronisée de vos favoris
            </MayushText>
          </View>
          <View style={styles.featureItem}>
            <MayushIcon name="truck" size={16} color={colors.brand.orange500} />
            <MayushText variant="caption" color={colors.brand.navy900}>
              Suivi de commande et retours simplifiés
            </MayushText>
          </View>
        </View>

        <PrimaryButton
          label="Explorer les collections"
          onPress={onContinue}
          style={styles.submitBtn}
        />
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
    marginBottom: 32,
  },
  card: {
    backgroundColor: colors.surface.white,
    borderRadius: 24,
    padding: 24,
    alignItems: 'center',
    width: '100%',
    shadowColor: colors.brand.navy900,
    shadowOpacity: 0.1,
    shadowRadius: 16,
    elevation: 4,
  },
  iconCircle: {
    width: 68,
    height: 68,
    borderRadius: 34,
    backgroundColor: colors.semantic.success,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 20,
  },
  title: {
    fontSize: 24,
    lineHeight: 30,
    marginBottom: 8,
  },
  subtitle: {
    lineHeight: 20,
    marginBottom: 20,
  },
  userBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
    backgroundColor: '#FFF3E6',
    borderRadius: 16,
    paddingVertical: 12,
    paddingHorizontal: 16,
    width: '100%',
    marginBottom: 20,
  },
  userInfo: {
    flex: 1,
  },
  userName: {
    fontSize: 16,
  },
  featuresList: {
    width: '100%',
    gap: 10,
    marginBottom: 24,
  },
  featureItem: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
  },
  submitBtn: {
    width: '100%',
  },
});
