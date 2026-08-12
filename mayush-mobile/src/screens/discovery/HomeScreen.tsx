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
import { MockUser } from '../../commerce/authState';
import { BuyerOrder } from '../../commerce/orderState';
import {
  getHomeCatalogProductById,
  getAuthenticatedHomeFirstName,
  getHomeRecommendationIds,
  HOME_BEST_SELLER_IDS,
  HOME_NEW_ARRIVAL_IDS,
  HOME_RECENT_FALLBACK_IDS,
  resolveHomeProducts,
} from './homeCatalog';

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
const PERSONALIZED_ARTWORK: Record<number, ImageSourcePropType> = {
  101: NEW_ARTWORK[0], 102: NEW_ARTWORK[1], 103: NEW_ARTWORK[2], 104: NEW_ARTWORK[3],
  201: BEST_ARTWORK[0], 202: BEST_ARTWORK[1], 203: BEST_ARTWORK[2], 204: BEST_ARTWORK[3],
  205: BEST_ARTWORK[2], 206: CATEGORY_ARTWORK[1], 207: NEW_ARTWORK[3], 208: BEST_ARTWORK[3],
};

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

const CATEGORIES: ShowcaseCategory[] = [
  { category: category(1, 'Salon'), art: CATEGORY_ARTWORK[0] },
  { category: category(2, '\u0053alle \u00e0 manger'), art: CATEGORY_ARTWORK[1] },
  { category: category(3, 'Chambre'), art: CATEGORY_ARTWORK[2] },
  { category: category(4, '\u00c9clairage'), art: CATEGORY_ARTWORK[3] },
  { category: category(5, 'D\u00e9coration'), art: CATEGORY_ARTWORK[4] },
];

const NEW_ARRIVALS: ShowcaseProduct[] = [
  ...HOME_NEW_ARRIVAL_IDS.map((id, index) => ({ product: getHomeCatalogProductById(id)!, art: NEW_ARTWORK[index] })),
];

const BEST_SELLERS: ShowcaseProduct[] = [
  ...HOME_BEST_SELLER_IDS.map((id, index) => ({ product: getHomeCatalogProductById(id)!, art: BEST_ARTWORK[index] })),
];

export interface HomeScreenProps {
  onSelectCategory?: (category: CategoryDto) => void;
  onSelectProduct?: (product: ProductMiniDto) => void;
  onNavigateTab?: (tab: TabKey) => void;
  activeTab?: TabKey;
  isAuthenticated?: boolean;
  authenticatedUser?: MockUser | null;
  orders?: BuyerOrder[];
  cartProductIds?: number[];
  wishlistedProductIds?: number[];
  onToggleWishlist?: (product: ProductMiniDto) => void;
  onOpenWishlist?: () => void;
  onOpenOrder?: (orderId: string) => void;
  onOpenPromotions?: () => void;
  onOpenRecentlyViewed?: () => void;
  onOpenBestSellers?: () => void;
  onOpenNewArrivals?: () => void;
  onOpenInspiration?: () => void;
  cartBadgeCount?: number;
}

