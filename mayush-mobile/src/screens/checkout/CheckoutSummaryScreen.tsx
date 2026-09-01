import React from 'react';
import { Image, ScrollView, StyleSheet, TouchableOpacity, View } from 'react-native';
import { CartState, formatMadPrice, getCartTotals } from '../../commerce/cartState';
import { DeliveryMethod, getCheckoutGrandTotalMad, PaymentMethod, SavedAddress } from '../../commerce/checkoutState';
import { MayushLogo } from '../../design-system/components/brand/MayushLogo';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { useTheme } from '../../design-system/theme/useTheme';
import { colors } from '../../design-system/tokens/colors';

const PRODUCT_IMAGES = [
  require('../../../assets/reference-art/home-new-luna.png'),
  require('../../../assets/reference-art/home-new-nori.png'),
  require('../../../assets/reference-art/home-new-kyoto.png'),
];

export interface CheckoutSummaryScreenProps {
  cart: CartState;
  address: SavedAddress;
  deliveryMethod: DeliveryMethod;
  paymentMethod: PaymentMethod;
  deliveryFeeMad?: number;
  onBack: () => void;
  onChooseAddress: () => void;
}

export const CheckoutSummaryScreen: React.FC<CheckoutSummaryScreenProps> = ({ cart, address, deliveryMethod, paymentMethod, deliveryFeeMad = 0, onBack, onChooseAddress }) => {
  const { isRTL, language } = useTheme();
  const totals = getCartTotals(cart);
  const copy = language === 'ar'
    ? {
      title: '\u0625\u062a\u0645\u0627\u0645 \u0637\u0644\u0628\u064a',
      address: '\u0627\u0644\u0639\u0646\u0648\u0627\u0646 \u0648\u0627\u0644\u062a\u0648\u0635\u064a\u0644',
      delivery: '\u0637\u0631\u064a\u0642\u0629 \u0627\u0644\u062a\u0648\u0635\u064a\u0644',
      payment: '\u0637\u0631\u064a\u0642\u0629 \u0627\u0644\u062f\u0641\u0639',
      edit: '\u062a\u0639\u062f\u064a\u0644',
      articles: '\u0627\u0644\u0645\u0646\u062a\u062c\u0627\u062a',
      summary: '\u0627\u0644\u0645\u0644\u062e\u0635',
      subtotal: '\u0627\u0644\u0645\u062c\u0645\u0648\u0639 \u0627\u0644\u0641\u0631\u0639\u064a',
      reduction: '\u0627\u0644\u062e\u0635\u0645',
      deliveryFee: '\u0627\u0644\u062a\u0648\u0635\u064a\u0644',
      total: '\u0627\u0644\u0625\u062c\u0645\u0627\u0644\u064a',
      continue: '\u0645\u062a\u0627\u0628\u0639\u0629',
    }
    : {
      title: 'Finaliser ma commande',
      address: 'Adresse de livraison',
      delivery: 'Mode de livraison',
      payment: 'Mode de paiement',
      edit: 'Modifier',
      articles: 'Articles',
      summary: 'Résumé',
      subtotal: 'Sous-total',
      reduction: 'Réduction',
      deliveryFee: 'Livraison',
      total: 'Total',
      continue: 'Continuer',
    };
  const direction = isRTL ? styles.rowReverse : undefined;
  const addressDetail = `${address.name}\n${address.addressLine}\n${address.postcode} ${address.city}, Maroc\n${address.phone}`;
  const deliveryDetail = deliveryMethod === 'express'
    ? (language === 'ar' ? 'توصيل سريع إلى المنزل\nخلال 24 إلى 48 ساعة' : 'Livraison express à domicile\nSous 24 à 48 h ouvrées')
    : deliveryMethod === 'relay'
      ? (language === 'ar' ? 'استلام من نقطة توزيع\nخلال 2 إلى 4 أيام عمل' : 'Retrait en point relais\nSous 2 à 4 jours ouvrés')
      : (language === 'ar' ? 'توصيل قياسي إلى المنزل\nخلال 2 إلى 4 أيام عمل' : 'Livraison standard à domicile\nSous 2 à 4 jours ouvrés');
  const paymentDetail = paymentMethod === 'wallet'
    ? (language === 'ar' ? 'محفظة Mayush\nدفع سريع وآمن.' : 'Portefeuille Mayush\nPaiement rapide et sécurisé.')
    : paymentMethod === 'cash-on-delivery'
      ? (language === 'ar' ? 'الدفع عند التسليم\nادفع عند الاستلام.' : 'Paiement à la livraison\nVous réglerez votre commande à la réception.')
      : (language === 'ar' ? 'دفع آمن عبر CMI\nبطاقة بنكية محمية.' : 'Paiement sécurisé par CMI\nCarte bancaire protégée.');

  return (
    <View style={styles.screen} accessibilityLabel={copy.title}>
      <View style={[styles.header, direction]}><TouchableOpacity accessibilityRole="button" accessibilityLabel={language === 'ar' ? '\u0631\u062c\u0648\u0639' : 'Retour'} onPress={onBack} style={styles.backButton}><MayushIcon name={isRTL ? 'arrow-right' : 'arrow-left'} size={24} color={colors.brand.navy900} /></TouchableOpacity><MayushLogo width={140} height={41} /><View style={styles.cartIcon}><MayushIcon name="shopping-bag" size={24} color={colors.brand.navy900} /><View style={styles.badge}><MayushText variant="caption" color={colors.surface.white}>{totals.itemCount}</MayushText></View></View></View>
      <MayushText variant="pageTitle" color={colors.brand.navy900} align="center" style={styles.title}>{copy.title}</MayushText>
      <View style={styles.steps}><Step number="1" label={language === 'ar' ? '\u0627\u0644\u0639\u0646\u0648\u0627\u0646' : 'Adresse'} active /><View style={styles.stepLine} /><Step number="2" label={language === 'ar' ? '\u0627\u0644\u062a\u0648\u0635\u064a\u0644' : 'Livraison'} active /><View style={styles.stepLineMuted} /><Step number="3" label={language === 'ar' ? '\u0627\u0644\u062f\u0641\u0639' : 'Paiement'} /><View style={styles.stepLineMuted} /><Step number="4" label={language === 'ar' ? '\u0627\u0644\u062a\u0623\u0643\u064a\u062f' : 'Confirmation'} /></View>
      <ScrollView showsVerticalScrollIndicator={false} contentContainerStyle={styles.content}>
        <CheckoutCard icon="map-pin" title={copy.address} detail={addressDetail} editLabel={copy.edit} direction={direction} onPress={onChooseAddress} />
        <CheckoutCard icon="truck-outline" title={copy.delivery} detail={deliveryDetail} editLabel={copy.edit} direction={direction} onPress={onChooseAddress} />
        <CheckoutCard icon="shopping-bag" title={copy.payment} detail={paymentDetail} editLabel={copy.edit} direction={direction} onPress={onChooseAddress} />
        <MayushText variant="strongBody" color={colors.brand.navy900} style={styles.sectionTitle}>{copy.articles}</MayushText>
        <View style={styles.articleCard}>{cart.lines.map((line, index) => <View key={line.id} style={[styles.articleRow, direction]}><Image source={line.imageUri ? { uri: line.imageUri } : PRODUCT_IMAGES[index % PRODUCT_IMAGES.length]} style={styles.articleImage} /><View style={styles.articleCopy}><MayushText variant="strongBody" color={colors.brand.navy900} numberOfLines={1}>{line.name}</MayushText><MayushText variant="caption" color={colors.neutral.gray700}>{line.variant}</MayushText><MayushText variant="caption" color={colors.neutral.gray700}>{language === 'ar' ? 'الكمية' : 'Quantité'} : {line.quantity}</MayushText></View><MayushText variant="strongBody" color={colors.brand.navy900}>{formatMadPrice(line.unitPriceMad * line.quantity)}</MayushText></View>)}</View>
        <View style={styles.summary}><MayushText variant="strongBody" color={colors.brand.navy900}>{copy.summary}</MayushText><Summary label={copy.subtotal} value={formatMadPrice(totals.subtotalMad)} /><Summary label={copy.reduction} value={`− ${formatMadPrice(totals.discountMad)}`} accent /><Summary label={copy.deliveryFee} value={formatMadPrice(deliveryFeeMad)} /><View style={styles.rule} /><Summary label={copy.total} value={formatMadPrice(getCheckoutGrandTotalMad(totals.totalMad, deliveryFeeMad))} total /></View>
      </ScrollView>
      <View style={styles.bottom}><TouchableOpacity accessibilityRole="button" accessibilityLabel={copy.continue} onPress={onChooseAddress} style={styles.continueButton}><MayushText variant="button" color={colors.surface.white}>{copy.continue}</MayushText></TouchableOpacity></View>
    </View>
  );
};

