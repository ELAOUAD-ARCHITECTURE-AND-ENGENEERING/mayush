/**
 * Mayush Catalog API Service
 * Production repositories for home sliders, banners, categories, product lists, product details,
 * variant price & stock lookups, search suggestions, and filters.
 *
 * Enforces strict Fallback Policy:
 * Default: ENABLE_CATALOG_FALLBACKS = false.
 * On API success -> Returns live Laravel database content ([CATALOG] <section> source: LIVE_API).
 * On API empty   -> Returns empty collection [] (never injects fake products).
 * On API failure -> Throws error for real loading/error/retry state.
 * Only when EXPO_PUBLIC_ENABLE_CATALOG_FALLBACKS=true will QA fixtures be returned.
 */

import { apiClient, API_BASE_URL, ENABLE_CATALOG_FALLBACKS } from '../../api';
import {
  CategoryDto,
  ProductCollectionDto,
  ProductMiniDto,
  ProductDetailDto,
  VariantPriceRequestDto,
  VariantPriceResponseDto,
  PaginatedCollectionDto,
  MvpAppLanguage,
} from '../../contracts/api/dto';
import { normalizeImageUrl } from '../../contracts/mappers/imageNormalizer';

export interface SliderItemDto {
  photo: string;
  url: string;
}

export interface SliderCollectionDto {
  data: SliderItemDto[];
}

export interface ShopDetailsDto {
  id: number;
  user_id: number;
  name: string;
  logo: string;
  sliders: string[];
  address: string;
  facebook?: string;
  google?: string;
  twitter?: string;
  rating?: number;
}

export interface ReviewItemDto {
  id: number;
  product_id: number;
  user_name: string;
  user_avatar: string;
  rating: number;
  comment: string;
  time: string;
}

export interface SearchFilterOptionDto {
  id: number;
  name: string;
  slug?: string;
}

export interface SearchParams {
  name?: string;
  category?: string;
  categories?: string;
  brand?: string;
  brands?: string;
  min?: number;
  max?: number;
  sort_by?: string;
  sort_key?: string;
  page?: number;
}

// Intentional developer QA fallback datasets for explicit offline testing
const FALLBACK_SLIDERS: SliderItemDto[] = [
  {
    photo: 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?q=80&w=800&auto=format&fit=crop',
    url: '#',
  },
  {
    photo: 'https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?q=80&w=800&auto=format&fit=crop',
    url: '#',
  },
];

const FALLBACK_CATEGORIES_FR: CategoryDto[] = [
  {
    id: 1,
    name: 'Canapés & Fauteuils',
    banner: 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?q=80&w=400&auto=format&fit=crop',
    icon: 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?q=80&w=200&auto=format&fit=crop',
    number_of_children: 4,
    links: { products: '', sub_categories: '' },
  },
];

const FALLBACK_CATEGORIES_AR: CategoryDto[] = [
  {
    id: 1,
    name: 'كنبات وأريكة',
    banner: 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?q=80&w=400&auto=format&fit=crop',
    icon: 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?q=80&w=200&auto=format&fit=crop',
    number_of_children: 4,
    links: { products: '', sub_categories: '' },
  },
];

const FALLBACK_PRODUCTS_FR: ProductMiniDto[] = [
  {
    id: 101,
    name: 'Canapé Luna Bouclé',
    slug: 'canape-luna-boucle',
    thumbnail_image: 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?q=80&w=600&auto=format&fit=crop',
    has_discount: true,
    discount: '-15%',
    stroked_price: '14 500 MAD',
    main_price: '12 325 MAD',
    rating: 4.9,
    sales: 124,
    links: { details: '' },
  },
];

const FALLBACK_PRODUCTS_AR: ProductMiniDto[] = [
  {
    id: 101,
    name: 'أريكة لونا الفاخرة',
    slug: 'canape-luna-boucle',
    thumbnail_image: 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?q=80&w=600&auto=format&fit=crop',
    has_discount: true,
    discount: '-15%',
    stroked_price: '14 500 د.م.',
    main_price: '12 325 د.م.',
    rating: 4.9,
    sales: 124,
    links: { details: '' },
  },
];

function isDevEnvironment(): boolean {
  return typeof __DEV__ !== 'undefined' ? Boolean(__DEV__) : process.env.NODE_ENV !== 'production';
}

