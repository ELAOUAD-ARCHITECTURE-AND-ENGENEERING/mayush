/**
 * ProductSpecificationsScreen (Figma Node 309:608 - 03-product-specifications-table)
 * Structured product technical specs table, dimensions, materials, and warranty information.
 */

import React from 'react';
import {
  ScrollView,
  StyleSheet,
  TouchableOpacity,
  View,
} from 'react-native';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { useTheme } from '../../design-system/theme/useTheme';
import { colors } from '../../design-system/tokens/colors';
import { radii } from '../../design-system/tokens/radii';

export interface ProductSpecificationRow {
  label: string;
  value: string;
}

export interface ProductSpecificationsScreenProps {
  productTitle?: string;
  customSpecs?: ProductSpecificationRow[];
  onBack: () => void;
}

export const ProductSpecificationsScreen: React.FC<ProductSpecificationsScreenProps> = ({
  productTitle = 'Fauteuil Lounge Luna',
  customSpecs,
  onBack,
}) => {
  const { isRTL, language } = useTheme();

  const defaultSpecs: ProductSpecificationRow[] = [
    { label: language === 'ar' ? 'العرض' : 'Largeur totale', value: '85 cm' },
    { label: language === 'ar' ? 'الارتفاع' : 'Hauteur totale', value: '78 cm' },
    { label: language === 'ar' ? 'العمق' : 'Profondeur totale', value: '82 cm' },
    { label: language === 'ar' ? 'ارتفاع المقعد' : 'Hauteur d’assise', value: '42 cm' },
    { label: language === 'ar' ? 'الوزن' : 'Poids net', value: '18,5 kg' },
    { label: language === 'ar' ? 'الهيكل' : 'Structure interne', value: language === 'ar' ? 'خشب خشب الزان الصلب' : 'Bois de hêtre massif' },
    { label: language === 'ar' ? 'القماش' : 'Revêtement', value: language === 'ar' ? 'قماش بوقلي كريمي' : 'Tissu bouclé 100% Polyester' },
    { label: language === 'ar' ? 'بلد الصنع' : 'Origine de fabrication', value: language === 'ar' ? 'المغرب (صنع يدوي)' : 'Maroc (Fait main)' },
    { label: language === 'ar' ? 'الضمان' : 'Garantie', value: language === 'ar' ? 'سنتان (24 شهرًا)' : '2 ans (24 mois)' },
  ];

  const specs = customSpecs && customSpecs.length > 0 ? customSpecs : defaultSpecs;

  return (
    <View style={styles.screen} accessibilityLabel="Product Specifications Screen">
      <View style={styles.header}>
        <TouchableOpacity accessibilityRole="button" onPress={onBack} style={styles.iconBtn}>
          <MayushIcon name={isRTL ? 'arrow-right' : 'arrow-left'} size={24} color={colors.brand.navy900} />
        </TouchableOpacity>
        <MayushText variant="sectionTitle" color={colors.brand.navy900} style={styles.headerTitle}>
          {language === 'ar' ? 'المواصفات التقنية' : 'Fiche technique'}
        </MayushText>
        <View style={styles.iconBtn} />
      </View>

      <ScrollView contentContainerStyle={styles.content} showsVerticalScrollIndicator={false}>
        <MayushText variant="pageTitle" color={colors.brand.navy900} style={styles.title}>
          {productTitle}
        </MayushText>

        <View style={styles.tableCard}>
          {specs.map((row, idx) => (
            <View
              key={row.label}
              style={[
                styles.tableRow,
                idx % 2 === 1 && styles.tableRowAlt,
                idx === specs.length - 1 && styles.tableRowLast,
              ]}
            >
              <MayushText variant="smallBody" color={colors.neutral.gray700} style={styles.labelCol}>
                {row.label}
              </MayushText>
              <MayushText variant="strongBody" color={colors.brand.navy900} style={styles.valueCol}>
                {row.value}
              </MayushText>
            </View>
          ))}
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
  content: { padding: 20 },
  title: { fontSize: 20, marginBottom: 16 },
  tableCard: {
    borderRadius: radii.xl,
    overflow: 'hidden',
    borderWidth: 1,
    borderColor: colors.surface.borderWarm,
    backgroundColor: colors.surface.white,
  },
  tableRow: {
    flexDirection: 'row',
    paddingVertical: 12,
    paddingHorizontal: 16,
    borderBottomWidth: 1,
    borderBottomColor: colors.surface.borderWarm,
  },
  tableRowAlt: { backgroundColor: colors.surface.creamLight },
  tableRowLast: { borderBottomWidth: 0 },
  labelCol: { flex: 1 },
  valueCol: { flex: 1.2, textAlign: 'right' },
});
