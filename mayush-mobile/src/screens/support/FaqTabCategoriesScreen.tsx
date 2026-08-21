import React, { useEffect, useState } from 'react';
import { Image, ScrollView, StyleSheet, TextInput, TouchableOpacity, View } from 'react-native';
import { accountPreferencesState } from '../../commerce/accountPreferencesState';
import { FaqCategory, FaqItem, supportState } from '../../commerce/supportState';
import { TabKey } from '../../design-system/components/navigation/BottomTabBar';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { colors } from '../../design-system/tokens/colors';
import { spacing } from '../../design-system/tokens/spacing';

export interface FaqTabCategoriesScreenProps {
  onNavigateTab?: (tab: TabKey) => void;
  onBack?: () => void;
  onNavigateArticle?: (articleId: string) => void;
  onNavigateContactSupport?: () => void;
}

export const FaqTabCategoriesScreen: React.FC<FaqTabCategoriesScreenProps> = ({
  onNavigateTab,
  onBack,
  onNavigateArticle,
  onNavigateContactSupport,
}) => {
  const isRTL = accountPreferencesState.getSelectedLanguage() === 'ar';
  const [selectedTab, setSelectedTab] = useState<string>('all');
  const [expandedFaqId, setExpandedFaqId] = useState<string>('faq-1');
  const [searchQuery, setSearchQuery] = useState<string>('');
  const [feedbackGiven, setFeedbackGiven] = useState<'yes' | 'no' | null>(null);
  const [faqCategories, setFaqCategories] = useState<FaqCategory[]>(supportState.getFaqCategories());
  const [allFaqs, setAllFaqs] = useState<FaqItem[]>(supportState.getFaqItems());

  useEffect(() => {
    const unsub = supportState.subscribe(() => {
      setFaqCategories(supportState.getFaqCategories());
      setAllFaqs(supportState.getFaqItems());
    });
    return unsub;
  }, []);

  const ALL_TAB = { id: 'all', label: 'Toutes', labelAr: 'الكل' };
  const tabs = [
    ALL_TAB,
    ...faqCategories.map((cat) => ({ id: cat.id, label: cat.label.split(' ')[0], labelAr: cat.labelAr.split(' ')[0] })),
  ];

  const filteredFaqs = allFaqs.filter((item) => {
    const matchesTab = selectedTab === 'all' || item.categoryId === selectedTab;
    const q = searchQuery.toLowerCase().trim();
    const matchesSearch = !q || item.question.toLowerCase().includes(q) || item.answer.toLowerCase().includes(q) || item.questionAr.includes(q);
    return matchesTab && matchesSearch;
  });

  const toggleExpand = (item: FaqItem) => {
    if (expandedFaqId === item.id) {
      setExpandedFaqId('');
    } else {
      setExpandedFaqId(item.id);
      if (item.steps || item.id === 'faq-1') {
        supportState.setSelectedArticleId(item.id);
        onNavigateArticle?.(item.id);
      }
    }
  };

  const getIconForCategory = (categoryId: string) => {
    switch (categoryId) {
      case 'commandes': return 'package';
      case 'paiement': return 'credit-card';
      case 'livraison': return 'truck';
      case 'retours': return 'rotate-ccw';
      case 'compte': return 'shield-check';
      default: return 'help-circle';
    }
  };

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
        {/* Title Block */}
        <View style={styles.titleBlock}>
          <MayushText variant="pageTitle" color={colors.brand.navy900} style={[styles.mainTitle, isRTL && styles.rtlText]}>
            {isRTL ? 'مركز المساعدة' : 'Centre d\'aide'}
          </MayushText>
          <MayushText variant="body" color={colors.neutral.gray500} style={[styles.mainSubtitle, isRTL && styles.rtlText]}>
            {isRTL ? 'اعثر بسرعة على إجابات لأسئلتك.' : 'Trouvez rapidement des réponses à vos questions.'}
          </MayushText>
        </View>

        {/* Search Bar Input */}
        <View style={[styles.searchBox, isRTL && styles.rtlRow]}>
          <MayushIcon name="search" size={20} color={colors.neutral.gray500} />
          <TextInput
            style={[styles.searchInput, isRTL && styles.rtlText]}
            placeholder={isRTL ? 'البحث عن سؤال...' : 'Rechercher une question...'}
            placeholderTextColor={colors.neutral.gray500}
            value={searchQuery}
            onChangeText={setSearchQuery}
          />
          {searchQuery.length > 0 && (
            <TouchableOpacity onPress={() => setSearchQuery('')} activeOpacity={0.7}>
              <MayushIcon name="x" size={18} color={colors.neutral.gray500} />
            </TouchableOpacity>
          )}
        </View>

        {/* Horizontal Scrollable Category Tabs */}
        <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={[styles.tabsContainer, isRTL && styles.rtlRow]}>
          {tabs.map((tab) => {
            const isSelected = selectedTab === tab.id;
            return (
              <TouchableOpacity
                key={tab.id}
                style={[styles.tabItem, isSelected && styles.tabItemSelected]}
                onPress={() => setSelectedTab(tab.id)}
                activeOpacity={0.7}
              >
                <MayushText variant="strongBody" color={isSelected ? colors.brand.orange500 : colors.brand.navy900}>
                  {isRTL ? tab.labelAr : tab.label}
                </MayushText>
                {isSelected && <View style={styles.tabIndicator} />}
              </TouchableOpacity>
            );
          })}
        </ScrollView>

        {/* FAQ Accordion List */}
        <View style={styles.faqList}>
          {filteredFaqs.map((item) => {
            const isExpanded = expandedFaqId === item.id;
            const iconName = getIconForCategory(item.categoryId);
            return (
              <View key={item.id} style={styles.accordionCard}>
                <TouchableOpacity
                  style={[styles.accordionHeader, isRTL && styles.rtlRow]}
                  onPress={() => toggleExpand(item)}
                  activeOpacity={0.7}
                >
                  <View style={styles.accordionIconCircle}>
                    <MayushIcon name={iconName as any} size={18} color={colors.brand.orange500} />
                  </View>
                  <MayushText variant="strongBody" color={colors.brand.navy900} style={[{ flex: 1 }, isRTL && styles.rtlText]}>
                    {isRTL ? item.questionAr : item.question}
                  </MayushText>
                  <MayushIcon name={isExpanded ? 'chevron-up' : 'chevron-down'} size={20} color={colors.neutral.gray500} />
                </TouchableOpacity>

                {isExpanded && (
                  <View style={styles.accordionBody}>
                    <View style={styles.accordionDivider} />
                    <MayushText variant="body" color={colors.neutral.gray700} style={[styles.answerText, isRTL && styles.rtlText]}>
                      {isRTL ? item.answerAr : item.answer}
                    </MayushText>
                    {item.steps && (
                      <TouchableOpacity
                        style={styles.viewFullArticleBtn}
                        onPress={() => {
                          supportState.setSelectedArticleId(item.id);
                          onNavigateArticle?.(item.id);
                        }}
                        activeOpacity={0.7}
                      >
                        <MayushText variant="smallBody" color={colors.brand.orange500} style={{ fontWeight: '600' }}>
                          {isRTL ? 'عرض خطوات الدليل الكاملة ←' : 'Consulter les étapes détaillées →'}
                        </MayushText>
                      </TouchableOpacity>
                    )}
                  </View>
                )}
              </View>
            );
          })}
        </View>

        {/* Helpful Feedback Section */}
        <View style={styles.feedbackSection}>
          <MayushText variant="smallBody" color={colors.neutral.gray500} style={{ textAlign: 'center' }}>
            {isRTL ? 'هل كان هذا المقال مفيداً بالنسبة لك؟' : 'Cet article vous a-t-il été utile ?'}
          </MayushText>
          <View style={styles.feedbackBtnRow}>
            <TouchableOpacity
              style={[styles.feedbackBtn, feedbackGiven === 'yes' && styles.feedbackBtnActive]}
              onPress={() => setFeedbackGiven('yes')}
              activeOpacity={0.7}
            >
              <MayushIcon name="thumbs-up" size={16} color={feedbackGiven === 'yes' ? colors.brand.orange500 : colors.brand.navy900} />
              <MayushText variant="strongBody" color={feedbackGiven === 'yes' ? colors.brand.orange500 : colors.brand.navy900}>
                {isRTL ? 'نعم، شكراً' : 'Oui, merci'}
              </MayushText>
            </TouchableOpacity>

            <TouchableOpacity
              style={[styles.feedbackBtn, feedbackGiven === 'no' && styles.feedbackBtnActive]}
              onPress={() => setFeedbackGiven('no')}
              activeOpacity={0.7}
            >
              <MayushIcon name="thumbs-down" size={16} color={feedbackGiven === 'no' ? colors.brand.orange500 : colors.brand.navy900} />
              <MayushText variant="strongBody" color={feedbackGiven === 'no' ? colors.brand.orange500 : colors.brand.navy900}>
                {isRTL ? 'ليس تماماً' : 'Pas vraiment'}
              </MayushText>
            </TouchableOpacity>
          </View>
        </View>

        {/* Support Contact Banner Card */}
        <View style={[styles.bannerOrangeCard, isRTL && styles.rtlRow]}>
          <View style={styles.bannerIconCircleWhite}>
            <MayushIcon name="headphones" size={24} color={colors.brand.orange500} />
          </View>
          <View style={{ flex: 1 }}>
            <MayushText variant="strongBody" color={colors.neutral.white} style={isRTL && styles.rtlText}>
              {isRTL ? 'ألم تجد إجابتك؟' : 'Vous ne trouvez pas votre réponse ?'}
            </MayushText>
            <MayushText variant="caption" color="rgba(255,255,255,0.85)" style={isRTL && styles.rtlText}>
              {isRTL ? 'فريق الدعم لدينا هنا لمساعدتك.' : 'Notre équipe support est là pour vous aider.'}
            </MayushText>
          </View>
          <TouchableOpacity style={styles.whiteContactBtn} onPress={onNavigateContactSupport} activeOpacity={0.85}>
            <MayushText variant="strongBody" color={colors.brand.orange500}>
              {isRTL ? 'التواصل مع الدعم >' : 'Contacter le support >'}
            </MayushText>
          </TouchableOpacity>
        </View>
      </ScrollView>

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
  titleBlock: { alignItems: 'center', gap: 4, marginVertical: 4 },
  mainTitle: { fontSize: 24, fontWeight: '700', fontFamily: 'serif', color: colors.brand.navy900 },
  mainSubtitle: { fontSize: 14, textAlign: 'center' },
  searchBox: {
    flexDirection: 'row', alignItems: 'center', backgroundColor: colors.neutral.white,
    borderRadius: 14, borderWidth: 1, borderColor: colors.neutral.gray300,
    paddingHorizontal: spacing.sm, height: 48, gap: spacing.xs,
  },
  searchInput: { flex: 1, fontSize: 14, color: colors.brand.navy900, paddingVertical: 0 },
  tabsContainer: { gap: spacing.md, borderBottomWidth: 1, borderBottomColor: colors.neutral.gray300, paddingBottom: 4 },
  tabItem: { paddingVertical: 8, paddingHorizontal: 4, position: 'relative' },
  tabItemSelected: {},
  tabIndicator: { position: 'absolute', bottom: -5, left: 0, right: 0, height: 3, backgroundColor: colors.brand.orange500, borderRadius: 2 },
  faqList: { gap: 10 },
  accordionCard: {
    backgroundColor: colors.neutral.white, borderRadius: 14,
    borderWidth: 1, borderColor: 'rgba(0,0,0,0.05)', overflow: 'hidden',
  },
  accordionHeader: { flexDirection: 'row', alignItems: 'center', gap: spacing.sm, padding: spacing.md },
  accordionIconCircle: { width: 36, height: 36, borderRadius: 18, backgroundColor: 'rgba(232,125,62,0.1)', alignItems: 'center', justifyContent: 'center' },
  accordionBody: { paddingHorizontal: spacing.md, paddingBottom: spacing.md },
  accordionDivider: { height: 1, backgroundColor: colors.neutral.gray300, marginBottom: spacing.sm },
  answerText: { fontSize: 13, lineHeight: 20 },
  viewFullArticleBtn: { marginTop: spacing.xs },
  feedbackSection: { alignItems: 'center', gap: spacing.xs, marginVertical: spacing.xs },
  feedbackBtnRow: { flexDirection: 'row', gap: spacing.md },
  feedbackBtn: {
    flexDirection: 'row', alignItems: 'center', gap: 6,
    backgroundColor: colors.neutral.white, borderRadius: 12, borderWidth: 1,
    borderColor: colors.neutral.gray300, paddingHorizontal: 16, paddingVertical: 10,
  },
  feedbackBtnActive: { borderColor: colors.brand.orange500, backgroundColor: 'rgba(232,125,62,0.06)' },
  bannerOrangeCard: {
    flexDirection: 'row', alignItems: 'center', gap: spacing.sm,
    backgroundColor: colors.brand.orange500, borderRadius: 16, padding: spacing.md,
  },
  bannerIconCircleWhite: { width: 44, height: 44, borderRadius: 22, backgroundColor: colors.neutral.white, alignItems: 'center', justifyContent: 'center' },
  whiteContactBtn: { backgroundColor: colors.neutral.white, borderRadius: 12, paddingHorizontal: 12, paddingVertical: 10 },
  rtlRow: { flexDirection: 'row-reverse' },
  rtlText: { textAlign: 'right' },
});
