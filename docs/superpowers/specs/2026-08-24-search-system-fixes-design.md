# Search System Architecture & Fixes Design Specification

**Document Path**: `docs/superpowers/specs/2026-08-24-search-system-fixes-design.md`  
**Date**: 2026-08-24  
**Status**: APPROVED WITH AMENDMENTS — READY FOR IMPLEMENTATION  
**Target Applications**: `mayush-mobile` (React Native / Expo / Web Prototype) & `Mayush Backend` (Laravel 10.x API v2)

---

## 1. Executive Overview & Problem Statement

An audit of the search experience across the Mayush mobile application revealed several critical functional breaks, API contract discrepancies, transient state issues, and disconnected UX entry points.

### Key Audit Findings:
1. **Broken Search Suggestions / Autocomplete**:
   - `catalogService.ts` calls `/api/v2/get-search-suggestions?keyword=...`, but Laravel's `SearchSuggestionController.php` expects `$request->query_key`.
   - Backend returns items with property `query`, while frontend maps over `s.name`, causing blank suggestion lists.
   - Missing query debounce, resulting in unthrottled API requests on every keystroke.
2. **Broken Sorting in Search Filters**:
   - `SearchFilterSheet.tsx` sends sort keys: `'price_low'`, `'price_high'`, `'new'`, `'popular'`.
   - `ProductController.php` expects: `'price_low_to_high'`, `'price_high_to_low'`, `'new_arrival'`, `'popularity'`, `'top_rated'`.
   - Sort choices fail silently and fall back to default order.
3. **Broken Category & Brand Filtering in Search**:
   - `SearchFilterSheet.tsx` passes slug strings (e.g. `'salons-sejours'`).
   - Backend `ProductController.php` runs `whereIn('category_id', $category_ids)` expecting numeric integer IDs.
4. **Disconnected UI Entry Points**:
   - Guest `HomeScreen.tsx` search bar redirects to the `'categories'` tab instead of opening `'search-landing'`.
   - Logged-in `HomeScreen.tsx` header lacks a search icon / button entirely.
   - `CategoriesScreen.tsx` and `CategoryProductListScreen.tsx` have non-interactive static search inputs.
5. **Transient Search History**:
   - `SearchLandingScreen.tsx` uses hardcoded mock strings in `useState` and does not persist search history to `AsyncStorage`.
6. **Bypassed Zero-Results Recovery Screen**:
   - `SearchNoResultsScreen.tsx` is only shown if the query contains fake keywords (`'xyz'` or `'000'`). Live searches returning 0 items only render a plain inline message.

---

## 2. System Architecture & Component Interactions

```mermaid
flowchart TD
    subgraph UI Entry Points
        H1[HomeScreen - Guest Search Bar] -->|onOpenSearch| SL[SearchLandingScreen]
        H2[HomeScreen - Logged-in Header Search Icon] -->|onOpenSearch| SL
        CAT[CategoriesScreen Search Bar] -->|onOpenSearch| SL
        CPL[CategoryProductListScreen Search Bar] -->|onOpenSearch| SL
        CL[CategoryLandingScreen Search Icon] -->|onOpenSearch| SL
    end

    subgraph Search Landing Flow
        SL -->|Load on Mount| SH[Search History Storage (AsyncStorage)]
        SL -->|Debounced Typing (300ms)| SS[catalogService.getSearchSuggestions]
        SS -->|GET /api/v2/get-search-suggestions?query_key=| BE_SS[SearchSuggestionController]
        SL -->|Submit / Select Item| SR[SearchResultsScreen]
    end

    subgraph Results & Filters Flow
        SR -->|Fetch Results| CS_SEARCH[catalogService.searchProducts]
        CS_SEARCH -->|GET /api/v2/products/search| BE_SEARCH[ProductController@search]
        SR -->|Open Filter Sheet| SFS[SearchFilterSheet]
        SFS -->|Apply Filter & Sort (Normalized IDs & Keys)| SR
        SR -->|Results Count === 0| SNRS[SearchNoResultsScreen]
        SNRS -->|Browse Categories| CAT_VIEW[CategoriesScreen]
    end
```

---

## 3. API Contract Alignment & Specifications

### 3.1. Autocomplete / Suggestions Endpoint

- **Route**: `GET /api/v2/get-search-suggestions`
- **Request Parameters**:
  ```typescript
  interface SearchSuggestionsParams {
    query_key: string; // Query search term (minimum 2 characters)
    type?: 'product' | 'brands' | 'sellers'; // Optional scope filter
  }
  ```
