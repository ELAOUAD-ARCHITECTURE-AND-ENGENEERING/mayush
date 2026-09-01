import React from 'react';
import { ScrollView, StyleSheet, View } from 'react-native';
import { BuyerOrder, OrderTrackingEvent } from '../../commerce/orderState';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { useTheme } from '../../design-system/theme/useTheme';
import { colors } from '../../design-system/tokens/colors';
import {
  formatOrderDate,
  getStatusCopy,
  getTrackingEventLabel,
  OrderActionButton,
  OrderCard,
  OrderScreenHeader,
  OrderStatusBadge,
} from './OrderScreenComponents';

export const OrderTrackingScreen: React.FC<{
  order: BuyerOrder;
  onBack: () => void;
  onOpenDetails: () => void;
  onSupport: () => void;
}> = ({ order, onBack, onOpenDetails, onSupport }) => {
  const { language, isRTL } = useTheme();
  const copy = language === 'ar' ? {
    title: 'تتبع الطلب', subtitle: 'بيانات تتبع توضيحية محفوظة مع طلبك.', number: 'رقم الطلب', estimated: 'التسليم المتوقع',
    carrier: 'الناقل', tracking: 'رقم التتبع', address: 'عنوان التسليم', last: 'آخر تحديث', support: 'اتصل بالدعم', details: 'عرض تفاصيل الطلب',
    fixture: 'هذا التتبع نموذج ثابت في الواجهة ولا يمثل اتصالاً مباشراً مع الناقل.',
  } : {
    title: 'Suivi de la commande', subtitle: 'Données de suivi déterministes associées à votre commande.', number: 'Numéro de commande', estimated: 'Livraison estimée',
    carrier: 'Transporteur', tracking: 'Numéro de suivi', address: 'Adresse de livraison', last: 'Dernière mise à jour', support: 'Contacter le support', details: 'Voir les détails de la commande',
    fixture: 'Ce suivi est une simulation frontend déterministe, sans connexion directe au transporteur.',
  };
  const latest = [...order.trackingEvents].reverse().find((event) => event.occurredAt);
  const row = isRTL ? styles.rowReverse : styles.row;
  return (
    <View style={styles.screen} accessibilityLabel={`${copy.title} ${order.orderId}`}>
      <View style={styles.canvas}>
        <OrderScreenHeader onBack={onBack} title={copy.title} subtitle={copy.subtitle} />
        <ScrollView showsVerticalScrollIndicator={false} contentContainerStyle={styles.content}>
          <OrderCard>
            <View style={[row, styles.summaryHeader]}>
              <View style={styles.flex}>
                <MayushText variant="caption" color={colors.neutral.gray700} align={isRTL ? 'right' : 'left'}>{copy.number.toUpperCase()}</MayushText>
                <MayushText variant="sectionTitle" color={colors.brand.navy900} align={isRTL ? 'right' : 'left'}>{order.orderId}</MayushText>
                <MayushText variant="caption" color={colors.neutral.gray700} align={isRTL ? 'right' : 'left'}>{formatOrderDate(order.createdAt, language)}</MayushText>
              </View>
              <OrderStatusBadge status={order.deliveryStatus} label={getStatusCopy(order.deliveryStatus, language)} />
            </View>
            <View style={[row, styles.transportGrid]}>
              <Fact icon="calendar" label={copy.estimated} value={formatOrderDate(order.estimatedDeliveryAt, language)} isRTL={isRTL} />
              <Fact icon="truck-outline" label={copy.carrier} value={order.carrier || 'Mayush Delivery'} isRTL={isRTL} />
              <Fact icon="clipboard" label={copy.tracking} value={order.trackingNumber || '—'} isRTL={isRTL} />
            </View>
          </OrderCard>

          <OrderCard>
            <View style={styles.timeline}>
              {order.trackingEvents.map((event, index) => <TrackingEventRow key={event.trackingEventId} event={event} isLast={index === order.trackingEvents.length - 1} />)}
            </View>
          </OrderCard>

          <OrderCard>
            <View style={[row, styles.address]}><MayushIcon name="map-pin" size={24} color={colors.brand.orange500} /><View style={styles.flex}><MayushText variant="caption" color={colors.neutral.gray700} align={isRTL ? 'right' : 'left'}>{copy.address.toUpperCase()}</MayushText><MayushText variant="body" color={colors.brand.navy900} align={isRTL ? 'right' : 'left'}>{order.address.addressLine}{'\n'}{order.address.zone}, {order.address.city} {order.address.postcode}</MayushText></View></View>
          </OrderCard>

          {latest ? <OrderCard><View style={[row, styles.address]}><MayushIcon name="info" size={23} color={colors.brand.orange500} /><View style={styles.flex}><MayushText variant="caption" color={colors.neutral.gray700} align={isRTL ? 'right' : 'left'}>{copy.last.toUpperCase()}</MayushText><MayushText variant="strongBody" color={colors.brand.orange500} align={isRTL ? 'right' : 'left'}>{formatOrderDate(latest.occurredAt, language)}</MayushText><MayushText variant="body" color={colors.neutral.gray700} align={isRTL ? 'right' : 'left'}>{getTrackingEventLabel(latest.labelKey, language)}</MayushText></View></View></OrderCard> : null}
          <View style={[row, styles.fixtureNotice]}><MayushIcon name="info" size={19} color={colors.semantic.info} /><MayushText variant="caption" color={colors.neutral.gray700} align={isRTL ? 'right' : 'left'} style={styles.flex}>{copy.fixture}</MayushText></View>
          <OrderActionButton label={copy.support} icon="headphones" onPress={onSupport} primary />
          <OrderActionButton label={copy.details} icon="file-text" onPress={onOpenDetails} />
        </ScrollView>
      </View>
    </View>
  );
};

