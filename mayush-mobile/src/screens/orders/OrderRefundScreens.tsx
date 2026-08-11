import React from 'react';
import { Image, ScrollView, StyleSheet, View } from 'react-native';
import { formatMadPrice } from '../../commerce/cartState';
import { CancelledOrderRefundDraft, RefundRecord, ReturnRequestRecord } from '../../commerce/orderActionState';
import { BuyerOrder } from '../../commerce/orderState';
import { MayushIcon, MayushIconName } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { useTheme } from '../../design-system/theme/useTheme';
import { colors } from '../../design-system/tokens/colors';
import { getOrderLineImage, OrderActionButton, OrderCard, OrderScreenHeader } from './OrderScreenComponents';

export const OrderCancelledRefundRequestScreen: React.FC<{
  order: BuyerOrder;
  draft: CancelledOrderRefundDraft;
  onBack: () => void;
  onConfirm: () => Promise<boolean>;
}> = ({ order, draft, onBack, onConfirm }) => {
  const { language, isRTL } = useTheme();
  const copy = language === 'ar' ? {
    title: 'طلب استرداد', subtitle: 'سنعالج طلبك بعناية.', order: 'رقم الطلب', cancelled: 'ملغاة', item: 'المنتج المعني', payment: 'طريقة الدفع', refundable: 'المبلغ القابل للاسترداد', method: 'طريقة الاسترداد', delay: 'المدة التقديرية للاسترداد', delayValue: 'من 2 إلى 5 أيام عمل', info: 'محاكاة واجهة محلية فقط. لا يتم إرسال طلب إلى البنك أو بوابة الدفع.', confirm: 'تأكيد الطلب', card: 'بطاقة بنكية',
  } : {
    title: 'Demander un remboursement', subtitle: 'Nous traiterons votre demande avec soin.', order: 'Numéro de commande', cancelled: 'Annulée', item: 'Article concerné', payment: 'Méthode de paiement', refundable: 'Montant remboursable', method: 'Méthode de remboursement', delay: 'Délai de remboursement estimé', delayValue: '2 à 5 jours ouvrés', info: 'Simulation frontend locale : aucune demande n’est transmise à une banque ou à une passerelle de paiement.', confirm: 'Confirmer la demande', card: 'Carte bancaire',
  };
  const line = order.lines[0];
  return <Canvas><OrderScreenHeader onBack={onBack} title={copy.title} subtitle={copy.subtitle} /><ScrollView contentContainerStyle={styles.content} showsVerticalScrollIndicator={false}>
    <OrderCard><View style={[styles.orderMeta, isRTL && styles.rowReverse]}><Circle icon="clipboard" /><View style={styles.flex}><Label>{copy.order}</Label><MayushText variant="strongBody" color={colors.brand.navy900}>{order.orderId}</MayushText><MayushText variant="caption" color={colors.neutral.gray700}>{order.createdAt}</MayushText></View><View style={styles.cancelled}><MayushIcon name="x-circle" size={16} color={colors.semantic.error} /><MayushText variant="caption" color={colors.semantic.error}>{copy.cancelled}</MayushText></View></View></OrderCard>
    <OrderCard><Label>{copy.item}</Label><View style={[styles.productRow, isRTL && styles.rowReverse]}><Image source={getOrderLineImage(line)} style={styles.productImage} /><View style={styles.flex}><MayushText variant="strongBody" color={colors.brand.navy900} align={isRTL ? 'right' : 'left'}>{line.name}</MayushText><MayushText variant="caption" color={colors.neutral.gray700}>{line.quantity} {language === 'ar' ? 'منتج' : 'article'}</MayushText></View><MayushText variant="sectionTitle" color={colors.brand.orange500}>{formatMadPrice(line.unitPriceMad * line.quantity)}</MayushText></View></OrderCard>
    <InfoRow icon="shield" label={copy.payment} value={`${copy.card} •••• 4587`} isRTL={isRTL} />
    <View style={[styles.refundAmount, isRTL && styles.rowReverse]}><Circle icon="refresh-cw" /><View style={styles.flex}><Label>{copy.refundable}</Label><MayushText variant="priceLarge" color={colors.brand.orange500}>{formatMadPrice(draft.requestedAmountMad)}</MayushText></View></View>
    <InfoRow icon="credit-card" label={copy.method} value={`${copy.card} •••• 4587`} isRTL={isRTL} />
    <InfoRow icon="clock" label={copy.delay} value={copy.delayValue} isRTL={isRTL} accent />
    <View style={[styles.notice, isRTL && styles.rowReverse]}><MayushIcon name="info" size={22} color={colors.brand.navy900} /><MayushText variant="caption" color={colors.neutral.gray700} style={styles.flex} align={isRTL ? 'right' : 'left'}>{copy.info}</MayushText></View>
    <OrderActionButton label={copy.confirm} icon="check-circle" onPress={() => { void onConfirm(); }} primary />
  </ScrollView></Canvas>;
};

