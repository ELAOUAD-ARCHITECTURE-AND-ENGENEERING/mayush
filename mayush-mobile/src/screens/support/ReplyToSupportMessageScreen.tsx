import React, { useState, useEffect } from 'react';
import {
  View,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  TextInput,
} from 'react-native';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { colors } from '../../design-system/tokens/colors';
import { spacing } from '../../design-system/tokens/spacing';
import { supportState, SupportRequest } from '../../commerce/supportState';
import { accountPreferencesState } from '../../commerce/accountPreferencesState';
import { TabKey } from '../../design-system/components/navigation/BottomTabBar';

interface ReplyToSupportMessageScreenProps {
  onNavigateTab: (tab: TabKey) => void;
  onBack: () => void;
  onSendReplySuccess: () => void;
}

export const ReplyToSupportMessageScreen: React.FC<ReplyToSupportMessageScreenProps> = ({
  onNavigateTab,
  onBack,
  onSendReplySuccess,
}) => {
  const [ticket, setTicket] = useState<SupportRequest | undefined>(
    supportState.getSupportRequestById(supportState.getSelectedSupportRequestId()) || supportState.getSupportRequests()[0]
  );
  const [replyMessage, setReplyMessage] = useState('');
  const [language, setLanguage] = useState(accountPreferencesState.getLanguage());
  const [errorMsg, setErrorMsg] = useState('');

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
  const lastMessage = ticket && ticket.messages.length > 0 ? ticket.messages[ticket.messages.length - 1] : undefined;

  const handleSend = () => {
    if (!replyMessage.trim()) {
      setErrorMsg(isRTL ? 'يرجى كتابة ردك قبل الإرسال' : 'Veuillez écrire votre message de réponse.');
      return;
    }
    setErrorMsg('');
    if (ticket) {
      supportState.addReplyToRequest(ticket.id, replyMessage);
    }
    setReplyMessage('');
    onSendReplySuccess();
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
        <TouchableOpacity onPress={handleSend} style={styles.bellBtn} activeOpacity={0.7}>
          <MayushIcon name="bell" size={20} color={colors.brand.navy900} />
        </TouchableOpacity>
      </View>

      <ScrollView style={styles.scrollView} contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
        {/* Title Section */}
        <View style={styles.titleSection}>
          <View style={styles.iconCircle}>
            <MayushIcon name="message-square" size={28} color={colors.brand.orange500} />
          </View>
          <MayushText variant="pageTitle" color={colors.brand.navy900} style={[styles.title, isRTL && styles.rtlText]}>
            {isRTL ? 'الرد على الدعم' : 'Répondre au support'}
          </MayushText>
          <MayushText variant="body" color={colors.neutral.gray500} style={[styles.subtitle, isRTL && styles.rtlText]}>
            {isRTL
              ? 'أجب على رسالة فريقنا. سنرد عليك في أقرب وقت ممكن.'
              : 'Répondez au message de notre équipe. Nous vous répondrons dans les plus brefs délais.'}
          </MayushText>
        </View>

        {/* Ticket Summary Context Card */}
        <View style={styles.ticketSummaryCard}>
          <View style={styles.cardHeaderRow}>
            <View style={styles.fileIconBadge}>
              <MayushIcon name="file-text" size={18} color={colors.brand.orange500} />
            </View>
            <View style={{ flex: 1 }}>
              <MayushText variant="smallBody" color={colors.neutral.gray500}>
                {isRTL ? 'رقم التذكرة' : 'NUMÉRO DE TICKET'}
              </MayushText>
              <MayushText variant="strongBody" color={colors.brand.navy900} style={{ fontSize: 13, marginTop: 2 }}>
                {ticket?.reference || 'SUP-2026-002154'}
              </MayushText>
              <MayushText variant="smallBody" color={colors.neutral.gray500}>
                {ticket?.date || '28 mai 2026 à 11:32'}
              </MayushText>
            </View>
            <View style={styles.statusBadge}>
              <MayushIcon name="clock" size={12} color="#D97706" />
              <MayushText variant="smallBody" color="#D97706" style={{ fontWeight: '600', marginLeft: 4 }}>
                {ticket?.statusLabel || (isRTL ? 'قيد الانتظار' : 'En cours')}
              </MayushText>
            </View>
          </View>

          <View style={styles.cardDivider} />

          <MayushText variant="smallBody" color={colors.neutral.gray500}>
            {isRTL ? 'الموضوع' : 'SUJET'}
          </MayushText>
          <MayushText variant="strongBody" color={colors.brand.navy900} style={{ fontSize: 13, marginTop: 2, marginBottom: 8 }}>
            {ticket ? (isRTL ? ticket.titleAr || ticket.title : ticket.title) : 'Commande non reçue'}
          </MayushText>

          <MayushText variant="smallBody" color={colors.neutral.gray500}>
            {isRTL ? 'آخر رسالة من الدعم' : 'DERNIER MESSAGE DU SUPPORT'}
          </MayushText>
          <MayushText variant="body" color={colors.brand.navy900} style={[{ fontSize: 13, marginTop: 2, lineHeight: 18 }, isRTL && styles.rtlText]}>
            {lastMessage ? lastMessage.text : 'Bonjour, nous avons bien reçu votre demande. Pouvez-vous nous confirmer votre adresse de livraison ?'}
          </MayushText>
        </View>

        {errorMsg ? (
          <View style={styles.errorBox}>
            <MayushIcon name="alert-circle" size={16} color="#D9381E" />
            <MayushText variant="smallBody" color="#D9381E" style={{ flex: 1 }}>
              {errorMsg}
            </MayushText>
          </View>
        ) : null}

        {/* Reply Form Section */}
        <MayushText variant="strongBody" color={colors.brand.navy900} style={[styles.sectionTitle, isRTL && styles.rtlText]}>
          {isRTL ? 'ردك' : 'Votre réponse'}
        </MayushText>

        <View style={styles.textAreaBox}>
          <TextInput
            style={[styles.textAreaInput, isRTL && styles.rtlText]}
            placeholder={isRTL ? 'اكتب رسالتك هنا...' : 'Écrivez votre message ici...'}
            placeholderTextColor={colors.neutral.gray500}
            multiline
            maxLength={2000}
            value={replyMessage}
            onChangeText={setReplyMessage}
          />
          <MayushText variant="smallBody" color={colors.neutral.gray500} style={styles.charCounter}>
            {replyMessage.length}/2000
          </MayushText>
        </View>

        {/* Optional Attachment Row */}
        <TouchableOpacity style={styles.attachmentRow} activeOpacity={0.7}>
          <MayushIcon name="paperclip" size={18} color={colors.brand.navy900} />
          <View style={{ flex: 1 }}>
            <MayushText variant="strongBody" color={colors.brand.navy900}>
              {isRTL ? 'إرفاق ملف (اختياري)' : 'Joindre un fichier (optionnel)'}
            </MayushText>
            <MayushText variant="smallBody" color={colors.neutral.gray500}>
              JPG, PNG, PDF – Max 5 Mo
            </MayushText>
          </View>
        </TouchableOpacity>

        {/* Action Buttons */}
        <TouchableOpacity style={styles.primaryBtn} onPress={handleSend} activeOpacity={0.85}>
          <MayushIcon name="send" size={18} color={colors.neutral.white} />
          <MayushText variant="strongBody" color={colors.neutral.white}>
            {isRTL ? 'إرسال الرد' : 'Envoyer ma réponse'}
          </MayushText>
        </TouchableOpacity>

        <TouchableOpacity style={styles.secondaryBtn} onPress={onBack} activeOpacity={0.85}>
          <MayushText variant="strongBody" color={colors.brand.navy900}>
            {isRTL ? 'إلغاء' : 'Annuler'}
          </MayushText>
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
  scrollContent: { padding: spacing.md, paddingBottom: 40 },
  titleSection: { alignItems: 'center', marginBottom: spacing.md },
  iconCircle: {
    width: 56, height: 56, borderRadius: 28, backgroundColor: 'rgba(232,125,62,0.1)',
    alignItems: 'center', justifyContent: 'center', marginBottom: spacing.xs,
  },
  title: { textAlign: 'center', marginBottom: 4 },
  subtitle: { textAlign: 'center', fontSize: 13, lineHeight: 18 },
  ticketSummaryCard: {
    backgroundColor: colors.neutral.white, borderRadius: 16, padding: spacing.md,
    marginBottom: spacing.md, borderWidth: 1, borderColor: 'rgba(0,0,0,0.05)',
  },
  cardHeaderRow: { flexDirection: 'row', alignItems: 'flex-start', gap: spacing.xs },
  fileIconBadge: {
    width: 36, height: 36, borderRadius: 18, backgroundColor: '#FFF2EB',
    alignItems: 'center', justifyContent: 'center', marginTop: 2,
  },
  statusBadge: {
    flexDirection: 'row', alignItems: 'center', backgroundColor: '#FFF8E6',
    borderRadius: 12, paddingHorizontal: 8, paddingVertical: 4,
  },
  cardDivider: { height: 1, backgroundColor: colors.neutral.gray300, marginVertical: spacing.sm },
  errorBox: {
    flexDirection: 'row', alignItems: 'center', gap: spacing.xs,
    backgroundColor: '#FDE8E8', borderWidth: 1, borderColor: '#F8B4B4',
    borderRadius: 12, padding: spacing.sm, marginBottom: spacing.md,
  },
  sectionTitle: { marginBottom: spacing.xs, fontSize: 14 },
  textAreaBox: {
    backgroundColor: colors.neutral.white, borderWidth: 1, borderColor: colors.neutral.gray300,
    borderRadius: 14, paddingHorizontal: spacing.sm, paddingTop: 10, paddingBottom: 6,
    marginBottom: spacing.md,
  },
  textAreaInput: { minHeight: 110, textAlignVertical: 'top', fontSize: 13, color: colors.brand.navy900 },
  charCounter: { textAlign: 'right', fontSize: 11, marginTop: 4 },
  attachmentRow: {
    flexDirection: 'row', alignItems: 'center', gap: spacing.xs,
    backgroundColor: colors.neutral.white, borderWidth: 1, borderColor: colors.neutral.gray300,
    borderRadius: 12, paddingHorizontal: spacing.sm, paddingVertical: 12, marginBottom: spacing.md,
  },
  primaryBtn: {
    flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: spacing.xs,
    backgroundColor: colors.brand.orange500, borderRadius: 14, paddingVertical: 14,
    marginBottom: spacing.sm,
  },
  secondaryBtn: {
    flexDirection: 'row', alignItems: 'center', justifyContent: 'center',
    backgroundColor: colors.neutral.white, borderWidth: 1, borderColor: colors.brand.navy900,
    borderRadius: 14, paddingVertical: 14,
  },
  rtlText: { textAlign: 'right' },
});
