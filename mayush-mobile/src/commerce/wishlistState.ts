import AsyncStorage from '@react-native-async-storage/async-storage';
import { ProductMiniDto } from '../contracts/api/dto';
import { authState } from './authState';
import { wishlistService } from '../services/api/wishlistService';

export interface WishlistItem extends ProductMiniDto {
  inStock: boolean;
  oldPriceMad?: number;
}


export const getWishlistStorageKey = (userId?: string): string => {
  return `mayush-mobile:wishlist:${userId && userId.trim() ? userId.trim() : 'guest'}`;
};

class WishlistStateManager {
  private items: WishlistItem[] = [];
  private listeners = new Set<() => void>();
  private currentUserId: string | undefined = undefined;
  private hydrated: boolean = false;

  constructor() {
    this.currentUserId = authState.getUser()?.id;
    void this.hydrate();

    authState.subscribe(() => {
      const nextUserId = authState.getUser()?.id;
      if (nextUserId !== this.currentUserId) {
        const previousUserId = this.currentUserId;
        this.currentUserId = nextUserId;
        void this.handleUserTransition(previousUserId, nextUserId);
      }
    });
  }

  public isHydrated(): boolean {
    return this.hydrated;
  }

  private async handleUserTransition(prevUserId?: string, nextUserId?: string): Promise<void> {
    const key = getWishlistStorageKey(nextUserId);
    try {
      const stored = await AsyncStorage.getItem(key);
      if (stored) {
        const parsed = JSON.parse(stored);
        if (Array.isArray(parsed)) {
          this.items = parsed;
          this.hydrated = true;
          this.notify();
          return;
        }
      }
    } catch {
      // Fallback
    }

    // If logging into a buyer account for the first time without stored items:
    // merge current guest items if transitioning from guest, or reset to empty
    if (!prevUserId && nextUserId && this.items.length) {
      // Keep existing guest items for newly logged in buyer
      this.hydrated = true;
      this.notify();
      void this.persist();
      return;
    }

    // If logging out or switching between buyers with no stored wishlist
    this.items = [];
    this.hydrated = true;
    this.notify();
  }

  public async hydrate(userId?: string): Promise<void> {
    const activeUserId = userId ?? this.currentUserId;
    const key = getWishlistStorageKey(activeUserId);
    try {
      const stored = await AsyncStorage.getItem(key);
      if (stored) {
        const parsed = JSON.parse(stored);
        if (Array.isArray(parsed)) {
          this.items = parsed;
          this.hydrated = true;
          this.notify();
        }
      }
    } catch {
      // Fall back safely
    }

    if (authState.isAuthenticated()) {
      try {
        const remote = await wishlistService.getWishlist();
        if (remote?.data && Array.isArray(remote.data)) {
          // Build a price map from previously cached items to detect price drops
          const cachedPriceMap = new Map<number, number>();
          for (const existing of this.items) {
            if (existing.priceMad != null && existing.priceMad > 0) {
              cachedPriceMap.set(existing.id, existing.priceMad);
            }
          }

          this.items = remote.data.map((entry) => {
            const currentPrice = entry.product.base_discounted_price || entry.product.base_price || 0;
            const cachedPrice = cachedPriceMap.get(entry.product.id);
            // Set oldPriceMad only when price has dropped compared to last cached value
            const oldPriceMad =
              cachedPrice != null && cachedPrice > currentPrice ? cachedPrice : undefined;

            return {
              id: entry.product.id,
              name: entry.product.name,
              priceMad: currentPrice,
              formattedPrice: `${currentPrice} MAD`,
              inStock: (entry.product.current_stock ?? 1) > 0,
              thumbnail_image: entry.product.thumbnail_image || '',
              has_discount: Boolean(entry.product.base_discounted_price && entry.product.base_discounted_price < (entry.product.base_price || 0)),
              discount: null,
              stroked_price: `${entry.product.base_price || 0} MAD`,
              main_price: `${currentPrice} MAD`,
              rating: entry.product.rating || 5,
              sales: 0,
              links: { details: '' },
              oldPriceMad,
            };
          });
          void this.persist();
        }
      } catch {
        // Keep offline/cached items
      }
    }

    this.hydrated = true;
    this.notify();
  }

  private async persist(): Promise<void> {
    const key = getWishlistStorageKey(this.currentUserId);
    try {
      await AsyncStorage.setItem(key, JSON.stringify(this.items));
    } catch {
      // Ignore storage errors safely
    }
  }

  public subscribe(listener: () => void): () => void {
    this.listeners.add(listener);
    return () => this.listeners.delete(listener);
  }

  private notify(): void {
    this.listeners.forEach((listener) => listener());
  }

  public getItems(): WishlistItem[] {
    return this.items.map((item) => ({ ...item }));
  }

  public getProductIds(): number[] {
    return this.items.map((item) => item.id);
  }

  public isWishlisted(productId: number): boolean {
    return this.items.some((item) => item.id === productId);
  }

  public toggle(product: ProductMiniDto): boolean {
    if (this.isWishlisted(product.id)) {
      this.remove(product.id);
      if (authState.isAuthenticated()) {
        const slug = (product as any).slug || String(product.id);
        wishlistService.removeFromWishlist(slug).catch(() => undefined);
      }
      return false;
    }
    this.items = [{ ...product, inStock: true }, ...this.items];
    this.notify();
    void this.persist();
    if (authState.isAuthenticated()) {
      const slug = (product as any).slug || String(product.id);
      wishlistService.addToWishlist(slug).catch(() => undefined);
    }
    return true;
  }

  public remove(productId: number): void {
    const next = this.items.filter((item) => item.id !== productId);
    if (next.length === this.items.length) return;
    const removedItem = this.items.find((item) => item.id === productId);
    this.items = next;
    this.notify();
    void this.persist();
    if (authState.isAuthenticated() && removedItem) {
      const slug = (removedItem as any).slug || String(removedItem.id);
      wishlistService.removeFromWishlist(slug).catch(() => undefined);
    }
  }

  public reset(): void {
    this.items = [];
    this.notify();
    void this.persist();
  }
}

export const wishlistState = new WishlistStateManager();