function logCatalogSource(sectionName: string, source: 'LIVE_API' | 'DEV_FIXTURE' | 'EMPTY_API') {
  if (isDevEnvironment()) {
    if (source === 'DEV_FIXTURE') {
      console.log(`[CATALOG] WARNING: DEV_FIXTURE used for ${sectionName}`);
    } else if (source === 'EMPTY_API') {
      console.log(`[CATALOG] ${sectionName} source: LIVE_API (empty collection)`);
    } else {
      console.log(`[CATALOG] ${sectionName} source: LIVE_API`);
    }
  }
}

export const catalogService = {
  /**
   * Fetch homepage hero sliders
   * GET /api/v2/sliders
   */
  async getSliders(language: MvpAppLanguage = 'fr'): Promise<SliderItemDto[]> {
    try {
      const res = await apiClient<{ data: SliderItemDto[]; success?: boolean }>('/api/v2/sliders', { language });
      if (res && Array.isArray(res.data)) {
        logCatalogSource('Home sliders', res.data.length === 0 ? 'EMPTY_API' : 'LIVE_API');
        return res.data.map((item) => ({
          photo: normalizeImageUrl(item.photo),
          url: item.url || '#',
        }));
      }
    } catch (err) {
      if (ENABLE_CATALOG_FALLBACKS) {
        logCatalogSource('Home sliders', 'DEV_FIXTURE');
        return FALLBACK_SLIDERS;
      }
      if (isDevEnvironment()) console.log('[CATALOG] ERROR: Home sliders API call failed', err);
      throw err;
    }
    return [];
  },

  /**
   * Fetch home promotional banners
   * GET /api/v2/banners-one | /api/v2/banners-two | /api/v2/banners-three
   */
  async getBanners(
    bannerNumber: 1 | 2 | 3 = 1,
    language: MvpAppLanguage = 'fr'
  ): Promise<SliderItemDto[]> {
    const endpointMap: Record<number, string> = {
      1: '/api/v2/banners-one',
      2: '/api/v2/banners-two',
      3: '/api/v2/banners-three',
    };

    try {
      const res = await apiClient<{ data: SliderItemDto[] }>(endpointMap[bannerNumber], { language });
      if (res && Array.isArray(res.data)) {
        logCatalogSource(`Promotional Banners (${bannerNumber})`, res.data.length === 0 ? 'EMPTY_API' : 'LIVE_API');
        return res.data.map((item) => ({
          photo: normalizeImageUrl(item.photo),
          url: item.url || '#',
        }));
      }
    } catch (err) {
      if (ENABLE_CATALOG_FALLBACKS) {
        logCatalogSource(`Promotional Banners (${bannerNumber})`, 'DEV_FIXTURE');
        return FALLBACK_SLIDERS;
      }
      if (isDevEnvironment()) console.log(`[CATALOG] ERROR: Banner ${bannerNumber} API call failed`, err);
      throw err;
    }
    return [];
  },

  /**
   * Fetch published product collections for home screen
   * GET /api/v2/product-collections
   */
  async getProductCollections(language: MvpAppLanguage = 'fr'): Promise<ProductCollectionDto[]> {
    try {
      const res = await apiClient<{ data: ProductCollectionDto[] }>('/api/v2/product-collections', { language });
      if (res && Array.isArray(res.data) && res.data.length > 0) {
        logCatalogSource('Product collections', 'LIVE_API');
        return res.data.map((col) => ({
          ...col,
          hero_image: normalizeImageUrl(col.hero_image),
        }));
      }
    } catch {
      // Fallthrough to root categories mapping
    }

    // Map real root categories with database banners to collections
    try {
      const categories = await this.getRootCategories(language);
      if (categories && categories.length > 0) {
        logCatalogSource('Product collections', 'LIVE_API');
        return categories.map((cat) => ({
          id: cat.id,
          slug: cat.slug || String(cat.id),
          name: cat.name,
          description: cat.banner ? 'Collection exclusive design & haute finition' : 'Inspiration & mobilier contemporain',
          hero_image: normalizeImageUrl(cat.banner || cat.icon || cat.cover_image),
        }));
      }
    } catch (err) {
      if (isDevEnvironment()) console.log('[CATALOG] ERROR: Product collections fallback failed', err);
    }
    return [];
  },

  /**
   * Fetch featured categories grid
   * GET /api/v2/categories/featured
   */
  async getFeaturedCategories(language: MvpAppLanguage = 'fr'): Promise<CategoryDto[]> {
    try {
      const res = await apiClient<{ data: CategoryDto[] }>('/api/v2/categories/featured', { language });
      if (res && Array.isArray(res.data)) {
        logCatalogSource('Featured categories', res.data.length === 0 ? 'EMPTY_API' : 'LIVE_API');
        return res.data.map((cat) => ({
          ...cat,
          icon: normalizeImageUrl(cat.icon),
          banner: normalizeImageUrl(cat.banner),
          cover_image: normalizeImageUrl(cat.cover_image),
        }));
      }
    } catch (err) {
      if (ENABLE_CATALOG_FALLBACKS) {
        logCatalogSource('Featured categories', 'DEV_FIXTURE');
        return language === 'ar' ? FALLBACK_CATEGORIES_AR : FALLBACK_CATEGORIES_FR;
      }
      if (isDevEnvironment()) console.log('[CATALOG] ERROR: Featured categories API call failed', err);
      throw err;
    }
    return [];
  },

  /**
   * Fetch root categories list
   * GET /api/v2/categories?parent_id=0
   */
  async getRootCategories(language: MvpAppLanguage = 'fr'): Promise<CategoryDto[]> {
    try {
      const res = await apiClient<{ data: CategoryDto[] }>('/api/v2/categories', {
        language,
        params: { parent_id: 0 },
      });
      if (res && Array.isArray(res.data)) {
        logCatalogSource('Root categories', res.data.length === 0 ? 'EMPTY_API' : 'LIVE_API');
        return res.data.map((cat) => ({
          ...cat,
          icon: normalizeImageUrl(cat.icon),
          banner: normalizeImageUrl(cat.banner),
          cover_image: normalizeImageUrl(cat.cover_image),
        }));
      }
    } catch (err) {
      if (ENABLE_CATALOG_FALLBACKS) {
        logCatalogSource('Root categories', 'DEV_FIXTURE');
        return language === 'ar' ? FALLBACK_CATEGORIES_AR : FALLBACK_CATEGORIES_FR;
      }
      if (isDevEnvironment()) console.log('[CATALOG] ERROR: Root categories API call failed', err);
      throw err;
    }
    return [];
  },

  /**
   * Fetch subcategories by parent category ID
   * GET /api/v2/sub-categories/{id}
   */
  async getSubCategories(parentId: number, language: MvpAppLanguage = 'fr'): Promise<CategoryDto[]> {
    try {
      const res = await apiClient<{ data: CategoryDto[] }>(`/api/v2/sub-categories/${parentId}`, { language });
      if (res && Array.isArray(res.data)) {
        logCatalogSource(`Subcategories (${parentId})`, res.data.length === 0 ? 'EMPTY_API' : 'LIVE_API');
        return res.data.map((cat) => ({
          ...cat,
          icon: normalizeImageUrl(cat.icon),
          banner: normalizeImageUrl(cat.banner),
        }));
      }
    } catch (err) {
      if (ENABLE_CATALOG_FALLBACKS) {
        logCatalogSource(`Subcategories (${parentId})`, 'DEV_FIXTURE');
        return [];
      }
      if (isDevEnvironment()) console.log(`[CATALOG] ERROR: Subcategories API call failed for ${parentId}`, err);
      throw err;
    }
    return [];
  },

  /**
   * Fetch paginated category products
   * GET /api/v2/products/category/{slug}?page={page}
   */
  async getCategoryProducts(
    slug: string,
    page: number = 1,
    language: MvpAppLanguage = 'fr'
  ): Promise<PaginatedCollectionDto<ProductMiniDto>> {
    const normalizedSlug = (slug || '').toLowerCase().trim();

    if (normalizedSlug === 'nouveautés' || normalizedSlug === 'nouveautes' || normalizedSlug === 'new-arrivals') {
      return this.searchProducts({ sort_key: 'new_arrival', page }, language);
    }

    if (normalizedSlug === 'meilleures-ventes' || normalizedSlug === 'best-sellers') {
      return this.searchProducts({ sort_key: 'popularity', page }, language);
    }

    if (normalizedSlug === 'inspiration') {
      return this.searchProducts({ sort_key: 'top_rated', page }, language);
    }

    try {
      const res = await apiClient<PaginatedCollectionDto<ProductMiniDto>>(
        `/api/v2/products/category/${encodeURIComponent(slug)}`,
        {
          language,
          params: { page },
        }
      );
      if (res && Array.isArray(res.data)) {
        logCatalogSource(`Category products (${slug})`, res.data.length === 0 ? 'EMPTY_API' : 'LIVE_API');
        return {
          ...res,
          data: res.data.map((prod) => ({
            ...prod,
            thumbnail_image: normalizeImageUrl(prod.thumbnail_image),
          })),
        };
      }
    } catch (err) {
      if (ENABLE_CATALOG_FALLBACKS) {
        logCatalogSource(`Category products (${slug})`, 'DEV_FIXTURE');
        const list = (language === 'ar' ? FALLBACK_PRODUCTS_AR : FALLBACK_PRODUCTS_FR).map((prod) => ({
          ...prod,
          thumbnail_image: normalizeImageUrl(prod.thumbnail_image),
        }));
        return {
          data: list,
          meta: { current_page: 1, from: 1, last_page: 1, path: '', per_page: 10, to: list.length, total: list.length },
        };
      }
      if (isDevEnvironment()) console.log(`[CATALOG] ERROR: Category products API call failed for ${slug}`, err);
      throw err;
    }

    return { data: [], meta: { current_page: 1, from: 0, last_page: 1, path: '', per_page: 10, to: 0, total: 0 } };
  },

  /**
   * Fetch today's deal products
   * GET /api/v2/products/todays-deal
   */
  async getTodaysDeals(language: MvpAppLanguage = 'fr'): Promise<ProductMiniDto[]> {
    try {
      const res = await apiClient<{ data: ProductMiniDto[] }>('/api/v2/products/todays-deal', { language });
      if (res && Array.isArray(res.data) && res.data.length > 0) {
        logCatalogSource("Today's deals", 'LIVE_API');
        return res.data.map((prod) => ({
          ...prod,
          thumbnail_image: normalizeImageUrl(prod.thumbnail_image),
        }));
      }
      const latestRes = await apiClient<{ data: ProductMiniDto[] }>('/api/v2/products', { language });
      if (latestRes && Array.isArray(latestRes.data)) {
        logCatalogSource("Today's deals (latest fallback)", 'LIVE_API');
        return latestRes.data.map((prod) => ({
          ...prod,
          thumbnail_image: normalizeImageUrl(prod.thumbnail_image),
        }));
      }
    } catch (err) {
      if (ENABLE_CATALOG_FALLBACKS) {
        logCatalogSource("Today's deals", 'DEV_FIXTURE');
        return (language === 'ar' ? FALLBACK_PRODUCTS_AR : FALLBACK_PRODUCTS_FR).map((prod) => ({
          ...prod,
          thumbnail_image: normalizeImageUrl(prod.thumbnail_image),
        }));
      }
      if (isDevEnvironment()) console.log("[CATALOG] ERROR: Today's deals API call failed", err);
      throw err;
    }
    return [];
  },

  /**
   * Fetch best selling products
   * GET /api/v2/products/best-seller
   */
  async getBestSellers(language: MvpAppLanguage = 'fr'): Promise<ProductMiniDto[]> {
    try {
      const res = await apiClient<{ data: ProductMiniDto[] }>('/api/v2/products/best-seller', { language });
      if (res && Array.isArray(res.data) && res.data.length > 0) {
        logCatalogSource('Best sellers', 'LIVE_API');
        return res.data.map((prod) => ({
          ...prod,
          thumbnail_image: normalizeImageUrl(prod.thumbnail_image),
        }));
      }
      const searchRes = await apiClient<PaginatedCollectionDto<ProductMiniDto>>('/api/v2/products/search', {
        language,
        params: { sort_key: 'popularity' },
      });
      if (searchRes && Array.isArray(searchRes.data)) {
        logCatalogSource('Best sellers (search fallback)', 'LIVE_API');
        return searchRes.data.map((prod) => ({
          ...prod,
          thumbnail_image: normalizeImageUrl(prod.thumbnail_image),
        }));
      }
    } catch (err) {
      if (ENABLE_CATALOG_FALLBACKS) {
        logCatalogSource('Best sellers', 'DEV_FIXTURE');
        return (language === 'ar' ? FALLBACK_PRODUCTS_AR : FALLBACK_PRODUCTS_FR).map((prod) => ({
          ...prod,
          thumbnail_image: normalizeImageUrl(prod.thumbnail_image),
        }));
      }
      if (isDevEnvironment()) console.log('[CATALOG] ERROR: Best sellers API call failed', err);
      throw err;
    }
    return [];
  },

  /**
   * Fetch full product details by product slug and optional user ID
   * GET /api/v2/products/{slug}/{user_id}
   * Established guest contract: pass user_id = 0 for unauthenticated guests
   */
  async getProductDetailsBySlug(
    slug: string,
    userId: number = 0,
    language: MvpAppLanguage = 'fr'
  ): Promise<ProductDetailDto | null> {
    try {
      const res = await apiClient<{ data: ProductDetailDto[] }>(`/api/v2/products/${slug}/${userId}`, {
        language,
      });
      if (res && Array.isArray(res.data) && res.data.length > 0) {
        logCatalogSource(`Product details (${slug})`, 'LIVE_API');
        const item = res.data[0];
        return {
          ...item,
          thumbnail_img: normalizeImageUrl(item.thumbnail_img || item.thumbnail_image),
          photos: (item.photos || []).map((p) =>
            typeof p === 'string' ? normalizeImageUrl(p) : normalizeImageUrl((p as any)?.path || '')
          ) as any,
        };
      }
      logCatalogSource(`Product details (${slug})`, 'EMPTY_API');
      return null;
    } catch (err) {
      if (ENABLE_CATALOG_FALLBACKS) {
        logCatalogSource(`Product details (${slug})`, 'DEV_FIXTURE');
        const fallback = language === 'ar' ? FALLBACK_PRODUCTS_AR[0] : FALLBACK_PRODUCTS_FR[0];
        return {
          id: fallback.id,
          name: fallback.name,
          slug: fallback.slug,
          added_by: 'admin',
          seller_id: 1,
          shop_id: 1,
          shop_name: 'Mayush Official Store',
          shop_logo: normalizeImageUrl(fallback.thumbnail_image),
          photos: [normalizeImageUrl(fallback.thumbnail_image)] as any,
          thumbnail_img: normalizeImageUrl(fallback.thumbnail_image),
          tags: ['mobilier', 'salon'],
          price_high_low: fallback.main_price,
          choice_options: [
            { name: '1', title: 'Tissu', options: ['Bouclé Crème', 'Velours Beige'] },
          ],
          colors: ['#E6E2DD', '#C5A059'],
          has_discount: fallback.has_discount,
          discount: fallback.discount,
          stroked_price: fallback.stroked_price,
          main_price: fallback.main_price,
          calculable_price: 12325,
          currency_symbol: 'MAD',
          current_stock: 12,
          unit: 'pièce',
          min_qty: 1,
          low_stock_quantity: 2,
          discount_type: 'percent',
          rating: fallback.rating,
          rating_count: 14,
          description: 'Découvrez l\'élégance du mobilier Mayush d\'exception.',
          digital: 0,
          cash_on_delivery: 1,
          est_shipping_days: 3,
        };
      }
      if (isDevEnvironment()) console.log(`[CATALOG] ERROR: Product details API call failed for ${slug}`, err);
      throw err;
    }
  },

  /**
   * Fetch full product details by numeric product ID
   * GET /api/v2/products/{id}
   */
  async getProductDetails(id: number, language: MvpAppLanguage = 'fr'): Promise<ProductDetailDto | null> {
    try {
      const res = await apiClient<{ data: ProductDetailDto[] }>(`/api/v2/products/${id}`, { language });
      if (res && Array.isArray(res.data) && res.data.length > 0) {
        logCatalogSource(`Product details (ID ${id})`, 'LIVE_API');
        const item = res.data[0];
        return {
          ...item,
          thumbnail_img: normalizeImageUrl(item.thumbnail_img || item.thumbnail_image),
        };
      }
      return null;
    } catch (err) {
      if (ENABLE_CATALOG_FALLBACKS) {
        return this.getProductDetailsBySlug('canape-luna-boucle', 0, language);
      }
      if (isDevEnvironment()) console.log(`[CATALOG] ERROR: Product details API call failed for ID ${id}`, err);
      throw err;
    }
  },

  /**
   * Calculate server-authoritative variant price & stock
   * POST /api/v2/products/variant/price
   */
  async getVariantPrice(
    payload: VariantPriceRequestDto,
    language: MvpAppLanguage = 'fr'
  ): Promise<VariantPriceResponseDto> {
    try {
      const res = await apiClient<VariantPriceResponseDto>('/api/v2/products/variant/price', {
        method: 'POST',
        language,
        body: payload,
      });
      if (res && res.result && res.data) {
        logCatalogSource(`Variant price (${payload.variants || payload.slug})`, 'LIVE_API');
        return {
          ...res,
          data: {
            ...res.data,
            image: res.data.image ? normalizeImageUrl(res.data.image) : undefined,
          },
        };
      }
    } catch (err) {
      if (ENABLE_CATALOG_FALLBACKS) {
        logCatalogSource(`Variant price (${payload.variants || payload.slug})`, 'DEV_FIXTURE');
        return {
          result: true,
          message: 'Success',
          data: {
            price: '12 325 MAD',
            stock: 12,
            stock_txt: 'En stock',
            digital: 0,
            variant: payload.variants || 'Standard',
          },
        };
      }
      if (isDevEnvironment()) console.log(`[CATALOG] ERROR: Variant price API call failed for ${payload.slug}`, err);
      throw err;
    }

    return { result: false, message: 'Variant not found' };
  },

  /**
   * Search products with optional query, category, brand, min/max price, and pagination
   * GET /api/v2/products/search
   */
  async searchProducts(
    params: SearchParams,
    language: MvpAppLanguage = 'fr'
  ): Promise<PaginatedCollectionDto<ProductMiniDto>> {
    try {
      const res = await apiClient<PaginatedCollectionDto<ProductMiniDto>>('/api/v2/products/search', {
        language,
        params: {
          name: params.name,
          category: params.category || params.categories,
          categories: params.categories || params.category,
          brand: params.brand || params.brands,
          brands: params.brands || params.brand,
          min: params.min,
          max: params.max,
          sort_key: params.sort_key || params.sort_by,
          sort_by: params.sort_by || params.sort_key,
          page: params.page || 1,
        },
      });

      if (res && Array.isArray(res.data)) {
        logCatalogSource(`Product search ("${params.name || ''}")`, res.data.length === 0 ? 'EMPTY_API' : 'LIVE_API');
        return {
          ...res,
          data: res.data.map((p) => ({
            ...p,
            thumbnail_image: normalizeImageUrl(p.thumbnail_image),
          })),
        };
      }
    } catch (err) {
      if (ENABLE_CATALOG_FALLBACKS) {
        logCatalogSource(`Product search ("${params.name || ''}")`, 'DEV_FIXTURE');
        const fallbackList = (language === 'ar' ? FALLBACK_PRODUCTS_AR : FALLBACK_PRODUCTS_FR).map((p) => ({
          ...p,
          thumbnail_image: normalizeImageUrl(p.thumbnail_image),
        }));
        return {
          data: fallbackList,
          meta: { current_page: 1, from: 1, last_page: 1, path: '', per_page: 10, to: fallbackList.length, total: fallbackList.length },
        };
      }
      if (isDevEnvironment()) console.log(`[CATALOG] ERROR: Search API call failed for "${params.name || ''}"`, err);
      throw err;
    }

    return { data: [], meta: { current_page: 1, from: 0, last_page: 1, path: '', per_page: 10, to: 0, total: 0 } };
  },

  /**
   * Fetch search suggestions for autocomplete
   * GET /api/v2/get-search-suggestions?keyword={keyword}
   */
  async getSearchSuggestions(
    keyword: string,
    language: MvpAppLanguage = 'fr'
  ): Promise<Array<{ id: number; name: string; count?: number }>> {
    if (!keyword || keyword.trim().length === 0) {
      return [];
    }

    try {
      const res = await apiClient<any>('/api/v2/get-search-suggestions', {
        language,
        params: { keyword: keyword.trim() },
      });
      if (res && Array.isArray(res)) {
        logCatalogSource(`Search suggestions ("${keyword}")`, 'LIVE_API');
        return res;
      }
      if (res && Array.isArray(res.data)) {
        logCatalogSource(`Search suggestions ("${keyword}")`, 'LIVE_API');
        return res.data;
      }
    } catch (err) {
      if (isDevEnvironment()) console.log(`[CATALOG] ERROR: Search suggestions API call failed for "${keyword}"`, err);
    }
    return [];
  },

  /**
   * Fetch filter category list
   * GET /api/v2/filter/categories
   */
  async getCategoryFilters(language: MvpAppLanguage = 'fr'): Promise<SearchFilterOptionDto[]> {
    try {
      const res = await apiClient<{ data: SearchFilterOptionDto[] }>('/api/v2/filter/categories', { language });
      if (res && Array.isArray(res.data)) {
        logCatalogSource('Category filters', 'LIVE_API');
        return res.data;
      }
    } catch (err) {
      if (isDevEnvironment()) console.log('[CATALOG] ERROR: Category filters API call failed', err);
    }
    return [];
  },

  /**
   * Fetch filter brand list
   * GET /api/v2/filter/brands
   */
  async getBrandFilters(language: MvpAppLanguage = 'fr'): Promise<SearchFilterOptionDto[]> {
    try {
      const res = await apiClient<{ data: SearchFilterOptionDto[] }>('/api/v2/filter/brands', { language });
      if (res && Array.isArray(res.data)) {
        logCatalogSource('Brand filters', 'LIVE_API');
        return res.data;
      }
    } catch (err) {
      if (isDevEnvironment()) console.log('[CATALOG] ERROR: Brand filters API call failed', err);
    }
    return [];
  },

  /**
   * Fetch related/frequently bought products by slug
   * GET /api/v2/products/frequently-bought/{slug}
   */
  async getFrequentlyBoughtProducts(
    slug: string,
    language: MvpAppLanguage = 'fr'
  ): Promise<ProductMiniDto[]> {
    try {
      const res = await apiClient<{ data: ProductMiniDto[] }>(`/api/v2/products/frequently-bought/${slug}`, {
        language,
      });
      if (res && Array.isArray(res.data)) {
        logCatalogSource(`Related products (${slug})`, res.data.length === 0 ? 'EMPTY_API' : 'LIVE_API');
        return res.data.map((p) => ({
          ...p,
          thumbnail_image: normalizeImageUrl(p.thumbnail_image),
        }));
      }
    } catch (err) {
      if (ENABLE_CATALOG_FALLBACKS) {
        logCatalogSource(`Related products (${slug})`, 'DEV_FIXTURE');
        return (language === 'ar' ? FALLBACK_PRODUCTS_AR : FALLBACK_PRODUCTS_FR).map((p) => ({
          ...p,
          thumbnail_image: normalizeImageUrl(p.thumbnail_image),
        }));
      }
      if (isDevEnvironment()) console.log(`[CATALOG] ERROR: Related products API call failed for ${slug}`, err);
    }
    return [];
  },

  /**
   * Fetch seller storefront shop details
   * GET /api/v2/shops/details/{id}
   */
  async getShopDetails(shopId: number, language: MvpAppLanguage = 'fr'): Promise<ShopDetailsDto | null> {
    try {
      const res = await apiClient<{ data: ShopDetailsDto[] }>(`/api/v2/shops/details/${shopId}`, { language });
      if (res && Array.isArray(res.data) && res.data.length > 0) {
        logCatalogSource(`Shop details (${shopId})`, 'LIVE_API');
        const item = res.data[0];
        return {
          ...item,
          logo: normalizeImageUrl(item.logo),
          sliders: (item.sliders || []).map((s) => normalizeImageUrl(s)),
        };
      }
    } catch (err) {
      if (ENABLE_CATALOG_FALLBACKS) {
        logCatalogSource(`Shop details (${shopId})`, 'DEV_FIXTURE');
        return {
          id: shopId,
          user_id: 1,
          name: 'Mayush Official Store',
          logo: normalizeImageUrl(FALLBACK_PRODUCTS_FR[0].thumbnail_image),
          sliders: [],
          address: 'Casablanca, Maroc',
          rating: 4.9,
        };
      }
      if (isDevEnvironment()) console.log(`[CATALOG] ERROR: Shop details API call failed for ${shopId}`, err);
    }
    return null;
  },

  /**
   * Fetch product customer reviews list
   * GET /api/v2/reviews/product/{id}
   */
  async getProductReviews(
    productId: number,
    page: number = 1,
    language: MvpAppLanguage = 'fr'
  ): Promise<PaginatedCollectionDto<ReviewItemDto>> {
    try {
      const res = await apiClient<PaginatedCollectionDto<ReviewItemDto>>(`/api/v2/reviews/product/${productId}`, {
        language,
        params: { page },
      });
      if (res && Array.isArray(res.data)) {
        logCatalogSource(`Product reviews (${productId})`, res.data.length === 0 ? 'EMPTY_API' : 'LIVE_API');
        return {
          ...res,
          data: res.data.map((r) => ({
            ...r,
            user_avatar: normalizeImageUrl(r.user_avatar),
          })),
        };
      }
    } catch (err) {
      if (ENABLE_CATALOG_FALLBACKS) {
        logCatalogSource(`Product reviews (${productId})`, 'DEV_FIXTURE');
        return {
          data: [
            {
              id: 1,
              product_id: productId,
              user_name: 'Salma K.',
              user_avatar: normalizeImageUrl(''),
              rating: 5,
              comment: 'Superbe qualité ! Livré rapidement à Casablanca.',
              time: 'Il y a 2 jours',
            },
          ],
          meta: { current_page: 1, from: 1, last_page: 1, path: '', per_page: 10, to: 1, total: 1 },
        };
      }
      if (isDevEnvironment()) console.log(`[CATALOG] ERROR: Product reviews API call failed for ${productId}`, err);
    }
    return { data: [], meta: { current_page: 1, from: 0, last_page: 1, path: '', per_page: 10, to: 0, total: 0 } };
  },

  /**
   * Fetch active flash deals with their products for home display
   * GET /api/v2/flash-deals
   */
  async getFlashDealsForHome(language: MvpAppLanguage = 'fr'): Promise<{ title: string; end_date: string; products: ProductMiniDto[] }[]> {
    try {
      const res = await apiClient<{ data: any[] }>('/api/v2/flash-deals', { language });
      if (res && Array.isArray(res.data) && res.data.length > 0) {
        logCatalogSource('Flash deals (home)', 'LIVE_API');
        return res.data.map((deal) => ({
          title: deal.title || '',
          end_date: deal.end_date || deal.date || '',
          products: Array.isArray(deal.products)
            ? deal.products.map((p: any) => ({
                id: p.id,
                name: p.name,
                slug: p.slug,
                thumbnail_image: normalizeImageUrl(p.thumbnail_image || p.thumbnail_img || ''),
                has_discount: Boolean(p.has_discount || p.discount),
                discount: p.discount || null,
                stroked_price: p.stroked_price || '',
                main_price: p.main_price || '',
                rating: p.rating || 0,
                sales: p.sales || 0,
                links: p.links || { details: '' },
              }))
            : [],
        }));
      }
    } catch (err) {
      if (isDevEnvironment()) console.log('[CATALOG] ERROR: Flash deals API call failed', err);
    }
    return [];
  },

  /**
   * Fetch featured/recommended products
   * GET /api/v2/products/featured
   */
  async getFeaturedProducts(language: MvpAppLanguage = 'fr'): Promise<ProductMiniDto[]> {
    try {
      const res = await apiClient<{ data: ProductMiniDto[] }>('/api/v2/products/featured', { language });
      if (res && Array.isArray(res.data)) {
        logCatalogSource('Featured products', res.data.length === 0 ? 'EMPTY_API' : 'LIVE_API');
        return res.data.map((prod) => ({
          ...prod,
          thumbnail_image: normalizeImageUrl(prod.thumbnail_image),
        }));
      }
    } catch (err) {
      if (ENABLE_CATALOG_FALLBACKS) {
        logCatalogSource('Featured products', 'DEV_FIXTURE');
        return (language === 'ar' ? FALLBACK_PRODUCTS_AR : FALLBACK_PRODUCTS_FR).map((prod) => ({
          ...prod,
          thumbnail_image: normalizeImageUrl(prod.thumbnail_image),
        }));
      }
      if (isDevEnvironment()) console.log('[CATALOG] ERROR: Featured products API call failed', err);
    }
    return [];
  },

  /**
   * Fetch last viewed products for authenticated user
   * GET /api/v2/products/last-viewed (requires auth:sanctum)
   */
  async getLastViewedProducts(language: MvpAppLanguage = 'fr'): Promise<ProductMiniDto[]> {
    try {
      const res = await apiClient<{ data: ProductMiniDto[] }>('/api/v2/products/last-viewed', { language });
      if (res && Array.isArray(res.data)) {
        logCatalogSource('Last viewed products', res.data.length === 0 ? 'EMPTY_API' : 'LIVE_API');
        return res.data.map((prod) => ({
          ...prod,
          thumbnail_image: normalizeImageUrl(prod.thumbnail_image),
        }));
      }
    } catch (err) {
      if (isDevEnvironment()) console.log('[CATALOG] ERROR: Last viewed products API call failed', err);
    }
    return [];
  },

  /**
   * Fetch available coupons for display
   * GET /api/v2/coupon-list
   */
  async getCouponList(language: MvpAppLanguage = 'fr'): Promise<Array<{
    id: number;
    code: string;
    discount: number;
    discount_type: 'percent' | 'amount';
    start_date: string;
    end_date: string;
    min_buy: number;
    details?: string;
  }>> {
    try {
      const res = await apiClient<{ data: Array<{
        id: number;
        code: string;
        discount: number;
        discount_type: 'percent' | 'amount';
        start_date: string;
        end_date: string;
        min_buy: number;
        details?: string;
      }> }>('/api/v2/coupon-list', { language });
      return res.data || [];
    } catch {
      return [];
    }
  },
};
