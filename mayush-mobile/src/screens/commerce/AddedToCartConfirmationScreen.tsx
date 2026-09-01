import React from 'react';
import { Image, StyleSheet, TouchableOpacity, View } from 'react-native';
import { CartState, formatMadPrice, getCartTotals } from '../../commerce/cartState';
import { MayushLogo } from '../../design-system/components/brand/MayushLogo';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { useTheme } from '../../design-system/theme/useTheme';
import { colors } from '../../design-system/tokens/colors';

const BACKDROP_HERO = require('../../../assets/reference-art/home-hero-scene.png');
const FALLBACK_PRODUCT_IMAGE = require('../../../assets/reference-art/home-new-luna.png');

export interface AddedToCartConfirmationScreenProps {
  cart: CartState;
  onViewCart: () => void;
}

export const AddedToCartConfirmationScreen: React.FC<AddedToCartConfirmationScreenProps> = ({ cart, onViewCart }) => {
  const { isRTL, language } = useTheme();
  const lastLine = cart.lines[cart.lines.length - 1];
  const totals = getCartTotals(cart);
  const copy = language === 'ar'
    ? {
      added: '\u062a\u0645\u062a \u0625\u0636\u0627\u0641\u0629 \u0627\u0644\u0645\u0646\u062a\u062c \u0625\u0644\u0649 \u0627\u0644\u0633\u0644\u0629!',
      success: '\u062a\u0645\u062a \u0625\u0636\u0627\u0641\u0647 \u0628\u0646\u062c\u0627\u062d.',
      subtotal: '\u0627\u0644\u0645\u062c\u0645\u0648\u0639 \u0627\u0644\u0641\u0631\u0639\u064a',
      articles: '\u0645\u0646\u062a\u062c\u0627\u062a',
      continue: '\u0645\u062a\u0627\u0628\u0639\u0629 \u0627\u0644\u062a\u0633\u0648\u0651\u0642',
      view: '\u0639\u0631\u0636 \u0633\u0644\u062a\u064a',
      secure: '\u062f\u0641\u0639 \u0622\u0645\u0646',
      delivery: '\u062a\u0648\u0635\u064a\u0644 \u0633\u0631\u064a\u0639',
      quality: '\u062c\u0648\u062f\u0629 \u0645\u0636\u0645\u0648\u0646\u0629',
    }
    : {
      added: 'Produit ajouté au panier !',
      success: 'Votre article a été ajouté avec succès.',
      subtotal: 'Sous-total panier',
      articles: 'articles',
      continue: 'Continuer mes achats',
      view: 'Voir mon panier',
      secure: 'Paiement sécurisé',
      delivery: 'Livraison rapide',
      quality: 'Qualité garantie',
    };
  const direction = isRTL ? styles.rowReverse : undefined;

  return (
    <View style={styles.screen} accessibilityLabel={copy.added}>
      <View pointerEvents="none" style={styles.backdrop}>
        <View style={[styles.backdropHeader, direction]}><MayushIcon name="menu" size={25} color={colors.brand.navy900} /><MayushLogo width={148} height={43} /><View style={[styles.backdropActions, direction]}><MayushIcon name="heart" size={25} color={colors.brand.navy900} /><MayushIcon name="shopping-cart" size={25} color={colors.brand.navy900} /></View></View>
        <View style={styles.searchGhost}><MayushIcon name="search" size={18} color={colors.neutral.gray700} /><MayushText variant="body" color={colors.neutral.gray700}>{language === 'ar' ? '\u0627\u0628\u062d\u062b \u0639\u0646 \u0645\u0646\u062a\u062c...' : 'Rechercher un produit, une catégorie...'}</MayushText></View>
        <Image source={BACKDROP_HERO} resizeMode="cover" style={styles.backdropHero} />
      </View>
      <View pointerEvents="none" style={styles.dimLayer} />

      <View style={styles.sheet}>
        <View style={styles.handle} />
        <View style={styles.sparkles}><MayushIcon name="star" size={12} color={colors.brand.orange500} /><MayushIcon name="star" size={7} color={colors.neutral.gray300} /><View style={styles.successCircle}><MayushIcon name="check" size={38} color={colors.surface.white} strokeWidth={2.5} /></View><MayushIcon name="star" size={7} color={colors.neutral.gray300} /><MayushIcon name="star" size={12} color={colors.brand.orange500} /></View>
        <MayushText variant="pageTitle" color={colors.brand.navy900} align="center" style={styles.addedTitle}>{copy.added}</MayushText>
        <MayushText variant="body" color={colors.neutral.gray700} align="center" style={styles.successCopy}>{copy.success}</MayushText>

        <View style={[styles.lineCard, direction]}>
          <Image source={lastLine?.imageUri ? { uri: lastLine.imageUri } : FALLBACK_PRODUCT_IMAGE} style={styles.productImage} />
          <View style={styles.lineCopy}><MayushText variant="strongBody" color={colors.brand.navy900} numberOfLines={1}>{lastLine?.name || 'Canapé Oslo 3 places'}</MayushText><MayushText variant="smallBody" color={colors.neutral.gray700} numberOfLines={1}>{lastLine?.variant || 'Tissu beige · Pieds bois clair'}</MayushText><View style={[styles.variantRow, direction]}><View style={styles.variantDot} /><MayushText variant="smallBody" color={colors.neutral.gray700}>{language === 'ar' ? '\u0628\u064a\u062c' : 'Beige'}</MayushText><MayushText variant="strongBody" color={colors.brand.navy900} style={styles.quantityText}>× {lastLine?.quantity || 1}</MayushText></View></View>
        </View>

        <View style={[styles.summaryCard, direction]}><View style={styles.summaryIcon}><MayushIcon name="shopping-bag" size={22} color={colors.brand.orange500} /></View><View style={styles.summaryCopy}><MayushText variant="strongBody" color={colors.brand.navy900}>{copy.subtotal}</MayushText><MayushText variant="smallBody" color={colors.neutral.gray700}>{totals.itemCount} {copy.articles}</MayushText></View><MayushText variant="sectionTitle" color={colors.brand.orange500} style={styles.total}>{formatMadPrice(totals.subtotalMad)}</MayushText><MayushIcon name={isRTL ? 'chevron-left' : 'chevron-right'} size={19} color={colors.brand.navy900} /></View>

        <TouchableOpacity accessibilityRole="button" accessibilityLabel={copy.continue} activeOpacity={0.86} onPress={onViewCart} style={styles.secondaryButton}><MayushIcon name="shopping-bag" size={21} color={colors.brand.orange500} /><MayushText variant="button" color={colors.brand.orange500}>{copy.continue}</MayushText></TouchableOpacity>
        <TouchableOpacity accessibilityRole="button" accessibilityLabel={copy.view} activeOpacity={0.86} onPress={onViewCart} style={styles.primaryButton}><MayushIcon name="shopping-cart" size={23} color={colors.surface.white} /><MayushText variant="button" color={colors.surface.white}>{copy.view}</MayushText></TouchableOpacity>

        <View style={[styles.trustRow, direction]}><TrustItem icon="shield" label={copy.secure} detail={language === 'ar' ? '100% \u0622\u0645\u0646' : '100% sécurisé'} /><TrustItem icon="truck-outline" label={copy.delivery} detail={language === 'ar' ? '\u0641\u064a \u062c\u0645\u064a\u0639 \u0623\u0646\u062d\u0627\u0621 \u0627\u0644\u0645\u063a\u0631\u0628' : 'Partout au Maroc'} /><TrustItem icon="star" label={copy.quality} detail={language === 'ar' ? '\u0645\u0646\u062a\u062c\u0627\u062a \u0645\u0645\u064a\u0632\u0629' : 'Produits premium'} /></View>
      </View>
    </View>
  );
};

