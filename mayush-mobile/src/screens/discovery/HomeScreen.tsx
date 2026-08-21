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
const PROMO_BANNER_BG = require('../../../assets/reference-art/home-promo-site-banner.png');
const JOURNAL_BANNER_BG = require('../../../assets/reference-art/home-journal-banner.png');

const RECENTLY_VIEWED_ITEMS = [
  { id: 1, title: 'Salon Moderne', image: require('../../../assets/reference-art/home-moodboard-salon.png') },
  { id: 2, title: 'Chambre Douce', image: require('../../../assets/reference-art/home-moodboard-chambre.png') },
  { id: 3, title: 'Bureau Inspirant', image: require('../../../assets/reference-art/home-moodboard-bureau.png') },
  { id: 4, title: 'Fauteuil Luna', image: require('../../../assets/reference-art/home-new-luna.png') },
];

const LOGGED_IN_RECOMMENDED_PRODUCTS = [
  {
    id: 101,
    name: 'Fauteuil Lounge Élégance',
    photos: [require('../../../assets/reference-art/home-rec-fauteuil-lounge.png')],
    thumbnail_image: require('../../../assets/reference-art/home-rec-fauteuil-lounge.png'),
    main_price: '249,00 MAD',
    has_discount: false,
    rating: 5,
    sales: 28,
  },
  {
    id: 102,
    name: 'Table à manger Moderne',
    photos: [require('../../../assets/reference-art/home-rec-table-manger.png')],
    thumbnail_image: require('../../../assets/reference-art/home-rec-table-manger.png'),
    main_price: '599,00 MAD',
    has_discount: false,
    rating: 5,
    sales: 19,
  },
  {
    id: 103,
    name: 'Suspension Nordique',
    photos: [require('../../../assets/reference-art/home-rec-suspension-nordique.png')],
    thumbnail_image: require('../../../assets/reference-art/home-rec-suspension-nordique.png'),
    main_price: '89,00 MAD',
    has_discount: false,
    rating: 4,
    sales: 42,
  },
  {
    id: 104,
    name: 'Étagère Design Maya',
    photos: [require('../../../assets/reference-art/home-rec-etagere-maya.png')],
    thumbnail_image: require('../../../assets/reference-art/home-rec-etagere-maya.png'),
    main_price: '189,00 MAD',
    has_discount: false,
    rating: 5,
    sales: 31,
  },
];

const GUEST_RECOMMENDED_PRODUCTS = [
  {
    id: 501,
    name: 'Miroir organique Aura',
    photos: [require('../../../assets/reference-art/home-rec-miroir-aura.png')],
    thumbnail_image: require('../../../assets/reference-art/home-rec-miroir-aura.png'),
    main_price: '249,00 MAD',
    has_discount: false,
    rating: 5,
    sales: 18,
  },
  {
    id: 502,
    name: 'Tabouret Moka',
    photos: [require('../../../assets/reference-art/home-rec-tabouret-moka.png')],
    thumbnail_image: require('../../../assets/reference-art/home-rec-tabouret-moka.png'),
    main_price: '129,00 MAD',
    has_discount: false,
    rating: 5,
    sales: 24,
  },
  {
    id: 503,
    name: 'Lampe de table Kumo',
    photos: [require('../../../assets/reference-art/home-rec-lampe-kumo.png')],
    thumbnail_image: require('../../../assets/reference-art/home-rec-lampe-kumo.png'),
    main_price: '159,00 MAD',
    has_discount: false,
    rating: 5,
    sales: 32,
  },
  {
    id: 504,
    name: 'Tapis Wabi Sable',
    photos: [require('../../../assets/reference-art/home-rec-tapis-sable.png')],
    thumbnail_image: require('../../../assets/reference-art/home-rec-tapis-sable.png'),
    main_price: '299,00 MAD',
    has_discount: false,
    rating: 5,
    sales: 15,
  },
  {
    id: 505,
    name: 'Vase Céramique Brume',
    photos: [require('../../../assets/reference-art/home-rec-vase-brume.png')],
    thumbnail_image: require('../../../assets/reference-art/home-rec-vase-brume.png'),
    main_price: '89,00 MAD',
    has_discount: false,
    rating: 5,
    sales: 41,
  },
];

