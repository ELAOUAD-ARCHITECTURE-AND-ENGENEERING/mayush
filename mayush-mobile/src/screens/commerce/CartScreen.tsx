import React from 'react';
import { Image, ScrollView, StyleSheet, TouchableOpacity, View } from 'react-native';
import { CartLine, CartState, formatMadPrice, getCartTotals } from '../../commerce/cartState';
import { MayushLogo } from '../../design-system/components/brand/MayushLogo';
import { BottomTabBar, TabKey } from '../../design-system/components/navigation/BottomTabBar';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { useTheme } from '../../design-system/theme/useTheme';
import { colors } from '../../design-system/tokens/colors';

const FALLBACK_IMAGES = [
  require('../../../assets/reference-art/home-new-luna.png'),
  require('../../../assets/reference-art/home-new-nori.png'),
  require('../../../assets/reference-art/home-new-kyoto.png'),
];

export interface CartScreenProps {
  cart?: CartState;
  onNavigateTab?: (tab: TabKey) => void;
  onStartShopping?: () => void;
  onViewWishlist?: () => void;
  onUpdateQuantity?: (lineId: string, quantity: number) => void;
  onCheckout?: () => void;
}

export const CartScreen: React.FC<CartScreenProps> = ({
  cart,
  onNavigateTab,
  onStartShopping,
  onViewWishlist,
  onUpdateQuantity,
  onCheckout,
}) => {
  const { isRTL, language } = useTheme();
  const activeCart = cart || { lines: [] };
  const totals = getCartTotals(activeCart);
  const copy = language === 'ar'
    ? {
      title: '\u0633\u0644\u062a\u064a',
      articles: '\u0645\u0646\u062a\u062c\u0627\u062a',
      empty: '\u0633\u0644\u062a\u0643 \u0641\u0627\u0631\u063a\u0629',
      emptyCopy: '\u0644\u0645 \u062a\u0636\u0641 \u0623\u064a \u0645\u0646\u062a\u062c \u0628\u0639\u062f.',
      shop: '\u0627\u0628\u062f\u0623 \u0627\u0644\u062a\u0633\u0648\u0651\u0642',
      variant: '\u062a\u0639\u062f\u064a\u0644 \u0627\u0644\u062e\u064a\u0627\u0631',
      later: '\u062d\u0641\u0638 \u0644\u0648\u0642\u062a \u0644\u0627\u062d\u0642',
      remove: '\u062d\u0630\u0641',
      promo: '\u0647\u0644 \u0644\u062f\u064a\u0643 \u0631\u0645\u0632 \u062a\u062e\u0641\u064a\u0636\u061f',
      promoHint: '\u0623\u062f\u062e\u0644 \u0631\u0645\u0632\u0643 \u0644\u062a\u0637\u0628\u064a\u0642 \u0627\u0644\u062e\u0635\u0645',
      add: '\u0625\u0636\u0627\u0641\u0629',
      summary: '\u0645\u0644\u062e\u0635 \u0627\u0644\u0637\u0644\u0628',
      subtotal: '\u0627\u0644\u0645\u062c\u0645\u0648\u0639 \u0627\u0644\u0641\u0631\u0639\u064a',
      reduction: '\u0627\u0644\u062e\u0635\u0645',
      delivery: '\u0627\u0644\u062a\u0648\u0635\u064a\u0644',
      total: '\u0627\u0644\u0625\u062c\u0645\u0627\u0644\u064a \u0627\u0644\u0645\u0624\u0642\u062a',
      deliveryHint: '\u064a\u062d\u0633\u0628 \u0641\u064a \u0627\u0644\u062e\u0637\u0648\u0629 \u0627\u0644\u062a\u0627\u0644\u064a\u0629',
      checkout: '\u0625\u062a\u0645\u0627\u0645 \u0627\u0644\u0637\u0644\u0628',
    }
    : {
      title: 'Mon panier',
      articles: 'articles',
      empty: 'Votre panier est vide',
      emptyCopy: 'Vous n\u2019avez encore rien ajouté à votre panier.',
      shop: 'Commencer mes achats',
      variant: 'Modifier la variante',
      later: 'Enregistrer pour plus tard',
      remove: 'Supprimer',
      promo: 'Vous avez un code promo ?',
      promoHint: 'Entrez votre code pour appliquer la réduction',
      add: 'Ajouter',
      summary: 'Résumé de la commande',
      subtotal: 'Sous-total',
      reduction: 'Réduction',
      delivery: 'Livraison',
      total: 'Total provisoire',
      deliveryHint: 'Calculée à l’étape suivante',
      checkout: 'Passer à la commande',
    };
  const direction = isRTL ? styles.rowReverse : undefined;

  if (!activeCart.lines.length) {
    return (
      <View style={styles.screen} accessibilityLabel={copy.title}>
        <View style={[styles.header, direction]}><MayushLogo width={109} height={32} /><View style={[styles.headerActions, direction]}><MayushIcon name="search" size={25} color={colors.brand.navy900} /><MayushIcon name="bell" size={25} color={colors.brand.navy900} /></View></View>
        <View style={styles.emptyState}><MayushIcon name="shopping-cart" size={58} color={colors.brand.orange500} /><MayushText variant="pageTitle" color={colors.brand.navy900} align="center" style={styles.emptyTitle}>{copy.empty}</MayushText><MayushText variant="body" color={colors.neutral.gray700} align="center" style={styles.emptyCopy}>{copy.emptyCopy}</MayushText><TouchableOpacity onPress={onStartShopping} style={styles.emptyButton}><MayushText variant="button" color={colors.surface.white}>{copy.shop}</MayushText></TouchableOpacity></View>
        <BottomTabBar activeTab="cart" onTabPress={(tab) => onNavigateTab?.(tab)} cartBadgeCount={0} />
      </View>
    );
  }

  return (
    <View style={styles.screen} accessibilityLabel={copy.title}>
      <View style={[styles.header, direction]}><MayushLogo width={109} height={32} /><View style={[styles.headerActions, direction]}><TouchableOpacity accessibilityRole="button" accessibilityLabel={language === 'ar' ? '\u0628\u062d\u062b' : 'Rechercher'}><MayushIcon name="search" size={25} color={colors.brand.navy900} /></TouchableOpacity><TouchableOpacity accessibilityRole="button" accessibilityLabel={language === 'ar' ? '\u0625\u0634\u0639\u0627\u0631\u0627\u062a' : 'Notifications'}><MayushIcon name="bell" size={25} color={colors.brand.navy900} /></TouchableOpacity></View></View>
      <ScrollView showsVerticalScrollIndicator={false} contentContainerStyle={styles.content}>
        <MayushText variant="pageTitle" color={colors.brand.navy900} style={[styles.title, isRTL && styles.rtlText]}>{copy.title}</MayushText>
        <MayushText variant="body" color={colors.neutral.gray700} style={[styles.articleCount, isRTL && styles.rtlText]}>{totals.itemCount} {copy.articles}</MayushText>
        <View style={styles.lineList}>{activeCart.lines.map((line, index) => <CartLineCard key={line.id} line={line} fallbackImage={FALLBACK_IMAGES[index % FALLBACK_IMAGES.length]} direction={direction} copy={copy} isRTL={isRTL} onUpdateQuantity={onUpdateQuantity} />)}</View>
        <TouchableOpacity accessibilityRole="button" accessibilityLabel={copy.promo} style={[styles.promoCard, direction]}><MayushIcon name="tag" size={27} color={colors.brand.orange500} /><View style={styles.promoCopy}><MayushText variant="strongBody" color={colors.brand.navy900}>{copy.promo}</MayushText><MayushText variant="caption" color={colors.neutral.gray700}>{copy.promoHint}</MayushText></View><MayushText variant="strongBody" color={colors.brand.orange500}>{copy.add}</MayushText><MayushIcon name={isRTL ? 'chevron-left' : 'chevron-right'} size={20} color={colors.brand.orange500} /></TouchableOpacity>
        <View style={styles.summary}><MayushText variant="strongBody" color={colors.brand.navy900}>{copy.summary}</MayushText><SummaryRow label={copy.subtotal} value={formatMadPrice(totals.subtotalMad)} /><SummaryRow label={copy.reduction} value="- 0 MAD" valueColor={colors.semantic.success} /><SummaryRow label={copy.delivery} value={copy.deliveryHint} muted /><View style={styles.summaryRule} /><SummaryRow label={copy.total} value={formatMadPrice(totals.subtotalMad)} total /></View>
      </ScrollView>
      <View style={styles.checkoutShell}><TouchableOpacity accessibilityRole="button" accessibilityLabel={copy.checkout} onPress={onCheckout} style={styles.checkoutButton}><MayushIcon name="shopping-bag" size={22} color={colors.surface.white} /><MayushText variant="button" color={colors.surface.white}>{copy.checkout}</MayushText></TouchableOpacity></View>
      <BottomTabBar activeTab="cart" onTabPress={(tab) => onNavigateTab?.(tab)} cartBadgeCount={totals.itemCount} />
    </View>
  );
};

