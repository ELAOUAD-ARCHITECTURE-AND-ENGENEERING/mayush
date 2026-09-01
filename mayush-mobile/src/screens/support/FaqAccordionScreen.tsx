import React, { useEffect, useState } from 'react';
import { ScrollView, StyleSheet, TouchableOpacity, View } from 'react-native';
import { accountPreferencesState } from '../../commerce/accountPreferencesState';
import { supportState, FaqItem } from '../../commerce/supportState';
import { BottomTabBar, TabKey } from '../../design-system/components/navigation/BottomTabBar';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { colors } from '../../design-system/tokens/colors';
import { spacing } from '../../design-system/tokens/spacing';

export interface FaqAccordionScreenProps {
  onNavigateTab?: (tab: TabKey) => void;
  onBack?: () => void;
  onNavigateFaqDetail?: (faqId: string) => void;
  onNavigateFaqCategories?: () => void;
}

export const FaqAccordionScreen: React.FC<FaqAccordionScreenProps> = ({
  onNavigateTab,
  onBack,
  onNavigateFaqDetail,
  onNavigateFaqCategories,
}) => {
  const isRTL = accountPreferencesState.getSelectedLanguage() === 'ar';
  const [expandedId, setExpandedId] = useState<string>('');
  const [searchText, setSearchText] = useState('');
  const [faqItems, setFaqItems] = useState<FaqItem[]>(supportState.getFaqItems());

  useEffect(() => {
    const unsub = supportState.subscribe(() => {
      setFaqItems(supportState.getFaqItems());
    });
    return unsub;
  }, []);

  const filteredItems = searchText
    ? faqItems.filter(
        (item) =>
          item.question.toLowerCase().includes(searchText.toLowerCase()) ||
          item.answer.toLowerCase().includes(searchText.toLowerCase()),
      )
    : faqItems;

  const toggleAccordion = (id: string) => {
    setExpandedId(expandedId === id ? '' : id);
  };

  const openDetail = (id: string) => {
    supportState.setSelectedFaqId(id);
    onNavigateFaqDetail?.(id);
  };

  return (
    <View style={styles.container}>
      {/* Header */}
      <View style={[styles.header, isRTL && styles.rtlRow]}>
        <TouchableOpacity style={styles.backButton} onPress={onBack} activeOpacity={0.7}>
          <MayushIcon name={isRTL ? 'chevron-right' : 'chevron-left'} size={24} color={colors.brand.navy900} />
        </TouchableOpacity>
        <MayushText variant="pageTitle" color={colors.brand.navy900} style={styles.headerTitle}>
          {isRTL ? 'الأسئلة الشائعة' : 'Questions Fréquentes'}
        </MayushText>
        <TouchableOpacity style={styles.backButton} onPress={onNavigateFaqCategories} activeOpacity={0.7}>
          <MayushIcon name="grid" size={22} color={colors.brand.navy900} />
        </TouchableOpacity>
      </View>

      {/* Search */}
      <View style={styles.searchContainer}>
        <View style={[styles.searchBox, isRTL && styles.rtlRow]}>
          <MayushIcon name="search" size={18} color={colors.neutral.gray500} />
          <MayushText
            variant="body"
            color={searchText ? colors.brand.navy900 : colors.neutral.gray500}
            style={[styles.searchInput, isRTL && styles.rtlText]}
            onPress={() => {}}
          >
            {searchText || (isRTL ? 'ابحث في الأسئلة...' : 'Rechercher une question...')}
          </MayushText>
        </View>
      </View>

      <ScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
        {filteredItems.map((item) => {
          const isExpanded = expandedId === item.id;
          const questionText = isRTL ? item.questionAr : item.question;
          const answerText = isRTL ? item.answerAr : item.answer;

          return (
            <View key={item.id} style={[styles.accordionCard, isExpanded && styles.accordionCardExpanded]}>
              <TouchableOpacity
                style={[styles.accordionHeader, isRTL && styles.rtlRow]}
                onPress={() => toggleAccordion(item.id)}
                activeOpacity={0.7}
              >
                <MayushText
                  variant="strongBody"
                  color={colors.brand.navy900}
                  style={[styles.questionText, isRTL && styles.rtlText]}
                >
                  {questionText}
                </MayushText>
                <MayushIcon
                  name={isExpanded ? 'chevron-up' : 'chevron-down'}
                  size={20}
                  color={isExpanded ? colors.brand.orange500 : colors.neutral.gray500}
                />
              </TouchableOpacity>

              {isExpanded && (
                <View style={styles.accordionBody}>
                  <MayushText
                    variant="body"
                    color={colors.neutral.gray700}
                    style={[styles.answerText, isRTL && styles.rtlText]}
                  >
                    {answerText}
                  </MayushText>
                  <TouchableOpacity
                    style={[styles.detailLink, isRTL && styles.rtlRow]}
                    onPress={() => openDetail(item.id)}
                    activeOpacity={0.7}
                  >
                    <MayushText variant="smallBody" color={colors.brand.orange500}>
                      {isRTL ? 'عرض التفاصيل الكاملة' : 'Voir la réponse complète'}
                    </MayushText>
                    <MayushIcon name={isRTL ? 'chevron-left' : 'chevron-right'} size={14} color={colors.brand.orange500} />
                  </TouchableOpacity>
                </View>
              )}
            </View>
          );
        })}

        {filteredItems.length === 0 && (
          <View style={styles.emptyContainer}>
            <MayushIcon name="help-circle" size={48} color={colors.neutral.gray500} />
            <MayushText variant="body" color={colors.neutral.gray500} style={{ marginTop: spacing.sm }}>
              {isRTL ? 'لم يتم العثور على أسئلة.' : 'Aucune question trouvée.'}
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
  searchContainer: { paddingHorizontal: spacing.md, paddingVertical: spacing.sm, backgroundColor: colors.neutral.white },
  searchBox: {
    flexDirection: 'row', alignItems: 'center', gap: spacing.xs,
    backgroundColor: colors.neutral.gray100, borderRadius: 12, paddingHorizontal: spacing.sm, height: 44,
  },
  searchInput: { flex: 1 },
  scrollContent: { padding: spacing.md, gap: spacing.xs, paddingBottom: 100 },
  accordionCard: {
    backgroundColor: colors.neutral.white, borderRadius: 14, borderWidth: 1,
    borderColor: colors.neutral.gray300, overflow: 'hidden',
  },
  accordionCardExpanded: { borderColor: colors.brand.orange500 },
  accordionHeader: {
    flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between',
    padding: spacing.md, gap: spacing.sm,
  },
  questionText: { flex: 1, lineHeight: 22 },
  accordionBody: {
    paddingHorizontal: spacing.md, paddingBottom: spacing.md,
    borderTopWidth: 1, borderTopColor: colors.neutral.gray300,
    paddingTop: spacing.sm,
  },
  answerText: { lineHeight: 22, marginBottom: spacing.sm },
  detailLink: { flexDirection: 'row', alignItems: 'center', gap: 4 },
  emptyContainer: { alignItems: 'center', paddingVertical: spacing.xl },
  rtlRow: { flexDirection: 'row-reverse' },
  rtlText: { textAlign: 'right' },
});