const TrustItem: React.FC<{ icon: React.ComponentProps<typeof MayushIcon>['name']; label: string; detail: string }> = ({ icon, label, detail }) => <View style={styles.trustItem}><MayushIcon name={icon} size={21} color={colors.brand.navy900} /><MayushText variant="caption" color={colors.brand.navy900} align="center" style={styles.trustLabel}>{label}</MayushText><MayushText variant="caption" color={colors.neutral.gray700} align="center" style={styles.trustDetail}>{detail}</MayushText></View>;

const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: colors.surface.white }, rowReverse: { flexDirection: 'row-reverse' },
  backdrop: { ...StyleSheet.absoluteFill, backgroundColor: colors.surface.creamLight }, backdropHeader: { height: 69, paddingHorizontal: 16, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' }, backdropActions: { flexDirection: 'row', gap: 15 }, searchGhost: { height: 39, marginHorizontal: 16, borderRadius: 9, borderWidth: 1, borderColor: colors.surface.borderWarm, flexDirection: 'row', alignItems: 'center', gap: 8, paddingHorizontal: 12, backgroundColor: colors.surface.white }, backdropHero: { height: 233, marginTop: 10, width: '100%', opacity: 0.72 }, dimLayer: { ...StyleSheet.absoluteFill, backgroundColor: 'rgba(16,29,53,0.48)' },
  sheet: { marginTop: 'auto', borderTopLeftRadius: 24, borderTopRightRadius: 24, paddingHorizontal: 20, paddingBottom: 22, backgroundColor: colors.surface.white }, handle: { width: 36, height: 4, marginTop: 8, alignSelf: 'center', borderRadius: 2, backgroundColor: colors.neutral.gray300 }, sparkles: { marginTop: 13, flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 18 }, successCircle: { width: 47, height: 47, borderRadius: 24, alignItems: 'center', justifyContent: 'center', backgroundColor: '#77CB5C' }, addedTitle: { marginTop: 6, fontSize: 20, lineHeight: 26 }, successCopy: { marginTop: 2, fontSize: 13, lineHeight: 18 },
  lineCard: { marginTop: 17, flexDirection: 'row', alignItems: 'center', gap: 13 }, productImage: { width: 117, height: 96, borderRadius: 9, backgroundColor: colors.surface.cream }, lineCopy: { flex: 1, gap: 4 }, variantRow: { flexDirection: 'row', alignItems: 'center', gap: 7, marginTop: 2 }, variantDot: { width: 15, height: 15, borderRadius: 8, backgroundColor: '#CDB394' }, quantityText: { marginLeft: 'auto', paddingHorizontal: 8, paddingVertical: 3, borderRadius: 5, borderWidth: 1, borderColor: colors.surface.borderWarm },
  summaryCard: { minHeight: 54, marginTop: 15, flexDirection: 'row', alignItems: 'center', gap: 10, borderRadius: 9, borderWidth: 1, borderColor: '#F1E2D5', paddingHorizontal: 12, backgroundColor: '#FFFAF6' }, summaryIcon: { width: 31, height: 31, borderRadius: 16, alignItems: 'center', justifyContent: 'center', backgroundColor: '#FFF0E0' }, summaryCopy: { flex: 1 }, total: { fontSize: 16 },
  secondaryButton: { height: 39, marginTop: 13, borderRadius: 8, borderWidth: 1, borderColor: colors.brand.orange500, flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 8 }, primaryButton: { height: 39, marginTop: 8, borderRadius: 8, flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 8, backgroundColor: colors.brand.orange500 },
  trustRow: { marginTop: 20, flexDirection: 'row', justifyContent: 'space-between' }, trustItem: { width: '31%', alignItems: 'center', gap: 2 }, trustLabel: { fontSize: 9, lineHeight: 12, fontWeight: '700' }, trustDetail: { fontSize: 8, lineHeight: 10 },
});
