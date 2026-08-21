# Critical Gaps Backend Wiring Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Wire all remaining hardcoded data in the mobile app to live Laravel backend APIs, closing the 14 critical functionality gaps identified in the design-to-code audit.

**Architecture:** Add new service methods to `catalogService.ts` and a new `notificationService.ts` for missing API calls (brands, flash deals for home, notifications, recently viewed). Then replace hardcoded constants in `HomeScreen.tsx` with API-fetched state. Finally wire account features (profile photo, active sessions, profile completion) to their respective backend endpoints.

**Tech Stack:** React Native (Expo SDK 57), TypeScript, Laravel 10.x REST API, AsyncStorage, `apiClient<T>()` pattern

---

## File Structure

### New files:
- `src/services/api/notificationService.ts` — notification count + summary API
- `src/services/api/brandService.ts` — brands/partners API

### Modified files:
- `src/services/api/catalogService.ts` — add `getFlashDealsForHome()`, `getFeaturedProducts()`, `getLastViewedProducts()`, `getTopBrands()`
- `src/screens/discovery/HomeScreen.tsx` — replace all hardcoded constants with API state
- `src/screens/discovery/RecentlyViewedScreen.tsx` — wire to `lastViewedProducts()` API
- `src/screens/search/SearchLandingScreen.tsx` — wire trending categories to API
- `src/screens/commerce/AccountScreen.tsx` — wire profile completion % and photo upload
- `src/screens/account/ActiveSessionsScreen.tsx` — wire to sessions API
- `src/commerce/authState.ts` — add profile photo upload method

---

## Task 1: Add notification service for unread count

**Files:**
- Create: `src/services/api/notificationService.ts`
- Modify: `src/screens/discovery/HomeScreen.tsx:45-50` (notification badge)

- [ ] **Step 1: Create notification service**

Create the file `src/services/api/notificationService.ts`:

```typescript
import { apiClient } from '../../api';

export interface NotificationSummaryDto {
  unread_count: number;
  total: number;
}

export const notificationService = {
  /**
   * GET /api/v2/unread-notifications
   * Requires auth:sanctum
   */
  async getUnreadCount(): Promise<number> {
    try {
      const res = await apiClient<{ count?: number; data?: any[] }>('/api/v2/unread-notifications');
      if (res && typeof res.count === 'number') return res.count;
      if (res && Array.isArray(res.data)) return res.data.length;
      return 0;
    } catch {
      return 0;
    }
  },

  /**
   * GET /api/v2/notifications/summary
   */
  async getSummary(): Promise<NotificationSummaryDto> {
    try {
      const res = await apiClient<NotificationSummaryDto>('/api/v2/notifications/summary');
      return { unread_count: res?.unread_count ?? 0, total: res?.total ?? 0 };
    } catch {
      return { unread_count: 0, total: 0 };
    }
  },
};
```

- [ ] **Step 2: Verify TypeScript compiles**

Run: `cd C:/laragon/www/mayush/mayush-mobile && npx tsc --noEmit --pretty 2>&1 | head -30`
Expected: No new errors from `notificationService.ts`

- [ ] **Step 3: Commit**

```bash
git add src/services/api/notificationService.ts
git commit -m "feat(api): add notificationService for unread count and summary"
```

---

## Task 2: Add brand service for partners/top brands

**Files:**
- Create: `src/services/api/brandService.ts`

- [ ] **Step 1: Create brand service**

Create the file `src/services/api/brandService.ts`:

```typescript
import { apiClient } from '../../api';
import { normalizeImageUrl } from '../../contracts/mappers/imageNormalizer';
import { MvpAppLanguage } from '../../contracts/api/dto';

export interface BrandDto {
  id: number;
  name: string;
  logo: string;
  slug?: string;
}

export const brandService = {
  /**
   * GET /api/v2/brands/top
   */
  async getTopBrands(language: MvpAppLanguage = 'fr'): Promise<BrandDto[]> {
    try {
      const res = await apiClient<{ data: BrandDto[] }>('/api/v2/brands/top', { language });
      if (res && Array.isArray(res.data)) {
        return res.data.map((b) => ({
          ...b,
          logo: normalizeImageUrl(b.logo),
        }));
      }
    } catch {
      // Fail silently — partners section is non-critical
    }
    return [];
  },

  /**
   * GET /api/v2/all-brands
   */
  async getAllBrands(language: MvpAppLanguage = 'fr'): Promise<BrandDto[]> {
    try {
      const res = await apiClient<{ data: BrandDto[] }>('/api/v2/all-brands', { language });
      if (res && Array.isArray(res.data)) {
        return res.data.map((b) => ({
          ...b,
          logo: normalizeImageUrl(b.logo),
        }));
      }
    } catch {
      // Fail silently
    }
    return [];
  },
};
```

