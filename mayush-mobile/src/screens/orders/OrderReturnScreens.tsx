import React, { useState } from 'react';
import { Image, ScrollView, StyleSheet, TextInput, TouchableOpacity, View } from 'react-native';
import { formatMadPrice } from '../../commerce/cartState';
import {
  ReturnReasonKey,
  ReturnRequestDraft,
  ReturnRequestRecord,
  ReturnTrackingEvent,
  canReturnLine,
} from '../../commerce/orderActionState';
import { BuyerOrder } from '../../commerce/orderState';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { useTheme } from '../../design-system/theme/useTheme';
import { colors } from '../../design-system/tokens/colors';
import { getOrderLineImage, OrderActionButton, OrderCard, OrderScreenHeader } from './OrderScreenComponents';

const RETURN_REASONS: ReturnReasonKey[] = ['damaged_on_delivery', 'not_as_described', 'size_or_dimensions', 'changed_mind'];

export const OrderReturnSelectionScreen: React.FC<{
  order: BuyerOrder;
  draft: ReturnRequestDraft;
  onBack: () => void;
  onSelect: (lineId: string, selected: boolean) => void;
  onQuantity: (lineId: string, quantity: number) => void;
  onReason: (reason: ReturnReasonKey) => void;
  onMessage: (message: string) => void;
  onSubmit: () => Promise<boolean>;
}> = ({ order, draft, onBack, onSelect, onQuantity, onReason, onMessage, onSubmit }) => {
  const { language, isRTL } = useTheme();
  const [showError, setShowError] = useState(false);
  const copy = language === 'ar' ? {
    title: 'طلب إرجاع', subtitle: 'اختر المنتجات المراد إرجاعها وحدد السبب.', items: 'منتجات الطلب', quantity: 'الكمية المراد إرجاعها', max: 'الحد الأقصى', reason: 'سبب الإرجاع', note: 'ملاحظة (اختيارية)', placeholder: 'أضف تفاصيل مفيدة لمعالجة طلبك.', policy: 'سياسة الإرجاع', policyText: 'هذه محاكاة واجهة محلية. لم يتم إرسال أي طلب إلى خادم أو ناقل.', submit: 'إرسال الطلب', required: 'اختر منتجاً واحداً على الأقل وسبب الإرجاع.', delivered: 'تم التوصيل', orderNumber: 'رقم الطلب', chars: 'حرفاً',
  } : {
    title: 'Demander un retour', subtitle: 'Sélectionnez les articles à retourner et indiquez le motif.', items: 'Articles de la commande', quantity: 'Quantité à retourner', max: 'max.', reason: 'Motif du retour', note: 'Note (facultatif)', placeholder: 'Ajoutez des détails supplémentaires pour nous aider à traiter votre demande.', policy: 'Politique de retour', policyText: 'Simulation frontend locale : aucune demande n’est envoyée à un serveur ou à un transporteur.', submit: 'Envoyer la demande', required: 'Sélectionnez au moins un article et un motif de retour.', delivered: 'Livrée', orderNumber: 'Numéro de commande', chars: '',
  };
  const reasons = language === 'ar' ? {
    damaged_on_delivery: 'المنتج تالف عند التسليم', not_as_described: 'لا يطابق الوصف', size_or_dimensions: 'المقاس / الأبعاد', changed_mind: 'غيرت رأيي',
  } : {
    damaged_on_delivery: 'Produit endommagé', not_as_described: 'Ne correspond pas à la description', size_or_dimensions: 'Taille / dimensions', changed_mind: 'Changement d’avis',
  };
  const valid = draft.selectedLines.length > 0 && Boolean(draft.reasonKey);
  const submit = async () => { const accepted = await onSubmit(); setShowError(!accepted); };
  return <OrderCanvas>
    <OrderScreenHeader onBack={onBack} title={copy.title} subtitle={copy.subtitle} />
    <ScrollView contentContainerStyle={styles.content} showsVerticalScrollIndicator={false}>
      <OrderCard><Meta order={order} status={copy.delivered} orderLabel={copy.orderNumber} isRTL={isRTL} /></OrderCard>
      <OrderCard>
        <SectionHeader icon="shopping-bag" title={copy.items} count={`${order.lines.filter((line) => canReturnLine(order, line)).length}`} isRTL={isRTL} />
        {order.lines.filter((line) => canReturnLine(order, line)).map((line) => {
          const selectedLine = draft.selectedLines.find((candidate) => candidate.orderLineId === line.orderLineId);
          return <View key={line.orderLineId} style={[styles.itemRow, isRTL && styles.rowReverse]}>
            <TouchableOpacity accessibilityRole="checkbox" accessibilityState={{ checked: Boolean(selectedLine) }} onPress={() => { onSelect(line.orderLineId, !selectedLine); setShowError(false); }} style={[styles.checkbox, selectedLine && styles.checkboxSelected]}>{selectedLine ? <MayushIcon name="check" size={15} color={colors.surface.white} /> : null}</TouchableOpacity>
            <Image source={getOrderLineImage(line)} style={styles.productImage} />
            <View style={styles.flex}><MayushText variant="strongBody" color={colors.brand.navy900} align={isRTL ? 'right' : 'left'}>{line.name}</MayushText><MayushText variant="caption" color={colors.brand.navy900} align={isRTL ? 'right' : 'left'}>{formatMadPrice(line.unitPriceMad)}</MayushText></View>
            <View style={styles.quantityBox}><MayushText variant="caption" color={colors.neutral.gray700} align="center">{copy.quantity}</MayushText><View style={styles.stepper}><TouchableOpacity disabled={!selectedLine || selectedLine.quantity <= 1} onPress={() => selectedLine && onQuantity(line.orderLineId, selectedLine.quantity - 1)}><MayushIcon name="minus" size={16} color={colors.brand.navy900} /></TouchableOpacity><MayushText variant="strongBody" color={colors.brand.navy900}>{selectedLine?.quantity ?? 0}</MayushText><TouchableOpacity disabled={!selectedLine || selectedLine.quantity >= line.quantity} onPress={() => selectedLine && onQuantity(line.orderLineId, selectedLine.quantity + 1)}><MayushIcon name="plus" size={16} color={colors.brand.navy900} /></TouchableOpacity></View><MayushText variant="caption" color={colors.neutral.gray500} align="center">{copy.max} {line.quantity}</MayushText></View>
          </View>;
        })}
      </OrderCard>
      <OrderCard><SectionHeader icon="alert-triangle" title={copy.reason} isRTL={isRTL} /><View style={styles.reasonGrid}>{RETURN_REASONS.map((key) => <TouchableOpacity key={key} accessibilityRole="radio" accessibilityState={{ checked: draft.reasonKey === key }} onPress={() => { onReason(key); setShowError(false); }} style={[styles.reason, draft.reasonKey === key && styles.reasonSelected, isRTL && styles.rowReverse]}><View style={[styles.radio, draft.reasonKey === key && styles.radioSelected]}>{draft.reasonKey === key ? <View style={styles.radioDot} /> : null}</View><MayushText variant="caption" color={draft.reasonKey === key ? colors.brand.orange500 : colors.brand.navy900} style={styles.flex}>{reasons[key]}</MayushText></TouchableOpacity>)}</View>{showError ? <MayushText variant="caption" color={colors.semantic.error}>{copy.required}</MayushText> : null}</OrderCard>
      <OrderCard><SectionHeader icon="edit-2" title={copy.note} isRTL={isRTL} /><TextInput multiline maxLength={300} value={draft.message} onChangeText={onMessage} placeholder={copy.placeholder} placeholderTextColor={colors.neutral.gray500} textAlign={isRTL ? 'right' : 'left'} style={styles.input} /><MayushText variant="caption" color={colors.neutral.gray500} align={isRTL ? 'left' : 'right'}>{draft.message.length}/300 {copy.chars}</MayushText></OrderCard>
      <InfoCard title={copy.policy} text={copy.policyText} isRTL={isRTL} />
      <OrderActionButton label={copy.submit} icon="send" onPress={() => { void submit(); }} primary disabled={!valid} />
    </ScrollView>
  </OrderCanvas>;
};

