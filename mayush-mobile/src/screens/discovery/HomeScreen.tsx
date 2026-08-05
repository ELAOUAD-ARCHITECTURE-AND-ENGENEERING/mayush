import React, { useEffect, useRef, useState } from 'react';
import { Image, ImageSourcePropType, ScrollView, StyleSheet, TouchableOpacity, useWindowDimensions, View } from 'react-native';
import { CategoryDto, ProductMiniDto } from '../../contracts/api/dto';
import { ProductCard } from '../../design-system/components/commerce/ProductCard';
import { MayushLogo } from '../../design-system/components/brand/MayushLogo';
import { BottomTabBar, TabKey } from '../../design-system/components/navigation/BottomTabBar';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { TextField } from '../../design-system/components/forms/TextField';
import { colors } from '../../design-system/tokens/colors';
import { useTheme } from '../../design-system/theme/useTheme';
import { formatStorePrice } from '../../config/currency';

const HERO_SCENE = require('../../../assets/reference-art/home-hero-scene.png');
const PREMIUM_HERO_SCENE = require('../../../assets/reference-art/home-hero-premium-scene.png');
const CATEGORY_HERO_SCENE = require('../../../assets/reference-art/home-hero-category-scene.png');
const CATEGORY_ARTWORK: ImageSourcePropType[] = [
  require('../../../assets/reference-art/home-category-salon.png'),
  require('../../../assets/reference-art/home-category-dining.png'),
  require('../../../assets/reference-art/home-category-bedroom.png'),
  require('../../../assets/reference-art/home-category-lighting.png'),
  require('../../../assets/reference-art/home-category-decor.png'),
];
const NEW_ARTWORK: ImageSourcePropType[] = [
  require('../../../assets/reference-art/home-new-luna.png'),
  require('../../../assets/reference-art/home-new-kyoto.png'),
  require('../../../assets/reference-art/home-new-eve.png'),
  require('../../../assets/reference-art/home-new-nori.png'),
];
const BEST_ARTWORK: ImageSourcePropType[] = [
  require('../../../assets/reference-art/home-best-solis.png'),
  require('../../../assets/reference-art/home-best-aria.png'),
  require('../../../assets/reference-art/home-best-elegance.png'),
  require('../../../assets/reference-art/home-best-linea.png'),
];
const INSPIRATION_ARTWORK: ImageSourcePropType[] = [
  require('../../../assets/reference-art/home-inspiration-japandi.png'),
  require('../../../assets/reference-art/home-inspiration-natural.png'),
];

interface ShowcaseCategory {
  category: CategoryDto;
  art: ImageSourcePropType;
}

interface ShowcaseProduct {
  product: ProductMiniDto;
  art: ImageSourcePropType;
}

interface HeroSlide {
  art: ImageSourcePropType;
  titleFr: string;
  accentFr?: string;
  bodyFr: string;
  ctaFr: string;
  titleAr: string;
  bodyAr: string;
  ctaAr: string;
}