const Fact: React.FC<{ icon: 'calendar' | 'truck-outline' | 'clipboard'; label: string; value: string; isRTL: boolean }> = ({ icon, label, value, isRTL }) => <View style={styles.fact}><MayushIcon name={icon} size={20} color={colors.brand.orange500} /><MayushText variant="caption" color={colors.neutral.gray700} align={isRTL ? 'right' : 'left'}>{label.toUpperCase()}</MayushText><MayushText variant="strongBody" color={colors.brand.navy900} align={isRTL ? 'right' : 'left'}>{value}</MayushText></View>;

const TrackingEventRow: React.FC<{ event: OrderTrackingEvent; isLast: boolean }> = ({ event, isLast }) => {
  const { language, isRTL } = useTheme();
  const active = event.state === 'current';
  const done = event.state === 'completed';
  const stateLabel = language === 'ar'
    ? (done ? 'مكتمل' : active ? 'قيد التنفيذ' : 'قادم')
    : (done ? 'Terminée' : active ? 'En cours' : 'À venir');
  return <View style={[isRTL ? styles.rowReverse : styles.row, styles.event]}><View style={styles.markerColumn}><View style={[styles.marker, (done || active) && styles.markerActive, done && styles.markerDone]}><MayushIcon name={done ? 'check' : active ? 'truck-outline' : 'circle'} size={14} color={(done || active) ? colors.surface.white : colors.neutral.gray500} /></View>{!isLast ? <View style={[styles.connector, done && styles.connectorDone]} /> : null}</View><View style={styles.eventCopy}><MayushText variant="strongBody" color={active ? colors.brand.orange500 : colors.brand.navy900} align={isRTL ? 'right' : 'left'}>{getTrackingEventLabel(event.labelKey, language)}</MayushText><MayushText variant="caption" color={colors.neutral.gray700} align={isRTL ? 'right' : 'left'}>{formatOrderDate(event.occurredAt, language)}</MayushText></View><View style={[styles.eventState, done && styles.eventDone, active && styles.eventActive]}><MayushText variant="caption" color={done ? colors.semantic.success : active ? colors.brand.orange500 : colors.neutral.gray700}>{stateLabel}</MayushText></View></View>;
};

const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: '#FFFDF9' }, canvas: { flex: 1, width: '100%', maxWidth: 393, alignSelf: 'center' }, content: { padding: 14, paddingBottom: 28, gap: 10 },
  row: { flexDirection: 'row' }, rowReverse: { flexDirection: 'row-reverse' }, flex: { flex: 1 }, summaryHeader: { alignItems: 'center', justifyContent: 'space-between' },
  transportGrid: { marginTop: 13, paddingTop: 13, borderTopWidth: 1, borderTopColor: colors.surface.borderWarm, gap: 8 }, fact: { flex: 1, gap: 3 },
  timeline: { paddingVertical: 3 }, event: { minHeight: 68, gap: 10 }, markerColumn: { width: 32, alignItems: 'center' }, marker: { width: 28, height: 28, borderRadius: 14, borderWidth: 1, borderColor: colors.neutral.gray300, alignItems: 'center', justifyContent: 'center', backgroundColor: colors.surface.white }, markerActive: { borderColor: colors.brand.orange500, backgroundColor: colors.brand.orange500 }, markerDone: { borderColor: colors.semantic.success, backgroundColor: colors.semantic.success }, connector: { flex: 1, width: 2, backgroundColor: colors.neutral.gray300 }, connectorDone: { backgroundColor: colors.semantic.success }, eventCopy: { flex: 1, paddingTop: 4, gap: 2 }, eventState: { alignSelf: 'flex-start', marginTop: 2, paddingHorizontal: 8, paddingVertical: 5, borderRadius: 7, backgroundColor: colors.neutral.gray100 }, eventDone: { backgroundColor: colors.semantic.successBackground }, eventActive: { backgroundColor: '#FFF2E6' },
  address: { alignItems: 'flex-start', gap: 10 }, fixtureNotice: { padding: 10, gap: 8, alignItems: 'center' },
});
