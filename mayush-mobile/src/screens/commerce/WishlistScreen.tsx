/**
 * WishlistScreen (Figma Nodes 309:670 - 309:675)
 * Dual-state wishlist supporting populated grid, price drop alerts, out-of-stock alternatives,
 * remove confirmation dialog, move-to-cart variant selection, and empty state.
 */

import React, { useState } from 'react';
import {
  Image,
  Modal,
  ScrollView,
  StyleSheet,
  TouchableOpacity,
  View,
} from 'react-native';
import { ProductMiniDto } from '../../contracts/api/dto';
import { MayushLogo } from '../../design-system/components/brand/MayushLogo';
import { BottomTabBar, TabKey } from '../../design-system/components/navigation/BottomTabBar';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { useTheme } from '../../design-system/theme/useTheme';
import { colors } from '../../design-system/tokens/colors';
import { radii } from '../../design-system/tokens/radii';

const CANAPE_IMG = require('../../../assets/reference-art/home-new-luna.png');
const FAUTEUIL_IMG = require('../../../assets/reference-art/home-new-nori.png');
const WISHLIST_EMPTY_ARTWORK = require('../../../assets/illustrations/wishlist-empty-scene.png');

export interface WishlistScreenProps {
  onNavigateTab?: (tab: TabKey) => void;
  onBrowseCollections?: () => void;
  onSelectProduct?: (product: ProductMiniDto) => void;
  onMoveToCart?: (product: ProductMiniDto) => void;
}

