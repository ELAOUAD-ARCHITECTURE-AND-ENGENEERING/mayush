import React from 'react';
import { ScrollView, StyleSheet, View } from 'react-native';
import { BuyerOrder, getOrderPackageLines, OrderPackage } from '../../commerce/orderState';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { useTheme } from '../../design-system/theme/useTheme';
import { colors } from '../../design-system/tokens/colors';
import { formatOrderDate, getStatusCopy, getTrackingEventLabel, OrderActionButton, OrderCard, OrderLineList, OrderScreenHeader, OrderStatusBadge } from './OrderScreenComponents';

export const OrderPackageDetailsScreen: React.FC<{
  order: BuyerOrder;
  orderPackage: OrderPackage;
  onBack: () => void;
  onTrack: () => void;
  onOpenInvoice: () => void;
  onSupport: () => void;
}> = ({ order, orderPackage, onBack, onTrack, onOpenInvoice, onSupport }) => {
  const { language, isRTL } = useTheme();
  const lines = getOrderPackageLines(order, orderPackage.packageId);
  const copy = language === 'ar' ? {
    title: 'تفاصيل الطرد', subtitle: 'اطّلع على معلومات طردك أثناء التوصيل.', number: 'رقم الطرد', seller: 'البائع', shipped: 'تاريخ الشحن',
    products: 'المنتجات داخل هذا الطرد', shipping: 'معلومات الشحن', carrier: 'الناقل', tracking: 'رقم التتبع', estimated: 'التسليم المتوقع',
    timeline: 'تتبع التوصيل', instructions: 'تعليمات التسليم', note: 'يرجى التأكد من وجود شخص لاستلام الطرد.', track: 'تتبع هذا الطرد',
    invoice: 'عرض فاتورة الطلب', support: 'اتصل بالدعم',
  } : {
    title: 'Détails du colis', subtitle: 'Consultez toutes les informations sur votre colis en cours de livraison.', number: 'Numéro du colis', seller: 'Vendeur', shipped: 'Date d’expédition',
    products: 'Articles inclus dans ce colis', shipping: 'Informations d’expédition', carrier: 'Transporteur', tracking: 'Numéro de suivi', estimated: 'Livraison estimée',
    timeline: 'Suivi de livraison', instructions: 'Instructions de livraison', note: 'Merci de vérifier que quelqu’un sera présent pour la réception du colis.', track: 'Suivre ce colis',
    invoice: 'Voir la facture de la commande', support: 'Contacter le support',
  };
  const row = isRTL ? styles.rowReverse : styles.row;
  return <View style={styles.screen} accessibilityLabel={`${copy.title} ${orderPackage.packageId}`}><View style={styles.canvas}><OrderScreenHeader onBack={onBack} title={copy.title} subtitle={copy.subtitle} /><ScrollView showsVerticalScrollIndicator={false} contentContainerStyle={styles.content}>
    <OrderCard><View style={[row, styles.overview]}><View style={styles.flex}><Fact label={copy.number} value={orderPackage.packageId} isRTL={isRTL} /><Fact label={copy.seller} value={orderPackage.sellerName} isRTL={isRTL} /></View><View style={styles.statusColumn}><OrderStatusBadge status={orderPackage.status} label={getStatusCopy(orderPackage.status, language)} /><Fact label={copy.shipped} value={formatOrderDate(orderPackage.shippedAt, language)} isRTL={isRTL} /></View></View></OrderCard>
    <OrderCard><View style={[row, styles.heading]}><View style={[row, styles.headingTitle]}><MayushIcon name="box" size={21} color={colors.brand.orange500} /><MayushText variant="sectionTitle" color={colors.brand.navy900}>{copy.products}</MayushText></View><MayushText variant="caption" color={colors.neutral.gray700}>{lines.length}</MayushText></View><OrderLineList lines={lines} showPrices={false} /></OrderCard>
    <OrderCard><View style={[row, styles.headingTitle]}><MayushIcon name="truck-outline" size={22} color={colors.brand.orange500} /><MayushText variant="sectionTitle" color={colors.brand.orange500}>{copy.shipping}</MayushText></View><View style={[row, styles.shippingFacts]}><Fact label={copy.carrier} value={orderPackage.carrier || '—'} isRTL={isRTL} /><Fact label={copy.tracking} value={orderPackage.trackingNumber || '—'} isRTL={isRTL} /><Fact label={copy.estimated} value={formatOrderDate(orderPackage.estimatedDeliveryAt, language)} isRTL={isRTL} /></View></OrderCard>
    {orderPackage.trackingEvents.length ? <OrderCard><View style={[row, styles.headingTitle]}><MayushIcon name="clock" size={22} color={colors.brand.orange500} /><MayushText variant="sectionTitle" color={colors.brand.navy900}>{copy.timeline}</MayushText></View><View style={styles.events}>{[...orderPackage.trackingEvents].reverse().map((event) => <View key={event.trackingEventId} style={[row, styles.event]}><View style={[styles.eventDot, event.state === 'current' && styles.eventDotCurrent]} /><View style={styles.flex}><MayushText variant="caption" color={colors.neutral.gray700} align={isRTL ? 'right' : 'left'}>{formatOrderDate(event.occurredAt, language)}</MayushText><MayushText variant="strongBody" color={event.state === 'current' ? colors.brand.orange500 : colors.brand.navy900} align={isRTL ? 'right' : 'left'}>{getTrackingEventLabel(event.labelKey, language)}</MayushText></View></View>)}</View></OrderCard> : null}
    <OrderCard><View style={[row, styles.headingTitle]}><MayushIcon name="map-pin" size={22} color={colors.brand.orange500} /><MayushText variant="sectionTitle" color={colors.brand.orange500}>{copy.instructions}</MayushText></View><MayushText variant="body" color={colors.brand.navy900} align={isRTL ? 'right' : 'left'}>{order.address.addressLine}{'\n'}{order.address.zone}, {order.address.city} {order.address.postcode}</MayushText><View style={[row, styles.note]}><MayushIcon name="message-square" size={16} color={colors.neutral.gray700} /><MayushText variant="caption" color={colors.neutral.gray700} align={isRTL ? 'right' : 'left'} style={styles.flex}>{copy.note}</MayushText></View></OrderCard>
    <OrderActionButton label={copy.track} icon="truck-outline" onPress={onTrack} primary />
    <OrderActionButton label={copy.invoice} icon="file-text" onPress={onOpenInvoice} />
    <OrderActionButton label={copy.support} icon="headphones" onPress={onSupport} />
  </ScrollView></View></View>;
};

