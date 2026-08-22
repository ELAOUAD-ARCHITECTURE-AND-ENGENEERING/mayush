import React, { useEffect } from 'react';
import { ActivityIndicator, Linking, StyleSheet, TouchableOpacity, View } from 'react-native';
import { formatMadPrice } from '../../commerce/cartState';
import { PrototypeOrder } from '../../commerce/orderState';
import { MayushLogo } from '../../design-system/components/brand/MayushLogo';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { colors } from '../../design-system/tokens/colors';

interface PaymentFlowProps {
  order: PrototypeOrder;
  onContinue: () => void;
  onCancel?: () => void;
  redirectUrl?: string;
}

const OrderSummary: React.FC<{ order: PrototypeOrder }> = ({ order }) => (
  <View style={styles.card}>
    <SummaryRow icon="clipboard" label="Référence de commande" value={order.id} />
    <View style={styles.rule} />
    <SummaryRow icon="shopping-bag" label="Montant à payer" value={formatMadPrice(order.totalMad)} accent />
  </View>
);

const SummaryRow: React.FC<{ icon: React.ComponentProps<typeof MayushIcon>['name']; label: string; value: string; accent?: boolean }> = ({ icon, label, value, accent }) => (
  <View style={styles.summaryRow}>
    <View style={styles.summaryIcon}><MayushIcon name={icon} size={22} color={colors.brand.navy900} /></View>
    <View><MayushText variant="caption" color={colors.neutral.gray700}>{label}</MayushText><MayushText variant="strongBody" color={accent ? colors.brand.orange500 : colors.brand.navy900}>{value}</MayushText></View>
  </View>
);

const FlowShell: React.FC<{ children: React.ReactNode }> = ({ children }) => <View style={styles.screen}><View style={styles.topAccent} /><MayushLogo width={214} height={63} style={styles.logo} />{children}</View>;

export const SecurePaymentRedirectScreen: React.FC<PaymentFlowProps> = ({ order, onContinue, onCancel }) => (
  <FlowShell>
    <MayushText variant="pageTitle" color={colors.brand.navy900} align="center" style={styles.title}>Paiement sécurisé</MayushText>
    <MayushText variant="body" color={colors.brand.navy900} align="center" style={styles.body}>Vous allez être redirigé vers l’espace de paiement sécurisé de CMI pour finaliser votre achat en toute sécurité.</MayushText>
    <OrderSummary order={order} />
    <View style={styles.securityArt}><View style={styles.securityCircle}><MayushIcon name="shield" size={52} color={colors.brand.orange500} /></View><View style={styles.securityDot} /></View>
    <View style={styles.protected}><MayushIcon name="shield" size={18} color={colors.brand.orange500} /><MayushText variant="caption" color={colors.brand.navy900}>Vos données sont protégées et votre paiement est sécurisé.</MayushText></View>
    <TouchableOpacity accessibilityRole="button" onPress={onContinue} style={styles.primary}><MayushText variant="button" color={colors.surface.white}>Continuer vers le paiement</MayushText><MayushIcon name="arrow-right" size={21} color={colors.surface.white} /></TouchableOpacity>
    {onCancel ? <TouchableOpacity accessibilityRole="button" onPress={onCancel} style={styles.textAction}><MayushText variant="button" color={colors.brand.navy900}>Annuler et revenir à la commande</MayushText></TouchableOpacity> : null}
  </FlowShell>
);

export const SecurePaymentLoadingScreen: React.FC<PaymentFlowProps> = ({ order, onContinue, redirectUrl }) => {
  useEffect(() => {
    let cancelled = false;

    const isValidUrl = redirectUrl && redirectUrl.startsWith('http');

    if (isValidUrl) {
      Linking.openURL(redirectUrl).catch(() => {
        if (!cancelled) onContinue();
      });
    } else {
      const timer = setTimeout(() => {
        if (!cancelled) onContinue();
      }, 700);
      return () => {
        cancelled = true;
        clearTimeout(timer);
      };
    }

    return () => { cancelled = true; };
  }, [onContinue, redirectUrl]);
  return <FlowShell><View style={styles.loading}><ActivityIndicator size="large" color={colors.brand.orange500} /><MayushText variant="pageTitle" color={colors.brand.navy900} align="center" style={styles.loadingTitle}>Connexion au paiement sécurisé…</MayushText><MayushText variant="body" color={colors.neutral.gray700} align="center">Ne fermez pas l’application pendant la redirection.</MayushText><OrderSummary order={order} /></View></FlowShell>;
};