export const WishlistScreen: React.FC<WishlistScreenProps> = ({
  onNavigateTab,
  onBrowseCollections,
  onSelectProduct,
  onMoveToCart,
}) => {
  const { language } = useTheme();

  const [wishlistItems, setWishlistItems] = useState<(ProductMiniDto & { inStock: boolean; oldPriceMad?: number })[]>([
    { id: 701, name: 'Fauteuil Nori Accent · Vert Sauge', priceMad: 1500, oldPriceMad: 1800, formattedPrice: '1 500 MAD', inStock: true, thumbnail_image: '', has_discount: true, discount: '-17%', stroked_price: '1 800 MAD', main_price: '1 500 MAD', rating: 5, sales: 12, links: { details: '' } },
    { id: 702, name: 'Canapé Luna 3 Places · Bouclé', priceMad: 4500, formattedPrice: '4 500 MAD', inStock: true, thumbnail_image: '', has_discount: false, discount: null, stroked_price: '4 500 MAD', main_price: '4 500 MAD', rating: 5, sales: 8, links: { details: '' } },
    { id: 703, name: 'Table Basse Oval Plâtre', priceMad: 2200, formattedPrice: '2 200 MAD', inStock: false, thumbnail_image: '', has_discount: false, discount: null, stroked_price: '2 200 MAD', main_price: '2 200 MAD', rating: 5, sales: 5, links: { details: '' } },
  ]);

  const [itemToRemove, setItemToRemove] = useState<number | null>(null);
  const [altModalVisible, setAltModalVisible] = useState(false);

  const handleRemoveConfirm = () => {
    if (itemToRemove !== null) {
      setWishlistItems((prev) => prev.filter((i) => i.id !== itemToRemove));
      setItemToRemove(null);
    }
  };

  const copy = language === 'ar'
    ? { title: 'مفضلتي', empty: 'قائمة رغباتك فارغة', body: 'احفظ الأثاث والديكور الذي تحبه لتجده هنا.', cta: 'اكتشف المجموعات' }
    : { title: 'Mes favoris', empty: 'Votre liste d’envies est vide', body: 'Enregistrez vos meubles et décorations préférés pour les retrouver ici.', cta: 'Découvrir les collections' };

  return (
    <View style={styles.screen} accessibilityLabel={copy.title}>
      <View style={styles.header}>
        <MayushLogo width={132} height={39} />
        <MayushText variant="sectionTitle" color={colors.brand.navy900} style={styles.headerTitle}>
          {copy.title} ({wishlistItems.length})
        </MayushText>
        <TouchableOpacity accessibilityRole="button" onPress={onBrowseCollections} style={styles.iconBtn}>
          <MayushIcon name="search" size={22} color={colors.brand.navy900} />
        </TouchableOpacity>
      </View>

      <ScrollView contentContainerStyle={styles.content} showsVerticalScrollIndicator={false}>
        {wishlistItems.some((i) => i.oldPriceMad) ? (
          <View style={styles.priceDropBanner}>
            <MayushIcon name="trending-down" size={20} color={colors.semantic.error} />
            <View style={styles.bannerTextCol}>
              <MayushText variant="strongBody" color={colors.brand.navy900}>
                {language === 'ar' ? 'انخفاض في السعر!' : 'Baisse de prix détectée !'}
              </MayushText>
              <MayushText variant="caption" color={colors.neutral.gray700}>
                {language === 'ar' ? 'وفر 300 درهم على كرسي نوري الأخضر' : 'Économisez 300 MAD sur Fauteuil Nori Accent'}
              </MayushText>
            </View>
          </View>
        ) : null}

        {wishlistItems.length > 0 ? (
          <View style={styles.grid}>
            {wishlistItems.map((item) => (
              <View key={item.id} style={styles.card}>
                <TouchableOpacity onPress={() => onSelectProduct?.(item)} style={styles.imgBox}>
                  <Image source={item.id % 2 === 1 ? FAUTEUIL_IMG : CANAPE_IMG} style={styles.productImg} resizeMode="cover" />
                  <TouchableOpacity style={styles.removeBtn} onPress={() => setItemToRemove(item.id)}>
                    <MayushIcon name="x" size={16} color={colors.brand.navy900} />
                  </TouchableOpacity>
                  {!item.inStock ? (
                    <View style={styles.outOfStockBadge}>
                      <MayushText variant="caption" color={colors.surface.white} style={styles.badgeText}>
                        Rupture
                      </MayushText>
                    </View>
                  ) : null}
                </TouchableOpacity>

                <View style={styles.cardBody}>
                  <MayushText variant="strongBody" color={colors.brand.navy900} numberOfLines={1}>
                    {item.name}
                  </MayushText>
                  <View style={styles.priceRow}>
                    <MayushText variant="priceRegular" color={colors.brand.orange500}>
                      {item.formattedPrice}
                    </MayushText>
                    {item.oldPriceMad ? (
                      <MayushText variant="caption" color={colors.neutral.gray500} style={styles.oldPrice}>
                        {item.oldPriceMad} MAD
                      </MayushText>
                    ) : null}
                  </View>

                  {item.inStock ? (
                    <TouchableOpacity style={styles.moveCartBtn} onPress={() => onMoveToCart?.(item)}>
                      <MayushIcon name="shopping-bag" size={14} color={colors.surface.white} />
                      <MayushText variant="caption" color={colors.surface.white} style={styles.moveCartText}>
                        {language === 'ar' ? 'إضافة للسلة' : 'Ajouter au panier'}
                      </MayushText>
                    </TouchableOpacity>
                  ) : (
                    <TouchableOpacity style={styles.altBtn} onPress={() => setAltModalVisible(true)}>
                      <MayushText variant="caption" color={colors.brand.orange500}>
                        {language === 'ar' ? 'بدائل مشابهة' : 'Voir les alternatives'}
                      </MayushText>
                    </TouchableOpacity>
                  )}
                </View>
              </View>
            ))}
          </View>
        ) : (
          <View style={styles.emptyCard}>
            <Image source={WISHLIST_EMPTY_ARTWORK} style={styles.emptyImg} resizeMode="contain" />
            <MayushText variant="pageTitle" color={colors.brand.navy900} align="center" style={styles.emptyTitle}>
              {copy.empty}
            </MayushText>
            <MayushText variant="body" color={colors.neutral.gray700} align="center" style={styles.emptyCopy}>
              {copy.body}
            </MayushText>
            <TouchableOpacity style={styles.browseBtn} onPress={onBrowseCollections}>
              <MayushText variant="button" color={colors.surface.white}>
                {copy.cta}
              </MayushText>
            </TouchableOpacity>
          </View>
        )}
      </ScrollView>

      {/* Remove Confirmation Dialog (Node 309:674) */}
      <Modal visible={itemToRemove !== null} transparent animationType="fade" onRequestClose={() => setItemToRemove(null)}>
        <View style={styles.modalOverlay}>
          <View style={styles.dialogCard}>
            <MayushText variant="sectionTitle" color={colors.brand.navy900} align="center">
              Retirer des favoris ?
            </MayushText>
            <MayushText variant="smallBody" color={colors.neutral.gray700} align="center" style={styles.dialogCopy}>
              Voulez-vous vraiment retirer cet article de votre liste de favoris ?
            </MayushText>
            <View style={styles.dialogActions}>
              <TouchableOpacity style={styles.cancelBtn} onPress={() => setItemToRemove(null)}>
                <MayushText variant="strongBody" color={colors.brand.navy900}>
                  Annuler
                </MayushText>
              </TouchableOpacity>
              <TouchableOpacity style={styles.confirmBtn} onPress={handleRemoveConfirm}>
                <MayushText variant="strongBody" color={colors.surface.white}>
                  Retirer
                </MayushText>
              </TouchableOpacity>
            </View>
          </View>
        </View>
      </Modal>

      {/* Out of Stock Alternatives Modal (Node 309:672) */}
      <Modal visible={altModalVisible} transparent animationType="slide" onRequestClose={() => setAltModalVisible(false)}>
        <View style={styles.modalOverlay}>
          <View style={styles.altSheet}>
            <View style={styles.altHeader}>
              <MayushText variant="sectionTitle" color={colors.brand.navy900}>
                Articles similaires disponibles
              </MayushText>
              <TouchableOpacity onPress={() => setAltModalVisible(false)}>
                <MayushIcon name="x" size={22} color={colors.brand.navy900} />
              </TouchableOpacity>
            </View>
            <ScrollView contentContainerStyle={styles.altBody}>
              <TouchableOpacity style={styles.altItemRow} onPress={() => { setAltModalVisible(false); onBrowseCollections?.(); }}>
                <Image source={CANAPE_IMG} style={styles.altThumb} />
                <View style={styles.altMeta}>
                  <MayushText variant="strongBody" color={colors.brand.navy900}>
                    Canapé Luna 2 Places · Bouclé
                  </MayushText>
                  <MayushText variant="priceRegular" color={colors.brand.orange500}>
                    3 800 MAD
                  </MayushText>
                </View>
              </TouchableOpacity>
            </ScrollView>
          </View>
        </View>
      </Modal>

      <BottomTabBar activeTab="wishlist" onTabPress={(tab) => onNavigateTab?.(tab)} />
    </View>
  );
};

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
  iconBtn: { width: 40, height: 40, alignItems: 'center', justifyContent: 'center' },
  headerTitle: { fontSize: 17, fontWeight: '700' },
  content: { padding: 16, paddingBottom: 80 },
  priceDropBanner: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 12,
    borderRadius: radii.lg,
    backgroundColor: colors.brand.orange100,
    borderWidth: 1,
    borderColor: colors.surface.borderWarm,
    marginBottom: 16,
    gap: 10,
  },
  bannerTextCol: { flex: 1 },
  grid: { flexDirection: 'row', flexWrap: 'wrap', gap: 12 },
  card: {
    width: '48%',
    borderRadius: radii.lg,
    overflow: 'hidden',
    borderWidth: 1,
    borderColor: colors.surface.borderWarm,
    backgroundColor: colors.surface.white,
  },
  imgBox: { position: 'relative', height: 130 },
  productImg: { width: '100%', height: '100%' },
  removeBtn: {
    position: 'absolute',
    top: 6,
    right: 6,
    width: 28,
    height: 28,
    borderRadius: 14,
    backgroundColor: 'rgba(255,255,255,0.9)',
    alignItems: 'center',
    justifyContent: 'center',
  },
  outOfStockBadge: {
    position: 'absolute',
    bottom: 6,
    left: 6,
    backgroundColor: colors.neutral.gray700,
    paddingHorizontal: 6,
    paddingVertical: 2,
    borderRadius: radii.sm,
  },
  badgeText: { fontSize: 10, fontWeight: '700' },
  cardBody: { padding: 10 },
  priceRow: { flexDirection: 'row', alignItems: 'baseline', gap: 6, marginVertical: 4 },
  oldPrice: { textDecorationLine: 'line-through' },
  moveCartBtn: {
    marginTop: 6,
    height: 34,
    borderRadius: radii.md,
    backgroundColor: colors.brand.orange500,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 6,
  },
  moveCartText: { fontWeight: '700' },
  altBtn: {
    marginTop: 6,
    height: 34,
    borderRadius: radii.md,
    borderWidth: 1,
    borderColor: colors.brand.orange500,
    alignItems: 'center',
    justifyContent: 'center',
  },
  emptyCard: { alignItems: 'center', paddingVertical: 32 },
  emptyImg: { width: 160, height: 160, marginBottom: 16 },
  emptyTitle: { fontSize: 20, marginBottom: 8 },
  emptyCopy: { lineHeight: 20, maxWidth: 280, marginBottom: 20 },
  browseBtn: {
    paddingHorizontal: 24,
    paddingVertical: 12,
    borderRadius: radii.lg,
    backgroundColor: colors.brand.orange500,
  },
  modalOverlay: { flex: 1, backgroundColor: 'rgba(0,0,0,0.5)', justifyContent: 'center', alignItems: 'center', padding: 20 },
  dialogCard: { width: '100%', padding: 20, borderRadius: radii.xl, backgroundColor: colors.surface.white, alignItems: 'center' },
  dialogCopy: { marginVertical: 12, lineHeight: 18 },
  dialogActions: { flexDirection: 'row', gap: 12, width: '100%', marginTop: 8 },
  cancelBtn: { flex: 1, height: 44, borderRadius: radii.lg, borderWidth: 1, borderColor: colors.surface.borderWarm, alignItems: 'center', justifyContent: 'center' },
  confirmBtn: { flex: 1, height: 44, borderRadius: radii.lg, backgroundColor: colors.semantic.error, alignItems: 'center', justifyContent: 'center' },
  altSheet: { width: '100%', maxHeight: '60%', backgroundColor: colors.surface.white, borderRadius: radii.xl, padding: 16 },
  altHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 16 },
  altBody: { paddingBottom: 16 },
  altItemRow: { flexDirection: 'row', alignItems: 'center', padding: 10, borderRadius: radii.lg, borderWidth: 1, borderColor: colors.surface.borderWarm, gap: 12 },
  altThumb: { width: 50, height: 50, borderRadius: radii.md },
  altMeta: { flex: 1 },
});
