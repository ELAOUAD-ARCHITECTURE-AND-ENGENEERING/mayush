/**
 * CartScreen (Figma Nodes 309:658 - 309:677)
 * Complete Cart supporting all core interactions, system states, and guest-account merge:
 * 1. Cart Update Needed (309:666)
 * 2. Cart Skeleton Loading (309:667)
 * 3. Cart Empty State (309:668)
 * 4. Cart Error Loading State (309:669)
 * 5. Saved for Later Items List (309:676)
 * 6. Guest-Account Cart Fusion Merge (309:677)
 */

import React, { useEffect, useRef, useState } from 'react';
import {
  Image,
  Modal,
  ScrollView,
  StyleSheet,
  TextInput,
  TouchableOpacity,
  View,
} from 'react-native';
import {
  CartLine,
  CartState,
  CartVariantSelection,
  PromotionValidationResult,
  formatMadPrice,
  getAvailablePromotions,
  getCartTotals,
  groupCartLinesBySeller,
} from '../../commerce/cartState';
import { CartEmptyState } from '../../components/cart/CartEmptyState';
import { CartErrorState } from '../../components/cart/CartErrorState';
import { CartMergeSummary } from '../../components/cart/CartMergeSummary';
import { CartSkeleton } from '../../components/cart/CartSkeleton';
import { CartToast } from '../../components/cart/CartToast';
import { CartUpdateAlert, PriceStockChangeItem } from '../../components/cart/CartUpdateAlert';
import { QuantityStepper } from '../../components/cart/QuantityStepper';
import { RemoveItemDialog } from '../../components/cart/RemoveItemDialog';
import { SavedForLaterList } from '../../components/cart/SavedForLaterList';
import { SellerCartGroup } from '../../components/cart/SellerCartGroup';
import { VariantEditSheet } from '../../components/cart/VariantEditSheet';
import { MayushLogo } from '../../design-system/components/brand/MayushLogo';
import { BottomTabBar, TabKey } from '../../design-system/components/navigation/BottomTabBar';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { useTheme } from '../../design-system/theme/useTheme';
import { colors } from '../../design-system/tokens/colors';
import { radii } from '../../design-system/tokens/radii';

const FALLBACK_IMAGES = [
  require('../../../assets/reference-art/home-new-luna.png'),
  require('../../../assets/reference-art/home-new-nori.png'),
  require('../../../assets/reference-art/home-new-kyoto.png'),
];

export interface CartScreenProps {
  cart?: CartState;
  loading?: boolean;
  error?: boolean;
  onRetry?: () => void;
  onNavigateTab?: (tab: TabKey) => void;
  onStartShopping?: () => void;
  onViewWishlist?: () => void;
  onSelectProduct?: (productId: number) => void;
  onUpdateQuantity?: (lineId: string, delta: number) => void;
  onUpdateVariant?: (lineId: string, selection: CartVariantSelection) => boolean;
  onApplyPromotion?: (code: string) => PromotionValidationResult;
  onRemovePromotion?: () => void;
  onCheckout?: () => void;
  onMergeCartLines?: (mergedLines: CartLine[]) => void;
}