export const OrderRefundCompletedScreen: React.FC<{
  order: BuyerOrder;
  refund: RefundRecord;
  returnRequest?: ReturnRequestRecord | null;
  onOrders: () => void;
  onShop: () => void;
}> = ({ order, refund, returnRequest, onOrders, onShop }) => {
  const { language, isRTL } = useTheme();
  const copy = language === 'ar' ? {
    title: 'تم الاسترداد', subtitle: 'وصلت محاكاة الاسترداد المحلية إلى حالة مكتملة. لا يؤكد ذلك تسوية بنكية حقيقية.', returnNumber: 'رقم الإرجاع', refundNumber: 'مرجع الاسترداد', order: 'الطلب المرتبط', amount: 'المبلغ المسترد', payment: 'وسيلة الدفع', items: 'المنتجات المرتبطة', total: 'إجمالي الاسترداد', orders: 'عرض طلباتي', shop: 'متابعة التسوق', local: 'حالة واجهة محلية مكتملة',
  } : {
    title: 'Remboursement effectué', subtitle: 'La simulation locale du remboursement a atteint l’état terminé. Elle ne confirme aucun règlement bancaire réel.', returnNumber: 'Numéro de retour', refundNumber: 'Référence de remboursement', order: 'Commande liée', amount: 'Montant remboursé', payment: 'Moyen de paiement', items: 'Articles associés', total: 'Total remboursé', orders: 'Voir mes commandes', shop: 'Continuer mes achats', local: 'État frontend local terminé',
  };
  const selectedIds = new Set(returnRequest?.selectedLines.map((line) => line.orderLineId) ?? order.lines.map((line) => line.orderLineId));
  const lines = order.lines.filter((line) => selectedIds.has(line.orderLineId));
  return <Canvas><View style={styles.successHeader}><View style={styles.successIcon}><MayushIcon name="check" size={47} color={colors.surface.white} /></View><MayushText variant="pageTitle" color={colors.brand.navy900} align="center">{copy.title}</MayushText><MayushText variant="body" color={colors.neutral.gray700} align="center">{copy.subtitle}</MayushText><MayushText variant="caption" color={colors.semantic.success} align="center">{copy.local}</MayushText></View><ScrollView contentContainerStyle={styles.content} showsVerticalScrollIndicator={false}>
    <OrderCard><ResultRow icon="clipboard" label={returnRequest ? copy.returnNumber : copy.refundNumber} value={returnRequest?.returnRequestId ?? refund.refundId} isRTL={isRTL} accent /><ResultRow icon="file-text" label={copy.order} value={order.orderId} isRTL={isRTL} /><ResultRow icon="wallet" label={copy.amount} value={formatMadPrice(refund.completedAmountMad ?? 0)} isRTL={isRTL} accent /><ResultRow icon="credit-card" label={copy.payment} value={language === 'ar' ? 'بطاقة بنكية •••• 4587' : 'Carte bancaire •••• 4587'} isRTL={isRTL} /></OrderCard>
    <OrderCard><View style={[styles.sectionHeader, isRTL && styles.rowReverse]}><View style={[styles.sectionTitle, isRTL && styles.rowReverse]}><MayushIcon name="shopping-bag" size={19} color={colors.brand.orange500} /><MayushText variant="strongBody" color={colors.brand.navy900}>{copy.items}</MayushText></View><MayushText variant="caption" color={colors.neutral.gray700}>{lines.length} {language === 'ar' ? 'منتجات' : 'articles'}</MayushText></View>{lines.map((line) => <View key={line.orderLineId} style={[styles.miniProduct, isRTL && styles.rowReverse]}><Image source={getOrderLineImage(line)} style={styles.miniImage} /><MayushText variant="body" color={colors.brand.navy900} style={styles.flex} align={isRTL ? 'right' : 'left'}>{line.name}</MayushText><MayushText variant="strongBody" color={colors.brand.navy900}>{formatMadPrice(line.unitPriceMad)}</MayushText></View>)}<View style={[styles.total, isRTL && styles.rowReverse]}><MayushText variant="strongBody" color={colors.brand.navy900}>{copy.total}</MayushText><MayushText variant="sectionTitle" color={colors.brand.orange500}>{formatMadPrice(refund.completedAmountMad ?? 0)}</MayushText></View></OrderCard>
    <OrderActionButton label={copy.orders} icon="clipboard" onPress={onOrders} primary /><OrderActionButton label={copy.shop} icon="shopping-bag" onPress={onShop} />
  </ScrollView></Canvas>;
};

