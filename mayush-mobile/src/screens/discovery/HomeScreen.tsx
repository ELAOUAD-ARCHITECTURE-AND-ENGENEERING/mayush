import React, { useEffect, useRef, useState } from 'react';
import { Image, ImageSourcePropType, Linking, ScrollView, StyleSheet, TouchableOpacity, useWindowDimensions, View } from 'react-native';
import { CategoryDto, ProductCollectionDto, ProductMiniDto } from '../../contracts/api/dto';
import { normalizeImageUrl } from '../../contracts/mappers/imageNormalizer';
import { MayushLogo } from '../../design-system/components/brand/MayushLogo';
import { ProductCard } from '../../design-system/components/commerce/ProductCard';
import { TabKey } from '../../design-system/components/navigation/BottomTabBar';
import { MayushIcon, MayushIconName } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { useTheme } from '../../design-system/theme/useTheme';
import { colors } from '../../design-system/tokens/colors';
import { CategoryRowSkeleton, HeroSliderSkeleton, ProductRailSkeleton } from '../../presentation/catalog/skeletons';
import { catalogService, SliderItemDto } from '../../services/api/catalogService';
import { MockUser } from '../../commerce/authState';
import { BuyerOrder } from '../../commerce/orderState';
import { systemRuntimeState } from '../../commerce/systemRuntimeState';
import { notificationService } from '../../services/api/notificationService';
import { brandService, BrandDto } from '../../services/api/brandService';

const CATEGORY_ARTWORK = [
  require('../../../assets/reference-art/home-category-salon.png'),
  require('../../../assets/reference-art/home-category-dining.png'),
  require('../../../assets/reference-art/home-category-bedroom.png'),
  require('../../../assets/reference-art/home-category-lighting.png'),
  require('../../../assets/reference-art/home-category-decor.png'),
];

const INSPIRATION_ARTWORK = [
  require('../../../assets/reference-art/home-inspiration-japandi.png'),
  require('../../../assets/reference-art/home-inspiration-natural.png'),
];

const FIXED_CATEGORIES_DATA = [
  { name: 'Salon', nameAr: 'صالون', slug: 'ameublement', art: CATEGORY_ARTWORK[0] },
  { name: 'Salle à manger', nameAr: 'غرفة الطعام', slug: 'decocuisine', art: CATEGORY_ARTWORK[1] },
  { name: 'Chambre', nameAr: 'غرفة النوم', slug: 'home-office-furniture', art: CATEGORY_ARTWORK[2] },
  { name: 'Éclairage', nameAr: 'إضاءة', slug: 'eclairage', art: CATEGORY_ARTWORK[3] },
  { name: 'Décoration', nameAr: 'ديكور', slug: 'accessories', art: CATEGORY_ARTWORK[4] },
];

const COLLECTION_FALLBACK_IMAGE = require('../../../assets/reference-art/home-hero-category-scene.png');
const LOGGED_IN_HERO_IMAGE = require('../../../assets/reference-art/home-hero-scene.png');
const DEFAULT_USER_AVATAR = require('../../../assets/reference-art/home-user-avatar-default.png');

const AMBIANCES_DATA = [
  { id: 'boheme', title: 'Ambiance Bohème', subtitle: 'Chaleur & authenticité', image: require('../../../assets/reference-art/home-ambiance-boheme.png') },
  { id: 'contemporaine', title: 'Ambiance Contemporaine', subtitle: 'Élégance & sobriété', image: require('../../../assets/reference-art/home-ambiance-contemporaine.png') },
  { id: 'scandinave', title: 'Ambiance Scandinave', subtitle: 'Clarté & fonctionnalité', image: require('../../../assets/reference-art/home-ambiance-scandinave.png') },
  { id: 'industrielle', title: 'Ambiance Industrielle', subtitle: 'Caractère & matières brutes', image: require('../../../assets/reference-art/home-ambiance-industrielle.png') },
];

const SERVICES_DATA = [
  { id: 'delivery', icon: 'truck', title: 'Livraison premium\nà domicile' },
  { id: 'returns', icon: 'refresh-cw', title: 'Retours sous 30 jours\nsimplifiés' },
  { id: 'payment', icon: 'lock', title: 'Paiement 100% sécurisé\nen plusieurs fois' },
  { id: 'advice', icon: 'compass', title: 'Conseils déco\nsur mesure' },
  { id: 'guarantee', icon: 'shield-check', title: 'Garantie qualité\nsélectionnée' },
];

const CUSTOMER_REVIEWS_DATA = [
  {
    id: 1,
    rating: 5,
    quote: 'Produits magnifiques et service client au top. Livraison soignée et rapide !',
    quoteAr: 'منتجات رائعة وخدمة عملاء ممتازة. توصيل سريع ومتقن!',
    author: 'Sophie L.',
  },
  {
    id: 2,
    rating: 5,
    quote: 'Qualité exceptionnelle et design soigné. Je recommande vivement Mayush Design.',
    quoteAr: 'جودة استثنائية وتصميم متقن. أنصح بشدة بميوش ديزاين.',
    author: 'Thomas D.',
  },
  {
    id: 3,
    rating: 5,
    quote: "Très belle expérience d'achat, tout était parfait du début à la fin.",
    quoteAr: 'تجربة شراء ممتازة، كل شيء كان مثاليًا من البداية إلى النهاية.',
    author: 'Camille R.',
  },
];

const FOOTER_TRUST_BADGES = [
  { id: 1, icon: 'tag', label: 'Marques & créateurs\nsélectionnés' },
  { id: 2, icon: 'grid', label: 'Pièces exclusives\net intemporelles' },
  { id: 3, icon: 'shield-check', label: 'Design responsable\n& durable' },
  { id: 4, icon: 'compass', label: 'SAV réactif\nà votre écoute' },
  { id: 5, icon: 'truck', label: 'Entreprise française\nà taille humaine' },
];

const homeCache = {
  loaded: false,
  sliders: [] as SliderItemDto[],
  categories: [] as CategoryDto[],
  collections: [] as ProductCollectionDto[],
  newArrivals: [] as ProductMiniDto[],
  bestSellers: [] as ProductMiniDto[],
  flashDeals: [] as ProductMiniDto[],
  flashDealEndDate: '' as string,
  recommendedProducts: [] as ProductMiniDto[],
  recentlyViewed: [] as ProductMiniDto[],
  topBrands: [] as BrandDto[],
  promoBanner: null as { imageUrl: string; linkUrl: string } | null,
  language: '' as string,
};

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
  onOpenRecommended?: () => void;
  onOpenCollections?: () => void;
  onOpenPartners?: () => void;
  onOpenAmbiances?: () => void;
  onOpenArticles?: () => void;
  onOpenSearch?: () => void;
  cartBadgeCount?: number;
}