export const CartScreen: React.FC<CartScreenProps> = ({
  cart,
  loading = false,
  error = false,
  onRetry,
  onNavigateTab,
  onStartShopping,
  onViewWishlist,
  onSelectProduct,
  onUpdateQuantity,
  onUpdateVariant,
  onApplyPromotion,
  onRemovePromotion,
  onCheckout,
  onMergeCartLines,
}) => {
  const { isRTL, language } = useTheme();
  const activeCart = cart || { lines: [] };
  const totals = getCartTotals(activeCart);
  const sellerGroups = groupCartLinesBySeller(activeCart);
  const availablePromotions = getAvailablePromotions(activeCart);

  // Free shipping threshold state (3000 MAD)
  const FREE_SHIPPING_THRESHOLD = 3000;
  const remainingForFreeShipping = Math.max(0, FREE_SHIPPING_THRESHOLD - totals.subtotalMad);
  const freeShippingProgress = Math.min(100, (totals.subtotalMad / FREE_SHIPPING_THRESHOLD) * 100);

  // System State: Price & Stock changes alert (Node 309:666)
  const [priceStockChanges, setPriceStockChanges] = useState<PriceStockChangeItem[]>([
    {
      id: '101:beige-boucle',
      productName: 'Fauteuil Lounge Luna',
      oldPriceMad: 2950,
      newPriceMad: 2700,
    },
  ]);

  // System State: Guest & Account Cart Merge Modal (Node 309:677)
  const [mergeModalVisible, setMergeModalVisible] = useState(false);
  const [accountCartFixture] = useState<CartState>({
    lines: [
      {
        id: 'acc-1',
        productId: 201,
        name: 'Canapé Nori 3 Places',
        productName: 'Canapé Nori 3 Places',
        variant: 'Tissu Beige · 210cm',
        selectedVariantText: 'Tissu Beige · 210cm',
        quantity: 1,
        unitPriceMad: 4800,
      },
    ],
  });

  // Toast feedback state (Node 309:659)
  const [toastVisible, setToastVisible] = useState(false);
  const [toastMessage, setToastMessage] = useState('');
  const toastTimer = useRef<ReturnType<typeof setTimeout> | null>(null);

  const triggerToast = (msg: string) => {
    if (toastTimer.current) clearTimeout(toastTimer.current);
    setToastMessage(msg);
    setToastVisible(true);
    toastTimer.current = setTimeout(() => setToastVisible(false), 1600);
  };

  useEffect(() => () => {
    if (toastTimer.current) clearTimeout(toastTimer.current);
  }, []);

  // Promo code state (Nodes 309:662 - 309:664)
  const [promoInput, setPromoInput] = useState('');
  const [promoError, setPromoError] = useState<string | null>(null);
  const [vouchersModalVisible, setVouchersModalVisible] = useState(false);

  // Modals state
  const [lineToRemove, setLineToRemove] = useState<CartLine | null>(null);
  const [lineToEditVariant, setLineToEditVariant] = useState<CartLine | null>(null);

  // Multi-vendor seller grouping toggle (Node 309:661)
  const [groupBySeller, setGroupBySeller] = useState(false);

  // Saved for later items state (Node 309:676)
  const [savedForLater, setSavedForLater] = useState<CartLine[]>([
    {
      id: 'saved-1',
      productId: 901,
      name: 'Table Basse Oval Plâtre',
      productName: 'Table Basse Oval Plâtre',
      variant: 'Plâtre Blanc · 110cm',
      selectedVariantText: 'Plâtre Blanc · 110cm',
      unitPriceMad: 2200,
      imageAsset: 'https://images.unsplash.com/photo-1595428774223-ef52624120d2?q=80&w=350&auto=format&fit=crop',
      quantity: 1,
    },
  ]);

  const handleAcceptPriceChanges = () => {
    setPriceStockChanges([]);
    triggerToast('Modifications de prix acceptées');
  };

  const handleRemoveUnavailable = () => {
    setPriceStockChanges((prev) => prev.filter((c) => !c.isUnavailable));
    triggerToast('Articles indisponibles retirés');
  };

  const handleApplyPromo = () => {
    setPromoError(null);
    const cleanCode = promoInput.trim().toUpperCase();
    const validation = onApplyPromotion?.(cleanCode);
    if (validation?.code === 'VALID' && validation.promotion) {
      setPromoInput('');
      triggerToast(`Code promotionnel ${validation.promotion.code} appliqué`);
    } else {
      setPromoError(language === 'ar'
        ? 'رمز التخفيض هذا غير قابل للتطبيق على المنتجات الموجودة في سلتك.'
        : validation?.code === 'MINIMUM_NOT_REACHED'
          ? 'Le montant minimum requis pour ce code n’est pas atteint.'
          : 'Ce code promotionnel n’est pas applicable aux produits présents dans votre panier.');
    }
  };

  const handleIncrementQuantity = (line: CartLine) => {
    onUpdateQuantity?.(line.id, 1);
    triggerToast(language === 'ar' ? 'جارٍ تحديث السلة' : 'Mise à jour du panier');
  };

  const handleDecrementQuantity = (line: CartLine) => {
    if (line.quantity <= 1) {
      setLineToRemove(line);
    } else {
      onUpdateQuantity?.(line.id, -1);
      triggerToast(language === 'ar' ? 'جارٍ تحديث السلة' : 'Mise à jour du panier');
    }
  };

  const handleMoveToLater = (line: CartLine) => {
    onUpdateQuantity?.(line.id, -line.quantity);
    setSavedForLater((prev) => [...prev, line]);
    triggerToast('Article enregistré pour plus tard');
  };

  const handleMoveBackToCart = (line: CartLine) => {
    setSavedForLater((prev) => prev.filter((item) => item.id !== line.id));
    onUpdateQuantity?.(line.id, 1);
    triggerToast('Article replacé dans le panier');
  };

  const handleRemoveSavedItem = (lineId: string) => {
    setSavedForLater((prev) => prev.filter((item) => item.id !== lineId));
    triggerToast('Article retiré des enregistrements');
  };

  const confirmRemoveLine = () => {
    if (lineToRemove) {
      onUpdateQuantity?.(lineToRemove.id, -lineToRemove.quantity);
      triggerToast(`${lineToRemove.productName || lineToRemove.name} retiré du panier`);
      setLineToRemove(null);
    }
  };

  const handleConfirmVariantChange = (lineId: string, selection: CartVariantSelection) => {
    if (onUpdateVariant?.(lineId, selection)) triggerToast('Variante mise à jour avec succès');
  };

  const handleMergeBothCarts = (mergedLines: CartLine[]) => {
    onMergeCartLines?.(mergedLines);
    setMergeModalVisible(false);
    triggerToast('Paniers fusionnés avec succès !');
  };

  const applyOffer = (code: string) => {
    const validation = onApplyPromotion?.(code);
    if (validation?.code !== 'VALID' || !validation.promotion) return;
    setPromoError(null);
    setVouchersModalVisible(false);
    triggerToast(`Code promotionnel ${validation.promotion.code} appliqué`);
  };

  const copy = language === 'ar'
    ? {
      title: 'سلتك',
      articles: 'منتجات',
      shop: 'ابدأ التسوق',
      checkout: 'متابعة الشراء',
    }
    : {
      title: 'Mon panier',
      articles: 'articles',
      shop: 'Commencer mes achats',
      checkout: 'Passer à la commande',
    };

  // 1. Skeleton Loading State (Node 309:667)
  if (loading) {
    return (
      <View style={styles.screen}>
        <CartSkeleton />
        <BottomTabBar activeTab="cart" onTabPress={(tab) => onNavigateTab?.(tab)} cartBadgeCount={0} />
      </View>
    );
  }

  // 2. Cart Loading Error State (Node 309:669)
  if (error) {
    return (
      <View style={styles.screen}>
        <View style={styles.header}>
          <MayushLogo width={109} height={32} />
          <MayushText variant="sectionTitle" color={colors.brand.navy900} style={styles.headerTitle}>
            {copy.title}
          </MayushText>
          <View style={{ width: 40 }} />
        </View>
        <CartErrorState onRetry={onRetry || (() => undefined)} />
        <BottomTabBar activeTab="cart" onTabPress={(tab) => onNavigateTab?.(tab)} cartBadgeCount={0} />
      </View>
    );
  }

  return (
    <View style={styles.screen} accessibilityLabel={copy.title}>
      {/* Quantity / Variant Update Toast Feedback (Node 309:659) */}
      <CartToast visible={toastVisible} message={toastMessage} />

      <View style={styles.header}>
        <MayushLogo width={109} height={32} />
        <MayushText variant="sectionTitle" color={colors.brand.navy900} style={styles.headerTitle}>
          {copy.title} ({totals.itemCount})
        </MayushText>
        <View style={styles.headerRightActions}>
          <TouchableOpacity accessibilityRole="button" onPress={() => setMergeModalVisible(true)} style={styles.iconBtn}>
            <MayushIcon name="refresh-cw" size={20} color={colors.brand.orange500} />
          </TouchableOpacity>
          <TouchableOpacity accessibilityRole="button" onPress={onViewWishlist} style={styles.iconBtn}>
            <MayushIcon name="heart" size={22} color={colors.brand.navy900} />
          </TouchableOpacity>
        </View>
      </View>

      <ScrollView showsVerticalScrollIndicator={false} contentContainerStyle={styles.content}>
        {/* System State 1: Price / Stock Updates Needed Alert (Node 309:666) */}
        {priceStockChanges.length > 0 ? (
          <CartUpdateAlert
            changes={priceStockChanges}
            onAcceptChanges={handleAcceptPriceChanges}
            onRemoveUnavailable={handleRemoveUnavailable}
          />
        ) : null}

        {activeCart.lines.length > 0 ? (
          <>
            {/* Free Shipping Progress Bar (Node 309:667) */}
            <View style={styles.freeShippingCard}>
              <View style={styles.freeShippingRow}>
                <MayushIcon name="truck" size={20} color={colors.brand.orange500} />
                <MayushText variant="strongBody" color={colors.brand.navy900} style={styles.freeShippingText}>
                  {remainingForFreeShipping > 0
                    ? language === 'ar'
                      ? `أضف ${remainingForFreeShipping} درهم للحصول على توصيل مجاني!`
                      : `Plus que ${remainingForFreeShipping} MAD pour la livraison offerte !`
                    : language === 'ar'
                      ? 'مكتمل! لقد حصلت على توصيل مجاني 🎉'
                      : 'Félicitations ! Vous bénéficiez de la livraison offerte 🎉'}
                </MayushText>
              </View>
              <View style={styles.progressTrack}>
                <View style={[styles.progressFill, { width: `${freeShippingProgress}%` }]} />
              </View>
            </View>

            {/* View Mode Toggle: Multi-Vendor Seller Grouping (Node 309:661) */}
            <View style={styles.toggleRow}>
              <MayushText variant="caption" color={colors.neutral.gray700}>
                Vue vendeurs indépendants (Multi-Vendeurs)
              </MayushText>
              <TouchableOpacity
                style={[styles.togglePill, groupBySeller && styles.togglePillActive]}
                onPress={() => setGroupBySeller(!groupBySeller)}
              >
                <MayushText variant="caption" color={groupBySeller ? colors.brand.orange500 : colors.brand.navy900}>
                  {groupBySeller ? 'Groupé par artisan' : 'Liste simple'}
                </MayushText>
              </TouchableOpacity>
            </View>

            {/* Cart Items List or Seller Grouped Cards */}
            {groupBySeller ? (
              <View>
                {sellerGroups.map((group, groupIndex) => (
                  <SellerCartGroup key={group.sellerId} sellerName={group.sellerName} lines={group.lines}>
                    {group.lines.map((line, lineIndex) => (
                      <CartItemRow
                        key={line.id}
                        line={line}
                        fallbackImg={FALLBACK_IMAGES[(groupIndex + lineIndex) % FALLBACK_IMAGES.length]}
                        onEditVariant={() => setLineToEditVariant(line)}
                        onIncrement={() => handleIncrementQuantity(line)}
                        onDecrement={() => handleDecrementQuantity(line)}
                        onRemove={() => setLineToRemove(line)}
                        onSaveLater={() => handleMoveToLater(line)}
                        onSelectProduct={onSelectProduct}
                      />
                    ))}
                  </SellerCartGroup>
                ))}
              </View>
            ) : (
              <View style={styles.lineList}>
                {activeCart.lines.map((line, idx) => (
                  <CartItemRow
                    key={line.id}
                    line={line}
                    fallbackImg={FALLBACK_IMAGES[idx % FALLBACK_IMAGES.length]}
                    onEditVariant={() => setLineToEditVariant(line)}
                    onIncrement={() => handleIncrementQuantity(line)}
                    onDecrement={() => handleDecrementQuantity(line)}
                    onRemove={() => setLineToRemove(line)}
                    onSaveLater={() => handleMoveToLater(line)}
                    onSelectProduct={onSelectProduct}
                  />
                ))}
              </View>
            )}

            {/* Promo Code Input & Available Vouchers (Nodes 309:662 - 309:664) */}
            <View style={styles.promoCard}>
              <View style={styles.promoHeaderRow}>
                <MayushText variant="strongBody" color={colors.brand.navy900}>
                  {language === 'ar' ? 'رمز التخفيض' : 'Code promo'}
                </MayushText>
                <TouchableOpacity onPress={() => setVouchersModalVisible(true)}>
                  <MayushText variant="caption" color={colors.brand.orange500} style={styles.voucherLink}>
                    {language === 'ar' ? 'عرض القسائم' : 'Voir les offres'}
                  </MayushText>
                </TouchableOpacity>
              </View>

              {totals.promotionCode ? (
                <View style={styles.appliedPromoBadge}>
                  <MayushIcon name="check-circle" size={16} color={colors.semantic.success} />
                  <MayushText variant="caption" color={colors.brand.navy900} style={styles.appliedPromoText}>
                    Code {totals.promotionCode} appliqué (-{formatMadPrice(totals.discountMad)})
                  </MayushText>
                  <TouchableOpacity onPress={() => { onRemovePromotion?.(); triggerToast('Code promo retiré'); }}>
                    <MayushIcon name="x" size={16} color={colors.neutral.gray500} />
                  </TouchableOpacity>
                </View>
              ) : (
                <View style={[styles.promoInputRow, isRTL && styles.rowReverse]}>
                  <TextInput
                    style={styles.promoInput}
                    placeholder={language === 'ar' ? 'أدخل الرمز' : 'Entrez votre code'}
                    value={promoInput}
                    onChangeText={setPromoInput}
                    autoCapitalize="characters"
                  />
                  <TouchableOpacity style={styles.applyBtn} onPress={handleApplyPromo}>
                    <MayushText variant="strongBody" color={colors.surface.white}>
                      {language === 'ar' ? 'تطبيق' : 'Appliquer'}
                    </MayushText>
                  </TouchableOpacity>
                </View>
              )}

              {promoError ? (
                <View style={styles.promoErrorPanel}>
                  <MayushText variant="caption" color={colors.semantic.error} style={styles.promoErrorText}>
                    {promoError}
                  </MayushText>
                  <View style={[styles.promoErrorActions, isRTL && styles.rowReverse]}>
                    <TouchableOpacity onPress={() => { setPromoInput(''); setPromoError(null); }}>
                      <MayushText variant="caption" color={colors.brand.navy900}>{language === 'ar' ? 'جرّب رمزاً آخر' : 'Essayer un autre code'}</MayushText>
                    </TouchableOpacity>
                    <TouchableOpacity onPress={() => setVouchersModalVisible(true)}>
                      <MayushText variant="caption" color={colors.brand.orange500}>{language === 'ar' ? 'عرض العروض المتاحة' : 'Voir les offres disponibles'}</MayushText>
                    </TouchableOpacity>
                  </View>
                </View>
              ) : null}
            </View>

            {/* Order Summary Card */}
            <View style={styles.summaryCard}>
              <MayushText variant="sectionTitle" color={colors.brand.navy900} style={styles.summaryTitle}>
                Résumé de la commande
              </MayushText>
              <View style={styles.summaryRow}>
                <MayushText variant="body" color={colors.neutral.gray700}>
                  Sous-total
                </MayushText>
                <MayushText variant="strongBody" color={colors.brand.navy900}>
                  {formatMadPrice(totals.subtotalMad)}
                </MayushText>
              </View>

              {totals.promotionCode ? (
                <View style={styles.summaryRow}>
                  <MayushText variant="body" color={colors.semantic.success}>
                    Réduction ({totals.promotionCode})
                  </MayushText>
                  <MayushText variant="strongBody" color={colors.semantic.success}>
                    -{formatMadPrice(totals.discountMad)}
                  </MayushText>
                </View>
              ) : null}

              <View style={styles.summaryRow}>
                <MayushText variant="body" color={colors.neutral.gray700}>
                  Livraison
                </MayushText>
                <MayushText variant="body" color={colors.neutral.gray500}>
                  Calculée à l'étape suivante
                </MayushText>
              </View>

              <View style={styles.summaryDivider} />

              <View style={styles.summaryRow}>
                <MayushText variant="sectionTitle" color={colors.brand.navy900}>
                  Total provisoire
                </MayushText>
                <MayushText variant="display" color={colors.brand.orange500}>
                  {formatMadPrice(totals.totalMad)}
                </MayushText>
              </View>
            </View>
          </>
        ) : (
          /* System State 3: Empty Cart View (Node 309:668) */
          <CartEmptyState onStartShopping={onStartShopping} />
        )}

        {/* System State 5: Saved for Later List Section (Node 309:676) */}
        <SavedForLaterList
          items={savedForLater}
          onMoveToCart={handleMoveBackToCart}
          onRemove={handleRemoveSavedItem}
          onSelectProduct={onSelectProduct}
        />
      </ScrollView>

      {/* System State 6: Guest-Account Cart Merge Modal (Node 309:677) */}
      <CartMergeSummary
        visible={mergeModalVisible}
        guestCart={activeCart}
        accountCart={accountCartFixture}
        onMergeBoth={handleMergeBothCarts}
        onKeepAccount={() => {
          onMergeCartLines?.(accountCartFixture.lines);
          setMergeModalVisible(false);
          triggerToast('Panier du compte conservé');
        }}
        onKeepGuest={() => {
          setMergeModalVisible(false);
          triggerToast('Panier invité conservé');
        }}
        onClose={() => setMergeModalVisible(false)}
      />

      {/* Remove Item Confirmation Dialog Modal (Node 309:665) */}
      <RemoveItemDialog
        visible={lineToRemove !== null}
        productName={lineToRemove?.productName || lineToRemove?.name}
        imageUri={lineToRemove?.imageUri}
        onCancel={() => setLineToRemove(null)}
        onConfirm={confirmRemoveLine}
      />

      {/* Variant Edit Bottom Sheet Modal (Node 309:660) */}
      <VariantEditSheet
        visible={lineToEditVariant !== null}
        line={lineToEditVariant}
        onClose={() => setLineToEditVariant(null)}
        onConfirm={handleConfirmVariantChange}
      />

      {/* Available Vouchers Sheet Modal (Node 309:664) */}
      <Modal visible={vouchersModalVisible} transparent animationType="slide" onRequestClose={() => setVouchersModalVisible(false)}>
        <View style={styles.modalOverlay}>
          <View style={styles.voucherSheet}>
            <View style={styles.sheetHeader}>
              <MayushText variant="sectionTitle" color={colors.brand.navy900}>
                {language === 'ar' ? 'رمز التخفيض' : 'Code promotionnel'}
              </MayushText>
              <TouchableOpacity onPress={() => setVouchersModalVisible(false)}>
                <MayushIcon name="x" size={22} color={colors.brand.navy900} />
              </TouchableOpacity>
            </View>
            <ScrollView contentContainerStyle={styles.sheetBody}>
              <View style={[styles.promoInputRow, isRTL && styles.rowReverse]}>
                <TextInput style={styles.promoInput} placeholder={language === 'ar' ? 'أدخل رمزك' : 'Saisissez votre code'} value={promoInput} onChangeText={setPromoInput} autoCapitalize="characters" />
                <TouchableOpacity style={styles.applyBtn} onPress={handleApplyPromo}><MayushText variant="strongBody" color={colors.surface.white}>{language === 'ar' ? 'تطبيق' : 'Appliquer'}</MayushText></TouchableOpacity>
              </View>
              <MayushText variant="strongBody" color={colors.brand.navy900}>{language === 'ar' ? 'العروض المتاحة' : 'Offres disponibles'}</MayushText>
              {availablePromotions.map((promotion) => (
                <TouchableOpacity key={promotion.promoId} style={styles.voucherItem} onPress={() => applyOffer(promotion.code)}>
                  <View style={[styles.voucherTitleRow, isRTL && styles.rowReverse]}>
                    <MayushText variant="strongBody" color={colors.brand.navy900}>{promotion.code}</MayushText>
                    {totals.appliedPromotionId === promotion.promoId ? <MayushText variant="caption" color={colors.semantic.success}>{language === 'ar' ? 'مطبّق' : 'Appliqué'}</MayushText> : null}
                  </View>
                  <MayushText variant="smallBody" color={colors.brand.orange500}>{promotion.title}</MayushText>
                  <MayushText variant="caption" color={colors.neutral.gray700}>
                    {promotion.minimumSubtotalMad ? `${language === 'ar' ? 'ابتداءً من' : 'À partir de'} ${formatMadPrice(promotion.minimumSubtotalMad)}` : ''}
                    {promotion.expiryLabel ? ` · ${language === 'ar' ? 'ينتهي في' : 'Expire le'} ${promotion.expiryLabel}` : ''}
                  </MayushText>
                </TouchableOpacity>
              ))}
              <MayushText variant="caption" color={colors.neutral.gray700} align={isRTL ? 'right' : 'left'}>
                {language === 'ar' ? 'اجمع ووفر! يمكن دمج بعض الرموز مع عروض أخرى.' : 'Cumulez et économisez ! Certains codes peuvent être combinés avec d’autres offres.'}
              </MayushText>
            </ScrollView>
          </View>
        </View>
      </Modal>

      {activeCart.lines.length > 0 ? (
        <View style={styles.checkoutShell}>
          <TouchableOpacity accessibilityRole="button" onPress={onCheckout} style={styles.checkoutButton}>
            <MayushIcon name="shopping-bag" size={22} color={colors.surface.white} />
            <MayushText variant="button" color={colors.surface.white}>
              {copy.checkout} ({formatMadPrice(totals.totalMad)})
            </MayushText>
          </TouchableOpacity>
        </View>
      ) : null}

      <BottomTabBar activeTab="cart" onTabPress={(tab) => onNavigateTab?.(tab)} cartBadgeCount={totals.itemCount} />
    </View>
  );
};