const CartLineCard: React.FC<{ line: CartLine; fallbackImage: number; direction: typeof styles.rowReverse | undefined; copy: Record<string, string>; isRTL: boolean; onUpdateQuantity?: (lineId: string, quantity: number) => void }> = ({ line, fallbackImage, direction, copy, isRTL, onUpdateQuantity }) => (
  <View style={styles.lineCard}>
    <View style={[styles.lineMain, direction]}><Image source={line.imageUri ? { uri: line.imageUri } : fallbackImage} style={styles.lineImage} /><View style={styles.lineDetails}><View style={[styles.namePrice, direction]}><MayushText variant="strongBody" color={colors.brand.navy900} numberOfLines={2} style={styles.lineName}>{line.name}</MayushText><MayushText variant="strongBody" color={colors.brand.navy900} style={styles.linePrice}>{formatMadPrice(line.unitPriceMad)}</MayushText></View><MayushText variant="caption" color={colors.neutral.gray700}>{line.variant}</MayushText><MayushText variant="caption" color={colors.neutral.gray700}>{isRTL ? '\u0627\u0644\u0645\u0642\u0627\u0633: \u062d\u0633\u0628 \u0627\u0644\u062e\u064a\u0627\u0631' : 'Dimensions : selon la variante'}</MayushText><View style={[styles.sellerQuantity, direction]}><MayushText variant="caption" color={colors.brand.orange500}>{isRTL ? '\u0628\u0627\u0626\u0639 Mayush' : 'Mayush Design'}</MayushText><View style={[styles.quantityControl, direction]}><TouchableOpacity accessibilityRole="button" accessibilityLabel={isRTL ? '\u062a\u0642\u0644\u064a\u0644 \u0627\u0644\u0643\u0645\u064a\u0629' : 'Réduire la quantité'} onPress={() => onUpdateQuantity?.(line.id, line.quantity - 1)} style={styles.quantityButton}><MayushIcon name="minus" size={16} color={colors.brand.navy900} /></TouchableOpacity><MayushText variant="strongBody" color={colors.brand.navy900}>{line.quantity}</MayushText><TouchableOpacity accessibilityRole="button" accessibilityLabel={isRTL ? '\u0632\u064a\u0627\u062f\u0629 \u0627\u0644\u0643\u0645\u064a\u0629' : 'Augmenter la quantité'} onPress={() => onUpdateQuantity?.(line.id, line.quantity + 1)} style={styles.quantityButton}><MayushIcon name="plus" size={16} color={colors.brand.navy900} /></TouchableOpacity></View></View></View></View>
    <View style={[styles.lineActions, direction]}><TouchableOpacity accessibilityRole="button" style={styles.lineAction}><MayushIcon name="edit-2" size={15} color={colors.neutral.gray700} /><MayushText variant="caption" color={colors.neutral.gray700}>{copy.variant}</MayushText></TouchableOpacity><TouchableOpacity accessibilityRole="button" style={styles.lineAction}><MayushIcon name="bookmark" size={15} color={colors.neutral.gray700} /><MayushText variant="caption" color={colors.neutral.gray700}>{copy.later}</MayushText></TouchableOpacity><TouchableOpacity accessibilityRole="button" onPress={() => onUpdateQuantity?.(line.id, 0)} style={styles.lineAction}><MayushIcon name="trash-2" size={15} color={colors.semantic.error} /><MayushText variant="caption" color={colors.semantic.error}>{copy.remove}</MayushText></TouchableOpacity></View>
  </View>
);