export const HomeScreen: React.FC<HomeScreenProps> = ({ onSelectCategory, onSelectProduct, onNavigateTab, activeTab = 'home', isAuthenticated = false, authenticatedUser = null, orders = [], cartProductIds = [], wishlistedProductIds = [], onToggleWishlist, onOpenWishlist, onOpenOrder, onOpenPromotions, onOpenRecentlyViewed, onOpenBestSellers, onOpenNewArrivals, onOpenInspiration, cartBadgeCount = 2 }) => {
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

  if (isAuthenticated) {
    return (
      <PersonalizedHome
        user={authenticatedUser}
        orders={orders}
        contentPadding={contentPadding}
        contentWidth={contentWidth}
        productWidth={productWidth}
        categorySize={categorySize}
        isRTL={isRTL}
        language={language}
        activeTab={activeTab}
        cartProductIds={cartProductIds}
        wishlistedProductIds={wishlistedProductIds}
        cartBadgeCount={cartBadgeCount}
        onSelectCategory={onSelectCategory}
        onSelectProduct={onSelectProduct}
        onNavigateTab={onNavigateTab}
        onToggleWishlist={onToggleWishlist}
        onOpenWishlist={onOpenWishlist}
        onOpenOrder={onOpenOrder}
        onOpenRecentlyViewed={onOpenRecentlyViewed}
      />
    );
  }

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

        <SectionHeader label={heading('Nouveautés', 'وصول جديد')} action={heading('Voir tout', 'عرض الكل')} isRTL={isRTL} onPress={onOpenNewArrivals ?? onOpenPromotions ?? (() => onNavigateTab?.('categories'))} />
        <ProductRail products={NEW_ARRIVALS} cardWidth={productWidth} onSelect={onSelectProduct} wishlistedProductIds={wishlistedProductIds} onToggleWishlist={onToggleWishlist} badgeText={heading('Nouveau', 'جديد')} />

        <SectionHeader label={heading('Meilleures ventes', 'الأكثر مبيعاً')} action={heading('Voir tout', 'عرض الكل')} isRTL={isRTL} onPress={onOpenBestSellers ?? (() => onNavigateTab?.('categories'))} />
        <ProductRail products={BEST_SELLERS} cardWidth={productWidth} onSelect={onSelectProduct} showRating wishlistedProductIds={wishlistedProductIds} onToggleWishlist={onToggleWishlist} badgeText={heading('Meilleure vente', 'الأكثر مبيعاً')} />

        <View style={styles.offerBanner}>
          <View style={styles.offerIcon}><MayushIcon name="tag" size={23} color={colors.brand.orange500} /></View>
          <View style={styles.offerCopy}><MayushText variant="sectionTitle" color={colors.brand.navy900} style={styles.offerTitle}>{heading('Offres du moment', 'عروض اللحظة')}</MayushText><MayushText variant="smallBody" color={colors.neutral.gray700}>{heading('Jusqu’à -20% sur une sélection de pièces d’exception.', 'حتى 20% على منتجات مختارة.')}</MayushText></View>
          <TouchableOpacity accessibilityRole="button" accessibilityLabel={heading('Profiter des offres', 'استفد من العروض')} onPress={onOpenPromotions} style={styles.offerButton}><MayushText variant="smallBody" color={colors.brand.orange500} style={styles.offerButtonLabel}>{heading('En profiter', 'استفد')}</MayushText></TouchableOpacity>
        </View>

        <SectionHeader label={heading('Inspiration du moment', 'إلهام اليوم')} action={heading('Voir tout', 'عرض الكل')} isRTL={isRTL} onPress={onOpenInspiration ?? onOpenWishlist ?? (() => onNavigateTab?.('categories'))} />
        <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.inspirationRail}>
          <InspirationCard source={INSPIRATION_ARTWORK[0]} width={Math.round(contentWidth * 0.488)} onPress={onOpenInspiration ?? onOpenWishlist ?? (() => onNavigateTab?.('categories'))} />
          <InspirationCard source={INSPIRATION_ARTWORK[1]} width={Math.round(contentWidth * 0.488)} onPress={onOpenInspiration ?? onOpenWishlist ?? (() => onNavigateTab?.('categories'))} />
        </ScrollView>
      </ScrollView>
      <BottomTabBar activeTab={activeTab} onTabPress={onNavigateTab ?? (() => undefined)} cartBadgeCount={cartBadgeCount} />
    </View>
  );
};

interface PersonalizedHomeProps {
  user: MockUser | null;
  orders: BuyerOrder[];
  contentPadding: number;
  contentWidth: number;
  productWidth: number;
  categorySize: number;
  isRTL: boolean;
  language: 'fr' | 'ar';
  activeTab: TabKey;
  cartProductIds: number[];
  wishlistedProductIds: number[];
  cartBadgeCount: number;
  onSelectCategory?: (category: CategoryDto) => void;
  onSelectProduct?: (product: ProductMiniDto) => void;
  onNavigateTab?: (tab: TabKey) => void;
  onToggleWishlist?: (product: ProductMiniDto) => void;
  onOpenWishlist?: () => void;
  onOpenOrder?: (orderId: string) => void;
  onOpenRecentlyViewed?: () => void;
}

