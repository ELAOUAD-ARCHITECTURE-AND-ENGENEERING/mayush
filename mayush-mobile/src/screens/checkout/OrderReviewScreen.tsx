import React, { useState } from 'react';
import { Image, ScrollView, StyleSheet, TouchableOpacity, View } from 'react-native';
import { CartState, formatMadPrice, getCartTotals } from '../../commerce/cartState';
import { DeliveryMethod, PaymentMethod, SavedAddress } from '../../commerce/checkoutState';
import { MayushLogo } from '../../design-system/components/brand/MayushLogo';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { colors } from '../../design-system/tokens/colors';

const FALLBACK_PRODUCT = require('../../../assets/reference-art/home-new-luna.png');

export interface OrderReviewScreenProps {
  cart: CartState;
  address: SavedAddress;
  deliveryMethod: DeliveryMethod;
  paymentMethod: PaymentMethod;
  onBack: () => void;
  onConfirm: () => void;
}

const deliveryLabel = (method: DeliveryMethod) => method === 'express' ? 'Livraison express' : method === 'relay' ? 'Point relais' : 'Livraison standard (2 à 4 jours ouvrés)';
const paymentLabel = (method: PaymentMethod) => method === 'cmi' ? 'Visa •••• 4242' : method === 'wallet' ? 'Portefeuille Mayush' : 'Paiement à la livraison';

export const OrderReviewScreen: React.FC<OrderReviewScreenProps> = ({ cart, address, deliveryMethod, paymentMethod, onBack, onConfirm }) => {
  const totals = getCartTotals(cart);
  const [termsAccepted, setTermsAccepted] = useState(false);
  return <View style={styles.screen} accessibilityLabel="Vérifier et confirmer">
    <View style={styles.header}><TouchableOpacity accessibilityRole="button" accessibilityLabel="Retour" onPress={onBack}><MayushIcon name="arrow-left" size={24} color={colors.brand.navy900} /></TouchableOpacity><MayushLogo width={155} height={45} /><View style={styles.headerSpace} /></View>
    <View style={styles.steps}><Step label="Panier" complete /><Step label="Livraison" complete /><Step label="Paiement" active /></View>
    <ScrollView showsVerticalScrollIndicator={false} contentContainerStyle={styles.content}>
      <MayushText variant="pageTitle" color={colors.brand.navy900} style={styles.title}>Vérifier et confirmer</MayushText>
      <MayushText variant="caption" color={colors.neutral.gray700}>Vérifiez vos informations avant de payer</MayushText>
      <ReviewCard icon="shopping-bag" title="Produits" action="2 vendeurs · articles">
        {cart.lines.map((line) => <View key={line.id} style={styles.line}><Image source={line.imageUri ? { uri: line.imageUri } : FALLBACK_PRODUCT} style={styles.image} /><View style={styles.lineCopy}><MayushText variant="caption" color={colors.brand.navy900} numberOfLines={1}>{line.name}</MayushText><MayushText variant="caption" color={colors.neutral.gray700}>{line.variant} × {line.quantity}</MayushText></View><MayushText variant="caption" color={colors.brand.navy900}>{formatMadPrice(line.quantity * line.unitPriceMad)}</MayushText></View>)}
      </ReviewCard>
      <ReviewCard icon="map-pin" title="Adresse de livraison"><MayushText variant="caption" color={colors.neutral.gray700}>{address.addressLine}{'\n'}{address.postcode} {address.city}, Maroc</MayushText></ReviewCard>
      <ReviewCard icon="truck-outline" title="Méthode de livraison" action="Gratuit"><MayushText variant="caption" color={colors.neutral.gray700}>{deliveryLabel(deliveryMethod)}</MayushText></ReviewCard>
      <ReviewCard icon="shopping-bag" title="Méthode de paiement"><MayushText variant="caption" color={colors.neutral.gray700}>{paymentLabel(paymentMethod)}</MayushText></ReviewCard>
      <View style={styles.totals}><Total label="Sous-total" value={formatMadPrice(totals.subtotalMad)} /><Total label="Réduction" value="− 0 MAD" green /><Total label="Livraison" value="Gratuit" green /><View style={styles.totalRule} /><Total label="Total à payer" value={formatMadPrice(totals.subtotalMad)} strong /></View>
      <TouchableOpacity accessibilityRole="checkbox" accessibilityState={{ checked: termsAccepted }} onPress={() => setTermsAccepted(!termsAccepted)} style={styles.terms}><View style={[styles.checkbox, termsAccepted && styles.checkboxChecked]}>{termsAccepted ? <MayushIcon name="check" size={14} color={colors.surface.white} /> : null}</View><MayushText variant="caption" color={colors.neutral.gray700} style={styles.termsCopy}>J’accepte les Conditions Générales de Vente et la Politique de confidentialité.</MayushText></TouchableOpacity>
      <TouchableOpacity disabled={!termsAccepted} accessibilityRole="button" onPress={onConfirm} style={[styles.payButton, !termsAccepted && styles.payButtonDisabled]}><MayushIcon name="shopping-bag" size={22} color={colors.surface.white} /><MayushText variant="button" color={colors.surface.white}>Payer {formatMadPrice(totals.subtotalMad)}</MayushText></TouchableOpacity>
      <View style={styles.footer}><MayushIcon name="shield" size={16} color={colors.brand.navy900} /><MayushText variant="caption" color={colors.brand.navy900}>Paiement sécurisé 100%</MayushText><MayushIcon name="arrow-down-up" size={16} color={colors.brand.navy900} /><MayushText variant="caption" color={colors.brand.navy900}>Satisfait ou remboursé</MayushText></View>
    </ScrollView>
  </View>;
};

