import React from 'react';
import { Image, ScrollView, StyleSheet, TouchableOpacity, View } from 'react-native';
import { MayushLogo } from '../../design-system/components/brand/MayushLogo';
import { BottomTabBar, TabKey } from '../../design-system/components/navigation/BottomTabBar';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { useTheme } from '../../design-system/theme/useTheme';
import { colors } from '../../design-system/tokens/colors';

const SUGGESTIONS = [
  { name: 'Canap\u00e9s', count: '128 produits', image: 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?q=80&w=360&auto=format&fit=crop' },
  { name: 'Fauteuils', count: '96 produits', image: 'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?q=80&w=360&auto=format&fit=crop' },
  { name: 'Tables', count: '78 produits', image: 'https://images.unsplash.com/photo-1615066390971-03e4e1c36ddf?q=80&w=360&auto=format&fit=crop' },
  { name: 'Luminaires', count: '64 produits', image: 'https://images.unsplash.com/photo-1507473885765-e6ed057f782c?q=80&w=360&auto=format&fit=crop' },
];
const CART_ARTWORK = require('../../../assets/illustrations/cart-empty-scene.png');

export interface CartScreenProps { onNavigateTab?: (tab: TabKey) => void; onStartShopping?: () => void; onViewWishlist?: () => void; }

export const CartScreen: React.FC<CartScreenProps> = ({ onNavigateTab, onStartShopping, onViewWishlist }) => {
  const { isRTL, language } = useTheme();
  const copy = language === 'ar'
    ? { title: '\u0633\u0644\u062a\u064a', empty: '\u0633\u0644\u062a\u0643 \u0641\u0627\u0631\u063a\u0629', body: '\u0644\u0645 \u062a\u0636\u0641 \u0623\u064a \u0645\u0646\u062a\u062c \u0628\u0639\u062f. \u0627\u0643\u062a\u0634\u0641 \u0645\u0646\u062a\u062c\u0627\u062a\u0646\u0627 \u0648\u0627\u062e\u062a\u0631 \u0645\u0627 \u062a\u062d\u0628.', shop: '\u0627\u0628\u062f\u0623 \u0627\u0644\u062a\u0633\u0648\u0651\u0642', favorites: '\u0639\u0631\u0636 \u0645\u0641\u0636\u0644\u062a\u064a', section: '\u0627\u0642\u062a\u0631\u0627\u062d\u0627\u062a \u0644\u0643', all: '\u0639\u0631\u0636 \u0627\u0644\u0643\u0644' }
    : { title: 'Mon panier', empty: 'Votre panier est vide', body: 'Oups ! Vous n\u2019avez encore rien ajout\u00e9 \u00e0 votre panier.\nD\u00e9couvrez nos produits et trouvez votre coup de c\u0153ur.', shop: 'Commencer mes achats', favorites: 'Voir mes favoris', section: 'Suggestions pour vous', all: 'Voir tout' };

  return (
    <View style={styles.screen} accessibilityLabel={copy.title}>
      <ScrollView showsVerticalScrollIndicator={false} contentContainerStyle={styles.content}>
        <View style={[styles.header, isRTL && styles.rowReverse]}><TouchableOpacity accessibilityRole="button" accessibilityLabel={copy.shop} onPress={onStartShopping} style={styles.headerButton}><MayushIcon name="menu" size={28} color={colors.brand.navy900} /></TouchableOpacity><MayushLogo width={153} height={45} /><View style={styles.headerButton}><MayushIcon name="bell" size={27} color={colors.brand.navy900} /></View></View>
        <MayushText variant="display" color={colors.brand.navy900} style={[styles.pageTitle, isRTL && styles.rtlText]}>{copy.title}</MayushText>
        <View pointerEvents="none" style={styles.emptyHero}><Image source={CART_ARTWORK} resizeMode="contain" style={styles.referenceArtwork} /></View>
        <MayushText variant="pageTitle" color={colors.brand.navy900} align="center" style={[styles.emptyTitle, isRTL && styles.rtlText]}>{copy.empty}</MayushText>
        <MayushText variant="body" color={colors.neutral.gray700} align="center" style={[styles.emptyCopy, isRTL && styles.rtlText]}>{copy.body}</MayushText>
        <TouchableOpacity accessibilityRole="button" accessibilityLabel={copy.shop} activeOpacity={0.84} onPress={onStartShopping} style={styles.primaryButton}><MayushIcon name="shopping-bag" size={24} color={colors.surface.white} /><MayushText variant="button" color={colors.surface.white} style={styles.primaryLabel}>{copy.shop}</MayushText></TouchableOpacity>
        <TouchableOpacity accessibilityRole="button" accessibilityLabel={copy.favorites} activeOpacity={0.84} onPress={onViewWishlist} style={styles.secondaryButton}><MayushIcon name="heart" size={25} color={colors.brand.navy900} /><MayushText variant="button" color={colors.brand.navy900} style={styles.secondaryLabel}>{copy.favorites}</MayushText></TouchableOpacity>
        <View style={[styles.sectionHeader, isRTL && styles.rowReverse]}><MayushText variant="sectionTitle" color={colors.brand.navy900}>{copy.section}</MayushText><TouchableOpacity onPress={onStartShopping} style={styles.allButton}><MayushText variant="body" color={colors.brand.orange500} style={styles.allLabel}>{copy.all}</MayushText><MayushIcon name={isRTL ? 'arrow-left' : 'arrow-right'} size={19} color={colors.brand.orange500} /></TouchableOpacity></View>
        <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.suggestions}>{SUGGESTIONS.map((item) => <TouchableOpacity key={item.name} accessibilityRole="button" accessibilityLabel={item.name} onPress={onStartShopping} activeOpacity={0.82} style={styles.suggestionCard}><Image source={{ uri: item.image }} style={styles.suggestionImage} /><MayushText variant="smallBody" color={colors.brand.navy900} style={styles.suggestionName}>{item.name}</MayushText><MayushText variant="caption" color={colors.neutral.gray700}>{item.count}</MayushText></TouchableOpacity>)}</ScrollView>
      </ScrollView>
      <BottomTabBar activeTab="cart" onTabPress={(tab) => onNavigateTab?.(tab)} cartBadgeCount={0} />
    </View>
  );
};