- **Backend Response Item Structure**:
  ```typescript
  interface SearchSuggestionApiItem {
    id: number;
    query: string;
    count: number;
    type: 'search' | 'product' | 'brand' | 'shop';
    type_string: string;
  }
  ```
- **Service Layer Implementation (`catalogService.ts:688-704`)**:
  1. Change `params: { keyword: keyword.trim() }` to `params: { query_key: keyword.trim() }`.
  2. Map raw API response objects directly at the service boundary so consumers receive normalized entities:
     ```typescript
     const rawItems = Array.isArray(res) ? res : Array.isArray(res?.data) ? res.data : [];
     return rawItems.map((item: any) => ({
       id: Number(item.id) || 0,
       name: String(item.query || item.name || '').trim(),
       count: Number(item.count) || 0,
       type: item.type || 'search',
     })).filter((item) => Boolean(item.name));
     ```

---

### 3.2. Product Search & Filter Endpoint

- **Route**: `GET /api/v2/products/search`
- **Request Parameters Specification**:
  ```typescript
  interface SearchProductsApiParams {
    name?: string;               // Search query keyword
    categories?: string;         // Comma-separated numeric Category IDs e.g. "12,15"
    brands?: string;             // Comma-separated numeric Brand IDs e.g. "3,8"
    min?: number;                // Minimum unit_price in MAD
    max?: number;                // Maximum unit_price in MAD
    sort_key?: SearchSortKey;    // Normalized backend sort enum
    page?: number;               // Pagination page number (default: 1)
    per_page?: number;           // Items per page (default: 20, max: 50)
  }

  export type SearchSortKey =
    | 'price_low_to_high'
    | 'price_high_to_low'
    | 'new_arrival'
    | 'popularity'
    | 'top_rated';
  ```

- **Sort Key Mapping Constant (`SORT_KEY_MAP`)**:
  ```typescript
  export const SORT_KEY_MAP: Record<string, SearchSortKey> = {
    price_low: 'price_low_to_high',
    price_high: 'price_high_to_low',
    new: 'new_arrival',
    popular: 'popularity',
    top_rated: 'top_rated',
    // Passthrough fallbacks if already normalized
    price_low_to_high: 'price_low_to_high',
    price_high_to_low: 'price_high_to_low',
    new_arrival: 'new_arrival',
    popularity: 'popularity',
  };
  ```

- **Clean Parameter Dispatching in `catalogService.searchProducts()`**:
  Clean up redundant hedging parameters (avoid sending duplicate keys like `category` AND `categories`):
  ```typescript
  params: {
    name: params.name,
    categories: params.categories || params.category,
    brands: params.brands || params.brand,
    min: params.min,
    max: params.max,
    sort_key: params.sort_key ? (SORT_KEY_MAP[params.sort_key] || params.sort_key) : undefined,
    page: params.page || 1,
  }
  ```

---

## 4. Component Refactoring & Implementation Details

### 4.1. Search History State Module (`src/commerce/searchHistoryState.ts`)
Create a robust storage module for persisting search queries:
```typescript
import AsyncStorage from '@react-native-async-storage/async-storage';

const SEARCH_HISTORY_STORAGE_KEY = '@mayush_recent_searches';
const MAX_SEARCH_HISTORY = 10;

export const searchHistoryState = {
  async getRecentSearches(): Promise<string[]> {
    try {
      const raw = await AsyncStorage.getItem(SEARCH_HISTORY_STORAGE_KEY);
      if (!raw) return [];
      const parsed = JSON.parse(raw);
      return Array.isArray(parsed) ? parsed : [];
    } catch {
      return [];
    }
  },

  async addRecentSearch(term: string): Promise<string[]> {
    const clean = term.trim();
    if (!clean) return this.getRecentSearches();
    try {
      const current = await this.getRecentSearches();
      const updated = [clean, ...current.filter((t) => t.toLowerCase() !== clean.toLowerCase())].slice(0, MAX_SEARCH_HISTORY);
      await AsyncStorage.setItem(SEARCH_HISTORY_STORAGE_KEY, JSON.stringify(updated));
      return updated;
    } catch {
      return [];
    }
  },

  async clearRecentSearches(): Promise<void> {
    try {
      await AsyncStorage.removeItem(SEARCH_HISTORY_STORAGE_KEY);
    } catch {}
  },
};
```

---

