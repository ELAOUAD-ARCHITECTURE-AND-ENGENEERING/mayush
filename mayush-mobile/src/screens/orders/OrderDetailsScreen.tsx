import React, { useMemo, useState } from 'react';
import { ScrollView, StyleSheet, TouchableOpacity, View } from 'react-native';
import { formatMadPrice } from '../../commerce/cartState';
import { BuyerOrder, getOrderPackageLines } from '../../commerce/orderState';
import { MayushIcon, MayushIconName } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { useTheme } from '../../design-system/theme/useTheme';
import { colors } from '../../design-system/tokens/colors';
import {
  formatOrderDate,
  getStatusCopy,
  OrderActionButton,
  OrderCard,
  OrderLineList,
  OrderScreenHeader,
  OrderStatusBadge,
} from './OrderScreenComponents';

export type OrderDetailVariant = 'preparing' | 'shipped' | 'delivered' | 'multi-vendor';

export interface OrderDetailsScreenProps {
  order: BuyerOrder;
  variant: OrderDetailVariant;
  onBack: () => void;
  onTrack: () => void;
  onOpenPackages: () => void;
  onOpenInvoice: () => void;
  onSupport: () => void;
  onCancel: () => void;
  onReorder: () => void;
  onRate: () => void;
  onReturn: () => void;
}

