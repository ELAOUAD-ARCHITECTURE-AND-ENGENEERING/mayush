import React, { useState } from 'react';
import { Image, ScrollView, StyleSheet, TouchableOpacity, View } from 'react-native';
import { accountPreferencesState } from '../../commerce/accountPreferencesState';
import { supportState } from '../../commerce/supportState';
import { BottomTabBar, TabKey } from '../../design-system/components/navigation/BottomTabBar';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { colors } from '../../design-system/tokens/colors';
import { spacing } from '../../design-system/tokens/spacing';

export interface FaqArticleTrackOrderStepsScreenProps {
  onNavigateTab?: (tab: TabKey) => void;
  onBack?: () => void;
  onNavigateOrdersList?: () => void;
  onNavigateRelatedArticle?: (articleId: string) => void;
  onNavigateContactSupport?: () => void;
}

export const FaqArticleTrackOrderStepsScreen: React.FC<FaqArticleTrackOrderStepsScreenProps> = ({
  onNavigateTab,
  onBack,
  onNavigateOrdersList,
  onNavigateRelatedArticle,
  onNavigateContactSupport,
}) => {
  const isRTL = accountPreferencesState.getSelectedLanguage() === 'ar';
  const [feedback, setFeedback] = useState<'yes' | 'no' | null>(null);

  const article = supportState.getFaqItemById('faq-1');
  const steps = article?.steps || [
    {
      stepNumber: 1,
      title: 'Accédez à vos commandes',
      titleAr: 'الوصول إلى طلباتك',
      description: 'Rendez-vous dans la section « Mes commandes » depuis votre compte.',
      descriptionAr: 'انتقل إلى قسم «طلباتي» من حسابك.',
    },
    {
      stepNumber: 2,
      title: 'Sélectionnez la commande concernée',
      titleAr: 'حدد الطلب المعني',
      description: 'Choisissez la commande que vous souhaitez suivre dans la liste.',
      descriptionAr: 'اختر الطلب الذي تريد تتبعه من القائمة.',
    },
    {
      stepNumber: 3,
      title: 'Consultez le statut et le suivi',
      titleAr: 'الاطلاع على الحالة والتتبع',
      description: 'Vous verrez le statut actuel (en cours, expédiée, livrée...) ainsi que le suivi du colis en temps réel.',
      descriptionAr: 'ستشاهد الحالة الحالية (قيد المعالجة، تم الشحن، تم التسليم...) وتتبع الطرد في الوقت الفعلي.',
    },
    {
      stepNumber: 4,
      title: 'Suivez votre colis',
      titleAr: 'تتبع طردك',
      description: 'Cliquez sur « Suivre le colis » pour être redirigé vers le transporteur et consulter l\'acheminement détaillé.',
      descriptionAr: 'انقر على «تتبع الطرد» للانتقال إلى شركة التوصيل والاطلاع على التفاصيل.',
    },
  ];

  const relatedArticles = [
    { id: 'faq-2', titleFr: 'Quels sont les délais de livraison ?', titleAr: 'ما هي مواعيد التسليم؟' },
    { id: 'faq-8', titleFr: 'Que faire si ma commande est en retard ?', titleAr: 'ماذا أفعل إذا تأخر طلبي؟' },
    { id: 'faq-3', titleFr: 'Puis-je modifier ou annuler ma commande ?', titleAr: 'هل يمكنني تعديل أو إلغاء طلبي؟' },
  ];

  return (
    <View style={styles.container}>
      {/* Top Header */}
      <View style={[styles.topHeader, isRTL && styles.rtlRow]}>
        <TouchableOpacity style={styles.iconBtn} onPress={onBack} activeOpacity={0.7}>
          <MayushIcon name={isRTL ? 'chevron-right' : 'chevron-left'} size={24} color={colors.brand.navy900} />
        </TouchableOpacity>
        <Image
          source={require('../../../assets/brand/logo-transparent.png')}
          style={styles.logoImage}
          resizeMode="contain"
        />
        <TouchableOpacity style={styles.iconBtn} activeOpacity={0.7}>
          <MayushIcon name="bell" size={22} color={colors.brand.navy900} />
        </TouchableOpacity>
      </View>

      <ScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
        {/* Article Illustration Badge Header */}
        <View style={styles.headerIllustrationCard}>
          <View style={styles.badgeCircle}>
            <MayushIcon name="search" size={32} color={colors.brand.orange500} />
          </View>
          <MayushText variant="pageTitle" color={colors.brand.navy900} style={[styles.title, isRTL && styles.rtlText]}>
            {isRTL ? article?.questionAr || 'كيف أتتبع طلبي؟' : article?.question || 'Comment suivre ma commande ?'}
          </MayushText>
          <MayushText variant="body" color={colors.neutral.gray500} style={[styles.subtitle, isRTL && styles.rtlText]}>
            {isRTL
              ? 'تابع شحن ونقل طلبك في خطوات بسيطة.'
              : 'Suivez l\'acheminement de votre commande en quelques étapes simples.'}
          </MayushText>
        </View>

        {/* Step Timeline Card */}
        <View style={styles.timelineCard}>
          {steps.map((st, idx) => {
            const isLast = idx === steps.length - 1;
            return (
              <View key={st.stepNumber} style={styles.timelineRow}>
                <View style={styles.leftCol}>
                  <TouchableOpacity
                    style={styles.stepNumCircle}
                    onPress={st.stepNumber === 1 ? onNavigateOrdersList : undefined}
                    activeOpacity={st.stepNumber === 1 ? 0.7 : 1}
                  >
                    <MayushText variant="strongBody" color={colors.brand.orange500}>
                      {st.stepNumber}
                    </MayushText>
                  </TouchableOpacity>
                  {!isLast && <View style={styles.verticalDottedLine} />}
                </View>

                <TouchableOpacity
                  style={styles.stepTextCol}
                  onPress={st.stepNumber === 1 ? onNavigateOrdersList : undefined}
                  activeOpacity={st.stepNumber === 1 ? 0.7 : 1}
                >
                  <MayushText variant="strongBody" color={colors.brand.navy900} style={isRTL && styles.rtlText}>
                    {isRTL ? st.titleAr : st.title}
                  </MayushText>
                  <MayushText variant="smallBody" color={colors.neutral.gray500} style={[{ lineHeight: 18 }, isRTL && styles.rtlText]}>
                    {isRTL ? st.descriptionAr : st.description}
                  </MayushText>
                </TouchableOpacity>
              </View>
            );
          })}

          {/* Quick CTA to navigate to Orders list */}
          <TouchableOpacity style={styles.ordersListCtaBtn} onPress={onNavigateOrdersList} activeOpacity={0.85}>
            <MayushIcon name="file-text" size={18} color={colors.neutral.white} />
            <MayushText variant="strongBody" color={colors.neutral.white}>
              {isRTL ? 'الانتقال إلى طلباتي' : 'Suivre ma commande'}
            </MayushText>
          </TouchableOpacity>
        </View>

        {/* Related Articles Section */}
        <View style={styles.section}>
          <MayushText variant="sectionTitle" color={colors.brand.navy900} style={[styles.sectionTitle, isRTL && styles.rtlText]}>
            {isRTL ? 'مقالات ذات صلة' : 'Articles liés'}
          </MayushText>

          <View style={styles.relatedCardBox}>
            {relatedArticles.map((rel, idx) => (
              <React.Fragment key={rel.id}>
                {idx > 0 && <View style={styles.divider} />}
                <TouchableOpacity
                  style={[styles.relatedRow, isRTL && styles.rtlRow]}
                  onPress={() => {
                    supportState.setSelectedArticleId(rel.id);
                    onNavigateRelatedArticle?.(rel.id);
                  }}
                  activeOpacity={0.7}
                >
                  <View style={styles.relatedIconCircle}>
                    <MayushIcon name="file-text" size={16} color={colors.brand.orange500} />
                  </View>
                  <MayushText variant="strongBody" color={colors.brand.navy900} style={[{ flex: 1 }, isRTL && styles.rtlText]}>
                    {isRTL ? rel.titleAr : rel.titleFr}
                  </MayushText>
                  <MayushIcon name={isRTL ? 'chevron-left' : 'chevron-right'} size={18} color={colors.neutral.gray500} />
                </TouchableOpacity>
              </React.Fragment>
            ))}
          </View>
        </View>

        {/* Feedback Question Section */}
        <View style={styles.feedbackCard}>
          <MayushText variant="smallBody" color={colors.neutral.gray500} style={{ textAlign: 'center' }}>
            {isRTL ? 'هل كان هذا المقال مفيداً بالنسبة لك؟' : 'Cet article vous a-t-il été utile ?'}
          </MayushText>
          <View style={styles.feedbackRow}>
            <TouchableOpacity
              style={[styles.feedbackChip, feedback === 'yes' && styles.feedbackChipActive]}
              onPress={() => setFeedback('yes')}
              activeOpacity={0.7}
            >
              <MayushIcon name="thumbs-up" size={16} color={feedback === 'yes' ? colors.brand.orange500 : colors.brand.navy900} />
              <MayushText variant="strongBody" color={feedback === 'yes' ? colors.brand.orange500 : colors.brand.navy900}>
                {isRTL ? 'نعم' : 'Oui'}
              </MayushText>
            </TouchableOpacity>

            <TouchableOpacity
              style={[styles.feedbackChip, feedback === 'no' && styles.feedbackChipActive]}
              onPress={() => setFeedback('no')}
              activeOpacity={0.7}
            >
              <MayushIcon name="thumbs-down" size={16} color={feedback === 'no' ? colors.brand.orange500 : colors.brand.navy900} />
              <MayushText variant="strongBody" color={feedback === 'no' ? colors.brand.orange500 : colors.brand.navy900}>
                {isRTL ? 'لا' : 'Non'}
              </MayushText>
            </TouchableOpacity>
          </View>
        </View>

        {/* Contact Support Banner */}
        <TouchableOpacity style={styles.supportBannerBtn} onPress={onNavigateContactSupport} activeOpacity={0.85}>
          <MayushIcon name="headphones" size={22} color={colors.neutral.white} />
          <View style={{ flex: 1 }}>
            <MayushText variant="strongBody" color={colors.neutral.white} style={isRTL && styles.rtlText}>
              {isRTL ? 'التواصل مع الدعم' : 'Contacter le support'}
            </MayushText>
            <MayushText variant="caption" color="rgba(255,255,255,0.85)" style={isRTL && styles.rtlText}>
              {isRTL ? 'فريقنا هنا لمساعدتك' : 'Notre équipe est là pour vous aider'}
            </MayushText>
          </View>
          <MayushIcon name={isRTL ? 'chevron-left' : 'chevron-right'} size={20} color={colors.neutral.white} />
        </TouchableOpacity>
      </ScrollView>

      <BottomTabBar activeTab="account" onTabPress={(tab) => onNavigateTab?.(tab)} />
    </View>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#FAF8F5' },
  topHeader: {
    height: 56, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between',
    paddingHorizontal: spacing.md, backgroundColor: colors.neutral.white,
    borderBottomWidth: 1, borderBottomColor: colors.neutral.gray300,
  },
  iconBtn: { width: 40, height: 40, alignItems: 'center', justifyContent: 'center' },
  logoImage: { height: 28, width: 120 },
  scrollContent: { padding: spacing.md, gap: spacing.md, paddingBottom: 100 },
  headerIllustrationCard: { alignItems: 'center', gap: 8, paddingVertical: spacing.sm },
  badgeCircle: {
    width: 64, height: 64, borderRadius: 32, backgroundColor: 'rgba(232,125,62,0.12)',
    alignItems: 'center', justifyContent: 'center', marginBottom: 4,
  },
  title: { fontSize: 22, fontWeight: '700', fontFamily: 'serif', color: colors.brand.navy900, textAlign: 'center' },
  subtitle: { fontSize: 13, textAlign: 'center', paddingHorizontal: spacing.md },
  timelineCard: {
    backgroundColor: colors.neutral.white, borderRadius: 16, padding: spacing.md,
    borderWidth: 1, borderColor: 'rgba(0,0,0,0.05)', gap: spacing.sm,
  },
  timelineRow: { flexDirection: 'row', gap: spacing.sm },
  leftCol: { alignItems: 'center', width: 40 },
  stepNumCircle: {
    width: 36, height: 36, borderRadius: 18, backgroundColor: 'rgba(232,125,62,0.12)',
    alignItems: 'center', justifyContent: 'center',
  },
  verticalDottedLine: { flex: 1, width: 2, backgroundColor: 'rgba(232,125,62,0.3)', marginVertical: 4 },
  stepTextCol: { flex: 1, gap: 2, paddingBottom: spacing.sm },
  ordersListCtaBtn: {
    flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: spacing.xs,
    backgroundColor: colors.brand.orange500, borderRadius: 12, height: 46, marginTop: spacing.xs,
  },
  section: { gap: spacing.xs },
  sectionTitle: { fontSize: 15, fontWeight: '700' },
  relatedCardBox: {
    backgroundColor: colors.neutral.white, borderRadius: 16,
    borderWidth: 1, borderColor: 'rgba(0,0,0,0.05)', paddingHorizontal: spacing.md,
  },
  relatedRow: { flexDirection: 'row', alignItems: 'center', gap: spacing.sm, paddingVertical: 14 },
  relatedIconCircle: { width: 32, height: 32, borderRadius: 16, backgroundColor: 'rgba(232,125,62,0.1)', alignItems: 'center', justifyContent: 'center' },
  divider: { height: 1, backgroundColor: colors.neutral.gray300 },
  feedbackCard: {
    backgroundColor: colors.neutral.white, borderRadius: 14, padding: spacing.md,
    alignItems: 'center', gap: spacing.xs, borderWidth: 1, borderColor: 'rgba(0,0,0,0.05)',
  },
  feedbackRow: { flexDirection: 'row', gap: spacing.md, marginTop: 4 },
  feedbackChip: {
    flexDirection: 'row', alignItems: 'center', gap: 6,
    backgroundColor: '#FAF8F5', borderRadius: 12, borderWidth: 1,
    borderColor: colors.neutral.gray300, paddingHorizontal: 24, paddingVertical: 10,
  },
  feedbackChipActive: { borderColor: colors.brand.orange500, backgroundColor: 'rgba(232,125,62,0.08)' },
  supportBannerBtn: {
    flexDirection: 'row', alignItems: 'center', gap: spacing.sm,
    backgroundColor: colors.brand.orange500, borderRadius: 16, padding: spacing.md,
  },
  rtlRow: { flexDirection: 'row-reverse' },
  rtlText: { textAlign: 'right' },
});
