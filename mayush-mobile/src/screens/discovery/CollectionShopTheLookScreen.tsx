/**
 * CollectionShopTheLookScreen (Figma Node 309:595 - 02-collection-salon-contemporain-shop-the-look)
 * Curated interior look with tagged room photography and one-click item addition.
 */

import React from 'react';
import {
  Image,
  ScrollView,
  StyleSheet,
  TouchableOpacity,
  View,
} from 'react-native';
import { ProductMiniDto } from '../../contracts/api/dto';
import { PrimaryButton } from '../../design-system/components/actions/PrimaryButton';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { useTheme } from '../../design-system/theme/useTheme';
import { colors } from '../../design-system/tokens/colors';
import { radii } from '../../design-system/tokens/radii';

const ROOM_HERO = require('../../../assets/reference-art/home-hero-scene.png');
const CANAPE_IMG = require('../../../assets/reference-art/home-new-luna.png');
const FAUTEUIL_IMG = require('../../../assets/reference-art/home-new-nori.png');

export interface CollectionShopTheLookScreenProps {
  collectionTitle?: string;
  heroImage?: string;
  items?: ProductMiniDto[];
  onBack: () => void;
  onSelectProduct: (product: ProductMiniDto) => void;
  onAddAllToCart: () => void;
  onOpenFilter?: () => void;
}

const FALLBACK_ITEMS: ProductMiniDto[] = [
  { id: 301, name: 'Canapé Luna 3 Places · Bouclé Beige', priceMad: 4500, formattedPrice: '4 500 MAD', thumbnail_image: '', has_discount: false, discount: null, stroked_price: '4 500 MAD', main_price: '4 500 MAD', rating: 5, sales: 10, links: { details: '' } },
  { id: 302, name: 'Fauteuil Nori Accent · Vert Sauge', priceMad: 1800, formattedPrice: '1 800 MAD', thumbnail_image: '', has_discount: false, discount: null, stroked_price: '1 800 MAD', main_price: '1 800 MAD', rating: 5, sales: 5, links: { details: '' } },
  { id: 303, name: 'Table Basse Oval Plâtre', priceMad: 2200, formattedPrice: '2 200 MAD', thumbnail_image: '', has_discount: false, discount: null, stroked_price: '2 200 MAD', main_price: '2 200 MAD', rating: 5, sales: 7, links: { details: '' } },
];

export const CollectionShopTheLookScreen: React.FC<CollectionShopTheLookScreenProps> = ({
  collectionTitle = 'Salon Contemporain',
  heroImage,
  items: itemsProp,
  onBack,
  onSelectProduct,
  onAddAllToCart,
}) => {
  const { isRTL } = useTheme();

  const items = itemsProp && itemsProp.length > 0 ? itemsProp : FALLBACK_ITEMS;

  return (
    <View style={styles.screen} accessibilityLabel="Collection Shop the Look Screen">
      <View style={styles.header}>
        <TouchableOpacity accessibilityRole="button" onPress={onBack} style={styles.iconBtn}>
          <MayushIcon name={isRTL ? 'arrow-right' : 'arrow-left'} size={24} color={colors.brand.navy900} />
        </TouchableOpacity>
        <MayushText variant="sectionTitle" color={colors.brand.navy900} style={styles.headerTitle}>
          Shop the Look
        </MayushText>
        <View style={styles.iconBtn} />
      </View>

      <ScrollView contentContainerStyle={styles.content} showsVerticalScrollIndicator={false}>
        <View style={styles.sceneCard}>
          <Image source={heroImage ? { uri: heroImage } : ROOM_HERO} style={styles.sceneImg} resizeMode="cover" />
          <View style={styles.hotspot1}>
            <View style={styles.dot} />
          </View>
          <View style={styles.hotspot2}>
            <View style={styles.dot} />
          </View>
        </View>

        <View style={styles.titleRow}>
          <MayushText variant="pageTitle" color={colors.brand.navy900}>
            {collectionTitle}
          </MayushText>
          <MayushText variant="smallBody" color={colors.neutral.gray700} style={styles.subtitle}>
            {items.length} article{items.length !== 1 ? 's' : ''} associé{items.length !== 1 ? 's' : ''} dans cette pièce d'inspiration.
          </MayushText>
        </View>

        {items.map((item, idx) => (
          <TouchableOpacity
            key={item.id}
            style={styles.itemRow}
            onPress={() => onSelectProduct(item)}
            activeOpacity={0.8}
          >
            <Image source={idx % 2 === 0 ? CANAPE_IMG : FAUTEUIL_IMG} style={styles.itemThumb} resizeMode="cover" />
            <View style={styles.itemMeta}>
              <MayushText variant="strongBody" color={colors.brand.navy900}>
                {item.name}
              </MayushText>
              <MayushText variant="priceRegular" color={colors.brand.orange500}>
                {item.formattedPrice}
              </MayushText>
            </View>
            <MayushIcon name={isRTL ? 'chevron-left' : 'chevron-right'} size={20} color={colors.neutral.gray500} />
          </TouchableOpacity>
        ))}
      </ScrollView>

      <View style={styles.footer}>
        <PrimaryButton
          label={`Ajouter le look complet (${items.reduce((sum, it) => sum + (it.priceMad ?? 0), 0).toLocaleString('fr-MA')} MAD)`}
          onPress={onAddAllToCart}
        />
      </View>
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
  headerTitle: { fontSize: 18, fontWeight: '700' },
  content: { padding: 16, paddingBottom: 100 },
  sceneCard: {
    height: 240,
    borderRadius: radii.xl,
    overflow: 'hidden',
    marginBottom: 16,
    position: 'relative',
  },
  sceneImg: { width: '100%', height: '100%' },
  hotspot1: {
    position: 'absolute',
    top: 90,
    left: 120,
    width: 28,
    height: 28,
    borderRadius: 14,
    backgroundColor: 'rgba(255,255,255,0.7)',
    alignItems: 'center',
    justifyContent: 'center',
  },
  hotspot2: {
    position: 'absolute',
    top: 140,
    right: 90,
    width: 28,
    height: 28,
    borderRadius: 14,
    backgroundColor: 'rgba(255,255,255,0.7)',
    alignItems: 'center',
    justifyContent: 'center',
  },
  dot: { width: 12, height: 12, borderRadius: 6, backgroundColor: colors.brand.orange500 },
  titleRow: { marginBottom: 16 },
  subtitle: { marginTop: 4 },
  itemRow: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 12,
    borderRadius: radii.lg,
    borderWidth: 1,
    borderColor: colors.surface.borderWarm,
    backgroundColor: colors.surface.white,
    marginBottom: 10,
  },
  itemThumb: { width: 60, height: 60, borderRadius: radii.md, marginRight: 12 },
  itemMeta: { flex: 1 },
  footer: {
    position: 'absolute',
    bottom: 0,
    left: 0,
    right: 0,
    padding: 16,
    backgroundColor: colors.surface.white,
    borderTopWidth: 1,
    borderTopColor: colors.surface.borderWarm,
  },
});
