/**
 * SearchLandingScreen (Figma Node 309:600 - 02-search-recent-popular-trending-categories)
 * Search entry screen with recent searches, popular keywords, and trending category shortcuts.
 */

import React, { useState } from 'react';
import {
  ScrollView,
  StyleSheet,
  TextInput,
  TouchableOpacity,
  View,
} from 'react-native';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { useTheme } from '../../design-system/theme/useTheme';
import { colors } from '../../design-system/tokens/colors';
import { radii } from '../../design-system/tokens/radii';

export interface SearchLandingScreenProps {
  onBack: () => void;
  onSearchSubmit: (query: string) => void;
  onSelectCategoryShortcut: (catSlug: string) => void;
}

export const SearchLandingScreen: React.FC<SearchLandingScreenProps> = ({
  onBack,
  onSearchSubmit,
  onSelectCategoryShortcut,
}) => {
  const { isRTL, language } = useTheme();
  const [query, setQuery] = useState('');
  const [recentSearches, setRecentSearches] = useState([
    'Canapé bouclé',
    'Fauteuil vert',
    'Table basse plâtre',
  ]);

  const popularSearches = [
    'Canapé 3 places',
    'Fauteuil accent',
    'Meuble TV bois',
    'Miroir arche',
    'Tapis berbère',
  ];

  const trendingCategories = [
    { slug: 'salon', name: language === 'ar' ? 'صالون' : 'Salon & Séjour', icon: 'sofa' },
    { slug: 'chambre', name: language === 'ar' ? 'غرفة النوم' : 'Chambre à coucher', icon: 'home' },
    { slug: 'salle-a-manger', name: language === 'ar' ? 'غرفة الطعام' : 'Salle à manger', icon: 'tag' },
    { slug: 'decoration', name: language === 'ar' ? 'ديكور' : 'Décoration & Miroirs', icon: 'briefcase' },
  ];

  const handleSearch = (term: string) => {
    if (!term.trim()) return;
    if (!recentSearches.includes(term)) {
      setRecentSearches([term, ...recentSearches.slice(0, 4)]);
    }
    onSearchSubmit(term);
  };

  return (
    <View style={styles.screen} accessibilityLabel="Search Landing Screen">
      <View style={styles.header}>
        <TouchableOpacity accessibilityRole="button" onPress={onBack} style={styles.iconBtn}>
          <MayushIcon name={isRTL ? 'arrow-right' : 'arrow-left'} size={24} color={colors.brand.navy900} />
        </TouchableOpacity>
        <View style={styles.searchBar}>
          <MayushIcon name="search" size={20} color={colors.neutral.gray700} />
          <TextInput
            value={query}
            onChangeText={setQuery}
            onSubmitEditing={() => handleSearch(query)}
            placeholder={language === 'ar' ? 'ابحث عن أثاث، ديكور...' : 'Rechercher un meuble, canapé…'}
            placeholderTextColor={colors.neutral.gray500}
            returnKeyType="search"
            style={[styles.searchInput, { textAlign: isRTL ? 'right' : 'left' }]}
            autoFocus
          />
          {query ? (
            <TouchableOpacity onPress={() => setQuery('')}>
              <MayushIcon name="x-circle" size={18} color={colors.neutral.gray500} />
            </TouchableOpacity>
          ) : null}
        </View>
      </View>

      <ScrollView contentContainerStyle={styles.content} keyboardShouldPersistTaps="handled">
        {recentSearches.length > 0 ? (
          <View style={styles.section}>
            <View style={styles.sectionHeaderRow}>
              <MayushText variant="sectionTitle" color={colors.brand.navy900} style={styles.sectionTitle}>
                {language === 'ar' ? 'عمليات البحث الأخيرة' : 'Recherches récentes'}
              </MayushText>
              <TouchableOpacity onPress={() => setRecentSearches([])}>
                <MayushText variant="caption" color={colors.brand.orange500}>
                  {language === 'ar' ? 'مسح الكل' : 'Effacer'}
                </MayushText>
              </TouchableOpacity>
            </View>
            <View style={styles.chipRow}>
              {recentSearches.map((term) => (
                <TouchableOpacity key={term} style={styles.chip} onPress={() => handleSearch(term)}>
                  <MayushIcon name="clock" size={14} color={colors.neutral.gray700} style={styles.chipIcon} />
                  <MayushText variant="caption" color={colors.brand.navy900}>
                    {term}
                  </MayushText>
                </TouchableOpacity>
              ))}
            </View>
          </View>
        ) : null}

        <View style={styles.section}>
          <MayushText variant="sectionTitle" color={colors.brand.navy900} style={styles.sectionTitle}>
            {language === 'ar' ? 'الأكثر بحثاً' : 'Recherches populaires'}
          </MayushText>
          <View style={styles.chipRow}>
            {popularSearches.map((term) => (
              <TouchableOpacity key={term} style={styles.popularChip} onPress={() => handleSearch(term)}>
                <MayushIcon name="trending-up" size={14} color={colors.brand.orange500} style={styles.chipIcon} />
                <MayushText variant="caption" color={colors.brand.navy900}>
                  {term}
                </MayushText>
              </TouchableOpacity>
            ))}
          </View>
        </View>

        <View style={styles.section}>
          <MayushText variant="sectionTitle" color={colors.brand.navy900} style={styles.sectionTitle}>
            {language === 'ar' ? 'الفئات المتداولة' : 'Catégories en tendance'}
          </MayushText>
          <View style={styles.catGrid}>
            {trendingCategories.map((cat) => (
              <TouchableOpacity
                key={cat.slug}
                style={styles.catCard}
                onPress={() => onSelectCategoryShortcut(cat.slug)}
              >
                <MayushIcon name={cat.icon as any} size={22} color={colors.brand.navy900} />
                <MayushText variant="strongBody" color={colors.brand.navy900} style={styles.catName}>
                  {cat.name}
                </MayushText>
              </TouchableOpacity>
            ))}
          </View>
        </View>
      </ScrollView>
    </View>
  );
};

