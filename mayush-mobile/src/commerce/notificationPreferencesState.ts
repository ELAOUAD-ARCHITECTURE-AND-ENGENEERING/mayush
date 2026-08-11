import AsyncStorage from '@react-native-async-storage/async-storage';
import { BuyerOrder, BuyerOrderRepository, orderState } from './orderState';

export interface MarketingPreferences {
  abandonedCartReminders: boolean;
  promotionsAndOffers: boolean;
  personalizedRecommendations: boolean;
  productNewsUpdates: boolean;
  emailMarketing: boolean;
  smsMarketing: boolean;
  pushMarketing: boolean;
}

export interface NotificationChannels {
  emailChannel: boolean;
  smsChannel: boolean;
  pushChannel: boolean;
  inAppChannel: boolean;
}

export interface NotificationCategorySettings {
  orders: boolean;
  delivery: boolean;
  promotions: boolean;
  wishlist: boolean;
  accountSecurity: boolean;
}

export interface NotificationFixture {
  id: string;
  type: 'order_preparation' | 'order_shipped' | 'promotion' | 'security';
  title: string;
  subtitle: string;
  orderNumber: string;
  orderId: string;
  date: string;
  statusText: string;
  description: string;
  carrier?: string;
  trackingNumber?: string;
  estimatedDelivery?: string;
  itemsSummary?: string;
  isRead: boolean;
}

const NOTIF_PREFERENCES_STORAGE_KEY = 'mayush-mobile:notification-preferences';

class NotificationPreferencesStateManager {
  private static instance: NotificationPreferencesStateManager;

  private marketingPreferences: MarketingPreferences = {
    abandonedCartReminders: true,
    promotionsAndOffers: true,
    personalizedRecommendations: true,
    productNewsUpdates: false,
    emailMarketing: true,
    smsMarketing: false,
    pushMarketing: true,
  };

  private notificationChannels: NotificationChannels = {
    emailChannel: true,
    smsChannel: true,
    pushChannel: true,
    inAppChannel: true,
  };

  private notificationSettings: NotificationCategorySettings = {
    orders: true,
    delivery: true,
    promotions: true,
    wishlist: true,
    accountSecurity: true,
  };