const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: '#FEFEFE' }, content: { paddingBottom: 26 }, rowReverse: { flexDirection: 'row-reverse' }, rtlText: { writingDirection: 'rtl' },
  header: { height: 79, paddingHorizontal: 21, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' }, headerButton: { width: 43, height: 43, alignItems: 'center', justifyContent: 'center' }, pageTitle: { marginLeft: 24, marginRight: 24, marginTop: 7, fontSize: 33, lineHeight: 39 },
  emptyHero: { height: 250, marginTop: 11, alignItems: 'center', justifyContent: 'center' }, referenceArtwork: { width: '100%', height: '100%' },
  emptyTitle: { marginTop: -1, marginHorizontal: 22, fontSize: 28, lineHeight: 34 }, emptyCopy: { marginTop: 12, marginHorizontal: 26, fontSize: 16, lineHeight: 24 }, primaryButton: { height: 58, marginHorizontal: 22, marginTop: 25, borderRadius: 15, flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 14, backgroundColor: colors.brand.orange500 }, primaryLabel: { fontSize: 18, fontWeight: '700' }, secondaryButton: { height: 57, marginHorizontal: 22, marginTop: 12, borderRadius: 15, borderWidth: 1.5, borderColor: '#AEB5C0', flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 14, backgroundColor: colors.surface.white }, secondaryLabel: { fontSize: 18, fontWeight: '700' },
  sectionHeader: { marginTop: 34, marginHorizontal: 24, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' }, allButton: { flexDirection: 'row', alignItems: 'center', gap: 6 }, allLabel: { fontWeight: '700' }, suggestions: { paddingHorizontal: 24, paddingTop: 16, gap: 13 }, suggestionCard: { width: 133 }, suggestionImage: { width: 133, height: 101, borderRadius: 14, backgroundColor: '#EEE7DE' }, suggestionName: { marginTop: 8, fontWeight: '700' },
});
