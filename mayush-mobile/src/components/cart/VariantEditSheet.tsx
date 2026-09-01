/** Cart-owned variant editor for Figma node 309:660. */
import React, { useEffect, useMemo, useState } from 'react';
import { Modal, StyleSheet, TouchableOpacity, View } from 'react-native';
import { CartLine, CartVariantSelection, formatMadPrice } from '../../commerce/cartState';
import { PrimaryButton } from '../../design-system/components/actions/PrimaryButton';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { useTheme } from '../../design-system/theme/useTheme';
import { colors } from '../../design-system/tokens/colors';
import { radii } from '../../design-system/tokens/radii';

export interface VariantEditSheetProps {
  visible: boolean;
  line: CartLine | null;
  onClose: () => void;
  onConfirm: (lineId: string, selection: CartVariantSelection) => void;
}

export const VariantEditSheet: React.FC<VariantEditSheetProps> = ({ visible, line, onClose, onConfirm }) => {
  const { isRTL, language } = useTheme();
  const options = useMemo(() => line?.variantOptions || (line ? [{
    variantId: line.variantId || line.variant,
    label: line.selectedVariantText || line.variant,
    unitPriceMad: line.unitPriceMad,
  }] : []), [line]);
  const [selectedVariantId, setSelectedVariantId] = useState('');
  const [selectedColor, setSelectedColor] = useState('Beige');
  const [selectedMaterial, setSelectedMaterial] = useState('Bouclé');
  const [quantity, setQuantity] = useState(1);

  useEffect(() => {
    if (!line) return;
    setSelectedVariantId(line.variantId || options[0]?.variantId || '');
    setQuantity(line.quantity);
  }, [line, options]);

  if (!line) return null;
  const selectedOption = options.find((option) => option.variantId === selectedVariantId) || options[0];
  const priceDifference = selectedOption ? selectedOption.unitPriceMad - line.unitPriceMad : 0;
  const direction = isRTL ? styles.rowReverse : styles.row;
  const colorOptions = ['Beige', 'Gris clair', 'Terracotta', 'Vert sauge', 'Bleu nuit'];
  const materialOptions = ['Bouclé', 'Velours'];

  const save = () => {
    if (!selectedOption) return;
    onConfirm(line.id, { ...selectedOption, quantity });
    onClose();
  };

  return (
    <Modal visible={visible} animationType="slide" transparent onRequestClose={onClose}>
      <View style={styles.overlay}>
        <View style={styles.sheet} accessibilityLabel="Modify Variant Bottom Sheet">
          <View style={styles.handle} />
          <View style={[styles.header, direction]}>
            <MayushText variant="sectionTitle" color={colors.brand.navy900} align={isRTL ? 'right' : 'left'}>
              {language === 'ar' ? 'تعديل الخيار' : 'Modifier la variante'}
            </MayushText>
            <TouchableOpacity onPress={onClose} accessibilityRole="button"><MayushIcon name="x" size={22} color={colors.brand.navy900} /></TouchableOpacity>
          </View>

          <View style={[styles.productRow, direction]}>
            <View style={styles.productCopy}>
              <MayushText variant="strongBody" color={colors.brand.navy900} align={isRTL ? 'right' : 'left'}>{line.productName || line.name}</MayushText>
              <MayushText variant="body" color={colors.brand.orange500} align={isRTL ? 'right' : 'left'}>{formatMadPrice(selectedOption?.unitPriceMad || line.unitPriceMad)}</MayushText>
            </View>
          </View>

          <ChoiceSection title={language === 'ar' ? 'اللون' : 'Couleur'} values={colorOptions} selected={selectedColor} onSelect={setSelectedColor} isRTL={isRTL} />
          <ChoiceSection title={language === 'ar' ? 'الخامة' : 'Matière'} values={materialOptions} selected={selectedMaterial} onSelect={setSelectedMaterial} isRTL={isRTL} />

          <View style={styles.section}>
            <MayushText variant="strongBody" color={colors.brand.navy900} align={isRTL ? 'right' : 'left'}>{language === 'ar' ? 'المقاس' : 'Dimensions'}</MayushText>
            {options.map((option) => {
              const selected = option.variantId === selectedOption?.variantId;
              return (
                <TouchableOpacity key={option.variantId} style={[styles.optionRow, direction, selected && styles.optionSelected]} onPress={() => setSelectedVariantId(option.variantId)}>
                  <View style={[styles.radio, selected && styles.radioSelected]}>{selected ? <View style={styles.radioDot} /> : null}</View>
                  <MayushText variant="smallBody" color={colors.brand.navy900} style={styles.optionLabel}>{option.label}</MayushText>
                  <MayushText variant="strongBody" color={colors.brand.orange500}>{formatMadPrice(option.unitPriceMad)}</MayushText>
                </TouchableOpacity>
              );
            })}
          </View>

          <View style={[styles.quantityRow, direction]}>
            <MayushText variant="strongBody" color={colors.brand.navy900}>{language === 'ar' ? 'الكمية' : 'Quantité'}</MayushText>
            <View style={[styles.stepper, direction]}>
              <TouchableOpacity onPress={() => setQuantity((value) => Math.max(1, value - 1))} style={styles.stepButton}><MayushText variant="strongBody" color={colors.brand.navy900}>−</MayushText></TouchableOpacity>
              <MayushText variant="strongBody" color={colors.brand.navy900}>{quantity}</MayushText>
              <TouchableOpacity onPress={() => setQuantity((value) => Math.min(line.maxQuantity || 10, value + 1))} style={styles.stepButton}><MayushText variant="strongBody" color={colors.brand.navy900}>+</MayushText></TouchableOpacity>
            </View>
          </View>
          <MayushText variant="caption" color={colors.semantic.success} align={isRTL ? 'right' : 'left'}>{language === 'ar' ? 'متوفر — التوصيل خلال 3 إلى 5 أيام' : 'En stock — Livraison estimée sous 3 à 5 jours'}</MayushText>
          <View style={[styles.priceDifference, direction]}>
            <MayushText variant="smallBody" color={colors.neutral.gray700}>{language === 'ar' ? 'فرق السعر' : 'Différence de prix'}</MayushText>
            <MayushText variant="strongBody" color={colors.brand.navy900}>{priceDifference >= 0 ? '+' : '−'}{formatMadPrice(Math.abs(priceDifference))}</MayushText>
          </View>
          <PrimaryButton label={`${language === 'ar' ? 'تحديث المنتج' : 'Mettre à jour l’article'} · ${formatMadPrice((selectedOption?.unitPriceMad || line.unitPriceMad) * quantity)}`} onPress={save} />
        </View>
      </View>
    </Modal>
  );
};

