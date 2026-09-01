import React, { useState, useEffect } from 'react';
import { View, StyleSheet, ScrollView, TouchableOpacity } from 'react-native';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { colors } from '../../design-system/tokens/colors';
import { spacing } from '../../design-system/tokens/spacing';
import { supportState, ContactDraft } from '../../commerce/supportState';
import { accountPreferencesState } from '../../commerce/accountPreferencesState';
import { TabKey } from '../../design-system/components/navigation/BottomTabBar';

interface ReviewSendSupportRequestScreenProps {
  onNavigateTab: (tab: TabKey) => void;
  onBack: () => void;
  onEditForm: () => void;
  onEditOrder: () => void;
  onEditAttachments: () => void;
  onSendSuccess: (ticketId: string) => void;
}

export const ReviewSendSupportRequestScreen: React.FC<ReviewSendSupportRequestScreenProps> = ({
  onNavigateTab,
  onBack,
  onEditForm,
  onEditOrder,
  onEditAttachments,
  onSendSuccess,
}) => {
  const [draft, setDraft] = useState<ContactDraft>(supportState.getContactDraft());
  const [language, setLanguage] = useState(accountPreferencesState.getLanguage());

  useEffect(() => {
    const unsubSupport = supportState.subscribe(() => {
      setDraft(supportState.getContactDraft());
    });
    const unsubPref = accountPreferencesState.subscribe(() => {
      setLanguage(accountPreferencesState.getLanguage());
    });
    return () => {
      unsubSupport();
      unsubPref();
    };
  }, []);

  const isRTL = language === 'ar';
  const categoryObj = supportState.getFaqCategories().find((c) => c.id === draft.categoryId);

  const handleSubmit = () => {
    const newReq = supportState.createSupportRequest(draft);
    onSendSuccess(newReq.id);
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
        <TouchableOpacity onPress={handleSubmit} style={styles.bellBtn} activeOpacity={0.7}>
          <MayushIcon name="bell" size={20} color={colors.brand.navy900} />
        </TouchableOpacity>
      </View>

      <ScrollView style={styles.scrollView} contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
        {/* Header Illustration */}
        <View style={styles.illustrationBox}>
          <View style={styles.illustrationCircle}>
            <MayushIcon name="file-text" size={32} color={colors.brand.orange500} />
            <View style={styles.checkBadge}>
              <MayushIcon name="check" size={14} color={colors.neutral.white} />
            </View>
          </View>
        </View>

        {/* Title Section */}
        <MayushText variant="pageTitle" color={colors.brand.navy900} style={[styles.title, isRTL && styles.rtlText]}>
          {isRTL ? 'المراجعة والإرسال' : 'Vérifier et envoyer'}
        </MayushText>
        <MayushText variant="body" color={colors.neutral.gray500} style={[styles.subtitle, isRTL && styles.rtlText]}>
          {isRTL
            ? 'يرجى التحقق من المعلومات أدناه قبل إرسال طلب المساعدة الخاص بك.'
            : 'Veuillez vérifier les informations ci-dessous avant d’envoyer votre demande d’assistance.'}
        </MayushText>

        {/* Summary Card 1: Category */}
        <View style={styles.summaryCard}>
          <View style={styles.cardRow}>
            <View style={styles.cardIconBadge}>
              <MayushIcon name="grid" size={18} color={colors.brand.orange500} />
            </View>
            <View style={{ flex: 1 }}>
              <MayushText variant="smallBody" color={colors.neutral.gray500}>
                {isRTL ? 'الفئة' : 'Catégorie'}
              </MayushText>
              <MayushText variant="strongBody" color={colors.brand.navy900} style={{ fontSize: 13, marginTop: 2 }}>
                {categoryObj ? categoryObj.label : 'Commandes & Livraison'}
              </MayushText>
            </View>
            <TouchableOpacity onPress={onEditForm} style={styles.editBtn}>
              <MayushIcon name="edit-3" size={14} color={colors.brand.orange500} />
              <MayushText variant="smallBody" color={colors.brand.orange500} style={{ fontWeight: '600' }}>
                {isRTL ? 'تعديل' : 'Modifier'}
              </MayushText>
            </TouchableOpacity>
          </View>
        </View>

        {/* Summary Card 2: Associated Order */}
        {draft.selectedOrderId ? (
          <View style={styles.summaryCard}>
            <View style={styles.cardRow}>
              <View style={styles.cardIconBadge}>
                <MayushIcon name="file-text" size={18} color={colors.brand.orange500} />
              </View>
              <View style={{ flex: 1 }}>
                <MayushText variant="smallBody" color={colors.neutral.gray500}>
                  {isRTL ? 'الطلب المعني' : 'Commande concernée'}
                </MayushText>
                <MayushText variant="strongBody" color={colors.brand.navy900} style={{ fontSize: 13, marginTop: 2 }}>
                  {draft.selectedOrderId}
                </MayushText>
              </View>
              <TouchableOpacity onPress={onEditOrder} style={styles.editBtn}>
                <MayushIcon name="edit-3" size={14} color={colors.brand.orange500} />
                <MayushText variant="smallBody" color={colors.brand.orange500} style={{ fontWeight: '600' }}>
                  {isRTL ? 'تعديل' : 'Modifier'}
                </MayushText>
              </TouchableOpacity>
            </View>
          </View>
        ) : null}

        {/* Summary Card 3: Subject */}
        <View style={styles.summaryCard}>
          <View style={styles.cardRow}>
            <View style={styles.cardIconBadge}>
              <MayushIcon name="message-circle" size={18} color={colors.brand.orange500} />
            </View>
            <View style={{ flex: 1 }}>
              <MayushText variant="smallBody" color={colors.neutral.gray500}>
                {isRTL ? 'الموضوع' : 'Sujet'}
              </MayushText>
              <MayushText variant="strongBody" color={colors.brand.navy900} style={{ fontSize: 13, marginTop: 2 }}>
                {draft.subject || 'Demande d\'assistance'}
              </MayushText>
            </View>
            <TouchableOpacity onPress={onEditForm} style={styles.editBtn}>
              <MayushIcon name="edit-3" size={14} color={colors.brand.orange500} />
              <MayushText variant="smallBody" color={colors.brand.orange500} style={{ fontWeight: '600' }}>
                {isRTL ? 'تعديل' : 'Modifier'}
              </MayushText>
            </TouchableOpacity>
          </View>
        </View>

        {/* Summary Card 4: Message */}
        <View style={styles.summaryCard}>
          <View style={styles.cardRow}>
            <View style={styles.cardIconBadge}>
              <MayushIcon name="align-left" size={18} color={colors.brand.orange500} />
            </View>
            <View style={{ flex: 1 }}>
              <MayushText variant="smallBody" color={colors.neutral.gray500}>
                {isRTL ? 'الرسالة' : 'Message'}
              </MayushText>
              <MayushText variant="body" color={colors.brand.navy900} style={[{ fontSize: 13, marginTop: 4, lineHeight: 18 }, isRTL && styles.rtlText]}>
                {draft.message || '—'}
              </MayushText>
            </View>
            <TouchableOpacity onPress={onEditForm} style={styles.editBtn}>
              <MayushIcon name="edit-3" size={14} color={colors.brand.orange500} />
              <MayushText variant="smallBody" color={colors.brand.orange500} style={{ fontWeight: '600' }}>
                {isRTL ? 'تعديل' : 'Modifier'}
              </MayushText>
            </TouchableOpacity>
          </View>
        </View>

        {/* Summary Card 5: Attachments */}
        <View style={styles.summaryCard}>
          <View style={styles.cardRow}>
            <View style={styles.cardIconBadge}>
              <MayushIcon name="paperclip" size={18} color={colors.brand.orange500} />
            </View>
            <View style={{ flex: 1 }}>
              <MayushText variant="smallBody" color={colors.neutral.gray500}>
                {isRTL ? 'المرفقات' : 'Pièces jointes'}
              </MayushText>
              <MayushText variant="strongBody" color={colors.brand.navy900} style={{ fontSize: 13, marginTop: 2 }}>
                {draft.attachments.length > 0
                  ? `${draft.attachments.length} ${isRTL ? 'ملفات مرفقة' : 'fichier(s) joint(s)'}`
                  : isRTL ? 'لا توجد مرفقات' : 'Aucune pièce jointe'}
              </MayushText>
              {draft.attachments.map((att) => (
                <MayushText key={att.id} variant="smallBody" color={colors.neutral.gray500} style={{ marginTop: 2 }}>
                  • {att.name} ({att.size})
                </MayushText>
              ))}
            </View>
            <TouchableOpacity onPress={onEditAttachments} style={styles.editBtn}>
              <MayushIcon name="edit-3" size={14} color={colors.brand.orange500} />
              <MayushText variant="smallBody" color={colors.brand.orange500} style={{ fontWeight: '600' }}>
                {isRTL ? 'تعديل' : 'Modifier'}
              </MayushText>
            </TouchableOpacity>
          </View>
        </View>

        {/* Summary Card 6: Contact Info */}
        <View style={styles.summaryCard}>
          <View style={styles.cardRow}>
            <View style={styles.cardIconBadge}>
              <MayushIcon name="user" size={18} color={colors.brand.orange500} />
            </View>
            <View style={{ flex: 1 }}>
              <MayushText variant="smallBody" color={colors.neutral.gray500}>
                {isRTL ? 'معلومات الاتصال الخاصة بك' : 'Vos informations de contact'}
              </MayushText>
              <MayushText variant="strongBody" color={colors.brand.navy900} style={{ fontSize: 13, marginTop: 2 }}>
                {draft.email}
              </MayushText>
              <MayushText variant="smallBody" color={colors.neutral.gray500}>
                {isRTL ? 'قناة الرد: ' : 'Canal : '}{draft.preferredChannel}
              </MayushText>
            </View>
            <TouchableOpacity onPress={onEditForm} style={styles.editBtn}>
              <MayushIcon name="edit-3" size={14} color={colors.brand.orange500} />
              <MayushText variant="smallBody" color={colors.brand.orange500} style={{ fontWeight: '600' }}>
                {isRTL ? 'تعديل' : 'Modifier'}
              </MayushText>
            </TouchableOpacity>
          </View>
        </View>

        {/* Primary Send CTA Button */}
        <TouchableOpacity style={styles.primaryBtn} onPress={handleSubmit} activeOpacity={0.85}>
          <MayushIcon name="send" size={18} color={colors.neutral.white} />
          <MayushText variant="strongBody" color={colors.neutral.white}>
            {isRTL ? 'إرسال طلبي' : 'Envoyer ma demande'}
          </MayushText>
        </TouchableOpacity>

        {/* Security Disclaimer */}
        <View style={styles.securityRow}>
          <MayushIcon name="lock" size={14} color={colors.neutral.gray500} />
          <MayushText variant="smallBody" color={colors.neutral.gray500} style={{ textAlign: 'center' }}>
            {isRTL
              ? 'معلوماتك آمنة ولن يتم مشاركتها أبداً.'
              : 'Vos informations sont sécurisées et ne seront jamais partagées.'}
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
  scrollContent: { padding: spacing.md, paddingBottom: 40 },
  illustrationBox: { alignItems: 'center', marginVertical: spacing.sm },
  illustrationCircle: {
    width: 64, height: 64, borderRadius: 32, backgroundColor: '#FFF2EB',
    alignItems: 'center', justifyContent: 'center',
  },
  checkBadge: {
    position: 'absolute', bottom: 4, right: 4, backgroundColor: colors.brand.orange500,
    borderRadius: 10, width: 20, height: 20, alignItems: 'center', justifyContent: 'center',
  },
  title: { textAlign: 'center', marginBottom: 4 },
  subtitle: { textAlign: 'center', fontSize: 13, lineHeight: 18, marginBottom: spacing.md },
  summaryCard: {
    backgroundColor: colors.neutral.white, borderRadius: 16, padding: spacing.md,
    marginBottom: spacing.sm, borderWidth: 1, borderColor: 'rgba(0,0,0,0.05)',
  },
  cardRow: { flexDirection: 'row', alignItems: 'flex-start', gap: spacing.xs },
  cardIconBadge: {
    width: 36, height: 36, borderRadius: 18, backgroundColor: 'rgba(232,125,62,0.1)',
    alignItems: 'center', justifyContent: 'center', marginTop: 2,
  },
  editBtn: { flexDirection: 'row', alignItems: 'center', gap: 4, padding: 4 },
  primaryBtn: {
    flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: spacing.xs,
    backgroundColor: colors.brand.orange500, borderRadius: 14, paddingVertical: 14,
    marginTop: spacing.md, marginBottom: spacing.sm,
  },
  securityRow: { flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 6 },
  rtlText: { textAlign: 'right' },
});