### 4.2. `SearchLandingScreen.tsx`
1. **Recent Searches**:
   - Hydrate from `searchHistoryState.getRecentSearches()` on mount.
   - When a term is submitted or tapped, persist with `searchHistoryState.addRecentSearch(term)`.
   - "Effacer" / "مسح الكل" button calls `searchHistoryState.clearRecentSearches()`.
2. **Debounced Suggestions**:
   - Add a 300ms timer via `setTimeout`/`clearTimeout` in the existing `useEffect`:
     ```typescript
     useEffect(() => {
       if (!query || query.trim().length < 2) {
         setSearchSuggestions([]);
         return;
       }
       const timer = setTimeout(() => {
         catalogService
           .getSearchSuggestions(query.trim(), language)
           .then((suggestions) => {
             setSearchSuggestions(suggestions.map((s) => s.name).slice(0, 6));
           })
           .catch(() => setSearchSuggestions([]));
       }, 300);
       return () => clearTimeout(timer);
     }, [query, language]);
     ```
3. **Trending Categories Shortcuts**:
   - Pass the selected category slug or ID through `onSelectCategoryShortcut(catSlug)` to load filtered products immediately.

---

### 4.3. `SearchResultsScreen.tsx` & `SearchFilterSheet.tsx`
1. **`SearchFilterSheet.tsx`**:
   - Fix category key selection ([line 202](file:///c:/laragon/www/mayush/mayush-mobile/src/screens/search/SearchFilterSheet.tsx#L202)): Use `String(cat.id)` instead of `cat.slug`.
   - Fix brand key selection ([line 232](file:///c:/laragon/www/mayush/mayush-mobile/src/screens/search/SearchFilterSheet.tsx#L232)): Use `String(brand.id)` instead of `brand.slug`.
   - Add `top_rated` sort chip option in `SORT_OPTIONS`.
2. **`SearchResultsScreen.tsx`**:
   - Map `sortKey` through `SORT_KEY_MAP` when querying `catalogService.searchProducts`.
   - Seamless zero-results transition: When `!loading && products.length === 0`, render `SearchNoResultsScreen` directly within the view (with tips and "Parcourir les catégories" CTA), retaining the back navigation without fake keyword checks.
   - Retain working "Voir plus" load-more button and wishlist toggles.

---

### 4.4. UI Entry Points Integration
1. **`HomeScreen.tsx`**:
   - Add `onOpenSearch?: () => void` to `HomeScreenProps`.
   - **Guest View**: Change search bar `onPress` from `() => onNavigateTab?.('categories')` to `onOpenSearch`.
   - **Logged-In View**: Add search icon button (`MayushIcon name="search"`) to the header icon cluster next to the notification bell.
2. **`CategoriesScreen.tsx` & `CategoryProductListScreen.tsx`**:
   - Wrap the search bar in a `TouchableOpacity` calling `onOpenSearch` (`() => setCurrentScreen('search-landing')`).
3. **`RootNavigator.tsx`**:
   - Update `handleSearchSubmit`:
     ```typescript
     const handleSearchSubmit = (query: string) => {
       setSearchQuery(query.trim());
       setCurrentScreen('search-results');
     };
     ```
   - Wire `onOpenSearch={() => setCurrentScreen('search-landing')}` to `HomeScreen`, `CategoriesScreen`, and `CategoryProductListScreen`.
   - Update `onSelectCategoryShortcut` on `SearchLandingScreen` to open `category-products` for the targeted category.

---

## 5. Verification & Testing Strategy

### 5.1. Automated Behavioral Tests
- **Suggestions API Test**: Verify that `catalogService.getSearchSuggestions('canape')` sends `query_key` and parses response items correctly.
- **Search Products Parameter Test**: Verify that `searchProducts` converts sort keys and numeric filters properly without breaking pagination.
- **Search History Storage Test**: Verify add, deduplicate, limit, and clear operations against `AsyncStorage`.
- **Regression Check**: Ensure virtual-slug category fallbacks (`meilleures-ventes`, `nouveautes`) continue functioning.

### 5.2. Manual & Prototype Verification
1. Tapping search bar on Home (guest & auth) opens `SearchLandingScreen`.
2. Typing "canapé" debounces (300ms) and displays live suggestions dropdown.
3. Submitting search loads `SearchResultsScreen` with live products and pagination.
4. Opening filter sheet, selecting "Prix croissant" + Category filter properly re-queries and sorts products.
5. Submitting a non-existent search term displays `SearchNoResultsScreen` with category browsing CTA.
6. RTL verification: Arabic mode renders chips, suggestions, and icons aligned properly.