- [ ] **Step 2: Verify TypeScript compiles**

Run: `cd C:/laragon/www/mayush/mayush-mobile && npx tsc --noEmit --pretty 2>&1 | head -30`
Expected: No new errors

- [ ] **Step 3: Commit**

```bash
git add src/services/api/brandService.ts
git commit -m "feat(api): add brandService for top brands and all brands"
```

---

## Task 3: Add catalogService methods for flash deals, featured products, and last viewed

**Files:**
- Modify: `src/services/api/catalogService.ts`

- [ ] **Step 1: Add three new methods to catalogService**

At the end of the `catalogService` object (before the closing `};`), add:

```typescript
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
```

- [ ] **Step 2: Verify TypeScript compiles**

Run: `cd C:/laragon/www/mayush/mayush-mobile && npx tsc --noEmit --pretty 2>&1 | head -30`
Expected: No new errors

- [ ] **Step 3: Commit**

```bash
git add src/services/api/catalogService.ts
git commit -m "feat(catalog): add getFlashDealsForHome, getFeaturedProducts, getLastViewedProducts"
```

---

## Task 4: Wire HomeScreen — replace all hardcoded product data with API state

**Files:**
- Modify: `src/screens/discovery/HomeScreen.tsx`

This is the largest task. It replaces 8 hardcoded constants with live API data.

- [ ] **Step 1: Add new imports and state variables**

At the top of HomeScreen.tsx, after the existing imports (~line 16), add:

```typescript
import { notificationService } from '../../services/api/notificationService';
import { brandService, BrandDto } from '../../services/api/brandService';
```

In the component body, after the existing state declarations (~line 419), add:

```typescript
  const [flashDeals, setFlashDeals] = useState<ProductMiniDto[]>([]);
  const [flashDealEndDate, setFlashDealEndDate] = useState<string>('');
  const [recommendedProducts, setRecommendedProducts] = useState<ProductMiniDto[]>([]);
  const [recentlyViewed, setRecentlyViewed] = useState<ProductMiniDto[]>([]);
  const [topBrands, setTopBrands] = useState<BrandDto[]>([]);
  const [notificationCount, setNotificationCount] = useState(0);
  const [flashCountdown, setFlashCountdown] = useState('');
```

- [ ] **Step 2: Add API fetch calls to the useEffect**

Inside the existing `useEffect` (after the bestSellers fetch block, ~line 514), add these fetch calls:

```typescript
    // Fetch flash deals for home
    catalogService
      .getFlashDealsForHome(language)
      .then((deals) => {
        if (mounted && deals.length > 0) {
          setFlashDeals(deals[0].products);
          setFlashDealEndDate(deals[0].end_date);
        }
      })
      .catch(() => {});

    // Fetch recommended/featured products
    catalogService
      .getFeaturedProducts(language)
      .then((res) => {
        if (mounted && res.length > 0) setRecommendedProducts(res);
      })
      .catch(() => {});

    // Fetch recently viewed (authenticated only)
    if (isAuthenticated) {
      catalogService
        .getLastViewedProducts(language)
        .then((res) => {
          if (mounted && res.length > 0) setRecentlyViewed(res);
        })
        .catch(() => {});

      notificationService
        .getUnreadCount()
        .then((count) => {
          if (mounted) setNotificationCount(count);
        })
        .catch(() => {});
    }

    // Fetch top brands/partners
    brandService
      .getTopBrands(language)
      .then((res) => {
        if (mounted && res.length > 0) setTopBrands(res);
      })
      .catch(() => {});
```

- [ ] **Step 3: Add flash deal countdown timer**

After the useEffect block, add a new useEffect for the live countdown:

