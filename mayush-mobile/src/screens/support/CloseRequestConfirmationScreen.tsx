import React from 'react';
import { View, StyleSheet, ScrollView, TouchableOpacity } from 'react-native';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { colors } from '../../design-system/tokens/colors';
import { spacing } from '../../design-system/tokens/spacing';
import { supportState } from '../../commerce/supportState';
import { accountPreferencesState } from '../../commerce/accountPreferencesState';
import { TabKey } from '../../design-system/components/navigation/BottomTabBar';

interface CloseRequestConfirmationScreenProps {
  onNavigateTab: (tab: TabKey) => void;
  onBack: () => void;
  onConfirmClose: () => void;
}

export const CloseRequestConfirmationScreen: React.FC<CloseRequestConfirmationScreenProps> = ({
  onNavigateTab,
  onBack,
  onConfirmClose,
}) => {
  const language = accountPreferencesState.getLanguage();
  const isRTL = language === 'ar';

  const handleClose = () => {
    const currentId = supportState.getSelectedSupportRequestId();
    if (currentId) {
      supportState.closeSupportRequest(currentId);
    }
    onConfirmClose();
  };

  return (
    <View style={styles.container}>
      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity onPress={onBack} style={styles.backBtn} activeOpacity={0.7}>
          <MayushIcon name={isRTL ? 'arrow-right' : 'arrow-left'} size={22} color={colors.brand.navy900} />
        </TouchableOpacity>
        <MayushText variant="cardTitle" style={styles.headerLogo}>
          MAYUSH<MayushText variant="cardTitle" color={colors.brand.orange500}> DESIGN</MayushText>
        </MayushText>
        <TouchableOpacity onPress={handleClose} style={styles.bellBtn} activeOpacity={0.7}>
          <MayushIcon name="bell" size={20} color={colors.brand.navy900} />
        </TouchableOpacity>
      </View>

      <ScrollView style={styles.scrollView} contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
        {/* Header Illustration */}
        <View style={styles.illustrationBox}>
          <View style={styles.illustrationCircle}>
            <MayushIcon name="x-circle" size={40} color={colors.brand.orange500} />
            <View style={styles.shieldBadge}>
              <MayushIcon name="lock" size={14} color={colors.neutral.white} />
            </View>
          </View>
        </View>

        {/* Title Section */}
        <MayushText variant="pageTitle" color={colors.brand.navy900} style={[styles.title, isRTL && styles.rtlText]}>
          {isRTL ? 'هل أنت تأكد من إغلاق هذا الطلب؟' : 'Êtes-vous sûr de vouloir clôturer cette demande ?'}
        </MayushText>
        <MayushText variant="body" color={colors.neutral.gray500} style={[styles.subtitle, isRTL && styles.rtlText]}>
          {isRTL
            ? 'بمجرد إغلاقه، لا يمكن تعديل هذا الطلب. يمكنك دائماً الاستشارة في سجل طلباتك.'
            : 'Une fois clôturée, cette demande ne pourra plus être modifiée. Vous pourrez toujours la consulter dans l’historique de vos demandes.'}
        </MayushText>

        {/* Warning Info Box */}
        <View style={styles.warningCard}>
          <MayushIcon name="alert-triangle" size={22} color="#D97706" />
          <MayushText variant="smallBody" color={colors.brand.navy900} style={[{ flex: 1, lineHeight: 18 }, isRTL && styles.rtlText]}>
            {isRTL
              ? 'إذا لم يتم حل طلبك بعد، يمكنك إبقاؤه مفتوحاً لمتابعة المتابعة مع فريقنا.'
              : 'Si votre demande n’est pas encore résolue, vous pouvez la conserver ouverte pour continuer le suivi avec notre équipe.'}
          </MayushText>
        </View>

        {/* Action Buttons */}
        <TouchableOpacity style={styles.secondaryBtn} onPress={onBack} activeOpacity={0.85}>
          <MayushText variant="strongBody" color={colors.brand.navy900}>
            {isRTL ? 'الإبقاء على الطلب مفتوحاً' : 'Conserver la demande ouverte'}
          </MayushText>
          <MayushIcon name={isRTL ? 'chevron-left' : 'chevron-right'} size={18} color={colors.brand.navy900} />
        </TouchableOpacity>

        <TouchableOpacity style={styles.primaryBtn} onPress={handleClose} activeOpacity={0.85}>
          <MayushText variant="strongBody" color={colors.neutral.white}>
            {isRTL ? 'إغلاق الطلب' : 'Clôturer la demande'}
          </MayushText>
        </TouchableOpacity>

        {/* Security Note */}
        <View style={styles.securityNote}>
          <MayushIcon name="lock" size={14} color={colors.neutral.gray500} />
          <MayushText variant="smallBody" color={colors.neutral.gray500}>
            {isRTL ? 'معلوماتك آمنة ولن يتم مشاركتها أبداً.' : 'Vos informations sont sécurisées et ne seront jamais partagées.'}
          </MayushText>
        </View>
      </ScrollView>
    </View>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#FAF8F5' },
  header: {
    flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between',
    paddingHorizontal: spacing.md, paddingTop: 48, paddingBottom: spacing.sm,
    backgroundColor: '#FAF8F5',
  },
  backBtn: { padding: spacing.xs },
  headerLogo: { fontSize: 18, fontWeight: '800', color: colors.brand.navy900 },
  bellBtn: { padding: spacing.xs },
  scrollView: { flex: 1 },
  scrollContent: { padding: spacing.md, paddingBottom: 40, alignItems: 'center' },
  illustrationBox: { marginVertical: spacing.md, alignItems: 'center' },
  illustrationCircle: {
    width: 88, height: 88, borderRadius: 44, backgroundColor: colors.neutral.white,
    borderWidth: 1, borderColor: 'rgba(0,0,0,0.06)', alignItems: 'center', justifyContent: 'center',
    shadowColor: '#000', shadowOffset: { width: 0, height: 2 }, shadowOpacity: 0.05, shadowRadius: 8,
  },
  shieldBadge: {
    position: 'absolute', bottom: 4, right: 4, backgroundColor: colors.brand.navy900,
    borderRadius: 12, padding: 4,
  },
  title: { textAlign: 'center', marginBottom: 6, paddingHorizontal: spacing.xs },
  subtitle: { textAlign: 'center', fontSize: 13, lineHeight: 18, marginBottom: spacing.lg, paddingHorizontal: spacing.sm },
  warningCard: {
    width: '100%', flexDirection: 'row', alignItems: 'center', gap: spacing.xs,
    backgroundColor: '#FFF8E6', borderWidth: 1, borderColor: '#FDE68A',
    borderRadius: 16, padding: spacing.md, marginBottom: spacing.lg,
  },
  secondaryBtn: {
    width: '100%', flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: spacing.xs,
    backgroundColor: colors.neutral.white, borderWidth: 1, borderColor: colors.brand.navy900,
    borderRadius: 14, paddingVertical: 14, marginBottom: spacing.sm,
  },
  primaryBtn: {
    width: '100%', flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: spacing.xs,
    backgroundColor: colors.brand.orange500, borderRadius: 14, paddingVertical: 14, marginBottom: spacing.lg,
  },
  securityNote: { flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 6 },
  rtlText: { textAlign: 'right' },
});