export const OrderReturnDetailScreen: React.FC<{
  order: BuyerOrder;
  request: ReturnRequestRecord;
  onBack: () => void;
  onTrack: () => void;
  onSupport: () => void;
}> = ({ order, request, onBack, onTrack, onSupport }) => {
  const { language, isRTL } = useTheme();
  const copy = language === 'ar' ? { title: 'تفاصيل الإرجاع', subtitle: 'اطّلع على معلومات طلب الإرجاع وحالته.', returned: 'المنتجات المرتجعة', status: 'قيد الفحص', reason: 'سبب الإرجاع', method: 'طريقة الاسترداد', original: 'استرداد على وسيلة الدفع الأصلية', amount: 'مبلغ الاسترداد المقدر', track: 'تتبع الطلب', support: 'اتصل بالدعم', local: 'مرجع محلي', items: 'منتجات' } : { title: 'Détails du retour', subtitle: 'Consultez les informations et le statut de votre demande de retour et remboursement.', returned: 'Articles retournés', status: 'En cours d’examen', reason: 'Raison du retour', method: 'Méthode de remboursement', original: 'Remboursement sur le moyen de paiement initial', amount: 'Montant du remboursement estimé', track: 'Suivre la demande', support: 'Contacter le support', local: 'Référence frontend locale', items: 'articles' };
  const lines = request.selectedLines.map((selected) => ({ selected, line: order.lines.find((line) => line.orderLineId === selected.orderLineId) })).filter((entry) => entry.line);
  return <OrderCanvas><OrderScreenHeader onBack={onBack} title={copy.title} subtitle={copy.subtitle} /><ScrollView contentContainerStyle={styles.content} showsVerticalScrollIndicator={false}>
    <OrderCard><View style={[styles.summaryGrid, isRTL && styles.rowReverse]}><Summary label={copy.local} value={request.returnRequestId} /><Summary label={language === 'ar' ? 'الطلب المرتبط' : 'Commande associée'} value={order.orderId} /><Summary label={language === 'ar' ? 'الحالة' : 'Statut'} value={copy.status} accent /></View></OrderCard>
    <OrderCard><SectionHeader icon="box" title={copy.returned} count={`${lines.length} ${copy.items}`} isRTL={isRTL} />{lines.map(({ selected, line }) => <View key={selected.orderLineId} style={[styles.detailLine, isRTL && styles.rowReverse]}><Image source={getOrderLineImage(line!)} style={styles.detailImage} /><View style={styles.flex}><MayushText variant="strongBody" color={colors.brand.navy900} align={isRTL ? 'right' : 'left'}>{line!.name}</MayushText><MayushText variant="caption" color={colors.neutral.gray700}>{language === 'ar' ? 'الكمية المرتجعة' : 'Quantité retournée'} : {selected.quantity}</MayushText></View><MayushText variant="strongBody" color={colors.brand.orange500}>{formatMadPrice(selected.requestedUnitPriceMad * selected.quantity)}</MayushText></View>)}</OrderCard>
    <DetailCard icon="help-circle" title={copy.reason} value={request.selectedLines[0]?.reasonKey.replaceAll('_', ' ') ?? ''} isRTL={isRTL} />
    <DetailCard icon="credit-card" title={copy.method} value={copy.original} isRTL={isRTL} />
    <OrderCard><View style={[styles.amountRow, isRTL && styles.rowReverse]}><MayushIcon name="wallet" size={24} color={colors.brand.orange500} /><MayushText variant="body" color={colors.brand.navy900} style={styles.flex}>{copy.amount}</MayushText><MayushText variant="sectionTitle" color={colors.brand.orange500}>{formatMadPrice(request.requestedRefundAmountMad)}</MayushText></View></OrderCard>
    <OrderActionButton label={copy.track} icon="truck-outline" onPress={onTrack} primary /><OrderActionButton label={copy.support} icon="headphones" onPress={onSupport} />
  </ScrollView></OrderCanvas>;
};

