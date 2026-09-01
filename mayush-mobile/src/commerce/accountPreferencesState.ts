import AsyncStorage from '@react-native-async-storage/async-storage';
import { localeState } from './localeState';

export interface PaymentMethodFixture {
  id: string;
  type: 'card' | 'cod' | 'wallet';
  title: string;
  subtitle: string;
  iconName: 'credit-card' | 'truck' | 'wallet';
  last4?: string;
  expiry?: string;
  brand?: string;
  verificationScenario?: 'confirmed_fixture' | 'failed_fixture' | 'pending_fixture';
  isDefault: boolean;
  balanceMad?: number;
}

export type AppLanguage = 'fr' | 'ar' | 'en';

export interface RegionInfo {
  country: string;
  countryCode: string;
  currency: string;
  currencySymbol: string;
  flag: string;
}

const PREFERENCES_STORAGE_KEY = 'mayush-mobile:account-preferences';

class AccountPreferencesStateManager {
  private static instance: AccountPreferencesStateManager;
  private hydrated: boolean = false;

  private region: RegionInfo = {
    country: 'Maroc',
    countryCode: 'MA',
    currency: 'MAD',
    currencySymbol: 'DH',
    flag: '🇲🇦',
  };

  private paymentMethods: PaymentMethodFixture[] = [
    {
      id: 'pm-card',
      type: 'card',
      title: 'Carte bancaire (Attijariwafa / CMI)',
      subtitle: '•••• •••• •••• 4242 (Expire 06/26)',
      iconName: 'credit-card',
      last4: '4242',
      expiry: '06/26',
      brand: 'Visa',
      verificationScenario: 'pending_fixture',
      isDefault: true,
    },
    {
      id: 'pm-mastercard',
      type: 'card',
      title: 'MasterCard',
      subtitle: '•••• •••• •••• 8731 (Expire 11/27)',
      iconName: 'credit-card',
      last4: '8731',
      expiry: '11/27',
      brand: 'MasterCard',
      verificationScenario: 'failed_fixture',
      isDefault: false,
    },
    {
      id: 'pm-cod',
      type: 'cod',
      title: 'Paiement à la livraison (Espèces)',
      subtitle: 'Payez en espèces à la réception de votre commande',
      iconName: 'truck',
      isDefault: false,
    },
    {
      id: 'pm-wallet',
      type: 'wallet',
      title: 'Mayush Wallet',
      subtitle: 'Solde disponible : 1,250 MAD',
      iconName: 'wallet',
      balanceMad: 1250,
      isDefault: false,
    },
  ];

  private selectedPaymentMethodId: string = 'pm-card';
  private listeners: (() => void)[] = [];

  private constructor() {
    localeState.subscribe(() => this.notifyListeners());
    void this.loadFromStorage();
  }

  public static getInstance(): AccountPreferencesStateManager {
    if (!AccountPreferencesStateManager.instance) {
      AccountPreferencesStateManager.instance = new AccountPreferencesStateManager();
    }
    return AccountPreferencesStateManager.instance;
  }

  public isHydrated(): boolean {
    return this.hydrated;
  }

  public async rehydrate(): Promise<void> {
    await this.loadFromStorage();
  }

  private async loadFromStorage() {
    try {
      const stored = await AsyncStorage.getItem(PREFERENCES_STORAGE_KEY);
      if (stored) {
        const parsed = JSON.parse(stored);
        if (parsed.selectedPaymentMethodId) this.selectedPaymentMethodId = parsed.selectedPaymentMethodId;
      }
    } catch {
      // Ignore storage errors
    }
    this.hydrated = true;
    this.notifyListeners();
  }

  private async persistToStorage() {
    try {
      await AsyncStorage.setItem(
        PREFERENCES_STORAGE_KEY,
        JSON.stringify({
          selectedPaymentMethodId: this.selectedPaymentMethodId,
        }),
      );
    } catch {
      // Ignore storage errors
    }
  }

  public subscribe(listener: () => void): () => void {
    this.listeners.push(listener);
    return () => {
      this.listeners = this.listeners.filter((l) => l !== listener);
    };
  }

  private notifyListeners() {
    this.listeners.forEach((l) => l());
  }

  private notify() {
    this.notifyListeners();
    this.persistToStorage();
  }

  // ── Payment Methods ──

  public getPaymentMethods(): PaymentMethodFixture[] {
    return [...this.paymentMethods];
  }

  public getSelectedPaymentMethodId(): string {
    return this.selectedPaymentMethodId;
  }

  public getWalletBalanceMad(): number {
    return this.paymentMethods.find((method) => method.type === 'wallet')?.balanceMad || 0;
  }

  public setSelectedPaymentMethod(id: string) {
    if (this.paymentMethods.some((pm) => pm.id === id)) {
      this.selectedPaymentMethodId = id;
      this.paymentMethods = this.paymentMethods.map((pm) => ({
        ...pm,
        isDefault: pm.id === id,
      }));
      this.notify();
    }
  }

  public removePaymentMethod(id: string) {
    this.paymentMethods = this.paymentMethods.filter((pm) => pm.id !== id);
    if (this.selectedPaymentMethodId === id && this.paymentMethods.length > 0) {
      this.selectedPaymentMethodId = this.paymentMethods[0].id;
      this.paymentMethods[0].isDefault = true;
    }
    this.notify();
  }

  // ── Language & Region ──

  public getSelectedLanguage(): AppLanguage {
    return (localeState.getLanguage() as AppLanguage) || 'fr';
  }

  public getLanguage(): AppLanguage {
    return (localeState.getLanguage() as AppLanguage) || 'fr';
  }

  public setSelectedLanguage(language: AppLanguage) {
    if (language === 'fr' || language === 'ar') {
      void localeState.setLanguage(language);
    }
    this.notifyListeners();
  }

  public getRegionInfo(): RegionInfo {
    return { ...this.region };
  }

  public reset() {
    void localeState.setLanguage('fr');
    this.selectedPaymentMethodId = 'pm-card';
    this.paymentMethods = [
      {
        id: 'pm-card',
        type: 'card',
        title: 'Carte bancaire (Attijariwafa / CMI)',
        subtitle: '•••• •••• •••• 4242 (Expire 06/26)',
        iconName: 'credit-card',
        last4: '4242',
        expiry: '06/26',
        brand: 'Visa',
        verificationScenario: 'pending_fixture',
        isDefault: true,
      },
      {
        id: 'pm-mastercard',
        type: 'card',
        title: 'MasterCard',
        subtitle: '•••• •••• •••• 8731 (Expire 11/27)',
        iconName: 'credit-card',
        last4: '8731',
        expiry: '11/27',
        brand: 'MasterCard',
        verificationScenario: 'failed_fixture',
        isDefault: false,
      },
      {
        id: 'pm-cod',
        type: 'cod',
        title: 'Paiement à la livraison (Espèces)',
        subtitle: 'Payez en espèces à la réception de votre commande',
        iconName: 'truck',
        isDefault: false,
      },
      {
        id: 'pm-wallet',
        type: 'wallet',
        title: 'Mayush Wallet',
        subtitle: 'Solde disponible : 1,250 MAD',
        iconName: 'wallet',
        balanceMad: 1250,
        isDefault: false,
      },
    ];
    this.notify();
  }
}

export const accountPreferencesState = AccountPreferencesStateManager.getInstance();
