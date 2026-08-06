import React from 'react';
import { Image, StyleSheet, TouchableOpacity, View } from 'react-native';
import { MayushLogo } from '../../design-system/components/brand/MayushLogo';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { colors } from '../../design-system/tokens/colors';

const GUEST_ART = require('../../../assets/illustrations/account-guest-scene.png');

export interface AuthenticationGateScreenProps {
  onSignIn: () => void;
  onCreateAccount: () => void;
  onContinueAsGuest: () => void;
}

export const AuthenticationGateScreen: React.FC<AuthenticationGateScreenProps> = ({ onSignIn, onCreateAccount, onContinueAsGuest }) => (
  <View style={styles.screen} accessibilityLabel="Bienvenue chez Mayush">
    <MayushLogo width={228} height={68} style={styles.logo} />
    <Image source={GUEST_ART} resizeMode="contain" style={styles.art} />
    <View style={styles.sheet}>
      <View style={styles.iconCircle}><MayushIcon name="sofa" size={29} color={colors.brand.orange500} /></View>
      <MayushText variant="pageTitle" color={colors.brand.navy900} align="center" style={styles.title}>Bienvenue chez Mayush</MayushText>
      <MayushText variant="caption" color={colors.neutral.gray700} align="center" style={styles.subtitle}>Connectez-vous pour retrouver vos favoris, suivre vos commandes et acheter plus rapidement.</MayushText>
      <TouchableOpacity accessibilityRole="button" onPress={onSignIn} style={styles.primary}><MayushIcon name="shield" size={18} color={colors.surface.white} /><MayushText variant="button" color={colors.surface.white}>Se connecter</MayushText></TouchableOpacity>
      <TouchableOpacity accessibilityRole="button" onPress={onCreateAccount} style={styles.secondary}><MayushIcon name="user" size={18} color={colors.brand.orange500} /><MayushText variant="button" color={colors.brand.orange500}>Créer un compte</MayushText></TouchableOpacity>
      <View style={styles.or}><View style={styles.orRule} /><MayushText variant="caption" color={colors.neutral.gray700}>ou</MayushText><View style={styles.orRule} /></View>
      <TouchableOpacity accessibilityRole="button" onPress={onContinueAsGuest} style={styles.guest}><MayushIcon name="user" size={18} color={colors.brand.navy900} /><MayushText variant="button" color={colors.brand.navy900}>Continuer en tant qu’invité</MayushText></TouchableOpacity>
    </View>
    <View style={styles.legal}><MayushIcon name="shield" size={15} color={colors.brand.navy900} /><MayushText variant="caption" color={colors.brand.navy900}>Conditions d’utilisation</MayushText><MayushText variant="caption" color={colors.neutral.gray700}>·</MayushText><MayushText variant="caption" color={colors.brand.navy900}>Politique de confidentialité</MayushText></View>
  </View>
);

const styles = StyleSheet.create({
  screen: { flex: 1, alignItems: 'center', backgroundColor: '#FFF8F0', overflow: 'hidden' },
  logo: { marginTop: 50 },
  art: { width: 330, height: 290, marginTop: 11 },
  sheet: { width: 363, minHeight: 289, marginTop: -70, borderRadius: 20, paddingHorizontal: 18, paddingTop: 17, backgroundColor: colors.surface.white, shadowColor: colors.brand.navy900, shadowOpacity: 0.13, shadowRadius: 16, shadowOffset: { width: 0, height: 4 }, elevation: 4 },
  iconCircle: { width: 42, height: 42, borderRadius: 21, alignSelf: 'center', alignItems: 'center', justifyContent: 'center', backgroundColor: '#FFF3E6' },
  title: { marginTop: 8, fontSize: 23, lineHeight: 28 },
  subtitle: { marginTop: 3, lineHeight: 16 },
  primary: { height: 36, marginTop: 13, borderRadius: 8, flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 9, backgroundColor: colors.brand.orange500 },
  secondary: { height: 36, marginTop: 8, borderRadius: 8, borderWidth: 1, borderColor: colors.brand.orange500, flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 9 },
  or: { height: 22, flexDirection: 'row', alignItems: 'center', gap: 8 },
  orRule: { flex: 1, height: 1, backgroundColor: colors.surface.borderWarm },
  guest: { height: 30, flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 9 },
  legal: { marginTop: 14, flexDirection: 'row', alignItems: 'center', gap: 7 },
});
