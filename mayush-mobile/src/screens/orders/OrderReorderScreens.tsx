import React from 'react';
import { Image, ScrollView, StyleSheet, TouchableOpacity, View } from 'react-native';
import { formatMadPrice } from '../../commerce/cartState';
import { ReorderCartResult, ReorderPlan, ReorderPlanLine } from '../../commerce/orderActionState';
import { BuyerOrder } from '../../commerce/orderState';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { useTheme } from '../../design-system/theme/useTheme';
import { colors } from '../../design-system/tokens/colors';
import { getOrderLineImage, OrderActionButton, OrderCard, OrderScreenHeader } from './OrderScreenComponents';

interface ReorderBaseProps {
  order: BuyerOrder;
  plan: ReorderPlan;
  onBack: () => void;
  onSelect: (orderLineId: string, selected: boolean) => void;
}

export const OrderReorderChangesScreen: React.FC<ReorderBaseProps & {
  onOpenSelection: () => void;
  onAddSelected: () => boolean;
}> = ({ order, plan, onBack, onSelect, onOpenSelection, onAddSelected }) => {
  const { language, isRTL } = useTheme();
  const copy = language === 'ar' ? {
    title: 'تغيّرت بعض المنتجات', subtitle: 'راجع التوفر والأسعار والخيارات الحالية قبل إنشاء سلة جديدة.', unavailable: 'منتج غير متوفر', changed: 'معلومات محدثة', available: 'متوفر', selection: 'يرجى مراجعة اختيارك قبل المتابعة.', update: 'تحديث الاختيار', continue: 'إضافة المنتجات المختارة',
  } : {
    title: 'Certains articles ont changé', subtitle: 'Vérifiez la disponibilité, les prix et variantes actuels avant de créer un nouveau panier.', unavailable: 'Produit indisponible', changed: 'Informations mises à jour', available: 'Disponible', selection: 'Veuillez revoir votre sélection avant de continuer.', update: 'Mettre à jour la sélection', continue: 'Ajouter les articles sélectionnés',
  };
  return (
    <ReorderScreen title={copy.title} subtitle={copy.subtitle} onBack={onBack}>
      {(['unavailable', 'changed', 'available'] as const).map((state) => {
        const lines = plan.lines.filter((line) => line.state === state);
        if (!lines.length) return null;
        return <OrderCard key={state}><GroupHeader state={state} title={copy[state]} count={lines.length} isRTL={isRTL} />{lines.map((line) => <ReorderLineRow key={line.orderLineId} order={order} line={line} isRTL={isRTL} language={language} onToggle={() => onSelect(line.orderLineId, !line.selected)} />)}</OrderCard>;
      })}
      <Notice text={copy.selection} isRTL={isRTL} />
      <OrderActionButton label={copy.update} icon="sliders-horizontal" onPress={onOpenSelection} primary />
      <OrderActionButton label={copy.continue} icon="shopping-cart" onPress={onAddSelected} disabled={!plan.lines.some((line) => line.selected && line.state !== 'unavailable')} />
    </ReorderScreen>
  );
};

