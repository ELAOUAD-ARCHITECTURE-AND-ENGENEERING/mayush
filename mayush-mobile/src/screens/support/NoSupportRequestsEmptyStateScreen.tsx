import React from 'react';
import { View, StyleSheet, ScrollView, TouchableOpacity } from 'react-native';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { colors } from '../../design-system/tokens/colors';
import { spacing } from '../../design-system/tokens/spacing';
import { accountPreferencesState } from '../../commerce/accountPreferencesState';
import { TabKey } from '../../design-system/components/navigation/BottomTabBar';

interface NoSupportRequestsEmptyStateScreenProps {
  onNavigateTab: (tab: TabKey) => void;
  onBack: () => void;
  onNavigateFaq: () => void;
  onNavigateContactSupport: () => void;
}

export const NoSupportRequestsEmptyStateScreen: React.FC<NoSupportRequestsEmptyStateScreenProps> = ({
  onNavigateTab,
  onBack,
  onNavigateFaq,
  onNavigateContactSupport,
}) => {
  const language = accountPreferencesState.getLanguage();
  const isRTL = language === 'ar';

  const features = [
    {
      icon: 'help-circle' as const,
      title: isRTL ? 'الحصول على إجابات سريعة' : 'Obtenez des réponses rapides',
      desc: isRTL
        ? 'راجع الأسئلة الشائعة للحصول على حلول للمشاكل المعتادة.'
        : 'Consultez notre FAQ pour trouver des solutions aux questions courantes.',
    },
    {
      icon: 'headphones' as const,
      title: isRTL ? 'التحدث إلى مستشار' : 'Parlez à un conseiller',
      desc: isRTL
        ? 'فريقنا متاح لمرافقتك والإجابة على جميع أسئلتك.'
        : 'Notre équipe est disponible pour vous accompagner et répondre à toutes vos questions.',
    },
    {
      icon: 'clock' as const,
      title: isRTL ? 'مساعدة سريعة' : 'Assistance rapide',
      desc: isRTL
        ? 'نلتزم بالرد عليك في أقرب وقت ممكن.'
        : 'Nous nous engageons à vous répondre dans les meilleurs délais.',
    },
  ];

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
        <TouchableOpacity onPress={onNavigateContactSupport} style={styles.bellBtn} activeOpacity={0.7}>
          <MayushIcon name="bell" size={20} color={colors.brand.navy900} />
        </TouchableOpacity>
      </View>

      <ScrollView style={styles.scrollView} contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
        {/* Empty Illustration Circle */}
        <View style={styles.illustrationBox}>
          <View style={styles.illustrationCircle}>
            <MayushIcon name="headphones" size={44} color={colors.brand.navy900} />
            <View style={styles.bubbleBadge}>
              <MayushIcon name="message-square" size={16} color={colors.brand.orange500} />
            </View>
          </View>
        </View>

        {/* Title Section */}
        <MayushText variant="pageTitle" color={colors.brand.navy900} style={[styles.title, isRTL && styles.rtlText]}>
          {isRTL ? 'لا توجد طلبات مساعدة' : 'Aucune demande d’assistance'}
        </MayushText>
        <MayushText variant="body" color={colors.neutral.gray500} style={[styles.subtitle, isRTL && styles.rtlText]}>
          {isRTL
            ? 'لم تقم بإرسال أي طلب بعد. نحن هنا لمساعدتك!'
            : 'Vous n’avez pas encore soumis de demande. Nous sommes là pour vous aider !'}
        </MayushText>

        {/* Features Card List */}
        <View style={styles.featuresCard}>
          {features.map((item, index) => (
            <View key={index}>
              <View style={styles.featureRow}>
                <View style={styles.featureIconCircle}>
                  <MayushIcon name={item.icon} size={20} color={colors.brand.orange500} />
                </View>
                <View style={styles.featureTextCol}>
                  <MayushText variant="strongBody" color={colors.brand.navy900} style={isRTL && styles.rtlText}>
                    {item.title}
                  </MayushText>
                  <MayushText variant="smallBody" color={colors.neutral.gray500} style={[{ lineHeight: 18, marginTop: 2 }, isRTL && styles.rtlText]}>
                    {item.desc}
                  </MayushText>
                </View>
              </View>
              {index < features.length - 1 && <View style={styles.divider} />}
            </View>
          ))}
        </View>

        {/* Action Buttons */}
        <TouchableOpacity style={styles.primaryBtn} onPress={onNavigateFaq} activeOpacity={0.85}>
          <MayushIcon name="file-text" size={18} color={colors.neutral.white} />
          <MayushText variant="strongBody" color={colors.neutral.white}>
            {isRTL ? 'الاطلاع على الأسئلة الشائعة' : 'Consulter la FAQ'}
          </MayushText>
        </TouchableOpacity>

        <TouchableOpacity style={styles.secondaryBtn} onPress={onNavigateContactSupport} activeOpacity={0.85}>
          <MayushIcon name="headphones" size={18} color={colors.brand.navy900} />
          <MayushText variant="strongBody" color={colors.brand.navy900}>
            {isRTL ? 'التواصل مع الدعم' : 'Contacter le support'}
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
  scrollContent: { padding: spacing.md, paddingBottom: 40, alignItems: 'center' },
  illustrationBox: { marginVertical: spacing.md, alignItems: 'center' },
  illustrationCircle: {
    width: 100, height: 100, borderRadius: 50, backgroundColor: colors.neutral.white,
    borderWidth: 1, borderColor: 'rgba(0,0,0,0.06)', alignItems: 'center', justifyContent: 'center',
    shadowColor: '#000', shadowOffset: { width: 0, height: 2 }, shadowOpacity: 0.05, shadowRadius: 8,
  },
  bubbleBadge: {
    position: 'absolute', bottom: 10, right: 10, backgroundColor: '#FFF2EB',
    borderRadius: 12, padding: 4, borderWidth: 1, borderColor: colors.brand.orange500,
  },
  title: { textAlign: 'center', marginBottom: 4 },
  subtitle: { textAlign: 'center', fontSize: 13, lineHeight: 18, marginBottom: spacing.lg, paddingHorizontal: spacing.md },
  featuresCard: {
    width: '100%', backgroundColor: colors.neutral.white, borderRadius: 16,
    padding: spacing.md, borderWidth: 1, borderColor: 'rgba(0,0,0,0.05)', marginBottom: spacing.lg,
  },
  featureRow: { flexDirection: 'row', alignItems: 'flex-start', gap: spacing.sm, paddingVertical: 8 },
  featureIconCircle: {
    width: 36, height: 36, borderRadius: 18, backgroundColor: 'rgba(232,125,62,0.1)',
    alignItems: 'center', justifyContent: 'center', marginTop: 2,
  },
  featureTextCol: { flex: 1 },
  divider: { height: 1, backgroundColor: colors.neutral.gray300, marginVertical: 6 },
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
