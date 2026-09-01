import React, { useEffect, useState } from 'react';
import { View, ScrollView, StyleSheet, Image, TouchableOpacity } from 'react-native';
import { useTheme } from '../../design-system/theme/useTheme';
import { MayushLogo } from '../../design-system/components/brand/MayushLogo';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { BottomTabBar, TabKey } from '../../design-system/components/navigation/BottomTabBar';
import { MayushIcon, MayushIconName } from '../../design-system/components/navigation/MayushIcon';
import { TextField } from '../../design-system/components/forms/TextField';
import { Skeleton } from '../../design-system/components/feedback/Skeleton';
import { colors } from '../../design-system/tokens/colors';
import { catalogService } from '../../services/api/catalogService';
import { CategoryDto } from '../../contracts/api/dto';

const categoryIcons: MayushIconName[] = ['sofa', 'bed', 'table-furniture', 'lamp', 'rug', 'package-variant-closed', 'chair-rolling', 'outdoor-lamp'];

export interface CategoriesScreenProps {
  onSelectCategory?: (category: CategoryDto) => void;
  onNavigateTab?: (tab: TabKey) => void;
  activeTab?: TabKey;
}

export const CategoriesScreen: React.FC<CategoriesScreenProps> = ({ onSelectCategory, onNavigateTab, activeTab = 'categories' }) => {
  const { language, isRTL } = useTheme();
  const [loading, setLoading] = useState(true);
  const [categories, setCategories] = useState<CategoryDto[]>([]);

  useEffect(() => {
    let mounted = true;
    catalogService.getRootCategories(language).then((data) => {
      if (mounted) { setCategories(data); setLoading(false); }
    });
    return () => { mounted = false; };
  }, [language]);

  const navigate = (tab: TabKey) => { onNavigateTab?.(tab); };
  const heading = (fr: string, ar: string) => isRTL ? ar : fr;
  const categoryRows = categories.slice(0, 9).reduce<CategoryDto[][]>((rows, category, index) => {
    const rowIndex = Math.floor(index / 3);
    rows[rowIndex] = rows[rowIndex] || [];
    rows[rowIndex].push(category);
    return rows;
  }, []);

  return (
    <View style={styles.container}>
      <ScrollView showsVerticalScrollIndicator={false} contentContainerStyle={styles.content}>
        <View style={styles.header}>
          <MayushLogo width={190} height={63} />
          <TouchableOpacity style={[styles.headerButton, isRTL && styles.headerButtonRtl]}><MayushIcon name="bell" size={26} color={colors.brand.navy900} /></TouchableOpacity>
        </View>
        <View style={styles.searchRow}>
          <TextField value="" placeholder={heading('Rechercher un produit, une catégorie...', '\u0627\u0628\u062d\u062b \u0639\u0646 \u0645\u0646\u062a\u062c \u0623\u0648 \u0642\u0633\u0645...')} leftIcon="search" containerStyle={{ flex: 1 }} />
          <TouchableOpacity style={styles.filterButton}><MayushIcon name="sliders-horizontal" size={24} color={colors.brand.navy900} /></TouchableOpacity>
        </View>
        {loading ? <View style={styles.grid}>{[1,2,3,4,5,6].map((item) => <Skeleton key={item} width="31%" height={170} borderRadius="xl" />)}</View> : (
          <View style={styles.grid}>
            {categoryRows.map((row, rowIndex) => (
              <View key={`row-${rowIndex}`} style={styles.gridRow}>
                {row.map((category, columnIndex) => {
                  const index = (rowIndex * 3) + columnIndex;
                  return (
                    <TouchableOpacity key={category.id} activeOpacity={0.85} onPress={() => onSelectCategory?.(category)} style={styles.card}>
                      <Image source={{ uri: category.banner || category.icon }} style={styles.cardImage} resizeMode="cover" />
                      <View style={[styles.cardFooter, isRTL && styles.rowReverse]}>
                        <MayushIcon name={categoryIcons[index % categoryIcons.length]} size={21} color={colors.brand.orange500} />
                        <MayushText variant="smallBody" color={colors.brand.navy900} numberOfLines={1} style={styles.cardTitle}>{category.name}</MayushText>
                      </View>
                    </TouchableOpacity>
                  );
                })}
              </View>
            ))}
          </View>
        )}
      </ScrollView>
      <BottomTabBar activeTab={activeTab} onTabPress={navigate} cartBadgeCount={2} />
    </View>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: colors.surface.creamLight },
  content: { paddingHorizontal: 20, paddingTop: 18, paddingBottom: 28, gap: 20 },
  header: { height: 76, position: 'relative', alignItems: 'center', justifyContent: 'center', paddingHorizontal: 2 },
  rowReverse: { flexDirection: 'row-reverse' },
  headerButton: { position: 'absolute', right: 2, width: 42, height: 42, alignItems: 'center', justifyContent: 'center' },
  headerButtonRtl: { right: undefined, left: 2 },
  searchRow: { flexDirection: 'row', alignItems: 'center', gap: 11 },
  filterButton: { width: 56, height: 56, borderWidth: 1.5, borderColor: colors.surface.borderWarm, backgroundColor: colors.surface.white, borderRadius: 28, alignItems: 'center', justifyContent: 'center' },
  grid: { gap: 12, marginHorizontal: -8 },
  gridRow: { flexDirection: 'row', justifyContent: 'space-between', gap: 12 },
  card: { width: '31.2%', maxWidth: '31.2%', minWidth: 0, overflow: 'hidden', borderRadius: 14, borderWidth: 1, borderColor: colors.surface.borderWarm, backgroundColor: colors.surface.white, shadowColor: '#101D35', shadowOpacity: 0.04, shadowRadius: 6, shadowOffset: { width: 0, height: 2 }, elevation: 1 },
  cardImage: { width: '100%', aspectRatio: 0.96 },
  cardFooter: { minHeight: 48, flexDirection: 'row', alignItems: 'center', gap: 7, paddingHorizontal: 9, paddingVertical: 8 },
  cardTitle: { flex: 1, minWidth: 0, fontWeight: '600', fontSize: 12, lineHeight: 16 },
});