export const OrderReorderAvailabilityScreen: React.FC<ReorderBaseProps & {
  onAddSelected: () => boolean;
  onOpenCart: () => void;
}> = ({ order, plan, onBack, onSelect, onAddSelected, onOpenCart }) => {
  const { language, isRTL } = useTheme();
  const copy = language === 'ar' ? {
    title: 'الطلب من جديد', subtitle: 'اختر المنتجات المتاحة لإضافتها إلى سلة جديدة.', available: 'المنتجات الحالية', warning: 'تظل بيانات الطلب التاريخية دون تغيير. الأسعار والخيارات أدناه تخص السلة الجديدة فقط.', subtotal: 'المجموع الفرعي الحالي', add: 'إضافة المنتجات المتاحة', cart: 'عرض سلتي',
  } : {
    title: 'Commander à nouveau', subtitle: 'Sélectionnez les articles actuellement disponibles à ajouter à un nouveau panier.', available: 'Articles actuels', warning: 'La commande historique reste inchangée. Les prix et variantes ci-dessous concernent uniquement le nouveau panier.', subtotal: 'Sous-total actuel', add: 'Ajouter les articles disponibles', cart: 'Voir mon panier',
  };
  const selectedSubtotal = plan.lines.filter((line) => line.selected && line.state !== 'unavailable').reduce((sum, line) => sum + line.currentUnitPriceMad * line.currentQuantity, 0);
  return (
    <ReorderScreen title={copy.title} subtitle={copy.subtitle} onBack={onBack}>
      <OrderCard>
        <View style={[styles.orderMeta, isRTL && styles.rowReverse]}><View style={styles.roundIcon}><MayushIcon name="clipboard" size={20} color={colors.brand.orange500} /></View><View style={styles.flex}><MayushText variant="strongBody" color={colors.brand.navy900} align={isRTL ? 'right' : 'left'}>{order.orderId}</MayushText><MayushText variant="caption" color={colors.semantic.success} align={isRTL ? 'right' : 'left'}>{language === 'ar' ? 'تم التسليم' : 'Terminée'}</MayushText></View></View>
      </OrderCard>
      <OrderCard>
        <GroupHeader state="available" title={copy.available} count={plan.lines.filter((line) => line.state !== 'unavailable').length} isRTL={isRTL} />
        {plan.lines.map((line) => <ReorderLineRow key={line.orderLineId} order={order} line={line} isRTL={isRTL} language={language} onToggle={() => onSelect(line.orderLineId, !line.selected)} />)}
        <View style={styles.total}><MayushText variant="strongBody" color={colors.brand.navy900}>{copy.subtotal}</MayushText><MayushText variant="sectionTitle" color={colors.brand.orange500}>{formatMadPrice(selectedSubtotal)}</MayushText></View>
      </OrderCard>
      <Notice text={copy.warning} isRTL={isRTL} />
      <OrderActionButton label={copy.add} icon="shopping-cart" onPress={onAddSelected} primary disabled={selectedSubtotal <= 0} />
      <OrderActionButton label={copy.cart} icon="shopping-bag" onPress={onOpenCart} />
    </ReorderScreen>
  );
};

export const OrderReorderAddedScreen: React.FC<{
  order: BuyerOrder;
  result: ReorderCartResult;
  onBack: () => void;
  onOpenCart: () => void;
  onContinueShopping: () => void;
}> = ({ order, result, onBack, onOpenCart, onContinueShopping }) => {
  const { language, isRTL } = useTheme();
  const copy = language === 'ar' ? {
    title: 'تمت إضافة المنتجات إلى السلة', subtitle: 'تمت إضافة المنتجات الحالية المتاحة بنجاح.', added: 'المنتجات المضافة', ignored: 'المنتجات غير المضافة', subtotal: 'المجموع المضاف إلى السلة', cart: 'عرض سلتي', shop: 'متابعة التسوق', boundary: 'تم تحديث السلة الحالية فقط؛ لم يتغير الطلب التاريخي.',
  } : {
    title: 'Articles ajoutés au panier', subtitle: 'Les articles actuellement disponibles ont été ajoutés avec succès.', added: 'Articles disponibles ajoutés', ignored: 'Articles indisponibles ou non sélectionnés', subtotal: 'Sous-total ajouté au panier', cart: 'Voir mon panier', shop: 'Continuer mes achats', boundary: 'Seul le panier actuel a été modifié ; la commande historique reste inchangée.',
  };
  return (
    <ReorderScreen title={copy.title} subtitle={copy.subtitle} onBack={onBack}>
      <View style={styles.successHero}><MayushIcon name="check" size={54} color={colors.surface.white} /></View>
      <OrderCard><View style={[styles.orderMeta, isRTL && styles.rowReverse]}><View style={styles.roundIcon}><MayushIcon name="clipboard" size={20} color={colors.brand.orange500} /></View><View style={styles.flex}><MayushText variant="caption" color={colors.neutral.gray700} align={isRTL ? 'right' : 'left'}>{language === 'ar' ? 'مرجع الطلب' : 'Référence de la commande'}</MayushText><MayushText variant="strongBody" color={colors.brand.navy900} align={isRTL ? 'right' : 'left'}>{order.orderId}</MayushText></View></View></OrderCard>
      <OrderCard>
        <ResultRow icon="check-circle" label={copy.added} value={`${result.addedLineIds.length}`} success isRTL={isRTL} />
        <ResultRow icon="minus" label={copy.ignored} value={`${result.ignoredLineIds.length}`} isRTL={isRTL} />
        <ResultRow icon="wallet" label={copy.subtotal} value={formatMadPrice(result.addedSubtotalMad)} accent isRTL={isRTL} />
      </OrderCard>
      <Notice text={copy.boundary} isRTL={isRTL} />
      <OrderActionButton label={copy.cart} icon="shopping-cart" onPress={onOpenCart} primary />
      <OrderActionButton label={copy.shop} icon="shopping-bag" onPress={onContinueShopping} />
    </ReorderScreen>
  );
};

