import React, { useEffect, useState } from 'react';
import { ScrollView, StyleSheet, TouchableOpacity, View } from 'react-native';
import { accountPreferencesState } from '../../commerce/accountPreferencesState';
import { supportState, FaqCategory, FaqItem } from '../../commerce/supportState';
import { BottomTabBar, TabKey } from '../../design-system/components/navigation/BottomTabBar';
import { MayushIcon, MayushIconName } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { colors } from '../../design-system/tokens/colors';
import { spacing } from '../../design-system/tokens/spacing';

export interface FaqCategoriesScreenProps {
  onNavigateTab?: (tab: TabKey) => void;
  onBack?: () => void;
  onNavigateHelpCenter?: () => void;
  onNavigateFaqDetail?: (faqId: string) => void;
}

export const FaqCategoriesScreen: React.FC<FaqCategoriesScreenProps> = ({
  onNavigateTab,
  onBack,
  onNavigateHelpCenter,
  onNavigateFaqDetail,
}) => {
  const isRTL = accountPreferencesState.getSelectedLanguage() === 'ar';
  const [categories] = useState<FaqCategory[]>(supportState.getFaqCategories());
  const [selectedCat, setSelectedCat] = useState<string>(supportState.getSelectedFaqCategory());
  const [filteredItems, setFilteredItems] = useState<FaqItem[]>(supportState.getFaqItems());

  useEffect(() => {
    const unsub = supportState.subscribe(() => {
      setSelectedCat(supportState.getSelectedFaqCategory());
    });
    return unsub;
  }, []);

  useEffect(() => {
    setFilteredItems(supportState.getFaqItemsByCategory(selectedCat));
  }, [selectedCat]);

  const selectCategory = (catId: string) => {
    const newCat = selectedCat === catId ? '' : catId;
    supportState.setSelectedFaqCategory(newCat);
    setSelectedCat(newCat);
  };

  const openFaqDetail = (faqId: string) => {
    supportState.setSelectedFaqId(faqId);
    onNavigateFaqDetail?.(faqId);
  };

  return (
    <View style={styles.container}>
      {/* Header */}
      <View style={[styles.header, isRTL && styles.rtlRow]}>
        <TouchableOpacity style={styles.backButton} onPress={onBack} activeOpacity={0.7}>
          <MayushIcon name={isRTL ? 'chevron-right' : 'chevron-left'} size={24} color={colors.brand.navy900} />
        </TouchableOpacity>
        <MayushText variant="pageTitle" color={colors.brand.navy900} style={styles.headerTitle}>
          {isRTL ? 'فئات الأسئلة' : 'Catégories FAQ'}
        </MayushText>
        <View style={{ width: 40 }} />
      </View>

      {/* Category Tabs */}
      <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.tabsContainer}>
        <TouchableOpacity
          style={[styles.categoryChip, !selectedCat && styles.categoryChipActive]}
          onPress={() => selectCategory('')}
          activeOpacity={0.7}
        >
          <MayushText variant="smallBody" color={!selectedCat ? colors.surface.white : colors.brand.navy900}>
            {isRTL ? 'الكل' : 'Toutes'}
          </MayushText>
        </TouchableOpacity>
        {categories.map((cat) => (
          <TouchableOpacity
            key={cat.id}
            style={[styles.categoryChip, selectedCat === cat.id && styles.categoryChipActive]}
            onPress={() => selectCategory(cat.id)}
            activeOpacity={0.7}
          >
            <MayushIcon
              name={(cat.icon || 'help-circle') as MayushIconName}
              size={16}
              color={selectedCat === cat.id ? colors.surface.white : colors.brand.navy900}
            />
            <MayushText
              variant="smallBody"
              color={selectedCat === cat.id ? colors.surface.white : colors.brand.navy900}
            >
              {isRTL ? cat.labelAr : cat.label}
            </MayushText>
          </TouchableOpacity>
        ))}
      </ScrollView>

      {/* Filtered FAQ List */}
      <ScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
        {filteredItems.map((item) => (
          <TouchableOpacity
            key={item.id}
            style={[styles.faqCard, isRTL && styles.rtlRow]}
            onPress={() => openFaqDetail(item.id)}
            activeOpacity={0.7}
          >
            <View style={styles.faqCardContent}>
              <MayushText
                variant="strongBody"
                color={colors.brand.navy900}
                style={[styles.faqQuestion, isRTL && styles.rtlText]}
              >
                {isRTL ? item.questionAr : item.question}
              </MayushText>
              <MayushText
                variant="caption"
                color={colors.neutral.gray500}
                numberOfLines={2}
                style={isRTL && styles.rtlText}
              >
                {isRTL ? item.answerAr : item.answer}
              </MayushText>
            </View>
            <MayushIcon name={isRTL ? 'chevron-left' : 'chevron-right'} size={18} color={colors.neutral.gray500} />
          </TouchableOpacity>
        ))}

        {/* Help Center Navigation */}
        <TouchableOpacity
          style={[styles.helpCenterCard, isRTL && styles.rtlRow]}
          onPress={onNavigateHelpCenter}
          activeOpacity={0.85}
        >
          <View style={styles.helpIconBox}>
            <MayushIcon name="life-buoy" size={22} color={colors.brand.orange500} />
          </View>
          <View style={styles.helpTextCol}>
            <MayushText variant="strongBody" color={colors.brand.navy900} style={isRTL && styles.rtlText}>
              {isRTL ? 'مركز المساعدة' : 'Centre d\'Aide'}
            </MayushText>
            <MayushText variant="smallBody" color={colors.neutral.gray500} style={isRTL && styles.rtlText}>
              {isRTL ? 'تواصل معنا واستكشف المزيد' : 'Contactez-nous et explorez les ressources'}
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
  tabsContainer: {
    paddingHorizontal: spacing.md, paddingVertical: spacing.sm, gap: spacing.xs,
    backgroundColor: colors.neutral.white,
  },
  categoryChip: {
    flexDirection: 'row', alignItems: 'center', gap: 6,
    paddingHorizontal: 14, paddingVertical: 8, borderRadius: 20,
    borderWidth: 1, borderColor: colors.neutral.gray300, backgroundColor: colors.neutral.white,
  },
  categoryChipActive: {
    backgroundColor: colors.brand.navy900, borderColor: colors.brand.navy900,
  },
  scrollContent: { padding: spacing.md, gap: spacing.xs, paddingBottom: 100 },
  faqCard: {
    flexDirection: 'row', alignItems: 'center', gap: spacing.sm,
    backgroundColor: colors.neutral.white, borderRadius: 14, padding: spacing.md,
    borderWidth: 1, borderColor: colors.neutral.gray300,
  },
  faqCardContent: { flex: 1 },
  faqQuestion: { lineHeight: 22, marginBottom: 4 },
  helpCenterCard: {
    flexDirection: 'row', alignItems: 'center', gap: spacing.md,
    backgroundColor: colors.neutral.white, borderRadius: 16, padding: spacing.md,
    borderWidth: 1, borderColor: colors.brand.orange500, marginTop: spacing.sm,
  },
  helpIconBox: {
    width: 44, height: 44, borderRadius: 12, backgroundColor: 'rgba(217,116,52,0.1)',
    alignItems: 'center', justifyContent: 'center',
  },
  helpTextCol: { flex: 1 },
  rtlRow: { flexDirection: 'row-reverse' },
  rtlText: { textAlign: 'right' },
});
