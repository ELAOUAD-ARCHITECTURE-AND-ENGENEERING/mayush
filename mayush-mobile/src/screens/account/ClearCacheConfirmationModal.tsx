import React from 'react';
import { Modal, StyleSheet, TouchableOpacity, View } from 'react-native';
import { accountPreferencesState } from '../../commerce/accountPreferencesState';
import { cacheState } from '../../commerce/cacheState';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { colors } from '../../design-system/tokens/colors';
import { spacing } from '../../design-system/tokens/spacing';

export interface ClearCacheConfirmationModalProps {
  visible: boolean;
  onCancel: () => void;
  onConfirm: () => void;
}

export const ClearCacheConfirmationModal: React.FC<ClearCacheConfirmationModalProps> = ({
  visible,
  onCancel,
  onConfirm,
}) => {
  const isRTL = accountPreferencesState.getSelectedLanguage() === 'ar';
  const formattedSize = cacheState.getFormattedCacheSize();

  const handleConfirm = () => {
    cacheState.clearCache();
    onConfirm();
  };

  return (
    <Modal visible={visible} transparent animationType="fade" onRequestClose={onCancel}>
      <View style={styles.overlay}>
        <View style={styles.dialogCard}>
          {/* Warning Icon Box */}
          <View style={styles.iconBox}>
            <MayushIcon name="trash-2" size={24} color={colors.semantic.error} />
          </View>

          <MayushText variant="cardTitle" color={colors.brand.navy900} align="center" style={styles.dialogTitle}>
            {isRTL ? 'تفريغ الذاكرة المؤقتة؟' : 'Vider le cache ?'}
          </MayushText>

          <MayushText variant="body" color={colors.neutral.gray700} align="center" style={styles.dialogBody}>
            {isRTL
              ? `سيؤدي هذا الإجراء إلى حذف ${formattedSize} من الملفات المؤقتة. ستبقى سلتك ومفضلاتك وحسابك كما هي.`
              : `Cette action supprimera ${formattedSize} de fichiers temporaires d'images. Vos favoris, votre panier et votre compte resteront intacts.`}
          </MayushText>

          {/* Action CTAs */}
          <View style={[styles.buttonRow, isRTL && styles.rtlRow]}>
            <TouchableOpacity style={styles.cancelBtn} onPress={onCancel} activeOpacity={0.8}>
              <MayushText variant="button" color={colors.brand.navy900}>
                {isRTL ? 'إلغاء' : 'Annuler'}
              </MayushText>
            </TouchableOpacity>

            <TouchableOpacity style={styles.confirmBtn} onPress={handleConfirm} activeOpacity={0.8}>
              <MayushText variant="button" color={colors.surface.white}>
                {isRTL ? 'تأكيد التفريغ' : 'Vider le cache'}
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
    flex: 1, backgroundColor: 'rgba(0,0,0,0.5)', justifyContent: 'center',
    alignItems: 'center', padding: spacing.md,
  },
  dialogCard: {
    width: '100%', maxWidth: 340, backgroundColor: colors.neutral.white,
    borderRadius: 20, padding: spacing.lg, alignItems: 'center', gap: spacing.sm,
  },
  iconBox: {
    width: 48, height: 48, borderRadius: 14, backgroundColor: 'rgba(217,45,32,0.1)',
    alignItems: 'center', justifyContent: 'center', marginBottom: spacing.xs,
  },
  dialogTitle: { fontSize: 18, fontWeight: '700' },
  dialogBody: { lineHeight: 22, marginBottom: spacing.xs },
  buttonRow: { flexDirection: 'row', gap: spacing.sm, width: '100%', marginTop: spacing.xs },
  cancelBtn: {
    flex: 1, height: 44, borderRadius: 12, borderWidth: 1, borderColor: colors.neutral.gray300,
    alignItems: 'center', justifyContent: 'center', backgroundColor: colors.neutral.white,
  },
  confirmBtn: {
    flex: 1, height: 44, borderRadius: 12, alignItems: 'center', justifyContent: 'center',
    backgroundColor: colors.semantic.error,
  },
  rtlRow: { flexDirection: 'row-reverse' },
});