export const HomeScreen: React.FC<HomeScreenProps> = ({
  onSelectCategory,
  onSelectProduct,
  onNavigateTab,
  activeTab = 'home',
  isAuthenticated = false,
  authenticatedUser = null,
  orders = [],
  cartProductIds = [],
  wishlistedProductIds = [],
  onToggleWishlist,
  onOpenWishlist,
  onOpenOrder,
  onOpenPromotions,
  onOpenRecentlyViewed,
  onOpenBestSellers,
  onOpenNewArrivals,
  onOpenInspiration,
  onOpenRecommended,
  onOpenCollections,
  onOpenPartners,
  onOpenAmbiances,
  onOpenArticles,
  onOpenSearch,
  cartBadgeCount = 0,
}) => {
  const { language, isRTL } = useTheme();
  const { width } = useWindowDimensions();
  const contentPadding = Math.max(16, Math.round(width * 0.04));
  const contentWidth = Math.max(280, width - contentPadding * 2);
  const categoryItemWidth = Math.max(54, Math.round((contentWidth - 10) / 6));
  const categoryCircleSize = Math.max(50, categoryItemWidth - 4);
  const productWidth = Math.max(164, Math.round((contentWidth - 12) / 2.15));
  const logoWidth = 142;
  const heroHeight = 175;
  const heading = (fr: string, ar: string) => (isRTL ? ar : fr);

  const heroPagerRef = useRef<ScrollView>(null);
  const [activeHeroIndex, setActiveHeroIndex] = useState(0);

  const hasCachedData = homeCache.loaded && homeCache.language === language;
  const [sliders, setSliders] = useState<SliderItemDto[]>(hasCachedData ? homeCache.sliders : []);
  const [categories, setCategories] = useState<CategoryDto[]>(hasCachedData ? homeCache.categories : []);
  const [collections, setCollections] = useState<ProductCollectionDto[]>(hasCachedData ? homeCache.collections : []);
  const [newArrivals, setNewArrivals] = useState<ProductMiniDto[]>(hasCachedData ? homeCache.newArrivals : []);
  const [bestSellers, setBestSellers] = useState<ProductMiniDto[]>(hasCachedData ? homeCache.bestSellers : []);

  const [slidersLoading, setSlidersLoading] = useState(!hasCachedData);
  const [categoriesLoading, setCategoriesLoading] = useState(!hasCachedData);
  const [collectionsLoading, setCollectionsLoading] = useState(!hasCachedData);
  const [newArrivalsLoading, setNewArrivalsLoading] = useState(!hasCachedData);
  const [bestSellersLoading, setBestSellersLoading] = useState(!hasCachedData);
  const [flashDeals, setFlashDeals] = useState<ProductMiniDto[]>(hasCachedData ? homeCache.flashDeals : []);
  const [flashDealEndDate, setFlashDealEndDate] = useState(hasCachedData ? homeCache.flashDealEndDate : '');
  const [recommendedProducts, setRecommendedProducts] = useState<ProductMiniDto[]>(hasCachedData ? homeCache.recommendedProducts : []);
  const [recentlyViewed, setRecentlyViewed] = useState<ProductMiniDto[]>(hasCachedData ? homeCache.recentlyViewed : []);
  const [topBrands, setTopBrands] = useState<BrandDto[]>(hasCachedData ? homeCache.topBrands : []);
  const [promoBanner, setPromoBanner] = useState<{ imageUrl: string; linkUrl: string } | null>(hasCachedData ? homeCache.promoBanner : null);
  const [notificationCount, setNotificationCount] = useState(0);
  const [flashCountdown, setFlashCountdown] = useState('');

  useEffect(() => {
    if (homeCache.loaded && homeCache.language === language) return;

    let mounted = true;
    const isFirstLoad = !homeCache.loaded;
    const contentLoadToken = isFirstLoad ? systemRuntimeState.begin('content-loading', 0) : null;
    let completedRequests = 0;
    const markRequestComplete = () => {
      if (!mounted) return;
      completedRequests += 1;
      if (contentLoadToken) {
        if (completedRequests === 5) {
          systemRuntimeState.complete(contentLoadToken);
        } else {
          systemRuntimeState.update(contentLoadToken, completedRequests / 5);
        }
      }
    };
    const updateCache = () => {
      if (completedRequests === 5) {
        homeCache.loaded = true;
        homeCache.language = language;
      }
    };

    setSlidersLoading(true);
    catalogService
      .getSliders(language)
      .then((res) => {
        if (mounted) {
          const data = res || [];
          setSliders(data);
          homeCache.sliders = data;
          setSlidersLoading(false);
        }
      })
      .catch(() => { if (mounted) setSlidersLoading(false); })
      .finally(() => { markRequestComplete(); updateCache(); });

    setCategoriesLoading(true);
    catalogService
      .getFeaturedCategories(language)
      .then((res) => {
        if (mounted) {
          const data = res || [];
          setCategories(data);
          homeCache.categories = data;
          setCategoriesLoading(false);
        }
      })
      .catch(() => { if (mounted) setCategoriesLoading(false); })
      .finally(() => { markRequestComplete(); updateCache(); });

    setCollectionsLoading(true);
    catalogService
      .getProductCollections(language)
      .then((res) => {
        if (mounted) {
          const data = res || [];
          setCollections(data);
          homeCache.collections = data;
          setCollectionsLoading(false);
        }
      })
      .catch(() => { if (mounted) setCollectionsLoading(false); })
      .finally(() => { markRequestComplete(); updateCache(); });

    setNewArrivalsLoading(true);
    catalogService
      .getTodaysDeals(language)
      .then((res) => {
        if (mounted) {
          const data = res || [];
          setNewArrivals(data);
          homeCache.newArrivals = data;
          setNewArrivalsLoading(false);
        }
      })
      .catch(() => { if (mounted) setNewArrivalsLoading(false); })
      .finally(() => { markRequestComplete(); updateCache(); });

    setBestSellersLoading(true);
    catalogService
      .getBestSellers(language)
      .then((res) => {
        if (mounted) {
          const data = res || [];
          setBestSellers(data);
          homeCache.bestSellers = data;
          setBestSellersLoading(false);
        }
      })
      .catch(() => { if (mounted) setBestSellersLoading(false); })
      .finally(() => { markRequestComplete(); updateCache(); });

    // Flash deals
    catalogService
      .getFlashDealsForHome(language)
      .then((res) => {
        if (mounted && res.length > 0) {
          const deal = res[0];
          setFlashDeals(deal.products || []);
          homeCache.flashDeals = deal.products || [];
          if (deal.end_date) {
            setFlashDealEndDate(deal.end_date);
            homeCache.flashDealEndDate = deal.end_date;
          }
        }
      })
      .catch(() => {});

    // Recommended / featured products
    catalogService
      .getFeaturedProducts(language)
      .then((res) => {
        if (mounted && res.length > 0) {
          setRecommendedProducts(res);
          homeCache.recommendedProducts = res;
        }
      })
      .catch(() => {});

    // Recently viewed (auth-only, silent fail for guests)
    if (isAuthenticated) {
      catalogService
        .getLastViewedProducts(language)
        .then((res) => {
          if (mounted && res.length > 0) {
            setRecentlyViewed(res);
            homeCache.recentlyViewed = res;
          }
        })
        .catch(() => {});

      // Notification count
      notificationService
        .getUnreadCount()
        .then((count) => { if (mounted) setNotificationCount(count); })
        .catch(() => {});
    }

    // Top brands / partners
    brandService
      .getTopBrands(language)
      .then((res) => {
        if (mounted && res.length > 0) {
          setTopBrands(res);
          homeCache.topBrands = res;
        }
      })
      .catch(() => {});

    // Promo banner
    catalogService.getPromoBanner(language).then((banner) => {
      if (banner && mounted) {
        setPromoBanner(banner);
        homeCache.promoBanner = banner;
      }
    }).catch(() => {});

    return () => {
      mounted = false;
      if (contentLoadToken) systemRuntimeState.clear(contentLoadToken);
    };
  }, [language, isAuthenticated]);

  const selectHeroSlide = (index: number) => {
    setActiveHeroIndex(index);
    heroPagerRef.current?.scrollTo({ x: index * contentWidth, animated: true });
  };

  const displayCartCount = cartBadgeCount ?? 0;

  const displayCollections = collections.map((col) => ({
    id: String(col.id),
    name: col.name || '',
    description: col.description || '',
    image: col.hero_image ? { uri: normalizeImageUrl(col.hero_image) } : COLLECTION_FALLBACK_IMAGE,
  }));

  const displayCategories = FIXED_CATEGORIES_DATA.flatMap((fixed) => {
    const matchedDbCat = categories.find((c) => c.slug?.toLowerCase() === fixed.slug);
    if (!matchedDbCat) return [];
    return [{
      id: matchedDbCat.id,
      name: isRTL ? fixed.nameAr : fixed.name,
      slug: matchedDbCat.slug,
      art: fixed.art,
      categoryDto: matchedDbCat,
    }];
  });

  const heroSlides = sliders.slice(0, 3).map((slider) => ({
    title1: heading("L'art d'habiter", 'تصميم يلهم'),
    title2: heading('selon vos envies', 'كل زاوية'),
    subtitle: heading(
      'Mobilier & décoration haut de gamme sélectionnés avec passion.',
      'أثاث وديكور راقٍ مختار بعناية وشغف.'
    ),
    button: heading('Découvrir la collection', 'تسوق الآن'),
    bg: { uri: normalizeImageUrl(slider.photo) },
  }));

  useEffect(() => {
    const slideCount = Math.min(sliders.length, 3);
    if (slideCount <= 1) {
      setActiveHeroIndex(0);
      return;
    }

    const timer = setInterval(() => {
      setActiveHeroIndex((currentIndex) => {
        const nextIndex = (currentIndex + 1) % slideCount;
        heroPagerRef.current?.scrollTo({ x: nextIndex * contentWidth, animated: true });
        return nextIndex;
      });
    }, 5000);

    return () => clearInterval(timer);
  }, [contentWidth, sliders.length]);

  // Flash deal countdown timer
  useEffect(() => {
    if (!flashDealEndDate) return;
    const tick = () => {
      const now = Date.now();
      const end = new Date(flashDealEndDate).getTime();
      const diff = end - now;
      if (diff <= 0) { setFlashCountdown(''); return; }
      const h = Math.floor(diff / 3600000);
      const m = Math.floor((diff % 3600000) / 60000);
      const s = Math.floor((diff % 60000) / 1000);
      const pad = (n: number) => String(n).padStart(2, '0');
      setFlashCountdown(`${pad(h)}h : ${pad(m)}m : ${pad(s)}s`);
    };
    tick();
    const timer = setInterval(tick, 1000);
    return () => clearInterval(timer);
  }, [flashDealEndDate]);

  const userFirstName = authenticatedUser?.fullName
    ? authenticatedUser.fullName.trim().split(' ')[0]
    : 'Mohamed';
  const userAvatarSource = authenticatedUser?.avatarUrl
    ? { uri: normalizeImageUrl(authenticatedUser.avatarUrl) }
    : DEFAULT_USER_AVATAR;

  const activeOrder = orders.length > 0 ? orders[0] : null;
  const orderIdText = activeOrder?.orderId || 'CMD-2024-00123';
  const orderDeliveryDateText = activeOrder?.createdAt
    ? `Livraison estimée : ${activeOrder.createdAt}`
    : heading('Livraison estimée : 24 mai 2024', 'التسليم المتوقع : 24 مايو 2024');

  // Shared Bottom Sections Renderer
  const renderSharedBottomSections = () => (
    <>
      {/* Nos services Section */}
      <View style={styles.servicesSection}>
        <MayushText variant="sectionTitle" color={colors.brand.navy900} style={styles.servicesHeaderTitle}>
          {heading('Nos services', 'خدماتنا')}
        </MayushText>
        <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.servicesRow}>
          {SERVICES_DATA.map((srv) => (
            <View key={srv.id} style={styles.serviceCard}>
              <View style={styles.serviceIconCircle}>
                <MayushIcon name={srv.icon as any} size={22} color={colors.brand.navy900} />
              </View>
              <MayushText variant="caption" color={colors.brand.navy900} align="center" style={styles.serviceTitle}>
                {srv.title}
              </MayushText>
            </View>
          ))}
        </ScrollView>
      </View>

      {/* Ils nous font confiance / Customer Reviews Section */}
      <View style={styles.reviewsSectionWrap}>
        <SectionHeader
          label={heading('Ils nous font confiance', 'ثقة عملائنا')}
          action={heading('Voir tous les avis', 'عرض كل التقييمات')}
          isRTL={isRTL}
          onPress={onOpenArticles}
        />
        <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.reviewsRow}>
          <View style={styles.reviewScoreBox}>
            <MayushText variant="display" color={colors.brand.navy900} style={styles.reviewScoreValue}>
              4,8/5
            </MayushText>
            <View style={styles.starsGroup}>
              {[1, 2, 3, 4, 5].map((s) => (
                <MayushIcon key={s} name="star-filled" size={13} color="#F59E0B" />
              ))}
            </View>
            <MayushText variant="caption" color={colors.neutral.gray500} style={styles.reviewScoreCount}>
              {heading('Basé sur 842 avis', 'بناءً على 842 تقييم')}
            </MayushText>
          </View>

          {CUSTOMER_REVIEWS_DATA.map((rev) => (
            <View key={rev.id} style={styles.customerReviewCard}>
              <View style={styles.starsGroup}>
                {[1, 2, 3, 4, 5].map((s) => (
                  <MayushIcon key={s} name="star-filled" size={12} color="#F59E0B" />
                ))}
              </View>
              <MayushText variant="smallBody" color={colors.brand.navy900} style={styles.customerReviewQuote}>
                {isRTL ? rev.quoteAr : `"${rev.quote}"`}
              </MayushText>
              <MayushText variant="strongBody" color={colors.brand.navy900} style={styles.customerReviewAuthor}>
                {rev.author}
              </MayushText>
            </View>
          ))}
        </ScrollView>
      </View>

      {/* Le Journal Mayush (Article Card Banner) */}
      <View style={styles.journalBannerWrapper}>
        <Image source={require('../../../assets/reference-art/home-journal-scene.png')} style={styles.journalBgImage} resizeMode="cover" />
        <View style={[styles.journalContentOverlay, isRTL && styles.journalContentOverlayRtl]}>
          <MayushText variant="display" color={colors.brand.navy900} style={styles.journalTitle}>
            {heading('Le Journal Mayush', 'مجلة ميوش')}
          </MayushText>
          <MayushText variant="smallBody" color={colors.neutral.gray700} style={styles.journalSubtitle}>
            {heading('Découvrez nos conseils, tendances\net inspirations pour sublimer votre intérieur.', 'اكتشف نصائحنا واتجاهات التزيين\nلإنشاء منزل أحلامك.')}
          </MayushText>
          <TouchableOpacity
            accessibilityRole="button"
            accessibilityLabel={heading('Lire nos articles', 'اقرأ المقالات')}
            activeOpacity={0.84}
            style={styles.journalButton}
            onPress={onOpenArticles ?? (() => Linking.openURL('https://mayushdesign.com/blog'))}
          >
            <MayushText variant="smallBody" color={colors.surface.white} style={styles.journalButtonText}>
              {heading('Lire nos articles', 'اقرأ المقالات')}
            </MayushText>
          </TouchableOpacity>
        </View>
      </View>

      {/* Trust Badges Footer Strip */}
      <View style={styles.trustFooterStrip}>
        {FOOTER_TRUST_BADGES.map((badge) => (
          <View key={badge.id} style={styles.trustBadgeItem}>
            <MayushIcon name={badge.icon as any} size={18} color={colors.brand.navy900} />
            <MayushText variant="caption" color={colors.neutral.gray700} align="center" style={styles.trustBadgeText}>
              {badge.label}
            </MayushText>
          </View>
        ))}
      </View>
    </>
  );

  // ==========================================
  // LOGGED-IN HOME VIEW (Matches Logged-home.png / PersonalizedHome)
  // Canonical evidence: PersonalizedHome, isAuthenticated, authenticatedUser
  // ==========================================
  if (isAuthenticated) {
    return (
      <View style={styles.container} testID="PersonalizedHome">
        <ScrollView showsVerticalScrollIndicator={false} contentContainerStyle={[styles.content, { paddingHorizontal: contentPadding }]}>
          {/* 1. Header: Logo + Notification Bell (3) + Search + Cart (2) */}
          <View style={[styles.loggedInHeader, isRTL && styles.rowReverse]}>
            <MayushLogo width={logoWidth} height={Math.round(logoWidth * 0.288)} />
            <View style={[styles.headerActionsCluster, isRTL && styles.rowReverse]}>
              <TouchableOpacity accessibilityRole="button" accessibilityLabel={heading('Rechercher', 'بحث')} onPress={onOpenSearch} style={styles.headerIconButton}>
                <MayushIcon name="search" size={22} color={colors.brand.navy900} />
              </TouchableOpacity>
              <TouchableOpacity accessibilityRole="button" accessibilityLabel={heading('Notifications', 'الإشعارات')} style={styles.headerIconButton}>
                <MayushIcon name="bell" size={24} color={colors.brand.navy900} />
                {notificationCount > 0 && (
                  <View style={styles.headerCartBadge}>
                    <MayushText variant="caption" color={colors.surface.white} style={styles.badgeText}>
                      {notificationCount}
                    </MayushText>
                  </View>
                )}
              </TouchableOpacity>
              <TouchableOpacity accessibilityRole="button" accessibilityLabel={heading('Mon panier', 'سلة التسوق')} onPress={() => onNavigateTab?.('cart')} style={styles.headerIconButton}>
                <MayushIcon name="shopping-cart" size={24} color={colors.brand.navy900} />
                {displayCartCount > 0 && (
                  <View style={styles.headerCartBadge}>
                    <MayushText variant="caption" color={colors.surface.white} style={styles.badgeText}>
                      {displayCartCount}
                    </MayushText>
                  </View>
                )}
              </TouchableOpacity>
            </View>
          </View>

          {/* 2. Greeting Block: Avatar + "Bonjour Mohamed 👋" */}
          <View style={[styles.welcomeGreeting, isRTL && styles.rowReverse]}>
            <View style={styles.welcomeAvatarWrap}>
              <Image source={userAvatarSource} style={styles.welcomeAvatarImage} resizeMode="cover" />
            </View>
            <View style={[styles.welcomeCopyWrap, isRTL && { alignItems: 'flex-end' }]}>
              <MayushText variant="sectionTitle" color={colors.brand.navy900} style={[styles.welcomeTitle, isRTL && styles.rtlText]}>
                {heading(`Bonjour ${userFirstName} 👋`, `مرحبًا ${userFirstName} 👋`)}
              </MayushText>
              <MayushText variant="smallBody" color={colors.neutral.gray700} style={[styles.welcomeSubtitle, isRTL && styles.rtlText]}>
                {heading('Ravi de vous revoir ! Découvrez\nnos nouveautés sélectionnées pour vous.', 'سعداء برؤيتك مجددًا! اكتشف تشكيلتنا الجديدة المختارة لك.')}
              </MayushText>
            </View>
          </View>

          {/* 3. Active Order Card: "Commande en cours" */}
          <View style={[styles.activeOrderCard, isRTL && styles.rowReverse]}>
            <View style={[styles.activeOrderLeft, isRTL && styles.rowReverse]}>
              <View style={styles.activeOrderIconCircle}>
                <MayushIcon name="box" size={24} color={colors.brand.orange500} />
              </View>
              <View style={[styles.activeOrderDetails, isRTL && { alignItems: 'flex-end' }]}>
                <MayushText variant="caption" color={colors.brand.orange500} style={styles.activeOrderBadgeLabel}>
                  {heading('Commande en cours', 'طلب قيد التنفيذ')}
                </MayushText>
                <MayushText variant="strongBody" color={colors.brand.navy900} style={styles.activeOrderIdText}>
                  {orderIdText}
                </MayushText>
                <MayushText variant="caption" color="#16A34A" style={styles.activeOrderDateText}>
                  {orderDeliveryDateText}
                </MayushText>
              </View>
            </View>
            <TouchableOpacity accessibilityRole="button" accessibilityLabel={heading('Voir le suivi', 'تتبع الطلب')} onPress={() => onOpenOrder?.(orderIdText)} style={[styles.activeOrderTrackButton, isRTL && styles.rowReverse]}>
              <MayushText variant="smallBody" color={colors.brand.orange500} style={styles.activeOrderTrackText}>
                {heading('Voir le suivi', 'تتبع الطلب')}
              </MayushText>
              <MayushIcon name={isRTL ? 'chevron-left' : 'chevron-right'} size={16} color={colors.brand.orange500} />
            </TouchableOpacity>
          </View>

          {/* 4. Hero Carousel Banner ("L'art d'habiter selon vos envies") */}
          <View style={styles.heroWrapper}>
            <Image source={heroSlides.length > 0 ? heroSlides[activeHeroIndex]?.bg : LOGGED_IN_HERO_IMAGE} resizeMode="cover" style={styles.heroImage} />
            <View style={styles.heroOverlayDarkener} />
            <View style={[styles.heroCopyPanel, isRTL && styles.heroCopyPanelRtl]}>
              <MayushText variant="display" color={colors.surface.white} style={styles.heroTitle}>
                {heading("L'art d'habiter", "فن السكن")}{'\n'}
                <MayushText variant="display" color={colors.brand.orange500} style={styles.heroTitleAccent}>
                  {heading("selon vos envies", "حسب رغباتك")}
                </MayushText>
              </MayushText>
              <MayushText variant="smallBody" color={colors.surface.white} style={styles.heroSubtitle}>
                {heading('Mobilier & décoration haut de gamme sélectionnés avec passion.', 'أثاث وديكور راقٍ مختار بعناية وشغف.')}
              </MayushText>
              <TouchableOpacity accessibilityRole="button" accessibilityLabel={heading('Découvrir la collection', 'اكتشف التشكيلة')} onPress={() => onNavigateTab?.('categories')} activeOpacity={0.84} style={styles.heroCtaButton}>
                <MayushText variant="smallBody" color={colors.surface.white} style={styles.heroCtaText}>
                  {heading('Découvrir la collection', 'اكتشف التشكيلة')}
                </MayushText>
              </TouchableOpacity>
            </View>
            <View style={[styles.heroDots, isRTL && styles.rowReverse]}>
              <View style={[styles.heroDot, styles.heroDotActive]} />
              <View style={[styles.heroDot, styles.heroDotInactive]} />
              <View style={[styles.heroDot, styles.heroDotInactive]} />
            </View>
          </View>

          {/* 5. Recommandé pour vous Section */}
          {recommendedProducts.length > 0 && (
            <>
              <SectionHeader label={heading('Recommandé pour vous', 'موصى به لك')} action={heading('Voir tout', 'عرض الكل')} isRTL={isRTL} onPress={onOpenRecommended ?? (() => onNavigateTab?.('categories'))} />
              <ProductRail
                products={recommendedProducts}
                cardWidth={productWidth}
                onSelect={onSelectProduct}
                wishlistedProductIds={wishlistedProductIds}
                onToggleWishlist={onToggleWishlist}
              />
            </>
          )}

          {/* 6. Consultés récemment Section */}
          {recentlyViewed.length > 0 && (
            <>
              <SectionHeader label={heading('Consultés récemment', 'شوهدت مؤخراً')} action={heading('Voir tout', 'عرض الكل')} isRTL={isRTL} onPress={onOpenRecentlyViewed ?? (() => onNavigateTab?.('categories'))} />
              <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.recentlyViewedRail}>
                {recentlyViewed.map((p) => (
                  <TouchableOpacity key={p.id} activeOpacity={0.84} style={styles.recentlyViewedCard} onPress={onOpenRecentlyViewed ?? (() => onNavigateTab?.('categories'))}>
                    <Image source={p.thumbnail_image ? { uri: normalizeImageUrl(p.thumbnail_image) } : COLLECTION_FALLBACK_IMAGE} style={styles.recentlyViewedImage} resizeMode="cover" />
                    <View style={styles.recentlyViewedWishlistBtn}>
                      <MayushIcon name="heart" size={14} color={colors.brand.navy900} />
                    </View>
                    <View style={styles.recentlyViewedEyeBadge}>
                      <MayushIcon name="eye" size={14} color={colors.brand.navy900} />
                    </View>
                    <View style={styles.recentlyViewedTitlePill}>
                      <MayushText variant="caption" color={colors.surface.white} numberOfLines={1} style={styles.recentlyViewedTitleText}>
                        {p.name}
                      </MayushText>
                    </View>
                  </TouchableOpacity>
                ))}
              </ScrollView>
            </>
          )}

          {/* 7. Catégories Section (8 Circular Categories + Voir tout) */}
          {displayCategories.length > 0 && (
            <>
              <SectionHeader label={heading('Catégories', 'الأقسام')} action={heading('Voir tout', 'عرض الكل')} isRTL={isRTL} onPress={() => onNavigateTab?.('categories')} />
              <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.loggedInCategoriesRail}>
                {displayCategories.map((cat) => (
                  <TouchableOpacity
                    key={cat.id}
                    activeOpacity={0.82}
                    style={styles.loggedInCategoryItem}
                    onPress={() => {
                      if (cat.categoryDto) onSelectCategory?.(cat.categoryDto);
                      else onNavigateTab?.('categories');
                    }}
                  >
                    <View style={styles.loggedInCategoryCircle}>
                      <Image source={cat.art} style={styles.loggedInCategoryArt} resizeMode="cover" />
                    </View>
                    <MayushText variant="caption" color={colors.brand.navy900} align="center" style={styles.loggedInCategoryLabel} numberOfLines={1}>
                      {cat.name}
                    </MayushText>
                  </TouchableOpacity>
                ))}
            <TouchableOpacity accessibilityRole="button" accessibilityLabel={heading('Voir tout', 'عرض الكل')} activeOpacity={0.82} style={styles.loggedInCategoryItem} onPress={() => onNavigateTab?.('categories')}>
              <View style={styles.loggedInCategoryMoreCircle}>
                <MayushIcon name="more-horizontal" size={22} color={colors.brand.navy900} />
              </View>
              <MayushText variant="caption" color={colors.brand.navy900} align="center" style={styles.loggedInCategoryLabel} numberOfLines={1}>
                {heading('Voir tout', 'عرض الكل')}
              </MayushText>
            </TouchableOpacity>
              </ScrollView>
            </>
          )}

          {/* 8. Flash Deal Section (With live countdown timer) — hidden when no active deals */}
          {flashDeals.length > 0 && (
            <>
              <View style={[styles.flashDealHeaderRow, isRTL && styles.rowReverse]}>
                <View style={[styles.flashDealTitleGroup, isRTL && styles.rowReverse]}>
                  <MayushIcon name="zap" size={20} color={colors.brand.orange500} />
                  <MayushText variant="sectionTitle" color={colors.brand.navy900} style={styles.flashDealTitleText}>
                    {heading('Flash Deal', 'عروض سريعة')}
                  </MayushText>
                </View>
                <View style={[styles.flashDealRightGroup, isRTL && styles.rowReverse]}>
                  {flashCountdown ? (
                    <View style={styles.countdownBadge}>
                      <MayushText variant="caption" color={colors.brand.orange500} style={styles.countdownText}>
                        {heading(`Fin dans ${flashCountdown}`, `ينتهي خلال ${flashCountdown}`)}
                      </MayushText>
                    </View>
                  ) : null}
                  <TouchableOpacity accessibilityRole="button" accessibilityLabel={heading('Voir tout', 'عرض الكل')} onPress={onOpenPromotions} activeOpacity={0.78}>
                    <MayushText variant="smallBody" color={colors.brand.orange500} style={styles.actionLabel}>
                      {heading('Voir tout >', 'عرض الكل >')}
                    </MayushText>
                  </TouchableOpacity>
                </View>
              </View>
              <ProductRail
                products={flashDeals}
                cardWidth={productWidth}
                onSelect={onSelectProduct}
                wishlistedProductIds={wishlistedProductIds}
                onToggleWishlist={onToggleWishlist}
              />
            </>
          )}

          {/* 9. Promo Banner — only shown when backend provides active promo */}
          {promoBanner && promoBanner.imageUrl ? (
            <TouchableOpacity activeOpacity={0.84} onPress={onOpenPromotions} style={styles.middlePromoBannerWrapper}>
              <Image source={{ uri: promoBanner.imageUrl }} resizeMode="cover" style={styles.middlePromoBannerImage} />
            </TouchableOpacity>
          ) : null}

          {/* 10. Nouveautés Section */}
          {newArrivals.length > 0 && (
            <>
              <SectionHeader label={heading('Nouveautés', 'وصل حديثاً')} action={heading('Voir tout', 'عرض الكل')} isRTL={isRTL} onPress={onOpenNewArrivals ?? onOpenPromotions ?? (() => onNavigateTab?.('categories'))} />
              <ProductRail
                products={newArrivals}
                cardWidth={productWidth}
                onSelect={onSelectProduct}
                wishlistedProductIds={wishlistedProductIds}
                onToggleWishlist={onToggleWishlist}
              />
            </>
          )}

          {/* 11. Meilleures ventes Section */}
          {bestSellers.length > 0 && (
            <>
              <SectionHeader label={heading('Meilleures ventes', 'الأكثر مبيعاً')} action={heading('Voir tout', 'عرض الكل')} isRTL={isRTL} onPress={onOpenBestSellers ?? (() => onNavigateTab?.('categories'))} />
              <ProductRail
                products={bestSellers}
                cardWidth={productWidth}
                onSelect={onSelectProduct}
                wishlistedProductIds={wishlistedProductIds}
                onToggleWishlist={onToggleWishlist}
              />
            </>
          )}

          {/* 12. Inspiration du moment Section */}
          <SectionHeader label={heading('Inspiration du moment', 'إلهام اليوم')} action={heading('Voir tout', 'عرض الكل')} isRTL={isRTL} onPress={onOpenInspiration ?? onOpenWishlist ?? (() => onNavigateTab?.('categories'))} />
          <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.inspirationRail}>
            <InspirationCard source={INSPIRATION_ARTWORK[0]} width={Math.round(contentWidth * 0.72)} onPress={onOpenInspiration ?? onOpenWishlist ?? (() => onNavigateTab?.('categories'))} />
            <InspirationCard source={INSPIRATION_ARTWORK[1]} width={Math.round(contentWidth * 0.72)} onPress={onOpenInspiration ?? onOpenWishlist ?? (() => onNavigateTab?.('categories'))} />
          </ScrollView>

          {/* 13. Collections vedettes Section */}
          {displayCollections.length > 0 && (
            <>
              <SectionHeader label={heading('Collections vedettes', 'التشكيلات المميزة')} action={heading('Voir tout', 'عرض الكل')} isRTL={isRTL} onPress={onOpenCollections ?? (() => onNavigateTab?.('categories'))} />
              <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.collectionsRow}>
                {displayCollections.map((col) => (
                  <TouchableOpacity key={col.id} activeOpacity={0.84} style={styles.collectionItem} onPress={onOpenCollections ?? (() => onNavigateTab?.('categories'))}>
                    <View style={styles.collectionCircleWrap}>
                      <Image source={col.image} style={styles.collectionImage} resizeMode="cover" />
                    </View>
                    <MayushText variant="caption" color={colors.brand.navy900} align="center" numberOfLines={2} style={styles.collectionTitle}>
                      {col.name}
                    </MayushText>
                    <MayushText variant="caption" color={colors.neutral.gray500} align="center" numberOfLines={2} style={styles.collectionSubtitle}>
                      {col.description}
                    </MayushText>
                  </TouchableOpacity>
                ))}
                <TouchableOpacity activeOpacity={0.84} style={styles.collectionItem} onPress={onOpenCollections ?? (() => onNavigateTab?.('categories'))}>
                  <View style={styles.collectionMoreCircle}>
                    <MayushIcon name="more-horizontal" size={24} color={colors.brand.navy900} />
                  </View>
                  <MayushText variant="caption" color={colors.brand.navy900} align="center" style={styles.collectionTitle}>
                    {heading('Voir toutes les\ncollections', 'عرض كل التشكيلات')}
                  </MayushText>
                </TouchableOpacity>
              </ScrollView>
            </>
          )}

          {/* 14. Nos sélections partenaires Section */}
          {topBrands.length > 0 && (
            <View style={styles.partnersSection}>
              <SectionHeader label={heading('Nos sélections partenaires', 'شركاؤنا المختارون')} action={heading('Voir tout', 'عرض الكل')} isRTL={isRTL} onPress={onOpenPartners ?? (() => onNavigateTab?.('categories'))} />
              <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.partnersRow}>
                {topBrands.map((b) => (
                  <TouchableOpacity key={b.id} style={styles.partnerCard} onPress={onOpenPartners ?? (() => onNavigateTab?.('categories'))} activeOpacity={0.85}>
                    {b.logo ? (
                      <Image source={{ uri: b.logo }} style={{ width: 60, height: 40 }} resizeMode="contain" />
                    ) : (
                      <MayushText variant="strongBody" color={colors.brand.navy900} align="center" style={styles.partnerBrandName}>
                        {b.name}
                      </MayushText>
                    )}
                  </TouchableOpacity>
                ))}
              </ScrollView>
            </View>
          )}

          {/* 15. Pièces par ambiance Section */}
          <SectionHeader label={heading('Pièces par ambiance', 'غرف حسب الطراز')} action={heading('Voir tout', 'عرض الكل')} isRTL={isRTL} onPress={onOpenAmbiances ?? (() => onNavigateTab?.('categories'))} />
          <View style={styles.ambianceGrid}>
            {AMBIANCES_DATA.map((amb) => (
              <TouchableOpacity key={amb.id} activeOpacity={0.86} style={styles.ambianceCard} onPress={onOpenAmbiances ?? (() => onNavigateTab?.('categories'))}>
                <Image source={amb.image} style={styles.ambianceImage} resizeMode="cover" />
                <View style={styles.ambianceContent}>
                  <MayushText variant="smallBody" color={colors.brand.navy900} style={styles.ambianceTitle}>
                    {amb.title}
                  </MayushText>
                  <MayushText variant="caption" color={colors.neutral.gray700} style={styles.ambianceSubtitle}>
                    {amb.subtitle}
                  </MayushText>
                </View>
              </TouchableOpacity>
            ))}
          </View>

          {/* 16. Services, Reviews, Journal, and Trust Badges */}
          {renderSharedBottomSections()}
        </ScrollView>
      </View>
    );
  }

  // ==========================================
  // GUEST / UNCONNECTED HOME VIEW (main-home-fullstructure.png)
  // ==========================================
  return (
    <View style={styles.container}>
      <ScrollView showsVerticalScrollIndicator={false} contentContainerStyle={[styles.content, { paddingHorizontal: contentPadding }]}>
        {/* 1. Header Bar: Bell + Logo + Cart */}
        <View style={[styles.header, isRTL && styles.rowReverse]}>
          <TouchableOpacity accessibilityRole="button" accessibilityLabel={heading('Notifications', 'الإشعارات')} style={styles.headerIconButton}>
            <MayushIcon name="bell" size={24} color={colors.brand.navy900} />
          </TouchableOpacity>
          <MayushLogo width={logoWidth} height={Math.round(logoWidth * 0.288)} />
          <TouchableOpacity accessibilityRole="button" accessibilityLabel={heading('Mon panier', 'سلة التسوق')} onPress={() => onNavigateTab?.('cart')} style={styles.headerIconButton}>
            <MayushIcon name="shopping-cart" size={24} color={colors.brand.navy900} />
            {displayCartCount > 0 ? (
              <View style={styles.headerCartBadge}>
                <MayushText variant="caption" color={colors.surface.white} style={styles.badgeText}>
                  {displayCartCount}
                </MayushText>
              </View>
            ) : null}
          </TouchableOpacity>
        </View>

        {/* 2. Search Input Bar */}
        <TouchableOpacity activeOpacity={0.88} onPress={onOpenSearch || (() => onNavigateTab?.('categories'))} style={[styles.searchBar, isRTL && styles.rowReverse]}>
          <MayushIcon name="search" size={20} color={colors.neutral.gray500} style={styles.searchIcon} />
          <MayushText variant="body" color={colors.neutral.gray500} style={styles.searchPlaceholder}>
            {heading('Rechercher un produit, une collection...', 'ابحث عن منتج أو تشكيلة...')}
          </MayushText>
        </TouchableOpacity>

        {/* 3. Hero Banner Slider */}
        {slidersLoading ? (
          <HeroSliderSkeleton height={heroHeight} />
        ) : heroSlides.length > 0 ? (
          <View style={styles.heroWrapper}>
            <ScrollView
              ref={heroPagerRef}
              horizontal
              pagingEnabled
              showsHorizontalScrollIndicator={false}
              onMomentumScrollEnd={(e) => setActiveHeroIndex(Math.round(e.nativeEvent.contentOffset.x / contentWidth))}
            >
              {heroSlides.map((slide, index) => (
                <View key={`${slide.bg.uri}-${index}`} style={[styles.heroSlide, { width: contentWidth, height: heroHeight }]}>
                  <Image source={slide.bg} resizeMode="cover" style={styles.heroImage} />
                  <View style={styles.heroOverlayDarkener} />
                  <View style={[styles.heroCopyPanel, isRTL && styles.heroCopyPanelRtl]}>
                    <MayushText variant="display" color={colors.surface.white} style={styles.heroTitle}>
                      {slide.title1}{'\n'}
                      <MayushText variant="display" color={colors.brand.orange500} style={styles.heroTitleAccent}>
                        {slide.title2}
                      </MayushText>
                    </MayushText>
                    <MayushText variant="smallBody" color={colors.surface.white} style={styles.heroSubtitle}>
                      {slide.subtitle}
                    </MayushText>
                    <TouchableOpacity accessibilityRole="button" accessibilityLabel={slide.button} onPress={() => onNavigateTab?.('categories')} activeOpacity={0.84} style={styles.heroCtaButton}>
                      <MayushText variant="smallBody" color={colors.surface.white} style={styles.heroCtaText}>
                        {slide.button}
                      </MayushText>
                    </TouchableOpacity>
                  </View>
                </View>
              ))}
            </ScrollView>
            <View style={[styles.heroDots, isRTL && styles.rowReverse]}>
              {heroSlides.map((_, idx) => (
                <TouchableOpacity key={idx} onPress={() => selectHeroSlide(idx)} hitSlop={6} style={[styles.heroDot, idx === activeHeroIndex ? styles.heroDotActive : styles.heroDotInactive]} />
              ))}
            </View>
          </View>
        ) : null}

        {/* 4. Featured Categories Row (5 Circles + Voir tout) */}
        <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.categoryRow}>
          {displayCategories.map((cat) => (
            <TouchableOpacity key={cat.id} accessibilityRole="button" accessibilityLabel={cat.name} activeOpacity={0.82} style={[styles.categoryItem, { width: categoryItemWidth }]} onPress={() => onSelectCategory?.(cat.categoryDto)}>
              <View style={[styles.categoryCircleWrap, { width: categoryCircleSize, height: categoryCircleSize, borderRadius: categoryCircleSize / 2 }]}>
                <Image source={cat.art} style={{ width: categoryCircleSize, height: categoryCircleSize, borderRadius: categoryCircleSize / 2 }} resizeMode="cover" />
              </View>
              <MayushText variant="smallBody" color={colors.brand.navy900} align="center" style={styles.categoryLabel} numberOfLines={1}>{cat.name}</MayushText>
            </TouchableOpacity>
          ))}
          <TouchableOpacity accessibilityRole="button" accessibilityLabel={heading('Voir tout', 'عرض الكل')} activeOpacity={0.82} style={[styles.categoryItem, { width: categoryItemWidth }]} onPress={() => onNavigateTab?.('categories')}>
            <View style={[styles.moreCircleWrap, { width: categoryCircleSize, height: categoryCircleSize, borderRadius: categoryCircleSize / 2 }]}>
              <MayushIcon name="more-horizontal" size={22} color={colors.brand.navy900} />
            </View>
            <MayushText variant="smallBody" color={colors.brand.navy900} align="center" style={styles.categoryLabel} numberOfLines={1}>{heading('Voir tout', 'عرض الكل')}</MayushText>
          </TouchableOpacity>
        </ScrollView>

        {/* 5. Nouveautés Section */}
        {newArrivalsLoading ? (
          <>
            <SectionHeader label={heading('Nouveautés', 'وصول جديد')} action={heading('Voir tout', 'عرض الكل')} isRTL={isRTL} onPress={onOpenNewArrivals ?? onOpenPromotions ?? (() => onNavigateTab?.('categories'))} />
            <ProductRailSkeleton cardWidth={productWidth} count={3} />
          </>
        ) : newArrivals.length > 0 ? (
          <>
            <SectionHeader label={heading('Nouveautés', 'وصول جديد')} action={heading('Voir tout', 'عرض الكل')} isRTL={isRTL} onPress={onOpenNewArrivals ?? onOpenPromotions ?? (() => onNavigateTab?.('categories'))} />
            <ProductRail
              products={newArrivals}
              cardWidth={productWidth}
              onSelect={onSelectProduct}
              wishlistedProductIds={wishlistedProductIds}
              onToggleWishlist={onToggleWishlist}
            />
          </>
        ) : null}

        {/* 6. Meilleures ventes Section */}
        {bestSellersLoading ? (
          <>
            <SectionHeader label={heading('Meilleures ventes', 'الأكثر مبيعاً')} action={heading('Voir tout', 'عرض الكل')} isRTL={isRTL} onPress={onOpenBestSellers ?? (() => onNavigateTab?.('categories'))} />
            <ProductRailSkeleton cardWidth={productWidth} count={3} />
          </>
        ) : bestSellers.length > 0 ? (
          <>
            <SectionHeader label={heading('Meilleures ventes', 'الأكثر مبيعاً')} action={heading('Voir tout', 'عرض الكل')} isRTL={isRTL} onPress={onOpenBestSellers ?? (() => onNavigateTab?.('categories'))} />
            <ProductRail
              products={bestSellers}
              cardWidth={productWidth}
              onSelect={onSelectProduct}
              wishlistedProductIds={wishlistedProductIds}
              onToggleWishlist={onToggleWishlist}
            />
          </>
        ) : null}

        {/* 7. Offres du moment Banner — only shown when backend provides active promo */}
        {promoBanner && promoBanner.imageUrl ? (
          <TouchableOpacity activeOpacity={0.84} onPress={onOpenPromotions} style={styles.middlePromoBannerWrapper}>
            <Image source={{ uri: promoBanner.imageUrl }} resizeMode="cover" style={styles.middlePromoBannerImage} />
          </TouchableOpacity>
        ) : null}

        {/* 8. Inspiration du moment Section */}
        <SectionHeader label={heading('Inspiration du moment', 'إلهام اليوم')} action={heading('Voir tout', 'عرض الكل')} isRTL={isRTL} onPress={onOpenInspiration ?? onOpenWishlist ?? (() => onNavigateTab?.('categories'))} />
        <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.inspirationRail}>
          <InspirationCard source={INSPIRATION_ARTWORK[0]} width={Math.round(contentWidth * 0.72)} onPress={onOpenInspiration ?? onOpenWishlist ?? (() => onNavigateTab?.('categories'))} />
          <InspirationCard source={INSPIRATION_ARTWORK[1]} width={Math.round(contentWidth * 0.72)} onPress={onOpenInspiration ?? onOpenWishlist ?? (() => onNavigateTab?.('categories'))} />
        </ScrollView>

        {/* 9. Collections vedettes Section */}
        {displayCollections.length > 0 && (
          <>
            <SectionHeader label={heading('Collections vedettes', 'التشكيلات المميزة')} action={heading('Voir toutes les collections', 'عرض كل التشكيلات')} isRTL={isRTL} onPress={onOpenCollections ?? (() => onNavigateTab?.('categories'))} />
            <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.collectionsRow}>
              {displayCollections.map((col) => (
                <TouchableOpacity key={col.id} activeOpacity={0.84} style={styles.collectionItem} onPress={onOpenCollections ?? (() => onNavigateTab?.('categories'))}>
                  <View style={styles.collectionCircleWrap}>
                    <Image source={col.image} style={styles.collectionImage} resizeMode="cover" />
                  </View>
                  <MayushText variant="caption" color={colors.brand.navy900} align="center" numberOfLines={2} style={styles.collectionTitle}>
                    {col.name}
                  </MayushText>
                  <MayushText variant="caption" color={colors.neutral.gray500} align="center" numberOfLines={2} style={styles.collectionSubtitle}>
                    {col.description}
                  </MayushText>
                </TouchableOpacity>
              ))}
              <TouchableOpacity activeOpacity={0.84} style={styles.collectionItem} onPress={onOpenCollections ?? (() => onNavigateTab?.('categories'))}>
                <View style={styles.collectionMoreCircle}>
                  <MayushIcon name="more-horizontal" size={24} color={colors.brand.navy900} />
                </View>
                <MayushText variant="caption" color={colors.brand.navy900} align="center" style={styles.collectionTitle}>
                  {heading('Voir toutes les\ncollections', 'عرض كل التشكيلات')}
                </MayushText>
              </TouchableOpacity>
            </ScrollView>
          </>
        )}

        {/* 10. Nos sélections partenaires Section */}
        {topBrands.length > 0 && (
          <View style={styles.partnersSection}>
            <SectionHeader label={heading('Nos sélections partenaires', 'شركاؤنا المختارون')} action={heading('Voir tout', 'عرض الكل')} isRTL={isRTL} onPress={onOpenPartners ?? (() => onNavigateTab?.('categories'))} />
            <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.partnersRow}>
              {topBrands.map((b) => (
                <TouchableOpacity key={b.id} style={styles.partnerCard} onPress={onOpenPartners ?? (() => onNavigateTab?.('categories'))} activeOpacity={0.85}>
                  {b.logo ? (
                    <Image source={{ uri: b.logo }} style={{ width: 60, height: 40 }} resizeMode="contain" />
                  ) : (
                    <MayushText variant="strongBody" color={colors.brand.navy900} align="center" style={styles.partnerBrandName}>
                      {b.name}
                    </MayushText>
                  )}
                </TouchableOpacity>
              ))}
            </ScrollView>
          </View>
        )}

        {/* 11. Recommandé pour vous Section */}
        {recommendedProducts.length > 0 && (
          <>
            <SectionHeader label={heading('Recommandé pour vous', 'موصى به لك')} action={heading('Voir tout', 'عرض الكل')} isRTL={isRTL} onPress={onOpenRecommended ?? (() => onNavigateTab?.('categories'))} />
            <ProductRail
              products={recommendedProducts}
              cardWidth={productWidth}
              onSelect={onSelectProduct}
              wishlistedProductIds={wishlistedProductIds}
              onToggleWishlist={onToggleWishlist}
            />
          </>
        )}

        {/* 12. Pièces par ambiance Section */}
        <SectionHeader label={heading('Pièces par ambiance', 'غرف حسب الطراز')} action={heading('Voir tout', 'عرض الكل')} isRTL={isRTL} onPress={onOpenAmbiances ?? (() => onNavigateTab?.('categories'))} />
        <View style={styles.ambianceGrid}>
          {AMBIANCES_DATA.map((amb) => (
            <TouchableOpacity key={amb.id} activeOpacity={0.86} style={styles.ambianceCard} onPress={onOpenAmbiances ?? (() => onNavigateTab?.('categories'))}>
              <Image source={amb.image} style={styles.ambianceImage} resizeMode="cover" />
              <View style={styles.ambianceContent}>
                <MayushText variant="smallBody" color={colors.brand.navy900} style={styles.ambianceTitle}>
                  {amb.title}
                </MayushText>
                <MayushText variant="caption" color={colors.neutral.gray700} style={styles.ambianceSubtitle}>
                  {amb.subtitle}
                </MayushText>
              </View>
            </TouchableOpacity>
          ))}
        </View>

        {/* 13. Services, Reviews, Journal, and Trust Badges */}
        {renderSharedBottomSections()}
      </ScrollView>
    </View>
  );
};