const ChoiceSection = ({ title, values, selected, onSelect, isRTL }: { title: string; values: string[]; selected: string; onSelect: (value: string) => void; isRTL: boolean }) => (
  <View style={styles.section}>
    <MayushText variant="strongBody" color={colors.brand.navy900} align={isRTL ? 'right' : 'left'}>{title}</MayushText>
    <View style={[styles.chips, isRTL && styles.rowReverse]}>{values.map((value) => <TouchableOpacity key={value} style={[styles.chip, selected === value && styles.chipSelected]} onPress={() => onSelect(value)}><MayushText variant="caption" color={selected === value ? colors.brand.orange500 : colors.brand.navy900}>{value}</MayushText></TouchableOpacity>)}</View>
  </View>
);

const styles = StyleSheet.create({
  overlay: { flex: 1, backgroundColor: 'rgba(0,0,0,0.5)', justifyContent: 'flex-end' },
  sheet: { backgroundColor: colors.surface.white, borderTopLeftRadius: radii.xl, borderTopRightRadius: radii.xl, padding: 20, paddingBottom: 28, gap: 14 },
  handle: { width: 44, height: 4, borderRadius: 2, backgroundColor: colors.neutral.gray300, alignSelf: 'center' },
  row: { flexDirection: 'row' }, rowReverse: { flexDirection: 'row-reverse' },
  header: { justifyContent: 'space-between', alignItems: 'center' },
  productRow: { alignItems: 'center' }, productCopy: { flex: 1, gap: 3 },
  section: { gap: 8 }, chips: { flexDirection: 'row', flexWrap: 'wrap', gap: 7 },
  chip: { paddingHorizontal: 11, paddingVertical: 7, borderRadius: radii.full, borderWidth: 1, borderColor: colors.surface.borderWarm, backgroundColor: colors.surface.creamLight },
  chipSelected: { borderColor: colors.brand.orange500, backgroundColor: colors.brand.orange100 },
  optionRow: { alignItems: 'center', gap: 9, padding: 10, borderWidth: 1, borderColor: colors.surface.borderWarm, borderRadius: radii.lg },
  optionSelected: { borderColor: colors.brand.orange500, backgroundColor: colors.brand.orange100 },
  radio: { width: 18, height: 18, borderRadius: 9, borderWidth: 1, borderColor: colors.neutral.gray500, alignItems: 'center', justifyContent: 'center' },
  radioSelected: { borderColor: colors.brand.orange500 }, radioDot: { width: 9, height: 9, borderRadius: 5, backgroundColor: colors.brand.orange500 },
  optionLabel: { flex: 1 }, quantityRow: { justifyContent: 'space-between', alignItems: 'center' },
  stepper: { alignItems: 'center', gap: 16, borderWidth: 1, borderColor: colors.surface.borderWarm, borderRadius: radii.full, paddingHorizontal: 6 },
  stepButton: { width: 32, height: 32, alignItems: 'center', justifyContent: 'center' },
  priceDifference: { justifyContent: 'space-between', paddingTop: 10, borderTopWidth: 1, borderTopColor: colors.surface.borderWarm },
});
