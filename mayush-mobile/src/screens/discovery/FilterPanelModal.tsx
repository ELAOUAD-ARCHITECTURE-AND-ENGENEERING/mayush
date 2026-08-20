/**
 * FilterPanelModal (Figma Node 309:596 - 02-filter-panel-category-price-color-material)
 * Modal sheet for filtering products by price, colors, materials, and availability.
 */

import React, { useState } from 'react';
import {
  Modal,
  ScrollView,
  StyleSheet,
  Switch,
  TouchableOpacity,
  View,
} from 'react-native';
import { PrimaryButton } from '../../design-system/components/actions/PrimaryButton';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { useTheme } from '../../design-system/theme/useTheme';
import { colors } from '../../design-system/tokens/colors';
import { radii } from '../../design-system/tokens/radii';

export interface FilterPanelModalProps {
  visible: boolean;
  onClose: () => void;
  onApplyFilters?: (filters: { colors: string[]; materials: string[]; inStockOnly: boolean }) => void;
  availableColors?: string[];
  availableMaterials?: string[];
}

export const FilterPanelModal: React.FC<FilterPanelModalProps> = ({
  visible,
  onClose,
  onApplyFilters,
  availableColors,
  availableMaterials,
}) => {
  const { language } = useTheme();
  const [inStockOnly, setInStockOnly] = useState(true);
  const [selectedColors, setSelectedColors] = useState<string[]>([]);
  const [selectedMaterials, setSelectedMaterials] = useState<string[]>([]);

  // Use provided options or fall back to hardcoded defaults
  const colorOptions = availableColors || ['Beige', 'Vert Sauge', 'Moka', 'Noir', 'Terracotta'];
  const materialOptions = availableMaterials || ['Bouclé', 'Velours', 'Bois Massif', 'Métal'];

  const handleReset = () => {
    setInStockOnly(false);
    setSelectedColors([]);
    setSelectedMaterials([]);
    // Call onApplyFilters with empty/default values
    onApplyFilters?.({ colors: [], materials: [], inStockOnly: false });
  };

  return (
    <Modal visible={visible} animationType="slide" transparent onRequestClose={onClose}>
      <View style={styles.overlay}>
        <View style={styles.sheetContainer}>
          <View style={styles.sheetHeader}>
            <TouchableOpacity onPress={onClose} style={styles.iconBtn}>
              <MayushIcon name="x" size={22} color={colors.brand.navy900} />
            </TouchableOpacity>
            <MayushText variant="sectionTitle" color={colors.brand.navy900} style={styles.sheetTitle}>
              {language === 'ar' ? 'التصفية' : 'Filtres'}
            </MayushText>
            <TouchableOpacity onPress={handleReset}>
              <MayushText variant="caption" color={colors.brand.orange500}>
                {language === 'ar' ? 'إعادة ضبط' : 'Réinitialiser'}
              </MayushText>
            </TouchableOpacity>
          </View>

          <ScrollView contentContainerStyle={styles.sheetBody} showsVerticalScrollIndicator={false}>
            <View style={styles.filterSection}>
              <MayushText variant="strongBody" color={colors.brand.navy900} style={styles.filterTitle}>
                {language === 'ar' ? 'اللون' : 'Couleur'}
              </MayushText>
              <View style={styles.chipRow}>
                {colorOptions.map((c) => {
                  const selected = selectedColors.includes(c);
                  return (
                    <TouchableOpacity
                      key={c}
                      style={[styles.chip, selected && styles.chipSelected]}
                      onPress={() => {
                        setSelectedColors(
                          selected ? selectedColors.filter((col) => col !== c) : [...selectedColors, c]
                        );
                      }}
                    >
                      <MayushText variant="caption" color={selected ? colors.brand.orange500 : colors.brand.navy900}>
                        {c}
                      </MayushText>
                    </TouchableOpacity>
                  );
                })}
              </View>
            </View>

            <View style={styles.filterSection}>
              <MayushText variant="strongBody" color={colors.brand.navy900} style={styles.filterTitle}>
                {language === 'ar' ? 'المادة' : 'Matière'}
              </MayushText>
              <View style={styles.chipRow}>
                {materialOptions.map((m) => {
                  const selected = selectedMaterials.includes(m);
                  return (
                    <TouchableOpacity
                      key={m}
                      style={[styles.chip, selected && styles.chipSelected]}
                      onPress={() => {
                        setSelectedMaterials(
                          selected ? selectedMaterials.filter((mat) => mat !== m) : [...selectedMaterials, m]
                        );
                      }}
                    >
                      <MayushText variant="caption" color={selected ? colors.brand.orange500 : colors.brand.navy900}>
                        {m}
                      </MayushText>
                    </TouchableOpacity>
                  );
                })}
              </View>
            </View>

            <View style={styles.switchRow}>
              <MayushText variant="strongBody" color={colors.brand.navy900}>
                {language === 'ar' ? 'المتوفر في المخزون فقط' : 'En stock uniquement'}
              </MayushText>
              <Switch
                value={inStockOnly}
                onValueChange={setInStockOnly}
                trackColor={{ false: '#E8E8E8', true: '#F8E6D7' }}
                thumbColor={inStockOnly ? colors.brand.orange500 : colors.surface.white}
              />
            </View>
          </ScrollView>

          <View style={styles.sheetFooter}>
            <PrimaryButton
              label={language === 'ar' ? 'تطبيق الفلاتر' : 'Appliquer les filtres'}
              onPress={() => {
                onApplyFilters?.({ colors: selectedColors, materials: selectedMaterials, inStockOnly });
                onClose();
              }}
            />
          </View>
        </View>
      </View>
    </Modal>
  );
};

const styles = StyleSheet.create({
  overlay: { flex: 1, backgroundColor: 'rgba(0,0,0,0.5)', justifyContent: 'flex-end' },
  sheetContainer: {
    maxHeight: '80%',
    backgroundColor: colors.surface.white,
    borderTopLeftRadius: radii.xl,
    borderTopRightRadius: radii.xl,
    paddingBottom: 24,
  },
  sheetHeader: {
    height: 60,
    paddingHorizontal: 20,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    borderBottomWidth: 1,
    borderBottomColor: colors.surface.borderWarm,
  },
  iconBtn: { width: 36, height: 36, alignItems: 'center', justifyContent: 'center' },
  sheetTitle: { fontSize: 18, fontWeight: '700' },
  sheetBody: { padding: 20 },
  filterSection: { marginBottom: 20 },
  filterTitle: { fontSize: 15, marginBottom: 10 },
  chipRow: { flexDirection: 'row', flexWrap: 'wrap', gap: 8 },
  chip: {
    paddingHorizontal: 14,
    paddingVertical: 8,
    borderRadius: radii.full,
    backgroundColor: colors.surface.creamLight,
    borderWidth: 1,
    borderColor: colors.surface.borderWarm,
  },
  chipSelected: {
    backgroundColor: colors.brand.orange100,
    borderColor: colors.brand.orange500,
  },
  switchRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingVertical: 12,
    borderTopWidth: 1,
    borderTopColor: colors.surface.borderWarm,
  },
  sheetFooter: { paddingHorizontal: 20, paddingTop: 12 },
});