const HERO_SLIDES: HeroSlide[] = [
  {
    art: HERO_SCENE,
    titleFr: 'L\u2019art d\u2019habiter\nselon ',
    accentFr: 'vos envies',
    bodyFr: 'Mobilier & d\u00e9coration haut de gamme\ns\u00e9lectionn\u00e9s avec passion.',
    ctaFr: 'D\u00e9couvrir la collection',
    titleAr: '\u0641\u0646 \u0627\u0644\u0633\u0643\u0646\n\u0643\u0645\u0627 \u062a\u062d\u0628',
    bodyAr: '\u0623\u062b\u0627\u062b \u0648\u062f\u064a\u0643\u0648\u0631 \u0645\u062e\u062a\u0627\u0631 \u0628\u0634\u063a\u0641.',
    ctaAr: '\u0627\u0643\u062a\u0634\u0641 \u0627\u0644\u0645\u062c\u0645\u0648\u0639\u0629',
  },
  {
    art: PREMIUM_HERO_SCENE,
    titleFr: 'Design. Qualit\u00e9.\nExcellence.',
    bodyFr: 'Des pi\u00e8ces uniques pour sublimer\nvos espaces.',
    ctaFr: 'D\u00e9couvrir maintenant',
    titleAr: '\u062a\u0635\u0645\u064a\u0645. \u062c\u0648\u062f\u0629.\n\u062a\u0645\u064a\u0651\u0632.',
    bodyAr: '\u0642\u0637\u0639 \u0645\u0645\u064a\u0632\u0629 \u062a\u0631\u062a\u0642\u064a \u0628\u0645\u0633\u0627\u062d\u0627\u062a\u0643.',
    ctaAr: '\u0627\u0643\u062a\u0634\u0641 \u0627\u0644\u0622\u0646',
  },
  {
    art: CATEGORY_HERO_SCENE,
    titleFr: 'Chaque pi\u00e8ce,\n\u00e0 sa place.',
    bodyFr: 'Composez un int\u00e9rieur qui vous\nressemble, simplement.',
    ctaFr: 'Voir les cat\u00e9gories',
    titleAr: '\u0635\u0645\u0651\u0645 \u0645\u0646\u0632\u0644\u0643\n\u0628\u0630\u0648\u0642\u0643.',
    bodyAr: '\u0627\u062e\u062a\u0631 \u0642\u0637\u0639\u0643 \u0648\u0631\u062a\u0651\u0628 \u0645\u0633\u0627\u062d\u062a\u0643 \u0628\u0633\u0647\u0648\u0644\u0629.',
    ctaAr: '\u062a\u0633\u0648\u0651\u0642 \u062d\u0633\u0628 \u0627\u0644\u0641\u0626\u0629',
  },
];

const category = (id: number, name: string): CategoryDto => ({
  id,
  name,
  banner: '',
  icon: '',
  number_of_children: 0,
  links: { products: '', sub_categories: '' },
});

const product = (id: number, name: string, price: string, rating = 0, sales = 0): ProductMiniDto => ({
  id,
  name,
  thumbnail_image: '',
  has_discount: false,
  discount: null,
  stroked_price: '',
  main_price: price,
  rating,
  sales,
  links: { details: '' },
});

const CATEGORIES: ShowcaseCategory[] = [
  { category: category(1, 'Salon'), art: CATEGORY_ARTWORK[0] },
  { category: category(2, '\u0053alle \u00e0 manger'), art: CATEGORY_ARTWORK[1] },
  { category: category(3, 'Chambre'), art: CATEGORY_ARTWORK[2] },
  { category: category(4, '\u00c9clairage'), art: CATEGORY_ARTWORK[3] },
  { category: category(5, 'D\u00e9coration'), art: CATEGORY_ARTWORK[4] },
];

const NEW_ARRIVALS: ShowcaseProduct[] = [
  { product: product(101, 'Fauteuil Luna', formatStorePrice('589,00')), art: NEW_ARTWORK[0] },
  { product: product(102, 'Buffet Kyoto', formatStorePrice('1 249,00')), art: NEW_ARTWORK[1] },
  { product: product(103, 'Table basse \u00c8ve', formatStorePrice('479,00')), art: NEW_ARTWORK[2] },
  { product: product(104, 'Suspension Nori', formatStorePrice('199,00')), art: NEW_ARTWORK[3] },
];

const BEST_SELLERS: ShowcaseProduct[] = [
  { product: product(201, 'Canap\u00e9 modulable Solis', formatStorePrice('1 890,00'), 5, 128), art: BEST_ARTWORK[0] },
  { product: product(202, '\u0054able \u00e0 manger Aria', formatStorePrice('1 390,00'), 5, 96), art: BEST_ARTWORK[1] },
  { product: product(203, 'Chaise Velours \u00c9l\u00e9gance', formatStorePrice('189,00'), 5, 75), art: BEST_ARTWORK[2] },
  { product: product(204, '\u00c9tag\u00e8re Linea', formatStorePrice('329,00'), 5, 64), art: BEST_ARTWORK[3] },
];

export interface HomeScreenProps {
  onSelectCategory?: (category: CategoryDto) => void;
  onSelectProduct?: (product: ProductMiniDto) => void;
  onNavigateTab?: (tab: TabKey) => void;
  activeTab?: TabKey;
}