const Step: React.FC<{ number: string; label: string; active?: boolean }> = ({ number, label, active }) => <View style={styles.step}><View style={[styles.stepCircle, active && styles.stepCircleActive]}><MayushText variant="caption" color={active ? colors.surface.white : colors.neutral.gray700}>{number}</MayushText></View><MayushText variant="caption" color={active ? colors.brand.orange500 : colors.neutral.gray700} align="center" style={styles.stepLabel}>{label}</MayushText></View>;
const CheckoutCard: React.FC<{ icon: React.ComponentProps<typeof MayushIcon>['name']; title: string; detail: string; editLabel: string; direction: typeof styles.rowReverse | undefined; onPress: () => void }> = ({ icon, title, detail, editLabel, direction, onPress }) => <TouchableOpacity accessibilityRole="button" onPress={onPress} style={[styles.checkoutCard, direction]}><View style={styles.cardIcon}><MayushIcon name={icon} size={27} color={colors.brand.navy900} /></View><View style={styles.cardCopy}><MayushText variant="strongBody" color={colors.brand.navy900}>{title}</MayushText><MayushText variant="caption" color={colors.neutral.gray700} style={styles.cardDetail}>{detail}</MayushText></View><MayushText variant="caption" color={colors.brand.orange500} style={styles.edit}>{editLabel}</MayushText><MayushIcon name="chevron-right" size={17} color={colors.neutral.gray700} /></TouchableOpacity>;
const Summary: React.FC<{ label: string; value: string; accent?: boolean; total?: boolean }> = ({ label, value, accent, total }) => <View style={styles.summaryRow}><MayushText variant={total ? 'strongBody' : 'caption'} color={colors.brand.navy900}>{label}</MayushText><MayushText variant={total ? 'sectionTitle' : 'caption'} color={accent ? colors.semantic.success : total ? colors.brand.orange500 : colors.brand.navy900}>{value}</MayushText></View>;