export const OrderDetailsScreen: React.FC<OrderDetailsScreenProps> = ({
  order,
  variant,
  onBack,
  onTrack,
  onOpenPackages,
  onOpenInvoice,
  onSupport,
  onCancel,
  onReorder,
  onRate,
  onReturn,
}) => {
  const { isRTL, language } = useTheme();
  const [deferredNotice, setDeferredNotice] = useState<string | null>(null);
  const copy = language === 'ar' ? {
    title: 'تفاصيل الطلب', subtitle: 'اطّلع على جميع تفاصيل طلبك.', reference: 'مرجع الطلب', payment: 'حالة الدفع',
    paid: 'مدفوع', current: 'الحالة الحالية', created: 'تاريخ الإنشاء', products: 'المنتجات المطلوبة', address: 'عنوان التسليم',
    delivery: 'التوصيل', estimated: 'التسليم المتوقع', total: 'المجموع المدفوع', support: 'اتصل بالدعم', track: 'تتبع التوصيل',
    cancel: 'إلغاء الطلب', packages: 'تتبع الطرود', reorder: 'اطلب من جديد', rate: 'قيّم المنتجات', return: 'طلب إرجاع',
    invoice: 'عرض الفاتورة', unavailable: 'سيتم توفير هذه العملية في المرحلة التالية دون تغيير طلبك.', carrier: 'الناقل', tracking: 'رقم التتبع', shipped: 'تاريخ الشحن',
    multiInfo: 'تم شحن هذا الطلب في عدة طرود. يمكن أن يكون لكل طرد تتبع وموعد تسليم مختلف.', packageCount: 'عدد الطرود', orderStatus: 'حالة الطلب',
  } : {
    title: 'Détails de la commande', subtitle: 'Retrouvez tous les détails de votre commande.', reference: 'Référence de commande', payment: 'Statut de paiement',
    paid: 'Payé', current: 'Statut actuel', created: 'Date de création', products: 'Articles commandés', address: 'Adresse de livraison',
    delivery: 'Mode de livraison', estimated: 'Livraison estimée', total: 'Total payé', support: 'Contacter le support', track: 'Suivre la livraison',
    cancel: 'Annuler la commande', packages: 'Suivre les colis', reorder: 'Commander à nouveau', rate: 'Évaluer les produits', return: 'Demander un retour',
    invoice: 'Voir la facture', unavailable: 'Cette action sera disponible dans une prochaine étape, sans modifier votre commande.', carrier: 'Transporteur', tracking: 'Référence de suivi', shipped: 'Date d’expédition',
    multiInfo: 'Cette commande est expédiée en plusieurs colis. Chaque colis peut avoir un suivi et une date de livraison différents.', packageCount: 'Nombre de colis', orderStatus: 'Statut de la commande',
  };

  const sellerGroups = useMemo(() => {
    const groups = new Map<string, { sellerName: string; lineIds: Set<string>; statuses: string[] }>();
    order.packages.forEach((pkg) => {
      const key = pkg.sellerId || pkg.sellerName;
      const group = groups.get(key) || { sellerName: pkg.sellerName, lineIds: new Set<string>(), statuses: [] };
      pkg.orderLineIds.forEach((lineId) => group.lineIds.add(lineId));
      group.statuses.push(getStatusCopy(pkg.status, language));
      groups.set(key, group);
    });
    return [...groups.values()].map((group) => ({
      ...group,
      lines: order.lines.filter((line) => group.lineIds.has(line.orderLineId)),
    }));
  }, [language, order]);

  const showDeferred = () => setDeferredNotice(copy.unavailable);
  const row = isRTL ? styles.rowReverse : styles.row;
  const status = getStatusCopy(order.deliveryStatus, language);

  return (
    <View style={styles.screen} accessibilityLabel={`${copy.title} ${order.orderId}`}>
      <View style={styles.canvas}>
        <OrderScreenHeader onBack={onBack} showBell={variant !== 'preparing' && variant !== 'shipped'} title={copy.title} subtitle={variant === 'delivered' ? copy.subtitle : order.orderId} />
        <ScrollView showsVerticalScrollIndicator={false} contentContainerStyle={styles.content}>
          <OrderCard>
            <View style={row}>
              <Meta icon="clipboard" label={copy.reference} value={order.orderId} isRTL={isRTL} />
              <Meta icon={variant === 'delivered' ? 'shield-check' : 'clock'} label={variant === 'multi-vendor' ? copy.orderStatus : copy.current} value={status} accent={variant !== 'delivered'} success={variant === 'delivered'} isRTL={isRTL} />
            </View>
            <View style={[row, styles.metaTop]}>
              <Meta icon="calendar" label={copy.created} value={formatOrderDate(order.createdAt, language)} isRTL={isRTL} />
              <Meta icon="shield" label={copy.payment} value={copy.paid} success isRTL={isRTL} />
            </View>
            {variant === 'multi-vendor' ? (
              <View style={[row, styles.packageCount]}>
                <MayushIcon name="box" size={20} color={colors.brand.orange500} />
                <MayushText variant="strongBody" color={colors.brand.navy900}>{copy.packageCount} : {order.packages.length}</MayushText>
              </View>
            ) : null}
          </OrderCard>

          {variant === 'preparing' ? <OrderProgress order={order} /> : null}
          {variant === 'shipped' ? <ShipmentSummary order={order} /> : null}
          {variant === 'delivered' && order.deliveredAt ? (
            <OrderCard style={styles.deliveredCard}>
              <View style={[row, styles.centerRow]}>
                <View style={styles.roundIcon}><MayushIcon name="package-variant-closed" size={28} color={colors.semantic.success} /></View>
                <View style={styles.flex}>
                  <MayushText variant="strongBody" color={colors.brand.navy900} align={isRTL ? 'right' : 'left'}>{language === 'ar' ? 'تم التسليم في' : 'Livrée le'}</MayushText>
                  <MayushText variant="sectionTitle" color={colors.brand.orange500} align={isRTL ? 'right' : 'left'}>{formatOrderDate(order.deliveredAt, language)}</MayushText>
                </View>
              </View>
            </OrderCard>
          ) : null}
          {variant === 'multi-vendor' ? <InfoBanner text={copy.multiInfo} isRTL={isRTL} /> : null}

          {variant === 'multi-vendor' ? sellerGroups.map((group) => (
            <OrderCard key={group.sellerName}>
              <View style={[row, styles.sectionHeader]}>
                <View style={[row, styles.centerRow]}>
                  <MayushIcon name="briefcase" size={21} color={colors.brand.orange500} />
                  <MayushText variant="sectionTitle" color={colors.brand.navy900}>{group.sellerName}</MayushText>
                </View>
                <OrderStatusBadge status="in_transit" label={group.statuses.join(' · ')} />
              </View>
              <OrderLineList lines={group.lines} />
            </OrderCard>
          )) : (
            <OrderCard>
              <View style={[row, styles.sectionHeader]}>
                <View style={[row, styles.centerRow]}>
                  <MayushIcon name="shopping-bag" size={21} color={colors.brand.orange500} />
                  <MayushText variant="sectionTitle" color={colors.brand.navy900}>{copy.products}</MayushText>
                </View>
                <MayushText variant="caption" color={colors.neutral.gray700}>{order.lines.length} {language === 'ar' ? 'منتجات' : 'articles'}</MayushText>
              </View>
              <OrderLineList lines={order.lines} />
            </OrderCard>
          )}

          <OrderCard>
            <View style={[row, styles.addressRow]}>
              <MayushIcon name="map-pin" size={24} color={colors.brand.orange500} />
              <View style={styles.flex}>
                <MayushText variant="strongBody" color={colors.brand.navy900} align={isRTL ? 'right' : 'left'}>{copy.address}</MayushText>
                <MayushText variant="body" color={colors.neutral.gray700} align={isRTL ? 'right' : 'left'}>{order.address.name}{'\n'}{order.address.addressLine}{'\n'}{order.address.zone}, {order.address.city} {order.address.postcode}</MayushText>
              </View>
            </View>
          </OrderCard>

          <OrderCard>
            <View style={styles.summaryLine}><MayushText variant="body" color={colors.neutral.gray700}>{language === 'ar' ? 'المجموع الفرعي' : 'Sous-total'}</MayushText><MayushText variant="body" color={colors.brand.navy900}>{formatMadPrice(order.totalMad - order.deliveryFeeMad + order.discountMad)}</MayushText></View>
            <View style={styles.summaryLine}><MayushText variant="body" color={colors.neutral.gray700}>{language === 'ar' ? 'رسوم التوصيل' : 'Frais de livraison'}</MayushText><MayushText variant="body" color={colors.brand.navy900}>{formatMadPrice(order.deliveryFeeMad)}</MayushText></View>
            {order.discountMad > 0 ? <View style={styles.summaryLine}><MayushText variant="body" color={colors.semantic.success}>{language === 'ar' ? 'الخصم' : 'Réduction'}</MayushText><MayushText variant="body" color={colors.semantic.success}>- {formatMadPrice(order.discountMad)}</MayushText></View> : null}
            <View style={[styles.summaryLine, styles.totalLine]}><MayushText variant="sectionTitle" color={colors.brand.navy900}>{copy.total}</MayushText><MayushText variant="sectionTitle" color={colors.brand.orange500}>{formatMadPrice(order.totalMad)}</MayushText></View>
          </OrderCard>

          {variant === 'delivered' ? (
            <View style={styles.actionGrid}>
              <SquareAction icon="rotate-ccw" label={copy.reorder} onPress={onReorder} />
              <SquareAction icon="star" label={copy.rate} onPress={onRate} />
              <SquareAction icon="box" label={copy.return} onPress={onReturn} />
              <SquareAction icon="file-text" label={copy.invoice} onPress={onOpenInvoice} />
            </View>
          ) : variant === 'multi-vendor' ? (
            <OrderActionButton label={copy.packages} icon="truck-outline" onPress={onOpenPackages} primary />
          ) : (
            <>
              <OrderActionButton label={copy.track} icon="truck-outline" onPress={onTrack} primary={variant === 'shipped'} />
              {variant === 'preparing' ? <OrderActionButton label={copy.cancel} icon="trash-2" onPress={onCancel} /> : null}
            </>
          )}
          <OrderActionButton label={copy.support} icon="headphones" onPress={onSupport} />
          {deferredNotice ? <InfoBanner text={deferredNotice} isRTL={isRTL} /> : null}
        </ScrollView>
      </View>
    </View>
  );
};