export const HomeScreen: React.FC<HomeScreenProps> = ({ onSelectCategory, onSelectProduct, onNavigateTab, activeTab = 'home' }) => {
  const { language, isRTL } = useTheme();
  const { width } = useWindowDimensions();
  const contentPadding = Math.max(20, Math.round(width * 0.031));
  const contentWidth = Math.max(280, width - contentPadding * 2);
  const categorySize = Math.max(58, Math.round(contentWidth * 0.107));
  const productWidth = Math.max(164, Math.round(contentWidth * 0.242));
  const logoWidth = Math.max(142, Math.min(280, Math.round(width * 0.30)));
  const heroTitleSize = Math.max(25, Math.min(39, Math.round(width * 0.04)));
  const heading = (fr: string, ar: string) => isRTL ? ar : fr;
  const heroPagerRef = useRef<ScrollView>(null);
  const [activeHeroIndex, setActiveHeroIndex] = useState(0);
  const heroHeight = Math.round(contentWidth / 2.93);

  useEffect(() => {
    const timer = setInterval(() => {
      setActiveHeroIndex((currentIndex) => {
        const nextIndex = (currentIndex + 1) % HERO_SLIDES.length;
        heroPagerRef.current?.scrollTo({ x: nextIndex * contentWidth, animated: true });
        return nextIndex;
      });
    }, 4600);

    return () => clearInterval(timer);
  }, [contentWidth]);

  const selectHeroSlide = (index: number) => {
    setActiveHeroIndex(index);
    heroPagerRef.current?.scrollTo({ x: index * contentWidth, animated: true });
  };

  const translatedCategories = CATEGORIES.map((entry, index) => ({
    ...entry,
    category: {
      ...entry.category,
      name: heading(entry.category.name, ['\u0635\u0627\u0644\u0648\u0646', '\u063a\u0631\u0641\u0629 \u0627\u0644\u0637\u0639\u0627\u0645', '\u063a\u0631\u0641\u0629 \u0627\u0644\u0646\u0648\u0645', '\u0625\u0636\u0627\u0621\u0629', '\u062f\u064a\u0643\u0648\u0631'][index]),
    },
  }));

  return (
    <View style={styles.container} accessibilityLabel={heading('Accueil', '\u0627\u0644\u0631\u0626\u064a\u0633\u064a\u0629')}>
      <ScrollView showsVerticalScrollIndicator={false} contentContainerStyle={[styles.content, { paddingHorizontal: contentPadding }]}> 
        <View style={[styles.header, isRTL && styles.rowReverse]}>
          <TouchableOpacity accessibilityRole="button" accessibilityLabel={heading('Notifications', '\u0627\u0644\u0625\u0634\u0639\u0627\u0631\u0627\u062a')} style={styles.headerButton} activeOpacity={0.72}>
            <MayushIcon name="bell" size={28} color={colors.brand.navy900} />
          </TouchableOpacity>
          <MayushLogo width={logoWidth} height={Math.round(logoWidth * 0.31)} />
          <TouchableOpacity accessibilityRole="button" accessibilityLabel={heading('Panier', '\u0627\u0644\u0633\u0644\u0629')} onPress={() => onNavigateTab?.('cart')} style={styles.headerButton} activeOpacity={0.72}>
            <MayushIcon name="shopping-cart" size={30} color={colors.brand.navy900} />
            <View style={styles.cartBadge}><MayushText variant="caption" color={colors.surface.white} style={styles.badgeText}>2</MayushText></View>
          </TouchableOpacity>
        </View>

        <TextField
          placeholder={heading('Rechercher un produit, une collection...', '\u0627\u0628\u062d\u062b \u0639\u0646 \u0645\u0646\u062a\u062c \u0623\u0648 \u0645\u062c\u0645\u0648\u0639\u0629...')}
          value=""
          leftIcon="search"
          accessibilityLabel={heading('Rechercher', '\u0628\u062d\u062b')}
        />

        <View style={[styles.hero, { height: heroHeight }]} accessibilityRole="adjustable" accessibilityLabel={heading('Carrousel de collections', '\u0639\u0627\u0631\u0636 \u0627\u0644\u0645\u062c\u0645\u0648\u0639\u0627\u062a')}>
          <ScrollView
            ref={heroPagerRef}
            horizontal
            pagingEnabled
            decelerationRate="fast"
            showsHorizontalScrollIndicator={false}
            onMomentumScrollEnd={({ nativeEvent }) => setActiveHeroIndex(Math.round(nativeEvent.contentOffset.x / contentWidth))}
          >
            {HERO_SLIDES.map((slide) => (
              <View key={slide.titleFr} style={[styles.heroSlide, { width: contentWidth }]}>
                <Image source={slide.art} resizeMode="cover" style={styles.heroImage} />
                <View style={[styles.heroCopyPanel, isRTL && styles.heroCopyPanelRtl]}>
                  <MayushText variant="display" color={colors.surface.white} style={[styles.heroTitle, { fontSize: heroTitleSize, lineHeight: Math.round(heroTitleSize * 1.08) }, isRTL && styles.rtlText]}>
                    {heading(slide.titleFr, slide.titleAr)}
                    {!isRTL && slide.accentFr ? <MayushText variant="display" color={colors.brand.orange500} style={[styles.heroTitle, { fontSize: heroTitleSize, lineHeight: Math.round(heroTitleSize * 1.08) }]}>{slide.accentFr}</MayushText> : null}
                  </MayushText>
                  <MayushText variant="body" color={colors.surface.white} style={[styles.heroBody, isRTL && styles.rtlText]}>{heading(slide.bodyFr, slide.bodyAr)}</MayushText>
                  <TouchableOpacity accessibilityRole="button" accessibilityLabel={heading(slide.ctaFr, slide.ctaAr)} onPress={() => onNavigateTab?.('categories')} activeOpacity={0.82} style={styles.heroCta}>
                    <MayushText variant="button" color={colors.surface.white} style={styles.heroCtaLabel}>{heading(slide.ctaFr, slide.ctaAr)}</MayushText>
                  </TouchableOpacity>
                </View>
              </View>
            ))}
          </ScrollView>
          <View style={[styles.dots, isRTL && styles.rowReverse]}>
            {HERO_SLIDES.map((slide, index) => <TouchableOpacity key={slide.titleFr} accessibilityRole="button" accessibilityLabel={heading(`Afficher la diapositive ${index + 1}`, `\u0639\u0631\u0636 \u0627\u0644\u0634\u0631\u064a\u062d\u0629 ${index + 1}`)} onPress={() => selectHeroSlide(index)} hitSlop={8} style={[styles.dot, index === activeHeroIndex && styles.dotActive]} />)}
          </View>
        </View>

        <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={[styles.categoryRow, { gap: Math.max(14, Math.round(contentWidth * 0.044)) }]}>
          {translatedCategories.map(({ category: item, art }) => (
            <TouchableOpacity key={item.id} accessibilityRole="button" accessibilityLabel={item.name} activeOpacity={0.82} style={[styles.categoryItem, { width: categorySize }]} onPress={() => onSelectCategory?.(item)}>
              <Image source={art} style={{ width: categorySize, height: categorySize, borderRadius: categorySize / 2 }} resizeMode="cover" />
              <MayushText variant="caption" color={colors.brand.navy900} align="center" numberOfLines={1} style={styles.categoryLabel}>{item.name}</MayushText>
            </TouchableOpacity>
          ))}
          <TouchableOpacity accessibilityRole="button" accessibilityLabel={heading('Voir toutes les cat\u00e9gories', '\u0639\u0631\u0636 \u0643\u0644 \u0627\u0644\u0641\u0626\u0627\u062a')} activeOpacity={0.82} style={[styles.categoryItem, { width: categorySize }]} onPress={() => onNavigateTab?.('categories')}>
            <View style={[styles.moreCircle, { width: categorySize, height: categorySize, borderRadius: categorySize / 2 }]}><MayushIcon name="more-horizontal" size={Math.max(22, categorySize * 0.30)} color={colors.brand.navy900} /></View>
            <MayushText variant="caption" color={colors.brand.navy900} align="center" style={styles.categoryLabel}>{heading('Voir tout', '\u0639\u0631\u0636 \u0627\u0644\u0643\u0644')}</MayushText>
          </TouchableOpacity>
        </ScrollView>

        <SectionHeader label={heading('Nouveaut\u00e9s', '\u0648\u0635\u0648\u0644 \u062c\u062f\u064a\u062f')} action={heading('Voir tout', '\u0639\u0631\u0636 \u0627\u0644\u0643\u0644')} />
        <ProductRail products={NEW_ARRIVALS} cardWidth={productWidth} onSelect={onSelectProduct} />

        <SectionHeader label={heading('Meilleures ventes', '\u0627\u0644\u0623\u0643\u062b\u0631 \u0645\u0628\u064a\u0639\u064b\u0627')} action={heading('Voir tout', '\u0639\u0631\u0636 \u0627\u0644\u0643\u0644')} />
        <ProductRail products={BEST_SELLERS} cardWidth={productWidth} onSelect={onSelectProduct} showRating />

        <View style={styles.offerBanner}>
          <View style={styles.offerIcon}><MayushIcon name="tag" size={23} color={colors.brand.orange500} /></View>
          <View style={styles.offerCopy}><MayushText variant="sectionTitle" color={colors.brand.navy900} style={styles.offerTitle}>{heading('Offres du moment', '\u0639\u0631\u0648\u0636 \u0627\u0644\u0644\u062d\u0638\u0629')}</MayushText><MayushText variant="smallBody" color={colors.neutral.gray700}>{heading('Jusqu\u2019\u00e0 -20% sur une s\u00e9lection de pi\u00e8ces d\u2019exception.', '\u062d\u062a\u0649 20% \u0639\u0644\u0649 \u0645\u0646\u062a\u062c\u0627\u062a \u0645\u062e\u062a\u0627\u0631\u0629.')}</MayushText></View>
          <TouchableOpacity accessibilityRole="button" accessibilityLabel={heading('Profiter des offres', '\u0627\u0633\u062a\u0641\u062f \u0645\u0646 \u0627\u0644\u0639\u0631\u0648\u0636')} style={styles.offerButton}><MayushText variant="smallBody" color={colors.brand.orange500} style={styles.offerButtonLabel}>{heading('En profiter', '\u0627\u0633\u062a\u0641\u062f')}</MayushText></TouchableOpacity>
        </View>

        <SectionHeader label={heading('Inspiration du moment', '\u0625\u0644\u0647\u0627\u0645 \u0627\u0644\u064a\u0648\u0645')} action={heading('Voir tout', '\u0639\u0631\u0636 \u0627\u0644\u0643\u0644')} />
        <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.inspirationRail}>
          <InspirationCard source={INSPIRATION_ARTWORK[0]} width={Math.round(contentWidth * 0.488)} />
          <InspirationCard source={INSPIRATION_ARTWORK[1]} width={Math.round(contentWidth * 0.488)} />
        </ScrollView>
      </ScrollView>
      <BottomTabBar activeTab={activeTab} onTabPress={onNavigateTab ?? (() => undefined)} cartBadgeCount={2} />
    </View>
  );
};