interface CartItemRowProps {
  line: CartLine;
  fallbackImg: any;
  onEditVariant: () => void;
  onIncrement: () => void;
  onDecrement: () => void;
  onRemove: () => void;
  onSaveLater: () => void;
  onSelectProduct?: (productId: number) => void;
}

const CartItemRow: React.FC<CartItemRowProps> = ({
  line,
  fallbackImg,
  onEditVariant,
  onIncrement,
  onDecrement,
  onRemove,
  onSaveLater,
  onSelectProduct,
}) => (
  <View style={styles.cartCard}>
    <TouchableOpacity
      activeOpacity={0.8}
      onPress={() => onSelectProduct?.(typeof line.productId === 'number' ? line.productId : 101)}
    >
      <Image source={fallbackImg} style={styles.thumbImg} resizeMode="cover" />
    </TouchableOpacity>

    <View style={styles.cardMainCol}>
      <View style={styles.cardHeaderRow}>
        <TouchableOpacity
          onPress={() => onSelectProduct?.(typeof line.productId === 'number' ? line.productId : 101)}
          style={{ flex: 1 }}
        >
          <MayushText variant="strongBody" color={colors.brand.navy900} numberOfLines={1} style={styles.prodTitle}>
            {line.productName || line.name}
          </MayushText>
        </TouchableOpacity>
        <TouchableOpacity onPress={onRemove} style={styles.removeBtn}>
          <MayushIcon name="trash-2" size={18} color={colors.neutral.gray500} />
        </TouchableOpacity>
      </View>

      <TouchableOpacity onPress={onEditVariant} style={styles.variantChip}>
        <MayushText variant="caption" color={colors.brand.navy900}>
          {line.selectedVariantText || line.variant || 'Standard'}
        </MayushText>
        <MayushIcon name="edit-2" size={12} color={colors.brand.orange500} />
      </TouchableOpacity>

      <View style={styles.cardFooterRow}>
        <MayushText variant="priceRegular" color={colors.brand.orange500}>
          {formatMadPrice(line.unitPriceMad * line.quantity)}
        </MayushText>

        <QuantityStepper
          quantity={line.quantity}
          onIncrement={onIncrement}
          onDecrement={onDecrement}
        />
      </View>

      <TouchableOpacity onPress={onSaveLater} style={styles.saveLaterBtn}>
        <MayushText variant="caption" color={colors.neutral.gray700}>
          Enregistrer pour plus tard
        </MayushText>
      </TouchableOpacity>
    </View>
  </View>
);