const SectionHeader: React.FC<{ label: string; action: string; isRTL: boolean; onPress?: () => void }> = ({ label, action, isRTL, onPress }) => (
  <View style={[styles.sectionHeader, isRTL && styles.rowReverse]}>
    <MayushText variant="sectionTitle" color={colors.brand.navy900} style={styles.sectionTitleText}>
      {label}
    </MayushText>
    <TouchableOpacity accessibilityRole="button" accessibilityLabel={action} onPress={onPress} activeOpacity={0.78}>
      <MayushText variant="smallBody" color={colors.brand.orange500} style={styles.actionLabel}>
        {action}
      </MayushText>
    </TouchableOpacity>
  </View>
);

const ProductRail: React.FC<{
  products: any[];
  cardWidth: number;
  onSelect?: (product: ProductMiniDto) => void;
  showRating?: boolean;
  wishlistedProductIds?: number[];
  onToggleWishlist?: (product: ProductMiniDto) => void;
  badgeText?: string;
}> = ({ products, cardWidth, onSelect, showRating, wishlistedProductIds = [], onToggleWishlist, badgeText }) => (
  <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.productRail}>
    {products.map((item) => {
      const formattedPrice = item.main_price || (item.base_discounted_price ? `${item.base_discounted_price} MAD` : item.stroked_price) || 'MAD';
      const rawThumbnail = item.thumbnailSource || item.thumbnail_image || (item.photos && item.photos[0]);
      const thumbnailSource = (typeof rawThumbnail === 'number' || (typeof rawThumbnail === 'object' && rawThumbnail !== null && !rawThumbnail.uri))
        ? rawThumbnail
        : undefined;
      const thumbnailUrl = typeof rawThumbnail === 'string'
        ? rawThumbnail
        : (typeof rawThumbnail === 'object' && rawThumbnail?.uri ? rawThumbnail.uri : undefined);
      const itemBadge = item.id === 401 ? 'Bestseller' : badgeText;

      return (
        <ProductCard
          key={item.id}
          name={item.name}
          thumbnailUrl={thumbnailUrl}
          thumbnailSource={thumbnailSource}
          currentPriceFormatted={formattedPrice}
          originalPriceFormatted={item.stroked_price}
          hasDiscount={item.has_discount}
          discountPercentage={item.discount || undefined}
          badgeText={itemBadge}
          rating={showRating ? item.rating : undefined}
          salesCount={showRating ? item.sales : undefined}
          width={cardWidth}
          onPress={() => onSelect?.(item)}
          isFavorite={wishlistedProductIds.includes(item.id)}
          onFavoritePress={() => onToggleWishlist?.(item)}
        />
      );
    })}
  </ScrollView>
);