const PersonalizedHome: React.FC<PersonalizedHomeProps> = ({
  user,
  orders,
  contentPadding,
  contentWidth,
  productWidth,
  categorySize,
  isRTL,
  language,
  activeTab,
  cartProductIds,
  wishlistedProductIds,
  cartBadgeCount,
  onSelectCategory,
  onSelectProduct,
  onNavigateTab,
  onToggleWishlist,
  onOpenWishlist,
  onOpenOrder,
  onOpenRecentlyViewed,
}) => {
  const fr = language !== 'ar';
  const copy = (frCopy: string, arCopy: string) => fr ? frCopy : arCopy;
  const firstName = getAuthenticatedHomeFirstName(user) || copy('Bonjour', 'مرحباً');
  const recommendationIds = getHomeRecommendationIds({
    wishlistProductIds: wishlistedProductIds,
    cartProductIds,
  });
  const recommendations = resolveHomeProducts(recommendationIds);
  const recentlyViewed = resolveHomeProducts(HOME_RECENT_FALLBACK_IDS);
  const currentOrder = orders.find((order) => !['delivered', 'cancelled', 'returned', 'refunded'].includes(order.orderStatus));
  const categoryEntries = [
    { item: category(1, copy('Salon', 'صالون')), art: CATEGORY_ARTWORK[0] },
    { item: category(2, copy('Salle à manger', 'غرفة الطعام')), art: CATEGORY_ARTWORK[1] },
    { item: category(3, copy('Chambre', 'غرفة النوم')), art: CATEGORY_ARTWORK[2] },
    { item: category(6, copy('Bureau', 'مكتب')), art: CATEGORY_ARTWORK[0] },
    { item: category(4, copy('Éclairage', 'إضاءة')), art: CATEGORY_ARTWORK[3] },
    { item: category(5, copy('Décoration', 'ديكور')), art: CATEGORY_ARTWORK[4] },
    { item: category(7, copy('Rangement', 'تخزين')), art: CATEGORY_ARTWORK[1] },
  ];
  const estimatedDate = currentOrder?.estimatedDeliveryAt
    ? new Intl.DateTimeFormat(fr ? 'fr-FR' : 'ar-MA', { day: 'numeric', month: 'long', year: 'numeric' }).format(new Date(currentOrder.estimatedDeliveryAt))
    : null;

  return (
    <View style={styles.container} accessibilityLabel={copy('Accueil personnalisé', 'الرئيسية المخصصة')}>
      <ScrollView showsVerticalScrollIndicator={false} contentContainerStyle={[styles.personalizedContent, { paddingHorizontal: contentPadding }]}>
        <View style={[styles.personalizedHeader, isRTL && styles.rowReverse]}>
          <MayushLogo width={118} height={37} />
          <View style={[styles.headerActions, isRTL && styles.rowReverse]}>
            <TouchableOpacity accessibilityRole="button" accessibilityLabel={copy('Notifications', 'الإشعارات')} style={styles.headerButton}>
              <MayushIcon name="bell" size={24} color={colors.brand.navy900} />
              <View style={styles.notificationBadge}><MayushText variant="caption" color={colors.surface.white} style={styles.badgeText}>3</MayushText></View>
            </TouchableOpacity>
            <TouchableOpacity accessibilityRole="button" accessibilityLabel={copy('Panier', 'السلة')} onPress={() => onNavigateTab?.('cart')} style={styles.headerButton}>
              <MayushIcon name="shopping-cart" size={26} color={colors.brand.navy900} />
              {cartBadgeCount > 0 ? <View style={styles.cartBadge}><MayushText variant="caption" color={colors.surface.white} style={styles.badgeText}>{cartBadgeCount}</MayushText></View> : null}
            </TouchableOpacity>
          </View>
        </View>

        <View style={[styles.greetingRow, isRTL && styles.rowReverse]}>
          <View style={styles.avatar}><MayushText variant="sectionTitle" color={colors.surface.white}>{firstName.charAt(0).toUpperCase()}</MayushText></View>
          <View style={styles.greetingCopy}>
            <MayushText variant="sectionTitle" color={colors.brand.navy900} style={isRTL && styles.rtlText}>{copy(`Bonjour ${firstName} 👋`, `مرحباً ${firstName} 👋`)}</MayushText>
            <MayushText variant="smallBody" color={colors.neutral.gray700} style={isRTL && styles.rtlText}>{copy('Ravi de vous revoir ! Découvrez\nnos nouveautés sélectionnées pour vous.', 'سعداء بعودتك! اكتشف\nمختاراتنا الجديدة لك.')}</MayushText>
          </View>
        </View>

        <View style={[styles.personalizedHero, { height: Math.round(contentWidth * 0.55) }]}>
          <Image source={PREMIUM_HERO_SCENE} resizeMode="cover" style={styles.personalizedHeroImage} />
          <View style={[styles.personalizedHeroCopy, isRTL && styles.personalizedHeroCopyRtl]}>
            <MayushText variant="caption" color={colors.brand.orange500} style={[styles.premiumEyebrow, isRTL && styles.rtlText]}>{copy('♛ COLLECTION PREMIUM', '♛ مجموعة فاخرة')}</MayushText>
            <MayushText variant="display" color={colors.surface.white} style={[styles.personalizedHeroTitle, isRTL && styles.rtlText]}>{copy('Design. Qualité.\nExcellence.', 'تصميم. جودة.\nتميّز.')}</MayushText>
            <MayushText variant="smallBody" color={colors.surface.white} style={[styles.personalizedHeroBody, isRTL && styles.rtlText]}>{copy('Des pièces uniques pour sublimer\nvos espaces.', 'قطع مميزة ترتقي\nبمساحاتك.')}</MayushText>
            <TouchableOpacity accessibilityRole="button" onPress={() => onNavigateTab?.('categories')} style={styles.personalizedHeroCta}>
              <MayushText variant="button" color={colors.surface.white}>{copy('Découvrir maintenant', 'اكتشف الآن')}</MayushText>
            </TouchableOpacity>
          </View>
          <View style={[styles.personalizedDots, isRTL && styles.rowReverse]}><View style={[styles.dot, styles.dotActive]} /><View style={styles.dot} /><View style={styles.dot} /></View>
        </View>

        {currentOrder ? (
          <TouchableOpacity accessibilityRole="button" accessibilityLabel={copy('Voir le suivi de la commande', 'عرض تتبع الطلب')} onPress={() => onOpenOrder?.(currentOrder.orderId)} activeOpacity={0.82} style={[styles.orderProgressCard, isRTL && styles.rowReverse]}>
            <View style={styles.orderIcon}><MayushIcon name="package-variant-closed" size={23} color={colors.brand.orange500} /></View>
            <View style={styles.orderProgressCopy}>
              <MayushText variant="strongBody" color={colors.brand.navy900} style={isRTL && styles.rtlText}>{copy('Commande en cours', 'طلب قيد التنفيذ')}</MayushText>
              <MayushText variant="caption" color={colors.neutral.gray700} style={isRTL && styles.rtlText}>{currentOrder.orderId}</MayushText>
              {estimatedDate ? <MayushText variant="caption" color={colors.neutral.gray700} style={isRTL && styles.rtlText}>{copy(`Livraison estimée : ${estimatedDate}`, `التوصيل المتوقع: ${estimatedDate}`)}</MayushText> : null}
            </View>
            <View style={[styles.orderTrackingAction, isRTL && styles.rowReverse]}>
              <MayushText variant="caption" color={colors.brand.orange500} style={styles.orderTrackingLabel}>{copy('Voir le suivi', 'عرض التتبع')}</MayushText>
              <MayushIcon name={isRTL ? 'chevron-left' : 'chevron-right'} size={16} color={colors.brand.orange500} />
            </View>
          </TouchableOpacity>
        ) : null}

        <ActionSectionHeader label={copy('Recommandé pour vous', 'موصى به لك')} action={copy('Voir tout', 'عرض الكل')} isRTL={isRTL} onPress={() => onNavigateTab?.('categories')} />
        <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.productRail}>
          {recommendations.map((item) => (
            <ProductCard
              key={item.id}
              name={item.name}
              thumbnailUrl=""
              thumbnailSource={PERSONALIZED_ARTWORK[item.id]}
              currentPriceFormatted={item.main_price}
              width={productWidth}
              isFavorite={wishlistedProductIds.includes(item.id)}
              onFavoritePress={() => onToggleWishlist?.(item)}
              onPress={() => onSelectProduct?.(item)}
            />
          ))}
        </ScrollView>

        <ActionSectionHeader label={copy('Récemment consultés', 'شوهدت مؤخراً')} action={copy('Voir tout', 'عرض الكل')} isRTL={isRTL} onPress={onOpenRecentlyViewed} />
        <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.recentRail}>
          {recentlyViewed.map((item) => (
            <TouchableOpacity key={item.id} accessibilityRole="button" accessibilityLabel={item.name} onPress={() => onSelectProduct?.(item)} style={styles.recentCard}>
              <Image source={PERSONALIZED_ARTWORK[item.id]} resizeMode="cover" style={styles.recentImage} />
              <View style={styles.recentEye}><MayushIcon name="eye" size={16} color={colors.surface.white} /></View>
            </TouchableOpacity>
          ))}
        </ScrollView>

        <ActionSectionHeader label={copy('Inspiration wishlist', 'إلهام المفضلة')} action={copy('Voir tout', 'عرض الكل')} isRTL={isRTL} onPress={onOpenWishlist} />
        <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.inspirationRail}>
          {[
            [INSPIRATION_ARTWORK[0], copy('Salon Moderne', 'صالون عصري'), 12],
            [INSPIRATION_ARTWORK[1], copy('Chambre Douce', 'غرفة هادئة'), 8],
            [CATEGORY_HERO_SCENE, copy('Bureau Inspirant', 'مكتب ملهم'), 6],
          ].map(([source, label, count]) => (
            <TouchableOpacity key={String(label)} onPress={onOpenWishlist} style={[styles.wishlistInspirationCard, { width: Math.round(contentWidth * 0.52) }]}>
              <Image source={source as ImageSourcePropType} resizeMode="cover" style={styles.inspirationImage} />
              <View style={styles.inspirationShade} />
              <MayushText variant="strongBody" color={colors.surface.white} style={[styles.inspirationLabel, isRTL && styles.rtlText]}>{String(label)}</MayushText>
              <View style={[styles.inspirationCount, isRTL && styles.rowReverse]}><MayushIcon name="heart" size={15} color={colors.surface.white} /><MayushText variant="caption" color={colors.surface.white}>{String(count)}</MayushText></View>
            </TouchableOpacity>
          ))}
        </ScrollView>

        <ActionSectionHeader label={copy('Catégories', 'الفئات')} action={copy('Voir tout', 'عرض الكل')} isRTL={isRTL} onPress={() => onNavigateTab?.('categories')} />
        <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.categoryRow}>
          {categoryEntries.map(({ item, art }) => (
            <TouchableOpacity key={item.id} accessibilityRole="button" accessibilityLabel={item.name} onPress={() => onSelectCategory?.(item)} style={[styles.categoryItem, { width: categorySize }]}>
              <Image source={art} style={{ width: categorySize, height: categorySize, borderRadius: categorySize / 2 }} resizeMode="cover" />
              <MayushText variant="caption" color={colors.brand.navy900} align="center" numberOfLines={2} style={styles.categoryLabel}>{item.name}</MayushText>
            </TouchableOpacity>
          ))}
        </ScrollView>
      </ScrollView>
      <BottomTabBar activeTab={activeTab} onTabPress={onNavigateTab ?? (() => undefined)} cartBadgeCount={cartBadgeCount} />
    </View>
  );
};

