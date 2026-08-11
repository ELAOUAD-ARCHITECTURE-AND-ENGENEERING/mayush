/**
 * Pure Frontend Auth State Abstraction for Mayush Mobile.
 * Maintain deterministic local/mock auth state without backend side-effects.
 */
import { SavedAddress, defaultSavedAddresses } from './checkoutState';
import AsyncStorage from '@react-native-async-storage/async-storage';

export const AUTH_SESSION_STORAGE_KEY = 'mayush-mobile:auth-session:v1';

export interface MockUser {
  id: string;
  fullName: string;
  emailOrPhone: string;
  email?: string;
  phone?: string;
  avatarUrl?: string | null;
  city?: string;
  gender?: 'm' | 'f' | 'other' | null;
  birthDate?: string;
  profileCompletionPercent?: number;
}

export interface ProfileDraft {
  fullName: string;
  email: string;
  phone: string;
  city: string;
  gender: 'm' | 'f' | 'other' | null;
  birthDate: string;
}

export interface ContactChangeDraft {
  newEmail: string;
  newPhone: string;
  currentPassword: string;
  newPassword: string;
  confirmPassword: string;
}

export interface ReturnDestination {
  route: string;
  params?: Record<string, any>;
  pendingAction?: 'favorite' | 'checkout' | null;
  favoriteItemId?: string;
}

export interface RegistrationDraft {
  fullName: string;
  emailOrPhone: string;
  password: string;
  agreedToTerms: boolean;
}

export interface ActiveSession {
  id: string;
  device: string;
  browser: string;
  location: string;
  lastActive: string;
  isCurrent: boolean;
  ipAddress?: string;
}

export type AuthStatus =
  | 'hydrating'
  | 'guest'
  | 'logging-in'
  | 'authenticated'
  | 'login-error'
  | 'registering'
  | 'registration-success'
  | 'recovering-password'
  | 'otp-sent'
  | 'otp-error'
  | 'password-reset-success';

export class AuthStateManager {
  private static instance: AuthStateManager;

  private status: AuthStatus = 'guest';
  private user: MockUser | null = null;
  private hydrated = false;
  private loginError: string | null = null;
  private registrationDraft: RegistrationDraft = {
    fullName: '',
    emailOrPhone: '',
    password: '',
    agreedToTerms: false,
  };
  private profileDraft: ProfileDraft = {
    fullName: 'Karim Benjelloun',
    email: 'karim.benjelloun@example.ma',
    phone: '+212 6 61 99 88 77',
    city: 'Casablanca',
    gender: 'm',
    birthDate: '1992-06-15',
  };
  private contactChangeDraft: ContactChangeDraft = {
    newEmail: '',
    newPhone: '',
    currentPassword: '',
    newPassword: '',
    confirmPassword: '',
  };
  private recoveryIdentifier: string = 'youssef@example.ma';
  private recoveryMethod: 'email' | 'phone' = 'email';
  private otpCode: string = '';
  private otpError: string | null = null;
  private resendTimerSeconds: number = 30;
  private newPasswordDraft: { password: string; confirmPassword: string } = {
    password: '',
    confirmPassword: '',
  };
  private returnDestination: ReturnDestination | null = null;
  private twoFactorEnabled: boolean = false;
  private activeSessions: ActiveSession[] = [
    {
      id: 'sess-1',
      device: 'iPhone 15 Pro',
      browser: 'Application Mayush iOS 1.0',
      location: 'Casablanca, Maroc',
      lastActive: 'En ce moment',
      isCurrent: true,
      ipAddress: '196.217.42.12',
    },
    {
      id: 'sess-2',
      device: 'Windows PC (Chrome)',
      browser: 'Chrome 122.0 — Windows 11',
      location: 'Rabat, Maroc',
      lastActive: 'Hier, 14:32',
      isCurrent: false,
      ipAddress: '105.158.12.89',
    },
    {
      id: 'sess-3',
      device: 'MacBook Air (Safari)',
      browser: 'Safari 17.2 — macOS Sonoma',
      location: 'Tanger, Maroc',
      lastActive: 'Il y a 3 jours',
      isCurrent: false,
      ipAddress: '196.200.88.45',
    },
  ];
  private selectedSession: ActiveSession | null = null;
  private savedAddresses: SavedAddress[] = [...defaultSavedAddresses];
  private selectedAddressForEdit: SavedAddress | null = null;
  private addressToDelete: SavedAddress | null = null;
  private listeners: (() => void)[] = [];

  private constructor() {}