const SummaryRow: React.FC<{ label: string; value: string; valueColor?: string; muted?: boolean; total?: boolean }> = ({ label, value, valueColor, muted, total }) => <View style={styles.summaryRow}><MayushText variant={total ? 'strongBody' : 'caption'} color={colors.brand.navy900}>{label}</MayushText><MayushText variant={total ? 'sectionTitle' : 'caption'} color={valueColor || (muted ? colors.neutral.gray700 : colors.brand.navy900)}>{value}</MayushText></View>;

const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: colors.surface.white }, rowReverse: { flexDirection: 'row-reverse' }, rtlText: { writingDirection: 'rtl', textAlign: 'right' },
  header: { height: 70, paddingHorizontal: 16, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' }, headerActions: { flexDirection: 'row', alignItems: 'center', gap: 16 },
  content: { paddingHorizontal: 15, paddingBottom: 15 }, title: { marginTop: 2, fontSize: 21, lineHeight: 27 }, articleCount: { marginTop: -1, fontSize: 12, lineHeight: 17 }, lineList: { marginTop: 8, gap: 10 },
  lineCard: { borderRadius: 8, borderWidth: 1, borderColor: '#F0ECE6', overflow: 'hidden', backgroundColor: colors.surface.white, shadowColor: colors.brand.navy900, shadowOpacity: 0.04, shadowRadius: 7, shadowOffset: { width: 0, height: 2 }, elevation: 1 }, lineMain: { flexDirection: 'row', gap: 9, padding: 8 }, lineImage: { width: 92, height: 76, borderRadius: 6, backgroundColor: colors.surface.cream }, lineDetails: { flex: 1, gap: 2 }, namePrice: { flexDirection: 'row', alignItems: 'flex-start', gap: 8 }, lineName: { flex: 1, fontSize: 11, lineHeight: 15 }, linePrice: { fontSize: 11, lineHeight: 15 }, sellerQuantity: { marginTop: 'auto', flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' }, quantityControl: { height: 26, minWidth: 87, paddingHorizontal: 3, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', borderWidth: 1, borderColor: colors.surface.borderWarm, borderRadius: 5 }, quantityButton: { width: 25, height: 24, alignItems: 'center', justifyContent: 'center' }, lineActions: { minHeight: 30, flexDirection: 'row', alignItems: 'center', borderTopWidth: 1, borderTopColor: '#F0ECE6' }, lineAction: { flex: 1, minHeight: 22, flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 4, borderRightWidth: 1, borderRightColor: '#F0ECE6' },
  promoCard: { minHeight: 41, marginTop: 10, borderRadius: 7, borderWidth: 1, borderStyle: 'dashed', borderColor: colors.brand.orange500, paddingHorizontal: 12, flexDirection: 'row', alignItems: 'center', gap: 9 }, promoCopy: { flex: 1 }, summary: { marginTop: 8, borderRadius: 7, padding: 11, backgroundColor: '#FBFBFC', borderWidth: 1, borderColor: '#F0ECE6', gap: 5 }, summaryRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' }, summaryRule: { height: 1, backgroundColor: colors.surface.borderWarm, marginVertical: 1 }, checkoutShell: { paddingHorizontal: 15, paddingTop: 6, paddingBottom: 7, backgroundColor: colors.surface.white }, checkoutButton: { height: 37, borderRadius: 7, flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 8, backgroundColor: colors.brand.orange500 },
  emptyState: { flex: 1, alignItems: 'center', justifyContent: 'center', paddingHorizontal: 35, gap: 12 }, emptyTitle: { marginTop: 12 }, emptyCopy: { lineHeight: 22 }, emptyButton: { height: 48, minWidth: 210, marginTop: 12, borderRadius: 12, alignItems: 'center', justifyContent: 'center', backgroundColor: colors.brand.orange500 },
});
