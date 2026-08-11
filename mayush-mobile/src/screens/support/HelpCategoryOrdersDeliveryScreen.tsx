import React from 'react';
import { Image, ScrollView, StyleSheet, TouchableOpacity, View } from 'react-native';
import { accountPreferencesState } from '../../commerce/accountPreferencesState';
import { supportState } from '../../commerce/supportState';
import { BottomTabBar, TabKey } from '../../design-system/components/navigation/BottomTabBar';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { colors } from '../../design-system/tokens/colors';
import { spacing } from '../../design-system/tokens/spacing';

export interface HelpCategoryOrdersDeliveryScreenProps {
  onNavigateTab?: (tab: TabKey) => void;
  onBack?: () => void;
  onNavigateArticleTrackOrder?: () => void;
  onNavigateOrdersList?: () => void;
  onNavigateReturnRefund?: () => void;
  onNavigateReportDeliveryIssue?: () => void;
  onNavigateContactSupport?: () => void;
}

export const HelpCategoryOrdersDeliveryScreen: React.FC<HelpCategoryOrdersDeliveryScreenProps> = ({
  onNavigateTab,
  onBack,
  onNavigateArticleTrackOrder,
  onNavigateOrdersList,
  onNavigateReturnRefund,
  onNavigateReportDeliveryIssue,
  onNavigateContactSupport,
}) => {
  const isRTL = accountPreferencesState.getSelectedLanguage() === 'ar';

  const popularQuestions = [
    {
      id: 'faq-1',
      titleFr: 'Comment suivre ma commande ?',
      titleAr: 'كيف أتتبع طلبي؟',
      icon: 'truck',
      onPress: () => {
        supportState.setSelectedArticleId('faq-1');
        onNavigateArticleTrackOrder?.();
      },
    },
    {
      id: 'faq-2',
      titleFr: 'Quels sont les délais de livraison ?',
      titleAr: 'ما هي مواعيد التسليم؟',
      icon: 'clock',
      onPress: () => {
        supportState.setSelectedArticleId('faq-2');
        onNavigateArticleTrackOrder?.();
      },
    },
    {
      id: 'faq-8',
      titleFr: 'Puis-je modifier ou annuler ma commande ?',
      titleAr: 'هل يمكنني تعديل أو إلغاء طلبي؟',
      icon: 'package',
      onPress: () => {
        supportState.setSelectedArticleId('faq-8');
        onNavigateArticleTrackOrder?.();
      },
    },
    {
      id: 'faq-9',
      titleFr: 'Livrez-vous dans ma ville ?',
      titleAr: 'هل تقومون بالتوصيل إلى مدينتي؟',
      icon: 'map-pin',
      onPress: () => {
        supportState.setSelectedArticleId('faq-9');
        onNavigateArticleTrackOrder?.();
      },
    },
  ];

  const linkedActions = [
    {
      id: 'my-orders',
      titleFr: 'Consulter mes commandes',
      titleAr: 'الاطلاع على طلباتي',
      icon: 'file-text',
      onPress: onNavigateOrdersList,
    },
    {
      id: 'request-return',
      titleFr: 'Demander un retour ou un remboursement',
      titleAr: 'طلب إرجاع أو استرداد المبلغ',
      icon: 'rotate-ccw',
      onPress: onNavigateReturnRefund || onNavigateContactSupport,
    },
    {
      id: 'report-issue',
      titleFr: 'Signaler un problème de livraison',
      titleAr: 'الإبلاغ عن مشكلة في التوصيل',
      icon: 'shield-alert',
      onPress: onNavigateReportDeliveryIssue || onNavigateContactSupport,
    },
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
        {/* Category Header Illustration */}
        <View style={styles.headerIllustrationCard}>
          <View style={styles.badgeCircle}>
            <MayushIcon name="truck" size={32} color={colors.brand.orange500} />
          </View>
          <MayushText variant="pageTitle" color={colors.brand.navy900} style={[styles.title, isRTL && styles.rtlText]}>
            {isRTL ? 'الطلبات والتوصيل' : 'Commandes et livraison'}
          </MayushText>
          <MayushText variant="body" color={colors.neutral.gray500} style={[styles.subtitle, isRTL && styles.rtlText]}>
            {isRTL
              ? 'تتبع طلباتك، اطلع على المواعيد واعثر على إجابات لأسئلتك.'
              : 'Suivez vos commandes, consultez les délais et trouvez des réponses à vos questions.'}
          </MayushText>
        </View>

        {/* Popular Questions Section */}
        <View style={styles.section}>
          <MayushText variant="sectionTitle" color={colors.brand.navy900} style={[styles.sectionTitle, isRTL && styles.rtlText]}>
            {isRTL ? 'الأسئلة الشائعة' : 'Questions populaires'}
          </MayushText>

          <View style={styles.cardBox}>
            {popularQuestions.map((q, idx) => (
              <React.Fragment key={q.id}>
                {idx > 0 && <View style={styles.divider} />}
                <TouchableOpacity style={[styles.rowItem, isRTL && styles.rtlRow]} onPress={q.onPress} activeOpacity={0.7}>
                  <View style={styles.itemIconCircle}>
                    <MayushIcon name={q.icon as any} size={18} color={colors.brand.orange500} />
                  </View>
                  <MayushText variant="strongBody" color={colors.brand.navy900} style={[{ flex: 1 }, isRTL && styles.rtlText]}>
                    {isRTL ? q.titleAr : q.titleFr}
                  </MayushText>
                  <MayushIcon name={isRTL ? 'chevron-left' : 'chevron-right'} size={18} color={colors.neutral.gray500} />
                </TouchableOpacity>
              </React.Fragment>
            ))}
          </View>
        </View>

        {/* Linked Actions Section */}
        <View style={styles.section}>
          <MayushText variant="sectionTitle" color={colors.brand.navy900} style={[styles.sectionTitle, isRTL && styles.rtlText]}>
            {isRTL ? 'الإجراءات المرتبطة' : 'Actions liées'}
          </MayushText>

          <View style={styles.cardBox}>
            {linkedActions.map((act, idx) => (
              <React.Fragment key={act.id}>
                {idx > 0 && <View style={styles.divider} />}
                <TouchableOpacity style={[styles.rowItem, isRTL && styles.rtlRow]} onPress={act.onPress} activeOpacity={0.7}>
                  <View style={styles.itemIconCircle}>
                    <MayushIcon name={act.icon as any} size={18} color={colors.brand.orange500} />
                  </View>
                  <MayushText variant="strongBody" color={colors.brand.navy900} style={[{ flex: 1 }, isRTL && styles.rtlText]}>
                    {isRTL ? act.titleAr : act.titleFr}
                  </MayushText>
                  <MayushIcon name={isRTL ? 'chevron-left' : 'chevron-right'} size={18} color={colors.neutral.gray500} />
                </TouchableOpacity>
              </React.Fragment>
            ))}
          </View>
        </View>

        {/* Support CTA Button */}
        <TouchableOpacity style={styles.supportBanner} onPress={onNavigateContactSupport} activeOpacity={0.85}>
          <MayushIcon name="message-circle" size={24} color={colors.neutral.white} />
          <View style={{ flex: 1 }}>
            <MayushText variant="strongBody" color={colors.neutral.white} style={isRTL && styles.rtlText}>
              {isRTL ? 'التواصل مع الدعم' : 'Contacter le support'}
            </MayushText>
            <MayushText variant="caption" color="rgba(255,255,255,0.85)" style={isRTL && styles.rtlText}>
              {isRTL ? 'نحن هنا لمساعدتك' : 'Nous sommes là pour vous aider'}
            </MayushText>
          </View>
          <MayushIcon name={isRTL ? 'chevron-left' : 'chevron-right'} size={20} color={colors.neutral.white} />
        </TouchableOpacity>

        {/* Back to Help Button */}
        <TouchableOpacity style={styles.backHelpBtn} onPress={onBack} activeOpacity={0.7}>
          <MayushIcon name={isRTL ? 'arrow-right' : 'arrow-left'} size={18} color={colors.brand.navy900} />
          <MayushText variant="strongBody" color={colors.brand.navy900}>
            {isRTL ? 'العودة إلى المساعدة' : 'Retour à l\'aide'}
          </MayushText>
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
  headerIllustrationCard: { alignItems: 'center', gap: 8, paddingVertical: spacing.md },
  badgeCircle: {
    width: 64, height: 64, borderRadius: 32, backgroundColor: 'rgba(232,125,62,0.12)',
    alignItems: 'center', justifyContent: 'center', marginBottom: 4,
  },
  title: { fontSize: 22, fontWeight: '700', fontFamily: 'serif', color: colors.brand.navy900, textAlign: 'center' },
  subtitle: { fontSize: 13, textAlign: 'center', paddingHorizontal: spacing.md },
  section: { gap: spacing.xs },
  sectionTitle: { fontSize: 15, fontWeight: '700' },
  cardBox: {
    backgroundColor: colors.neutral.white, borderRadius: 16,
    borderWidth: 1, borderColor: 'rgba(0,0,0,0.05)', paddingHorizontal: spacing.md,
  },
  rowItem: { flexDirection: 'row', alignItems: 'center', gap: spacing.sm, paddingVertical: 14 },
  itemIconCircle: { width: 36, height: 36, borderRadius: 18, backgroundColor: 'rgba(232,125,62,0.1)', alignItems: 'center', justifyContent: 'center' },
  divider: { height: 1, backgroundColor: colors.neutral.gray300 },
  supportBanner: {
    flexDirection: 'row', alignItems: 'center', gap: spacing.sm,
    backgroundColor: colors.brand.orange500, borderRadius: 16, padding: spacing.md,
  },
  backHelpBtn: {
    flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: spacing.xs,
    backgroundColor: colors.neutral.white, borderRadius: 14, height: 48,
    borderWidth: 1, borderColor: colors.brand.navy900,
  },
  rtlRow: { flexDirection: 'row-reverse' },
  rtlText: { textAlign: 'right' },
});
