import React from 'react';
import { Modal, StyleSheet, TouchableOpacity, View } from 'react-native';
import { PrimaryButton } from '../../design-system/components/actions/PrimaryButton';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { colors } from '../../design-system/tokens/colors';
import { authState } from '../../commerce/authState';

export interface FavoritesAuthPromptOverlayProps {
  visible: boolean;
  onClose: () => void;
  onSignIn: () => void;
  onCreateAccount: () => void;
  favoriteItemId?: string;
  returnRoute?: string;
}

export const FavoritesAuthPromptOverlay: React.FC<FavoritesAuthPromptOverlayProps> = ({
  visible,
  onClose,
  onSignIn,
  onCreateAccount,
  favoriteItemId,
  returnRoute = 'wishlist',
}) => {
  const handleSignIn = () => {
    authState.setReturnDestination({
      route: returnRoute,
      pendingAction: 'favorite',
      favoriteItemId,
    });
    onClose();
    onSignIn();
  };

  const handleCreateAccount = () => {
    authState.setReturnDestination({
      route: returnRoute,
      pendingAction: 'favorite',
      favoriteItemId,
    });
    onClose();
    onCreateAccount();
  };

  return (
    <Modal
      visible={visible}
      transparent
      animationType="fade"
      onRequestClose={onClose}
      accessibilityLabel="Overlay d'authentification des favoris"
    >
      <View style={styles.backdrop}>
        <View style={styles.modalContent}>
          <TouchableOpacity
            onPress={onClose}
            style={styles.closeBtn}
            accessibilityRole="button"
            accessibilityLabel="Fermer"
          >
            <MayushIcon name="x" size={20} color={colors.brand.navy900} />
          </TouchableOpacity>

          <View style={styles.iconCircle}>
            <MayushIcon name="heart" size={32} color={colors.brand.orange500} />
          </View>

          <MayushText variant="pageTitle" color={colors.brand.navy900} align="center" style={styles.title}>
            Connectez-vous pour vos Favoris
          </MayushText>

          <MayushText variant="caption" color={colors.neutral.gray700} align="center" style={styles.subtitle}>
            Sauvegardez vos articles préférés dans votre liste d'envies et retrouvez-les à tout moment.
          </MayushText>

          <PrimaryButton
            label="Se connecter"
            onPress={handleSignIn}
            style={styles.primaryBtn}
          />

          <TouchableOpacity
            onPress={handleCreateAccount}
            style={styles.secondaryBtn}
            accessibilityRole="button"
          >
            <MayushText variant="button" color={colors.brand.orange500}>
              Créer un compte
            </MayushText>
          </TouchableOpacity>

          <TouchableOpacity
            onPress={onClose}
            style={styles.dismissBtn}
            accessibilityRole="button"
          >
            <MayushText variant="caption" color={colors.neutral.gray700}>
              Continuer sans se connecter
            </MayushText>
          </TouchableOpacity>
        </View>
      </View>
    </Modal>
  );
};

const styles = StyleSheet.create({
  backdrop: {
    flex: 1,
    backgroundColor: 'rgba(31, 42, 58, 0.6)',
    alignItems: 'center',
    justifyContent: 'center',
    paddingHorizontal: 24,
  },
  modalContent: {
    backgroundColor: colors.surface.white,
    borderRadius: 24,
    padding: 24,
    alignItems: 'center',
    width: '100%',
    shadowColor: colors.brand.navy900,
    shadowOpacity: 0.15,
    shadowRadius: 20,
    elevation: 5,
  },
  closeBtn: {
    position: 'absolute',
    top: 16,
    right: 16,
    width: 32,
    height: 32,
    borderRadius: 16,
    backgroundColor: colors.surface.cream,
    alignItems: 'center',
    justifyContent: 'center',
  },
  iconCircle: {
    width: 64,
    height: 64,
    borderRadius: 32,
    backgroundColor: '#FFF3E6',
    alignItems: 'center',
    justifyContent: 'center',
    marginTop: 8,
    marginBottom: 16,
  },
  title: {
    fontSize: 22,
    lineHeight: 28,
    marginBottom: 8,
  },
  subtitle: {
    lineHeight: 20,
    marginBottom: 24,
  },
  primaryBtn: {
    width: '100%',
    marginBottom: 12,
  },
  secondaryBtn: {
    height: 48,
    width: '100%',
    borderRadius: 24,
    borderWidth: 1.5,
    borderColor: colors.brand.orange500,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 12,
  },
  dismissBtn: {
    paddingVertical: 8,
  },
});