const Meta: React.FC<{ icon: MayushIconName; label: string; value: string; accent?: boolean; success?: boolean; isRTL: boolean }> = ({ icon, label, value, accent, success, isRTL }) => (
  <View style={[styles.meta, isRTL && styles.rowReverse]}>
    <View style={styles.roundIcon}><MayushIcon name={icon} size={20} color={success ? colors.semantic.success : colors.brand.orange500} /></View>
    <View style={styles.flex}><MayushText variant="caption" color={colors.neutral.gray700} align={isRTL ? 'right' : 'left'}>{label.toUpperCase()}</MayushText><MayushText variant="strongBody" color={success ? colors.semantic.success : accent ? colors.brand.orange500 : colors.brand.navy900} align={isRTL ? 'right' : 'left'}>{value}</MayushText></View>
  </View>
);

const OrderProgress: React.FC<{ order: BuyerOrder }> = ({ order }) => {
  const { language } = useTheme();
  const steps = language === 'ar' ? ['مؤكد', 'قيد التحضير', 'تم الشحن', 'تم التسليم'] : ['Confirmée', 'En préparation', 'Expédiée', 'Livrée'];
  return <OrderCard><View style={styles.progress}>{steps.map((step, index) => <View key={step} style={styles.progressStep}><View style={[styles.progressDot, index < 2 && styles.progressDotActive]}>{index === 0 ? <MayushIcon name="check" size={15} color={colors.surface.white} /> : <MayushIcon name={index === 1 ? 'box' : 'truck-outline'} size={15} color={index < 2 ? colors.surface.white : colors.neutral.gray700} />}</View><MayushText variant="caption" color={index === 1 ? colors.brand.orange500 : colors.brand.navy900} align="center">{step}</MayushText></View>)}</View></OrderCard>;
};