export const PaymentVerificationScreen: React.FC<PaymentFlowProps> = ({ order, onContinue }) => {
  useEffect(() => {
    const timer = setTimeout(onContinue, 900);
    return () => clearTimeout(timer);
  }, [onContinue]);
  return <FlowShell><View style={styles.loading}><View style={styles.progressRing}><ActivityIndicator size="large" color={colors.brand.orange500} /><MayushIcon name="shield" size={37} color={colors.brand.navy900} /></View><MayushText variant="pageTitle" color={colors.brand.navy900} align="center" style={styles.loadingTitle}>Vérification du paiement…</MayushText><MayushText variant="body" color={colors.brand.navy900} align="center">Nous confirmons votre paiement auprès de notre partenaire bancaire de manière sécurisée.</MayushText><OrderSummary order={order} /><View style={styles.notice}><MayushIcon name="shield" size={31} color={colors.brand.orange500} /><MayushText variant="caption" color={colors.brand.navy900} style={styles.noticeText}>Veuillez ne pas réessayer le paiement immédiatement. Toute nouvelle tentative pourrait entraîner un double débit.</MayushText></View></View></FlowShell>;
};

export const CashOnDeliveryConfirmationScreen: React.FC<PaymentFlowProps> = ({ order, onContinue }) => (
  <FlowShell><View style={styles.loading}><View style={styles.successCircle}><MayushIcon name="check" size={54} color={colors.brand.orange500} /></View><MayushText variant="pageTitle" color={colors.brand.navy900} align="center" style={styles.loadingTitle}>Commande confirmée</MayushText><MayushText variant="body" color={colors.neutral.gray700} align="center">Votre commande sera réglée à la livraison.</MayushText><OrderSummary order={order} /><TouchableOpacity accessibilityRole="button" onPress={onContinue} style={styles.primary}><MayushText variant="button" color={colors.surface.white}>Voir la confirmation</MayushText><MayushIcon name="arrow-right" size={21} color={colors.surface.white} /></TouchableOpacity></View></FlowShell>
);

export interface PaymentFailureScreenProps {
  order: PrototypeOrder;
  onRetry: () => void;
  onChangePayment: () => void;
}

export const PaymentFailureScreen: React.FC<PaymentFailureScreenProps> = ({ order, onRetry, onChangePayment }) => (
  <FlowShell><View style={styles.loading}><View style={styles.failureCircle}><MayushIcon name="x" size={44} color={colors.semantic.error} /></View><MayushText variant="pageTitle" color={colors.brand.navy900} align="center" style={styles.loadingTitle}>Le paiement n’a pas abouti</MayushText><MayushText variant="body" color={colors.brand.navy900} align="center">Aucun paiement confirmé n’a été enregistré pour cette tentative. Vous n’avez pas été débité.</MayushText><OrderSummary order={order} /><TouchableOpacity accessibilityRole="button" onPress={onRetry} style={styles.primary}><MayushIcon name="arrow-down-up" size={19} color={colors.surface.white} /><MayushText variant="button" color={colors.surface.white}>Réessayer le paiement</MayushText></TouchableOpacity><TouchableOpacity accessibilityRole="button" onPress={onChangePayment} style={styles.secondary}><MayushIcon name="shopping-bag" size={19} color={colors.brand.navy900} /><MayushText variant="button" color={colors.brand.navy900}>Changer de mode de paiement</MayushText><MayushIcon name="chevron-right" size={18} color={colors.brand.navy900} /></TouchableOpacity></View></FlowShell>
);

