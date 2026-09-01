/**
 * SearchFilterSheet
 * Modal-based filter bottom sheet for search results.
 * Supports sort options, category filter, brand filter, and price range.
 * Bilingual: French / Arabic.
 */

import React, { useCallback, useEffect, useState } from 'react';
import {
  ActivityIndicator,
  Modal,
  ScrollView,
  StyleSheet,
  TextInput,
  TouchableOpacity,
  View,
} from 'react-native';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { colors } from '../../design-system/tokens/colors';
import { radii } from '../../design-system/tokens/radii';
import { catalogService, SearchFilterOptionDto } from '../../services/api/catalogService';

// ─── Types ───────────────────────────────────────────────────────────────────

export interface SearchFilterSheetProps {
  visible: boolean;
  language: 'fr' | 'ar';
  currentFilters: {
    category?: string;
    brand?: string;
    min?: number;
    max?: number;
    sortKey?: string;
  };
  onApply: (filters: {
    category?: string;
    brand?: string;
    min?: number;
    max?: number;
    sortKey?: string;
  }) => void;
  onClose: () => void;
}

// ─── Sort options ─────────────────────────────────────────────────────────────

interface SortOption {
  key: string;
  labelFr: string;
  labelAr: string;
}

const SORT_OPTIONS: SortOption[] = [
  { key: 'price_low',  labelFr: 'Prix croissant',   labelAr: 'السعر تصاعدي' },
  { key: 'price_high', labelFr: 'Prix décroissant',  labelAr: 'السعر تنازلي' },
  { key: 'new',        labelFr: 'Nouveautés',         labelAr: 'الأحدث' },
  { key: 'popular',   labelFr: 'Popularité',          labelAr: 'الأكثر شعبية' },
];

// ─── Labels ───────────────────────────────────────────────────────────────────

const t = (fr: string, ar: string, language: 'fr' | 'ar') =>
  language === 'ar' ? ar : fr;

// ─── Component ────────────────────────────────────────────────────────────────

