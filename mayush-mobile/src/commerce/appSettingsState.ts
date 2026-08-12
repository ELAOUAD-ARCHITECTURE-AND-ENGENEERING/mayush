import AsyncStorage from '@react-native-async-storage/async-storage';

export type TextSizeOption = 'normal' | 'large' | 'xlarge';
export type PermissionStatus = 'granted' | 'denied' | 'not-requested';
export type ImageQualityOption = 'standard' | 'high' | 'data-saver';

export interface AppAccessibilitySettings {
  textSize: TextSizeOption;
  highContrast: boolean;
  reducedMotion: boolean;
  readableFont: boolean;
}

export interface AppPermissionSettings {
  camera: PermissionStatus;
  photos: PermissionStatus;
  location: PermissionStatus;
  notifications: PermissionStatus;
}

export interface AppDataUsageSettings {
  imageQuality: ImageQualityOption;
  wifiOnlyDownloads: boolean;
  dataSaverMode: boolean;
  autoPlayMedia: boolean;
}

const STORAGE_KEY = 'mayush-mobile:app-settings';

const DEFAULT_ACCESSIBILITY: AppAccessibilitySettings = {
  textSize: 'normal',
  highContrast: false,
  reducedMotion: false,
  readableFont: false,
};

const DEFAULT_PERMISSIONS: AppPermissionSettings = {
  camera: 'not-requested',
  photos: 'not-requested',
  location: 'not-requested',
  notifications: 'granted',
};

const DEFAULT_DATA_USAGE: AppDataUsageSettings = {
  imageQuality: 'standard',
  wifiOnlyDownloads: true,
  dataSaverMode: false,
  autoPlayMedia: true,
};

class AppSettingsStateManager {
  private static instance: AppSettingsStateManager;
  private hydrated: boolean = false;

  private accessibility: AppAccessibilitySettings = { ...DEFAULT_ACCESSIBILITY };
  private permissions: AppPermissionSettings = { ...DEFAULT_PERMISSIONS };
  private dataUsage: AppDataUsageSettings = { ...DEFAULT_DATA_USAGE };
  private offlineModeEnabled = false;
  private listeners: (() => void)[] = [];

  private constructor() {
    void this.loadFromStorage();
  }

  public static getInstance(): AppSettingsStateManager {
    if (!AppSettingsStateManager.instance) {
      AppSettingsStateManager.instance = new AppSettingsStateManager();
    }
    return AppSettingsStateManager.instance;
  }

  public isHydrated(): boolean {
    return this.hydrated;
  }

  private async loadFromStorage() {
    try {
      const stored = await AsyncStorage.getItem(STORAGE_KEY);
      if (stored) {
        const parsed = JSON.parse(stored);
        if (parsed.accessibility) this.accessibility = { ...DEFAULT_ACCESSIBILITY, ...parsed.accessibility };
        if (parsed.permissions) this.permissions = { ...DEFAULT_PERMISSIONS, ...parsed.permissions };
        if (parsed.dataUsage) this.dataUsage = { ...DEFAULT_DATA_USAGE, ...parsed.dataUsage };
      }
    } catch {
      // Ignore storage error
    }
    this.hydrated = true;
    this.notifyListeners();
  }

  private notifyListeners() {
    this.listeners.forEach((l) => l());
  }

  private async persistToStorage() {
    try {
      await AsyncStorage.setItem(
        STORAGE_KEY,
        JSON.stringify({
          accessibility: this.accessibility,
          permissions: this.permissions,
          dataUsage: this.dataUsage,
        }),
      );
    } catch {
      // Ignore storage error
    }
  }

  public subscribe(listener: () => void): () => void {
    this.listeners.push(listener);
    return () => {
      this.listeners = this.listeners.filter((l) => l !== listener);
    };
  }

  private notify() {
    this.listeners.forEach((l) => l());
    this.persistToStorage();
  }

  // ── Accessibility Getters & Setters ──

  public getAccessibility(): AppAccessibilitySettings {
    return { ...this.accessibility };
  }

  public setTextSize(textSize: TextSizeOption) {
    this.accessibility.textSize = textSize;
    this.notify();
  }

  public toggleHighContrast() {
    this.accessibility.highContrast = !this.accessibility.highContrast;
    this.notify();
  }

  public toggleReducedMotion() {
    this.accessibility.reducedMotion = !this.accessibility.reducedMotion;
    this.notify();
  }

  public toggleReadableFont() {
    this.accessibility.readableFont = !this.accessibility.readableFont;
    this.notify();
  }

  // ── Permissions Getters & Setters ──

  public getPermissions(): AppPermissionSettings {
    return { ...this.permissions };
  }

  public setPermissionStatus(key: keyof AppPermissionSettings, status: PermissionStatus) {
    this.permissions[key] = status;
    this.notify();
  }

  public togglePermission(key: keyof AppPermissionSettings) {
    const current = this.permissions[key];
    this.permissions[key] = current === 'granted' ? 'denied' : 'granted';
    this.notify();
  }

  // ── Data Usage Getters & Setters ──

  public getDataUsage(): AppDataUsageSettings {
    return { ...this.dataUsage };
  }

  public setImageQuality(imageQuality: ImageQualityOption) {
    this.dataUsage.imageQuality = imageQuality;
    this.notify();
  }

  public toggleWifiOnlyDownloads() {
    this.dataUsage.wifiOnlyDownloads = !this.dataUsage.wifiOnlyDownloads;
    this.notify();
  }

  public toggleDataSaverMode() {
    this.dataUsage.dataSaverMode = !this.dataUsage.dataSaverMode;
    this.notify();
  }

  public toggleAutoPlayMedia() {
    this.dataUsage.autoPlayMedia = !this.dataUsage.autoPlayMedia;
    this.notify();
  }

  // ── Offline Mode ──

  public getOfflineMode(): boolean {
    return this.offlineModeEnabled;
  }

  public toggleOfflineMode(): void {
    this.offlineModeEnabled = !this.offlineModeEnabled;
    this.notify();
  }

  // ── Reset ──

  public reset() {
    this.accessibility = { ...DEFAULT_ACCESSIBILITY };
    this.permissions = { ...DEFAULT_PERMISSIONS };
    this.dataUsage = { ...DEFAULT_DATA_USAGE };
    this.offlineModeEnabled = false;
    this.notify();
  }
}

export const appSettingsState = AppSettingsStateManager.getInstance();