export const OrderReturnTrackingScreen: React.FC<{
  order: BuyerOrder;
  request: ReturnRequestRecord;
  onBack: () => void;
  onDetails: () => void;
  onSupport: () => void;
}> = ({ order, request, onBack, onDetails, onSupport }) => {
  const { language, isRTL } = useTheme();
  const copy = language === 'ar' ? { title: 'تتبع الإرجاع', subtitle: 'تابع تقدم الإرجاع وحالة الاسترداد.', current: 'استرداد قيد المعالجة', estimate: 'تاريخ الاسترداد التقديري', estimateValue: 'الأربعاء 28 مايو 2026', support: 'اتصل بالدعم', details: 'عرض تفاصيل الإرجاع' } : { title: 'Suivi du retour', subtitle: 'Consultez l’avancement de votre retour et le statut de votre remboursement.', current: 'Remboursement en cours', estimate: 'Date estimée du remboursement', estimateValue: 'Mercredi 28 mai 2026', support: 'Contacter le support', details: 'Voir les détails du retour' };
  return <OrderCanvas><OrderScreenHeader onBack={onBack} title={copy.title} subtitle={copy.subtitle} /><ScrollView contentContainerStyle={styles.content} showsVerticalScrollIndicator={false}>
    <OrderCard><View style={[styles.summaryGrid, isRTL && styles.rowReverse]}><Summary label={language === 'ar' ? 'رقم الإرجاع' : 'Numéro de retour'} value={request.returnRequestId} /><Summary label={language === 'ar' ? 'الطلب المرتبط' : 'Commande liée'} value={order.orderId} /><Summary label={language === 'ar' ? 'الحالة الحالية' : 'Statut actuel'} value={copy.current} success /></View></OrderCard>
    <OrderCard>{request.trackingEvents.map((event, index) => <TimelineRow key={event.returnTrackingEventId} event={event} isLast={index === request.trackingEvents.length - 1} language={language} isRTL={isRTL} />)}</OrderCard>
    <View style={[styles.estimate, isRTL && styles.rowReverse]}><MayushIcon name="calendar" size={29} color={colors.brand.orange500} /><View style={styles.flex}><MayushText variant="strongBody" color={colors.brand.navy900} align={isRTL ? 'right' : 'left'}>{copy.estimate}</MayushText><MayushText variant="sectionTitle" color={colors.brand.orange500} align={isRTL ? 'right' : 'left'}>{copy.estimateValue}</MayushText><MayushText variant="caption" color={colors.neutral.gray700}>{language === 'ar' ? 'تاريخ محاكاة محلي، وليس وعداً بنكياً.' : 'Estimation locale du prototype, sans promesse bancaire.'}</MayushText></View></View>
    <OrderActionButton label={copy.support} icon="headphones" onPress={onSupport} /><OrderActionButton label={copy.details} icon="file-text" onPress={onDetails} primary />
  </ScrollView></OrderCanvas>;
};