export const SearchFilterSheet: React.FC<SearchFilterSheetProps> = ({
  visible,
  language,
  currentFilters,
  onApply,
  onClose,
}) => {
  const [categories, setCategories] = useState<SearchFilterOptionDto[]>([]);
  const [brands, setBrands] = useState<SearchFilterOptionDto[]>([]);
  const [loadingFilters, setLoadingFilters] = useState(false);

  // Local draft state (not committed until Apply)
  const [draftCategory, setDraftCategory] = useState<string | undefined>(currentFilters.category);
  const [draftBrand, setDraftBrand] = useState<string | undefined>(currentFilters.brand);
  const [draftMin, setDraftMin] = useState<string>(
    currentFilters.min != null ? String(currentFilters.min) : ''
  );
  const [draftMax, setDraftMax] = useState<string>(
    currentFilters.max != null ? String(currentFilters.max) : ''
  );
  const [draftSortKey, setDraftSortKey] = useState<string | undefined>(currentFilters.sortKey);

  // Sync draft with incoming props when sheet opens
  useEffect(() => {
    if (visible) {
      setDraftCategory(currentFilters.category);
      setDraftBrand(currentFilters.brand);
      setDraftMin(currentFilters.min != null ? String(currentFilters.min) : '');
      setDraftMax(currentFilters.max != null ? String(currentFilters.max) : '');
      setDraftSortKey(currentFilters.sortKey);
    }
  }, [visible, currentFilters.category, currentFilters.brand, currentFilters.min, currentFilters.max, currentFilters.sortKey]);

  const loadFilters = useCallback(async () => {
    setLoadingFilters(true);
    try {
      const [cats, brnds] = await Promise.all([
        catalogService.getCategoryFilters(language),
        catalogService.getBrandFilters(language),
      ]);
      setCategories(cats);
      setBrands(brnds);
    } catch {
      // silently fail — chips just won't appear
    } finally {
      setLoadingFilters(false);
    }
  }, [language]);

  useEffect(() => {
    if (visible) {
      loadFilters();
    }
  }, [visible, loadFilters]);

  const handleApply = () => {
    onApply({
      category: draftCategory,
      brand: draftBrand,
      min: draftMin !== '' ? Number(draftMin) : undefined,
      max: draftMax !== '' ? Number(draftMax) : undefined,
      sortKey: draftSortKey,
    });
  };

  const handleReset = () => {
    setDraftCategory(undefined);
    setDraftBrand(undefined);
    setDraftMin('');
    setDraftMax('');
    setDraftSortKey(undefined);
  };

  return (
    <Modal
      visible={visible}
      transparent
      animationType="slide"
      onRequestClose={onClose}
    >
      <View style={styles.backdrop}>
        <View style={styles.sheet}>
          {/* Header */}
          <View style={styles.sheetHeader}>
            <MayushText variant="sectionTitle" color={colors.brand.navy900}>
              {t('Filtres & Tri', 'الفلاتر والترتيب', language)}
            </MayushText>
            <TouchableOpacity onPress={onClose} style={styles.closeBtn} accessibilityRole="button">
              <MayushIcon name="x" size={22} color={colors.neutral.gray700} />
            </TouchableOpacity>
          </View>

          <ScrollView showsVerticalScrollIndicator={false} contentContainerStyle={styles.scrollContent}>
            {loadingFilters ? (
              <View style={styles.loadingContainer}>
                <ActivityIndicator size="large" color={colors.brand.orange500} />
              </View>
            ) : (
              <>
                {/* ── Sort ────────────────────────────────────────────── */}
                <View style={styles.section}>
                  <MayushText variant="body" color={colors.brand.navy900} style={styles.sectionLabel}>
                    {t('Trier par', 'ترتيب حسب', language)}
                  </MayushText>
                  <View style={styles.chipRow}>
                    {SORT_OPTIONS.map((opt) => {
                      const active = draftSortKey === opt.key;
                      return (
                        <TouchableOpacity
                          key={opt.key}
                          style={[styles.chip, active && styles.chipActive]}
                          onPress={() => setDraftSortKey(active ? undefined : opt.key)}
                          accessibilityRole="button"
                        >
                          <MayushText
                            variant="smallBody"
                            color={active ? colors.surface.white : colors.brand.navy900}
                          >
                            {language === 'ar' ? opt.labelAr : opt.labelFr}
                          </MayushText>
                        </TouchableOpacity>
                      );
                    })}
                  </View>
                </View>

                {/* ── Categories ──────────────────────────────────────── */}
                {categories.length > 0 && (
                  <View style={styles.section}>
                    <MayushText variant="body" color={colors.brand.navy900} style={styles.sectionLabel}>
                      {t('Catégorie', 'الفئة', language)}
                    </MayushText>
                    <View style={styles.chipRow}>
                      {categories.map((cat) => {
                        const key = cat.slug ?? String(cat.id);
                        const active = draftCategory === key;
                        return (
                          <TouchableOpacity
                            key={cat.id}
                            style={[styles.chip, active && styles.chipActive]}
                            onPress={() => setDraftCategory(active ? undefined : key)}
                            accessibilityRole="button"
                          >
                            <MayushText
                              variant="smallBody"
                              color={active ? colors.surface.white : colors.brand.navy900}
                            >
                              {cat.name}
                            </MayushText>
                          </TouchableOpacity>
                        );
                      })}
                    </View>
                  </View>
                )}

                {/* ── Brands ──────────────────────────────────────────── */}
                {brands.length > 0 && (
                  <View style={styles.section}>
                    <MayushText variant="body" color={colors.brand.navy900} style={styles.sectionLabel}>
                      {t('Marque', 'العلامة التجارية', language)}
                    </MayushText>
                    <View style={styles.chipRow}>
                      {brands.map((brand) => {
                        const key = brand.slug ?? String(brand.id);
                        const active = draftBrand === key;
                        return (
                          <TouchableOpacity
                            key={brand.id}
                            style={[styles.chip, active && styles.chipActive]}
                            onPress={() => setDraftBrand(active ? undefined : key)}
                            accessibilityRole="button"
                          >
                            <MayushText
                              variant="smallBody"
                              color={active ? colors.surface.white : colors.brand.navy900}
                            >
                              {brand.name}
                            </MayushText>
                          </TouchableOpacity>
                        );
                      })}
                    </View>
                  </View>
                )}

                {/* ── Price range ─────────────────────────────────────── */}
                <View style={styles.section}>
                  <MayushText variant="body" color={colors.brand.navy900} style={styles.sectionLabel}>
                    {t('Prix (MAD)', 'السعر (درهم)', language)}
                  </MayushText>
                  <View style={styles.priceRow}>
                    <View style={styles.priceInputWrapper}>
                      <MayushText variant="caption" color={colors.neutral.gray500} style={styles.priceInputLabel}>
                        {t('Min', 'الحد الأدنى', language)}
                      </MayushText>
                      <TextInput
                        style={styles.priceInput}
                        value={draftMin}
                        onChangeText={setDraftMin}
                        keyboardType="numeric"
                        placeholder="0"
                        placeholderTextColor={colors.neutral.gray500}
                      />
                    </View>
                    <View style={styles.priceDivider} />
                    <View style={styles.priceInputWrapper}>
                      <MayushText variant="caption" color={colors.neutral.gray500} style={styles.priceInputLabel}>
                        {t('Max', 'الحد الأقصى', language)}
                      </MayushText>
                      <TextInput
                        style={styles.priceInput}
                        value={draftMax}
                        onChangeText={setDraftMax}
                        keyboardType="numeric"
                        placeholder="99999"
                        placeholderTextColor={colors.neutral.gray500}
                      />
                    </View>
                  </View>
                </View>
              </>
            )}
          </ScrollView>

          {/* Footer buttons */}
          <View style={styles.footer}>
            <TouchableOpacity style={styles.resetBtn} onPress={handleReset} accessibilityRole="button">
              <MayushText variant="button" color={colors.brand.navy900}>
                {t('Réinitialiser', 'إعادة تعيين', language)}
              </MayushText>
            </TouchableOpacity>
            <TouchableOpacity style={styles.applyBtn} onPress={handleApply} accessibilityRole="button">
              <MayushText variant="button" color={colors.surface.white}>
                {t('Appliquer', 'تطبيق', language)}
              </MayushText>
            </TouchableOpacity>
          </View>
        </View>
      </View>
    </Modal>
  );
};

