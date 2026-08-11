export interface CacheMetrics {
  cacheSizeBytes: number;
  cachedImageCount: number;
  lastClearedAt: string | null;
}

const DEFAULT_CACHE_BYTES = 124 * 1024 * 1024; // 124 MB initial fixture
const DEFAULT_IMAGE_COUNT = 186;

class CacheStateManager {
  private static instance: CacheStateManager;

  private metrics: CacheMetrics = {
    cacheSizeBytes: DEFAULT_CACHE_BYTES,
    cachedImageCount: DEFAULT_IMAGE_COUNT,
    lastClearedAt: null,
  };

  private listeners: (() => void)[] = [];

  private constructor() {}

  public static getInstance(): CacheStateManager {
    if (!CacheStateManager.instance) {
      CacheStateManager.instance = new CacheStateManager();
    }
    return CacheStateManager.instance;
  }

  public subscribe(listener: () => void): () => void {
    this.listeners.push(listener);
    return () => {
      this.listeners = this.listeners.filter((l) => l !== listener);
    };
  }

  private notify() {
    this.listeners.forEach((l) => l());
  }

  public getMetrics(): CacheMetrics {
    return { ...this.metrics };
  }

  public getFormattedCacheSize(): string {
    const bytes = this.metrics.cacheSizeBytes;
    if (bytes === 0) return '0 Mo';
    const mb = bytes / (1024 * 1024);
    return `${Math.round(mb)} Mo`;
  }

  /**
   * CRITICAL DATA SAFETY:
   * Clears only disposable cache metrics without deleting persistent storage keys
   * and without touching durable user state (Auth, Cart, Wishlist, Addresses, Settings).
   */
  public clearCache(): void {
    this.metrics.cacheSizeBytes = 0;
    this.metrics.cachedImageCount = 0;
    this.metrics.lastClearedAt = new Date().toISOString();
    this.notify();
  }

  public reset(): void {
    this.metrics = {
      cacheSizeBytes: DEFAULT_CACHE_BYTES,
      cachedImageCount: DEFAULT_IMAGE_COUNT,
      lastClearedAt: null,
    };
    this.notify();
  }
}

export const cacheState = CacheStateManager.getInstance();
