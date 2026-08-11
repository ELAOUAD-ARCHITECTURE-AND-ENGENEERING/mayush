import React, { useState, useEffect } from 'react';
import { ScrollView, StyleSheet, TextInput, TouchableOpacity, View } from 'react-native';
import { accountPreferencesState } from '../../commerce/accountPreferencesState';
import { supportState, SupportRequest } from '../../commerce/supportState';
import { MayushLogo } from '../../design-system/components/brand/MayushLogo';
import { TabKey } from '../../design-system/components/navigation/BottomTabBar';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { colors } from '../../design-system/tokens/colors';
import { spacing } from '../../design-system/tokens/spacing';

export interface TicketResolvedRatingScreenProps {
  onNavigateTab?: (tab: TabKey) => void;
  onBack?: () => void;
  onNavigateConnectionError?: () => void;
  onNavigateFaqDetail?: (faqId?: string) => void;
  onNavigateTrackOrderFaq?: () => void;
  ticketId?: string;
}

export const TicketResolvedRatingScreen: React.FC<TicketResolvedRatingScreenProps> = ({
  onNavigateTab,
  onBack,
  onNavigateConnectionError,
  onNavigateFaqDetail,
  onNavigateTrackOrderFaq,
  ticketId,
}) => {
  const language = accountPreferencesState.getSelectedLanguage();
  const isRTL = language === 'ar';

  const [ticket, setTicket] = useState<SupportRequest | undefined>(() => {
    const selectedId = ticketId || supportState.getSelectedSupportRequestId();
    const req = supportState.getSupportRequestById(selectedId);
    if (req && req.status === 'resolved') return req;
    const resolvedReq = supportState.getSupportRequests().find((r) => r.status === 'resolved');
    return resolvedReq || req || supportState.getSupportRequests()[0];
  });

  const [ratingStars, setRatingStars] = useState<number>(ticket?.rating?.stars || 0);
  const [commentText, setCommentText] = useState<string>(ticket?.rating?.comment || '');
  const [submitted, setSubmitted] = useState<boolean>(!!ticket?.rating);

  useEffect(() => {
    const unsub = supportState.subscribe(() => {
      const selectedId = ticketId || supportState.getSelectedSupportRequestId();
      const req = supportState.getSupportRequestById(selectedId);
      if (req) {
        setTicket(req);
        if (req.rating) {
          setRatingStars(req.rating.stars);
          setCommentText(req.rating.comment || '');
          setSubmitted(true);
        }
      }
    });
    return unsub;
  }, [ticketId]);

  const handleSelectStar = (stars: number) => {
    setRatingStars(stars);
  };

  const handleSubmitRating = () => {
    if (ratingStars > 0 && ticket) {
      supportState.rateTicket(ticket.id, ratingStars, commentText);
      setSubmitted(true);
    }
  };

  const defaultReference = ticket?.reference || 'TKT-2026-004892';
  const defaultCreatedDate = ticket?.date || 'Créé le 20 mai 2026 à 11:20';

  return (
    <View style={styles.container}>
      {/* Top Header */}
      <View style={[styles.header, isRTL && styles.rtlRow]}>
        <TouchableOpacity style={styles.backButton} onPress={onBack} activeOpacity={0.7} testID="back-button">
          <MayushIcon name={isRTL ? 'chevron-right' : 'chevron-left'} size={24} color={colors.brand.navy900} />
        </TouchableOpacity>
        <View style={styles.logoContainer}>
          <MayushLogo width={120} height={32} />
        </View>
        <TouchableOpacity
          style={styles.bellButton}
          onPress={onNavigateConnectionError}
          activeOpacity={0.7}
          testID="prototype-nav-connection-error"
        >
          <MayushIcon name="bell" size={22} color={colors.brand.navy900} />
        </TouchableOpacity>
      </View>

      <ScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
        {/* Status Artwork & Banner */}
        <View style={styles.heroSection}>
          <View style={styles.artworkContainer}>
            <View style={styles.artworkOuterCircle}>
              <View style={styles.artworkCheckBadge}>
                <MayushIcon name="check" size={32} color={colors.neutral.white} />
              </View>
              <View style={styles.artworkShieldBadge}>
                <MayushIcon name="shield-check" size={16} color={colors.neutral.white} />
              </View>
            </View>
          </View>

          <MayushText variant="pageTitle" color={colors.brand.navy900} align="center" style={styles.heroTitle}>
            {isRTL ? 'Ticket résolu' : 'Ticket résolu'}
          </MayushText>
          <MayushText variant="smallBody" color={colors.neutral.gray500} align="center" style={styles.heroSubtitle}>
            {isRTL
              ? 'تمت معالجة طلبك بنجاح. شكراً لتواصلك معنا.'
              : 'Votre demande a été résolue. Merci de nous avoir contactés.'}
          </MayushText>
        </View>

        {/* Ticket Reference Card */}
        <View style={styles.card}>
          <View style={[styles.refRow, isRTL && styles.rtlRow]}>
            <View style={styles.refIconCircle}>
              <MayushIcon name="tag" size={20} color="#0D894F" />
            </View>
            <View style={[styles.refInfo, isRTL && styles.rtlTextCol]}>
              <MayushText variant="caption" color={colors.neutral.gray500} style={styles.uppercaseLabel}>
                {isRTL ? 'رقم التذكرة' : 'NUMÉRO DU TICKET'}
              </MayushText>

              <MayushText variant="cardTitle" color={colors.brand.navy900} style={styles.refNumber}>
                {defaultReference}
              </MayushText>
              <MayushText variant="caption" color={colors.neutral.gray500}>
                {isRTL ? `تم الإنشاء في ${defaultCreatedDate}` : defaultCreatedDate}
              </MayushText>
            </View>
            <View style={styles.statusBadgeResolved}>
              <MayushIcon name="check-circle" size={14} color="#0D894F" />
              <MayushText variant="caption" color="#0D894F" style={{ fontWeight: '700' }}>
                {isRTL ? 'تم الحل' : 'Résolu'}
              </MayushText>
            </View>
          </View>
        </View>

        {/* Support Response Card */}
        <View style={styles.card}>
          <View style={[styles.cardHeaderRow, isRTL && styles.rtlRow]}>
            <View style={styles.iconCircleNavy}>
              <MayushIcon name="message-square" size={18} color={colors.brand.navy900} />
            </View>
            <MayushText variant="strongBody" color={colors.brand.navy900} style={styles.cardHeaderTitle}>
              {isRTL ? 'رد الدعم الفني' : 'Réponse du support'}
            </MayushText>
          </View>

          <View style={styles.responseBody}>
            <MayushText variant="body" color={colors.brand.navy900} style={[styles.responseText, isRTL && styles.rtlText]}>
              {isRTL
                ? 'مرحباً،\nنؤكد لك أنه تم التعامل مع طلب الاسترداد الخاص بك بنجاح. تم إجراء الاسترداد بمبلغ 6250 درهم مغربي على طريقة الدفع الأصلية.\nقد تختلف مدة الإيداع حسب بنكك (بين 24 ساعة و 5 أيام عمل).\nشكراً لصبرك وثقتك.\nفريق مايوش ديزاين'
                : 'Bonjour,\nNous vous confirmons que votre demande de remboursement a bien été traitée. Le remboursement de 6 250 MAD a été effectué sur votre mode de paiement initial.\nLe délai d\'apparition peut varier selon votre banque (entre 24h et 5 jours ouvrés).\nMerci pour votre patience et votre confiance.\nL\'équipe Mayush Design'}
            </MayushText>
          </View>

          <View style={styles.divider} />

          <View style={[styles.footerRow, isRTL && styles.rtlRow]}>
            <View style={[styles.footerDateBox, isRTL && styles.rtlRow]}>
              <MayushIcon name="calendar" size={16} color={colors.brand.orange500} />
              <MayushText variant="caption" color={colors.neutral.gray500}>
                {isRTL ? 'تم الحل في' : 'Résolu le'}
              </MayushText>
            </View>
            <MayushText variant="caption" color={colors.brand.navy900} style={{ fontWeight: '600' }}>
              22 mai 2026 à 14:35
            </MayushText>
          </View>
        </View>

        {/* Rating Card */}
        <View style={styles.card}>
          <View style={[styles.cardHeaderRow, isRTL && styles.rtlRow]}>
            <View style={styles.iconCircleNavy}>
              <MayushIcon name="star" size={18} color={colors.brand.navy900} />
            </View>
            <MayushText variant="strongBody" color={colors.brand.navy900} style={styles.cardHeaderTitle}>
              {isRTL ? 'قيم تجربتك' : 'Évaluez votre expérience'}
            </MayushText>
          </View>

          <MayushText variant="smallBody" color={colors.neutral.gray500} align={isRTL ? 'right' : 'left'} style={styles.questionText}>
            {isRTL ? 'كيف تقيم جودة الدعم الفني لدينا؟' : 'Comment évaluez-vous la qualité de notre support ?'}
          </MayushText>

          {/* Star Bar */}
          <View style={styles.starsContainer}>
            {[1, 2, 3, 4, 5].map((star) => (
              <TouchableOpacity
                key={star}
                style={styles.starTouchable}
                onPress={() => handleSelectStar(star)}
                activeOpacity={0.7}
                testID={`star-rating-${star}`}
              >
                <MayushIcon
                  name={star <= ratingStars ? 'star-filled' : 'star'}
                  size={32}
                  color={star <= ratingStars ? colors.brand.orange500 : '#E5E7EB'}
                />
              </TouchableOpacity>
            ))}
          </View>

          {ratingStars > 0 && !submitted ? (
            <View style={styles.commentInputContainer}>
              <TextInput
                style={[styles.commentInput, isRTL && styles.rtlInput]}
                placeholder={isRTL ? 'أضف تعليقاً (اختياري)...' : 'Ajoutez un commentaire (optionnel)...'}
                placeholderTextColor={colors.neutral.gray500}
                value={commentText}
                onChangeText={setCommentText}
                multiline
                numberOfLines={3}
              />
              <TouchableOpacity style={styles.submitRatingBtn} onPress={handleSubmitRating} activeOpacity={0.8}>
                <MayushText variant="strongBody" color={colors.neutral.white}>
                  {isRTL ? 'إرسال التقييم' : 'Envoyer mon avis'}
                </MayushText>
              </TouchableOpacity>
            </View>
          ) : null}

          {submitted ? (
            <View style={styles.thankYouBadge}>
              <MayushIcon name="check-circle" size={18} color="#0D894F" />
              <MayushText variant="smallBody" color="#0D894F" style={{ fontWeight: '600' }}>
                {isRTL ? 'شكراً جزيلاً على تقييمك!' : 'Merci pour votre évaluation !'}
              </MayushText>
            </View>
          ) : null}
        </View>

        {/* Questions Similaires Card */}
        <View style={styles.card}>
          <View style={[styles.cardHeaderRow, isRTL && styles.rtlRow]}>
            <View style={styles.iconCircleNavy}>
              <MayushIcon name="help-circle" size={18} color={colors.brand.navy900} />
            </View>
            <MayushText variant="strongBody" color={colors.brand.navy900} style={styles.cardHeaderTitle}>
              {isRTL ? 'أسئلة مشابهة' : 'Questions similaires'}
            </MayushText>
          </View>

          <TouchableOpacity
            style={[styles.faqRow, isRTL && styles.rtlRow]}
            onPress={() => onNavigateFaqDetail?.('faq-5')}
            activeOpacity={0.7}
          >
            <MayushText variant="body" color={colors.brand.navy900} style={{ flex: 1 }}>
              {isRTL ? 'ما هي مواعيد الاسترداد؟' : 'Quels sont les délais de remboursement ?'}
            </MayushText>
            <MayushIcon name={isRTL ? 'chevron-left' : 'chevron-right'} size={20} color={colors.neutral.gray500} />
          </TouchableOpacity>

          <View style={styles.faqDivider} />

          <TouchableOpacity
            style={[styles.faqRow, isRTL && styles.rtlRow]}
            onPress={() => onNavigateTrackOrderFaq?.()}
            activeOpacity={0.7}
          >
            <MayushText variant="body" color={colors.brand.navy900} style={{ flex: 1 }}>
              {isRTL ? 'كيف أتبع عملية الاسترداد؟' : 'Comment suivre mon remboursement ?'}
            </MayushText>
            <MayushIcon name={isRTL ? 'chevron-left' : 'chevron-right'} size={20} color={colors.neutral.gray500} />
          </TouchableOpacity>
        </View>

        {/* Dev Next Prototype Action Button */}
        {onNavigateConnectionError ? (
          <TouchableOpacity
            style={styles.protoNavBtn}
            onPress={onNavigateConnectionError}
            activeOpacity={0.8}
            testID="proto-next-309-821"
          >
            <MayushText variant="smallBody" color={colors.neutral.gray500}>
              {isRTL ? 'اختبار حالة خطأ الاتصال (309:821)' : 'Simuler Erreur de connexion (309:821) →'}
            </MayushText>
          </TouchableOpacity>
        ) : null}
      </ScrollView>
    </View>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#FAF8F5' },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: spacing.md,
    paddingTop: 48,
    paddingBottom: spacing.sm,
    backgroundColor: '#FAF8F5',
  },
  backButton: { padding: spacing.xs },
  logoContainer: { flex: 1, alignItems: 'center' },
  bellButton: { padding: spacing.xs },
  scrollContent: { padding: spacing.md, paddingBottom: 40 },
  heroSection: { alignItems: 'center', marginBottom: spacing.md },
  artworkContainer: { marginBottom: spacing.sm, alignItems: 'center' },
  artworkOuterCircle: {
    width: 96,
    height: 96,
    borderRadius: 48,
    backgroundColor: '#EDFCF2',
    alignItems: 'center',
    justifyContent: 'center',
    position: 'relative',
  },
  artworkCheckBadge: {
    width: 60,
    height: 60,
    borderRadius: 30,
    backgroundColor: '#0D894F',
    alignItems: 'center',
    justifyContent: 'center',
  },
  artworkShieldBadge: {
    position: 'absolute',
    bottom: 2,
    right: 2,
    width: 28,
    height: 28,
    borderRadius: 14,
    backgroundColor: colors.brand.navy900,
    alignItems: 'center',
    justifyContent: 'center',
  },
  heroTitle: { marginBottom: 4 },
  heroSubtitle: { textAlign: 'center', paddingHorizontal: spacing.md },
  card: {
    backgroundColor: colors.neutral.white,
    borderRadius: 16,
    padding: spacing.md,
    marginBottom: spacing.md,
    borderWidth: 1,
    borderColor: 'rgba(0,0,0,0.05)',
  },
  refRow: { flexDirection: 'row', alignItems: 'center', gap: spacing.xs },
  refIconCircle: {
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: '#EDFCF2',
    alignItems: 'center',
    justifyContent: 'center',
  },
  refInfo: { flex: 1 },
  uppercaseLabel: { fontSize: 10, letterSpacing: 0.5, fontWeight: '700' },
  refNumber: { fontSize: 16, fontWeight: '800' },
  statusBadgeResolved: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
    backgroundColor: '#EDFCF2',
    paddingHorizontal: 10,
    paddingVertical: 5,
    borderRadius: 12,
  },
  cardHeaderRow: { flexDirection: 'row', alignItems: 'center', gap: spacing.xs, marginBottom: spacing.sm },
  iconCircleNavy: {
    width: 32,
    height: 32,
    borderRadius: 16,
    backgroundColor: 'rgba(15,23,42,0.06)',
    alignItems: 'center',
    justifyContent: 'center',
  },
  cardHeaderTitle: { fontSize: 16, fontWeight: '700' },
  responseBody: { marginVertical: spacing.xs },
  responseText: { fontSize: 14, lineHeight: 22 },
  divider: { height: 1, backgroundColor: colors.neutral.gray300, marginVertical: spacing.sm },
  footerRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
  footerDateBox: { flexDirection: 'row', alignItems: 'center', gap: 6 },
  questionText: { marginBottom: spacing.md },
  starsContainer: { flexDirection: 'row', justifyContent: 'center', gap: spacing.sm, marginBottom: spacing.md },
  starTouchable: { padding: 4 },
  commentInputContainer: { marginTop: spacing.xs, gap: spacing.xs },
  commentInput: {
    backgroundColor: '#FAF8F5',
    borderRadius: 12,
    borderWidth: 1,
    borderColor: colors.neutral.gray300,
    padding: spacing.sm,
    fontSize: 14,
    color: colors.brand.navy900,
    minHeight: 70,
    textAlignVertical: 'top',
  },
  submitRatingBtn: {
    backgroundColor: colors.brand.orange500,
    borderRadius: 12,
    paddingVertical: 12,
    alignItems: 'center',
  },
  thankYouBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 6,
    backgroundColor: '#EDFCF2',
    padding: 10,
    borderRadius: 12,
  },
  faqRow: { flexDirection: 'row', alignItems: 'center', paddingVertical: spacing.xs },
  faqDivider: { height: 1, backgroundColor: colors.neutral.gray300, marginVertical: 6 },
  protoNavBtn: { alignItems: 'center', paddingVertical: spacing.xs, marginTop: spacing.xs },
  rtlRow: { flexDirection: 'row-reverse' },
  rtlText: { textAlign: 'right' },
  rtlTextCol: { alignItems: 'flex-end' },
  rtlInput: { textAlign: 'right' },
});