const ActionSectionHeader: React.FC<{ label: string; action: string; isRTL: boolean; onPress?: () => void }> = ({ label, action, isRTL, onPress }) => (
  <View style={[styles.sectionHeader, isRTL && styles.rowReverse]}>
    <MayushText variant="sectionTitle" color={colors.brand.navy900} style={[styles.sectionTitle, isRTL && styles.rtlText]}>{label}</MayushText>
    <TouchableOpacity accessibilityRole="button" accessibilityLabel={action} onPress={onPress}>
      <MayushText variant="smallBody" color={colors.brand.orange500} style={styles.seeAll}>{action}</MayushText>
    </TouchableOpacity>
  </View>
);

const SectionHeader: React.FC<{ label: string; action: string; isRTL?: boolean; onPress?: () => void }> = ({ label, action, isRTL, onPress }) => (
  <View style={[styles.sectionHeader, isRTL && styles.rowReverse]}>
    <MayushText variant="sectionTitle" color={colors.brand.navy900} style={[styles.sectionTitle, isRTL && styles.rtlText]}>{label}</MayushText>
    <TouchableOpacity accessibilityRole="button" accessibilityLabel={action} onPress={onPress}>
      <MayushText variant="smallBody" color={colors.brand.orange500} style={styles.seeAll}>{action}</MayushText>
    </TouchableOpacity>
  </View>
);