export const PaymentCancelledScreen: React.FC<PaymentFlowProps> = ({ order, onContinue }) => (
  <FlowShell><View style={styles.loading}><View style={styles.failureCircle}><MayushIcon name="arrow-left" size={44} color={colors.brand.orange500} /></View><MayushText variant="pageTitle" color={colors.brand.navy900} align="center" style={styles.loadingTitle}>Paiement annulé</MayushText><MayushText variant="body" color={colors.neutral.gray700} align="center">Votre commande est conservée. Vous pouvez reprendre le paiement lorsque vous le souhaitez.</MayushText><OrderSummary order={order} /><TouchableOpacity accessibilityRole="button" onPress={onContinue} style={styles.primary}><MayushText variant="button" color={colors.surface.white}>Reprendre le paiement</MayushText><MayushIcon name="arrow-right" size={21} color={colors.surface.white} /></TouchableOpacity></View></FlowShell>
);

const styles = StyleSheet.create({
  screen: { flex: 1, alignItems: 'center', paddingHorizontal: 37, backgroundColor: '#FFFDF9', overflow: 'hidden' },
  topAccent: { position: 'absolute', top: 0, left: 0, width: 124, height: 124, borderBottomRightRadius: 124, backgroundColor: colors.brand.orange500 },
  logo: { marginTop: 65 },
  title: { marginTop: 22, fontSize: 27, lineHeight: 33 },
  body: { marginTop: 10, lineHeight: 20 },
  card: { width: '100%', marginTop: 17, borderRadius: 12, padding: 15, backgroundColor: colors.surface.white, shadowColor: colors.brand.navy900, shadowOpacity: 0.1, shadowRadius: 12, shadowOffset: { width: 0, height: 4 }, elevation: 3 },
  summaryRow: { minHeight: 47, flexDirection: 'row', alignItems: 'center', gap: 12 },
  summaryIcon: { width: 44, height: 44, borderRadius: 22, alignItems: 'center', justifyContent: 'center', backgroundColor: '#FFF5E9' },
  rule: { height: 1, backgroundColor: colors.surface.borderWarm, marginVertical: 6 },
  securityArt: { width: 120, height: 102, marginTop: 18, alignItems: 'center', justifyContent: 'center' },
  securityCircle: { width: 80, height: 80, borderWidth: 2, borderColor: colors.brand.orange500, borderRadius: 40, alignItems: 'center', justifyContent: 'center' },
  securityDot: { position: 'absolute', right: 8, top: 21, width: 15, height: 15, borderRadius: 8, backgroundColor: colors.brand.orange500 },
  protected: { width: '100%', marginTop: 0, flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 8 },
  primary: { width: '100%', height: 47, marginTop: 19, borderRadius: 10, flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 10, backgroundColor: colors.brand.orange500 },
  textAction: { height: 40, marginTop: 4, justifyContent: 'center' },
  loading: { width: '100%', flex: 1, alignItems: 'center', paddingTop: 39 },
  loadingTitle: { marginTop: 22, fontSize: 25, lineHeight: 31 },
  progressRing: { width: 126, height: 126, borderRadius: 63, borderWidth: 4, borderColor: '#FFE2BE', alignItems: 'center', justifyContent: 'center' },
  notice: { width: '100%', marginTop: 14, borderRadius: 8, padding: 13, flexDirection: 'row', alignItems: 'center', gap: 10, backgroundColor: '#FFF3E5' },
  noticeText: { flex: 1, lineHeight: 15 },
  successCircle: { width: 104, height: 104, borderRadius: 52, alignItems: 'center', justifyContent: 'center', backgroundColor: '#FFF5E9' },
  failureCircle: { width: 82, height: 82, borderRadius: 41, borderWidth: 2, borderColor: colors.semantic.error, alignItems: 'center', justifyContent: 'center', backgroundColor: '#FFF0ED' },
  secondary: { width: '100%', height: 43, marginTop: 9, borderRadius: 8, borderWidth: 1, borderColor: colors.brand.orange500, paddingHorizontal: 13, flexDirection: 'row', alignItems: 'center', gap: 9 },
});