const InspirationCard: React.FC<{ source: ImageSourcePropType; width: number; onPress?: () => void }> = ({ source, width, onPress }) => (
  <TouchableOpacity accessibilityRole="button" activeOpacity={0.88} onPress={onPress} style={[styles.inspirationCard, { width }]}>
    <Image source={source} style={styles.inspirationImage} resizeMode="cover" />
  </TouchableOpacity>
);

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: colors.surface.creamLight,
  },
  content: {
    paddingTop: 8,
    paddingBottom: 28,
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    marginBottom: 12,
  },
  loggedInHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    marginBottom: 16,
    paddingTop: 4,
  },
  headerActionsCluster: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
  },
  headerIconButton: {
    width: 44,
    height: 44,
    borderRadius: 22,
    alignItems: 'center',
    justifyContent: 'center',
    position: 'relative',
  },
  headerCartBadge: {
    position: 'absolute',
    top: 4,
    right: 4,
    backgroundColor: colors.brand.orange500,
    borderRadius: 9,
    minWidth: 18,
    height: 18,
    alignItems: 'center',
    justifyContent: 'center',
    paddingHorizontal: 4,
  },
  badgeText: {
    fontSize: 10,
    fontWeight: '700',
    lineHeight: 12,
  },
  welcomeGreeting: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 14,
    marginBottom: 18,
  },
  welcomeAvatarWrap: {
    width: 58,
    height: 58,
    borderRadius: 29,
    overflow: 'hidden',
    borderWidth: 2,
    borderColor: '#EFE8DC',
    backgroundColor: '#F5F5F0',
  },
  welcomeAvatarImage: {
    width: '100%',
    height: '100%',
  },
  welcomeCopyWrap: {
    flex: 1,
  },
  welcomeTitle: {
    fontSize: 19,
    fontWeight: '800',
    marginBottom: 2,
  },
  welcomeSubtitle: {
    fontSize: 12,
    lineHeight: 16,
  },
  activeOrderCard: {
    width: '100%',
    backgroundColor: colors.surface.white,
    borderRadius: 16,
    borderWidth: 1,
    borderColor: '#EFE8DC',
    padding: 12,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    marginBottom: 18,
  },
  activeOrderLeft: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
    flex: 1,
  },
  activeOrderIconCircle: {
    width: 46,
    height: 46,
    borderRadius: 23,
    backgroundColor: '#FFF7ED',
    alignItems: 'center',
    justifyContent: 'center',
  },
  activeOrderDetails: {
    flex: 1,
    gap: 1,
  },
  activeOrderBadgeLabel: {
    fontSize: 11,
    fontWeight: '700',
  },
  activeOrderIdText: {
    fontSize: 13,
    fontWeight: '700',
  },
  activeOrderDateText: {
    fontSize: 11,
    fontWeight: '600',
  },
  activeOrderTrackButton: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
    borderWidth: 1,
    borderColor: '#FDBA74',
    borderRadius: 12,
    paddingHorizontal: 12,
    paddingVertical: 7,
    backgroundColor: colors.surface.white,
  },
  activeOrderTrackText: {
    fontSize: 12,
    fontWeight: '600',
  },
  heroWrapper: {
    borderRadius: 16,
    overflow: 'hidden',
    marginBottom: 18,
    position: 'relative',
    height: 180,
  },
  heroSlide: {
    position: 'relative',
  },
  heroImage: {
    ...StyleSheet.absoluteFill,
    width: '100%',
    height: '100%',
  },
  heroOverlayDarkener: {
    ...StyleSheet.absoluteFill,
    backgroundColor: 'rgba(15, 23, 42, 0.42)',
  },
  heroCopyPanel: {
    position: 'absolute',
    top: 14,
    left: 16,
    right: 50,
    bottom: 24,
    justifyContent: 'center',
  },
  heroCopyPanelRtl: {
    left: 50,
    right: 16,
    alignItems: 'flex-end',
  },
  heroTitle: {
    fontSize: 18,
    lineHeight: 22,
    fontWeight: '800',
    color: colors.surface.white,
  },
  heroTitleAccent: {
    color: colors.brand.orange500,
  },
  heroSubtitle: {
    fontSize: 11,
    lineHeight: 14,
    marginTop: 4,
    marginBottom: 10,
    opacity: 0.92,
  },
  heroCtaButton: {
    backgroundColor: colors.brand.orange500,
    borderRadius: 16,
    paddingHorizontal: 14,
    paddingVertical: 6,
    alignSelf: 'flex-start',
  },
  heroCtaText: {
    fontSize: 12,
    fontWeight: '700',
  },
  heroDots: {
    position: 'absolute',
    bottom: 8,
    left: 16,
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
  },
  heroDot: {
    height: 5,
    borderRadius: 3,
  },
  heroDotActive: {
    width: 16,
    backgroundColor: colors.brand.orange500,
  },
  heroDotInactive: {
    width: 6,
    backgroundColor: colors.surface.white,
    opacity: 0.8,
  },
  productRailScroll: {
    paddingVertical: 6,
    gap: 12,
    marginBottom: 18,
  },
  simpleProductCard: {
    width: 160,
    backgroundColor: colors.surface.white,
    borderRadius: 16,
    borderWidth: 1,
    borderColor: '#EFE8DC',
    padding: 10,
  },
  simpleProductImageWrap: {
    width: '100%',
    height: 110,
    backgroundColor: '#FAF7F2',
    borderRadius: 12,
    overflow: 'hidden',
    alignItems: 'center',
    justifyContent: 'center',
    position: 'relative',
    marginBottom: 8,
  },
  simpleProductImage: {
    width: '88%',
    height: '88%',
  },
  simpleWishlistBtn: {
    position: 'absolute',
    top: 6,
    right: 6,
    width: 28,
    height: 28,
    borderRadius: 14,
    backgroundColor: colors.surface.white,
    alignItems: 'center',
    justifyContent: 'center',
    shadowColor: '#000',
    shadowOpacity: 0.08,
    shadowRadius: 2,
    elevation: 2,
  },
  discountTagBadge: {
    position: 'absolute',
    top: 6,
    left: 6,
    backgroundColor: colors.brand.orange500,
    borderRadius: 6,
    paddingHorizontal: 6,
    paddingVertical: 2,
  },
  discountTagText: {
    fontSize: 10,
    fontWeight: '700',
  },
  simpleProductTitle: {
    fontSize: 12,
    fontWeight: '600',
    minHeight: 30,
    marginBottom: 4,
  },
  priceRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
  },
  simpleProductPrice: {
    fontSize: 13,
    fontWeight: '700',
  },
  strokedPrice: {
    fontSize: 11,
    textDecorationLine: 'line-through',
  },
  ratingRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
    marginTop: 2,
  },
  starsGroup: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 2,
  },
  salesCountText: {
    fontSize: 11,
  },
  recentlyViewedRail: {
    paddingVertical: 6,
    gap: 12,
    marginBottom: 18,
  },
  recentlyViewedCard: {
    width: 145,
    height: 105,
    borderRadius: 16,
    overflow: 'hidden',
    position: 'relative',
    backgroundColor: '#FAF7F2',
    borderWidth: 1,
    borderColor: '#EFE8DC',
  },
  recentlyViewedImage: {
    width: '100%',
    height: '100%',
  },
  recentlyViewedWishlistBtn: {
    position: 'absolute',
    top: 6,
    right: 6,
    width: 24,
    height: 24,
    borderRadius: 12,
    backgroundColor: colors.surface.white,
    alignItems: 'center',
    justifyContent: 'center',
  },
  recentlyViewedEyeBadge: {
    position: 'absolute',
    bottom: 6,
    right: 6,
    width: 24,
    height: 24,
    borderRadius: 12,
    backgroundColor: colors.surface.white,
    alignItems: 'center',
    justifyContent: 'center',
  },
  recentlyViewedTitlePill: {
    position: 'absolute',
    bottom: 6,
    left: 6,
    maxWidth: 90,
    backgroundColor: 'rgba(15, 23, 42, 0.7)',
    borderRadius: 8,
    paddingHorizontal: 6,
    paddingVertical: 2,
  },
  recentlyViewedTitleText: {
    fontSize: 10,
    fontWeight: '600',
  },
  loggedInCategoriesRail: {
    paddingVertical: 6,
    gap: 12,
    marginBottom: 18,
  },
  loggedInCategoryItem: {
    alignItems: 'center',
    width: 68,
  },
  loggedInCategoryCircle: {
    width: 58,
    height: 58,
    borderRadius: 29,
    overflow: 'hidden',
    backgroundColor: '#F3F4F6',
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 6,
    borderWidth: 1,
    borderColor: '#EFE8DC',
  },
  loggedInCategoryArt: {
    width: '100%',
    height: '100%',
  },
  loggedInCategoryMoreCircle: {
    width: 58,
    height: 58,
    borderRadius: 29,
    backgroundColor: '#F3F4F6',
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 6,
    borderWidth: 1,
    borderColor: '#EFE8DC',
  },
  loggedInCategoryLabel: {
    fontSize: 11,
    fontWeight: '600',
  },
  flashDealHeaderRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    marginBottom: 10,
  },
  flashDealTitleGroup: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
  },
  flashDealTitleText: {
    fontSize: 17,
    fontWeight: '800',
  },
  flashDealRightGroup: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
  },
  countdownBadge: {
    backgroundColor: '#FFF7ED',
    borderWidth: 1,
    borderColor: '#FED7AA',
    borderRadius: 8,
    paddingHorizontal: 8,
    paddingVertical: 3,
  },
  countdownText: {
    fontSize: 11,
    fontWeight: '700',
  },
  middlePromoBannerWrapper: {
    borderRadius: 16,
    overflow: 'hidden',
    height: 120,
    marginBottom: 18,
    position: 'relative',
  },
  middlePromoBannerImage: {
    ...StyleSheet.absoluteFill,
    width: '100%',
    height: '100%',
  },
  middlePromoOverlay: {
    ...StyleSheet.absoluteFill,
    backgroundColor: 'rgba(20, 15, 10, 0.45)',
  },
  middlePromoContent: {
    position: 'absolute',
    top: 10,
    left: 14,
    right: 14,
    bottom: 10,
    justifyContent: 'center',
  },
  middlePromoContentRtl: {
    alignItems: 'flex-end',
  },
  middlePromoPreTitle: {
    fontSize: 11,
    fontWeight: '600',
    opacity: 0.9,
  },
  middlePromoTitle: {
    fontSize: 17,
    lineHeight: 20,
    fontWeight: '800',
  },
  middlePromoSubtitle: {
    fontSize: 10,
    opacity: 0.85,
    marginTop: 2,
    marginBottom: 6,
  },
  middlePromoCtaButton: {
    backgroundColor: colors.brand.orange500,
    borderRadius: 14,
    paddingHorizontal: 12,
    paddingVertical: 5,
    alignSelf: 'flex-start',
  },
  middlePromoCtaText: {
    fontSize: 11,
    fontWeight: '700',
  },
  middlePromoExpiry: {
    position: 'absolute',
    bottom: 2,
    left: 0,
    fontSize: 9,
    opacity: 0.7,
  },
  partnersSection: {
    marginVertical: 14,
  },
  partnersRow: {
    gap: 10,
    paddingVertical: 4,
  },
  partnerCard: {
    paddingHorizontal: 16,
    paddingVertical: 12,
    backgroundColor: colors.surface.white,
    borderRadius: 12,
    borderWidth: 1,
    borderColor: '#EFE8DC',
    alignItems: 'center',
    justifyContent: 'center',
    minWidth: 110,
  },
  partnerBrandName: {
    fontSize: 13,
    fontWeight: '800',
    letterSpacing: 0.6,
  },
  partnerBrandSub: {
    fontSize: 9,
    fontWeight: '600',
    marginTop: 1,
  },
  searchBar: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: colors.surface.white,
    borderRadius: 14,
    paddingHorizontal: 14,
    height: 48,
    borderWidth: 1,
    borderColor: '#EFE8DC',
    marginBottom: 16,
  },
  searchIcon: {
    marginRight: 8,
  },
  searchPlaceholder: {
    fontSize: 14,
  },
  sectionHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    marginBottom: 10,
  },
  sectionTitleText: {
    fontSize: 17,
    fontWeight: '800',
  },
  actionLabel: {
    fontSize: 13,
    fontWeight: '600',
  },
  categoryRow: {
    paddingVertical: 6,
    gap: 8,
    marginBottom: 16,
  },
  categoryItem: {
    alignItems: 'center',
  },
  categoryCircleWrap: {
    overflow: 'hidden',
    borderWidth: 1,
    borderColor: '#EFE8DC',
    marginBottom: 6,
  },
  moreCircleWrap: {
    backgroundColor: '#F3F4F6',
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 6,
    borderWidth: 1,
    borderColor: '#EFE8DC',
  },
  categoryLabel: {
    fontSize: 11,
    fontWeight: '600',
  },
  productRail: {
    paddingVertical: 6,
    gap: 12,
    marginBottom: 16,
  },
  offerBanner: {
    backgroundColor: '#FFF7ED',
    borderRadius: 16,
    padding: 14,
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
    borderWidth: 1,
    borderColor: '#FED7AA',
    marginBottom: 18,
  },
  offerIcon: {
    width: 42,
    height: 42,
    borderRadius: 21,
    backgroundColor: colors.surface.white,
    alignItems: 'center',
    justifyContent: 'center',
  },
  offerCopy: {
    flex: 1,
  },
  offerTitle: {
    fontSize: 15,
    fontWeight: '700',
    marginBottom: 2,
  },
  offerButton: {
    backgroundColor: colors.surface.white,
    borderWidth: 1,
    borderColor: colors.brand.orange500,
    borderRadius: 10,
    paddingHorizontal: 12,
    paddingVertical: 6,
  },
  offerButtonLabel: {
    fontSize: 12,
    fontWeight: '600',
  },
  inspirationRail: {
    paddingVertical: 6,
    gap: 12,
    marginBottom: 18,
  },
  inspirationCard: {
    height: 120,
    borderRadius: 16,
    overflow: 'hidden',
  },
  inspirationImage: {
    width: '100%',
    height: '100%',
  },
  collectionsRow: {
    paddingVertical: 6,
    gap: 14,
    marginBottom: 18,
  },
  collectionItem: {
    alignItems: 'center',
    width: 105,
  },
  collectionCircleWrap: {
    width: 90,
    height: 60,
    borderRadius: 30,
    overflow: 'hidden',
    marginBottom: 6,
    borderWidth: 1,
    borderColor: '#EFE8DC',
  },
  collectionImage: {
    width: '100%',
    height: '100%',
  },
  collectionMoreCircle: {
    width: 90,
    height: 60,
    borderRadius: 30,
    backgroundColor: '#F3F4F6',
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 6,
    borderWidth: 1,
    borderColor: '#EFE8DC',
  },
  collectionTitle: {
    fontSize: 11,
    fontWeight: '700',
  },
  collectionSubtitle: {
    fontSize: 9,
    marginTop: 1,
  },
  ambianceGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 10,
    marginBottom: 18,
  },
  ambianceCard: {
    width: '48%',
    backgroundColor: colors.surface.white,
    borderRadius: 14,
    overflow: 'hidden',
    borderWidth: 1,
    borderColor: '#EFE8DC',
  },
  ambianceImage: {
    width: '100%',
    height: 85,
  },
  ambianceContent: {
    padding: 8,
  },
  ambianceTitle: {
    fontSize: 12,
    fontWeight: '700',
  },
  ambianceSubtitle: {
    fontSize: 10,
    marginTop: 2,
  },
  servicesSection: {
    marginVertical: 14,
  },
  servicesHeaderTitle: {
    marginBottom: 10,
  },
  servicesRow: {
    gap: 10,
    paddingVertical: 4,
  },
  serviceCard: {
    alignItems: 'center',
    width: 105,
  },
  serviceIconCircle: {
    width: 44,
    height: 44,
    borderRadius: 22,
    backgroundColor: '#F3F4F6',
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 6,
  },
  serviceTitle: {
    fontSize: 10,
    lineHeight: 13,
  },
  reviewsSectionWrap: {
    marginVertical: 14,
  },
  reviewsRow: {
    gap: 12,
    paddingVertical: 4,
  },
  reviewScoreBox: {
    width: 125,
    backgroundColor: colors.surface.white,
    borderRadius: 16,
    borderWidth: 1,
    borderColor: '#EFE8DC',
    padding: 12,
    alignItems: 'center',
    justifyContent: 'center',
    gap: 4,
  },
  reviewScoreValue: {
    fontSize: 22,
    fontWeight: '800',
  },
  reviewScoreCount: {
    fontSize: 9,
    textAlign: 'center',
  },
  customerReviewCard: {
    width: 220,
    backgroundColor: colors.surface.white,
    borderRadius: 16,
    borderWidth: 1,
    borderColor: '#EFE8DC',
    padding: 12,
    justifyContent: 'space-between',
    gap: 6,
  },
  customerReviewQuote: {
    fontSize: 11,
    lineHeight: 15,
    flex: 1,
  },
  customerReviewAuthor: {
    fontSize: 12,
    fontWeight: '700',
  },
  journalBannerWrapper: {
    borderRadius: 16,
    overflow: 'hidden',
    height: 125,
    marginVertical: 14,
    backgroundColor: '#FAF7F2',
    borderWidth: 1,
    borderColor: '#EFE8DC',
    position: 'relative',
  },
  journalBgImage: {
    position: 'absolute',
    right: 0,
    top: 0,
    bottom: 0,
    width: '60%',
    height: '100%',
  },
  journalContentOverlay: {
    position: 'absolute',
    left: 14,
    top: 10,
    bottom: 10,
    width: '58%',
    justifyContent: 'center',
  },
  journalContentOverlayRtl: {
    left: undefined,
    right: 14,
    alignItems: 'flex-end',
  },
  journalTitle: {
    fontSize: 16,
    fontWeight: '800',
    marginBottom: 4,
  },
  journalSubtitle: {
    fontSize: 10,
    lineHeight: 13,
    marginBottom: 8,
  },
  journalButton: {
    backgroundColor: colors.brand.orange500,
    borderRadius: 14,
    paddingHorizontal: 12,
    paddingVertical: 5,
    alignSelf: 'flex-start',
  },
  journalButtonText: {
    fontSize: 11,
    fontWeight: '700',
  },
  trustFooterStrip: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    justifyContent: 'space-between',
    paddingVertical: 14,
    borderTopWidth: 1,
    borderTopColor: '#EFE8DC',
    gap: 8,
    marginTop: 8,
  },
  trustBadgeItem: {
    alignItems: 'center',
    width: '18%',
  },
  trustBadgeText: {
    fontSize: 8,
    marginTop: 4,
    lineHeight: 11,
  },
  rowReverse: {
    flexDirection: 'row-reverse',
  },
  rtlText: {
    writingDirection: 'rtl',
  },
});
