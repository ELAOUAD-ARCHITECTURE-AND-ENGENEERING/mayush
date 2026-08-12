import AsyncStorage from '@react-native-async-storage/async-storage';
import { MvpAppLanguage } from '../contracts/api/dto';

export const LANGUAGE_STORAGE_KEY = 'mayush-mobile:language';
export const LEGACY_ACCOUNT_PREFERENCES_KEY = 'mayush-mobile:account-preferences';

class LocaleStateManager {
  private static instance: LocaleStateManager;
  private currentLanguage: MvpAppLanguage = 'fr';
  private hydrated: boolean = false;
  private listeners: Set<() => void> = new Set();

  private constructor() {
    void this.hydrate();
  }

  public static getInstance(): LocaleStateManager {
    if (!LocaleStateManager.instance) {
      LocaleStateManager.instance = new LocaleStateManager();
    }
    return LocaleStateManager.instance;
  }

  public isHydrated(): boolean {
    return this.hydrated;
  }

  public getLanguage(): MvpAppLanguage {
    return this.currentLanguage;
  }

  public isRTL(): boolean {
    return this.currentLanguage === 'ar';
  }

  public async setLanguage(lang: MvpAppLanguage): Promise<void> {
    if (this.currentLanguage === lang) return;
    this.currentLanguage = lang;
    this.notify();
    try {
      await AsyncStorage.setItem(LANGUAGE_STORAGE_KEY, lang);
    } catch {
      // Storage error ignored
    }
  }

  public async hydrate(): Promise<void> {
    try {
      const stored = await AsyncStorage.getItem(LANGUAGE_STORAGE_KEY);
      if (stored === 'fr' || stored === 'ar') {
        this.currentLanguage = stored;
        this.hydrated = true;
        this.notify();
        return;
      }

      // Migration fallback from legacy account preferences key
      const legacyPrefs = await AsyncStorage.getItem(LEGACY_ACCOUNT_PREFERENCES_KEY);
      if (legacyPrefs) {
        const parsed = JSON.parse(legacyPrefs);
        if (parsed && typeof parsed === 'object' && (parsed.selectedLanguage === 'fr' || parsed.selectedLanguage === 'ar')) {
          const lang = parsed.selectedLanguage;
          this.currentLanguage = lang;
          await AsyncStorage.setItem(LANGUAGE_STORAGE_KEY, lang);
          const { selectedLanguage: _extracted, ...remainingPrefs } = parsed;
          await AsyncStorage.setItem(LEGACY_ACCOUNT_PREFERENCES_KEY, JSON.stringify(remainingPrefs));
          this.hydrated = true;
          this.notify();
          return;
        }
      }
    } catch {
      // Safe fallback to 'fr'
    }
    this.hydrated = true;
    this.notify();
  }

  public subscribe(listener: () => void): () => void {
    this.listeners.add(listener);
    return () => {
      this.listeners.delete(listener);
    };
  }

  private notify(): void {
    this.listeners.forEach((listener) => listener());
  }

  public reset(): void {
    this.currentLanguage = 'fr';
    this.notify();
    void AsyncStorage.setItem(LANGUAGE_STORAGE_KEY, 'fr');
  }
}

export const localeState = LocaleStateManager.getInstance();