const SectionHeader: React.FC<{ label: string; action: string }> = ({ label, action }) => <View style={styles.sectionHeader}><MayushText variant="sectionTitle" color={colors.brand.navy900} style={styles.sectionTitle}>{label}</MayushText><MayushText variant="smallBody" color={colors.brand.orange500} style={styles.seeAll}>{action}</MayushText></View>;

const ProductRail: React.FC<{ products: ShowcaseProduct[]; cardWidth: number; onSelect?: (product: ProductMiniDto) => void; showRating?: boolean }> = ({ products, cardWidth, onSelect, showRating }) => (
  <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.productRail}>
    {products.map(({ product: item, art }) => <ProductCard key={item.id} name={item.name} thumbnailUrl="" thumbnailSource={art} currentPriceFormatted={item.main_price} hasDiscount={false} rating={showRating ? item.rating : undefined} salesCount={showRating ? item.sales : undefined} width={cardWidth} onPress={() => onSelect?.(item)} />)}
  </ScrollView>
);

const InspirationCard: React.FC<{ source: ImageSourcePropType; width: number }> = ({ source, width }) => <View style={[styles.inspirationCard, { width, height: Math.round(width * (143 / 432)) }]}><Image source={source} style={styles.inspirationImage} resizeMode="cover" /></View>;

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#FFFCF8' },
  content: { paddingTop: 18, paddingBottom: 28, gap: 20 },
  header: { height: 65, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' },
  rowReverse: { flexDirection: 'row-reverse' },
  headerButton: { width: 48, height: 48, justifyContent: 'center', alignItems: 'center', position: 'relative' },
  cartBadge: { position: 'absolute', right: 0, top: 2, minWidth: 22, height: 22, borderRadius: 11, backgroundColor: colors.brand.orange500, alignItems: 'center', justifyContent: 'center' },
  badgeText: { fontWeight: '700', fontSize: 12 },
  hero: { width: '100%', overflow: 'hidden', borderRadius: 22, backgroundColor: colors.brand.navy900 },
  heroSlide: { height: '100%', overflow: 'hidden', backgroundColor: colors.brand.navy900 },
  heroImage: { position: 'absolute', right: 0, width: '66%', height: '100%' },
  heroCopyPanel: { flex: 1, width: '57%', paddingLeft: 40, justifyContent: 'center', alignItems: 'flex-start', zIndex: 1 },
  heroCopyPanelRtl: { alignItems: 'flex-end', alignSelf: 'flex-end', paddingLeft: 0, paddingRight: 28 },
  heroTitle: { fontFamily: 'Georgia', fontWeight: '700', letterSpacing: -0.4 },
  heroBody: { marginTop: 13, fontSize: 16, lineHeight: 23 },
  heroCta: { marginTop: 20, borderRadius: 13, backgroundColor: colors.brand.orange500, paddingHorizontal: 21, paddingVertical: 14 },
  heroCtaLabel: { fontSize: 16, fontWeight: '700' },
  dots: { position: 'absolute', left: 40, bottom: 20, flexDirection: 'row', alignItems: 'center', gap: 12 },
  dot: { width: 12, height: 12, borderRadius: 6, backgroundColor: colors.surface.white },
  dotActive: { width: 20, backgroundColor: colors.brand.orange500 },
  categoryRow: { paddingRight: 4 },
  categoryItem: { alignItems: 'center', gap: 10 },
  categoryLabel: { fontSize: 15, lineHeight: 18, width: 116 },
  moreCircle: { alignItems: 'center', justifyContent: 'center', backgroundColor: colors.surface.white, borderWidth: 1, borderColor: '#ECE7E0' },
  sectionHeader: { flexDirection: 'row', alignItems: 'baseline', justifyContent: 'space-between', marginTop: 2 },
  sectionTitle: { fontFamily: 'Georgia', fontSize: 27, lineHeight: 32 },
  seeAll: { fontSize: 16, fontWeight: '600' },
  productRail: { gap: 16, paddingRight: 8 },
  offerBanner: { minHeight: 96, flexDirection: 'row', alignItems: 'center', gap: 14, borderRadius: 18, backgroundColor: '#FFF0DE', padding: 15 },
  offerIcon: { width: 52, height: 52, borderRadius: 26, backgroundColor: '#FFD8AB', alignItems: 'center', justifyContent: 'center' },
  offerCopy: { flex: 1, gap: 3 },
  offerTitle: { fontFamily: 'Georgia', fontSize: 21 },
  offerButton: { borderWidth: 1.5, borderColor: colors.brand.orange500, borderRadius: 11, paddingHorizontal: 17, paddingVertical: 12 },
  offerButtonLabel: { fontSize: 15, fontWeight: '700' },
  inspirationRail: { gap: 16, paddingRight: 8 },
  inspirationCard: { overflow: 'hidden', borderRadius: 18, backgroundColor: colors.brand.navy900 },
  inspirationImage: { width: '100%', height: '100%' },
  rtlText: { writingDirection: 'rtl', textAlign: 'right' },
});
