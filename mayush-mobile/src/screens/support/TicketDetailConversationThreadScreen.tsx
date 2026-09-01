import React, { useState, useEffect } from 'react';
import { View, StyleSheet, ScrollView, TouchableOpacity } from 'react-native';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { colors } from '../../design-system/tokens/colors';
import { spacing } from '../../design-system/tokens/spacing';
import { supportState, SupportRequest } from '../../commerce/supportState';
import { accountPreferencesState } from '../../commerce/accountPreferencesState';
import { TabKey } from '../../design-system/components/navigation/BottomTabBar';

interface TicketDetailConversationThreadScreenProps {
  onNavigateTab: (tab: TabKey) => void;
  onBack: () => void;
  onNavigateReply: () => void;
  onNavigateCloseRequest: () => void;
  onNavigateOrdersList: () => void;
  onNavigateRating?: () => void;
}

export const TicketDetailConversationThreadScreen: React.FC<TicketDetailConversationThreadScreenProps> = ({
  onNavigateTab,
  onBack,
  onNavigateReply,
  onNavigateCloseRequest,
  onNavigateOrdersList,
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

  const getStatusBadge = (status: string) => {
    switch (status) {
      case 'open':
        return { label: isRTL ? 'مفتوح' : 'Ouvert', bg: 'rgba(232,125,62,0.12)', text: colors.brand.orange500, icon: 'clock' as const };
      case 'in-progress':
        return { label: isRTL ? 'قيد الانتظار' : 'En cours', bg: '#FFF8E6', text: '#D97706', icon: 'clock' as const };
      case 'resolved':
      case 'closed':
        return { label: isRTL ? 'مغلق' : 'Résolu', bg: '#EDFCF2', text: '#0D894F', icon: 'check-circle' as const };
      default:
        return { label: status, bg: colors.neutral.gray100, text: colors.neutral.gray700, icon: 'help-circle' as const };
    }
  };

  const badge = ticket ? getStatusBadge(ticket.status) : getStatusBadge('open');

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
        <TouchableOpacity onPress={onNavigateReply} style={styles.bellBtn} activeOpacity={0.7}>
          <MayushIcon name="bell" size={20} color={colors.brand.navy900} />
        </TouchableOpacity>
      </View>

      <ScrollView style={styles.scrollView} contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
        {/* Title Section */}
        <View style={styles.titleSection}>
          <MayushText variant="pageTitle" color={colors.brand.navy900} style={[styles.title, isRTL && styles.rtlText]}>
            {isRTL ? 'تفاصيل الطلب' : 'Détail de la demande'}
          </MayushText>
          <MayushText variant="body" color={colors.neutral.gray500} style={[styles.subtitle, isRTL && styles.rtlText]}>
            {isRTL ? 'نحن هنا لمساعدتك.' : 'Nous sommes là pour vous aider.'}
          </MayushText>
        </View>

        {/* Ticket Summary Header Card */}
        <View style={styles.summaryCard}>
          <View style={styles.summaryTopRow}>
            <View style={styles.headsetCircle}>
              <MayushIcon name="headphones" size={20} color={colors.brand.orange500} />
            </View>
            <View style={{ flex: 1 }}>
              <MayushText variant="smallBody" color={colors.neutral.gray500}>
                {isRTL ? 'رقم الطلب' : 'NUMÉRO DE DEMANDE'}
              </MayushText>
              <MayushText variant="strongBody" color={colors.brand.navy900} style={{ fontSize: 14 }}>
                {ticket?.reference || 'SUP-2026-000842'}
              </MayushText>
              <MayushText variant="smallBody" color={colors.neutral.gray500}>
                {ticket?.date || '28 mai 2026 à 10:24'}
              </MayushText>
            </View>
            <View style={[styles.badge, { backgroundColor: badge.bg }]}>
              <MayushIcon name={badge.icon} size={12} color={badge.text} />
              <MayushText variant="smallBody" color={badge.text} style={{ fontWeight: '600', marginLeft: 4 }}>
                {badge.label}
              </MayushText>
            </View>
          </View>

          <View style={styles.divider} />

          <View style={styles.metaRow}>
            <View style={styles.metaCol}>
              <MayushText variant="smallBody" color={colors.neutral.gray500}>
                {isRTL ? 'الفئة' : 'CATÉGORIE'}
              </MayushText>
              <View style={styles.metaBadge}>
                <MayushIcon name="box" size={14} color={colors.brand.navy900} />
                <MayushText variant="smallBody" color={colors.brand.navy900} style={{ fontWeight: '600' }}>
                  {ticket?.categoryLabel || (isRTL ? 'التوصيل' : 'Livraison')}
                </MayushText>
              </View>
            </View>

            {ticket?.relatedOrderId ? (
              <TouchableOpacity style={styles.metaCol} onPress={onNavigateOrdersList} activeOpacity={0.7}>
                <MayushText variant="smallBody" color={colors.neutral.gray500}>
                  {isRTL ? 'الطلب المعني' : 'COMMANDE CONCERNÉE'}
                </MayushText>
                <View style={styles.metaBadge}>
                  <MayushIcon name="file-text" size={14} color={colors.brand.orange500} />
                  <MayushText variant="smallBody" color={colors.brand.orange500} style={{ fontWeight: '600' }}>
                    {ticket.relatedOrderId}
                  </MayushText>
                </View>
              </TouchableOpacity>
            ) : null}

            <View style={styles.metaCol}>
              <MayushText variant="smallBody" color={colors.neutral.gray500}>
                {isRTL ? 'الأولوية' : 'PRIORITÉ'}
              </MayushText>
              <View style={styles.metaBadge}>
                <MayushIcon name="sun" size={14} color={colors.brand.orange500} />
                <MayushText variant="smallBody" color={colors.brand.navy900} style={{ fontWeight: '600' }}>
                  {ticket?.priority || (isRTL ? 'عادية' : 'Normale')}
                </MayushText>
              </View>
            </View>
          </View>
        </View>

        {/* Message Thread */}
        <View style={styles.threadContainer}>
          {ticket?.messages.map((msg) => {
            const isUser = msg.sender === 'user';
            return (
              <View key={msg.id} style={styles.messageBlock}>
                {/* Message Header */}
                <View style={styles.msgHeader}>
                  <View style={styles.avatarCircle}>
                    {isUser ? (
                      <MayushText variant="strongBody" color={colors.brand.navy900} style={{ fontSize: 12 }}>
                        VO
                      </MayushText>
                    ) : (
                      <MayushIcon name="headphones" size={16} color={colors.brand.orange500} />
                    )}
                  </View>
                  <View style={{ flex: 1 }}>
                    <MayushText variant="strongBody" color={colors.brand.navy900}>
                      {isUser ? (isRTL ? 'أنت' : 'Vous') : (isRTL ? 'دعم مايوش' : 'Support Mayush')}
                    </MayushText>
                    <MayushText variant="smallBody" color={colors.neutral.gray500}>
                      {msg.timestamp}
                    </MayushText>
                  </View>

                  {msg.statusBadge && (
                    <View style={styles.msgBadge}>
                      <MayushIcon name="check-circle" size={12} color="#0D894F" />
                      <MayushText variant="smallBody" color="#0D894F" style={{ fontWeight: '600', marginLeft: 4 }}>
                        {msg.statusBadge}
                      </MayushText>
                    </View>
                  )}
                </View>

                {/* Message Bubble Card */}
                <View style={styles.msgBubble}>
                  <MayushText variant="body" color={colors.brand.navy900} style={[{ fontSize: 13, lineHeight: 20 }, isRTL && styles.rtlText]}>
                    {msg.text}
                  </MayushText>

                  {/* Message Attachments if any */}
                  {msg.attachments && msg.attachments.length > 0 ? (
                    <View style={styles.attachmentsBox}>
                      {msg.attachments.map((att) => (
                        <View key={att.id} style={styles.attRow}>
                          <MayushIcon name={att.type === 'image' ? 'image' : 'file-text'} size={16} color={colors.brand.orange500} />
                          <View style={{ flex: 1 }}>
                            <MayushText variant="smallBody" color={colors.brand.navy900} style={{ fontWeight: '600' }} numberOfLines={1}>
                              {att.name}
                            </MayushText>
                            <MayushText variant="smallBody" color={colors.neutral.gray500}>
                              {att.size}
                            </MayushText>
                          </View>
                        </View>
                      ))}
                    </View>
                  ) : null}
                </View>
              </View>
            );
          })}
        </View>

        {/* Primary Reply CTA Button */}
        {ticket?.status !== 'resolved' && ticket?.status !== 'closed' ? (
          <TouchableOpacity style={styles.primaryBtn} onPress={onNavigateReply} activeOpacity={0.85}>
            <MayushIcon name="send" size={18} color={colors.neutral.white} />
            <MayushText variant="strongBody" color={colors.neutral.white}>
              {isRTL ? 'الرد على الطلب' : 'Répondre à la demande'}
            </MayushText>
          </TouchableOpacity>
        ) : null}

        {/* Close Request Button */}
        {ticket?.status !== 'resolved' && ticket?.status !== 'closed' ? (
          <TouchableOpacity style={styles.closeBtn} onPress={onNavigateCloseRequest} activeOpacity={0.7}>
            <MayushIcon name="x-circle" size={16} color={colors.neutral.gray500} />
            <MayushText variant="smallBody" color={colors.neutral.gray500} style={{ fontWeight: '600' }}>
              {isRTL ? 'إغلاق هذه التذكرة' : 'Clôturer la demande'}
            </MayushText>
          </TouchableOpacity>
        ) : (
          onNavigateRating ? (
            <TouchableOpacity style={styles.primaryBtn} onPress={onNavigateRating} activeOpacity={0.85} testID="view-rating-cta">
              <MayushIcon name="star" size={18} color={colors.neutral.white} />
              <MayushText variant="strongBody" color={colors.neutral.white}>
                {isRTL ? 'عرض تقييم الخدمة' : 'Voir l\'évaluation du ticket'}
              </MayushText>
            </TouchableOpacity>
          ) : null
        )}

        {/* Security Note */}
        <View style={styles.securityNote}>
          <MayushIcon name="lock" size={14} color={colors.neutral.gray500} />
          <MayushText variant="smallBody" color={colors.neutral.gray500}>
            {isRTL ? 'هذه المحادثة آمنة وسرية.' : 'Cette conversation est sécurisée et confidentielle.'}
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
  titleSection: { alignItems: 'center', marginBottom: spacing.md },
  title: { textAlign: 'center', marginBottom: 4 },
  subtitle: { textAlign: 'center', fontSize: 13, lineHeight: 18 },
  summaryCard: {
    backgroundColor: colors.neutral.white, borderRadius: 16, padding: spacing.md,
    marginBottom: spacing.md, borderWidth: 1, borderColor: 'rgba(0,0,0,0.05)',
  },
  summaryTopRow: { flexDirection: 'row', alignItems: 'center', gap: spacing.xs },
  headsetCircle: {
    width: 40, height: 40, borderRadius: 20, backgroundColor: 'rgba(232,125,62,0.1)',
    alignItems: 'center', justifyContent: 'center',
  },
  badge: { flexDirection: 'row', alignItems: 'center', paddingHorizontal: 8, paddingVertical: 4, borderRadius: 12 },
  divider: { height: 1, backgroundColor: colors.neutral.gray300, marginVertical: spacing.md },
  metaRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
  metaCol: { gap: 4 },
  metaBadge: { flexDirection: 'row', alignItems: 'center', gap: 4, marginTop: 2 },
  threadContainer: { gap: spacing.md, marginBottom: spacing.md },
  messageBlock: { gap: spacing.xs },
  msgHeader: { flexDirection: 'row', alignItems: 'center', gap: spacing.xs },
  avatarCircle: {
    width: 32, height: 32, borderRadius: 16, backgroundColor: '#FFF2EB',
    alignItems: 'center', justifyContent: 'center', borderWidth: 1, borderColor: 'rgba(232,125,62,0.2)',
  },
  msgBadge: { flexDirection: 'row', alignItems: 'center', backgroundColor: '#EDFCF2', paddingHorizontal: 8, paddingVertical: 4, borderRadius: 12 },
  msgBubble: {
    backgroundColor: colors.neutral.white, borderRadius: 16, padding: spacing.md,
    borderWidth: 1, borderColor: 'rgba(0,0,0,0.05)', marginLeft: 40,
  },
  attachmentsBox: { marginTop: 10, paddingTop: 10, borderTopWidth: 1, borderTopColor: colors.neutral.gray300, gap: 6 },
  attRow: { flexDirection: 'row', alignItems: 'center', gap: 8, backgroundColor: '#FAF8F5', borderRadius: 8, padding: 8 },
  primaryBtn: {
    flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: spacing.xs,
    backgroundColor: colors.brand.orange500, borderRadius: 14, paddingVertical: 14,
    marginBottom: spacing.sm,
  },
  closeBtn: {
    flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 6,
    paddingVertical: 10, marginBottom: spacing.sm,
  },
  securityNote: { flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 6 },
  rtlText: { textAlign: 'right' },
});