// ─── Styles ───────────────────────────────────────────────────────────────────

const styles = StyleSheet.create({
  backdrop: {
    flex: 1,
    backgroundColor: 'rgba(0,0,0,0.45)',
    justifyContent: 'flex-end',
  },
  sheet: {
    backgroundColor: colors.surface.white,
    borderTopLeftRadius: radii.lg,
    borderTopRightRadius: radii.lg,
    maxHeight: '85%',
  },
  sheetHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: 20,
    paddingVertical: 16,
    borderBottomWidth: 1,
    borderBottomColor: colors.surface.borderWarm,
  },
  closeBtn: {
    width: 34,
    height: 34,
    alignItems: 'center',
    justifyContent: 'center',
  },
  scrollContent: {
    paddingHorizontal: 20,
    paddingBottom: 8,
  },
  loadingContainer: {
    paddingVertical: 48,
    alignItems: 'center',
  },
  section: {
    marginTop: 20,
  },
  sectionLabel: {
    marginBottom: 10,
    fontWeight: '600',
  },
  chipRow: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 8,
  },
  chip: {
    paddingHorizontal: 14,
    paddingVertical: 7,
    borderRadius: radii.full,
    borderWidth: 1,
    borderColor: colors.surface.borderWarm,
    backgroundColor: colors.surface.creamLight,
  },
  chipActive: {
    backgroundColor: colors.brand.orange500,
    borderColor: colors.brand.orange500,
  },
  priceRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
  },
  priceInputWrapper: {
    flex: 1,
  },
  priceInputLabel: {
    marginBottom: 4,
  },
  priceInput: {
    borderWidth: 1,
    borderColor: colors.surface.borderWarm,
    borderRadius: radii.md,
    paddingHorizontal: 12,
    paddingVertical: 10,
    fontSize: 14,
    color: colors.brand.navy900,
    backgroundColor: colors.surface.creamLight,
  },
  priceDivider: {
    width: 16,
    height: 1,
    backgroundColor: colors.surface.borderWarm,
    marginTop: 20,
  },
  footer: {
    flexDirection: 'row',
    gap: 12,
    padding: 16,
    borderTopWidth: 1,
    borderTopColor: colors.surface.borderWarm,
  },
  resetBtn: {
    flex: 1,
    paddingVertical: 14,
    borderRadius: radii.md,
    borderWidth: 1.5,
    borderColor: colors.brand.navy900,
    alignItems: 'center',
    justifyContent: 'center',
  },
  applyBtn: {
    flex: 2,
    paddingVertical: 14,
    borderRadius: radii.md,
    backgroundColor: colors.brand.orange500,
    alignItems: 'center',
    justifyContent: 'center',
  },
});