const ProductRail: React.FC<{
  products: ShowcaseProduct[];
  cardWidth: number;
  onSelect?: (product: ProductMiniDto) => void;
  showRating?: boolean;
  wishlistedProductIds?: number[];
  onToggleWishlist?: (product: ProductMiniDto) => void;
  badgeText?: string;
}> = ({ products, cardWidth, onSelect, showRating, wishlistedProductIds = [], onToggleWishlist, badgeText }) => (
  <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.productRail}>
    {products.map(({ product: item, art }) => (
      <ProductCard
        key={item.id}
        name={item.name}
        thumbnailUrl=""
        thumbnailSource={art}
        currentPriceFormatted={item.main_price}
        hasDiscount={false}
        badgeText={badgeText}
        rating={showRating ? item.rating : undefined}
        salesCount={showRating ? item.sales : undefined}
        width={cardWidth}
        isFavorite={wishlistedProductIds.includes(item.id)}
        onFavoritePress={() => onToggleWishlist?.(item)}
        onPress={() => onSelect?.(item)}
      />
    ))}
  </ScrollView>
);

const InspirationCard: React.FC<{ source: ImageSourcePropType; width: number; onPress?: () => void }> = ({ source, width, onPress }) => (
  <TouchableOpacity activeOpacity={0.82} onPress={onPress} style={[styles.inspirationCard, { width, height: Math.round(width * (143 / 432)) }]}>
    <Image source={source} style={styles.inspirationImage} resizeMode="cover" />
  </TouchableOpacity>
);

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#FFFCF8' },
  content: { paddingTop: 18, paddingBottom: 28, gap: 20 },
  personalizedContent: { paddingTop: 10, paddingBottom: 28, gap: 16 },
  personalizedHeader: { height: 50, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' },
  headerActions: { flexDirection: 'row', alignItems: 'center', gap: 4 },
  notificationBadge: { position: 'absolute', right: 1, top: 1, minWidth: 18, height: 18, borderRadius: 9, backgroundColor: colors.brand.orange500, alignItems: 'center', justifyContent: 'center' },
  greetingRow: { flexDirection: 'row', alignItems: 'center', gap: 12, paddingVertical: 2 },
  avatar: { width: 50, height: 50, borderRadius: 25, alignItems: 'center', justifyContent: 'center', backgroundColor: colors.brand.orange500 },
  greetingCopy: { flex: 1, gap: 2 },
  personalizedHero: { width: '100%', overflow: 'hidden', borderRadius: 18, backgroundColor: colors.brand.navy900 },
  personalizedHeroImage: { position: 'absolute', right: 0, width: '68%', height: '100%', opacity: 0.76 },
  personalizedHeroCopy: { flex: 1, width: '64%', paddingLeft: 22, justifyContent: 'center', alignItems: 'flex-start', zIndex: 1 },
  personalizedHeroCopyRtl: { alignItems: 'flex-end', alignSelf: 'flex-end', paddingLeft: 0, paddingRight: 22 },
  premiumEyebrow: { fontSize: 10, fontWeight: '800', letterSpacing: 0.8 },
  personalizedHeroTitle: { marginTop: 6, fontSize: 25, lineHeight: 28, fontWeight: '700' },
  personalizedHeroBody: { marginTop: 7, fontSize: 12, lineHeight: 17 },
  personalizedHeroCta: { marginTop: 11, borderRadius: 9, backgroundColor: colors.brand.orange500, paddingHorizontal: 13, paddingVertical: 9 },
  personalizedDots: { position: 'absolute', left: 22, bottom: 9, flexDirection: 'row', alignItems: 'center', gap: 7 },
  orderProgressCard: { minHeight: 88, flexDirection: 'row', alignItems: 'center', gap: 10, padding: 12, borderWidth: 1, borderColor: colors.surface.borderWarm, borderRadius: 14, backgroundColor: colors.surface.white },
  orderIcon: { width: 42, height: 42, borderRadius: 12, alignItems: 'center', justifyContent: 'center', backgroundColor: colors.brand.orange100 },
  orderProgressCopy: { flex: 1, gap: 1 },
  orderTrackingAction: { flexDirection: 'row', alignItems: 'center', gap: 2 },
  orderTrackingLabel: { fontWeight: '700' },
  recentRail: { gap: 12, paddingRight: 8 },
  recentCard: { width: 112, height: 82, borderRadius: 12, overflow: 'hidden', backgroundColor: colors.surface.cream },
  recentImage: { width: '100%', height: '100%' },
  recentEye: { position: 'absolute', right: 7, bottom: 7, width: 28, height: 28, borderRadius: 14, alignItems: 'center', justifyContent: 'center', backgroundColor: 'rgba(16,29,53,0.72)' },
  wishlistInspirationCard: { height: 112, overflow: 'hidden', borderRadius: 14, backgroundColor: colors.brand.navy900 },
  inspirationShade: { position: 'absolute', left: 0, right: 0, top: 0, bottom: 0, backgroundColor: 'rgba(16,29,53,0.26)' },
  inspirationLabel: { position: 'absolute', left: 12, bottom: 10, right: 48 },
  inspirationCount: { position: 'absolute', right: 10, top: 10, flexDirection: 'row', alignItems: 'center', gap: 4, borderRadius: 12, paddingHorizontal: 7, paddingVertical: 4, backgroundColor: 'rgba(16,29,53,0.62)' },
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
  heroTitle: { fontWeight: '700', letterSpacing: -0.4 },
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
  sectionTitle: { fontSize: 27, lineHeight: 32 },
  seeAll: { fontSize: 16, fontWeight: '600' },
  productRail: { gap: 16, paddingRight: 8 },
  offerBanner: { minHeight: 96, flexDirection: 'row', alignItems: 'center', gap: 14, borderRadius: 18, backgroundColor: '#FFF0DE', padding: 15 },
  offerIcon: { width: 52, height: 52, borderRadius: 26, backgroundColor: '#FFD8AB', alignItems: 'center', justifyContent: 'center' },
  offerCopy: { flex: 1, gap: 3 },
  offerTitle: { fontSize: 21 },
  offerButton: { borderWidth: 1.5, borderColor: colors.brand.orange500, borderRadius: 11, paddingHorizontal: 17, paddingVertical: 12 },
  offerButtonLabel: { fontSize: 15, fontWeight: '700' },
  inspirationRail: { gap: 16, paddingRight: 8 },
  inspirationCard: { overflow: 'hidden', borderRadius: 18, backgroundColor: colors.brand.navy900 },
  inspirationImage: { width: '100%', height: '100%' },
  rtlText: { writingDirection: 'rtl', textAlign: 'right' },
});