const ReorderScreen: React.FC<{ title: string; subtitle: string; onBack: () => void; children: React.ReactNode }> = ({ title, subtitle, onBack, children }) => <View style={styles.screen}><View style={styles.canvas}><OrderScreenHeader onBack={onBack} title={title} subtitle={subtitle} /><ScrollView contentContainerStyle={styles.content} showsVerticalScrollIndicator={false}>{children}</ScrollView></View></View>;
const GroupHeader: React.FC<{ state: ReorderPlanLine['state']; title: string; count: number; isRTL: boolean }> = ({ state, title, count, isRTL }) => <View style={[styles.groupHeader, isRTL && styles.rowReverse]}><View style={[styles.groupTitle, isRTL && styles.rowReverse]}><MayushIcon name={state === 'unavailable' ? 'alert-triangle' : state === 'changed' ? 'tag' : 'check-circle'} size={21} color={state === 'available' ? colors.semantic.success : colors.brand.orange500} /><MayushText variant="strongBody" color={colors.brand.navy900}>{title}</MayushText></View><View style={styles.count}><MayushText variant="caption" color={colors.neutral.gray700}>{count}</MayushText></View></View>;
const ReorderLineRow: React.FC<{ order: BuyerOrder; line: ReorderPlanLine; isRTL: boolean; language: 'fr' | 'ar'; onToggle: () => void }> = ({ order, line, isRTL, language, onToggle }) => {
  const sourceLine = order.lines.find((item) => item.orderLineId === line.orderLineId)!;
  const stateLabel = language === 'ar' ? { available: 'متوفر', changed: 'متغير', unavailable: 'غير متوفر' }[line.state] : { available: 'Disponible', changed: 'Modifié', unavailable: 'Indisponible' }[line.state];
  return <View style={[styles.line, isRTL && styles.rowReverse]}><Image source={getOrderLineImage(sourceLine)} style={styles.image} /><View style={styles.lineCopy}><MayushText variant="strongBody" color={colors.brand.navy900} align={isRTL ? 'right' : 'left'}>{line.name}</MayushText><MayushText variant="caption" color={line.state === 'unavailable' ? colors.brand.orange500 : colors.neutral.gray700} align={isRTL ? 'right' : 'left'}>{stateLabel}</MayushText>{line.state !== 'unavailable' ? <><MayushText variant="caption" color={colors.neutral.gray700} align={isRTL ? 'right' : 'left'}>{line.currentVariantLabel}</MayushText><MayushText variant="strongBody" color={colors.brand.orange500} align={isRTL ? 'right' : 'left'}>{formatMadPrice(line.currentUnitPriceMad)}</MayushText>{line.changes.includes('price') ? <MayushText variant="caption" color={colors.neutral.gray500} align={isRTL ? 'right' : 'left'} style={styles.oldPrice}>{formatMadPrice(line.historicalUnitPriceMad)}</MayushText> : null}</> : null}</View><TouchableOpacity accessibilityRole="checkbox" accessibilityState={{ checked: line.selected, disabled: line.state === 'unavailable' }} disabled={line.state === 'unavailable'} onPress={onToggle} style={[styles.checkbox, line.selected && styles.checkboxSelected, line.state === 'unavailable' && styles.checkboxDisabled]}>{line.selected ? <MayushIcon name="check" size={15} color={colors.surface.white} /> : line.state === 'unavailable' ? <MayushIcon name="x" size={14} color={colors.semantic.error} /> : null}</TouchableOpacity></View>;
};
const Notice: React.FC<{ text: string; isRTL: boolean }> = ({ text, isRTL }) => <View style={[styles.notice, isRTL && styles.rowReverse]}><MayushIcon name="info" size={22} color={colors.brand.orange500} /><MayushText variant="body" color={colors.brand.navy900} align={isRTL ? 'right' : 'left'} style={styles.flex}>{text}</MayushText></View>;
const ResultRow: React.FC<{ icon: 'check-circle' | 'minus' | 'wallet'; label: string; value: string; isRTL: boolean; success?: boolean; accent?: boolean }> = ({ icon, label, value, isRTL, success, accent }) => <View style={[styles.resultRow, isRTL && styles.rowReverse]}><MayushIcon name={icon} size={21} color={success ? colors.semantic.success : colors.brand.orange500} /><MayushText variant="body" color={colors.brand.navy900} style={styles.flex}>{label}</MayushText><MayushText variant="strongBody" color={success ? colors.semantic.success : accent ? colors.brand.orange500 : colors.brand.navy900}>{value}</MayushText></View>;

