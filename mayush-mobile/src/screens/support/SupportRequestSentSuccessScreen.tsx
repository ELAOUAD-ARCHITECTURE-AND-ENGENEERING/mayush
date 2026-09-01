import React, { useState, useEffect } from 'react';
import { View, StyleSheet, ScrollView, TouchableOpacity } from 'react-native';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { colors } from '../../design-system/tokens/colors';
import { spacing } from '../../design-system/tokens/spacing';
import { supportState, SupportRequest } from '../../commerce/supportState';
import { accountPreferencesState } from '../../commerce/accountPreferencesState';
import { TabKey } from '../../design-system/components/navigation/BottomTabBar';

interface SupportRequestSentSuccessScreenProps {
  onNavigateTab: (tab: TabKey) => void;
  onBack: () => void;
  onViewTicket: (ticketId: string) => void;
  onReturnToHelpCenter: () => void;
  onNavigateRating?: () => void;
}

export const SupportRequestSentSuccessScreen: React.FC<SupportRequestSentSuccessScreenProps> = ({
  onNavigateTab,
  onBack,
  onViewTicket,
  onReturnToHelpCenter,
  onNavigateRating,
}) => {
  const [ticket, setTicket] = useState<SupportRequest | undefined>(
    supportState.getSupportRequestById(supportState.getSelectedSupportRequestId()) || supportState.getSupportRequests()[0]
  );
  const [language, setLanguage] = useState(accountPreferencesState.getLanguage());

  useEffect(() => {
    const unsubSupport = supportState.subscribe(() => {
      setTicket(supportState.getSupportRequestById(supportState.getSelectedSupportRequestId()) || supportState.getSupportRequests()[0]);
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
  const categoryObj = ticket ? supportState.getFaqCategories().find((c) => c.id === ticket.categoryId) : undefined;

  const handleView = () => {
    if (ticket) {
      onViewTicket(ticket.id);
    } else {
      onReturnToHelpCenter();
    }
  };

  return (
    <View style={styles.container}>
      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity onPress={onReturnToHelpCenter} style={styles.backBtn} activeOpacity={0.7}>
          <MayushIcon name={isRTL ? 'arrow-right' : 'arrow-left'} size={22} color={colors.brand.navy900} />
        </TouchableOpacity>
        <MayushText variant="cardTitle" style={styles.headerLogo}>
          MAYUSH<MayushText variant="cardTitle" color={colors.brand.orange500}> DESIGN</MayushText>
        </MayushText>
        <TouchableOpacity onPress={onReturnToHelpCenter} style={styles.bellBtn} activeOpacity={0.7}>
          <MayushIcon name="bell" size={20} color={colors.brand.navy900} />
        </TouchableOpacity>
      </View>

      <ScrollView style={styles.scrollView} contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
        {/* Success Checkmark Circle */}
        <View style={styles.illustrationBox}>
          <View style={styles.illustrationCircle}>
            <View style={styles.greenCircle}>
              <MayushIcon name="check" size={32} color={colors.neutral.white} />
            </View>
            <View style={styles.envelopeBadge}>
              <MayushIcon name="mail" size={18} color={colors.brand.navy900} />
            </View>
          </View>
        </View>

        {/* Title Section */}
        <MayushText variant="pageTitle" color={colors.brand.navy900} style={[styles.title, isRTL && styles.rtlText]}>
          {isRTL ? 'تم إرسال طلبك بنجاح' : 'Votre demande a été envoyée'}
        </MayushText>
        <MayushText variant="body" color={colors.neutral.gray500} style={[styles.subtitle, isRTL && styles.rtlText]}>
          {isRTL
            ? 'شكراً لك! تم أخذ طلبك في الاعتبار. سيرد عليك فريقنا في أقرب وقت.'
            : 'Merci ! Votre demande a bien été prise en compte. Notre équipe vous répondra dans les plus brefs délais.'}
        </MayushText>

        {/* Ticket Reference Summary Card */}
        <View style={styles.summaryCard}>
          <View style={styles.infoRow}>
            <View style={styles.infoIconBadge}>
              <MayushIcon name="file-text" size={18} color={colors.brand.orange500} />
            </View>
            <View style={{ flex: 1 }}>
              <MayushText variant="smallBody" color={colors.neutral.gray500}>
                {isRTL ? 'رقم التذكرة' : 'Numéro de ticket'}
              </MayushText>
              <MayushText variant="strongBody" color={colors.brand.navy900} style={{ fontSize: 14, marginTop: 2 }}>
                {ticket?.reference || 'SUP-2026-001842'}
              </MayushText>
            </View>
          </View>

          <View style={styles.divider} />

          <View style={styles.infoRow}>
            <View style={styles.infoIconBadge}>
              <MayushIcon name="grid" size={18} color={colors.brand.orange500} />
            </View>
            <View style={{ flex: 1 }}>
              <MayushText variant="smallBody" color={colors.neutral.gray500}>
                {isRTL ? 'الفئة' : 'Catégorie'}
              </MayushText>
              <MayushText variant="strongBody" color={colors.brand.navy900} style={{ fontSize: 13, marginTop: 2 }}>
                {ticket?.categoryLabel || (categoryObj ? categoryObj.label : 'Commandes & Livraison')}
              </MayushText>
            </View>
          </View>

          <View style={styles.divider} />

          <View style={styles.infoRow}>
            <View style={styles.infoIconBadge}>
              <MayushIcon name="calendar" size={18} color={colors.brand.orange500} />
            </View>
            <View style={{ flex: 1 }}>
              <MayushText variant="smallBody" color={colors.neutral.gray500}>
                {isRTL ? 'تاريخ التقديم' : 'Date de soumission'}
              </MayushText>
              <MayushText variant="strongBody" color={colors.brand.navy900} style={{ fontSize: 13, marginTop: 2 }}>
                {ticket?.date || '28 mai 2026 à 10:24'}
              </MayushText>
            </View>
          </View>

          <View style={styles.divider} />

          <View style={styles.infoRow}>
            <View style={styles.infoIconBadge}>
              <MayushIcon name="check-circle" size={18} color="#0D894F" />
            </View>
            <View style={{ flex: 1, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' }}>
              <MayushText variant="smallBody" color={colors.neutral.gray500}>
                {isRTL ? 'الحالة' : 'Statut'}
              </MayushText>
              <View style={styles.submittedBadge}>
                <MayushText variant="smallBody" color="#0D894F" style={{ fontWeight: '600' }}>
                  {isRTL ? 'مرسلة' : 'Soumise'}
                </MayushText>
              </View>
            </View>
          </View>
        </View>

        {/* Primary View Ticket CTA Button */}
        <TouchableOpacity style={styles.primaryBtn} onPress={handleView} activeOpacity={0.85}>
          <MayushIcon name="file-text" size={18} color={colors.neutral.white} />
          <MayushText variant="strongBody" color={colors.neutral.white}>
            {isRTL ? 'عرض تذكرتي' : 'Voir mon ticket'}
          </MayushText>
          <MayushIcon name={isRTL ? 'chevron-left' : 'chevron-right'} size={18} color={colors.neutral.white} />
        </TouchableOpacity>

        {/* Secondary Return to Support CTA Button */}
        <TouchableOpacity style={styles.secondaryBtn} onPress={onReturnToHelpCenter} activeOpacity={0.85}>
          <MayushIcon name="headphones" size={18} color={colors.brand.navy900} />
          <MayushText variant="strongBody" color={colors.brand.navy900}>
            {isRTL ? 'العودة إلى الدعم' : 'Retour au support'}
          </MayushText>
          <MayushIcon name={isRTL ? 'chevron-left' : 'chevron-right'} size={18} color={colors.brand.navy900} />
        </TouchableOpacity>
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
  greenCircle: {
    width: 52, height: 52, borderRadius: 26, backgroundColor: '#0D894F',
    alignItems: 'center', justifyContent: 'center',
  },
  envelopeBadge: {
    position: 'absolute', bottom: 4, right: 4, backgroundColor: colors.brand.navy900,
    borderRadius: 12, padding: 4,
  },
  title: { textAlign: 'center', marginBottom: 4 },
  subtitle: { textAlign: 'center', fontSize: 13, lineHeight: 18, marginBottom: spacing.lg, paddingHorizontal: spacing.sm },
  summaryCard: {
    width: '100%', backgroundColor: colors.neutral.white, borderRadius: 16,
    padding: spacing.md, borderWidth: 1, borderColor: 'rgba(0,0,0,0.05)', marginBottom: spacing.lg,
  },
  infoRow: { flexDirection: 'row', alignItems: 'center', gap: spacing.xs, paddingVertical: 4 },
  infoIconBadge: {
    width: 36, height: 36, borderRadius: 18, backgroundColor: '#FFF2EB',
    alignItems: 'center', justifyContent: 'center',
  },
  divider: { height: 1, backgroundColor: colors.neutral.gray300, marginVertical: 8 },
  submittedBadge: { backgroundColor: '#EDFCF2', borderRadius: 8, paddingHorizontal: 10, paddingVertical: 4 },
  primaryBtn: {
    width: '100%', flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: spacing.xs,
    backgroundColor: colors.brand.orange500, borderRadius: 14, paddingVertical: 14, marginBottom: spacing.sm,
  },
  secondaryBtn: {
    width: '100%', flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: spacing.xs,
    backgroundColor: colors.neutral.white, borderWidth: 1, borderColor: colors.brand.navy900,
    borderRadius: 14, paddingVertical: 14,
  },
  rtlText: { textAlign: 'right' },
});