const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: colors.surface.white }, rowReverse: { flexDirection: 'row-reverse' },
  header: { height: 70, paddingHorizontal: 20, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' }, backButton: { width: 32, height: 32, alignItems: 'center', justifyContent: 'center' }, cartIcon: { width: 32, height: 32, alignItems: 'center', justifyContent: 'center', position: 'relative' }, badge: { position: 'absolute', top: -2, right: -1, minWidth: 15, height: 15, borderRadius: 8, alignItems: 'center', justifyContent: 'center', backgroundColor: colors.brand.orange500 },
  title: { marginTop: -3, fontSize: 19, lineHeight: 25 }, steps: { marginTop: 10, marginHorizontal: 22, flexDirection: 'row', alignItems: 'flex-start', justifyContent: 'center' }, step: { width: 53, alignItems: 'center' }, stepCircle: { width: 18, height: 18, borderRadius: 9, alignItems: 'center', justifyContent: 'center', borderWidth: 1, borderColor: colors.neutral.gray300, backgroundColor: colors.surface.white }, stepCircleActive: { borderColor: colors.brand.orange500, backgroundColor: colors.brand.orange500 }, stepLabel: { marginTop: 3, fontSize: 9, lineHeight: 11 }, stepLine: { height: 1, width: 39, marginTop: 9, backgroundColor: colors.brand.orange500 }, stepLineMuted: { height: 1, width: 39, marginTop: 9, backgroundColor: colors.neutral.gray300 },
  content: { paddingHorizontal: 21, paddingTop: 9, paddingBottom: 10, gap: 5 }, checkoutCard: { minHeight: 70, borderWidth: 1, borderColor: '#F0E3D7', borderRadius: 9, padding: 10, flexDirection: 'row', alignItems: 'center', gap: 9 }, cardIcon: { width: 43, height: 43, borderRadius: 7, alignItems: 'center', justifyContent: 'center', backgroundColor: '#FFF2E4' }, cardCopy: { flex: 1 }, cardDetail: { marginTop: 2, lineHeight: 13 }, edit: { fontWeight: '700' }, sectionTitle: { marginTop: 4 }, articleCard: { borderWidth: 1, borderColor: '#F0E3D7', borderRadius: 8, padding: 8, gap: 7 }, articleRow: { minHeight: 50, flexDirection: 'row', alignItems: 'center', gap: 8, borderBottomWidth: 1, borderBottomColor: '#F3EFEA', paddingBottom: 6 }, articleImage: { width: 44, height: 44, borderRadius: 4, backgroundColor: colors.surface.cream }, articleCopy: { flex: 1, gap: 1 },
  summary: { borderWidth: 1, borderColor: '#F0E3D7', borderRadius: 8, padding: 9, gap: 3 }, summaryRow: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' }, rule: { height: 1, backgroundColor: colors.surface.borderWarm, marginVertical: 2 }, bottom: { paddingHorizontal: 21, paddingTop: 5, paddingBottom: 8, backgroundColor: colors.surface.white }, continueButton: { height: 34, borderRadius: 7, alignItems: 'center', justifyContent: 'center', backgroundColor: colors.brand.orange500 },
});