const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: '#FFFDF9' }, canvas: { flex: 1, width: '100%', maxWidth: 393, alignSelf: 'center' }, content: { padding: 14, paddingBottom: 30, gap: 9 }, flex: { flex: 1 }, rowReverse: { flexDirection: 'row-reverse' },
  groupHeader: { minHeight: 35, marginBottom: 6, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' }, groupTitle: { flexDirection: 'row', alignItems: 'center', gap: 7 }, count: { minWidth: 26, height: 24, borderRadius: 7, alignItems: 'center', justifyContent: 'center', backgroundColor: '#FFF3E7' },
  line: { minHeight: 91, paddingVertical: 8, borderBottomWidth: 1, borderBottomColor: colors.surface.borderWarm, flexDirection: 'row', alignItems: 'center', gap: 9 }, image: { width: 78, height: 70, borderRadius: 8, backgroundColor: colors.surface.cream }, lineCopy: { flex: 1, gap: 2 },
  checkbox: { width: 24, height: 24, borderRadius: 6, borderWidth: 1, borderColor: colors.brand.navy900, alignItems: 'center', justifyContent: 'center' }, checkboxSelected: { borderColor: colors.brand.orange500, backgroundColor: colors.brand.orange500 }, checkboxDisabled: { borderColor: colors.semantic.error, backgroundColor: colors.semantic.errorBackground }, oldPrice: { textDecorationLine: 'line-through' },
  notice: { borderWidth: 1, borderColor: colors.brand.orange100, borderRadius: 11, padding: 12, backgroundColor: '#FFF8F0', flexDirection: 'row', alignItems: 'center', gap: 9 }, orderMeta: { minHeight: 49, flexDirection: 'row', alignItems: 'center', gap: 9 }, roundIcon: { width: 40, height: 40, borderRadius: 20, alignItems: 'center', justifyContent: 'center', backgroundColor: '#FFF3E7' },
  total: { minHeight: 45, marginTop: 8, paddingTop: 9, borderTopWidth: 1, borderTopColor: colors.surface.borderWarm, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' }, successHero: { width: 116, height: 116, borderRadius: 58, alignSelf: 'center', alignItems: 'center', justifyContent: 'center', backgroundColor: colors.brand.orange500 },
  resultRow: { minHeight: 46, borderBottomWidth: 1, borderBottomColor: colors.surface.borderWarm, flexDirection: 'row', alignItems: 'center', gap: 9 },
});