  public static getInstance(): AuthStateManager {
    if (!AuthStateManager.instance) {
      AuthStateManager.instance = new AuthStateManager();
    }
    return AuthStateManager.instance;
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

  private async persistSession(): Promise<void> {
    if (!this.user || !this.isAuthenticated()) {
      await AsyncStorage.removeItem(AUTH_SESSION_STORAGE_KEY);
      return;
    }
    await AsyncStorage.setItem(AUTH_SESSION_STORAGE_KEY, JSON.stringify({
      status: 'authenticated',
      user: this.user,
    }));
  }

  public async hydrate(): Promise<void> {
    if (this.hydrated) return;
    this.status = 'hydrating';
    this.notify();
    try {
      const stored = await AsyncStorage.getItem(AUTH_SESSION_STORAGE_KEY);
      if (stored) {
        const parsed = JSON.parse(stored) as { status?: AuthStatus; user?: MockUser };
        if (parsed.status === 'authenticated' && parsed.user?.id && parsed.user.fullName) {
          this.user = { ...parsed.user };
          this.status = 'authenticated';
        } else {
          this.user = null;
          this.status = 'guest';
        }
      } else {
        this.user = null;
        this.status = 'guest';
      }
    } catch {
      this.user = null;
      this.status = 'guest';
    } finally {
      this.hydrated = true;
      this.notify();
    }
  }

  public isHydrated(): boolean {
    return this.hydrated;
  }

  public getStatus(): AuthStatus {
    return this.status;
  }

  public getUser(): MockUser | null {
    return this.user;
  }

  public isAuthenticated(): boolean {
    return Boolean(this.user && (this.status === 'authenticated' || this.status === 'registration-success'));
  }

  public getLoginError(): string | null {
    return this.loginError;
  }

  public getRegistrationDraft(): RegistrationDraft {
    return { ...this.registrationDraft };
  }

  public getReturnDestination(): ReturnDestination | null {
    return this.returnDestination;
  }

  public setReturnDestination(destination: ReturnDestination | null) {
    this.returnDestination = destination;
    this.notify();
  }

  public clearReturnDestination() {
    this.returnDestination = null;
    this.notify();
  }

  public setGuest() {
    this.status = 'guest';
    this.user = null;
    this.loginError = null;
    this.hydrated = true;
    void this.persistSession();
    this.notify();
  }

  public startLogin() {
    this.status = 'logging-in';
    this.loginError = null;
    this.notify();
  }

  public failLogin(error: string = 'Identifiants incorrects. Veuillez réessayer.') {
    this.status = 'login-error';
    this.loginError = error;
    this.notify();
  }

  public completeLogin(emailOrPhone: string, fullName: string = 'Karim Benjelloun') {
    const isEmail = emailOrPhone.includes('@');
    this.status = 'authenticated';
    this.user = {
      id: 'mock-user-101',
      fullName,
      emailOrPhone,
      email: isEmail ? emailOrPhone : 'karim.benjelloun@example.ma',
      phone: !isEmail ? emailOrPhone : '+212 6 61 99 88 77',
      city: 'Casablanca',
      gender: 'm',
      birthDate: '1992-06-15',
      profileCompletionPercent: 60,
    };
    this.profileDraft = {
      fullName: this.user.fullName,
      email: this.user.email || 'karim.benjelloun@example.ma',
      phone: this.user.phone || '+212 6 61 99 88 77',
      city: this.user.city || 'Casablanca',
      gender: (this.user.gender as 'm' | 'f' | 'other' | null) || 'm',
      birthDate: this.user.birthDate || '1992-06-15',
    };
    this.loginError = null;
    this.hydrated = true;
    void this.persistSession();
    this.notify();
  }

  public updateRegistrationDraft(partial: Partial<RegistrationDraft>) {
    this.registrationDraft = {
      ...this.registrationDraft,
      ...partial,
    };
    this.notify();
  }

  public completeRegistration() {
    this.status = 'registration-success';
    const input = this.registrationDraft.emailOrPhone;
    const isEmail = input.includes('@');
    this.user = {
      id: 'mock-user-102',
      fullName: this.registrationDraft.fullName || 'Nouveau Membre',
      emailOrPhone: input,
      email: isEmail ? input : 'nouveau.membre@example.ma',
      phone: !isEmail ? input : '+212 6 61 99 88 77',
      city: 'Casablanca',
      gender: 'm',
      birthDate: '1995-01-01',
      profileCompletionPercent: 60,
    };
    this.profileDraft = {
      fullName: this.user.fullName,
      email: this.user.email || 'nouveau.membre@example.ma',
      phone: this.user.phone || '+212 6 61 99 88 77',
      city: 'Casablanca',
      gender: 'm',
      birthDate: '1995-01-01',
    };
    this.hydrated = true;
    void this.persistSession();
    this.notify();
  }

  public getProfileDraft(): ProfileDraft {
    if (this.user) {
      return {
        fullName: this.user.fullName,
        email: this.user.email || 'karim.benjelloun@example.ma',
        phone: this.user.phone || '+212 6 61 99 88 77',
        city: this.user.city || 'Casablanca',
        gender: (this.user.gender as 'm' | 'f' | 'other' | null) || 'm',
        birthDate: this.user.birthDate || '1992-06-15',
      };
    }
    return { ...this.profileDraft };
  }

  public updateProfileDraft(partial: Partial<ProfileDraft>) {
    this.profileDraft = {
      ...this.profileDraft,
      ...partial,
    };
    this.notify();
  }

  public saveProfileFromDraft(): MockUser | null {
    if (this.user) {
      this.user = {
        ...this.user,
        fullName: this.profileDraft.fullName,
        email: this.profileDraft.email,
        phone: this.profileDraft.phone,
        city: this.profileDraft.city,
        gender: this.profileDraft.gender,
        birthDate: this.profileDraft.birthDate,
        profileCompletionPercent: 85,
      };
      this.notify();
      void this.persistSession();
      return this.user;
    }
    return null;
  }

  public getContactChangeDraft(): ContactChangeDraft {
    return { ...this.contactChangeDraft };
  }

  public updateContactChangeDraft(partial: Partial<ContactChangeDraft>) {
    this.contactChangeDraft = {
      ...this.contactChangeDraft,
      ...partial,
    };
    this.notify();
  }

  public changeEmail(newEmail: string) {
    if (this.user) {
      this.user.email = newEmail;
      this.user.emailOrPhone = newEmail;
      this.profileDraft.email = newEmail;
      void this.persistSession();
      this.notify();
    }
  }

  public changePhone(newPhone: string) {
    if (this.user) {
      this.user.phone = newPhone;
      this.user.emailOrPhone = newPhone;
      this.profileDraft.phone = newPhone;
      void this.persistSession();
      this.notify();
    }
  }

  public changeAvatar(avatarUrl: string | null) {
    if (this.user) {
      this.user.avatarUrl = avatarUrl;
      void this.persistSession();
      this.notify();
    }
  }

  public logout() {
    this.status = 'guest';
    this.user = null;
    this.loginError = null;
    this.hydrated = true;
    void this.persistSession();
    this.notify();
  }

  public getRecoveryIdentifier(): string {
    return this.recoveryIdentifier;
  }

  public getRecoveryMethod(): 'email' | 'phone' {
    return this.recoveryMethod;
  }

  public getOtpCode(): string {
    return this.otpCode;
  }

  public getOtpError(): string | null {
    return this.otpError;
  }

  public getResendTimerSeconds(): number {
    return this.resendTimerSeconds;
  }

  public getNewPasswordDraft(): { password: string; confirmPassword: string } {
    return { ...this.newPasswordDraft };
  }

  public startPasswordRecovery(identifier: string, method: 'email' | 'phone' = 'email') {
    this.recoveryIdentifier = identifier;
    this.recoveryMethod = method;
    this.status = 'recovering-password';
    this.notify();
  }

  public setOtpCode(code: string) {
    this.otpCode = code;
    this.otpError = null;
    this.notify();
  }

  public failOtp(error: string = 'Code OTP incorrect. Veuillez réessayer.') {
    this.status = 'otp-error';
    this.otpError = error;
    this.notify();
  }

  public updateNewPasswordDraft(partial: Partial<{ password: string; confirmPassword: string }>) {
    this.newPasswordDraft = {
      ...this.newPasswordDraft,
      ...partial,
    };
    this.notify();
  }

  public completePasswordReset() {
    this.status = 'password-reset-success';
    this.notify();
  }

  public isTwoFactorEnabled(): boolean {
    return this.twoFactorEnabled;
  }

  public setTwoFactorEnabled(enabled: boolean) {
    this.twoFactorEnabled = enabled;
    this.notify();
  }

  public toggleTwoFactor(): boolean {
    this.twoFactorEnabled = !this.twoFactorEnabled;
    this.notify();
    return this.twoFactorEnabled;
  }

  public getActiveSessions(): ActiveSession[] {
    return [...this.activeSessions];
  }

  public getSelectedSession(): ActiveSession | null {
    return this.selectedSession;
  }

  public setSelectedSession(session: ActiveSession | null) {
    this.selectedSession = session;
    this.notify();
  }

  public disconnectSession(sessionId: string) {
    this.activeSessions = this.activeSessions.filter((s) => s.id !== sessionId);
    if (this.selectedSession?.id === sessionId) {
      this.selectedSession = null;
    }
    this.notify();
  }

  // ── Address Book State ──

  public getSavedAddresses(): SavedAddress[] {
    return [...this.savedAddresses];
  }

  /** Hydrates the canonical address book from checkout's durable session. */
  public replaceSavedAddresses(addresses: SavedAddress[]) {
    this.savedAddresses = addresses.map((address) => ({ ...address }));
    this.notify();
  }

  public addAddress(address: SavedAddress) {
    if (address.isDefault) {
      this.savedAddresses = this.savedAddresses.map((a) => ({ ...a, isDefault: false }));
    }
    this.savedAddresses.push(address);
    this.notify();
  }

  public updateAddress(id: string, updated: Partial<SavedAddress>) {
    if (updated.isDefault) {
      this.savedAddresses = this.savedAddresses.map((a) => ({ ...a, isDefault: a.id === id }));
    }
    this.savedAddresses = this.savedAddresses.map((a) =>
      a.id === id ? { ...a, ...updated } : a,
    );
    this.notify();
  }

  public deleteAddress(id: string) {
    const wasDefault = this.savedAddresses.find((a) => a.id === id)?.isDefault;
    this.savedAddresses = this.savedAddresses.filter((a) => a.id !== id);
    if (wasDefault && this.savedAddresses.length > 0) {
      this.savedAddresses[0] = { ...this.savedAddresses[0], isDefault: true };
    }
    if (this.addressToDelete?.id === id) {
      this.addressToDelete = null;
    }
    this.notify();
  }

  public setDefaultAddress(id: string) {
    this.savedAddresses = this.savedAddresses.map((a) => ({
      ...a,
      isDefault: a.id === id,
    }));
    this.notify();
  }

  public getSelectedAddressForEdit(): SavedAddress | null {
    return this.selectedAddressForEdit;
  }

  public setSelectedAddressForEdit(address: SavedAddress | null) {
    this.selectedAddressForEdit = address;
    this.notify();
  }

  public getAddressToDelete(): SavedAddress | null {
    return this.addressToDelete;
  }

  public setAddressToDelete(address: SavedAddress | null) {
    this.addressToDelete = address;
    this.notify();
  }

  public reset() {
    this.status = 'guest';
    this.user = null;
    this.hydrated = false;
    this.loginError = null;
    this.registrationDraft = {
      fullName: '',
      emailOrPhone: '',
      password: '',
      agreedToTerms: false,
    };
    this.recoveryIdentifier = 'youssef@example.ma';
    this.recoveryMethod = 'email';
    this.otpCode = '';
    this.otpError = null;
    this.resendTimerSeconds = 30;
    this.newPasswordDraft = { password: '', confirmPassword: '' };
    this.returnDestination = null;
    this.twoFactorEnabled = false;
    this.activeSessions = [
      {
        id: 'sess-1',
        device: 'iPhone 15 Pro',
        browser: 'Application Mayush iOS 1.0',
        location: 'Casablanca, Maroc',
        lastActive: 'En ce moment',
        isCurrent: true,
        ipAddress: '196.217.42.12',
      },
      {
        id: 'sess-2',
        device: 'Windows PC (Chrome)',
        browser: 'Chrome 122.0 — Windows 11',
        location: 'Rabat, Maroc',
        lastActive: 'Hier, 14:32',
        isCurrent: false,
        ipAddress: '105.158.12.89',
      },
      {
        id: 'sess-3',
        device: 'MacBook Air (Safari)',
        browser: 'Safari 17.2 — macOS Sonoma',
        location: 'Tanger, Maroc',
        lastActive: 'Il y a 3 jours',
        isCurrent: false,
        ipAddress: '196.200.88.45',
      },
    ];
    this.selectedSession = null;
    this.savedAddresses = [...defaultSavedAddresses];
    this.selectedAddressForEdit = null;
    this.addressToDelete = null;
    this.notify();
  }
}

export const authState = AuthStateManager.getInstance();

export const createCheckoutAuthReturnDestination = (checkoutAttemptId: string, route: 'payment-method' | 'wallet-balance' = 'payment-method'): ReturnDestination => ({
  route,
  params: { checkoutAttemptId },
  pendingAction: 'checkout',
});
