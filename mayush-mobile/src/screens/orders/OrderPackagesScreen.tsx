import React from 'react';
import { Image, ScrollView, StyleSheet, View } from 'react-native';
import { BuyerOrder, getOrderPackageLines } from '../../commerce/orderState';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { useTheme } from '../../design-system/theme/useTheme';
import { colors } from '../../design-system/tokens/colors';
import { formatOrderDate, getOrderLineImage, getStatusCopy, OrderActionButton, OrderCard, OrderScreenHeader, OrderStatusBadge } from './OrderScreenComponents';

export const OrderPackagesScreen: React.FC<{
  order: BuyerOrder;
  onBack: () => void;
  onOpenPackage: (packageId: string) => void;
}> = ({ order, onBack, onOpenPackage }) => {
  const { language, isRTL } = useTheme();
  const copy = language === 'ar' ? {
    title: 'عدة طرود', subtitle: 'تم شحن طلبك في عدة طرود وقد تصل في تواريخ مختلفة.', order: 'رقم الطلب', status: 'حالة الطلب',
    seller: 'البائع', carrier: 'الناقل', estimated: 'التسليم المتوقع', track: 'عرض الطرد', info: 'يمكن أن يكون لكل طرد ناقل وموعد تسليم خاص به.',
  } : {
    title: 'Plusieurs colis', subtitle: 'Votre commande a été expédiée en plusieurs colis. Ils peuvent arriver à des dates différentes.', order: 'Numéro de commande', status: 'Statut de la commande',
    seller: 'Vendeur', carrier: 'Transporteur', estimated: 'Livraison estimée', track: 'Voir le colis', info: 'Chaque colis peut avoir son propre transporteur et sa propre date de livraison estimée.',
  };
  const row = isRTL ? styles.rowReverse : styles.row;
  return <View style={styles.screen} accessibilityLabel={`${copy.title} ${order.orderId}`}><View style={styles.canvas}><OrderScreenHeader onBack={onBack} title={copy.title} subtitle={copy.subtitle} /><ScrollView showsVerticalScrollIndicator={false} contentContainerStyle={styles.content}>
    <OrderCard><View style={[row, styles.overview]}><View style={styles.flex}><MayushText variant="caption" color={colors.neutral.gray700} align={isRTL ? 'right' : 'left'}>{copy.order.toUpperCase()}</MayushText><MayushText variant="sectionTitle" color={colors.brand.navy900} align={isRTL ? 'right' : 'left'}>{order.orderId}</MayushText></View><View><MayushText variant="caption" color={colors.neutral.gray700} align={isRTL ? 'right' : 'left'}>{copy.status.toUpperCase()}</MayushText><MayushText variant="sectionTitle" color={colors.brand.orange500} align={isRTL ? 'right' : 'left'}>{getStatusCopy(order.deliveryStatus, language)}</MayushText></View></View></OrderCard>
    {order.packages.map((pkg, index) => {
      const lines = getOrderPackageLines(order, pkg.packageId);
      return <OrderCard key={pkg.packageId}>
        <View style={[row, styles.packageHeader]}><View style={[row, styles.packageTitle]}><MayushIcon name="box" size={23} color={colors.brand.orange500} /><MayushText variant="sectionTitle" color={colors.brand.navy900}>{language === 'ar' ? `الطرد ${index + 1} من ${order.packages.length}` : `Colis ${index + 1} sur ${order.packages.length}`}</MayushText></View><OrderStatusBadge status={pkg.status} label={getStatusCopy(pkg.status, language)} /></View>
        <View style={[row, styles.packageBody]}><View style={styles.images}>{lines.slice(0, 2).map((line) => <Image key={line.orderLineId} source={getOrderLineImage(line)} style={styles.image} />)}</View><View style={styles.packageFacts}><Fact label={copy.seller} value={pkg.sellerName} isRTL={isRTL} /><Fact label={copy.carrier} value={pkg.carrier || '—'} isRTL={isRTL} /></View><View style={styles.packageFacts}><Fact label={copy.estimated} value={formatOrderDate(pkg.estimatedDeliveryAt, language)} isRTL={isRTL} /></View></View>
        <OrderActionButton label={copy.track} icon="truck-outline" onPress={() => onOpenPackage(pkg.packageId)} />
      </OrderCard>;
    })}
    <View style={[row, styles.info]}><MayushIcon name="info" size={22} color={colors.brand.orange500} /><MayushText variant="body" color={colors.brand.navy900} align={isRTL ? 'right' : 'left'} style={styles.flex}>{copy.info}</MayushText></View>
  </ScrollView></View></View>;
};

const Fact: React.FC<{ label: string; value: string; isRTL: boolean }> = ({ label, value, isRTL }) => <View style={styles.fact}><MayushText variant="caption" color={colors.neutral.gray700} align={isRTL ? 'right' : 'left'}>{label}</MayushText><MayushText variant="strongBody" color={colors.brand.navy900} align={isRTL ? 'right' : 'left'}>{value}</MayushText></View>;
const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: '#FFFDF9' }, canvas: { flex: 1, width: '100%', maxWidth: 393, alignSelf: 'center' }, content: { padding: 14, paddingBottom: 28, gap: 10 }, row: { flexDirection: 'row' }, rowReverse: { flexDirection: 'row-reverse' }, flex: { flex: 1 }, overview: { justifyContent: 'space-between', gap: 14 },
  packageHeader: { alignItems: 'center', justifyContent: 'space-between', gap: 8, marginBottom: 10 }, packageTitle: { alignItems: 'center', gap: 7 }, packageBody: { gap: 10, marginBottom: 10 }, images: { flexDirection: 'row', gap: 5 }, image: { width: 68, height: 61, borderRadius: 7, backgroundColor: colors.surface.cream }, packageFacts: { flex: 1, gap: 8 }, fact: { gap: 2 }, info: { borderWidth: 1, borderColor: colors.brand.orange100, borderRadius: 10, padding: 12, alignItems: 'center', gap: 9, backgroundColor: '#FFF9F2' },
});
