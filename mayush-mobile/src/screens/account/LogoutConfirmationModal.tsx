import React from 'react';
import { Modal, StyleSheet, TouchableOpacity, View } from 'react-native';
import { authState } from '../../commerce/authState';
import { PrimaryButton } from '../../design-system/components/actions/PrimaryButton';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { colors } from '../../design-system/tokens/colors';
import { spacing } from '../../design-system/tokens/spacing';

export interface LogoutConfirmationModalProps {
  visible: boolean;
  onCancel: () => void;
  onConfirmLogout: () => void;
  language?: 'fr' | 'ar' | 'en';
}

export const LogoutConfirmationModal: React.FC<LogoutConfirmationModalProps> = ({
  visible,
  onCancel,
  onConfirmLogout,
  language = 'fr',
}) => {
  if (!visible) return null;
  const isRTL = language === 'ar';

  const copy = language === 'ar'
    ? {
        title: 'تسجيل الخروج؟',
        description: 'هل أنت تأكد من رغبتك في تسجيل الخروج من حسابك؟ سيتم حفظ السلة والعديد من تفضيلاتك.',
        confirm: 'تسجيل الخروج',
        cancel: 'إلغاء',
      }
    : language === 'en'
    ? {
        title: 'Log out?',
        description: 'Are you sure you want to log out of your account? Your cart and wishlist items will remain saved.',
        confirm: 'Log out',
        cancel: 'Cancel',
      }
    : {
        title: 'Se déconnecter ?',
        description: 'Êtes-vous sûr de vouloir vous déconnecter de votre compte ? Votre panier et vos favoris resteront conservés.',
        confirm: 'Se déconnecter',
        cancel: 'Annuler',
      };

  const handleConfirm = () => {
    authState.logout();
    onConfirmLogout();
  };

  return (
    <Modal visible={visible} transparent animationType="fade" onRequestClose={onCancel}>
      <View style={styles.overlay}>
        <View style={styles.dialogCard}>
          <View style={styles.iconCircle}>
            <MayushIcon name="log-out" size={28} color={colors.semantic.error} />
          </View>

          <MayushText variant="pageTitle" color={colors.brand.navy900} style={[styles.title, isRTL && styles.rtlText]}>
            {copy.title}
          </MayushText>

          <MayushText variant="body" color={colors.neutral.gray700} style={[styles.description, isRTL && styles.rtlText]}>
            {copy.description}
          </MayushText>

          <View style={styles.buttonGroup}>
            <PrimaryButton
              label={copy.confirm}
              onPress={handleConfirm}
              style={styles.confirmButton}
            />
            <TouchableOpacity accessibilityRole="button" onPress={onCancel} style={styles.cancelButton}>
              <MayushText variant="button" color={colors.neutral.gray700}>
                {copy.cancel}
              </MayushText>
            </TouchableOpacity>
          </View>
        </View>
      </View>
    </Modal>
  );
};

const styles = StyleSheet.create({
  overlay: {
    flex: 1, backgroundColor: 'rgba(0, 0, 0, 0.5)',
    justifyContent: 'center', alignItems: 'center', padding: spacing.md,
  },
  dialogCard: {
    width: '100%', maxWidth: 360, borderRadius: 24, backgroundColor: colors.surface.white,
    padding: spacing.lg, alignItems: 'center', gap: 12,
  },
  iconCircle: {
    width: 56, height: 56, borderRadius: 28, backgroundColor: '#FFEEEC',
    alignItems: 'center', justifyContent: 'center',
  },
  title: { fontSize: 18, fontWeight: '700', textAlign: 'center' },
  description: { fontSize: 13, lineHeight: 18, textAlign: 'center', color: colors.neutral.gray700 },
  buttonGroup: { width: '100%', gap: 10, marginTop: 8 },
  confirmButton: { backgroundColor: colors.semantic.error },
  cancelButton: {
    height: 48, borderRadius: 16, alignItems: 'center', justifyContent: 'center',
    backgroundColor: colors.surface.creamLight,
  },
  rtlText: { writingDirection: 'rtl' },
});
