import React, { useState } from 'react';
import { ScrollView, StyleSheet, TextInput, TouchableOpacity, View } from 'react-native';
import { BuyerOrder } from '../../commerce/orderState';
import { DeliveryIssueRecord, DeliveryRescheduleRequestRecord } from '../../commerce/orderActionState';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { colors } from '../../design-system/tokens/colors';
import { useTheme } from '../../design-system/theme/useTheme';
import { OrderActionButton, OrderCard, OrderScreenHeader } from './OrderScreenComponents';

const date = (value?: string) => value ? new Date(value).toLocaleString('fr-MA', { dateStyle: 'long', timeStyle: 'short' }) : '—';
const DirectionCanvas: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const { isRTL } = useTheme();
  return <View style={[styles.canvas, { direction: isRTL ? 'rtl' : 'ltr' }]}>{children}</View>;
};

export const DeliveryDelayedScreen: React.FC<{ order: BuyerOrder; issue: DeliveryIssueRecord; onBack: () => void; onTrack: () => void; onSupport: () => void }> = ({ order, issue, onBack, onTrack, onSupport }) => (
  <View style={styles.screen}><DirectionCanvas>
    <OrderScreenHeader onBack={onBack} showBell title="Livraison retardée" subtitle="Votre colis prend plus de temps que prévu. Nous vous tiendrons informé(e) dès que possible." />
    <ScrollView contentContainerStyle={styles.content} showsVerticalScrollIndicator={false}>
      <View style={styles.hero}><View style={styles.heroIcon}><MayushIcon name="clock" size={52} color={colors.brand.orange500} /></View></View>
      <OrderCard><MayushText variant="caption" color={colors.neutral.gray700}>RÉFÉRENCE DE COMMANDE</MayushText><MayushText variant="sectionTitle" color={colors.brand.navy900}>{order.orderId}</MayushText><View style={styles.badge}><MayushText variant="strongBody" color={colors.brand.orange500}>En retard</MayushText></View></OrderCard>
      <OrderCard><Fact label="Livraison estimée initiale" value={date(issue.expectedDeliveryAt)} /><Fact label="Nouvelle livraison estimée" value={date(issue.revisedDeliveryAt)} accent /><Fact label="Dernière mise à jour du transporteur" value={`${date(issue.occurredAt)}\nRetard au centre de tri`} /></OrderCard>
      <OrderCard><MayushText variant="sectionTitle" color={colors.brand.navy900}>Pourquoi ce retard ?</MayushText><MayushText variant="body" color={colors.neutral.gray700}>Un volume de colis plus important que prévu ralentit temporairement le traitement au centre de tri.</MayushText></OrderCard>
      <View style={styles.notice}><MayushIcon name="info" size={22} color={colors.brand.orange500} /><MayushText variant="body" color={colors.brand.navy900} style={styles.flex}>Nous sommes désolés pour ce délai. Cette estimation provient d’une fixture frontend et ne remplace pas une confirmation du transporteur.</MayushText></View>
      <OrderActionButton label="Continuer le suivi" icon="truck-outline" onPress={onTrack} primary />
      <OrderActionButton label="Contacter le support" icon="headphones" onPress={onSupport} />
    </ScrollView>
  </DirectionCanvas></View>
);

