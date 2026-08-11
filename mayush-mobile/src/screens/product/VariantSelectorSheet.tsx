import React, { useEffect, useState } from 'react';
import { Modal, View, ScrollView, StyleSheet, Image, TouchableOpacity, ActivityIndicator } from 'react-native';
import { useTheme } from '../../design-system/theme/useTheme';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { colors } from '../../design-system/tokens/colors';
import { catalogService } from '../../services/api/catalogService';
import { ProductDetailDto } from '../../contracts/api/dto';

export interface VariantSelectorSheetProps {
  visible: boolean;
  product: ProductDetailDto | null;
  onClose: () => void;
  onConfirmAddToCart?: (variant: string, qty: number) => void;
}

const materialOptions = [
  { label: 'Tissu Bouclé', subtitle: 'Premium', icon: 'sofa' as const },
  { label: 'Velours', subtitle: 'Premium', icon: 'rug' as const },
  { label: 'Cuir', subtitle: 'Premium', icon: 'package-variant-closed' as const },
];

export const VariantSelectorSheet: React.FC<VariantSelectorSheetProps> = ({ visible, product, onClose, onConfirmAddToCart }) => {
  const { language, isRTL } = useTheme();
  const [selectedColor, setSelectedColor] = useState('');
  const [selectedMaterial, setSelectedMaterial] = useState(0);
  const [selectedDimension, setSelectedDimension] = useState(0);
  const [quantity, setQuantity] = useState(1);
  const [calculating, setCalculating] = useState(false);
  const [price, setPrice] = useState('');
  const [stock, setStock] = useState(1);
  const [inStock, setInStock] = useState(true);
  const [variant, setVariant] = useState('');

  useEffect(() => {
    if (!product) return;
    setSelectedColor(product.colors?.[0] || '#EFE4D6');
    setSelectedMaterial(0);
    setSelectedDimension(0);
    setQuantity(1);
    setPrice(product.main_price);
    setStock(product.current_stock || 1);
  }, [product]);

  useEffect(() => {
    if (!product || !visible) return;
    let active = true;
    const variantText = [selectedColor, materialOptions[selectedMaterial]?.label, `L ${selectedDimension === 0 ? '78' : selectedDimension === 1 ? '88' : '98'} cm`].filter(Boolean).join('-');
    setCalculating(true);
    catalogService.getVariantPrice({ slug: String(product.id), variants: variantText, color: selectedColor.replace('#', '') }, language)
      .then((response) => {
        if (!active || !response.result || !response.data) return;
        setPrice(response.data.price);
        setStock(response.data.stock);
        setInStock(response.data.stock > 0);
        setVariant(response.data.variant || variantText);
      })
      .catch(() => {
        if (!active) return;
        setPrice(product.main_price);
        setStock(product.current_stock || 1);
        setInStock((product.current_stock || 1) > 0);
        setVariant(variantText);
      })
      .finally(() => {
        if (active) setCalculating(false);
      });
    return () => { active = false; };
  }, [product, visible, selectedColor, selectedMaterial, selectedDimension, quantity, language]);

  if (!product) return null;
  const image = product.thumbnail_img || product.photos?.[0] || 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?q=80&w=600&auto=format&fit=crop';
  const imageUri = typeof image === 'string' ? image : (image as unknown as { path: string }).path;
  const colorsToRender = product.colors?.length ? product.colors : ['#EFE4D6', '#A6A6A6', '#536B44', '#203248', '#111111'];
  const heading = (fr: string, ar: string) => isRTL ? ar : fr;

  return (
    <Modal visible={visible} transparent animationType="slide" onRequestClose={onClose}>
      <View style={styles.backdrop}>
        <TouchableOpacity style={styles.dismissArea} activeOpacity={1} onPress={onClose} />
        <View style={styles.sheet}>
          <View style={styles.handle} />
          <ScrollView showsVerticalScrollIndicator={false} contentContainerStyle={styles.content}>
            <View style={[styles.summary, isRTL && styles.rowReverse]}>
              <Image source={{ uri: imageUri }} style={styles.summaryImage} />
              <View style={{ flex: 1, gap: 3 }}>
                <MayushText variant="sectionTitle" color={colors.brand.navy900} numberOfLines={1}>{product.name}</MayushText>
                <MayushText variant="body" color={colors.neutral.gray700}>{heading('Confort raffiné, design intemporel', '\u0631\u0627\u062d\u0629 \u0641\u0627\u062e\u0631\u0629 \u0648\u062a\u0635\u0645\u064a\u0645 \u062e\u0627\u0644\u062f')}</MayushText>
                <MayushText variant="display" color={colors.brand.orange500} style={styles.summaryPrice}>{calculating ? '…' : price || product.main_price}</MayushText>
                <View style={styles.inStock}><View style={styles.stockDot} /><MayushText variant="strongBody" color="#208A30">{heading('En stock', '\u0645\u062a\u0648\u0641\u0631')}</MayushText></View>
              </View>
              <TouchableOpacity onPress={onClose} style={styles.close}><MayushIcon name="x" size={31} color={colors.brand.navy900} /></TouchableOpacity>
            </View>
            <View style={styles.rule} />

            <View style={styles.section}><View style={[styles.sectionLabel, isRTL && styles.rowReverse]}><MayushText variant="sectionTitle" color={colors.brand.navy900}>{heading('Couleur', '\u0627\u0644\u0644\u0648\u0646')}</MayushText><View style={styles.selectedColorLabel}><MayushText variant="body" color={colors.neutral.gray700}>{heading('Crème', '\u0643\u0631\u064a\u0645\u064a')}</MayushText><MayushIcon name="check" size={17} color="#208A30" /></View></View><View style={[styles.colorRow, isRTL && styles.rowReverse]}>{colorsToRender.map((item) => <TouchableOpacity key={item} onPress={() => setSelectedColor(item)} style={[styles.colorWrap, selectedColor === item && styles.selectedColor]}><View style={[styles.color, { backgroundColor: item }]}>{selectedColor === item ? <View style={styles.colorCheck}><MayushIcon name="check" size={15} color={colors.surface.white} strokeWidth={3} /></View> : null}</View></TouchableOpacity>)}</View></View>

            <View style={styles.section}><MayushText variant="sectionTitle" color={colors.brand.navy900}>{heading('Matière', '\u0627\u0644\u062e\u0627\u0645\u0629')}</MayushText><View style={[styles.choiceRow, isRTL && styles.rowReverse]}>{materialOptions.map((item, index) => <ChoiceCard key={item.label} icon={item.icon} label={item.label} subtitle={item.subtitle} active={selectedMaterial === index} onPress={() => setSelectedMaterial(index)} />)}</View></View>

            <View style={styles.section}><MayushText variant="sectionTitle" color={colors.brand.navy900}>{heading('Dimensions', '\u0627\u0644\u0623\u0628\u0639\u0627\u062f')}</MayushText><View style={[styles.choiceRow, isRTL && styles.rowReverse]}>{['L 78 x P 76 x H 78 cm', 'L 88 x P 82 x H 85 cm', 'L 98 x P 88 x H 90 cm'].map((label, index) => <ChoiceCard key={label} icon="sofa" label={label} subtitle={index === 2 ? heading('Bientôt disponible', '\u0642\u0631\u064a\u0628\u0627\u064b') : ''} active={selectedDimension === index} disabled={index === 2} onPress={() => setSelectedDimension(index)} />)}</View></View>

            <View style={[styles.quantityRow, isRTL && styles.rowReverse]}><MayushText variant="sectionTitle" color={colors.brand.navy900}>{heading('Quantité', '\u0627\u0644\u0643\u0645\u064a\u0629')}</MayushText><View style={styles.stepper}><TouchableOpacity disabled={quantity <= 1} onPress={() => setQuantity((value) => Math.max(1, value - 1))} style={styles.stepperButton}><MayushIcon name="minus" size={20} color={colors.neutral.gray700} /></TouchableOpacity><MayushText variant="sectionTitle" color={colors.brand.navy900}>{quantity}</MayushText><TouchableOpacity disabled={quantity >= stock} onPress={() => setQuantity((value) => Math.min(stock, value + 1))} style={styles.stepperButton}><MayushIcon name="plus" size={23} color={colors.brand.navy900} /></TouchableOpacity></View></View>
            <View style={styles.delivery}><MayushIcon name="truck-outline" size={28} color={colors.brand.orange500} /><MayushText variant="body" color={colors.brand.navy900}>{heading('Livraison estimée : ', '\u0627\u0644\u062a\u0648\u0635\u064a\u0644 \u0627\u0644\u0645\u0642\u062f\u0631: ')}<MayushText variant="strongBody" color={colors.brand.orange500}>{heading('24 – 27 mai', '24 – 27 \u0645\u0627\u064a')}</MayushText></MayushText></View>
            <TouchableOpacity disabled={!inStock || calculating} onPress={() => { onConfirmAddToCart?.(variant, quantity); onClose(); }} style={[styles.addButton, (!inStock || calculating) && styles.addDisabled]}>{calculating ? <ActivityIndicator color={colors.surface.white} /> : <><MayushIcon name="shopping-bag" size={28} color={colors.surface.white} /><MayushText variant="sectionTitle" color={colors.surface.white} style={{ flex: 1, textAlign: 'center' }}>{heading('Ajouter au panier', '\u0623\u0636\u0641 \u0625\u0644\u0649 \u0627\u0644\u0633\u0644\u0629')}</MayushText><MayushText variant="sectionTitle" color={colors.surface.white}>{price || product.main_price}</MayushText></>}</TouchableOpacity>
            <View style={styles.security}><MayushIcon name="shield" size={18} color={colors.neutral.gray700} /><MayushText variant="smallBody" color={colors.neutral.gray700}>{heading('Paiement sécurisé • Retour sous 14 jours', '\u062f\u0641\u0639 \u0622\u0645\u0646 \u2022 \u0625\u0631\u062c\u0627\u0639 \u062e\u0644\u0627\u0644 14 \u064a\u0648\u0645\u0627\u064b')}</MayushText></View>
          </ScrollView>
        </View>
      </View>
    </Modal>
  );
};

