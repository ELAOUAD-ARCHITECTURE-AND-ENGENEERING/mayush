import React, { useEffect } from 'react';
import { ActivityIndicator, StyleSheet, View } from 'react-native';
import { PrototypeOrder } from '../../commerce/orderState';
import { MayushLogo } from '../../design-system/components/brand/MayushLogo';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { colors } from '../../design-system/tokens/colors';

export const OrderProcessingScreen: React.FC<{ order: PrototypeOrder; onFinish: () => void }> = ({ order, onFinish }) => {
  useEffect(() => {
    const timer = setTimeout(onFinish, 850);
    return () => clearTimeout(timer);
  }, [onFinish]);
  return <View style={styles.screen} accessibilityLabel="Création de votre commande">
    <MayushLogo width={246} height={72} style={styles.logo} />
    <View style={styles.ring}><ActivityIndicator size="large" color={colors.brand.orange500} /><MayushIcon name="shopping-cart" size={37} color={colors.brand.navy900} /></View>
    <MayushText variant="pageTitle" color={colors.brand.navy900} align="center" style={styles.title}>Création de votre commande…</MayushText>
    <MayushText variant="body" color={colors.neutral.gray700} align="center">Ne fermez pas l’application.</MayushText>
    <View style={styles.divider} />
    <MayushText variant="body" color={colors.brand.navy900} align="center" style={styles.body}>Votre commande est en cours de traitement en toute sécurité. Nous empêchons tout paiement en double.</MayushText>
    <View style={styles.reference}><MayushIcon name="clipboard" size={24} color={colors.brand.navy900} /><View><MayushText variant="caption" color={colors.neutral.gray700}>Référence de commande</MayushText><MayushText variant="strongBody" color={colors.brand.orange500}>#{order.id}</MayushText></View></View>
    <View style={styles.secure}><View style={styles.secureCircle}><MayushIcon name="shield" size={33} color={colors.brand.orange500} /></View><MayushText variant="strongBody" color={colors.brand.navy900}>Paiement sécurisé</MayushText><MayushText variant="caption" color={colors.neutral.gray700} align="center">Vos informations sont protégées et votre paiement est 100% sécurisé.</MayushText></View>
  </View>;
};

const styles = StyleSheet.create({
  screen: { flex: 1, alignItems: 'center', paddingHorizontal: 36, backgroundColor: '#FFFDF9' },
  logo: { marginTop: 72 },
  ring: { width: 94, height: 94, marginTop: 48, borderRadius: 47, borderWidth: 4, borderColor: '#FFE0B7', alignItems: 'center', justifyContent: 'center' },
  title: { marginTop: 17, fontSize: 27, lineHeight: 31 },
  divider: { width: 35, height: 2, marginTop: 18, backgroundColor: colors.brand.orange500 },
  body: { marginTop: 14, lineHeight: 20 },
  reference: { minWidth: 177, height: 50, marginTop: 18, paddingHorizontal: 12, borderWidth: 1, borderColor: '#FFD6A2', borderRadius: 6, flexDirection: 'row', alignItems: 'center', gap: 10 },
  secure: { marginTop: 21, alignItems: 'center', gap: 3 },
  secureCircle: { width: 57, height: 57, borderRadius: 29, alignItems: 'center', justifyContent: 'center', backgroundColor: '#FFF5E9' },
});
