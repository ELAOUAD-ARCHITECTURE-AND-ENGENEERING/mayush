/**
 * VariantEditSheet Component (Figma Node 309:660 - 05-cart-modify-variant-bottom-sheet-fr)
 * Bottom sheet modal for editing selected item color, material, or dimensions from within the cart.
 */

import React, { useState } from 'react';
import { Modal, StyleSheet, TouchableOpacity, View } from 'react-native';
import { CartLine } from '../../commerce/cartState';
import { PrimaryButton } from '../../design-system/components/actions/PrimaryButton';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { colors } from '../../design-system/tokens/colors';
import { radii } from '../../design-system/tokens/radii';

export interface VariantEditSheetProps {
  visible: boolean;
  line: CartLine | null;
  onClose: () => void;
  onConfirm: (lineId: string, newVariantText: string) => void;
}

export const VariantEditSheet: React.FC<VariantEditSheetProps> = ({
  visible,
  line,
  onClose,
  onConfirm,
}) => {
  if (!line) return null;

  const [selectedColor, setSelectedColor] = useState('Beige');
  const [selectedFabric, setSelectedFabric] = useState('Tissu Bouclé');

  const colorOptions = ['Beige', 'Vert Sauge', 'Moka', 'Terracotta'];
  const fabricOptions = ['Tissu Bouclé', 'Velours Ciselé', 'Lin Naturel'];

  const handleSave = () => {
    const nextVariantText = `${selectedFabric} · ${selectedColor}`;
    onConfirm(line.id, nextVariantText);
    onClose();
  };

  return (
    <Modal visible={visible} animationType="slide" transparent onRequestClose={onClose}>
      <View style={styles.overlay}>
        <View style={styles.sheetContainer} accessibilityLabel="Modify Variant Bottom Sheet">
          <View style={styles.sheetHeader}>
            <MayushText variant="sectionTitle" color={colors.brand.navy900}>
              Modifier la variante
            </MayushText>
            <TouchableOpacity onPress={onClose}>
              <MayushIcon name="x" size={22} color={colors.brand.navy900} />
            </TouchableOpacity>
          </View>

          <MayushText variant="strongBody" color={colors.brand.navy900} style={styles.itemTitle}>
            {line.productName}
          </MayushText>

          <View style={styles.section}>
            <MayushText variant="caption" color={colors.neutral.gray700} style={styles.label}>
              Couleur
            </MayushText>
            <View style={styles.chipRow}>
              {colorOptions.map((c) => {
                const selected = selectedColor === c;
                return (
                  <TouchableOpacity
                    key={c}
                    style={[styles.chip, selected && styles.chipSelected]}
                    onPress={() => setSelectedColor(c)}
                  >
                    <MayushText variant="caption" color={selected ? colors.brand.orange500 : colors.brand.navy900}>
                      {c}
                    </MayushText>
                  </TouchableOpacity>
                );
              })}
            </View>
          </View>

          <View style={styles.section}>
            <MayushText variant="caption" color={colors.neutral.gray700} style={styles.label}>
              Matière / Revêtement
            </MayushText>
            <View style={styles.chipRow}>
              {fabricOptions.map((f) => {
                const selected = selectedFabric === f;
                return (
                  <TouchableOpacity
                    key={f}
                    style={[styles.chip, selected && styles.chipSelected]}
                    onPress={() => setSelectedFabric(f)}
                  >
                    <MayushText variant="caption" color={selected ? colors.brand.orange500 : colors.brand.navy900}>
                      {f}
                    </MayushText>
                  </TouchableOpacity>
                );
              })}
            </View>
          </View>

          <View style={styles.footer}>
            <PrimaryButton label="Valider la modification" onPress={handleSave} />
          </View>
        </View>
      </View>
    </Modal>
  );
};

const styles = StyleSheet.create({
  overlay: { flex: 1, backgroundColor: 'rgba(0,0,0,0.5)', justifyContent: 'flex-end' },
  sheetContainer: {
    backgroundColor: colors.surface.white,
    borderTopLeftRadius: radii.xl,
    borderTopRightRadius: radii.xl,
    padding: 20,
    gap: 16,
  },
  sheetHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
  itemTitle: { fontSize: 16, fontWeight: '700' },
  section: { gap: 8 },
  label: { textTransform: 'uppercase', fontSize: 11, fontWeight: '700' },
  chipRow: { flexDirection: 'row', flexWrap: 'wrap', gap: 8 },
  chip: {
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: radii.full,
    backgroundColor: colors.surface.creamLight,
    borderWidth: 1,
    borderColor: colors.surface.borderWarm,
  },
  chipSelected: { backgroundColor: colors.brand.orange100, borderColor: colors.brand.orange500 },
  footer: { marginTop: 8 },
});
