/**
 * CategoryLandingScreen (Figma Node 309:593 - 02-category-landing-salon-collections-fr)
 * Category landing page with subcategories, curated collections, and featured products.
 */

import React from 'react';
import {
  Image,
  ScrollView,
  StyleSheet,
  TouchableOpacity,
  View,
} from 'react-native';
import { CategoryDto, ProductMiniDto } from '../../contracts/api/dto';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { useTheme } from '../../design-system/theme/useTheme';
import { colors } from '../../design-system/tokens/colors';
import { radii } from '../../design-system/tokens/radii';

const SALON_HERO = require('../../../assets/reference-art/home-hero-scene.png');
const CANAPE_THUMB = require('../../../assets/reference-art/home-new-luna.png');
const FAUTEUIL_THUMB = require('../../../assets/reference-art/home-new-nori.png');

export interface CategoryLandingScreenProps {
  category?: CategoryDto;
  onBack: () => void;
  onSelectSubcategory: (subcat: string) => void;
  onOpenCollection: (collectionId: string) => void;
  onSelectProduct: (product: ProductMiniDto) => void;
  onOpenSearch: () => void;
}

export const CategoryLandingScreen: React.FC<CategoryLandingScreenProps> = ({
  category,
  onBack,
  onSelectSubcategory,
  onOpenCollection,
  onSelectProduct,
  onOpenSearch,
}) => {
  const { isRTL, language } = useTheme();
  const title = category ? (language === 'ar' ? category.nameAr || category.name : category.name) : 'Salon & Séjour';

  const subcategories = [
    { id: 'canapes', name: language === 'ar' ? 'أرائك' : 'Canapés', image: CANAPE_THUMB },
    { id: 'fauteuils', name: language === 'ar' ? 'كراسي مريحة' : 'Fauteuils', image: FAUTEUIL_THUMB },
    { id: 'tables-basses', name: language === 'ar' ? 'طاولات قهوة' : 'Tables Basses', image: CANAPE_THUMB },
    { id: 'meubles-tv', name: language === 'ar' ? 'خزائن تلفزيون' : 'Meubles TV', image: FAUTEUIL_THUMB },
  ];

  const featuredProducts: ProductMiniDto[] = [
    { id: 201, name: 'Canapé Luna 3 Places', priceMad: 4500, formattedPrice: '4 500 MAD', thumbnail_image: 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?q=80&w=350&auto=format&fit=crop', has_discount: false, discount: null, stroked_price: '4 500 MAD', main_price: '4 500 MAD', rating: 5, sales: 12, links: { details: '' } },
    { id: 202, name: 'Fauteuil Nori Accent', priceMad: 1800, formattedPrice: '1 800 MAD', thumbnail_image: 'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?q=80&w=350&auto=format&fit=crop', has_discount: false, discount: null, stroked_price: '1 800 MAD', main_price: '1 800 MAD', rating: 5, sales: 8, links: { details: '' } },
  ];

  return (
    <View style={styles.screen} accessibilityLabel="Category Landing Screen">
      <View style={styles.header}>
        <TouchableOpacity accessibilityRole="button" onPress={onBack} style={styles.iconBtn}>
          <MayushIcon name={isRTL ? 'arrow-right' : 'arrow-left'} size={24} color={colors.brand.navy900} />
        </TouchableOpacity>
        <MayushText variant="sectionTitle" color={colors.brand.navy900} style={styles.headerTitle}>
          {title}
        </MayushText>
        <TouchableOpacity accessibilityRole="button" onPress={onOpenSearch} style={styles.iconBtn}>
          <MayushIcon name="search" size={22} color={colors.brand.navy900} />
        </TouchableOpacity>
      </View>

      <ScrollView contentContainerStyle={styles.content} showsVerticalScrollIndicator={false}>
        <View style={styles.heroCard}>
          <Image source={SALON_HERO} style={styles.heroImage} resizeMode="cover" />
          <View style={styles.heroOverlay}>
            <MayushText variant="caption" color={colors.brand.orange500} style={styles.badge}>
              Collection 2026
            </MayushText>
            <MayushText variant="pageTitle" color={colors.surface.white} style={styles.heroTitle}>
              Salon Contemporain
            </MayushText>
            <MayushText variant="smallBody" color={colors.surface.cream} style={styles.heroSubtitle}>
              L'élégance et le confort sur-mesure pour votre intérieur.
            </MayushText>
          </View>
        </View>

        <MayushText variant="sectionTitle" color={colors.brand.navy900} style={styles.sectionHeading}>
          {language === 'ar' ? 'الفئات الفرعية' : 'Catégories populaires'}
        </MayushText>

        <View style={styles.subcatGrid}>
          {subcategories.map((sub) => (
            <TouchableOpacity
              key={sub.id}
              style={styles.subcatCard}
              onPress={() => onSelectSubcategory(sub.id)}
              activeOpacity={0.8}
            >
              <Image source={sub.image} style={styles.subcatImage} resizeMode="cover" />
              <View style={styles.subcatLabelBar}>
                <MayushText variant="strongBody" color={colors.brand.navy900}>
                  {sub.name}
                </MayushText>
              </View>
            </TouchableOpacity>
          ))}
        </View>

        <TouchableOpacity
          style={styles.lookBanner}
          onPress={() => onOpenCollection('salon-contemporain')}
          activeOpacity={0.85}
        >
          <View style={styles.lookTextCol}>
            <MayushText variant="caption" color={colors.brand.orange500}>
              Shop the Look
            </MayushText>
            <MayushText variant="sectionTitle" color={colors.brand.navy900} style={styles.lookTitle}>
              Inspiration Salon Minimaliste
            </MayushText>
            <MayushText variant="caption" color={colors.neutral.gray700}>
              Découvrez la sélection coordonnée.
            </MayushText>
          </View>
          <MayushIcon name={isRTL ? 'chevron-left' : 'chevron-right'} size={24} color={colors.brand.orange500} />
        </TouchableOpacity>

        <MayushText variant="sectionTitle" color={colors.brand.navy900} style={styles.sectionHeading}>
          {language === 'ar' ? 'المنتجات المميزة' : 'Sélection du moment'}
        </MayushText>

        <View style={styles.productGrid}>
          {featuredProducts.map((prod) => (
            <TouchableOpacity
              key={prod.id}
              style={styles.productCard}
              onPress={() => onSelectProduct(prod)}
              activeOpacity={0.8}
            >
              <Image source={prod.id === 201 ? CANAPE_THUMB : FAUTEUIL_THUMB} style={styles.productImg} resizeMode="cover" />
              <View style={styles.productMeta}>
                <MayushText variant="strongBody" color={colors.brand.navy900} numberOfLines={1}>
                  {prod.name}
                </MayushText>
                <MayushText variant="priceRegular" color={colors.brand.orange500}>
                  {prod.formattedPrice}
                </MayushText>
              </View>
            </TouchableOpacity>
          ))}
        </View>
      </ScrollView>
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
  content: { padding: 16, paddingBottom: 32 },
  heroCard: {
    height: 190,
    borderRadius: radii.xl,
    overflow: 'hidden',
    marginBottom: 20,
    position: 'relative',
  },
  heroImage: { width: '100%', height: '100%' },
  heroOverlay: {
    ...StyleSheet.absoluteFill,
    backgroundColor: 'rgba(16,29,53,0.45)',
    padding: 16,
    justifyContent: 'flex-end',
  },
  badge: { textTransform: 'uppercase', letterSpacing: 0.5, fontWeight: '700' },
  heroTitle: { fontSize: 22, marginTop: 4 },
  heroSubtitle: { marginTop: 2 },
  sectionHeading: { marginBottom: 12 },
  subcatGrid: { flexDirection: 'row', flexWrap: 'wrap', gap: 12, marginBottom: 20 },
  subcatCard: {
    width: '48%',
    height: 120,
    borderRadius: radii.lg,
    overflow: 'hidden',
    borderWidth: 1,
    borderColor: colors.surface.borderWarm,
  },
  subcatImage: { width: '100%', height: 85 },
  subcatLabelBar: {
    height: 35,
    backgroundColor: colors.surface.white,
    alignItems: 'center',
    justifyContent: 'center',
  },
  lookBanner: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    padding: 16,
    borderRadius: radii.xl,
    backgroundColor: colors.brand.orange100,
    marginBottom: 20,
  },
  lookTextCol: { flex: 1, marginRight: 8 },
  lookTitle: { fontSize: 16, marginVertical: 2 },
  productGrid: { flexDirection: 'row', gap: 12 },
  productCard: {
    flex: 1,
    borderRadius: radii.lg,
    overflow: 'hidden',
    borderWidth: 1,
    borderColor: colors.surface.borderWarm,
    backgroundColor: colors.surface.white,
  },
  productImg: { width: '100%', height: 130 },
  productMeta: { padding: 10 },
});
