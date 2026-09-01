import React, { useEffect, useMemo, useRef, useState } from 'react';
import { Image, ImageSourcePropType, Linking, ScrollView, StyleSheet, TouchableOpacity, useWindowDimensions, View } from 'react-native';
import { CategoryDto, ProductCollectionDto, ProductMiniDto } from '../../contracts/api/dto';
import { normalizeImageUrl } from '../../contracts/mappers/imageNormalizer';
import { MayushLogo } from '../../design-system/components/brand/MayushLogo';
import { ProductCard } from '../../design-system/components/commerce/ProductCard';
import { TabKey } from '../../design-system/components/navigation/BottomTabBar';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
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
import { inspirationService, InspirationPreview } from '../../services/api/inspirationService';
import { wishlistState } from '../../commerce/wishlistState';
import { recentlyViewedState } from '../../commerce/recentlyViewedState';

import { getHomeDisplayCategories, CategoryDisplayInfo } from '../../presentation/catalog/categoryPresentation';

const HOME_PRODUCT_RAIL_GAP = 8;

const LOGGED_IN_HERO_IMAGE = require('../../../assets/reference-art/home-hero-scene.png');
const DEFAULT_USER_AVATAR = require('../../../assets/reference-art/home-user-avatar-default.png');
const PROMO_BANNER_IMAGE = require('../../../assets/reference-art/home-promo-banner-moroccan.jpg');

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
    id: 'rev-1',
    author: 'Sophie L.',
    date: '18 mai 2024',
    rating: 5,
    quoteFr: 'Produits magnifiques et service client au top. Livraison soignée et rapide !',
    quoteAr: 'منتجات رائعة وخدمة عملاء ممتازة. التوصيل سريع ومتقن!',
    verified: true,
  },
  {
    id: 'rev-2',
    author: 'Thomas D.',
    date: '12 mai 2024',
    rating: 5,
    quoteFr: 'Qualité exceptionnelle et design soigné. Je recommande vivement Mayush Design.',
    quoteAr: 'جودة استثنائية وتصميم متقن. أوصي بشدة بـ Mayush Design.',
    verified: true,
  },
  {
    id: 'rev-3',
    author: 'Camille R.',
    date: '5 mai 2024',
    rating: 5,
    quoteFr: 'Très belle expérience d\'achat, tout était parfait du début à la fin.',
    quoteAr: 'تجربة تسوق رائعة جداً، كل شيء كان مثالياً من البداية حتى النهاية.',
    verified: true,
  },
];

const MOROCCO_SERVICE_TRUST_PILLARS = [
  { id: 'delivery', icon: 'truck', titleFr: 'Livraison rapide', subFr: 'Partout au Maroc', titleAr: 'توصيل سريع', subAr: 'لكل مدن المغرب' },
  { id: 'payment', icon: 'lock', titleFr: 'Paiement sécurisé', subFr: '100% sécurisé', titleAr: 'دفع آمن', subAr: '100% عند الاستلام' },
  { id: 'returns', icon: 'refresh-cw', titleFr: 'Retours faciles', subFr: 'Sous 14 jours', titleAr: 'إرجاع سهل', subAr: 'خلال 14 يومًا' },
  { id: 'support', icon: 'headphones', titleFr: 'Service client', subFr: '7j/7 à votre écoute', titleAr: 'خدمة العملاء', subAr: '7/7 في خدمتكم' },
];

const DEFAULT_NEW_CATEGORY_CHIPS = [
  { slug: 'eclairage', nameFr: 'Éclairage', nameAr: 'إضاءة' },
  { slug: 'salon', nameFr: 'Salon', nameAr: 'صالون' },
  { slug: 'chambre', nameFr: 'Chambre', nameAr: 'غرفة نوم' },
  { slug: 'decoration', nameFr: 'Décoration', nameAr: 'ديكور' },
  { slug: 'rangement', nameFr: 'Rangement', nameAr: 'تخزين' },
  { slug: 'bureau', nameFr: 'Bureau', nameAr: 'مكتب' },
];

const FOOTER_TRUST_BADGES = [
  { id: 1, icon: 'tag', label: 'Marques & créateurs\nsélectionnés' },
  { id: 2, icon: 'grid', label: 'Pièces exclusives\net intemporelles' },
  { id: 3, icon: 'shield-check', label: 'Design responsable\n& durable' },
  { id: 4, icon: 'compass', label: 'SAV réactif\nà votre écoute' },
  { id: 5, icon: 'truck', label: 'Entreprise française\nà taille humaine' },
];

