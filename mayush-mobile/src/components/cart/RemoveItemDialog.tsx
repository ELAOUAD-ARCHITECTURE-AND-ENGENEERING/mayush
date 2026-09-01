/**
 * RemoveItemDialog Component (Figma Node 309:665 - 05-cart-remove-item-confirmation-dialog-fr)
 * Modal confirmation dialog asking buyer to confirm or cancel item removal from cart.
 */

import React from 'react';
import { Image, Modal, StyleSheet, TouchableOpacity, View } from 'react-native';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { useTheme } from '../../design-system/theme/useTheme';
import { colors } from '../../design-system/tokens/colors';
import { radii } from '../../design-system/tokens/radii';

export interface RemoveItemDialogProps {
  visible: boolean;
  productName?: string;
  imageUri?: string;
  onCancel: () => void;
  onConfirm: () => void;
  onMoveToWishlist?: () => void;
}

export const RemoveItemDialog: React.FC<RemoveItemDialogProps> = ({
  visible,
  productName = "cet article",
  imageUri,
  onCancel,
  onConfirm,
  onMoveToWishlist,
}) => {
  const { isRTL, language } = useTheme();
  const direction = isRTL ? styles.rowReverse : styles.row;
  return (
    <Modal visible={visible} transparent animationType="fade" onRequestClose={onCancel}>
      <View style={styles.overlay}>
        <View style={styles.dialogCard} accessibilityLabel="Remove Item Confirmation Bottom Sheet">
          <View style={styles.handle} />
          <MayushText variant="sectionTitle" color={colors.brand.navy900} align="center">
            {language === 'ar' ? 'إزالة هذا المنتج من السلة؟' : 'Retirer cet article du panier ?'}
          </MayushText>
          <MayushText variant="smallBody" color={colors.neutral.gray700} align="center" style={styles.copy}>
            {language === 'ar' ? 'سيتم حذف هذا المنتج من سلتك.' : 'Cet article sera supprimé de votre panier.'}
          </MayushText>
          <View style={[styles.productRow, direction]}>
            {imageUri ? <Image source={{ uri: imageUri }} style={styles.image} /> : <View style={styles.imagePlaceholder} />}
            <MayushText variant="strongBody" color={colors.brand.navy900} style={styles.productName}>{productName}</MayushText>
          </View>
          <View style={[styles.actionsRow, direction]}>
            <TouchableOpacity style={styles.cancelBtn} onPress={onCancel}>
              <MayushText variant="strongBody" color={colors.brand.navy900}>
                {language === 'ar' ? 'إلغاء' : 'Annuler'}
              </MayushText>
            </TouchableOpacity>
            <TouchableOpacity style={styles.confirmBtn} onPress={onConfirm}>
              <MayushText variant="strongBody" color={colors.surface.white}>
                {language === 'ar' ? 'إزالة' : 'Retirer'}
              </MayushText>
            </TouchableOpacity>
          </View>
          {onMoveToWishlist ? (
            <TouchableOpacity style={styles.wishlistBtn} onPress={onMoveToWishlist}>
              <MayushText variant="strongBody" color={colors.brand.orange500}>{language === 'ar' ? 'نقل إلى المفضلة' : 'Déplacer vers les favoris'}</MayushText>
            </TouchableOpacity>
          ) : null}
        </View>
      </View>
    </Modal>
  );
};

const styles = StyleSheet.create({
  row: { flexDirection: 'row' },
  rowReverse: { flexDirection: 'row-reverse' },
  overlay: { flex: 1, backgroundColor: 'rgba(0,0,0,0.5)', justifyContent: 'flex-end' },
  dialogCard: { width: '100%', padding: 20, paddingBottom: 28, borderTopLeftRadius: radii.xl, borderTopRightRadius: radii.xl, backgroundColor: colors.surface.white, alignItems: 'center' },
  handle: { width: 44, height: 4, borderRadius: 2, backgroundColor: colors.neutral.gray300, marginBottom: 20 },
  copy: { marginVertical: 12, lineHeight: 18 },
  productRow: { width: '100%', flexDirection: 'row', alignItems: 'center', gap: 12, padding: 12, borderRadius: radii.lg, backgroundColor: colors.surface.creamLight, marginBottom: 16 },
  image: { width: 58, height: 58, borderRadius: radii.md },
  imagePlaceholder: { width: 58, height: 58, borderRadius: radii.md, backgroundColor: colors.surface.borderWarm },
  productName: { flex: 1 },
  actionsRow: { flexDirection: 'row', gap: 12, width: '100%', marginTop: 8 },
  cancelBtn: { flex: 1, height: 44, borderRadius: radii.lg, borderWidth: 1, borderColor: colors.surface.borderWarm, alignItems: 'center', justifyContent: 'center' },
  confirmBtn: { flex: 1, height: 44, borderRadius: radii.lg, backgroundColor: colors.semantic.error, alignItems: 'center', justifyContent: 'center' },
  wishlistBtn: { height: 44, alignItems: 'center', justifyContent: 'center', marginTop: 8 },
});