const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: colors.surface.white },
  header: {
    height: 64,
    paddingHorizontal: 12,
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    borderBottomWidth: 1,
    borderBottomColor: colors.surface.borderWarm,
  },
  iconBtn: { width: 36, height: 36, alignItems: 'center', justifyContent: 'center' },
  searchBar: {
    flex: 1,
    height: 44,
    borderRadius: radii.pill,
    backgroundColor: colors.surface.creamLight,
    borderWidth: 1,
    borderColor: colors.surface.borderWarm,
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 12,
    gap: 8,
  },
  searchInput: { flex: 1, height: '100%', fontSize: 15, color: colors.brand.navy900 },
  content: { padding: 16 },
  section: { marginBottom: 24 },
  sectionHeaderRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 12 },
  sectionTitle: { fontSize: 18, marginBottom: 12 },
  chipRow: { flexDirection: 'row', flexWrap: 'wrap', gap: 8 },
  chip: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 12,
    paddingVertical: 8,
    borderRadius: radii.pill,
    backgroundColor: colors.surface.creamLight,
    borderWidth: 1,
    borderColor: colors.surface.borderWarm,
  },
  popularChip: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 12,
    paddingVertical: 8,
    borderRadius: radii.pill,
    backgroundColor: colors.brand.orange100,
    borderWidth: 1,
    borderColor: colors.surface.borderWarm,
  },
  chipIcon: { marginRight: 6 },
  catGrid: { flexDirection: 'row', flexWrap: 'wrap', gap: 12 },
  catCard: {
    width: '48%',
    padding: 16,
    borderRadius: radii.lg,
    backgroundColor: colors.surface.creamLight,
    borderWidth: 1,
    borderColor: colors.surface.borderWarm,
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
  },
  catName: { fontSize: 14, flex: 1 },
});