const LOGGED_IN_CATEGORIES_DATA = [
  { id: 'salon', name: 'Salon', nameAr: 'صالون', art: require('../../../assets/reference-art/home-category-salon.png'), icon: 'sofa' as MayushIconName, slug: 'ameublement' },
  { id: 'salle-a-manger', name: 'Salle à manger', nameAr: 'غرفة الطعام', art: require('../../../assets/reference-art/home-category-dining.png'), icon: 'table-chair' as MayushIconName, slug: 'decocuisine' },
  { id: 'chambre', name: 'Chambre', nameAr: 'غرفة النوم', art: require('../../../assets/reference-art/home-category-bedroom.png'), icon: 'bed' as MayushIconName, slug: 'home-office-furniture' },
  { id: 'bureau', name: 'Bureau', nameAr: 'مكتب', art: require('../../../assets/reference-art/home-category-bureau.png'), icon: 'desk' as MayushIconName, slug: 'home-office-furniture' },
  { id: 'eclairage', name: 'Éclairage', nameAr: 'إضاءة', art: require('../../../assets/reference-art/home-category-eclairage.png'), icon: 'lamp' as MayushIconName, slug: 'eclairage' },
  { id: 'decoration', name: 'Décoration', nameAr: 'ديكور', art: require('../../../assets/reference-art/home-category-decoration.png'), icon: 'vase-outline' as MayushIconName, slug: 'accessories' },
  { id: 'rangement', name: 'Rangement', nameAr: 'تخزين', art: require('../../../assets/reference-art/home-category-rangement.png'), icon: 'bookshelf' as MayushIconName, slug: 'ameublement' },
];

const LOGGED_IN_FLASH_DEALS_PRODUCTS = [
  {
    id: 201,
    name: 'Fauteuil Luna',
    photos: [require('../../../assets/reference-art/home-new-luna.png')],
    thumbnail_image: require('../../../assets/reference-art/home-new-luna.png'),
    main_price: '479,00 MAD',
    stroked_price: '599,00 MAD',
    discount: '-20%',
    has_discount: true,
  },
  {
    id: 202,
    name: 'Table basse Ève',
    photos: [require('../../../assets/reference-art/home-new-eve.png')],
    thumbnail_image: require('../../../assets/reference-art/home-new-eve.png'),
    main_price: '399,00 MAD',
    stroked_price: '469,00 MAD',
    discount: '-15%',
    has_discount: true,
  },
  {
    id: 203,
    name: 'Suspension Nori',
    photos: [require('../../../assets/reference-art/home-new-nori.png')],
    thumbnail_image: require('../../../assets/reference-art/home-new-nori.png'),
    main_price: '149,00 MAD',
    stroked_price: '199,00 MAD',
    discount: '-25%',
    has_discount: true,
  },
  {
    id: 204,
    name: 'Chaise Velours',
    photos: [require('../../../assets/reference-art/home-best-elegance.png')],
    thumbnail_image: require('../../../assets/reference-art/home-best-elegance.png'),
    main_price: '159,00 MAD',
    stroked_price: '199,00 MAD',
    discount: '-20%',
    has_discount: true,
  },
];