const ShipmentSummary: React.FC<{ order: BuyerOrder }> = ({ order }) => {
  const { language, isRTL } = useTheme();
  const labels = language === 'ar' ? ['الناقل', 'رقم التتبع', 'تاريخ الشحن', 'التسليم المتوقع'] : ['Transporteur', 'Référence de suivi', 'Date d’expédition', 'Livraison estimée'];
  const values = [order.carrier || 'Mayush Delivery', order.trackingNumber || '—', formatOrderDate(order.shippedAt, language), formatOrderDate(order.estimatedDeliveryAt, language)];
  return <OrderCard><View style={styles.shipmentGrid}>{labels.map((label, index) => <View key={label} style={styles.shipmentItem}><MayushText variant="caption" color={colors.neutral.gray700} align={isRTL ? 'right' : 'left'}>{label.toUpperCase()}</MayushText><MayushText variant="strongBody" color={index === 3 ? colors.brand.orange500 : colors.brand.navy900} align={isRTL ? 'right' : 'left'}>{values[index]}</MayushText></View>)}</View></OrderCard>;
};

const SquareAction: React.FC<{ icon: MayushIconName; label: string; onPress: () => void }> = ({ icon, label, onPress }) => <TouchableOpacity accessibilityRole="button" onPress={onPress} style={styles.squareAction}><MayushIcon name={icon} size={25} color={colors.brand.orange500} /><MayushText variant="button" color={colors.brand.navy900} align="center">{label}</MayushText></TouchableOpacity>;
const InfoBanner: React.FC<{ text: string; isRTL: boolean }> = ({ text, isRTL }) => <View style={[styles.info, isRTL && styles.rowReverse]}><MayushIcon name="info" size={21} color={colors.brand.orange500} /><MayushText variant="body" color={colors.brand.navy900} align={isRTL ? 'right' : 'left'} style={styles.flex}>{text}</MayushText></View>;

const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: '#FFFDF9' },
  canvas: { flex: 1, width: '100%', maxWidth: 393, alignSelf: 'center' },
  content: { padding: 14, paddingBottom: 28, gap: 10 },
  row: { flexDirection: 'row', gap: 8 }, rowReverse: { flexDirection: 'row-reverse', gap: 8 }, flex: { flex: 1 },
  meta: { width: '50%', flexDirection: 'row', gap: 8, alignItems: 'center' }, metaTop: { marginTop: 12, paddingTop: 12, borderTopWidth: 1, borderTopColor: colors.surface.borderWarm },
  roundIcon: { width: 42, height: 42, borderRadius: 21, alignItems: 'center', justifyContent: 'center', backgroundColor: '#FFF4E9' },
  packageCount: { marginTop: 12, paddingTop: 12, borderTopWidth: 1, borderTopColor: colors.surface.borderWarm, alignItems: 'center' },
  centerRow: { alignItems: 'center' }, sectionHeader: { alignItems: 'center', justifyContent: 'space-between', marginBottom: 10 },
  addressRow: { alignItems: 'flex-start' },
  shipmentGrid: { flexDirection: 'row', flexWrap: 'wrap', gap: 10 }, shipmentItem: { width: '47%', gap: 3 },
  progress: { flexDirection: 'row', justifyContent: 'space-between' }, progressStep: { width: '24%', alignItems: 'center', gap: 5 }, progressDot: { width: 32, height: 32, borderRadius: 16, borderWidth: 1, borderColor: colors.neutral.gray300, alignItems: 'center', justifyContent: 'center' }, progressDotActive: { backgroundColor: colors.brand.orange500, borderColor: colors.brand.orange500 },
  deliveredCard: { backgroundColor: '#FCFFF9' },
  summaryLine: { minHeight: 28, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' }, totalLine: { marginTop: 5, paddingTop: 9, borderTopWidth: 1, borderTopColor: colors.surface.borderWarm },
  actionGrid: { flexDirection: 'row', gap: 7 }, squareAction: { flex: 1, minHeight: 105, padding: 7, borderRadius: 10, borderWidth: 1, borderColor: colors.surface.borderWarm, alignItems: 'center', justifyContent: 'center', gap: 7, backgroundColor: colors.surface.white },
  info: { borderWidth: 1, borderColor: colors.brand.orange100, borderRadius: 10, padding: 12, flexDirection: 'row', alignItems: 'center', gap: 10, backgroundColor: '#FFF9F2' },
});
