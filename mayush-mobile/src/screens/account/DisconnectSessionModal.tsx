import React from 'react';
import { Modal, StyleSheet, TouchableOpacity, View } from 'react-native';
import { ActiveSession } from '../../commerce/authState';
import { PrimaryButton } from '../../design-system/components/actions/PrimaryButton';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { colors } from '../../design-system/tokens/colors';
import { spacing } from '../../design-system/tokens/spacing';

export interface DisconnectSessionModalProps {
  visible: boolean;
  session: ActiveSession | null;
  onCancel: () => void;
  onConfirm: () => void;
  language?: 'fr' | 'ar';
}

export const DisconnectSessionModal: React.FC<DisconnectSessionModalProps> = ({
  visible,
  session,
  onCancel,
  onConfirm,
  language = 'fr',
}) => {
  if (!visible || !session) return null;
  const isRTL = language === 'ar';

  return (
    <Modal visible={visible} transparent animationType="fade" onRequestClose={onCancel}>
      <View style={styles.overlay}>
        <View style={styles.dialogCard}>
          {/* Header Icon */}
          <View style={styles.iconCircle}>
            <MayushIcon name="trash-2" size={28} color={colors.semantic.error} />
          </View>

          {/* Title */}
          <MayushText variant="pageTitle" color={colors.brand.navy900} style={[styles.title, isRTL && styles.rtlText]}>
            {language === 'ar' ? 'إنهاء هذه الجلسة؟' : 'Déconnecter cet appareil ?'}
          </MayushText>

          {/* Session Details Box */}
          <View style={styles.sessionBox}>
            <MayushText variant="cardTitle" color={colors.brand.navy900} style={isRTL && styles.rtlText}>
              {session.device}
            </MayushText>
            <MayushText variant="body" color={colors.neutral.gray700} style={isRTL && styles.rtlText}>
              {session.browser}
            </MayushText>
            <MayushText variant="caption" color={colors.neutral.gray500} style={isRTL && styles.rtlText}>
              {`${session.location} • ${session.lastActive}`}
            </MayushText>
          </View>

          {/* Explanation */}
          <MayushText variant="body" color={colors.neutral.gray700} style={[styles.description, isRTL && styles.rtlText]}>
            {language === 'ar'
              ? 'سيتم تسجيل الخروج فوراً من هذا الجهاز وسيتوجب عليك إدخال كلمة المرور مرة أخرى للوصول.'
              : "Cette session sera immédiatement fermée et l'appareil devra se reconnecter."}
          </MayushText>

          {/* Action Buttons */}
          <View style={styles.buttonGroup}>
            <PrimaryButton
              label={language === 'ar' ? 'تأكيد الإنهاء' : 'Confirmer la déconnexion'}
              onPress={onConfirm}
              style={styles.confirmButton}
            />

            <TouchableOpacity accessibilityRole="button" onPress={onCancel} style={styles.cancelButton}>
              <MayushText variant="button" color={colors.neutral.gray700}>
                {language === 'ar' ? 'إلغاء' : 'Annuler'}
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
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.5)',
    justifyContent: 'center',
    alignItems: 'center',
    padding: spacing.md,
  },
  dialogCard: {
    width: '100%',
    maxWidth: 360,
    borderRadius: 24,
    backgroundColor: colors.surface.white,
    padding: spacing.lg,
    alignItems: 'center',
    gap: 12,
  },
  iconCircle: {
    width: 56,
    height: 56,
    borderRadius: 28,
    backgroundColor: '#FFEEEC',
    alignItems: 'center',
    justifyContent: 'center',
  },
  title: { fontSize: 18, fontWeight: '700', textAlign: 'center' },
  sessionBox: {
    width: '100%',
    padding: 12,
    borderRadius: 14,
    backgroundColor: colors.surface.cream,
    alignItems: 'center',
    gap: 4,
  },
  description: { fontSize: 13, lineHeight: 18, textAlign: 'center', color: colors.neutral.gray500 },
  buttonGroup: { width: '100%', gap: 10, marginTop: 8 },
  confirmButton: { backgroundColor: colors.semantic.error },
  cancelButton: {
    height: 48,
    borderRadius: 16,
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: colors.surface.creamLight,
  },
  rtlText: { writingDirection: 'rtl' },
});
