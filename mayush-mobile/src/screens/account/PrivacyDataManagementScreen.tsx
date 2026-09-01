import React, { useState } from 'react';
import { Modal, ScrollView, StyleSheet, TouchableOpacity, View } from 'react-native';
import { accountPreferencesState } from '../../commerce/accountPreferencesState';
import { authState } from '../../commerce/authState';
import { BottomTabBar, TabKey } from '../../design-system/components/navigation/BottomTabBar';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { colors } from '../../design-system/tokens/colors';
import { spacing } from '../../design-system/tokens/spacing';

export interface PrivacyDataManagementScreenProps {
  onNavigateTab?: (tab: TabKey) => void;
  onBack?: () => void;
  onNavigatePrivacyPolicy?: () => void;
}

export const PrivacyDataManagementScreen: React.FC<PrivacyDataManagementScreenProps> = ({
  onNavigateTab,
  onBack,
  onNavigatePrivacyPolicy,
}) => {
  const isRTL = accountPreferencesState.getSelectedLanguage() === 'ar';
  const isAuthenticated = authState.getStatus() === 'authenticated';
  const buyerUser = authState.getUser();

  const [downloadRequested, setDownloadRequested] = useState(false);
  const [showDeleteModal, setShowDeleteModal] = useState(false);
  const [deleteRequestSent, setDeleteRequestSent] = useState(false);

  const handleDownloadRequest = () => {
    setDownloadRequested(true);
  };

  const handleDeletePress = () => {
    setShowDeleteModal(true);
  };

  const handleConfirmDeleteRequest = () => {
    setDeleteRequestSent(true);
  };

  return (
    <View style={styles.container}>
      {/* Top Header */}
      <View style={[styles.header, isRTL && styles.rtlRow]}>
        <TouchableOpacity style={styles.backButton} onPress={onBack} activeOpacity={0.7}>
          <MayushIcon name={isRTL ? 'chevron-right' : 'chevron-left'} size={24} color={colors.brand.navy900} />
        </TouchableOpacity>
        <MayushText variant="pageTitle" color={colors.brand.navy900} style={styles.headerTitle}>
          {isRTL ? 'الخصوصية وإدارة البيانات' : 'Confidentialité & Données'}
        </MayushText>
        <View style={{ width: 40 }} />
      </View>

      <ScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
        {/* Protection Info Card */}
        <View style={styles.card}>
          <View style={[styles.cardHeaderRow, isRTL && styles.rtlRow]}>
            <View style={styles.iconBoxBrand}>
              <MayushIcon name="shield" size={20} color={colors.brand.orange500} />
            </View>
            <View style={styles.textCol}>
              <MayushText variant="strongBody" color={colors.brand.navy900} style={isRTL && styles.rtlText}>
                {isRTL ? 'حماية البيانات الشخصية (القانون 09-08)' : 'Protection des données (Loi 09-08)'}
              </MayushText>
              <MayushText variant="smallBody" color={colors.neutral.gray500} style={isRTL && styles.rtlText}>
                {isRTL
                  ? 'بياناتك المحفوظة مؤمنة بالكامل ومستضافة وفق المعايير المغربية.'
                  : 'Vos données enregistrées au Maroc sont protégées et réservées au traitement de vos commandes.'}
              </MayushText>
            </View>
          </View>

          <View style={styles.divider} />

          <TouchableOpacity style={[styles.actionRow, isRTL && styles.rtlRow]} onPress={onNavigatePrivacyPolicy} activeOpacity={0.7}>
            <MayushIcon name="file-text" size={18} color={colors.brand.navy900} />
            <MayushText variant="strongBody" color={colors.brand.navy900} style={[{ flex: 1 }, isRTL && styles.rtlText]}>
              {isRTL ? 'قراءة سياسة الخصوصية الكاملة' : 'Consulter la Politique de Confidentialité'}
            </MayushText>
            <MayushIcon name={isRTL ? 'chevron-left' : 'chevron-right'} size={18} color={colors.neutral.gray500} />
          </TouchableOpacity>
        </View>

        {/* Data Export / Request Card */}
        <View style={styles.card}>
          <MayushText variant="sectionTitle" color={colors.brand.navy900} style={[styles.sectionTitle, isRTL && styles.rtlText]}>
            {isRTL ? 'تصدير وتحميل البيانات' : 'Gestion des données personnelles'}
          </MayushText>

          <View style={[styles.dataSummaryBox, isRTL && styles.rtlRow]}>
            <MayushIcon name="user" size={18} color={colors.neutral.gray700} />
            <View style={styles.textCol}>
              <MayushText variant="strongBody" color={colors.brand.navy900} style={isRTL && styles.rtlText}>
                {isAuthenticated ? (buyerUser?.fullName || 'Acheteur Mayush') : (isRTL ? 'حساب زائر' : 'Compte Invité')}
              </MayushText>
              <MayushText variant="smallBody" color={colors.neutral.gray500} style={isRTL && styles.rtlText}>
                {isAuthenticated ? (buyerUser?.email || 'contact@mayush.ma') : (isRTL ? 'لا يوجد بيانات شخصية مسجلة' : 'Données locales uniquement')}
              </MayushText>
            </View>
          </View>

          {downloadRequested ? (
            <View style={styles.successBanner}>
              <MayushIcon name="check-circle" size={18} color={colors.semantic.success} />
              <MayushText variant="smallBody" color={colors.semantic.success} style={[{ flex: 1 }, isRTL && styles.rtlText]}>
                {isRTL
                  ? 'تم تسجيل طلبك. سيتم إرسال رابط التصدير إلى بريدك عند المعالجة.'
                  : 'Demande enregistrée. Un lien de téléchargement sécurisé sera transmis après vérification.'}
              </MayushText>
            </View>
          ) : (
            <TouchableOpacity style={styles.secondaryBtn} onPress={handleDownloadRequest} activeOpacity={0.7}>
              <MayushIcon name="file-text" size={18} color={colors.brand.navy900} />
              <MayushText variant="strongBody" color={colors.brand.navy900}>
                {isRTL ? 'تحميل نسخة من بياناتي' : 'Télécharger une copie de mes données'}
              </MayushText>
            </TouchableOpacity>
          )}
        </View>

        {/* Danger Zone: Account Deletion */}
        <View style={[styles.card, styles.dangerCard]}>
          <MayushText variant="sectionTitle" color={colors.semantic.error} style={[styles.sectionTitle, isRTL && styles.rtlText]}>
            {isRTL ? 'منطقة الخطر' : 'Zone de Danger'}
          </MayushText>

          <TouchableOpacity style={[styles.actionRow, isRTL && styles.rtlRow]} onPress={handleDeletePress} activeOpacity={0.7}>
            <View style={styles.iconBoxError}>
              <MayushIcon name="trash-2" size={18} color={colors.semantic.error} />
            </View>
            <View style={styles.textCol}>
              <MayushText variant="strongBody" color={colors.semantic.error} style={isRTL && styles.rtlText}>
                {isRTL ? 'حذف حسابي على مايووش' : 'Supprimer mon compte Mayush'}
              </MayushText>
              <MayushText variant="smallBody" color={colors.neutral.gray500} style={isRTL && styles.rtlText}>
                {isRTL ? 'طلب إغلاق الحساب وحذف المعلومات المسجلة' : 'Demande de fermeture définitive et suppression des données'}
              </MayushText>
            </View>
            <MayushIcon name={isRTL ? 'chevron-left' : 'chevron-right'} size={18} color={colors.semantic.error} />
          </TouchableOpacity>
        </View>
      </ScrollView>

      {/* Account Deletion Modal */}
      <Modal visible={showDeleteModal} animationType="fade" transparent={true} onRequestClose={() => setShowDeleteModal(false)}>
        <View style={styles.modalOverlay}>
          <View style={styles.modalCard}>
            <View style={styles.dangerIconBadge}>
              <MayushIcon name="info" size={28} color={colors.semantic.error} />
            </View>

            <MayushText variant="sectionTitle" color={colors.brand.navy900} style={{ textAlign: 'center' }}>
              {isRTL ? 'Demande de suppression de compte' : 'Suppression du compte Mayush'}
            </MayushText>

            {deleteRequestSent ? (
              <View style={styles.deleteSuccessBox}>
                <MayushText variant="body" color={colors.brand.navy900} style={{ textAlign: 'center' }}>
                  {isRTL
                    ? 'تم تسجيل طلبك بنجاح. سيتم مراجعة الطلب من طرف فريق الدعم قبل الإغلاق.'
                    : 'Votre demande de suppression a été transmise à l\'équipe support pour vérification avant fermeture.'}
                </MayushText>
                <TouchableOpacity style={styles.primaryModalBtn} onPress={() => { setShowDeleteModal(false); setDeleteRequestSent(false); }}>
                  <MayushText variant="strongBody" color={colors.neutral.white}>
                    {isRTL ? 'موافق' : 'Compris'}
                  </MayushText>
                </TouchableOpacity>
              </View>
            ) : (
              <>
                <MayushText variant="body" color={colors.neutral.gray700} style={{ textAlign: 'center', lineHeight: 20 }}>
                  {isRTL
                    ? 'هل أنت تأكد من رغبتك في إغلاق الحساب؟ يتطلب هذا الإجراء تأكيداً عبر البريد لتأمين معاملاتك السابقة.'
                    : 'La suppression de votre compte nécessite une vérification backend de vos commandes en cours. Vos données locales actuelles restent protégées.'}
                </MayushText>

                <View style={styles.modalBtnRow}>
                  <TouchableOpacity style={styles.cancelModalBtn} onPress={() => setShowDeleteModal(false)}>
                    <MayushText variant="strongBody" color={colors.brand.navy900}>
                      {isRTL ? 'إلغاء' : 'Annuler'}
                    </MayushText>
                  </TouchableOpacity>
                  <TouchableOpacity style={styles.dangerModalBtn} onPress={handleConfirmDeleteRequest}>
                    <MayushText variant="strongBody" color={colors.neutral.white}>
                      {isRTL ? 'تأكيد الطلب' : 'Confirmer la demande'}
                    </MayushText>
                  </TouchableOpacity>
                </View>
              </>
            )}
          </View>
        </View>
      </Modal>

      <BottomTabBar activeTab="account" onTabPress={(tab) => onNavigateTab?.(tab)} />
    </View>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: colors.neutral.gray100 },
  header: {
    height: 56, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between',
    paddingHorizontal: spacing.md, backgroundColor: colors.neutral.white,
    borderBottomWidth: 1, borderBottomColor: colors.neutral.gray300,
  },
  headerTitle: { fontSize: 18, fontWeight: '700' },
  backButton: { width: 40, height: 40, alignItems: 'center', justifyContent: 'center' },
  scrollContent: { padding: spacing.md, gap: spacing.md, paddingBottom: 100 },
  card: {
    backgroundColor: colors.neutral.white, borderRadius: 16, padding: spacing.md,
    borderWidth: 1, borderColor: colors.neutral.gray300, gap: spacing.xs,
  },
  dangerCard: { borderColor: 'rgba(235,87,87,0.3)', backgroundColor: 'rgba(235,87,87,0.03)' },
  cardHeaderRow: { flexDirection: 'row', alignItems: 'center', gap: spacing.sm },
  iconBoxBrand: { width: 40, height: 40, borderRadius: 10, backgroundColor: 'rgba(217,116,52,0.1)', alignItems: 'center', justifyContent: 'center' },
  iconBoxError: { width: 36, height: 36, borderRadius: 10, backgroundColor: 'rgba(235,87,87,0.1)', alignItems: 'center', justifyContent: 'center' },
  textCol: { flex: 1 },
  divider: { height: 1, backgroundColor: colors.neutral.gray300, marginVertical: spacing.xs },
  sectionTitle: { fontSize: 15, fontWeight: '700', marginBottom: spacing.xs },
  actionRow: { flexDirection: 'row', alignItems: 'center', gap: spacing.sm, paddingVertical: 4 },
  dataSummaryBox: { flexDirection: 'row', alignItems: 'center', gap: spacing.sm, backgroundColor: colors.neutral.gray100, borderRadius: 12, padding: spacing.sm, marginBottom: spacing.xs },
  secondaryBtn: { flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: spacing.xs, backgroundColor: colors.neutral.gray100, borderRadius: 12, paddingVertical: 12 },
  successBanner: { flexDirection: 'row', alignItems: 'center', gap: spacing.xs, backgroundColor: colors.semantic.successBackground, borderRadius: 12, padding: spacing.sm },
  rtlRow: { flexDirection: 'row-reverse' },
  rtlText: { textAlign: 'right' },
  modalOverlay: { flex: 1, backgroundColor: 'rgba(0,0,0,0.5)', justifyContent: 'center', alignItems: 'center', padding: spacing.md },
  modalCard: { width: '100%', maxWidth: 360, backgroundColor: colors.neutral.white, borderRadius: 20, padding: spacing.lg, gap: spacing.md, alignItems: 'center' },
  dangerIconBadge: { width: 56, height: 56, borderRadius: 28, backgroundColor: 'rgba(235,87,87,0.1)', alignItems: 'center', justifyContent: 'center' },
  deleteSuccessBox: { width: '100%', gap: spacing.md, alignItems: 'center' },
  modalBtnRow: { flexDirection: 'row', gap: spacing.sm, width: '100%' },
  cancelModalBtn: { flex: 1, backgroundColor: colors.neutral.gray100, borderRadius: 12, paddingVertical: 12, alignItems: 'center' },
  dangerModalBtn: { flex: 1, backgroundColor: colors.semantic.error, borderRadius: 12, paddingVertical: 12, alignItems: 'center' },
  primaryModalBtn: { width: '100%', backgroundColor: colors.brand.navy900, borderRadius: 12, paddingVertical: 12, alignItems: 'center' },
});