const LOGGED_IN_NEW_ARRIVALS = [
  {
    id: 301,
    name: 'Fauteuil Luna',
    photos: [require('../../../assets/reference-art/home-new-luna.png')],
    thumbnail_image: require('../../../assets/reference-art/home-new-luna.png'),
    main_price: '589,00 MAD',
    has_discount: false,
  },
  {
    id: 302,
    name: 'Buffet Kyoto',
    photos: [require('../../../assets/reference-art/home-new-kyoto.png')],
    thumbnail_image: require('../../../assets/reference-art/home-new-kyoto.png'),
    main_price: '1 249,00 MAD',
    has_discount: false,
  },
  {
    id: 303,
    name: 'Table basse Ève',
    photos: [require('../../../assets/reference-art/home-new-eve.png')],
    thumbnail_image: require('../../../assets/reference-art/home-new-eve.png'),
    main_price: '479,00 MAD',
    has_discount: false,
  },
  {
    id: 304,
    name: 'Suspension Nori',
    photos: [require('../../../assets/reference-art/home-new-nori.png')],
    thumbnail_image: require('../../../assets/reference-art/home-new-nori.png'),
    main_price: '199,00 MAD',
    has_discount: false,
  },
];

const LOGGED_IN_BEST_SELLERS = [
  {
    id: 401,
    name: 'Canapé modulable Solis',
    photos: [require('../../../assets/reference-art/home-best-solis.png')],
    thumbnail_image: require('../../../assets/reference-art/home-best-solis.png'),
    main_price: '1 890,00 MAD',
    has_discount: false,
    rating: 5,
    sales: 128,
  },
  {
    id: 402,
    name: 'Table à manger Aria',
    photos: [require('../../../assets/reference-art/home-best-aria.png')],
    thumbnail_image: require('../../../assets/reference-art/home-best-aria.png'),
    main_price: '1 390,00 MAD',
    has_discount: false,
    rating: 5,
    sales: 96,
  },
  {
    id: 403,
    name: 'Chaise Velours Élégance',
    photos: [require('../../../assets/reference-art/home-best-elegance.png')],
    thumbnail_image: require('../../../assets/reference-art/home-best-elegance.png'),
    main_price: '189,00 MAD',
    has_discount: false,
    rating: 5,
    sales: 75,
  },
  {
    id: 404,
    name: 'Étagère Linea',
    photos: [require('../../../assets/reference-art/home-best-linea.png')],
    thumbnail_image: require('../../../assets/reference-art/home-best-linea.png'),
    main_price: '329,00 MAD',
    has_discount: false,
    rating: 5,
    sales: 64,
  },
];

const COLLECTIONS_VEDETTES_DATA = [
  { id: 'epure', name: 'Collection Épure', description: 'Lignes douces &\nmatériaux nobles', image: require('../../../assets/reference-art/home-collection-epure.png') },
  { id: 'nomade', name: 'Collection Nomade', description: 'Influences ethniques\net artisanales', image: require('../../../assets/reference-art/home-collection-nomade.png') },
  { id: 'atelier', name: 'Collection Atelier', description: 'Esprit industriel\net authentique', image: require('../../../assets/reference-art/home-collection-atelier.png') },
  { id: 'velours', name: 'Collection Velours', description: 'Touches luxueuses\net confort absolu', image: require('../../../assets/reference-art/home-collection-velours.png') },
];

