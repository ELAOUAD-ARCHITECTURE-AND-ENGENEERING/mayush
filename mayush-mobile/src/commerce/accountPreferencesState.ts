import AsyncStorage from '@react-native-async-storage/async-storage';

export interface PaymentMethodFixture {
  id: string;
  type: 'card' | 'cod' | 'wallet';
  title: string;
  subtitle: string;
  iconName: 'credit-card' | 'truck' | 'wallet';
  last4?: string;
  expiry?: string;
  brand?: string;
  isDefault: boolean;
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

  private selectedLanguage: AppLanguage = 'fr';
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
      subtitle: '•••• •••• •••• 4242 (Expire 12/28)',
      iconName: 'credit-card',
      last4: '4242',
      expiry: '12/28',
      brand: 'Visa',
      isDefault: true,
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
      isDefault: false,
    },
  ];

  private selectedPaymentMethodId: string = 'pm-card';
  private listeners: (() => void)[] = [];

  private constructor() {
    this.loadFromStorage();
  }

  public static getInstance(): AccountPreferencesStateManager {
    if (!AccountPreferencesStateManager.instance) {
      AccountPreferencesStateManager.instance = new AccountPreferencesStateManager();
    }
    return AccountPreferencesStateManager.instance;
  }

  private async loadFromStorage() {
    try {
      const stored = await AsyncStorage.getItem(PREFERENCES_STORAGE_KEY);
      if (stored) {
        const parsed = JSON.parse(stored);
        if (parsed.selectedLanguage) this.selectedLanguage = parsed.selectedLanguage;
        if (parsed.selectedPaymentMethodId) this.selectedPaymentMethodId = parsed.selectedPaymentMethodId;
      }
    } catch {
      // Ignore storage errors
    }
  }

  private async persistToStorage() {
    try {
      await AsyncStorage.setItem(
        PREFERENCES_STORAGE_KEY,
        JSON.stringify({
          selectedLanguage: this.selectedLanguage,
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

  private notify() {
    this.listeners.forEach((l) => l());
    this.persistToStorage();
  }

  // ── Payment Methods ──

  public getPaymentMethods(): PaymentMethodFixture[] {
    return [...this.paymentMethods];
  }

  public getSelectedPaymentMethodId(): string {
    return this.selectedPaymentMethodId;
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
    return this.selectedLanguage;
  }

  public getLanguage(): AppLanguage {
    return this.selectedLanguage;
  }

  public setSelectedLanguage(language: AppLanguage) {
    this.selectedLanguage = language;
    this.notify();
  }

  public getRegionInfo(): RegionInfo {
    return { ...this.region };
  }

  public reset() {
    this.selectedLanguage = 'fr';
    this.selectedPaymentMethodId = 'pm-card';
    this.paymentMethods = [
      {
        id: 'pm-card',
        type: 'card',
        title: 'Carte bancaire (Attijariwafa / CMI)',
        subtitle: '•••• •••• •••• 4242 (Expire 12/28)',
        iconName: 'credit-card',
        last4: '4242',
        expiry: '12/28',
        brand: 'Visa',
        isDefault: true,
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
        isDefault: false,
      },
    ];
    this.notify();
  }
}

export const accountPreferencesState = AccountPreferencesStateManager.getInstance();