```typescript
  useEffect(() => {
    if (!flashDealEndDate) return;
    const updateCountdown = () => {
      const end = new Date(flashDealEndDate).getTime();
      const now = Date.now();
      const diff = end - now;
      if (diff <= 0) {
        setFlashCountdown('00h : 00m : 00s');
        return;
      }
      const h = Math.floor(diff / 3600000);
      const m = Math.floor((diff % 3600000) / 60000);
      const s = Math.floor((diff % 60000) / 1000);
      setFlashCountdown(`${String(h).padStart(2, '0')}h : ${String(m).padStart(2, '0')}m : ${String(s).padStart(2, '0')}s`);
    };
    updateCountdown();
    const interval = setInterval(updateCountdown, 1000);
    return () => clearInterval(interval);
  }, [flashDealEndDate]);
```

- [ ] **Step 4: Replace hardcoded data references in the render**

Replace all references to hardcoded constants throughout the JSX render:

1. **Notification badge** — find the hardcoded `"3"` in the bell badge area (~line 697) and replace with:
   ```typescript
   {notificationCount > 0 && (
     <View style={styles.notifBadge}>
       <MayushText variant="badge" color={colors.surface.white}>{notificationCount > 99 ? '99+' : String(notificationCount)}</MayushText>
     </View>
   )}
   ```

2. **Logged hero slider** — find where `LOGGED_IN_HERO_IMAGE` is used as a static image (~line 755) and replace with the API sliders (same as guest view):
   ```typescript
   {sliders.length > 0 ? (
     <Image source={{ uri: sliders[activeHeroIndex % sliders.length]?.photo }} style={[styles.heroImage, { width: contentWidth }]} resizeMode="cover" />
   ) : (
     <Image source={LOGGED_IN_HERO_IMAGE} style={[styles.heroImage, { width: contentWidth }]} resizeMode="cover" />
   )}
   ```

3. **Flash deals products** — find where `LOGGED_IN_FLASH_DEALS_PRODUCTS` is used and replace with:
   ```typescript
   {(flashDeals.length > 0 ? flashDeals : []).map((product) => (
   ```

4. **Flash deal countdown** — find the hardcoded `"12h : 45m : 30s"` and replace with:
   ```typescript
   {flashCountdown || '00h : 00m : 00s'}
   ```

5. **New arrivals (logged)** — find where `LOGGED_IN_NEW_ARRIVALS` is used and replace with `newArrivals` (already fetched and wired — verify no remaining reference to the constant).

6. **Best sellers (logged)** — find where `LOGGED_IN_BEST_SELLERS` is used and replace with `bestSellers` (already fetched and wired — verify no remaining reference).

7. **Recommended products** — find where `LOGGED_IN_RECOMMENDED_PRODUCTS` or `GUEST_RECOMMENDED_PRODUCTS` is used and replace with:
   ```typescript
   {(recommendedProducts.length > 0 ? recommendedProducts : []).map((product) => (
   ```

8. **Recently viewed** — find where `RECENTLY_VIEWED_ITEMS` is used and replace with:
   ```typescript
   {(recentlyViewed.length > 0 ? recentlyViewed : []).map((product) => (
   ```
   Adapt the rendering to use `ProductMiniDto` fields (`product.name`, `product.thumbnail_image`) instead of `item.title`, `item.image`.

9. **Categories (logged view)** — find where `LOGGED_IN_CATEGORIES_DATA` is used and replace with `displayCategories` (already computed from API data, same as guest view).

10. **Partners** — find where `PARTNERS_DATA` is used and replace with:
    ```typescript
    {(topBrands.length > 0 ? topBrands : PARTNERS_DATA.map((p) => ({ id: 0, name: p.name, logo: '', slug: '' }))).map((brand) => (
      <View key={brand.id || brand.name} style={styles.partnerItem}>
        {brand.logo ? (
          <Image source={{ uri: brand.logo }} style={styles.partnerLogo} resizeMode="contain" />
        ) : (
          <MayushText variant="strongBody" color={colors.brand.navy900}>{brand.name}</MayushText>
        )}
      </View>
    ))}
    ```

- [ ] **Step 5: Remove unused hardcoded constants**