function parseHeroTitle(rawTitleHtml?: string | null, fallbackMain = '', fallbackAccent = '') {
  if (!rawTitleHtml || !rawTitleHtml.trim()) {
    return { title1: fallbackMain, title2: fallbackAccent };
  }

  const decoded = rawTitleHtml
    .replace(/&nbsp;/g, ' ')
    .replace(/&#039;/g, "'")
    .replace(/&quot;/g, '"')
    .replace(/&amp;/g, '&');

  const spanMatches = Array.from(decoded.matchAll(/<span[^>]*>(.*?)<\/span>/gi))
    .map((m) => m[1].replace(/<[^>]+>/g, '').trim())
    .filter(Boolean);

  if (spanMatches.length >= 2) {
    return {
      title1: spanMatches[0],
      title2: spanMatches.slice(1).join(' '),
    };
  }

  const plainText = decoded.replace(/<[^>]+>/g, '').trim();
  const words = plainText.split(' ').filter(Boolean);
  if (words.length > 3) {
    const half = Math.ceil(words.length / 2);
    return {
      title1: words.slice(0, half).join(' '),
      title2: words.slice(half).join(' '),
    };
  }

  return { title1: plainText, title2: '' };
}

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
  featuredInspirations: [] as InspirationPreview[],
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
  onOpenInspiration?: (slug?: string) => void;
  onOpenRecommended?: () => void;
  onOpenCollections?: () => void;
  onSelectCollection?: (collection: ProductCollectionDto) => void;
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
  onSelectCollection,
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
  const productWidth = Math.max(64, Math.floor((contentWidth - HOME_PRODUCT_RAIL_GAP * 3) / 4));
  const logoWidth = 142;
  const heroHeight = 200;
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
  const [newArrivalsLoading, setNewArrivalsLoading] = useState(!hasCachedData);
  const [bestSellersLoading, setBestSellersLoading] = useState(!hasCachedData);
  const [flashDeals, setFlashDeals] = useState<ProductMiniDto[]>(hasCachedData ? homeCache.flashDeals : []);
  const [flashDealEndDate, setFlashDealEndDate] = useState(hasCachedData ? homeCache.flashDealEndDate : '');
  const [recommendedProducts, setRecommendedProducts] = useState<ProductMiniDto[]>(hasCachedData ? homeCache.recommendedProducts : []);
  const [recentlyViewed, setRecentlyViewed] = useState<ProductMiniDto[]>(hasCachedData ? homeCache.recentlyViewed : []);
  const [wishlistRevision, setWishlistRevision] = useState(0);
  const [recentRevision, setRecentRevision] = useState(0);
  const [selectedNewCategorySlug, setSelectedNewCategorySlug] = useState<string>('eclairage');
  const [categoryNewArrivals, setCategoryNewArrivals] = useState<ProductMiniDto[]>([]);
  const [categoryNewArrivalsLoading, setCategoryNewArrivalsLoading] = useState(false);
  const [topBrands, setTopBrands] = useState<BrandDto[]>(hasCachedData ? homeCache.topBrands : []);
  const [promoBanner, setPromoBanner] = useState<{ imageUrl: string; linkUrl: string } | null>(hasCachedData ? homeCache.promoBanner : null);
  const [featuredInspirations, setFeaturedInspirations] = useState<InspirationPreview[]>(hasCachedData ? homeCache.featuredInspirations : []);
  const [inspirationsLoading, setInspirationsLoading] = useState(!hasCachedData);
  const [notificationCount, setNotificationCount] = useState(0);
  const [flashCountdown, setFlashCountdown] = useState('');

  useEffect(() => {
    const unsubWishlist = wishlistState.subscribe(() => {
      setWishlistRevision((r) => r + 1);
    });
    const unsubRecent = recentlyViewedState.subscribe(() => {
      setRecentRevision((r) => r + 1);
    });
    return () => {
      unsubWishlist();
      unsubRecent();
    };
  }, []);

  const wishlistPromotions: ProductMiniDto[] = useMemo(() => {
    const allWishlist = wishlistState.getItems();
    return allWishlist.filter((item) => {
      const hasDiscountFlag = Boolean(item.has_discount);
      const hasDiscountValue = Boolean(item.discount && item.discount !== '0' && item.discount !== '0%');
      const hasPriceDrop = Boolean(item.base_discounted_price && item.base_price && item.base_discounted_price < item.base_price);
      const hasStrokedPrice = Boolean(item.stroked_price && item.main_price && item.stroked_price !== item.main_price);
      const hasOldPrice = Boolean(item.oldPriceMad && item.priceMad && item.oldPriceMad > item.priceMad);
      return hasDiscountFlag || hasDiscountValue || hasPriceDrop || hasStrokedPrice || hasOldPrice;
    });
  }, [wishlistRevision]);

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
        if (completedRequests === 4) {
          systemRuntimeState.complete(contentLoadToken);
        } else {
          systemRuntimeState.update(contentLoadToken, completedRequests / 4);
        }
      }
    };
    const updateCache = () => {
      if (completedRequests === 4) {
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

    // Recently viewed (auth-only)
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

    // Featured inspirations
    setInspirationsLoading(true);
    inspirationService
      .getFeatured(language)
      .then((res) => {
        if (mounted) {
          setFeaturedInspirations(res);
          homeCache.featuredInspirations = res;
          setInspirationsLoading(false);
        }
      })
      .catch(() => { if (mounted) setInspirationsLoading(false); });

    // Collections
    catalogService
      .getProductCollections(language)
      .then((res) => {
        if (mounted && res.length > 0) {
          setCollections(res);
          homeCache.collections = res;
        }
      })
      .catch(() => {});

    return () => {
      mounted = false;
      if (contentLoadToken) systemRuntimeState.clear(contentLoadToken);
    };
  }, [language, isAuthenticated]);

  // Fetch category new arrivals when tab changes
  useEffect(() => {
    let active = true;
    setCategoryNewArrivalsLoading(true);
    catalogService
      .getCategoryProducts(selectedNewCategorySlug, 1, language)
      .then((res) => {
        if (active) {
          const items = res?.data || [];
          if (items.length > 0) {
            setCategoryNewArrivals(items.slice(0, 6));
          } else {
            setCategoryNewArrivals(newArrivals.slice(0, 6));
          }
          setCategoryNewArrivalsLoading(false);
        }
      })
      .catch(() => {
        if (active) {
          setCategoryNewArrivals(newArrivals.slice(0, 6));
          setCategoryNewArrivalsLoading(false);
        }
      });
    return () => { active = false; };
  }, [selectedNewCategorySlug, language, newArrivals]);

  const selectHeroSlide = (index: number) => {
    setActiveHeroIndex(index);
    heroPagerRef.current?.scrollTo({ x: index * contentWidth, animated: true });
  };

  const displayCartCount = cartBadgeCount ?? 0;

  const displayCategories: CategoryDisplayInfo[] = useMemo(() => {
    return getHomeDisplayCategories(categories, isRTL ? 'ar' : 'fr');
  }, [categories, isRTL]);

  const displayCollections = useMemo(() => {
    return collections
      .filter((col) => Boolean(col?.id) && Boolean(col?.name?.trim()))
      .map((col, idx) => {
        const fallbackArt = [
          require('../../../assets/reference-art/home-collection-epure.png'),
          require('../../../assets/reference-art/home-collection-nomade.png'),
          require('../../../assets/reference-art/home-collection-atelier.png'),
          require('../../../assets/reference-art/home-collection-velours.png'),
          require('../../../assets/reference-art/home-inspiration-japandi.png'),
          require('../../../assets/reference-art/home-inspiration-natural.png'),
        ][idx % 6];
        const hasHero = Boolean(col.hero_image && !col.hero_image.includes('placeholder'));
        return {
          id: String(col.id),
          name: col.name.trim(),
          image: hasHero ? { uri: normalizeImageUrl(col.hero_image) } : fallbackArt,
          collection: col,
        };
      });
  }, [collections]);

  const recentlyViewedScenes = useMemo(() => {
    const items = recentlyViewedState.getItems();
    if (items && items.length > 0) {
      return items.slice(0, 4).map((p) => ({
        id: String(p.id),
        title: p.name,
        image: { uri: normalizeImageUrl(p.thumbnail_image) },
        product: p,
      }));
    }
    return [];
  }, [recentRevision]);

  const newCategoryChips = useMemo(() => {
    if (categories && categories.length > 0) {
      return categories.slice(0, 6).map((c) => ({
        slug: c.slug || String(c.id),
        nameFr: c.name,
        nameAr: c.name,
      }));
    }
    return DEFAULT_NEW_CATEGORY_CHIPS;
  }, [categories]);

  const handleHeroCtaPress = (ctaLink?: string | null) => {
    if (!ctaLink || ctaLink === '#') {
      if (onOpenCollections) onOpenCollections();
      else onNavigateTab?.('categories');
      return;
    }
    const clean = ctaLink.trim().toLowerCase();
    if (clean.includes('collection') || clean.includes('selection-mayush')) {
      if (onOpenCollections) onOpenCollections();
      else onNavigateTab?.('categories');
    } else if (clean.includes('promotions') || clean.includes('deals')) {
      if (onOpenPromotions) onOpenPromotions();
      else onNavigateTab?.('categories');
    } else if (clean.includes('search')) {
      if (onOpenSearch) onOpenSearch();
      else onNavigateTab?.('categories');
    } else {
      if (onOpenCollections) onOpenCollections();
      else onNavigateTab?.('categories');
    }
  };

  const heroSlides = useMemo(() => {
    if (sliders.length === 0) {
      return [
        {
          id: 0,
          title1: heading('Et si votre intérieur', 'ماذا لو كان منزلكم'),
          title2: heading('reflétait qui vous êtes ?', 'يعبر عن شخصيتكم ؟'),
          subtitle: heading(
            'Mobilier et décoration design sélectionnés pour leur esthétique et leur caractère. Livraison nationale, paiement à la livraison.',
            'أثاث وديكور عصري مصمم بعناية ليضفي جمالاً وطابعاً فريداً على مساحتكم. التوصيل لجميع المدن والدفع عند الاستلام.'
          ),
          button: heading('Découvrir nos collections', 'اكتشفوا تشكيلاتنا'),
          ctaLink: '/collections/selection-mayush',
          bg: LOGGED_IN_HERO_IMAGE,
        },
      ];
    }

    return sliders.map((slider, idx) => {
      const parsed = parseHeroTitle(
        slider.title,
        heading('Et si votre intérieur', 'ماذا لو كان منزلكم'),
        heading('reflétait qui vous êtes ?', 'يعبر عن شخصيتكم ؟')
      );
      return {
        id: idx,
        title1: parsed.title1,
        title2: parsed.title2,
        subtitle:
          slider.description ||
          heading(
            'Mobilier et décoration design sélectionnés pour leur esthétique et leur caractère. Livraison nationale, paiement à la livraison.',
            'أثاث وديكور عصري مصمم بعناية ليضفي جمالاً وطابعاً فريداً على مساحتكم. التوصيل لجميع المدن والدفع عند الاستلام.'
          ),
        button: slider.cta_text || heading('Découvrir nos collections', 'اكتشفوا تشكيلاتنا'),
        ctaLink: slider.cta_link || slider.url || '/collections/selection-mayush',
        bg: slider.photo ? { uri: normalizeImageUrl(slider.photo) } : LOGGED_IN_HERO_IMAGE,
      };
    });
  }, [sliders, heading]);

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
    : '';
  const userAvatarSource = authenticatedUser?.avatarUrl
    ? { uri: normalizeImageUrl(authenticatedUser.avatarUrl) }
    : DEFAULT_USER_AVATAR;

  const activeOrder = orders && orders.length > 0 ? orders[0] : null;

  // Dynamic Inspiration Section Renderer (live API data or hidden when empty)
  const renderInspirationsSection = () => {
    if (inspirationsLoading || featuredInspirations.length === 0) return null;

    return (
      <View style={styles.inspirationsSection}>
        <SectionHeader
          label={heading('Inspiration du moment', 'إلهام اليوم')}
          action={heading('Voir tout', 'عرض الكل')}
          isRTL={isRTL}
          onPress={() => onOpenInspiration?.()}
        />
        <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.inspirationRail}>
          {featuredInspirations.map((item) => (
            <InspirationCard
              key={`insp-${item.id}`}
              source={{ uri: normalizeImageUrl(item.image) }}
              title={item.title}
              subtitle={item.subtitle}
              width={Math.round(contentWidth * 0.72)}
              onPress={() => onOpenInspiration?.(item.slug)}
            />
          ))}
        </ScrollView>
      </View>
    );
  };

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

      {/* Ils nous font confiance (Customer Reviews) */}
      <View style={styles.reviewsSection}>
        <SectionHeader
          label={heading('Ils nous font confiance', 'ما يقوله عملاؤنا')}
          action={heading('Voir tous les avis', 'عرض كل التقييمات')}
          isRTL={isRTL}
          onPress={() => Linking.openURL('https://mayushdesign.com')}
        />
        <View style={[styles.reviewsScoreAndRail, isRTL && styles.rowReverse]}>
          <View style={styles.reviewScoreBadge}>
            <MayushText variant="display" color={colors.brand.navy900} style={styles.reviewScoreNumber}>
              4,8<MayushText variant="pageTitle" color={colors.neutral.gray500}>/5</MayushText>
            </MayushText>
            <View style={styles.reviewStarsRow}>
              {[1, 2, 3, 4, 5].map((star) => (
                <MayushIcon key={star} name="star" size={13} color="#F59E0B" style={styles.starIcon} />
              ))}
            </View>
            <MayushText variant="caption" color={colors.neutral.gray700} style={styles.reviewCountText}>
              {heading('Basé sur 842 avis', 'بناءً على 842 تقييماً')}
            </MayushText>
          </View>
          <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.reviewsCardsRail}>
            {CUSTOMER_REVIEWS_DATA.map((rev) => (
              <View key={rev.id} style={styles.reviewCard}>
                <View style={[styles.reviewCardTop, isRTL && styles.rowReverse]}>
                  <View style={styles.reviewStarsRow}>
                    {[1, 2, 3, 4, 5].map((star) => (
                      <MayushIcon key={star} name="star" size={11} color="#F59E0B" style={styles.starIcon} />
                    ))}
                  </View>
                  <MayushText variant="caption" color={colors.neutral.gray500}>
                    {rev.date}
                  </MayushText>
                </View>
                <MayushText variant="smallBody" color={colors.neutral.gray900} numberOfLines={3} style={styles.reviewQuoteText}>
                  {heading(rev.quoteFr, rev.quoteAr)}
                </MayushText>
                <MayushText variant="strongBody" color={colors.brand.navy900} style={styles.reviewAuthorName}>
                  {rev.author}
                </MayushText>
              </View>
            ))}
          </ScrollView>
        </View>
      </View>

      {/* Le Journal Mayush (Article Card Banner) */}
      <View style={styles.journalBannerWrapper}>
        <Image source={require('../../../assets/reference-art/home-journal-scene.png')} style={styles.journalBgImage} resizeMode="cover" />
        <View style={styles.journalOverlayDarkener} />
        <View style={[styles.journalContentOverlay, isRTL && styles.journalContentOverlayRtl]}>
          <MayushText variant="display" color={colors.surface.white} style={styles.journalTitle}>
            {heading('Le Journal Mayush', 'مجلة ميوش')}
          </MayushText>
          <MayushText variant="smallBody" color="rgba(255, 255, 255, 0.92)" style={[styles.journalSubtitle, isRTL && styles.textRtl]}>
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

      {/* Morocco 4-Pillar Service Trust Footer Strip */}
      <View style={[styles.moroccoTrustStrip, isRTL && styles.rowReverse]}>
        {MOROCCO_SERVICE_TRUST_PILLARS.map((pillar) => (
          <View key={pillar.id} style={styles.moroccoTrustItem}>
            <View style={styles.moroccoTrustIconWrap}>
              <MayushIcon name={pillar.icon as any} size={20} color={colors.brand.orange500} />
            </View>
            <MayushText variant="strongBody" color={colors.brand.navy900} align="center" style={styles.moroccoTrustTitle}>
              {heading(pillar.titleFr, pillar.titleAr)}
            </MayushText>
            <MayushText variant="caption" color={colors.neutral.gray500} align="center" style={styles.moroccoTrustSub}>
              {heading(pillar.subFr, pillar.subAr)}
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
          {/* 1. Header: Logo + Notification Bell (3) + Cart (2) (No search bar below in design) */}
          <View style={[styles.loggedInHeader, isRTL && styles.rowReverse]}>
            <MayushLogo width={logoWidth} height={Math.round(logoWidth * 0.288)} />
            <View style={[styles.headerActionsCluster, isRTL && styles.rowReverse]}>
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
                {userFirstName
                  ? heading(`Bonjour ${userFirstName} 👋`, `مرحباً ${userFirstName} 👋`)
                  : heading('Bonjour 👋', 'مرحباً 👋')
                }
              </MayushText>
              <MayushText variant="smallBody" color={colors.neutral.gray700} style={[styles.welcomeSubtitle, isRTL && styles.rtlText]}>
                {heading('Ravi de vous revoir ! Découvrez\nnos nouveautés sélectionnées pour vous.', 'سعداء برؤيتك مجددًا! اكتشف تشكيلتنا الجديدة المختارة لك.')}
              </MayushText>
            </View>
          </View>

          {/* 3. Active Order Card: "Commande en cours" (Only if active order exists) */}
          {activeOrder && (
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
                    {activeOrder.orderId}
                  </MayushText>
                  <MayushText variant="caption" color="#16A34A" style={styles.activeOrderDateText}>
                    {heading(`Livraison estimée : ${activeOrder.createdAt}`, `التسليم المتوقع : ${activeOrder.createdAt}`)}
                  </MayushText>
                </View>
              </View>
              <TouchableOpacity accessibilityRole="button" accessibilityLabel={heading('Voir le suivi', 'تتبع الطلب')} onPress={() => onOpenOrder?.(activeOrder.orderId)} style={[styles.activeOrderTrackButton, isRTL && styles.rowReverse]}>
                <MayushText variant="smallBody" color={colors.brand.orange500} style={styles.activeOrderTrackText}>
                  {heading('Voir le suivi', 'تتبع الطلب')}
                </MayushText>
                <MayushIcon name={isRTL ? 'chevron-left' : 'chevron-right'} size={16} color={colors.brand.orange500} />
              </TouchableOpacity>
            </View>
          )}

          {/* 4. Hero Carousel Banner ("Et si votre intérieur reflétait qui vous êtes ?") */}
          <View style={styles.heroWrapper}>
            <Image source={heroSlides.length > 0 ? heroSlides[activeHeroIndex]?.bg : LOGGED_IN_HERO_IMAGE} resizeMode="cover" style={styles.heroImage} />
            <View style={styles.heroOverlayDarkener} />
            <View style={[styles.heroCopyPanel, isRTL && styles.heroCopyPanelRtl]}>
              <MayushText variant="display" color={colors.surface.white} style={styles.heroTitle}>
                {heroSlides[activeHeroIndex]?.title1 || heading('Et si votre intérieur', 'ماذا لو كان منزلكم')}{'\n'}
                {heroSlides[activeHeroIndex]?.title2 ? (
                  <MayushText variant="display" color={colors.brand.orange500} style={styles.heroTitleAccent}>
                    {heroSlides[activeHeroIndex]?.title2}
                  </MayushText>
                ) : null}
              </MayushText>
              <MayushText variant="smallBody" color={colors.surface.white} style={styles.heroSubtitle}>
                {heroSlides[activeHeroIndex]?.subtitle ||
                  heading(
                    'Mobilier et décoration design sélectionnés pour leur esthétique et leur caractère.',
                    'أثاث وديكور عصري مصمم بعناية ليضفي جمالاً وطابعاً فريداً على مساحتكم.'
                  )}
              </MayushText>
              <TouchableOpacity
                accessibilityRole="button"
                accessibilityLabel={heroSlides[activeHeroIndex]?.button || heading('Découvrir nos collections', 'اكتشفوا تشكيلاتنا')}
                onPress={() => handleHeroCtaPress(heroSlides[activeHeroIndex]?.ctaLink)}
                activeOpacity={0.84}
                style={styles.heroCtaButton}
              >
                <MayushText variant="smallBody" color={colors.surface.white} style={styles.heroCtaText}>
                  {heroSlides[activeHeroIndex]?.button || heading('Découvrir nos collections', 'اكتشفوا تشكيلاتنا')}
                </MayushText>
              </TouchableOpacity>
            </View>
            {heroSlides.length > 1 && (
              <View style={[styles.heroDots, isRTL && styles.heroDotsRtl]}>
                {heroSlides.map((_, idx) => (
                  <TouchableOpacity
                    key={idx}
                    onPress={() => selectHeroSlide(idx)}
                    hitSlop={6}
                    style={[styles.heroDot, idx === activeHeroIndex ? styles.heroDotActive : styles.heroDotInactive]}
                  />
                ))}
              </View>
            )}
          </View>

          {/* 5. Recommandé pour vous (Personalized Home: High priority position) */}
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

          {/* 6. ❤️ Vos favoris sont en promotion (NEW) */}
          {wishlistPromotions.length > 0 && (
            <>
              <SectionHeader
                label={heading('❤️ Vos favoris sont en promotion', '❤️ منتجاتك المفضلة بتخفيضات')}
                action={heading('Voir tous mes favoris', 'عرض كل المفضلة')}
                isRTL={isRTL}
                onPress={onOpenWishlist ?? (() => onNavigateTab?.('wishlist'))}
              />
              <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.productRail}>
                {wishlistPromotions.map((prod) => (
                  <ProductCard
                    key={`fav-promo-${prod.id}`}
                    name={prod.name}
                    thumbnailUrl={prod.thumbnail_image}
                    currentPriceFormatted={prod.main_price || prod.formattedPrice || '159 MAD'}
                    originalPriceFormatted={prod.stroked_price}
                    hasDiscount={true}
                    discountPercentage={prod.discount ? (prod.discount.includes('%') ? prod.discount : `-${prod.discount}%`) : '-20%'}
                    isFavorite={true}
                    onFavoritePress={() => onToggleWishlist?.(prod)}
                    onPress={() => onSelectProduct?.(prod)}
                    width={productWidth}
                    isRTL={isRTL}
                  />
                ))}
              </ScrollView>
            </>
          )}

          {/* 7. Consultés récemment (Enhanced Dark Moodboard Scene Cards with 👁️ Eye Badges) */}
          {recentlyViewedScenes.length > 0 && (
            <>
              <SectionHeader
                label={heading('Consultés récemment', 'شوهدت مؤخراً')}
                action={heading('Voir tout', 'عرض الكل')}
                isRTL={isRTL}
                onPress={onOpenRecentlyViewed ?? (() => onNavigateTab?.('categories'))}
              />
              <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.recentScenesRail}>
                {recentlyViewedScenes.map((item, idx) => (
                  <TouchableOpacity
                    key={`recent-scene-${item.id}-${idx}`}
                    activeOpacity={0.88}
                    style={styles.recentSceneCard}
                    onPress={() => (item.product ? onSelectProduct?.(item.product) : onOpenRecentlyViewed?.())}
                  >
                    <Image source={item.image} style={styles.recentSceneImage} resizeMode="cover" />
                    <View style={styles.recentSceneDarkener} />
                    <View style={styles.recentSceneEyeBadgeTop}>
                      <MayushIcon name="eye" size={13} color={colors.surface.white} />
                    </View>
                    <View style={[styles.recentSceneFooter, isRTL && styles.rowReverse]}>
                      <MayushText variant="strongBody" color={colors.surface.white} numberOfLines={1} style={styles.recentSceneTitle}>
                        {item.title}
                      </MayushText>
                      <View style={styles.recentSceneEyeBadgeBottom}>
                        <MayushIcon name="eye" size={13} color={colors.surface.white} />
                      </View>
                    </View>
                  </TouchableOpacity>
                ))}
              </ScrollView>
            </>
          )}

          {/* 8. Catégories Section (Circle Row per design) */}
          {displayCategories.length > 0 && (
            <>
              <SectionHeader label={heading('Catégories', 'الأقسام')} action={heading('Voir tout', 'عرض الكل')} isRTL={isRTL} onPress={() => onNavigateTab?.('categories')} />
              <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.categoryCircleRow}>
                {displayCategories.map((cat) => (
                  <TouchableOpacity
                    key={cat.id}
                    accessibilityRole="button"
                    accessibilityLabel={cat.displayName}
                    activeOpacity={0.82}
                    style={styles.categoryCircleItem}
                    onPress={() => {
                      if (cat.categoryDto) onSelectCategory?.(cat.categoryDto);
                      else onNavigateTab?.('categories');
                    }}
                  >
                    <View style={styles.categoryCircleWrap}>
                      <Image source={cat.iconAsset} style={styles.categoryCircleArt} resizeMode="contain" />
                    </View>
                    <MayushText variant="smallBody" color={colors.brand.navy900} align="center" numberOfLines={2} style={styles.categoryCircleLabel}>
                      {cat.displayName}
                    </MayushText>
                  </TouchableOpacity>
                ))}
                <TouchableOpacity
                  accessibilityRole="button"
                  accessibilityLabel={heading('Voir tout', 'عرض الكل')}
                  activeOpacity={0.82}
                  style={styles.categoryCircleItem}
                  onPress={() => onNavigateTab?.('categories')}
                >
                  <View style={styles.categoryMoreCircleWrap}>
                    <MayushIcon name="more-horizontal" size={24} color={colors.brand.navy900} />
                  </View>
                  <MayushText variant="smallBody" color={colors.brand.navy900} align="center" numberOfLines={2} style={styles.categoryCircleLabel}>
                    {heading('Voir tout', 'عرض الكل')}
                  </MayushText>
                </TouchableOpacity>
              </ScrollView>
            </>
          )}

          {/* 9. Flash Deal Section (With live countdown timer) */}
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

          {/* 10. Promo Banner (-15% sur tout le site) */}
          <TouchableOpacity
            activeOpacity={0.88}
            onPress={onOpenPromotions}
            accessibilityRole="button"
            accessibilityLabel={heading('Offre spéciale -15% sur tout le site', 'عرض خاص -15% على جميع المنتجات')}
            style={styles.middlePromoBannerWrapper}
          >
            <Image
              source={promoBanner && promoBanner.imageUrl && !promoBanner.imageUrl.includes('placeholder') ? { uri: promoBanner.imageUrl } : PROMO_BANNER_IMAGE}
              resizeMode="cover"
              style={styles.middlePromoBannerImage}
            />
            <View style={styles.promoBannerDarkener} />
            <View style={[styles.promoBannerContent, isRTL && styles.promoBannerContentRtl]}>
              <View style={[styles.promoTagBadge, isRTL && styles.promoTagBadgeRtl]}>
                <MayushText variant="caption" color={colors.surface.white} style={styles.promoTagText}>
                  {heading('OFFRE SPÉCIALE', 'عرض خاص')}
                </MayushText>
              </View>
              <MayushText variant="display" color={colors.surface.white} style={[styles.promoBannerTitle, isRTL && styles.textRtl]}>
                {heading("-15% sur tout le site", "تخفيض 15%- على كل الموقع")}
              </MayushText>
              <MayushText variant="smallBody" color={colors.surface.white} style={[styles.promoBannerSubtitle, isRTL && styles.textRtl]}>
                {heading("Des pièces uniques pour sublimer votre intérieur.", "قطع فريدة ومميزة لتجديد ديكور منزلك بأناقة.")}
              </MayushText>
              <View style={[styles.promoBannerCta, isRTL && styles.rowReverse]}>
                <MayushText variant="button" color={colors.surface.white} style={styles.promoBannerCtaText}>
                  {heading("Profitez-en maintenant", "استفد من العرض دابا")}
                </MayushText>
                <MayushIcon name={isRTL ? "arrow-left" : "arrow-right"} size={14} color={colors.surface.white} />
              </View>
            </View>
          </TouchableOpacity>

          {/* 11. Les plus appréciés cette semaine (❤️ Très apprécié + 5★) */}
          {bestSellers.length > 0 && (
            <>
              <SectionHeader
                label={heading('Les plus appréciés cette semaine', 'الأكثر إعجاباً هذا الأسبوع')}
                action={heading('Voir tout', 'عرض الكل')}
                isRTL={isRTL}
                onPress={onOpenBestSellers ?? (() => onNavigateTab?.('categories'))}
              />
              <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.productRail}>
                {bestSellers.map((prod, idx) => (
                  <ProductCard
                    key={`appreciated-${prod.id}-${idx}`}
                    name={prod.name}
                    thumbnailUrl={prod.thumbnail_image}
                    currentPriceFormatted={prod.main_price || prod.formattedPrice || '199 MAD'}
                    originalPriceFormatted={prod.stroked_price}
                    badgeText={heading('Très apprécié', 'نال إعجاب الكثير')}
                    badgeIcon="heart-filled"
                    badgeBgColor="#FFF5EB"
                    badgeTextColor="#DE703B"
                    rating={prod.rating || 5}
                    ratingCount={prod.review_count ?? prod.rating_count ?? (idx === 0 ? 128 : idx === 1 ? 96 : idx === 2 ? 75 : 64)}
                    isFavorite={wishlistedProductIds.includes(prod.id)}
                    onFavoritePress={() => onToggleWishlist?.(prod)}
                    onPress={() => onSelectProduct?.(prod)}
                    width={productWidth}
                    isRTL={isRTL}
                  />
                ))}
              </ScrollView>
            </>
          )}

          {/* 12. Nouveautés dans vos catégories préférées (Interactive Category Chips + Green 'Nouveau' badge) */}
          <View style={styles.newArrivalsCategorySection}>
            <SectionHeader
              label={heading('Nouveautés dans vos catégories préférées', 'جديد الأقسام المفضلة لديك')}
              action={heading('Voir tout', 'عرض الكل')}
              isRTL={isRTL}
              onPress={onOpenNewArrivals ?? onOpenPromotions ?? (() => onNavigateTab?.('categories'))}
            />
            {/* Category Filter Chips */}
            <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.categoryFilterChipsRow}>
              {newCategoryChips.map((chip) => {
                const isActive = selectedNewCategorySlug === chip.slug;
                return (
                  <TouchableOpacity
                    key={chip.slug}
                    activeOpacity={0.8}
                    style={[styles.categoryFilterChip, isActive && styles.categoryFilterChipActive]}
                    onPress={() => setSelectedNewCategorySlug(chip.slug)}
                  >
                    <MayushText
                      variant="smallBody"
                      color={isActive ? colors.surface.white : colors.brand.navy900}
                      style={[styles.categoryFilterChipText, isActive && styles.categoryFilterChipTextActive]}
                    >
                      {heading(chip.nameFr, chip.nameAr)}
                    </MayushText>
                  </TouchableOpacity>
                );
              })}
            </ScrollView>
            {/* Filtered Products Rail */}
            <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.productRail}>
              {(categoryNewArrivals.length > 0 ? categoryNewArrivals : newArrivals).map((prod, idx) => (
                <ProductCard
                  key={`new-cat-${prod.id}-${idx}`}
                  name={prod.name}
                  thumbnailUrl={prod.thumbnail_image}
                  currentPriceFormatted={prod.main_price || prod.formattedPrice || '129 MAD'}
                  originalPriceFormatted={prod.stroked_price}
                  badgeText={heading('Nouveau', 'جديد')}
                  badgeBgColor="#16A34A"
                  badgeTextColor="#FFFFFF"
                  isFavorite={wishlistedProductIds.includes(prod.id)}
                  onFavoritePress={() => onToggleWishlist?.(prod)}
                  onPress={() => onSelectProduct?.(prod)}
                  width={productWidth}
                  isRTL={isRTL}
                />
              ))}
            </ScrollView>
          </View>

          {/* 13. Dynamic Inspiration du moment Section (Live Rail vs Coming Soon Preview) */}
          {renderInspirationsSection()}

          {/* 14. Collections vedettes Section */}
          {displayCollections.length > 0 && (
            <>
              <SectionHeader
                label={heading('Collections vedettes', 'التشكيلات المميزة')}
                action={heading('Voir tout', 'عرض الكل')}
                isRTL={isRTL}
                onPress={onOpenCollections ?? (() => onNavigateTab?.('categories'))}
              />
              <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.collectionsCircleRow}>
                {displayCollections.map((col) => (
                  <TouchableOpacity
                    key={col.id}
                    accessibilityRole="button"
                    accessibilityLabel={col.name}
                    activeOpacity={0.84}
                    style={styles.collectionCircleItem}
                    onPress={() => (onSelectCollection ? onSelectCollection(col.collection) : onOpenCollections?.())}
                  >
                    <View style={styles.collectionCircleWrap}>
                      <Image source={col.image} style={styles.collectionCircleImage} resizeMode="cover" />
                    </View>
                    <MayushText variant="strongBody" color={colors.brand.navy900} align="center" numberOfLines={1} style={styles.collectionCircleTitle}>
                      {col.name}
                    </MayushText>
                  </TouchableOpacity>
                ))}
                <TouchableOpacity
                  accessibilityRole="button"
                  accessibilityLabel={heading('Voir toutes les collections', 'عرض كل التشكيلات')}
                  activeOpacity={0.84}
                  style={styles.collectionCircleItem}
                  onPress={onOpenCollections ?? (() => onNavigateTab?.('categories'))}
                >
                  <View style={styles.collectionMoreCircleWrap}>
                    <MayushIcon name="more-horizontal" size={26} color={colors.brand.navy900} />
                  </View>
                  <MayushText variant="strongBody" color={colors.brand.navy900} align="center" numberOfLines={2} style={styles.collectionCircleTitle}>
                    {heading('Voir toutes les\ncollections', 'عرض كل\nالتشكيلات')}
                  </MayushText>
                </TouchableOpacity>
              </ScrollView>
            </>
          )}

          {/* 15. Services, Reviews, Journal, and Trust Badges */}
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
                <View key={`hero-slide-${index}`} style={[styles.heroSlide, { width: contentWidth, height: heroHeight }]}>
                  <Image source={slide.bg} resizeMode="cover" style={styles.heroImage} />
                  <View style={styles.heroOverlayDarkener} />
                  <View style={[styles.heroCopyPanel, isRTL && styles.heroCopyPanelRtl]}>
                    <MayushText variant="display" color={colors.surface.white} style={styles.heroTitle}>
                      {slide.title1}{'\n'}
                      {slide.title2 ? (
                        <MayushText variant="display" color={colors.brand.orange500} style={styles.heroTitleAccent}>
                          {slide.title2}
                        </MayushText>
                      ) : null}
                    </MayushText>
                    <MayushText variant="smallBody" color={colors.surface.white} style={styles.heroSubtitle}>
                      {slide.subtitle}
                    </MayushText>
                    <TouchableOpacity
                      accessibilityRole="button"
                      accessibilityLabel={slide.button}
                      onPress={() => handleHeroCtaPress(slide.ctaLink)}
                      activeOpacity={0.84}
                      style={styles.heroCtaButton}
                    >
                      <MayushText variant="smallBody" color={colors.surface.white} style={styles.heroCtaText}>
                        {slide.button}
                      </MayushText>
                    </TouchableOpacity>
                  </View>
                </View>
              ))}
            </ScrollView>
            {heroSlides.length > 1 && (
              <View style={[styles.heroDots, isRTL && styles.heroDotsRtl]}>
                {heroSlides.map((_, idx) => (
                  <TouchableOpacity
                    key={idx}
                    onPress={() => selectHeroSlide(idx)}
                    hitSlop={6}
                    style={[styles.heroDot, idx === activeHeroIndex ? styles.heroDotActive : styles.heroDotInactive]}
                  />
                ))}
              </View>
            )}
          </View>
        ) : null}

        {/* 4. Featured Categories Row (Circle Row per design) */}
        {categoriesLoading ? (
          <CategoryRowSkeleton />
        ) : displayCategories.length > 0 ? (
          <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.categoryCircleRow}>
            {displayCategories.map((cat) => (
              <TouchableOpacity
                key={cat.id}
                accessibilityRole="button"
                accessibilityLabel={cat.displayName}
                activeOpacity={0.82}
                style={styles.categoryCircleItem}
                onPress={() => {
                  if (cat.categoryDto) onSelectCategory?.(cat.categoryDto);
                  else onNavigateTab?.('categories');
                }}
              >
                <View style={styles.categoryCircleWrap}>
                  <Image source={cat.iconAsset} style={styles.categoryCircleArt} resizeMode="contain" />
                </View>
                <MayushText variant="smallBody" color={colors.brand.navy900} align="center" numberOfLines={2} style={styles.categoryCircleLabel}>
                  {cat.displayName}
                </MayushText>
              </TouchableOpacity>
            ))}
            <TouchableOpacity
              accessibilityRole="button"
              accessibilityLabel={heading('Voir tout', 'عرض الكل')}
              activeOpacity={0.82}
              style={styles.categoryCircleItem}
              onPress={() => onNavigateTab?.('categories')}
            >
              <View style={styles.categoryMoreCircleWrap}>
                <MayushIcon name="more-horizontal" size={24} color={colors.brand.navy900} />
              </View>
              <MayushText variant="smallBody" color={colors.brand.navy900} align="center" numberOfLines={2} style={styles.categoryCircleLabel}>
                {heading('Voir tout', 'عرض الكل')}
              </MayushText>
            </TouchableOpacity>
          </ScrollView>
        ) : null}

        {/* 5. Nouveautés Section */}
        {newArrivalsLoading ? (
          <>
            <SectionHeader label={heading('Nouveautés', 'وصول جديد')} action={heading('Voir tout', 'عرض الكل')} isRTL={isRTL} onPress={onOpenNewArrivals ?? onOpenPromotions ?? (() => onNavigateTab?.('categories'))} />
            <ProductRailSkeleton cardWidth={productWidth} count={4} />
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
            <ProductRailSkeleton cardWidth={productWidth} count={4} />
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

        {/* 7. Offres du moment Banner */}
        <TouchableOpacity
          activeOpacity={0.88}
          onPress={onOpenPromotions}
          accessibilityRole="button"
          accessibilityLabel={heading('Offre exclusive mobilier et décoration', 'همزة حصرية على الأثاث والديكور')}
          style={styles.middlePromoBannerWrapper}
        >
          <Image
            source={promoBanner && promoBanner.imageUrl && !promoBanner.imageUrl.includes('placeholder') ? { uri: promoBanner.imageUrl } : PROMO_BANNER_IMAGE}
            resizeMode="cover"
            style={styles.middlePromoBannerImage}
          />
          <View style={styles.promoBannerDarkener} />
          <View style={[styles.promoBannerContent, isRTL && styles.promoBannerContentRtl]}>
            <View style={[styles.promoTagBadge, isRTL && styles.promoTagBadgeRtl]}>
              <MayushText variant="caption" color={colors.surface.white} style={styles.promoTagText}>
                {heading('OFFRE SPÉCIALE • JUSQU’À -40%', 'همزة حصرية 🔥 تخفيضات حتى لـ 40%-')}
              </MayushText>
            </View>
            <MayushText variant="display" color={colors.surface.white} style={[styles.promoBannerTitle, isRTL && styles.textRtl]}>
              {heading("Sublimez votre intérieur", "بدّل ديكور دارك بأحسن ما كاين")}
            </MayushText>
            <MayushText variant="smallBody" color={colors.surface.white} style={[styles.promoBannerSubtitle, isRTL && styles.textRtl]}>
              {heading("Sélection exclusive de mobilier et déco haut de gamme.", "أثاث وديكور راقي بأثمنة واعرة كتوالم دارك.")}
            </MayushText>
            <View style={[styles.promoBannerCta, isRTL && styles.rowReverse]}>
              <MayushText variant="button" color={colors.surface.white} style={styles.promoBannerCtaText}>
                {heading("Découvrir les promos", "استفد من العرض دابا")}
              </MayushText>
              <MayushIcon name={isRTL ? "arrow-left" : "arrow-right"} size={14} color={colors.surface.white} />
            </View>
          </View>
        </TouchableOpacity>

        {/* 8. Dynamic Inspiration du moment Section (Live Rail vs Coming Soon Preview) */}
        {renderInspirationsSection()}

        {/* 9. Collections vedettes Section */}
        {displayCollections.length > 0 && (
          <>
            <SectionHeader label={heading('Collections vedettes', 'التشكيلات المميزة')} action={heading('Voir tout', 'عرض الكل')} isRTL={isRTL} onPress={onOpenCollections ?? (() => onNavigateTab?.('categories'))} />
            <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.collectionsCircleRow}>
              {displayCollections.map((col) => (
                <TouchableOpacity
                  key={col.id}
                  accessibilityRole="button"
                  accessibilityLabel={col.name}
                  activeOpacity={0.84}
                  style={styles.collectionCircleItem}
                  onPress={() => (onSelectCollection ? onSelectCollection(col.collection) : onOpenCollections?.())}
                >
                  <View style={styles.collectionCircleWrap}>
                    <Image source={col.image} style={styles.collectionCircleImage} resizeMode="cover" />
                  </View>
                  <MayushText variant="strongBody" color={colors.brand.navy900} align="center" numberOfLines={1} style={styles.collectionCircleTitle}>
                    {col.name}
                  </MayushText>
                </TouchableOpacity>
              ))}
              <TouchableOpacity
                accessibilityRole="button"
                accessibilityLabel={heading('Voir toutes les collections', 'عرض كل التشكيلات')}
                activeOpacity={0.84}
                style={styles.collectionCircleItem}
                onPress={onOpenCollections ?? (() => onNavigateTab?.('categories'))}
              >
                <View style={styles.collectionMoreCircleWrap}>
                  <MayushIcon name="more-horizontal" size={26} color={colors.brand.navy900} />
                </View>
                <MayushText variant="strongBody" color={colors.brand.navy900} align="center" numberOfLines={2} style={styles.collectionCircleTitle}>
                  {heading('Voir toutes les\ncollections', 'عرض كل\nالتشكيلات')}
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

        {/* 12. Services, Reviews, Journal, and Trust Badges */}
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
  wishlistedProductIds?: number[];
  onToggleWishlist?: (product: ProductMiniDto) => void;
  badgeText?: string;
}> = ({ products, cardWidth, onSelect, wishlistedProductIds = [], onToggleWishlist, badgeText }) => (
  <ScrollView
    horizontal
    showsHorizontalScrollIndicator={false}
    decelerationRate="fast"
    snapToInterval={cardWidth + HOME_PRODUCT_RAIL_GAP}
    contentContainerStyle={styles.productRail}
  >
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
          rating={item.rating}
          ratingCount={item.review_count ?? item.rating_count ?? 0}
          width={cardWidth}
          onPress={() => onSelect?.(item)}
          isFavorite={wishlistedProductIds.includes(item.id)}
          onFavoritePress={() => onToggleWishlist?.(item)}
        />
      );
    })}
  </ScrollView>
);