const TimelineRow: React.FC<{ event: ReturnTrackingEvent; isLast: boolean; language: 'fr' | 'ar'; isRTL: boolean }> = ({ event, isLast, language, isRTL }) => {
  const labels = language === 'ar' ? { request_created: 'تم إرسال الطلب', approved: 'تمت الموافقة على الإرجاع', parcel_received: 'تم استلام الطرد', inspection: 'فحص الجودة', refund_processing: 'بدأ الاسترداد', refunded: 'تم الاسترداد' } : { request_created: 'Demande envoyée', approved: 'Retour approuvé', parcel_received: 'Colis reçu', inspection: 'Contrôle qualité', refund_processing: 'Remboursement initié', refunded: 'Remboursé' };
  const active = event.state !== 'upcoming';
  return <View style={[styles.timelineRow, isRTL && styles.rowReverse]}><View style={styles.timelineRail}><View style={[styles.timelineDot, active && styles.timelineDotActive, event.state === 'current' && styles.timelineDotCurrent]}>{event.state === 'completed' ? <MayushIcon name="check" size={12} color={colors.semantic.success} /> : null}</View>{!isLast ? <View style={[styles.timelineLine, active && styles.timelineLineActive]} /> : null}</View><View style={styles.flex}><MayushText variant="strongBody" color={event.state === 'current' ? colors.brand.orange500 : colors.brand.navy900} align={isRTL ? 'right' : 'left'}>{labels[event.labelKey]}</MayushText><MayushText variant="caption" color={colors.neutral.gray700} align={isRTL ? 'right' : 'left'}>{event.state === 'completed' ? (language === 'ar' ? 'مكتمل' : 'Terminé') : event.state === 'current' ? (language === 'ar' ? 'قيد التنفيذ' : 'En cours') : (language === 'ar' ? 'قيد الانتظار' : 'En attente')}</MayushText></View></View>;
};

