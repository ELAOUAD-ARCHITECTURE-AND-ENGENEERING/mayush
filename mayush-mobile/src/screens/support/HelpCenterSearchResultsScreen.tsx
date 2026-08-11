import React, { useEffect, useState } from 'react';
import { Image, ScrollView, StyleSheet, TextInput, TouchableOpacity, View } from 'react-native';
import { accountPreferencesState } from '../../commerce/accountPreferencesState';
import { supportState } from '../../commerce/supportState';
import { BottomTabBar, TabKey } from '../../design-system/components/navigation/BottomTabBar';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { colors } from '../../design-system/tokens/colors';
import { spacing } from '../../design-system/tokens/spacing';

export interface HelpCenterSearchResultsScreenProps {
  onNavigateTab?: (tab: TabKey) => void;
  onBack?: () => void;
  onNavigateArticle?: (articleId: string) => void;
  onNavigateCategory?: (categoryId: string) => void;
  onNavigateContactSupport?: () => void;
}

export const HelpCenterSearchResultsScreen: React.FC<HelpCenterSearchResultsScreenProps> = ({
  onNavigateTab,
  onBack,
  onNavigateArticle,
  onNavigateCategory,
  onNavigateContactSupport,
}) => {
  const isRTL = accountPreferencesState.getSelectedLanguage() === 'ar';
  const [query, setQuery] = useState(supportState.getSearchQuery() || 'Comment suivre ma commande ?');
  const [searchResults, setSearchResults] = useState(supportState.searchHelp(query));

  useEffect(() => {
    setSearchResults(supportState.searchHelp(query));
  }, [query]);

  const handleClear = () => {
    setQuery('');
    supportState.setSearchQuery('');
  };

  const handleSelectArticle = (id: string) => {
    supportState.setSelectedArticleId(id);
    onNavigateArticle?.(id);
  };

  const handleSelectCategory = (id: string) => {
    supportState.setSelectedHelpCategory(id);
    onNavigateCategory?.(id);
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
            placeholder={isRTL ? 'البحث عن مساعدة...' : 'Rechercher une aide...'}
            placeholderTextColor={colors.neutral.gray500}
            value={query}
            onChangeText={(t) => {
              setQuery(t);
              supportState.setSearchQuery(t);
            }}
            returnKeyType="search"
          />
          {query.length > 0 && (
            <TouchableOpacity onPress={handleClear} activeOpacity={0.7}>
              <MayushIcon name="x" size={18} color={colors.neutral.gray500} />
            </TouchableOpacity>
          )}
        </View>

        {/* Results Counter Summary */}
        {query.trim().length > 0 && (
          <MayushText variant="smallBody" color={colors.neutral.gray500} style={[styles.resultCountText, isRTL && styles.rtlText]}>
            {searchResults.totalResults > 0
              ? isRTL
                ? `تم العثور على ${searchResults.totalResults} نتيجة لـ "${query}"`
                : `${searchResults.totalResults} résultats trouvés pour "${query}"`
              : isRTL
              ? `لم يتم العثور على نتائج لـ "${query}"`
              : `Aucun résultat trouvé pour "${query}"`}
          </MayushText>
        )}

        {/* No Results Fallback */}
        {query.trim().length > 0 && searchResults.totalResults === 0 && (
          <View style={styles.noResultsCard}>
            <View style={styles.noResultsIconCircle}>
              <MayushIcon name="search" size={32} color={colors.neutral.gray500} />
            </View>
            <MayushText variant="sectionTitle" color={colors.brand.navy900} style={{ textAlign: 'center' }}>
              {isRTL ? 'لم يتم العثور على نتائج' : 'Aucun résultat trouvé'}
            </MayushText>
            <MayushText variant="body" color={colors.neutral.gray500} style={{ textAlign: 'center', lineHeight: 20 }}>
              {isRTL
                ? 'جرّب الكلمات المفتاحية المختلفة أو تواصل مباشرة مع فريق الدعم.'
                : 'Essayez d\'autres mots-clés ou contactez directement notre équipe support pour obtenir de l\'aide.'}
            </MayushText>
            <TouchableOpacity style={styles.noResultsBtn} onPress={onNavigateContactSupport} activeOpacity={0.85}>
              <MayushText variant="strongBody" color={colors.neutral.white}>
                {isRTL ? 'التواصل مع الدعم' : 'Contacter le support'}
              </MayushText>
            </TouchableOpacity>
          </View>
        )}

        {/* Articles Section */}
        {searchResults.articles.length > 0 && (
          <View style={styles.section}>
            <MayushText variant="sectionTitle" color={colors.brand.navy900} style={[styles.sectionTitle, isRTL && styles.rtlText]}>
              {isRTL ? 'المقالات' : 'Articles'}
            </MayushText>

            <View style={styles.cardList}>
              {searchResults.articles.map((item) => (
                <TouchableOpacity
                  key={item.id}
                  style={[styles.articleCard, isRTL && styles.rtlRow]}
                  onPress={() => handleSelectArticle(item.id)}
                  activeOpacity={0.7}
                >
                  <View style={styles.articleIconCircle}>
                    <MayushIcon name="file-text" size={18} color={colors.brand.orange500} />
                  </View>
                  <View style={styles.cardTextCol}>
                    <MayushText variant="strongBody" color={colors.brand.navy900} style={isRTL && styles.rtlText}>
                      {isRTL ? item.questionAr : item.question}
                    </MayushText>
                    <MayushText variant="smallBody" color={colors.neutral.gray500} numberOfLines={2} style={isRTL && styles.rtlText}>
                      {isRTL ? (item.subtitleAr || item.answerAr) : (item.subtitle || item.answer)}
                    </MayushText>
                  </View>
                  <MayushIcon name={isRTL ? 'chevron-left' : 'chevron-right'} size={18} color={colors.neutral.gray500} />
                </TouchableOpacity>
              ))}
            </View>
          </View>
        )}

        {/* Categories Section */}
        {searchResults.categories.length > 0 && (
          <View style={styles.section}>
            <MayushText variant="sectionTitle" color={colors.brand.navy900} style={[styles.sectionTitle, isRTL && styles.rtlText]}>
              {isRTL ? 'الفئات' : 'Catégories'}
            </MayushText>

            <View style={styles.cardList}>
              {searchResults.categories.map((cat) => (
                <TouchableOpacity
                  key={cat.id}
                  style={[styles.articleCard, isRTL && styles.rtlRow]}
                  onPress={() => handleSelectCategory(cat.id)}
                  activeOpacity={0.7}
                >
                  <View style={styles.categoryIconCircle}>
                    <MayushIcon name={cat.icon as any} size={18} color={colors.brand.navy900} />
                  </View>
                  <View style={styles.cardTextCol}>
                    <MayushText variant="strongBody" color={colors.brand.navy900} style={isRTL && styles.rtlText}>
                      {isRTL ? cat.labelAr : cat.label}
                    </MayushText>
                    <MayushText variant="smallBody" color={colors.neutral.gray500} style={isRTL && styles.rtlText}>
                      {isRTL ? (cat.subtitleAr || 'المتابعة والمعلومات') : (cat.subtitleFr || 'Informations et aide')}
                    </MayushText>
                  </View>
                  <MayushIcon name={isRTL ? 'chevron-left' : 'chevron-right'} size={18} color={colors.neutral.gray500} />
                </TouchableOpacity>
              ))}
            </View>
          </View>
        )}

        {/* Support Contact Banner Card */}
        <View style={[styles.bannerCard, isRTL && styles.rtlRow]}>
          <View style={styles.bannerIconCircle}>
            <MayushIcon name="headphones" size={24} color={colors.brand.orange500} />
          </View>
          <View style={{ flex: 1 }}>
            <MayushText variant="strongBody" color={colors.brand.navy900} style={isRTL && styles.rtlText}>
              {isRTL ? 'ألم تجد إجابتك؟' : 'Vous ne trouvez pas votre réponse ?'}
            </MayushText>
            <MayushText variant="smallBody" color={colors.neutral.gray500} style={isRTL && styles.rtlText}>
              {isRTL ? 'فريق الدعم لدينا هنا لمساعدتك.' : 'Notre équipe support est là pour vous aider.'}
            </MayushText>
          </View>
          <TouchableOpacity style={styles.bannerBtn} onPress={onNavigateContactSupport} activeOpacity={0.85}>
            <MayushText variant="strongBody" color={colors.neutral.white}>
              {isRTL ? 'التواصل معنا' : 'Nous contacter'}
            </MayushText>
          </TouchableOpacity>
        </View>
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
  titleBlock: { alignItems: 'center', gap: 4, marginVertical: 4 },
  mainTitle: { fontSize: 24, fontWeight: '700', fontFamily: 'serif', color: colors.brand.navy900 },
  mainSubtitle: { fontSize: 14, textAlign: 'center' },
  searchBox: {
    flexDirection: 'row', alignItems: 'center', backgroundColor: colors.neutral.white,
    borderRadius: 14, borderWidth: 1, borderColor: colors.neutral.gray300,
    paddingHorizontal: spacing.sm, height: 48, gap: spacing.xs,
  },
  searchInput: { flex: 1, fontSize: 14, color: colors.brand.navy900, paddingVertical: 0 },
  resultCountText: { fontSize: 13, marginHorizontal: 4 },
  section: { gap: spacing.xs },
  sectionTitle: { fontSize: 16, fontWeight: '700' },
  cardList: { gap: 10 },
  articleCard: {
    flexDirection: 'row', alignItems: 'center', gap: spacing.sm,
    backgroundColor: colors.neutral.white, borderRadius: 14, padding: spacing.md,
    borderWidth: 1, borderColor: 'rgba(0,0,0,0.05)',
  },
  articleIconCircle: { width: 40, height: 40, borderRadius: 20, backgroundColor: 'rgba(232,125,62,0.1)', alignItems: 'center', justifyContent: 'center' },
  categoryIconCircle: { width: 40, height: 40, borderRadius: 20, backgroundColor: 'rgba(31,42,58,0.06)', alignItems: 'center', justifyContent: 'center' },
  cardTextCol: { flex: 1, gap: 2 },
  noResultsCard: {
    backgroundColor: colors.neutral.white, borderRadius: 16, padding: spacing.lg,
    alignItems: 'center', gap: spacing.sm, borderWidth: 1, borderColor: 'rgba(0,0,0,0.05)',
  },
  noResultsIconCircle: { width: 60, height: 60, borderRadius: 30, backgroundColor: 'rgba(0,0,0,0.04)', alignItems: 'center', justifyContent: 'center' },
  noResultsBtn: {
    backgroundColor: colors.brand.orange500, borderRadius: 12, paddingHorizontal: 24, paddingVertical: 12, marginTop: 8,
  },
  bannerCard: {
    flexDirection: 'row', alignItems: 'center', gap: spacing.sm,
    backgroundColor: colors.neutral.white, borderRadius: 16, padding: spacing.md,
    borderWidth: 1, borderColor: 'rgba(0,0,0,0.05)',
  },
  bannerIconCircle: { width: 44, height: 44, borderRadius: 22, backgroundColor: 'rgba(232,125,62,0.1)', alignItems: 'center', justifyContent: 'center' },
  bannerBtn: { backgroundColor: colors.brand.orange500, borderRadius: 12, paddingHorizontal: 14, paddingVertical: 10 },
  rtlRow: { flexDirection: 'row-reverse' },
  rtlText: { textAlign: 'right' },
});