const InspirationCard: React.FC<{ source: ImageSourcePropType; width: number; title?: string; subtitle?: string; onPress?: () => void }> = ({ source, width, title, subtitle, onPress }) => (
  <TouchableOpacity accessibilityRole="button" activeOpacity={0.88} onPress={onPress} style={[styles.inspirationCard, { width }]}>
    <Image source={source} style={styles.inspirationImage} resizeMode="cover" />
    {title ? (
      <View style={styles.inspirationOverlay}>
        <MayushText variant="button" color="#FFFFFF" numberOfLines={1}>{title}</MayushText>
        {subtitle ? <MayushText variant="caption" color="rgba(255,255,255,0.85)" numberOfLines={1}>{subtitle}</MayushText> : null}
      </View>
    ) : null}
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
    height: 200,
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
    backgroundColor: 'rgba(15, 23, 42, 0.46)',
  },
  heroCopyPanel: {
    position: 'absolute',
    top: 0,
    bottom: 0,
    left: 20,
    right: 28,
    justifyContent: 'center',
    alignItems: 'flex-start',
  },
  heroCopyPanelRtl: {
    left: 28,
    right: 20,
    alignItems: 'flex-end',
  },
  heroTitle: {
    fontSize: 18,
    lineHeight: 23,
    fontWeight: '800',
    color: colors.surface.white,
    letterSpacing: -0.2,
  },
  heroTitleAccent: {
    color: colors.brand.orange500,
    fontWeight: '800',
  },
  heroSubtitle: {
    fontSize: 11.5,
    lineHeight: 15.5,
    marginTop: 5,
    marginBottom: 12,
    color: 'rgba(255, 255, 255, 0.94)',
    maxWidth: '90%',
  },
  heroCtaButton: {
    backgroundColor: colors.brand.orange500,
    borderRadius: 20,
    paddingHorizontal: 16,
    paddingVertical: 7,
    alignSelf: 'flex-start',
    shadowColor: colors.brand.orange500,
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.25,
    shadowRadius: 4,
    elevation: 3,
  },
  heroCtaText: {
    fontSize: 12,
    fontWeight: '700',
    color: colors.surface.white,
  },
  heroDots: {
    position: 'absolute',
    bottom: 12,
    right: 16,
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
  },
  heroDotsRtl: {
    right: undefined,
    left: 16,
    flexDirection: 'row-reverse',
  },
  heroDot: {
    height: 5,
    borderRadius: 3,
  },
  heroDotActive: {
    width: 18,
    backgroundColor: colors.brand.orange500,
  },
  heroDotInactive: {
    width: 6,
    backgroundColor: 'rgba(255, 255, 255, 0.55)',
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
    borderRadius: 18,
    overflow: 'hidden',
    height: 165,
    marginBottom: 20,
    position: 'relative',
    shadowColor: '#12192A',
    shadowOffset: { width: 0, height: 6 },
    shadowOpacity: 0.12,
    shadowRadius: 10,
    elevation: 4,
  },
  middlePromoBannerImage: {
    ...StyleSheet.absoluteFill,
    width: '100%',
    height: '100%',
  },
  promoBannerDarkener: {
    ...StyleSheet.absoluteFill,
    backgroundColor: 'rgba(18, 24, 38, 0.44)',
  },
  promoBannerContent: {
    position: 'absolute',
    top: 14,
    left: 16,
    right: 50,
    bottom: 14,
    justifyContent: 'center',
  },
  promoBannerContentRtl: {
    left: 50,
    right: 16,
    alignItems: 'flex-end',
  },
  promoTagBadge: {
    backgroundColor: colors.brand.orange500,
    borderRadius: 12,
    paddingHorizontal: 10,
    paddingVertical: 3,
    alignSelf: 'flex-start',
    marginBottom: 6,
  },
  promoTagBadgeRtl: {
    alignSelf: 'flex-end',
  },
  promoTagText: {
    fontSize: 10,
    fontWeight: '700',
    letterSpacing: 0.5,
  },
  promoBannerTitle: {
    fontSize: 17,
    lineHeight: 21,
    fontWeight: '800',
    color: colors.surface.white,
  },
  promoBannerSubtitle: {
    fontSize: 11,
    lineHeight: 14,
    color: '#F3F5F7',
    opacity: 0.95,
    marginTop: 2,
    marginBottom: 10,
  },
  promoBannerCta: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: colors.brand.orange500,
    borderRadius: 16,
    paddingHorizontal: 12,
    paddingVertical: 5,
    alignSelf: 'flex-start',
    gap: 6,
  },
  promoBannerCtaText: {
    fontSize: 11,
    fontWeight: '700',
  },
  textRtl: {
    textAlign: 'right',
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
  categoryCircleRow: {
    flexDirection: 'row',
    paddingVertical: 6,
    gap: 10,
  },
  categoryCircleItem: {
    alignItems: 'center',
    width: 70,
    paddingHorizontal: 1,
  },
  categoryCircleWrap: {
    width: 54,
    height: 54,
    borderRadius: 27,
    backgroundColor: '#FAF5EE',
    alignItems: 'center',
    justifyContent: 'center',
    overflow: 'hidden',
  },
  categoryCircleArt: {
    width: 54,
    height: 54,
    borderRadius: 27,
  },
  categoryCircleLabel: {
    marginTop: 5,
    fontSize: 10.5,
    fontWeight: '600',
    lineHeight: 13,
  },
  categoryMoreCircleWrap: {
    width: 54,
    height: 54,
    borderRadius: 27,
    backgroundColor: '#FAF5EE',
    alignItems: 'center',
    justifyContent: 'center',
  },
  collectionsCircleRow: {
    flexDirection: 'row',
    paddingVertical: 6,
    gap: 14,
  },
  collectionCircleItem: {
    alignItems: 'center',
    width: 105,
  },
  collectionCircleWrap: {
    width: 100,
    height: 55,
    borderRadius: 28,
    overflow: 'hidden',
    backgroundColor: '#FAF7F2',
    borderWidth: 1,
    borderColor: '#EFE8DC',
    alignItems: 'center',
    justifyContent: 'center',
  },
  collectionCircleImage: {
    width: '100%',
    height: '100%',
  },
  collectionCircleTitle: {
    marginTop: 6,
    fontSize: 12,
    fontWeight: '700',
  },
  collectionCircleSub: {
    marginTop: 2,
    fontSize: 10,
    lineHeight: 13,
  },
  collectionMoreCircleWrap: {
    width: 100,
    height: 55,
    borderRadius: 28,
    backgroundColor: '#FAF7F2',
    borderWidth: 1,
    borderColor: '#EFE8DC',
    alignItems: 'center',
    justifyContent: 'center',
  },
  productRail: {
    paddingVertical: 6,
    gap: HOME_PRODUCT_RAIL_GAP,
    marginBottom: 16,
  },
  inspirationsSection: {
    marginBottom: 16,
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
  inspirationOverlay: {
    position: 'absolute',
    bottom: 0,
    left: 0,
    right: 0,
    paddingHorizontal: 12,
    paddingVertical: 10,
    backgroundColor: 'rgba(15, 23, 42, 0.52)',
  },
  inspirationComingSoonCard: {
    borderRadius: 16,
    overflow: 'hidden',
    minHeight: 165,
    backgroundColor: colors.brand.navy900,
    position: 'relative',
    justifyContent: 'flex-end',
    marginBottom: 14,
  },
  inspirationComingSoonImage: {
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
    bottom: 0,
    width: '100%',
    height: '100%',
  },
  inspirationComingSoonDarkener: {
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
    bottom: 0,
    backgroundColor: 'rgba(15, 23, 42, 0.54)',
  },
  inspirationComingSoonContent: {
    padding: 14,
    gap: 5,
  },
  inspirationComingSoonContentRtl: {
    alignItems: 'flex-end',
  },
  inspirationComingSoonBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    alignSelf: 'flex-start',
    gap: 4,
    backgroundColor: colors.brand.orange500,
    paddingHorizontal: 8,
    paddingVertical: 3,
    borderRadius: 6,
    marginBottom: 2,
  },
  inspirationComingSoonBadgeRtl: {
    alignSelf: 'flex-end',
    flexDirection: 'row-reverse',
  },
  inspirationComingSoonBadgeText: {
    fontSize: 10,
    fontWeight: '700',
    letterSpacing: 0.8,
  },
  inspirationComingSoonTitle: {
    fontSize: 17,
    lineHeight: 22,
    fontWeight: '700',
  },
  inspirationComingSoonSubtitle: {
    fontSize: 12,
    lineHeight: 16,
  },
  inspirationComingSoonCta: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    marginTop: 3,
  },
  inspirationComingSoonCtaText: {
    fontSize: 12,
    fontWeight: '600',
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
  reviewsSection: {
    marginVertical: 14,
  },
  reviewsScoreAndRail: {
    gap: 12,
  },
  reviewScoreBadge: {
    backgroundColor: colors.surface.white,
    borderRadius: 14,
    borderWidth: 1,
    borderColor: '#EFE8DC',
    padding: 12,
    alignItems: 'center',
    justifyContent: 'center',
  },
  reviewScoreNumber: {
    fontSize: 24,
    fontWeight: '800',
    lineHeight: 28,
  },
  reviewStarsRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 3,
    marginVertical: 4,
  },
  starIcon: {
    marginHorizontal: 1,
  },
  reviewCountText: {
    fontSize: 11,
  },
  reviewsCardsRail: {
    paddingVertical: 4,
    gap: 10,
  },
  reviewCard: {
    width: 250,
    backgroundColor: colors.surface.white,
    borderRadius: 14,
    borderWidth: 1,
    borderColor: '#EFE8DC',
    padding: 12,
    justifyContent: 'space-between',
  },
  reviewCardTop: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    marginBottom: 6,
  },
  reviewQuoteText: {
    fontSize: 11,
    lineHeight: 15,
    marginBottom: 8,
  },
  reviewAuthorName: {
    fontSize: 12,
    fontWeight: '700',
  },
  journalBannerWrapper: {
    borderRadius: 16,
    overflow: 'hidden',
    height: 145,
    marginVertical: 16,
    position: 'relative',
    backgroundColor: '#1E293B',
  },
  journalBgImage: {
    ...StyleSheet.absoluteFill,
    width: '100%',
    height: '100%',
  },
  journalOverlayDarkener: {
    ...StyleSheet.absoluteFill,
    backgroundColor: 'rgba(15, 23, 42, 0.52)',
  },
  journalContentOverlay: {
    position: 'absolute',
    left: 18,
    right: 18,
    top: 0,
    bottom: 0,
    justifyContent: 'center',
    alignItems: 'flex-start',
  },
  journalContentOverlayRtl: {
    alignItems: 'flex-end',
  },
  journalTitle: {
    fontSize: 17,
    fontWeight: '800',
    marginBottom: 4,
  },
  journalSubtitle: {
    fontSize: 11,
    lineHeight: 15,
    marginBottom: 10,
    maxWidth: '85%',
  },
  journalButton: {
    backgroundColor: colors.brand.orange500,
    borderRadius: 16,
    paddingHorizontal: 14,
    paddingVertical: 6,
    alignSelf: 'flex-start',
    shadowColor: colors.brand.orange500,
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.25,
    shadowRadius: 4,
    elevation: 2,
  },
  journalButtonText: {
    fontSize: 11.5,
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
  recentScenesRail: {
    paddingVertical: 6,
    gap: 12,
    marginBottom: 18,
  },
  recentSceneCard: {
    width: 145,
    height: 100,
    borderRadius: 14,
    overflow: 'hidden',
    position: 'relative',
    backgroundColor: '#1E1E1E',
  },
  recentSceneImage: {
    width: '100%',
    height: '100%',
  },
  recentSceneDarkener: {
    ...StyleSheet.absoluteFill,
    backgroundColor: 'rgba(0, 0, 0, 0.42)',
  },
  recentSceneEyeBadgeTop: {
    position: 'absolute',
    top: 8,
    right: 8,
    width: 24,
    height: 24,
    borderRadius: 12,
    backgroundColor: 'rgba(0, 0, 0, 0.5)',
    alignItems: 'center',
    justifyContent: 'center',
  },
  recentSceneFooter: {
    position: 'absolute',
    bottom: 8,
    left: 10,
    right: 10,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    gap: 6,
  },
  recentSceneTitle: {
    fontSize: 11.5,
    fontWeight: '700',
    color: colors.surface.white,
    flex: 1,
  },
  recentSceneEyeBadgeBottom: {
    width: 20,
    height: 20,
    borderRadius: 10,
    backgroundColor: 'rgba(0, 0, 0, 0.35)',
    alignItems: 'center',
    justifyContent: 'center',
  },
  newArrivalsCategorySection: {
    marginBottom: 18,
  },
  categoryFilterChipsRow: {
    flexDirection: 'row',
    gap: 8,
    paddingVertical: 6,
    marginBottom: 10,
  },
  categoryFilterChip: {
    paddingHorizontal: 16,
    paddingVertical: 7,
    borderRadius: 20,
    backgroundColor: colors.surface.white,
    borderWidth: 1,
    borderColor: '#EAE3D9',
  },
  categoryFilterChipActive: {
    backgroundColor: colors.brand.orange500,
    borderColor: colors.brand.orange500,
  },
  categoryFilterChipText: {
    fontSize: 12.5,
    fontWeight: '600',
    color: colors.brand.navy900,
  },
  categoryFilterChipTextActive: {
    color: colors.surface.white,
    fontWeight: '700',
  },
  moroccoTrustStrip: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    paddingVertical: 18,
    paddingHorizontal: 8,
    backgroundColor: '#FAF7F2',
    borderRadius: 16,
    marginTop: 12,
    marginBottom: 20,
    borderWidth: 1,
    borderColor: '#EFE8DC',
  },
  moroccoTrustItem: {
    flex: 1,
    alignItems: 'center',
    paddingHorizontal: 3,
  },
  moroccoTrustIconWrap: {
    width: 38,
    height: 38,
    borderRadius: 19,
    backgroundColor: '#FFF7ED',
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 6,
  },
  moroccoTrustTitle: {
    fontSize: 11,
    fontWeight: '700',
    marginBottom: 2,
  },
  moroccoTrustSub: {
    fontSize: 9.5,
    lineHeight: 12,
  },
  rowReverse: {
    flexDirection: 'row-reverse',
  },
  rtlText: {
    writingDirection: 'rtl',
  },
});
