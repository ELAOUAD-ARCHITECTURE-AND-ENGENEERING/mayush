import React, { useEffect, useState } from 'react';
import { ScrollView, StyleSheet, TouchableOpacity, View } from 'react-native';
import { accountPreferencesState } from '../../commerce/accountPreferencesState';
import { supportState, FaqItem, FaqCategory } from '../../commerce/supportState';
import { BottomTabBar, TabKey } from '../../design-system/components/navigation/BottomTabBar';
import { MayushIcon, MayushIconName } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { colors } from '../../design-system/tokens/colors';
import { spacing } from '../../design-system/tokens/spacing';

export interface FaqDetailScreenProps {
  onNavigateTab?: (tab: TabKey) => void;
  onBack?: () => void;
  onNavigateFaqCategories?: () => void;
}

export const FaqDetailScreen: React.FC<FaqDetailScreenProps> = ({
  onNavigateTab,
  onBack,
  onNavigateFaqCategories,
}) => {
  const isRTL = accountPreferencesState.getSelectedLanguage() === 'ar';
  const [faqItem, setFaqItem] = useState<FaqItem | undefined>(
    supportState.getFaqItemById(supportState.getSelectedFaqId()),
  );
  const [category, setCategory] = useState<FaqCategory | undefined>();

  useEffect(() => {
    const unsub = supportState.subscribe(() => {
      const item = supportState.getFaqItemById(supportState.getSelectedFaqId());
      setFaqItem(item);
      if (item) {
        setCategory(supportState.getFaqCategories().find((c) => c.id === item.categoryId));
      }
    });
    // Init
    const item = supportState.getFaqItemById(supportState.getSelectedFaqId());
    if (item) {
      setCategory(supportState.getFaqCategories().find((c) => c.id === item.categoryId));
    }
    return unsub;
  }, []);

  const questionText = isRTL ? faqItem?.questionAr : faqItem?.question;
  const answerText = isRTL ? faqItem?.answerAr : faqItem?.answer;
  const categoryLabel = isRTL ? category?.labelAr : category?.label;

  return (
    <View style={styles.container}>
      {/* Header */}
      <View style={[styles.header, isRTL && styles.rtlRow]}>
        <TouchableOpacity style={styles.backButton} onPress={onBack} activeOpacity={0.7}>
          <MayushIcon name={isRTL ? 'chevron-right' : 'chevron-left'} size={24} color={colors.brand.navy900} />
        </TouchableOpacity>
        <MayushText variant="pageTitle" color={colors.brand.navy900} style={styles.headerTitle}>
          {isRTL ? 'تفاصيل السؤال' : 'Détail FAQ'}
        </MayushText>
        <View style={{ width: 40 }} />
      </View>

      <ScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
        {faqItem ? (
          <>
            {/* Category Badge */}
            {category && (
              <View style={[styles.categoryBadge, isRTL && styles.rtlRow]}>
                <MayushIcon name={(category.icon || 'help-circle') as MayushIconName} size={16} color={colors.brand.orange500} />
                <MayushText variant="caption" color={colors.brand.orange500}>
                  {categoryLabel}
                </MayushText>
              </View>
            )}

            {/* Question Card */}
            <View style={styles.card}>
              <MayushText
                variant="cardTitle"
                color={colors.brand.navy900}
                style={[styles.questionTitle, isRTL && styles.rtlText]}
              >
                {questionText}
              </MayushText>

              <View style={styles.divider} />

              <MayushText
                variant="body"
                color={colors.neutral.gray700}
                style={[styles.answerBody, isRTL && styles.rtlText]}
              >
                {answerText}
              </MayushText>
            </View>

            {/* Helpful section */}
            <View style={styles.card}>
              <MayushText
                variant="sectionTitle"
                color={colors.brand.navy900}
                style={[styles.helpfulTitle, isRTL && styles.rtlText]}
              >
                {isRTL ? 'هل كانت هذه الإجابة مفيدة؟' : 'Cette réponse vous a-t-elle aidé ?'}
              </MayushText>
              <View style={[styles.helpfulRow, isRTL && styles.rtlRow]}>
                <TouchableOpacity style={styles.helpfulButton} activeOpacity={0.7}>
                  <MayushIcon name="thumbs-up" size={20} color={colors.semantic.success} />
                  <MayushText variant="smallBody" color={colors.semantic.success}>
                    {isRTL ? 'نعم' : 'Oui'}
                  </MayushText>
                </TouchableOpacity>
                <TouchableOpacity style={styles.helpfulButton} activeOpacity={0.7}>
                  <MayushIcon name="thumbs-down" size={20} color={colors.semantic.error} />
                  <MayushText variant="smallBody" color={colors.semantic.error}>
                    {isRTL ? 'لا' : 'Non'}
                  </MayushText>
                </TouchableOpacity>
              </View>
            </View>

            {/* Navigate to categories */}
            <TouchableOpacity
              style={[styles.secondaryButton, isRTL && styles.rtlRow]}
              onPress={onNavigateFaqCategories}
              activeOpacity={0.85}
            >
              <MayushIcon name="grid" size={18} color={colors.brand.navy900} />
              <MayushText variant="button" color={colors.brand.navy900}>
                {isRTL ? 'تصفح جميع الفئات' : 'Parcourir les catégories FAQ'}
              </MayushText>
            </TouchableOpacity>
          </>
        ) : (
          <View style={styles.emptyContainer}>
            <MayushIcon name="help-circle" size={48} color={colors.neutral.gray500} />
            <MayushText variant="body" color={colors.neutral.gray500} style={{ marginTop: spacing.sm }}>
              {isRTL ? 'لم يتم تحديد سؤال.' : 'Aucune question sélectionnée.'}
            </MayushText>
          </View>
        )}
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
  categoryBadge: {
    flexDirection: 'row', alignItems: 'center', gap: spacing.xs,
    backgroundColor: 'rgba(217,116,52,0.1)', alignSelf: 'flex-start',
    paddingHorizontal: 12, paddingVertical: 6, borderRadius: 8,
  },
  card: {
    backgroundColor: colors.neutral.white, borderRadius: 16, padding: spacing.md,
    borderWidth: 1, borderColor: colors.neutral.gray300,
  },
  questionTitle: { fontSize: 17, fontWeight: '700', lineHeight: 24 },
  divider: { height: 1, backgroundColor: colors.neutral.gray300, marginVertical: spacing.md },
  answerBody: { lineHeight: 24 },
  helpfulTitle: { fontSize: 15, fontWeight: '600', marginBottom: spacing.sm },
  helpfulRow: { flexDirection: 'row', gap: spacing.md },
  helpfulButton: {
    flexDirection: 'row', alignItems: 'center', gap: spacing.xs,
    backgroundColor: colors.neutral.gray100, paddingHorizontal: spacing.md,
    paddingVertical: spacing.sm, borderRadius: 10,
  },
  secondaryButton: {
    height: 48, backgroundColor: colors.neutral.white, borderWidth: 1,
    borderColor: colors.neutral.gray300, borderRadius: 14,
    flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: spacing.xs,
  },
  emptyContainer: { alignItems: 'center', paddingVertical: spacing.xl },
  rtlRow: { flexDirection: 'row-reverse' },
  rtlText: { textAlign: 'right' },
});