const ChoiceCard: React.FC<{ icon: React.ComponentProps<typeof MayushIcon>['name']; label: string; subtitle: string; active: boolean; disabled?: boolean; onPress: () => void }> = ({ icon, label, subtitle, active, disabled, onPress }) => <TouchableOpacity disabled={disabled} onPress={onPress} style={[styles.choiceCard, active && styles.choiceActive, disabled && styles.choiceDisabled]}><MayushIcon name={icon} size={29} color={active ? colors.brand.orange500 : colors.brand.navy900} /><MayushText variant="smallBody" color={disabled ? colors.neutral.gray500 : colors.brand.navy900} align="center" style={styles.choiceLabel}>{label}</MayushText>{subtitle ? <MayushText variant="caption" color={colors.neutral.gray700} align="center">{subtitle}</MayushText> : null}{active ? <View style={styles.choiceCheck}><MayushIcon name="check" size={13} color={colors.surface.white} strokeWidth={3} /></View> : null}</TouchableOpacity>;

const styles = StyleSheet.create({
  backdrop: { flex: 1, backgroundColor: 'rgba(16,29,53,0.46)', justifyContent: 'flex-end' },
  dismissArea: { flex: 1 },
  sheet: { maxHeight: '88%', backgroundColor: colors.surface.creamLight, borderTopLeftRadius: 28, borderTopRightRadius: 28, overflow: 'hidden' },
  handle: { width: 52, height: 5, borderRadius: 3, backgroundColor: '#D4D6DA', alignSelf: 'center', marginTop: 12, marginBottom: 9 },
  content: { paddingHorizontal: 20, paddingBottom: 24, gap: 19 },
  summary: { flexDirection: 'row', gap: 14, alignItems: 'flex-start' },
  rowReverse: { flexDirection: 'row-reverse' },
  summaryImage: { width: 108, height: 108, borderRadius: 12, backgroundColor: colors.surface.cream },
  summaryPrice: { fontSize: 27, lineHeight: 33, marginTop: 5 },
  inStock: { flexDirection: 'row', alignItems: 'center', gap: 8 },
  stockDot: { width: 10, height: 10, borderRadius: 5, backgroundColor: '#1AA334' },
  close: { width: 34, height: 34, alignItems: 'center', justifyContent: 'center' },
  rule: { height: 1, backgroundColor: colors.surface.borderWarm },
  section: { gap: 12 },
  sectionLabel: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' },
  selectedColorLabel: { flexDirection: 'row', alignItems: 'center', gap: 7 },
  colorRow: { flexDirection: 'row', gap: 13 },
  colorWrap: { width: 57, height: 57, padding: 4, borderRadius: 29, borderWidth: 2, borderColor: 'transparent' },
  selectedColor: { borderColor: colors.brand.orange500 },
  color: { flex: 1, borderRadius: 25, alignItems: 'center', justifyContent: 'center', borderWidth: 1, borderColor: 'rgba(0,0,0,0.06)' },
  colorCheck: { width: 24, height: 24, borderRadius: 12, backgroundColor: colors.brand.orange500, alignItems: 'center', justifyContent: 'center' },
  choiceRow: { flexDirection: 'row', gap: 10 },
  choiceCard: { minHeight: 103, flex: 1, borderWidth: 1, borderColor: '#DADCE0', borderRadius: 14, backgroundColor: colors.surface.white, alignItems: 'center', justifyContent: 'center', gap: 5, padding: 8, position: 'relative' },
  choiceActive: { borderColor: colors.brand.orange500, borderWidth: 1.8, backgroundColor: '#FFFDFC' },
  choiceDisabled: { opacity: 0.48 },
  choiceLabel: { fontWeight: '600', fontSize: 12, lineHeight: 16 },
  choiceCheck: { position: 'absolute', right: -7, top: -8, width: 24, height: 24, borderRadius: 12, backgroundColor: colors.brand.orange500, alignItems: 'center', justifyContent: 'center' },
  quantityRow: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' },
  stepper: { flexDirection: 'row', alignItems: 'center', gap: 21, borderWidth: 1, borderColor: '#DADCE0', borderRadius: 11, paddingHorizontal: 5 },
  stepperButton: { width: 38, height: 38, alignItems: 'center', justifyContent: 'center' },
  delivery: { flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 9, borderRadius: 13, backgroundColor: '#FFF0E0', paddingVertical: 13, paddingHorizontal: 10 },
  addButton: { minHeight: 62, borderRadius: 15, backgroundColor: colors.brand.orange500, flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 12, paddingHorizontal: 18 },
  addDisabled: { backgroundColor: '#F1B77D' },
  security: { flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 8 },
});