Delete these constants from the top of the file (they're no longer referenced):
- `LOGGED_IN_FLASH_DEALS_PRODUCTS` (lines 158-199)
- `LOGGED_IN_NEW_ARRIVALS` (lines 201-234)
- `LOGGED_IN_BEST_SELLERS` (lines 236-277)
- `LOGGED_IN_RECOMMENDED_PRODUCTS` (lines 52-93)
- `GUEST_RECOMMENDED_PRODUCTS` (lines 95-146)
- `RECENTLY_VIEWED_ITEMS` (lines 45-50)
- `LOGGED_IN_CATEGORIES_DATA` (lines 148-156)

Keep `COLLECTIONS_VEDETTES_DATA` as fallback for empty collections.
Keep `PARTNERS_DATA` as fallback for empty brands.
Keep `CUSTOMER_REVIEWS_DATA` — no reviews aggregate API exists yet.
Keep `SERVICES_DATA`, `AMBIANCES_DATA`, `FOOTER_TRUST_BADGES` — static marketing content.

- [ ] **Step 6: Verify TypeScript compiles**

Run: `cd C:/laragon/www/mayush/mayush-mobile && npx tsc --noEmit --pretty 2>&1 | head -30`
Expected: No new errors. Fix any type mismatches from the ProductMiniDto render adaptation.

- [ ] **Step 7: Commit**

```bash
git add src/screens/discovery/HomeScreen.tsx
git commit -m "feat(home): wire flash deals, recommended, recently viewed, brands, notifications to live API"
```

---

## Task 5: Wire RecentlyViewedScreen to backend API

**Files:**
- Modify: `src/screens/discovery/RecentlyViewedScreen.tsx`

- [ ] **Step 1: Read the current file to understand its structure**

Read `src/screens/discovery/RecentlyViewedScreen.tsx` in full.

- [ ] **Step 2: Add API fetch to RecentlyViewedScreen**

Import catalogService and add a useEffect to fetch last viewed products:

```typescript
import { catalogService } from '../../services/api/catalogService';
import { ProductMiniDto } from '../../contracts/api/dto';
```

Add state and fetch:

```typescript
const [products, setProducts] = useState<ProductMiniDto[]>([]);
const [loading, setLoading] = useState(true);

useEffect(() => {
  setLoading(true);
  catalogService
    .getLastViewedProducts(language)
    .then((res) => {
      setProducts(res);
      setLoading(false);
    })
    .catch(() => setLoading(false));
}, [language]);
```

Replace any hardcoded items with the `products` array. Add a loading skeleton and empty state.

- [ ] **Step 3: Verify TypeScript compiles**

Run: `cd C:/laragon/www/mayush/mayush-mobile && npx tsc --noEmit --pretty 2>&1 | head -30`

- [ ] **Step 4: Commit**

```bash
git add src/screens/discovery/RecentlyViewedScreen.tsx
git commit -m "feat(recently-viewed): wire to /api/v2/products/last-viewed endpoint"
```

---

## Task 6: Wire SearchLandingScreen trending categories to API

**Files:**
- Modify: `src/screens/search/SearchLandingScreen.tsx`

- [ ] **Step 1: Read the current file**

Read `src/screens/search/SearchLandingScreen.tsx` in full.

- [ ] **Step 2: Add API fetch for trending categories**

Import and fetch featured categories from catalogService:

```typescript
import { catalogService } from '../../services/api/catalogService';
```

Add state for trending categories and fetch in useEffect:

```typescript
const [trendingCategories, setTrendingCategories] = useState<CategoryDto[]>([]);

useEffect(() => {
  catalogService
    .getFeaturedCategories(language)
    .then((res) => {
      if (res.length > 0) setTrendingCategories(res.slice(0, 6));
    })
    .catch(() => {});
}, [language]);
```

Replace any hardcoded trending categories data with `trendingCategories`.

- [ ] **Step 3: Verify TypeScript compiles**

Run: `cd C:/laragon/www/mayush/mayush-mobile && npx tsc --noEmit --pretty 2>&1 | head -30`

- [ ] **Step 4: Commit**

```bash
git add src/screens/search/SearchLandingScreen.tsx
git commit -m "feat(search): wire trending categories to catalogService API"
```

---

## Task 7: Wire profile photo upload to backend

**Files:**
- Modify: `src/commerce/authState.ts`
- Modify: `src/screens/commerce/AccountScreen.tsx` or `src/screens/account/EditProfileScreen.tsx`

- [ ] **Step 1: Add updateProfilePhoto method to authState**

In `src/commerce/authState.ts`, add a method to the AuthStateManager class:

```typescript
public async updateProfilePhoto(imageUri: string): Promise<boolean> {
  try {
    const formData = new FormData();
    formData.append('photo', {
      uri: imageUri,
      type: 'image/jpeg',
      name: 'profile.jpg',
    } as any);

    const res = await apiClient<{ result: boolean; path?: string }>('/api/v2/profile/update-image', {
      method: 'POST',
      body: formData,
      headers: { 'Content-Type': 'multipart/form-data' },
    });

    if (res?.result && res.path) {
      const user = this.getUser();
      if (user) {
        this.currentUser = { ...user, avatar: res.path };
        this.notify();
        void this.persist();
      }
      return true;
    }
    return false;
  } catch {
    return false;
  }
}
```

- [ ] **Step 2: Wire the photo upload in EditProfileScreen**

In `src/screens/account/EditProfileScreen.tsx`, find the avatar/photo section and wire the `onPress` to launch image picker then call `authState.updateProfilePhoto(uri)`.

```typescript
import * as ImagePicker from 'expo-image-picker';

const handlePhotoChange = async () => {
  const result = await ImagePicker.launchImageLibraryAsync({
    mediaTypes: ImagePicker.MediaTypeOptions.Images,
    allowsEditing: true,
    aspect: [1, 1],
    quality: 0.8,
  });

  if (!result.canceled && result.assets[0]?.uri) {
    const success = await authState.updateProfilePhoto(result.assets[0].uri);
    if (!success) {
      // Show error toast
    }
  }
};
```

- [ ] **Step 3: Verify TypeScript compiles**

Run: `cd C:/laragon/www/mayush/mayush-mobile && npx tsc --noEmit --pretty 2>&1 | head -30`

- [ ] **Step 4: Commit**

```bash
git add src/commerce/authState.ts src/screens/account/EditProfileScreen.tsx
git commit -m "feat(profile): wire profile photo upload to backend API"
```

---

## Task 8: Wire profile completion percentage to real data

**Files:**
- Modify: `src/screens/commerce/AccountScreen.tsx`

- [ ] **Step 1: Read the AccountScreen to find the profile completion section**

Read `src/screens/commerce/AccountScreen.tsx` and locate where the "75%" or profile completion progress is displayed.

- [ ] **Step 2: Compute completion from real user data**

Replace hardcoded percentage with a computed value based on actual user fields:

```typescript
const computeProfileCompletion = (user: MockUser | null): number => {
  if (!user) return 0;
  let filled = 0;
  let total = 0;

  // Required fields
  const fields: (keyof MockUser)[] = ['name', 'email', 'phone', 'avatar'];
  for (const field of fields) {
    total += 1;
    if (user[field] && String(user[field]).trim().length > 0) filled += 1;
  }

  // Check if user has at least one address
  total += 1;
  if (user.addresses && user.addresses.length > 0) filled += 1;

  return Math.round((filled / total) * 100);
};
```

Use this in the render:
```typescript
const completionPercent = computeProfileCompletion(authenticatedUser);
```

- [ ] **Step 3: Verify TypeScript compiles**

Run: `cd C:/laragon/www/mayush/mayush-mobile && npx tsc --noEmit --pretty 2>&1 | head -30`

- [ ] **Step 4: Commit**

```bash
git add src/screens/commerce/AccountScreen.tsx
git commit -m "feat(account): compute profile completion from real user data"
```

---

## Task 9: Wire ActiveSessionsScreen to backend API

**Files:**
- Modify: `src/screens/account/ActiveSessionsScreen.tsx`

- [ ] **Step 1: Read the current file**

Read `src/screens/account/ActiveSessionsScreen.tsx` to understand its current structure.

- [ ] **Step 2: Add API fetch for active sessions**

The Laravel backend may expose sessions via `/api/v2/profile/sessions` or similar. Add a fetch call:

```typescript
import { apiClient } from '../../api';

interface DeviceSession {
  id: string;
  device_name: string;
  ip_address: string;
  last_active: string;
  is_current: boolean;
}

const [sessions, setSessions] = useState<DeviceSession[]>([]);
const [loading, setLoading] = useState(true);

useEffect(() => {
  setLoading(true);
  apiClient<{ data: DeviceSession[] }>('/api/v2/profile/sessions')
    .then((res) => {
      if (res && Array.isArray(res.data)) setSessions(res.data);
      setLoading(false);
    })
    .catch(() => setLoading(false));
}, []);
```

Replace any hardcoded session data with the fetched `sessions` array. If the endpoint doesn't exist yet, display current session only using device info from `expo-device`.

- [ ] **Step 3: Wire disconnect action**

```typescript
const handleDisconnect = async (sessionId: string) => {
  try {
    await apiClient('/api/v2/profile/sessions/' + sessionId, { method: 'DELETE' });
    setSessions((prev) => prev.filter((s) => s.id !== sessionId));
  } catch {
    // Show error
  }
};
```

- [ ] **Step 4: Verify TypeScript compiles**

Run: `cd C:/laragon/www/mayush/mayush-mobile && npx tsc --noEmit --pretty 2>&1 | head -30`

- [ ] **Step 5: Commit**

```bash
git add src/screens/account/ActiveSessionsScreen.tsx
git commit -m "feat(account): wire active sessions screen to backend API"
```

---

## Task 10: Wire FAQ content to backend or structured data source

**Files:**
- Modify: `src/screens/support/FaqAccordionScreen.tsx`
- Modify: `src/screens/support/FaqTabCategoriesScreen.tsx`

- [ ] **Step 1: Read current FAQ screens**

Read `src/screens/support/FaqAccordionScreen.tsx` and `src/screens/support/FaqTabCategoriesScreen.tsx` to see how FAQ content is currently structured.

- [ ] **Step 2: Create FAQ content source**

Check if a FAQ API endpoint exists. If not, centralize FAQ content in a structured data file:

```typescript
// src/content/faqContent.ts
export interface FaqItem {
  id: string;
  question: string;
  questionAr: string;
  answer: string;
  answerAr: string;
  category: string;
}

export const FAQ_ITEMS: FaqItem[] = [
  {
    id: 'track-order',
    question: 'Comment suivre ma commande ?',
    questionAr: 'كيف أتتبع طلبي؟',
    answer: 'Rendez-vous dans "Mes commandes" puis cliquez sur "Suivre la livraison" pour voir le statut en temps réel.',
    answerAr: 'اذهب إلى "طلباتي" ثم انقر على "تتبع التوصيل" لرؤية الحالة في الوقت الفعلي.',
    category: 'orders',
  },
  // ... complete FAQ items matching the design reference
];
```

If an API exists (e.g., `/api/v2/faq`), fetch from it instead:

```typescript
const [faqItems, setFaqItems] = useState<FaqItem[]>([]);

useEffect(() => {
  apiClient<{ data: FaqItem[] }>('/api/v2/faq')
    .then((res) => {
      if (res && Array.isArray(res.data)) setFaqItems(res.data);
      else setFaqItems(FAQ_ITEMS); // fallback to static
    })
    .catch(() => setFaqItems(FAQ_ITEMS));
}, []);
```

- [ ] **Step 3: Verify TypeScript compiles**

Run: `cd C:/laragon/www/mayush/mayush-mobile && npx tsc --noEmit --pretty 2>&1 | head -30`

- [ ] **Step 4: Commit**

```bash
git add src/content/faqContent.ts src/screens/support/FaqAccordionScreen.tsx src/screens/support/FaqTabCategoriesScreen.tsx
git commit -m "feat(support): centralize FAQ content with API fallback"
```

---

## Task 11: Wire wishlist price-change detection

**Files:**
- Modify: `src/commerce/wishlistState.ts`
- Modify: `src/screens/commerce/WishlistScreen.tsx`

- [ ] **Step 1: Add price comparison to wishlist hydration**

In `src/commerce/wishlistState.ts`, enhance the `hydrate()` method to detect price changes when refreshing from API:

```typescript
// Inside the hydrate method, after fetching remote wishlist data:
// Compare stored prices with fresh API prices
const priceChanges: Array<{ productId: number; oldPrice: number; newPrice: number; name: string }> = [];

for (const remoteItem of freshItems) {
  const storedItem = this.items.find((i) => i.id === remoteItem.id);
  if (storedItem && storedItem.priceMad !== remoteItem.priceMad) {
    priceChanges.push({
      productId: remoteItem.id,
      oldPrice: storedItem.priceMad,
      newPrice: remoteItem.priceMad,
      name: remoteItem.name,
    });
  }
}

if (priceChanges.length > 0) {
  this.lastPriceChanges = priceChanges;
}
```

Add a field and getter:
```typescript
private lastPriceChanges: Array<{ productId: number; oldPrice: number; newPrice: number; name: string }> = [];

public getPriceChanges() {
  return this.lastPriceChanges;
}

public clearPriceChanges() {
  this.lastPriceChanges = [];
}
```

- [ ] **Step 2: Show price change notification in WishlistScreen**

In `src/screens/commerce/WishlistScreen.tsx`, check for price changes on mount and display a banner if any exist:

```typescript
const priceChanges = wishlistState.getPriceChanges();

// In render, before the items list:
{priceChanges.length > 0 && (
  <View style={styles.priceChangeBanner}>
    <MayushIcon name="alert-triangle" size={20} color={colors.semantic.warning} />
    <MayushText variant="smallBody" color={colors.brand.navy900}>
      {heading(
        `${priceChanges.length} article(s) ont changé de prix`,
        `${priceChanges.length} منتج(ات) تغير سعرها`
      )}
    </MayushText>
  </View>
)}
```

- [ ] **Step 3: Verify TypeScript compiles**

Run: `cd C:/laragon/www/mayush/mayush-mobile && npx tsc --noEmit --pretty 2>&1 | head -30`

- [ ] **Step 4: Commit**

```bash
git add src/commerce/wishlistState.ts src/screens/commerce/WishlistScreen.tsx
git commit -m "feat(wishlist): add price-change detection on hydration with UI notification"
```

---

## Task 12: Final verification and cleanup

**Files:**
- All modified files from Tasks 1-11

- [ ] **Step 1: Run full TypeScript compilation**

Run: `cd C:/laragon/www/mayush/mayush-mobile && npx tsc --noEmit --pretty 2>&1 | tail -20`
Expected: Only pre-existing errors (if any), no new errors from our changes.

- [ ] **Step 2: Verify no remaining hardcoded fixture references in HomeScreen**

Run: `grep -n "LOGGED_IN_FLASH_DEALS\|LOGGED_IN_RECOMMENDED\|GUEST_RECOMMENDED\|RECENTLY_VIEWED_ITEMS\|LOGGED_IN_CATEGORIES_DATA\|LOGGED_IN_NEW_ARRIVALS\|LOGGED_IN_BEST_SELLERS" src/screens/discovery/HomeScreen.tsx`
Expected: No matches (all constants removed or used only in fallback position)

- [ ] **Step 3: Verify all new service files exist**

Run: `ls -la src/services/api/notificationService.ts src/services/api/brandService.ts`
Expected: Both files exist

- [ ] **Step 4: Commit any final cleanup**

```bash
git add -A
git commit -m "chore(mobile): final cleanup after critical gaps backend wiring"
```

---

## Notes

### Sections intentionally kept as static content (no backend API needed):
- `SERVICES_DATA` — marketing services badges (delivery, returns, payment, advice, guarantee)
- `AMBIANCES_DATA` — editorial inspiration sections (Bohème, Contemporaine, Scandinave, Industrielle)
- `FOOTER_TRUST_BADGES` — brand trust section
- `CUSTOMER_REVIEWS_DATA` — no aggregate reviews API endpoint exists; would need a new backend endpoint

### Backend endpoints NOT yet confirmed to exist:
- `/api/v2/profile/sessions` — active sessions management (Task 9). If missing, implement using current device info only.
- `/api/v2/profile/update-image` — profile photo upload (Task 7). If missing, check for `/api/v2/profile/update` with multipart form.
- `/api/v2/faq` — FAQ content API (Task 10). If missing, use structured static data.

### Items deferred (require new backend endpoints):
- **Wallet balance payment** — requires wallet balance API endpoint
- **Saved payment cards** — requires card management API endpoint  
- **Real-time order tracking** — requires WebSocket or polling infrastructure
- **Invoice PDF download** — requires PDF generation endpoint
- **2FA setup** — requires TOTP/SMS 2FA backend flow
- **Help center search** — could use client-side search over FAQ content
