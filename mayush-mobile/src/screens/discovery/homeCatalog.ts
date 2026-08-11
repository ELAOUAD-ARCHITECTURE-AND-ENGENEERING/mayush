import { ProductMiniDto } from '../../contracts/api/dto';
import { formatStorePrice } from '../../config/currency';

export type HomeCatalogProductId = 101 | 102 | 103 | 104 | 201 | 202 | 203 | 204 | 205 | 206 | 207 | 208;

const product = (id: HomeCatalogProductId, name: string, price: string, rating = 0, sales = 0): ProductMiniDto => ({
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

/** Canonical deterministic product identities rendered by both Home variants. */
export const HOME_PRODUCT_CATALOG: readonly ProductMiniDto[] = [
  product(101, 'Fauteuil Luna', formatStorePrice('589,00')),
  product(102, 'Buffet Kyoto', formatStorePrice('1 249,00')),
  product(103, 'Table basse Ève', formatStorePrice('479,00')),
  product(104, 'Suspension Nori', formatStorePrice('199,00')),
  product(201, 'Canapé modulable Solis', formatStorePrice('1 890,00'), 5, 128),
  product(202, 'Table à manger Aria', formatStorePrice('1 390,00'), 5, 96),
  product(203, 'Chaise Velours Élégance', formatStorePrice('189,00'), 5, 75),
  product(204, 'Étagère Linea', formatStorePrice('329,00'), 5, 64),
  product(205, 'Fauteuil Lounge Élégance', formatStorePrice('2 490,00'), 5, 48),
  product(206, 'Table à manger Moderne', formatStorePrice('599,00'), 5, 37),
  product(207, 'Suspension Nordique', formatStorePrice('89,00'), 5, 29),
  product(208, 'Étagère Design Maya', formatStorePrice('189,00'), 5, 31),
];

export const HOME_NEW_ARRIVAL_IDS: readonly HomeCatalogProductId[] = [101, 102, 103, 104];
export const HOME_BEST_SELLER_IDS: readonly HomeCatalogProductId[] = [201, 202, 203, 204];
export const HOME_PERSONALIZED_FALLBACK_IDS: readonly HomeCatalogProductId[] = [205, 206, 207, 208];
export const HOME_RECENT_FALLBACK_IDS: readonly HomeCatalogProductId[] = [203, 104, 102, 101];
export const RECENTLY_VIEWED_DATA_SOURCE = 'deterministic_catalog_fallback' as const;

export const getHomeCatalogProductById = (productId: number): ProductMiniDto | undefined =>
  HOME_PRODUCT_CATALOG.find((item) => item.id === productId);

export interface HomeRecommendationInput {
  wishlistProductIds?: readonly number[];
  recentProductIds?: readonly number[];
  cartProductIds?: readonly number[];
  limit?: number;
}

/**
 * A stable local projection, not a recommendation engine. Existing buyer signals
 * are preferred only when they resolve to this catalog; canonical fallback IDs fill
 * every remaining slot in a repeatable order.
 */
export const getHomeRecommendationIds = ({
  wishlistProductIds = [],
  recentProductIds = [],
  cartProductIds = [],
  limit = 4,
}: HomeRecommendationInput = {}): number[] => {
  const validIds = new Set(HOME_PRODUCT_CATALOG.map((item) => item.id));
  const ordered = [
    ...wishlistProductIds,
    ...recentProductIds,
    ...cartProductIds,
    ...HOME_PERSONALIZED_FALLBACK_IDS,
    ...HOME_BEST_SELLER_IDS,
    ...HOME_NEW_ARRIVAL_IDS,
  ];
  const unique: number[] = [];
  ordered.forEach((id) => {
    if (validIds.has(id) && !unique.includes(id) && unique.length < limit) unique.push(id);
  });
  return unique;
};

export const resolveHomeProducts = (ids: readonly number[]): ProductMiniDto[] =>
  ids.map(getHomeCatalogProductById).filter((item): item is ProductMiniDto => Boolean(item));

export const getRecentlyViewedFallbackProducts = (): ProductMiniDto[] => resolveHomeProducts(HOME_RECENT_FALLBACK_IDS);

export type HomeRuntimeVariant = 'generic' | 'personalized';

export const resolveHomeRuntimeVariant = (isAuthenticated: boolean): HomeRuntimeVariant =>
  isAuthenticated ? 'personalized' : 'generic';

export const getAuthenticatedHomeFirstName = (user: { fullName?: string } | null): string | null => {
  const firstName = user?.fullName?.trim().split(/\s+/)[0];
  return firstName || null;
};