const Step: React.FC<{ label: string; complete?: boolean; active?: boolean }> = ({ label, complete, active }) => <View style={styles.step}><View style={[styles.stepDot, (complete || active) && styles.stepDotActive]}>{complete ? <MayushIcon name="check" size={10} color={colors.surface.white} /> : <MayushText variant="caption" color={active ? colors.brand.orange500 : colors.neutral.gray700}>3</MayushText>}</View><MayushText variant="caption" color={colors.brand.navy900}>{label}</MayushText></View>;
const ReviewCard: React.FC<{ icon: React.ComponentProps<typeof MayushIcon>['name']; title: string; action?: string; children: React.ReactNode }> = ({ icon, title, action, children }) => <View style={styles.card}><View style={styles.cardHead}><View style={styles.cardTitle}><MayushIcon name={icon} size={17} color={colors.brand.orange500} /><MayushText variant="strongBody" color={colors.brand.navy900}>{title}</MayushText></View>{action ? <MayushText variant="caption" color={action === 'Gratuit' ? colors.semantic.success : colors.neutral.gray700}>{action}</MayushText> : <MayushIcon name="chevron-right" size={16} color={colors.neutral.gray700} />}</View><View style={styles.cardContent}>{children}</View></View>;
const Total: React.FC<{ label: string; value: string; strong?: boolean; green?: boolean }> = ({ label, value, strong, green }) => <View style={styles.total}><MayushText variant={strong ? 'strongBody' : 'caption'} color={colors.brand.navy900}>{label}</MayushText><MayushText variant={strong ? 'sectionTitle' : 'caption'} color={green ? colors.semantic.success : strong ? colors.brand.orange500 : colors.brand.navy900}>{value}</MayushText></View>;

const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: '#FFFDF9' }, header: { height: 72, paddingHorizontal: 20, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' }, headerSpace: { width: 24 }, steps: { height: 37, paddingHorizontal: 34, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', borderTopWidth: 1, borderTopColor: colors.surface.borderWarm, borderBottomWidth: 1, borderBottomColor: colors.surface.borderWarm }, step: { flexDirection: 'row', alignItems: 'center', gap: 5 }, stepDot: { width: 18, height: 18, borderRadius: 9, borderWidth: 1, borderColor: colors.brand.orange500, alignItems: 'center', justifyContent: 'center' }, stepDotActive: { backgroundColor: colors.brand.orange500 }, content: { paddingHorizontal: 26, paddingTop: 17, paddingBottom: 24 }, title: { fontSize: 24, lineHeight: 30 }, card: { marginTop: 8, borderWidth: 1, borderColor: '#F1E7DC', borderRadius: 9, padding: 10, backgroundColor: colors.surface.white }, cardHead: { minHeight: 22, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' }, cardTitle: { flexDirection: 'row', alignItems: 'center', gap: 7 }, cardContent: { marginTop: 7 }, line: { minHeight: 41, flexDirection: 'row', alignItems: 'center', gap: 8, marginTop: 4 }, image: { width: 43, height: 35, borderRadius: 4, backgroundColor: colors.surface.cream }, lineCopy: { flex: 1 }, totals: { marginTop: 8, borderWidth: 1, borderColor: '#F1E7DC', borderRadius: 9, padding: 10, backgroundColor: colors.surface.white }, total: { flexDirection: 'row', justifyContent: 'space-between', marginTop: 3 }, totalRule: { height: 1, marginTop: 6, backgroundColor: colors.surface.borderWarm }, terms: { marginTop: 10, flexDirection: 'row', alignItems: 'flex-start', gap: 8 }, checkbox: { width: 17, height: 17, borderRadius: 3, borderWidth: 1, borderColor: colors.brand.orange500, alignItems: 'center', justifyContent: 'center' }, checkboxChecked: { backgroundColor: colors.brand.orange500 }, termsCopy: { flex: 1, lineHeight: 14 }, payButton: { height: 39, marginTop: 10, borderRadius: 8, flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 10, backgroundColor: colors.brand.orange500 }, payButtonDisabled: { opacity: 0.45 }, footer: { marginTop: 12, flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 5, flexWrap: 'wrap' },
});