  private quietHoursEnabled: boolean = true;
  private quietHoursDays: string[] = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];
  private quietHoursStart: string = '22:00';
  private quietHoursEnd: string = '08:00';

  private selectedNotificationId: string = 'notif-prep';

  private notificationFixtures: NotificationFixture[] = [
    {
      id: 'notif-prep',
      type: 'order_preparation',
      title: 'Commande en cours de préparation',
      subtitle: 'Votre commande #MY-84920 est en cours d’assemblage.',
      orderNumber: '#MY-84920',
      orderId: 'MAY-2026-001842',
      date: '05 Août 2026 à 14:30',
      statusText: 'En préparation',
      description: 'Votre commande de Canapé Luna Velvet 3 Places et Table Basse Marble est actuellement préparée avec soin dans nos ateliers de Casablanca.',
      itemsSummary: '1× Canapé Luna Velvet 3 Places, 1× Table Basse Marble',
      isRead: false,
    },
    {
      id: 'notif-shipped',
      type: 'order_shipped',
      title: 'Commande expédiée',
      subtitle: 'Votre colis #MY-84920 a été remis au livreur.',
      orderNumber: '#MY-84920',
      orderId: 'MAY-2026-001842',
      date: '06 Août 2026 à 09:15',
      statusText: 'Expédiée',
      description: 'Votre commande a quitté notre entrepôt logistique. Le livreur vous contactera par téléphone le jour de la livraison.',
      carrier: 'CTM Messagerie Express',
      trackingNumber: 'CTM-948201-MA',
      estimatedDelivery: '08 Août 2026 (Entre 09h et 18h)',
      itemsSummary: '1× Canapé Luna Velvet 3 Places, 1× Table Basse Marble',
      isRead: true,
    },
  ];

  private listeners: (() => void)[] = [];

  private constructor() {
    this.loadFromStorage();
  }

  public static getInstance(): NotificationPreferencesStateManager {
    if (!NotificationPreferencesStateManager.instance) {
      NotificationPreferencesStateManager.instance = new NotificationPreferencesStateManager();
    }
    return NotificationPreferencesStateManager.instance;
  }

  private async loadFromStorage() {
    try {
      const stored = await AsyncStorage.getItem(NOTIF_PREFERENCES_STORAGE_KEY);
      if (stored) {
        const parsed = JSON.parse(stored);
        if (parsed.marketingPreferences) {
          this.marketingPreferences = { ...this.marketingPreferences, ...parsed.marketingPreferences };
        }
        if (parsed.notificationChannels) {
          this.notificationChannels = { ...this.notificationChannels, ...parsed.notificationChannels };
        }
        if (parsed.notificationSettings) {
          this.notificationSettings = { ...this.notificationSettings, ...parsed.notificationSettings };
        }
        if (parsed.quietHoursEnabled !== undefined) this.quietHoursEnabled = parsed.quietHoursEnabled;
        if (parsed.quietHoursDays) this.quietHoursDays = parsed.quietHoursDays;
        if (parsed.quietHoursStart) this.quietHoursStart = parsed.quietHoursStart;
        if (parsed.quietHoursEnd) this.quietHoursEnd = parsed.quietHoursEnd;
        if (parsed.selectedNotificationId) this.selectedNotificationId = parsed.selectedNotificationId;
      }
    } catch {
      // Ignore storage read errors
    }
  }

  private async persistToStorage() {
    try {
      await AsyncStorage.setItem(
        NOTIF_PREFERENCES_STORAGE_KEY,
        JSON.stringify({
          marketingPreferences: this.marketingPreferences,
          notificationChannels: this.notificationChannels,
          notificationSettings: this.notificationSettings,
          quietHoursEnabled: this.quietHoursEnabled,
          quietHoursDays: this.quietHoursDays,
          quietHoursStart: this.quietHoursStart,
          quietHoursEnd: this.quietHoursEnd,
          selectedNotificationId: this.selectedNotificationId,
        }),
      );
    } catch {
      // Ignore storage write errors
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

  // ── Marketing Preferences ──

  public getMarketingPreferences(): MarketingPreferences {
    return { ...this.marketingPreferences };
  }

  public updateMarketingPreferences(partial: Partial<MarketingPreferences>) {
    this.marketingPreferences = {
      ...this.marketingPreferences,
      ...partial,
    };
    this.notify();
  }

  public toggleMarketingPreference(key: keyof MarketingPreferences): boolean {
    this.marketingPreferences[key] = !this.marketingPreferences[key];
    this.notify();
    return this.marketingPreferences[key];
  }

  // ── Notification Channels ──

  public getNotificationChannels(): NotificationChannels {
    return { ...this.notificationChannels };
  }

  public updateNotificationChannels(partial: Partial<NotificationChannels>) {
    this.notificationChannels = {
      ...this.notificationChannels,
      ...partial,
    };
    this.notify();
  }

  public toggleNotificationChannel(key: keyof NotificationChannels): boolean {
    this.notificationChannels[key] = !this.notificationChannels[key];
    this.notify();
    return this.notificationChannels[key];
  }

  // ── Notification Category Settings ──

  public getNotificationSettings(): NotificationCategorySettings {
    return { ...this.notificationSettings };
  }

  public updateNotificationSettings(partial: Partial<NotificationCategorySettings>) {
    this.notificationSettings = {
      ...this.notificationSettings,
      ...partial,
    };
    this.notify();
  }

  public toggleNotificationSetting(key: keyof NotificationCategorySettings): boolean {
    this.notificationSettings[key] = !this.notificationSettings[key];
    this.notify();
    return this.notificationSettings[key];
  }

  // ── Quiet Hours & Do Not Disturb ──

  public getQuietHoursEnabled(): boolean {
    return this.quietHoursEnabled;
  }

  public setQuietHoursEnabled(enabled: boolean) {
    this.quietHoursEnabled = enabled;
    this.notify();
  }

  public toggleQuietHours(): boolean {
    this.quietHoursEnabled = !this.quietHoursEnabled;
    this.notify();
    return this.quietHoursEnabled;
  }

  public getQuietHoursDays(): string[] {
    return [...this.quietHoursDays];
  }

  public setQuietHoursDays(days: string[]) {
    this.quietHoursDays = days;
    this.notify();
  }

  public toggleQuietHoursDay(day: string) {
    if (this.quietHoursDays.includes(day)) {
      this.quietHoursDays = this.quietHoursDays.filter((d) => d !== day);
    } else {
      this.quietHoursDays.push(day);
    }
    this.notify();
  }

  public getQuietHoursTimeRange(): { start: string; end: string } {
    return { start: this.quietHoursStart, end: this.quietHoursEnd };
  }

  public setQuietHoursTimeRange(start: string, end: string) {
    this.quietHoursStart = start;
    this.quietHoursEnd = end;
    this.notify();
  }

  // ── Notification Fixtures & Selection ──

  public getNotificationFixtures(): NotificationFixture[] {
    return [...this.notificationFixtures];
  }

  public getSelectedNotificationId(): string {
    return this.selectedNotificationId;
  }

  public setSelectedNotificationId(id: string) {
    if (this.notificationFixtures.some((n) => n.id === id)) {
      this.selectedNotificationId = id;
      this.notify();
    }
  }

  public getSelectedNotification(): NotificationFixture | undefined {
    return this.notificationFixtures.find((n) => n.id === this.selectedNotificationId);
  }

  public reset() {
    this.marketingPreferences = {
      abandonedCartReminders: true,
      promotionsAndOffers: true,
      personalizedRecommendations: true,
      productNewsUpdates: false,
      emailMarketing: true,
      smsMarketing: false,
      pushMarketing: true,
    };
    this.notificationChannels = {
      emailChannel: true,
      smsChannel: true,
      pushChannel: true,
      inAppChannel: true,
    };
    this.notificationSettings = {
      orders: true,
      delivery: true,
      promotions: true,
      wishlist: true,
      accountSecurity: true,
    };
    this.quietHoursEnabled = true;
    this.quietHoursDays = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];
    this.quietHoursStart = '22:00';
    this.quietHoursEnd = '08:00';
    this.selectedNotificationId = 'notif-prep';
    this.notify();
  }
}

export const notificationPreferencesState = NotificationPreferencesStateManager.getInstance();

export const resolveNotificationBuyerOrder = (
  notificationId: string,
  repository: BuyerOrderRepository = orderState,
): BuyerOrder | null => {
  const notification = notificationPreferencesState.getNotificationFixtures()
    .find((candidate) => candidate.id === notificationId);
  return notification ? repository.getOrderById(notification.orderId) : null;
};