const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: colors.surface.white },
  header: {
    height: 64,
    paddingHorizontal: 16,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    borderBottomWidth: 1,
    borderBottomColor: colors.surface.borderWarm,
  },
  headerRightActions: { flexDirection: 'row', alignItems: 'center', gap: 4 },
  iconBtn: { width: 40, height: 40, alignItems: 'center', justifyContent: 'center' },
  headerTitle: { fontSize: 17, fontWeight: '700' },
  content: { padding: 16, paddingBottom: 100 },
  freeShippingCard: {
    padding: 14,
    borderRadius: radii.xl,
    backgroundColor: colors.brand.orange100,
    borderWidth: 1,
    borderColor: colors.surface.borderWarm,
    marginBottom: 12,
  },
  freeShippingRow: { flexDirection: 'row', alignItems: 'center', gap: 8, marginBottom: 8 },
  freeShippingText: { flex: 1, fontSize: 13 },
  progressTrack: { height: 6, borderRadius: 3, backgroundColor: colors.surface.borderWarm, overflow: 'hidden' },
  progressFill: { height: '100%', backgroundColor: colors.brand.orange500 },
  toggleRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 12 },
  togglePill: { paddingHorizontal: 10, paddingVertical: 4, borderRadius: radii.full, backgroundColor: colors.surface.creamLight, borderWidth: 1, borderColor: colors.surface.borderWarm },
  togglePillActive: { backgroundColor: colors.brand.orange100, borderColor: colors.brand.orange500 },
  lineList: { gap: 12, marginBottom: 16 },
  cartCard: {
    flexDirection: 'row',
    padding: 12,
    borderRadius: radii.xl,
    borderWidth: 1,
    borderColor: colors.surface.borderWarm,
    backgroundColor: colors.surface.white,
  },
  thumbImg: { width: 80, height: 80, borderRadius: radii.lg, marginRight: 12 },
  cardMainCol: { flex: 1 },
  cardHeaderRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'flex-start' },
  prodTitle: { fontSize: 15 },
  removeBtn: { padding: 4 },
  variantChip: { flexDirection: 'row', alignItems: 'center', gap: 4, marginTop: 2, marginBottom: 8, alignSelf: 'flex-start', paddingHorizontal: 8, paddingVertical: 2, borderRadius: radii.sm, backgroundColor: colors.surface.creamLight },
  cardFooterRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
  saveLaterBtn: { marginTop: 8, alignSelf: 'flex-start' },
  promoCard: {
    padding: 14,
    borderRadius: radii.xl,
    borderWidth: 1,
    borderColor: colors.surface.borderWarm,
    backgroundColor: colors.surface.creamLight,
    marginBottom: 16,
  },
  promoHeaderRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 10 },
  voucherLink: { fontWeight: '700' },
  promoInputRow: { flexDirection: 'row', gap: 8 },
  promoInput: {
    flex: 1,
    height: 40,
    borderRadius: radii.lg,
    backgroundColor: colors.surface.white,
    borderWidth: 1,
    borderColor: colors.surface.borderWarm,
    paddingHorizontal: 12,
    fontSize: 14,
  },
  applyBtn: {
    height: 40,
    paddingHorizontal: 16,
    borderRadius: radii.lg,
    backgroundColor: colors.brand.orange500,
    alignItems: 'center',
    justifyContent: 'center',
  },
  appliedPromoBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    padding: 10,
    borderRadius: radii.lg,
    backgroundColor: colors.surface.white,
    borderWidth: 1,
    borderColor: colors.semantic.success,
  },
  appliedPromoText: { flex: 1, fontWeight: '600' },
  promoErrorText: { marginTop: 6 },
  promoErrorPanel: { gap: 9 },
  promoErrorActions: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginTop: 8 },
  rowReverse: { flexDirection: 'row-reverse' },
  summaryCard: {
    padding: 16,
    borderRadius: radii.xl,
    borderWidth: 1,
    borderColor: colors.surface.borderWarm,
    backgroundColor: colors.surface.white,
    marginBottom: 16,
  },
  summaryTitle: { fontSize: 17, marginBottom: 12 },
  summaryRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 8 },
  summaryDivider: { height: 1, backgroundColor: colors.surface.borderWarm, marginVertical: 8 },
  modalOverlay: { flex: 1, backgroundColor: 'rgba(0,0,0,0.5)', justifyContent: 'flex-end' },
  voucherSheet: { width: '100%', maxHeight: '78%', backgroundColor: colors.surface.white, borderTopLeftRadius: radii.xl, borderTopRightRadius: radii.xl, padding: 20, paddingBottom: 28 },
  sheetHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 16 },
  sheetBody: { paddingBottom: 16, gap: 10 },
  voucherItem: { padding: 12, borderRadius: radii.lg, borderWidth: 1, borderColor: colors.surface.borderWarm, marginBottom: 8 },
  voucherTitleRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
  checkoutShell: { padding: 16, backgroundColor: colors.surface.white, borderTopWidth: 1, borderTopColor: colors.surface.borderWarm },
  checkoutButton: { height: 50, borderRadius: radii.xl, backgroundColor: colors.brand.orange500, flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 10 },
});