const Canvas: React.FC<{ children: React.ReactNode }> = ({ children }) => <View style={styles.screen}><View style={styles.canvas}>{children}</View></View>;
const Label: React.FC<{ children: React.ReactNode }> = ({ children }) => <MayushText variant="caption" color={colors.neutral.gray700}>{String(children).toUpperCase()}</MayushText>;
const Circle: React.FC<{ icon: MayushIconName }> = ({ icon }) => <View style={styles.circle}><MayushIcon name={icon} size={22} color={colors.brand.orange500} /></View>;
const InfoRow: React.FC<{ icon: MayushIconName; label: string; value: string; isRTL: boolean; accent?: boolean }> = ({ icon, label, value, isRTL, accent }) => <OrderCard><View style={[styles.infoRow, isRTL && styles.rowReverse]}><Circle icon={icon} /><View style={styles.flex}><Label>{label}</Label><MayushText variant="strongBody" color={accent ? colors.brand.orange500 : colors.brand.navy900} align={isRTL ? 'right' : 'left'}>{value}</MayushText></View></View></OrderCard>;
const ResultRow: React.FC<{ icon: MayushIconName; label: string; value: string; isRTL: boolean; accent?: boolean }> = ({ icon, label, value, isRTL, accent }) => <View style={[styles.resultRow, isRTL && styles.rowReverse]}><Circle icon={icon} /><View style={styles.flex}><Label>{label}</Label><MayushText variant="strongBody" color={accent ? colors.brand.orange500 : colors.brand.navy900} align={isRTL ? 'right' : 'left'}>{value}</MayushText></View></View>;

const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: '#FFFDF9' }, canvas: { flex: 1, width: '100%', maxWidth: 393, alignSelf: 'center' }, content: { padding: 14, paddingBottom: 30, gap: 8 }, flex: { flex: 1 }, rowReverse: { flexDirection: 'row-reverse' }, circle: { width: 40, height: 40, borderRadius: 20, alignItems: 'center', justifyContent: 'center', backgroundColor: '#FFF3E7' },
  orderMeta: { minHeight: 53, flexDirection: 'row', alignItems: 'center', gap: 9 }, cancelled: { padding: 7, borderRadius: 6, backgroundColor: colors.semantic.errorBackground, flexDirection: 'row', alignItems: 'center', gap: 4 }, productRow: { minHeight: 75, flexDirection: 'row', alignItems: 'center', gap: 9 }, productImage: { width: 70, height: 62, borderRadius: 7, backgroundColor: colors.surface.cream }, infoRow: { minHeight: 54, flexDirection: 'row', alignItems: 'center', gap: 10 }, refundAmount: { padding: 11, borderRadius: 10, borderWidth: 1, borderColor: colors.brand.orange100, backgroundColor: '#FFF8F0', flexDirection: 'row', alignItems: 'center', gap: 10 }, notice: { padding: 11, borderRadius: 9, borderWidth: 1, borderColor: colors.brand.orange100, flexDirection: 'row', alignItems: 'center', gap: 9 },
  successHeader: { paddingHorizontal: 28, paddingTop: 58, paddingBottom: 8, alignItems: 'center', gap: 6 }, successIcon: { width: 94, height: 94, borderRadius: 47, marginBottom: 7, alignItems: 'center', justifyContent: 'center', backgroundColor: colors.brand.orange500 }, resultRow: { minHeight: 58, borderBottomWidth: 1, borderBottomColor: colors.surface.borderWarm, flexDirection: 'row', alignItems: 'center', gap: 9 }, sectionHeader: { height: 34, flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' }, sectionTitle: { flexDirection: 'row', alignItems: 'center', gap: 7 }, miniProduct: { minHeight: 52, borderTopWidth: 1, borderTopColor: colors.surface.borderWarm, flexDirection: 'row', alignItems: 'center', gap: 8 }, miniImage: { width: 62, height: 40, borderRadius: 5 }, total: { minHeight: 42, borderTopWidth: 1, borderTopColor: colors.surface.borderWarm, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' },
});