const PARTNERS_DATA = [
  { id: 'hk', name: 'HK', subtitle: 'LIVING' },
  { id: 'ferm', name: 'ferm', subtitle: 'LIVING' },
  { id: 'norr', name: 'NORR11', subtitle: '' },
  { id: 'tradition', name: '&Tradition', subtitle: 'COPENHAGEN' },
  { id: 'maisons', name: 'Maisons', subtitle: 'du Monde' },
];

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
  onOpenArticles?: () => void;
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
  onOpenArticles,
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

    return () => {
      mounted = false;
      if (contentLoadToken) systemRuntimeState.clear(contentLoadToken);
    };
  }, [language]);

  const selectHeroSlide = (index: number) => {
    setActiveHeroIndex(index);
    heroPagerRef.current?.scrollTo({ x: index * contentWidth, animated: true });
  };

  const displayCartCount = cartBadgeCount ?? 0;

  const displayCollections = collections.length > 0
    ? collections.map((col) => ({
        id: String(col.id),
        name: col.name || '',
        description: col.description || '',
        image: col.hero_image ? { uri: normalizeImageUrl(col.hero_image) } : COLLECTION_FALLBACK_IMAGE,
      }))
    : COLLECTIONS_VEDETTES_DATA;

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

  const heroCopy = [
    { title1: "L'art d'habiter", title2: 'selon vos envies', subtitle: 'Mobilier & décoration haut de gamme sélectionnés avec passion.', button: 'Découvrir la collection' },
    { title1: 'Élégance & Confort', title2: 'pour votre intérieur', subtitle: 'Des créations exclusives pensées pour sublimer vos espaces.', button: 'Explorer les produits' },
  ];
  const heroSlides = sliders.slice(0, 3).map((slider, index) => ({
    ...heroCopy[index % heroCopy.length],
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
  // LOGGED-IN HOME VIEW (Matches Logged-home.png)
  // ==========================================
  if (isAuthenticated) {
    return (
      <View style={styles.container}>
        <ScrollView showsVerticalScrollIndicator={false} contentContainerStyle={[styles.content, { paddingHorizontal: contentPadding }]}>
          {/* 1. Header: Logo + Notification Bell (3) + Cart (2) */}
          <View style={[styles.loggedInHeader, isRTL && styles.rowReverse]}>
            <MayushLogo width={logoWidth} height={Math.round(logoWidth * 0.288)} />
            <View style={[styles.headerActionsCluster, isRTL && styles.rowReverse]}>
              <TouchableOpacity accessibilityRole="button" accessibilityLabel={heading('Notifications', 'الإشعارات')} style={styles.headerIconButton}>
                <MayushIcon name="bell" size={24} color={colors.brand.navy900} />
                <View style={styles.headerCartBadge}>
                  <MayushText variant="caption" color={colors.surface.white} style={styles.badgeText}>
                    3
                  </MayushText>
                </View>
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
            <Image source={LOGGED_IN_HERO_IMAGE} resizeMode="cover" style={styles.heroImage} />
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
          <SectionHeader label={heading('Recommandé pour vous', 'موصى به لك')} action={heading('Voir tout', 'عرض الكل')} isRTL={isRTL} onPress={() => onNavigateTab?.('categories')} />
          <ProductRail
            products={LOGGED_IN_RECOMMENDED_PRODUCTS as any}
            cardWidth={productWidth}
            onSelect={onSelectProduct}
            wishlistedProductIds={wishlistedProductIds}
            onToggleWishlist={onToggleWishlist}
          />

          {/* 6. Consultés récemment Section */}
          <SectionHeader label={heading('Consultés récemment', 'شوهدت مؤخراً')} action={heading('Voir tout', 'عرض الكل')} isRTL={isRTL} onPress={onOpenRecentlyViewed ?? (() => onNavigateTab?.('categories'))} />
          <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.recentlyViewedRail}>
            {RECENTLY_VIEWED_ITEMS.map((item) => (
              <TouchableOpacity key={item.id} activeOpacity={0.84} style={styles.recentlyViewedCard} onPress={onOpenRecentlyViewed ?? (() => onNavigateTab?.('categories'))}>
                <Image source={item.image} style={styles.recentlyViewedImage} resizeMode="cover" />
                <View style={styles.recentlyViewedWishlistBtn}>
                  <MayushIcon name="heart" size={14} color={colors.brand.navy900} />
                </View>
                <View style={styles.recentlyViewedEyeBadge}>
                  <MayushIcon name="eye" size={14} color={colors.brand.navy900} />
                </View>
                <View style={styles.recentlyViewedTitlePill}>
                  <MayushText variant="caption" color={colors.surface.white} numberOfLines={1} style={styles.recentlyViewedTitleText}>
                    {item.title}
                  </MayushText>
                </View>
              </TouchableOpacity>
            ))}
          </ScrollView>

          {/* 7. Catégories Section (8 Circular Categories + Voir tout) */}
          <SectionHeader label={heading('Catégories', 'الأقسام')} action={heading('Voir tout', 'عرض الكل')} isRTL={isRTL} onPress={() => onNavigateTab?.('categories')} />
          <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.loggedInCategoriesRail}>
            {LOGGED_IN_CATEGORIES_DATA.map((cat) => (
              <TouchableOpacity
                key={cat.id}
                activeOpacity={0.82}
                style={styles.loggedInCategoryItem}
                onPress={() => {
                  const matched = categories.find((c) => c.slug?.toLowerCase() === cat.slug);
                  if (matched) onSelectCategory?.(matched);
                  else onNavigateTab?.('categories');
                }}
              >
                <View style={styles.loggedInCategoryCircle}>
                  <Image source={cat.art} style={styles.loggedInCategoryArt} resizeMode="cover" />
                </View>
                <MayushText variant="caption" color={colors.brand.navy900} align="center" style={styles.loggedInCategoryLabel} numberOfLines={1}>
                  {isRTL ? cat.nameAr : cat.name}
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

          {/* 8. Flash Deal Section (With live countdown timer) */}
          <View style={[styles.flashDealHeaderRow, isRTL && styles.rowReverse]}>
            <View style={[styles.flashDealTitleGroup, isRTL && styles.rowReverse]}>
              <MayushIcon name="zap" size={20} color={colors.brand.orange500} />
              <MayushText variant="sectionTitle" color={colors.brand.navy900} style={styles.flashDealTitleText}>
                {heading('Flash Deal', 'عروض سريعة')}
              </MayushText>
            </View>
            <View style={[styles.flashDealRightGroup, isRTL && styles.rowReverse]}>
              <View style={styles.countdownBadge}>
                <MayushText variant="caption" color={colors.brand.orange500} style={styles.countdownText}>
                  {heading('Fin dans 12h : 45m : 30s', 'ينتهي خلال 12س : 45د : 30ث')}
                </MayushText>
              </View>
              <TouchableOpacity accessibilityRole="button" accessibilityLabel={heading('Voir tout', 'عرض الكل')} onPress={onOpenPromotions} activeOpacity={0.78}>
                <MayushText variant="smallBody" color={colors.brand.orange500} style={styles.actionLabel}>
                  {heading('Voir tout >', 'عرض الكل >')}
                </MayushText>
              </TouchableOpacity>
            </View>
          </View>
          <ProductRail
            products={LOGGED_IN_FLASH_DEALS_PRODUCTS as any}
            cardWidth={productWidth}
            onSelect={onSelectProduct}
            wishlistedProductIds={wishlistedProductIds}
            onToggleWishlist={onToggleWishlist}
          />

          {/* 9. Middle Promo Banner ("Offre spéciale -15% sur tout le site") */}
          <View style={styles.middlePromoBannerWrapper}>
            <Image source={PROMO_BANNER_BG} resizeMode="cover" style={styles.middlePromoBannerImage} />
            <View style={styles.middlePromoOverlay} />
            <View style={[styles.middlePromoContent, isRTL && styles.middlePromoContentRtl]}>
              <MayushText variant="caption" color={colors.surface.white} style={styles.middlePromoPreTitle}>
                {heading('Offre spéciale', 'عرض خاص')}
              </MayushText>
              <MayushText variant="display" color={colors.surface.white} style={styles.middlePromoTitle}>
                {heading('-15% sur tout le site', '15%- على كامل الموقع')}
              </MayushText>
              <MayushText variant="smallBody" color={colors.surface.white} style={styles.middlePromoSubtitle}>
                {heading('Des pièces uniques pour sublimer votre intérieur.', 'قطع فريدة لترقية ديكور منزلك.')}
              </MayushText>
              <TouchableOpacity activeOpacity={0.84} style={styles.middlePromoCtaButton} onPress={onOpenPromotions}>
                <MayushText variant="smallBody" color={colors.surface.white} style={styles.middlePromoCtaText}>
                  {heading('Profitez-en maintenant', 'استفد الآن')}
                </MayushText>
              </TouchableOpacity>
              <MayushText variant="caption" color={colors.surface.white} style={styles.middlePromoExpiry}>
                {heading("Jusqu'au 31 mai 2024", 'حتى 31 مايو 2024')}
              </MayushText>
            </View>
          </View>

          {/* 10. Nouveautés Section */}
          <SectionHeader label={heading('Nouveautés', 'وصل حديثاً')} action={heading('Voir tout', 'عرض الكل')} isRTL={isRTL} onPress={onOpenNewArrivals ?? onOpenPromotions ?? (() => onNavigateTab?.('categories'))} />
          <ProductRail
            products={newArrivals.length > 0 ? newArrivals : LOGGED_IN_NEW_ARRIVALS as any}
            cardWidth={productWidth}
            onSelect={onSelectProduct}
            wishlistedProductIds={wishlistedProductIds}
            onToggleWishlist={onToggleWishlist}
          />

          {/* 11. Meilleures ventes Section */}
          <SectionHeader label={heading('Meilleures ventes', 'الأكثر مبيعاً')} action={heading('Voir tout', 'عرض الكل')} isRTL={isRTL} onPress={onOpenBestSellers ?? (() => onNavigateTab?.('categories'))} />
          <ProductRail
            products={bestSellers.length > 0 ? bestSellers : LOGGED_IN_BEST_SELLERS as any}
            cardWidth={productWidth}
            onSelect={onSelectProduct}
            wishlistedProductIds={wishlistedProductIds}
            onToggleWishlist={onToggleWishlist}
          />

          {/* 12. Inspiration du moment Section */}
          <SectionHeader label={heading('Inspiration du moment', 'إلهام اليوم')} action={heading('Voir tout', 'عرض الكل')} isRTL={isRTL} onPress={onOpenInspiration ?? onOpenWishlist ?? (() => onNavigateTab?.('categories'))} />
          <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.inspirationRail}>
            <InspirationCard source={INSPIRATION_ARTWORK[0]} width={Math.round(contentWidth * 0.72)} onPress={onOpenInspiration ?? onOpenWishlist ?? (() => onNavigateTab?.('categories'))} />
            <InspirationCard source={INSPIRATION_ARTWORK[1]} width={Math.round(contentWidth * 0.72)} onPress={onOpenInspiration ?? onOpenWishlist ?? (() => onNavigateTab?.('categories'))} />
          </ScrollView>

          {/* 13. Collections vedettes Section */}
          <SectionHeader label={heading('Collections vedettes', 'التشكيلات المميزة')} action={heading('Voir tout', 'عرض الكل')} isRTL={isRTL} onPress={() => onNavigateTab?.('categories')} />
          <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.collectionsRow}>
            {displayCollections.map((col) => (
              <TouchableOpacity key={col.id} activeOpacity={0.84} style={styles.collectionItem} onPress={() => onNavigateTab?.('categories')}>
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
            <TouchableOpacity activeOpacity={0.84} style={styles.collectionItem} onPress={() => onNavigateTab?.('categories')}>
              <View style={styles.collectionMoreCircle}>
                <MayushIcon name="more-horizontal" size={24} color={colors.brand.navy900} />
              </View>
              <MayushText variant="caption" color={colors.brand.navy900} align="center" style={styles.collectionTitle}>
                {heading('Voir toutes les\ncollections', 'عرض كل التشكيلات')}
              </MayushText>
            </TouchableOpacity>
          </ScrollView>

          {/* 14. Nos sélections partenaires Section */}
          <View style={styles.partnersSection}>
            <SectionHeader label={heading('Nos sélections partenaires', 'شركاؤنا المختارون')} action={heading('Voir tout', 'عرض الكل')} isRTL={isRTL} onPress={() => onNavigateTab?.('categories')} />
            <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.partnersRow}>
              {PARTNERS_DATA.map((p) => (
                <View key={p.id} style={styles.partnerCard}>
                  <MayushText variant="strongBody" color={colors.brand.navy900} align="center" style={styles.partnerBrandName}>
                    {p.name}
                  </MayushText>
                  {p.subtitle ? (
                    <MayushText variant="caption" color={colors.neutral.gray700} align="center" style={styles.partnerBrandSub}>
                      {p.subtitle}
                    </MayushText>
                  ) : null}
                </View>
              ))}
            </ScrollView>
          </View>

          {/* 15. Pièces par ambiance Section */}
          <SectionHeader label={heading('Pièces par ambiance', 'غرف حسب الطراز')} action={heading('Voir tout', 'عرض الكل')} isRTL={isRTL} onPress={() => onNavigateTab?.('categories')} />
          <View style={styles.ambianceGrid}>
            {AMBIANCES_DATA.map((amb) => (
              <TouchableOpacity key={amb.id} activeOpacity={0.86} style={styles.ambianceCard} onPress={() => onNavigateTab?.('categories')}>
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
        <TouchableOpacity activeOpacity={0.88} onPress={() => onNavigateTab?.('categories')} style={[styles.searchBar, isRTL && styles.rowReverse]}>
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
        <SectionHeader label={heading('Nouveautés', 'وصول جديد')} action={heading('Voir tout', 'عرض الكل')} isRTL={isRTL} onPress={onOpenNewArrivals ?? onOpenPromotions ?? (() => onNavigateTab?.('categories'))} />
        {newArrivalsLoading ? (
          <ProductRailSkeleton cardWidth={productWidth} count={3} />
        ) : (
          <ProductRail
            products={newArrivals.length > 0 ? newArrivals : LOGGED_IN_NEW_ARRIVALS as any}
            cardWidth={productWidth}
            onSelect={onSelectProduct}
            wishlistedProductIds={wishlistedProductIds}
            onToggleWishlist={onToggleWishlist}
          />
        )}

        {/* 6. Meilleures ventes Section */}
        <SectionHeader label={heading('Meilleures ventes', 'الأكثر مبيعاً')} action={heading('Voir tout', 'عرض الكل')} isRTL={isRTL} onPress={onOpenBestSellers ?? (() => onNavigateTab?.('categories'))} />
        {bestSellersLoading ? (
          <ProductRailSkeleton cardWidth={productWidth} count={3} />
        ) : (
          <ProductRail
            products={bestSellers.length > 0 ? bestSellers : LOGGED_IN_BEST_SELLERS as any}
            cardWidth={productWidth}
            onSelect={onSelectProduct}
            wishlistedProductIds={wishlistedProductIds}
            onToggleWishlist={onToggleWishlist}
          />
        )}

        {/* 7. Offres du moment Banner */}
        <View style={[styles.offerBanner, isRTL && styles.rowReverse]}>
          <View style={styles.offerIcon}>
            <MayushIcon name="tag" size={22} color={colors.brand.orange500} />
          </View>
          <View style={styles.offerCopy}>
            <MayushText variant="sectionTitle" color={colors.brand.navy900} style={styles.offerTitle}>
              {heading('Offres du moment', 'عروض اللحظة')}
            </MayushText>
            <MayushText variant="smallBody" color={colors.neutral.gray700}>
              {heading('Jusqu’à -20% sur une sélection de pièces d’exception.', 'حتى 20% على منتجات مختارة.')}
            </MayushText>
          </View>
          <TouchableOpacity accessibilityRole="button" accessibilityLabel={heading('Profiter des offres', 'استفد من العروض')} onPress={onOpenPromotions} style={styles.offerButton}>
            <MayushText variant="smallBody" color={colors.brand.orange500} style={styles.offerButtonLabel}>
              {heading('En profiter', 'استفد')}
            </MayushText>
          </TouchableOpacity>
        </View>

        {/* 8. Inspiration du moment Section */}
        <SectionHeader label={heading('Inspiration du moment', 'إلهام اليوم')} action={heading('Voir tout', 'عرض الكل')} isRTL={isRTL} onPress={onOpenInspiration ?? onOpenWishlist ?? (() => onNavigateTab?.('categories'))} />
        <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.inspirationRail}>
          <InspirationCard source={INSPIRATION_ARTWORK[0]} width={Math.round(contentWidth * 0.72)} onPress={onOpenInspiration ?? onOpenWishlist ?? (() => onNavigateTab?.('categories'))} />
          <InspirationCard source={INSPIRATION_ARTWORK[1]} width={Math.round(contentWidth * 0.72)} onPress={onOpenInspiration ?? onOpenWishlist ?? (() => onNavigateTab?.('categories'))} />
        </ScrollView>

        {/* 9. Collections vedettes Section */}
        <SectionHeader label={heading('Collections vedettes', 'التشكيلات المميزة')} action={heading('Voir toutes les collections', 'عرض كل التشكيلات')} isRTL={isRTL} onPress={() => onNavigateTab?.('categories')} />
        <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.collectionsRow}>
          {displayCollections.map((col) => (
            <TouchableOpacity key={col.id} activeOpacity={0.84} style={styles.collectionItem} onPress={() => onNavigateTab?.('categories')}>
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
          <TouchableOpacity activeOpacity={0.84} style={styles.collectionItem} onPress={() => onNavigateTab?.('categories')}>
            <View style={styles.collectionMoreCircle}>
              <MayushIcon name="more-horizontal" size={24} color={colors.brand.navy900} />
            </View>
            <MayushText variant="caption" color={colors.brand.navy900} align="center" style={styles.collectionTitle}>
              {heading('Voir toutes les\ncollections', 'عرض كل التشكيلات')}
            </MayushText>
          </TouchableOpacity>
        </ScrollView>

        {/* 10. Nos sélections partenaires Section */}
        <View style={styles.partnersSection}>
          <SectionHeader label={heading('Nos sélections partenaires', 'شركاؤنا المختارون')} action={heading('Voir tout', 'عرض الكل')} isRTL={isRTL} onPress={() => onNavigateTab?.('categories')} />
          <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.partnersRow}>
            {PARTNERS_DATA.map((p) => (
              <View key={p.id} style={styles.partnerCard}>
                <MayushText variant="strongBody" color={colors.brand.navy900} align="center" style={styles.partnerBrandName}>
                  {p.name}
                </MayushText>
                {p.subtitle ? (
                  <MayushText variant="caption" color={colors.neutral.gray700} align="center" style={styles.partnerBrandSub}>
                    {p.subtitle}
                  </MayushText>
                ) : null}
              </View>
            ))}
          </ScrollView>
        </View>

        {/* 11. Recommandé pour vous Section */}
        <SectionHeader label={heading('Recommandé pour vous', 'موصى به لك')} action={heading('Voir tout', 'عرض الكل')} isRTL={isRTL} onPress={() => onNavigateTab?.('categories')} />
        <ProductRail
          products={GUEST_RECOMMENDED_PRODUCTS as any}
          cardWidth={productWidth}
          onSelect={onSelectProduct}
          wishlistedProductIds={wishlistedProductIds}
          onToggleWishlist={onToggleWishlist}
        />

        {/* 12. Pièces par ambiance Section */}
        <SectionHeader label={heading('Pièces par ambiance', 'غرف حسب الطراز')} action={heading('Voir tout', 'عرض الكل')} isRTL={isRTL} onPress={() => onNavigateTab?.('categories')} />
        <View style={styles.ambianceGrid}>
          {AMBIANCES_DATA.map((amb) => (
            <TouchableOpacity key={amb.id} activeOpacity={0.86} style={styles.ambianceCard} onPress={() => onNavigateTab?.('categories')}>
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
    ...StyleSheet.absoluteFillObject,
    width: '100%',
    height: '100%',
  },
  heroOverlayDarkener: {
    ...StyleSheet.absoluteFillObject,
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
    ...StyleSheet.absoluteFillObject,
    width: '100%',
    height: '100%',
  },
  middlePromoOverlay: {
    ...StyleSheet.absoluteFillObject,
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