const OrderCanvas: React.FC<{ children: React.ReactNode }> = ({ children }) => <View style={styles.screen}><View style={styles.canvas}>{children}</View></View>;
const SectionHeader: React.FC<{ icon: 'shopping-bag' | 'alert-triangle' | 'edit-2' | 'box'; title: string; count?: string; isRTL: boolean }> = ({ icon, title, count, isRTL }) => <View style={[styles.sectionHeader, isRTL && styles.rowReverse]}><View style={[styles.sectionTitle, isRTL && styles.rowReverse]}><MayushIcon name={icon} size={20} color={colors.brand.orange500} /><MayushText variant="strongBody" color={colors.brand.navy900}>{title}</MayushText></View>{count ? <MayushText variant="caption" color={colors.neutral.gray700}>{count}</MayushText> : null}</View>;
const Meta: React.FC<{ order: BuyerOrder; status: string; orderLabel: string; isRTL: boolean }> = ({ order, status, orderLabel, isRTL }) => <View style={[styles.meta, isRTL && styles.rowReverse]}><View style={styles.metaIcon}><MayushIcon name="clipboard" size={21} color={colors.brand.orange500} /></View><View style={styles.flex}><MayushText variant="caption" color={colors.neutral.gray700}>{orderLabel.toUpperCase()}</MayushText><MayushText variant="strongBody" color={colors.brand.navy900}>{order.orderId}</MayushText><MayushText variant="caption" color={colors.neutral.gray700}>{order.deliveredAt}</MayushText></View><View><MayushText variant="caption" color={colors.neutral.gray700}>{isRTL ? 'الحالة' : 'STATUT DE LIVRAISON'}</MayushText><MayushText variant="strongBody" color={colors.semantic.success}>{status}</MayushText></View></View>;
const Summary: React.FC<{ label: string; value: string; accent?: boolean; success?: boolean }> = ({ label, value, accent, success }) => <View style={styles.summary}><MayushText variant="caption" color={colors.neutral.gray700}>{label.toUpperCase()}</MayushText><MayushText variant="strongBody" color={success ? colors.semantic.success : accent ? colors.brand.orange500 : colors.brand.navy900}>{value}</MayushText></View>;
const DetailCard: React.FC<{ icon: 'help-circle' | 'credit-card'; title: string; value: string; isRTL: boolean }> = ({ icon, title, value, isRTL }) => <OrderCard><View style={[styles.detailCard, isRTL && styles.rowReverse]}><View style={styles.metaIcon}><MayushIcon name={icon} size={21} color={colors.brand.orange500} /></View><View style={styles.flex}><MayushText variant="strongBody" color={colors.brand.navy900} align={isRTL ? 'right' : 'left'}>{title}</MayushText><MayushText variant="body" color={colors.neutral.gray700} align={isRTL ? 'right' : 'left'}>{value}</MayushText></View></View></OrderCard>;
const InfoCard: React.FC<{ title: string; text: string; isRTL: boolean }> = ({ title, text, isRTL }) => <View style={[styles.info, isRTL && styles.rowReverse]}><MayushIcon name="shield-check" size={24} color={colors.brand.orange500} /><View style={styles.flex}><MayushText variant="strongBody" color={colors.brand.orange500} align={isRTL ? 'right' : 'left'}>{title}</MayushText><MayushText variant="caption" color={colors.neutral.gray700} align={isRTL ? 'right' : 'left'}>{text}</MayushText></View></View>;

