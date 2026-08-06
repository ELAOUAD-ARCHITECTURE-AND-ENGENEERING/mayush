import React from 'react';
import { StyleSheet, TouchableOpacity, View } from 'react-native';
import { formatMadPrice } from '../../commerce/cartState';
import { PrototypeOrder } from '../../commerce/orderState';
import { MayushLogo } from '../../design-system/components/brand/MayushLogo';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { colors } from '../../design-system/tokens/colors';

export const PaymentStepIntroScreen: React.FC<{ order: PrototypeOrder; onBack: () => void; onContinue: () => void }> = ({ order, onBack, onContinue }) => (
  <View style={styles.screen} accessibilityLabel="Paiement">
    <View style={styles.header}><TouchableOpacity accessibilityRole="button" onPress={onBack}><MayushIcon name="arrow-left" size={24} color={colors.brand.navy900} /></TouchableOpacity><MayushLogo width={159} height={47} /><View style={styles.headerSpace} /></View>
    <View style={styles.steps}><Step label="Panier" done /><Step label="Livraison" done /><Step label="Paiement" active /></View>
    <View style={styles.content}>
      <View style={styles.icon}><MayushIcon name="shield" size={44} color={colors.brand.orange500} /></View>
      <MayushText variant="pageTitle" color={colors.brand.navy900} align="center" style={styles.title}>Paiement sécurisé</MayushText>
      <MayushText variant="body" color={colors.neutral.gray700} align="center">Vous êtes sur le point de finaliser votre commande de manière sécurisée.</MayushText>
      <View style={styles.card}><MayushText variant="caption" color={colors.neutral.gray700}>Référence de commande</MayushText><MayushText variant="sectionTitle" color={colors.brand.navy900}>{order.id}</MayushText><View style={styles.rule} /><MayushText variant="caption" color={colors.neutral.gray700}>Montant à payer</MayushText><MayushText variant="sectionTitle" color={colors.brand.orange500}>{formatMadPrice(order.totalMad)}</MayushText></View>
    </View>
    <TouchableOpacity accessibilityRole="button" onPress={onContinue} style={styles.primary}><MayushText variant="button" color={colors.surface.white}>Continuer vers le paiement</MayushText><MayushIcon name="arrow-right" size={20} color={colors.surface.white} /></TouchableOpacity>
  </View>
);

const Step: React.FC<{ label: string; done?: boolean; active?: boolean }> = ({ label, done, active }) => <View style={styles.step}><View style={[styles.dot, (done || active) && styles.dotActive]}>{done ? <MayushIcon name="check" size={10} color={colors.surface.white} /> : <MayushText variant="caption" color={colors.brand.orange500}>3</MayushText>}</View><MayushText variant="caption" color={colors.brand.navy900}>{label}</MayushText></View>;
const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: '#FFFDF9' }, header: { height: 75, paddingHorizontal: 22, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' }, headerSpace: { width: 24 },
  steps: { height: 39, paddingHorizontal: 35, borderTopWidth: 1, borderBottomWidth: 1, borderColor: colors.surface.borderWarm, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' }, step: { flexDirection: 'row', alignItems: 'center', gap: 5 }, dot: { width: 18, height: 18, borderRadius: 9, borderWidth: 1, borderColor: colors.brand.orange500, alignItems: 'center', justifyContent: 'center' }, dotActive: { backgroundColor: colors.brand.orange500 },
  content: { flex: 1, alignItems: 'center', paddingHorizontal: 32, paddingTop: 82 }, icon: { width: 98, height: 98, borderRadius: 49, alignItems: 'center', justifyContent: 'center', backgroundColor: '#FFF3E6' }, title: { marginTop: 20, fontSize: 27 }, card: { width: '100%', marginTop: 24, borderRadius: 12, padding: 16, backgroundColor: colors.surface.white, shadowColor: colors.brand.navy900, shadowOpacity: 0.09, shadowRadius: 11, shadowOffset: { width: 0, height: 3 }, elevation: 3 }, rule: { height: 1, marginVertical: 10, backgroundColor: colors.surface.borderWarm }, primary: { height: 47, margin: 22, borderRadius: 9, flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 10, backgroundColor: colors.brand.orange500 },
});

