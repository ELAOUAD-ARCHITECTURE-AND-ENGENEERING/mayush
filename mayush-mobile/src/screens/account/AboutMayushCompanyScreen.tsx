import React from 'react';
import { ScrollView, StyleSheet, TouchableOpacity, View } from 'react-native';
import { accountPreferencesState } from '../../commerce/accountPreferencesState';
import { MayushLogo } from '../../design-system/components/brand/MayushLogo';
import { BottomTabBar, TabKey } from '../../design-system/components/navigation/BottomTabBar';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { colors } from '../../design-system/tokens/colors';
import { spacing } from '../../design-system/tokens/spacing';

export interface AboutMayushCompanyScreenProps {
  onNavigateTab?: (tab: TabKey) => void;
  onBack?: () => void;
  onNavigateAccessibility?: () => void;
}

export const AboutMayushCompanyScreen: React.FC<AboutMayushCompanyScreenProps> = ({
  onNavigateTab,
  onBack,
  onNavigateAccessibility,
}) => {
  const isRTL = accountPreferencesState.getSelectedLanguage() === 'ar';

  const companyValues = [
    {
      icon: 'sofa',
      title: isRTL ? 'تصميم راقٍ ومبتكر' : 'Design Épuré & Innovant',
      desc: isRTL ? 'قطع أثاث متميزة تعكس الفخامة والأناقة.' : 'Collections exclusives pensées pour sublimer vos espaces.',
    },
    {
      icon: 'shield-check',
      title: isRTL ? 'جودة ومواد رفيعة' : 'Qualité & Matériaux Nobles',
      desc: isRTL ? 'خشب ناعم، أقمشة فاخرة وتشطيبات متقنة.' : 'Bois nobles, tissus raffinés et finitions d\'exception.',
    },
    {
      icon: 'heart',
      title: isRTL ? 'حرفية مغربية وعالمية' : 'Artisanat & Savoir-faire',
      desc: isRTL ? 'دمج التراث المغربي العصري بالأناقة العالمية.' : 'Alliance parfaite entre tradition marocaine et modernité.',
    },
  ];

  return (
    <View style={styles.container}>
      {/* Header */}
      <View style={[styles.header, isRTL && styles.rtlRow]}>
        <TouchableOpacity style={styles.backButton} onPress={onBack} activeOpacity={0.7}>
          <MayushIcon name={isRTL ? 'chevron-right' : 'chevron-left'} size={24} color={colors.brand.navy900} />
        </TouchableOpacity>
        <MayushText variant="pageTitle" color={colors.brand.navy900} style={styles.headerTitle}>
          {isRTL ? 'عن مايووش ديزاين' : 'À propos de Mayush'}
        </MayushText>
        <View style={{ width: 40 }} />
      </View>

      <ScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
        {/* Brand Mission Banner */}
        <View style={styles.heroCard}>
          <View style={styles.logoWrapper}>
            <MayushLogo width={180} height={54} />
          </View>
          <MayushText variant="cardTitle" color={colors.brand.navy900} align="center" style={styles.heroTagline}>
            {isRTL ? 'المنصة الأولى للأثاث والديكور الفاخر في المغرب' : 'La Première Marketplace du Mobilier & Décoration au Maroc'}
          </MayushText>
          <MayushText variant="body" color={colors.neutral.gray700} align="center" style={styles.heroBody}>
            {isRTL
              ? 'تأسست مايووش ديزاين بهدف تقديم أرقى تشكيلات الأثاث والديكور الداخلي بالمغرب، مع الحرص على أعلى معايير الجودة والتوصيل السريع والآمن.'
              : 'Mayush Design a été créée pour offrir une expérience d\'achat d\'exception pour votre intérieur au Maroc, alliant esthétique, qualité supérieure et service de livraison sur-mesure.'}
          </MayushText>
        </View>

        {/* Company Values */}
        <MayushText variant="sectionTitle" color={colors.brand.navy900} style={[styles.sectionLabel, isRTL && styles.rtlText]}>
          {isRTL ? 'قيمنا ومبادئنا' : 'Nos Valeurs'}
        </MayushText>

        {companyValues.map((val, idx) => (
          <View key={idx} style={[styles.valueRow, isRTL && styles.rtlRow]}>
            <View style={styles.valueIconBox}>
              <MayushIcon name={val.icon as any} size={22} color={colors.brand.orange500} />
            </View>
            <View style={styles.valueTextCol}>
              <MayushText variant="strongBody" color={colors.brand.navy900} style={isRTL && styles.rtlText}>
                {val.title}
              </MayushText>
              <MayushText variant="smallBody" color={colors.neutral.gray500} style={isRTL && styles.rtlText}>
                {val.desc}
              </MayushText>
            </View>
          </View>
        ))}

        {/* Website & Contact Info */}
        <View style={styles.contactCard}>
          <MayushText variant="sectionTitle" color={colors.brand.navy900} style={[styles.sectionLabel, isRTL && styles.rtlText]}>
            {isRTL ? 'موقعنا وتواصل معنا' : 'Site Web & Présence'}
          </MayushText>

          <View style={[styles.contactRow, isRTL && styles.rtlRow]}>
            <MayushIcon name="globe" size={18} color={colors.brand.orange500} />
            <MayushText variant="smallBody" color={colors.brand.navy900}>
              www.mayush.ma
            </MayushText>
          </View>

          <View style={styles.divider} />

          <View style={[styles.contactRow, isRTL && styles.rtlRow]}>
            <MayushIcon name="mail" size={18} color={colors.brand.orange500} />
            <MayushText variant="smallBody" color={colors.brand.navy900}>
              contact@mayush.ma
            </MayushText>
          </View>
        </View>

        {/* Next Step Navigation to Accessibility */}
        <TouchableOpacity
          style={[styles.nextCard, isRTL && styles.rtlRow]}
          onPress={onNavigateAccessibility}
          activeOpacity={0.85}
        >
          <View style={styles.nextIconBox}>
            <MayushIcon name="user" size={20} color={colors.brand.navy900} />
          </View>
          <View style={styles.nextTextCol}>
            <MayushText variant="strongBody" color={colors.brand.navy900} style={isRTL && styles.rtlText}>
              {isRTL ? 'إعدادات إمكانية الوصول' : 'Accessibilité & Contraste'}
            </MayushText>
            <MayushText variant="smallBody" color={colors.neutral.gray500} style={isRTL && styles.rtlText}>
              {isRTL ? 'تخصيص العرض وحجم الخط' : 'Personnaliser l\'affichage'}
            </MayushText>
          </View>
          <MayushIcon name={isRTL ? 'chevron-left' : 'chevron-right'} size={18} color={colors.neutral.gray500} />
        </TouchableOpacity>
      </ScrollView>

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
  heroCard: {
    backgroundColor: colors.neutral.white, borderRadius: 16, padding: spacing.lg,
    borderWidth: 1, borderColor: colors.neutral.gray300, alignItems: 'center',
  },
  logoWrapper: { marginBottom: spacing.sm },
  heroTagline: { fontSize: 16, fontWeight: '700', marginTop: spacing.xs, marginBottom: spacing.xs },
  heroBody: { lineHeight: 22, marginTop: spacing.xs },
  sectionLabel: { fontSize: 16, fontWeight: '700' },
  valueRow: {
    flexDirection: 'row', alignItems: 'center', gap: spacing.md,
    backgroundColor: colors.neutral.white, borderRadius: 14, padding: spacing.md,
    borderWidth: 1, borderColor: colors.neutral.gray300,
  },
  valueIconBox: {
    width: 44, height: 44, borderRadius: 12, backgroundColor: 'rgba(217,116,52,0.1)',
    alignItems: 'center', justifyContent: 'center',
  },
  valueTextCol: { flex: 1 },
  contactCard: {
    backgroundColor: colors.neutral.white, borderRadius: 16, padding: spacing.md,
    borderWidth: 1, borderColor: colors.neutral.gray300, gap: spacing.xs,
  },
  contactRow: { flexDirection: 'row', alignItems: 'center', gap: spacing.sm, paddingVertical: 4 },
  divider: { height: 1, backgroundColor: colors.neutral.gray300 },
  nextCard: {
    flexDirection: 'row', alignItems: 'center', gap: spacing.md,
    backgroundColor: colors.neutral.white, borderRadius: 16, padding: spacing.md,
    borderWidth: 1, borderColor: colors.neutral.gray300,
  },
  nextIconBox: {
    width: 40, height: 40, borderRadius: 10, backgroundColor: colors.neutral.gray100,
    alignItems: 'center', justifyContent: 'center',
  },
  nextTextCol: { flex: 1 },
  rtlRow: { flexDirection: 'row-reverse' },
  rtlText: { textAlign: 'right' },
});