const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: '#FFFDF9' }, canvas: { flex: 1, width: '100%', maxWidth: 393, alignSelf: 'center' }, content: { padding: 14, paddingBottom: 30, gap: 8 }, flex: { flex: 1 }, rowReverse: { flexDirection: 'row-reverse' },
  meta: { minHeight: 58, flexDirection: 'row', alignItems: 'center', gap: 9 }, metaIcon: { width: 38, height: 38, borderRadius: 19, alignItems: 'center', justifyContent: 'center', backgroundColor: '#FFF3E7' }, sectionHeader: { minHeight: 32, flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' }, sectionTitle: { flexDirection: 'row', alignItems: 'center', gap: 7 },
  itemRow: { minHeight: 88, paddingVertical: 7, flexDirection: 'row', alignItems: 'center', gap: 8, borderTopWidth: 1, borderTopColor: colors.surface.borderWarm }, checkbox: { width: 22, height: 22, borderRadius: 4, borderWidth: 1, borderColor: colors.neutral.gray500, alignItems: 'center', justifyContent: 'center' }, checkboxSelected: { backgroundColor: colors.brand.orange500, borderColor: colors.brand.orange500 }, productImage: { width: 64, height: 58, borderRadius: 7, backgroundColor: colors.surface.cream }, quantityBox: { width: 92, gap: 2 }, stepper: { height: 27, borderWidth: 1, borderColor: colors.surface.borderWarm, borderRadius: 6, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-around' },
  reasonGrid: { flexDirection: 'row', flexWrap: 'wrap', gap: 7 }, reason: { width: '48.8%', minHeight: 42, padding: 7, borderRadius: 7, borderWidth: 1, borderColor: colors.surface.borderWarm, flexDirection: 'row', alignItems: 'center', gap: 7 }, reasonSelected: { borderColor: colors.brand.orange500, backgroundColor: '#FFF5EC' }, radio: { width: 17, height: 17, borderRadius: 9, borderWidth: 1, borderColor: colors.neutral.gray500, alignItems: 'center', justifyContent: 'center' }, radioSelected: { borderColor: colors.brand.orange500 }, radioDot: { width: 7, height: 7, borderRadius: 4, backgroundColor: colors.brand.orange500 }, input: { minHeight: 64, padding: 9, borderRadius: 7, borderWidth: 1, borderColor: colors.surface.borderWarm, color: colors.brand.navy900, textAlignVertical: 'top' }, info: { padding: 11, borderRadius: 10, borderWidth: 1, borderColor: colors.brand.orange100, backgroundColor: '#FFF8F0', flexDirection: 'row', gap: 9 },
  summaryGrid: { flexDirection: 'row', gap: 8 }, summary: { flex: 1, minWidth: 0 }, detailLine: { minHeight: 76, borderTopWidth: 1, borderTopColor: colors.surface.borderWarm, paddingVertical: 7, flexDirection: 'row', alignItems: 'center', gap: 9 }, detailImage: { width: 92, height: 66, borderRadius: 7 }, detailCard: { flexDirection: 'row', alignItems: 'center', gap: 9 }, amountRow: { minHeight: 45, flexDirection: 'row', alignItems: 'center', gap: 8 },
  timelineRow: { minHeight: 62, flexDirection: 'row', gap: 11 }, timelineRail: { width: 24, alignItems: 'center' }, timelineDot: { width: 19, height: 19, borderRadius: 10, borderWidth: 2, borderColor: colors.neutral.gray500, backgroundColor: colors.surface.white, alignItems: 'center', justifyContent: 'center' }, timelineDotActive: { borderColor: colors.semantic.success }, timelineDotCurrent: { borderColor: colors.brand.orange500 }, timelineLine: { flex: 1, width: 1.5, backgroundColor: colors.neutral.gray300 }, timelineLineActive: { backgroundColor: colors.semantic.success }, estimate: { padding: 13, borderRadius: 11, borderWidth: 1, borderColor: colors.brand.orange100, backgroundColor: '#FFF8F0', flexDirection: 'row', alignItems: 'center', gap: 10 },
});
