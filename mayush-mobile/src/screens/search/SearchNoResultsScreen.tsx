/**
 * SearchNoResultsScreen (Figma Node 309:602 - 02-search-no-results-found)
 * Empty search results page with helpful suggestions and quick category recovery.
 */

import React from 'react';
import {
  ScrollView,
  StyleSheet,
  TouchableOpacity,
  View,
} from 'react-native';
import { PrimaryButton } from '../../design-system/components/actions/PrimaryButton';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { useTheme } from '../../design-system/theme/useTheme';
import { colors } from '../../design-system/tokens/colors';
import { radii } from '../../design-system/tokens/radii';

export interface SearchNoResultsScreenProps {
  searchQuery: string;
  onBack: () => void;
  onClearSearch: () => void;
  onBrowseCategories: () => void;
}

export const SearchNoResultsScreen: React.FC<SearchNoResultsScreenProps> = ({
  searchQuery,
  onBack,
  onBrowseCategories,
}) => {
  const { isRTL, language } = useTheme();

  return (
    <View style={styles.screen} accessibilityLabel="Search No Results Screen">
      <View style={styles.header}>
        <TouchableOpacity accessibilityRole="button" onPress={onBack} style={styles.iconBtn}>
          <MayushIcon name={isRTL ? 'arrow-right' : 'arrow-left'} size={24} color={colors.brand.navy900} />
        </TouchableOpacity>
        <MayushText variant="sectionTitle" color={colors.brand.navy900} style={styles.headerTitle}>
          {language === 'ar' ? 'البحث' : 'Recherche'}
        </MayushText>
        <View style={styles.iconBtn} />
      </View>

      <ScrollView contentContainerStyle={styles.content} showsVerticalScrollIndicator={false}>
        <View style={styles.emptyCard}>
          <View style={styles.iconCircle}>
            <MayushIcon name="search" size={40} color={colors.brand.orange500} />
          </View>
          <MayushText variant="pageTitle" color={colors.brand.navy900} align="center" style={styles.title}>
            {language === 'ar'
              ? `لا توجد نتائج لـ "${searchQuery}"`
              : `Aucun résultat pour « ${searchQuery} »`}
          </MayushText>
          <MayushText variant="smallBody" color={colors.neutral.gray700} align="center" style={styles.subtitle}>
            {language === 'ar'
              ? 'تأكد من كتابة الكلمات بشكل صحيح أو جرب كلمات مفتاحية أكثر عمومية.'
              : 'Vérifiez l’orthographe ou essayez des termes plus généraux comme « Canapé » ou « Table ».'}
          </MayushText>
        </View>

        <View style={styles.tipsCard}>
          <MayushText variant="strongBody" color={colors.brand.navy900} style={styles.tipsHeading}>
            {language === 'ar' ? 'نصائح للبحث:' : 'Conseils pour votre recherche :'}
          </MayushText>
          <View style={styles.tipRow}>
            <MayushIcon name="check" size={16} color={colors.brand.orange500} style={styles.tipIcon} />
            <MayushText variant="smallBody" color={colors.neutral.gray700}>
              {language === 'ar' ? 'استخدم كلمات رئيسية بسيطة' : 'Utilisez des mots-clés simples.'}
            </MayushText>
          </View>
          <View style={styles.tipRow}>
            <MayushIcon name="check" size={16} color={colors.brand.orange500} style={styles.tipIcon} />
            <MayushText variant="smallBody" color={colors.neutral.gray700}>
              {language === 'ar' ? 'تصفح الكتالوج حسب الفئة' : 'Parcourez notre catalogue par catégorie.'}
            </MayushText>
          </View>
        </View>

        <View style={styles.actionsRow}>
          <PrimaryButton
            label={language === 'ar' ? 'تصفح الفئات' : 'Parcourir les catégories'}
            onPress={onBrowseCategories}
          />
        </View>
      </ScrollView>
    </View>
  );
};

const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: colors.surface.white },
  header: {
    height: 64,
    paddingHorizontal: 16,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    borderBottomWidth: 1,
    borderBottomColor: colors.surface.borderWarm,
  },
  iconBtn: { width: 40, height: 40, alignItems: 'center', justifyContent: 'center' },
  headerTitle: { fontSize: 18, fontWeight: '700' },
  content: { padding: 20, alignItems: 'center' },
  emptyCard: {
    alignItems: 'center',
    marginVertical: 24,
    paddingHorizontal: 16,
  },
  iconCircle: {
    width: 80,
    height: 80,
    borderRadius: 40,
    backgroundColor: colors.brand.orange100,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 16,
  },
  title: { fontSize: 20, lineHeight: 26, marginBottom: 8 },
  subtitle: { lineHeight: 20, maxWidth: 300 },
  tipsCard: {
    width: '100%',
    padding: 16,
    borderRadius: radii.xl,
    backgroundColor: colors.surface.creamLight,
    borderWidth: 1,
    borderColor: colors.surface.borderWarm,
    marginBottom: 24,
  },
  tipsHeading: { marginBottom: 12 },
  tipRow: { flexDirection: 'row', alignItems: 'center', marginBottom: 8 },
  tipIcon: { marginRight: 8 },
  actionsRow: { width: '100%' },
});