export const DeliveryFailedScreen: React.FC<{ order: BuyerOrder; issue: DeliveryIssueRecord; request: DeliveryRescheduleRequestRecord | null; onBack: () => void; onReschedule: (slot: string) => Promise<boolean>; onSupport: () => void }> = ({ order, issue, request, onBack, onReschedule, onSupport }) => {
  const [submitting, setSubmitting] = useState(false);
  const submit = async () => { setSubmitting(true); await onReschedule(issue.revisedDeliveryAt || '2026-05-30T09:00:00.000Z'); setSubmitting(false); };
  return <View style={styles.screen}><DirectionCanvas>
    <OrderScreenHeader onBack={onBack} showBell title="Échec de livraison" subtitle="La livraison n’a pas pu avoir lieu. Reprogrammez une nouvelle tentative." />
    <ScrollView contentContainerStyle={styles.content} showsVerticalScrollIndicator={false}>
      <View style={styles.hero}><View style={styles.heroIcon}><MayushIcon name="alert-circle" size={52} color={colors.brand.orange500} /></View></View>
      <OrderCard><Fact label="Tentative échouée" value={date(issue.occurredAt)} /><Fact label="Motif indiqué" value="Destinataire absent" accent /></OrderCard>
      <OrderCard><MayushText variant="caption" color={colors.neutral.gray700}>COMMANDE</MayushText><MayushText variant="sectionTitle" color={colors.brand.navy900}>{order.orderId}</MayushText><MayushText variant="body" color={colors.neutral.gray700}>{order.lines.length} articles · {order.totalMad.toLocaleString('fr-MA')} MAD</MayushText></OrderCard>
      <OrderCard><Fact label="Adresse de livraison" value={`${order.address.addressLine}\n${order.address.zone}, ${order.address.city} ${order.address.postcode} Maroc`} /><Fact label="Nouvelle plage demandée" value={date(issue.revisedDeliveryAt)} accent /></OrderCard>
      <View style={styles.notice}><MayushIcon name="info" size={22} color={colors.brand.orange500} /><MayushText variant="body" color={colors.brand.navy900} style={styles.flex}>{request ? 'Votre demande locale de reprogrammation est enregistrée. La confirmation du transporteur reste en attente.' : 'Action requise : choisissez une nouvelle tentative de livraison.'}</MayushText></View>
      <OrderActionButton label={request ? 'Demande enregistrée localement' : submitting ? 'Enregistrement…' : 'Reprogrammer la livraison'} icon="calendar" onPress={() => { if (!request && !submitting) void submit(); }} primary />
      <OrderActionButton label="Contacter le support" icon="headphones" onPress={onSupport} />
    </ScrollView>
  </DirectionCanvas></View>;
};

export const TrackingUnavailableScreen: React.FC<{ order: BuyerOrder; onBack: () => void; onRefresh: () => void; onDetails: () => void }> = ({ order, onBack, onRefresh, onDetails }) => (
  <View style={styles.screen}><View style={styles.canvas}><OrderScreenHeader onBack={onBack} showBell title="Suivi indisponible" subtitle="Les informations de suivi ne sont pas encore disponibles. Elles apparaîtront dès que votre commande sera expédiée." />
    <ScrollView contentContainerStyle={styles.content}><View style={styles.hero}><View style={styles.heroIcon}><MayushIcon name="box" size={54} color={colors.brand.orange500} /></View></View>
      <OrderCard><MayushText variant="caption" color={colors.neutral.gray700}>RÉFÉRENCE DE COMMANDE</MayushText><MayushText variant="sectionTitle" color={colors.brand.navy900}>{order.orderId}</MayushText><View style={styles.badge}><MayushText variant="strongBody" color={colors.brand.orange500}>En préparation</MayushText></View></OrderCard>
      <View style={styles.notice}><MayushIcon name="info" size={22} color={colors.brand.navy900} /><MayushText variant="body" color={colors.brand.navy900} style={styles.flex}>Votre commande est en cours de préparation. Vous recevrez un e-mail avec votre numéro de suivi dès que votre colis sera expédié.</MayushText></View>
      <OrderActionButton label="Actualiser" icon="rotate-ccw" onPress={onRefresh} primary /><OrderActionButton label="Voir les détails" icon="file-text" onPress={onDetails} />
    </ScrollView></View></View>
);

const Fact: React.FC<{ label: string; value: string; accent?: boolean }> = ({ label, value, accent }) => <View style={styles.fact}><MayushText variant="caption" color={colors.neutral.gray700}>{label.toUpperCase()}</MayushText><MayushText variant="strongBody" color={accent ? colors.brand.orange500 : colors.brand.navy900}>{value}</MayushText></View>;

const styles = StyleSheet.create({ screen: { flex: 1, backgroundColor: '#FFFDF9' }, canvas: { flex: 1, width: '100%', maxWidth: 393, alignSelf: 'center' }, content: { padding: 18, paddingBottom: 32, gap: 10 }, hero: { alignItems: 'center', paddingVertical: 12 }, heroIcon: { width: 112, height: 112, borderRadius: 56, backgroundColor: '#FFF2E6', alignItems: 'center', justifyContent: 'center' }, badge: { position: 'absolute', right: 14, top: 18, backgroundColor: '#FFF2E6', paddingHorizontal: 10, paddingVertical: 6, borderRadius: 8 }, fact: { gap: 3, paddingVertical: 7 }, notice: { flexDirection: 'row', gap: 10, padding: 14, borderRadius: 10, backgroundColor: '#FFF4E9', alignItems: 'center' }, flex: { flex: 1 } });
