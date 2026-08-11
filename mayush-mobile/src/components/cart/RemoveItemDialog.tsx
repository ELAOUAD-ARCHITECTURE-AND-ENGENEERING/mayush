/**
 * RemoveItemDialog Component (Figma Node 309:665 - 05-cart-remove-item-confirmation-dialog-fr)
 * Modal confirmation dialog asking buyer to confirm or cancel item removal from cart.
 */

import React from 'react';
import { Modal, StyleSheet, TouchableOpacity, View } from 'react-native';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { colors } from '../../design-system/tokens/colors';
import { radii } from '../../design-system/tokens/radii';

export interface RemoveItemDialogProps {
  visible: boolean;
  productName?: string;
  onCancel: () => void;
  onConfirm: () => void;
}

export const RemoveItemDialog: React.FC<RemoveItemDialogProps> = ({
  visible,
  productName = "cet article",
  onCancel,
  onConfirm,
}) => {
  return (
    <Modal visible={visible} transparent animationType="fade" onRequestClose={onCancel}>
      <View style={styles.overlay}>
        <View style={styles.dialogCard} accessibilityLabel="Remove Item Confirmation Dialog">
          <MayushText variant="sectionTitle" color={colors.brand.navy900} align="center">
            Supprimer l'article ?
          </MayushText>
          <MayushText variant="smallBody" color={colors.neutral.gray700} align="center" style={styles.copy}>
            Voulez-vous vraiment retirer {productName} de votre panier ?
          </MayushText>
          <View style={styles.actionsRow}>
            <TouchableOpacity style={styles.cancelBtn} onPress={onCancel}>
              <MayushText variant="strongBody" color={colors.brand.navy900}>
                Annuler
              </MayushText>
            </TouchableOpacity>
            <TouchableOpacity style={styles.confirmBtn} onPress={onConfirm}>
              <MayushText variant="strongBody" color={colors.surface.white}>
                Supprimer
              </MayushText>
            </TouchableOpacity>
          </View>
        </View>
      </View>
    </Modal>
  );
};

const styles = StyleSheet.create({
  overlay: { flex: 1, backgroundColor: 'rgba(0,0,0,0.5)', justifyContent: 'center', alignItems: 'center', padding: 20 },
  dialogCard: { width: '100%', padding: 20, borderRadius: radii.xl, backgroundColor: colors.surface.white, alignItems: 'center' },
  copy: { marginVertical: 12, lineHeight: 18 },
  actionsRow: { flexDirection: 'row', gap: 12, width: '100%', marginTop: 8 },
  cancelBtn: { flex: 1, height: 44, borderRadius: radii.lg, borderWidth: 1, borderColor: colors.surface.borderWarm, alignItems: 'center', justifyContent: 'center' },
  confirmBtn: { flex: 1, height: 44, borderRadius: radii.lg, backgroundColor: colors.semantic.error, alignItems: 'center', justifyContent: 'center' },
});