const Fact: React.FC<{ label: string; value: string; isRTL: boolean }> = ({ label, value, isRTL }) => <View style={styles.fact}><MayushText variant="caption" color={colors.neutral.gray700} align={isRTL ? 'right' : 'left'}>{label.toUpperCase()}</MayushText><MayushText variant="strongBody" color={colors.brand.navy900} align={isRTL ? 'right' : 'left'}>{value}</MayushText></View>;
const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: '#FFFDF9' }, canvas: { flex: 1, width: '100%', maxWidth: 393, alignSelf: 'center' }, content: { padding: 14, paddingBottom: 28, gap: 10 }, row: { flexDirection: 'row' }, rowReverse: { flexDirection: 'row-reverse' }, flex: { flex: 1 }, overview: { gap: 12 }, statusColumn: { width: '47%', gap: 12 }, fact: { flex: 1, gap: 3, marginBottom: 8 }, heading: { justifyContent: 'space-between', alignItems: 'center', marginBottom: 8 }, headingTitle: { alignItems: 'center', gap: 8, marginBottom: 8 }, shippingFacts: { gap: 8 }, events: { gap: 8 }, event: { gap: 9, alignItems: 'flex-start' }, eventDot: { width: 11, height: 11, marginTop: 5, borderRadius: 6, borderWidth: 2, borderColor: colors.neutral.gray500, backgroundColor: colors.surface.white }, eventDotCurrent: { borderColor: colors.brand.orange500, backgroundColor: colors.brand.orange500 }, note: { marginTop: 10, gap: 8, alignItems: 'center' },
});
