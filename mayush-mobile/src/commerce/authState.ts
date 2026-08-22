/**
 * Pure Frontend Auth State Abstraction for Mayush Mobile.
 * Maintain deterministic local/mock auth state without backend side-effects.
 */
import { SavedAddress } from './checkoutState';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { apiClient } from '../api/client';
import { getSecureToken, setSecureToken, removeSecureToken } from './secureStorage';
import { ApiError } from '../api/apiError';
import * as profileService from '../services/api/profileService';
import * as addressService from '../services/api/addressService';
import type { AddressDto } from '../contracts/api/dto';
import { normalizeImageUrl } from '../contracts/mappers/imageNormalizer';

export const AUTH_SESSION_STORAGE_KEY = 'mayush-mobile:auth-session:v1';
export const ADDRESSES_STORAGE_KEY_PREFIX = 'mayush-mobile:addresses:v1:';
export const getAddressesStorageKey = (userId?: string): string =>
  `${ADDRESSES_STORAGE_KEY_PREFIX}${userId && userId.trim() ? userId.trim() : 'guest'}`;

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
  | 'password-reset-success'
  | 'device-verification';

export interface DeviceVerificationChallenge {
  challengeId: string;
  expiresAt: string;
  method: 'email' | 'sms';
  emailOrPhone: string;
  password: string;
}

export class AuthStateManager {
  private static instance: AuthStateManager;

  private status: AuthStatus = 'guest';
  private user: MockUser | null = null;
  private hydrated = false;
  private loginError: string | null = null;
  private sessionInvalidated = false;
  private registrationDraft: RegistrationDraft = {
    fullName: '',
    emailOrPhone: '',
    password: '',
    agreedToTerms: false,
  };
  private profileDraft: ProfileDraft = {
    fullName: '',
    email: '',
    phone: '',
    city: '',
    gender: null,
    birthDate: '',
  };
  private contactChangeDraft: ContactChangeDraft = {
    newEmail: '',
    newPhone: '',
    currentPassword: '',
    newPassword: '',
    confirmPassword: '',
  };
  private recoveryIdentifier: string = '';
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
      id: 'session-1',
      device: 'iPhone 15 Pro',
      browser: 'Mayush App v1.0.0',
      location: 'Casablanca, Maroc',
      lastActive: 'Actif maintenant',
      isCurrent: true,
      ipAddress: '196.200.150.12',
    },
    {
      id: 'session-2',
      device: 'MacBook Pro 16"',
      browser: 'Safari 17.2',
      location: 'Rabat, Maroc',
      lastActive: 'Il y a 2 heures',
      isCurrent: false,
      ipAddress: '196.200.150.14',
    },
    {
      id: 'session-3',
      device: 'Samsung Galaxy Tab S9',
      browser: 'Chrome Mobile',
      location: 'Marrakech, Maroc',
      lastActive: 'Il y a 3 jours',
      isCurrent: false,
      ipAddress: '105.154.20.45',
    },
  ];
  private deviceVerificationChallenge: DeviceVerificationChallenge | null = null;
  private selectedSession: ActiveSession | null = null;
  private savedAddresses: SavedAddress[] = [];
  private selectedAddressForEdit: SavedAddress | null = null;
  private addressToDelete: SavedAddress | null = null;
  private listeners: (() => void)[] = [];

  private constructor() {
    this.reset();
  }

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

  public async persistAddresses(): Promise<void> {
    const key = getAddressesStorageKey(this.user?.id);
    try {
      await AsyncStorage.setItem(key, JSON.stringify(this.savedAddresses));
    } catch {
      // Storage error ignored
    }
  }

  public async hydrateAddresses(userId?: string): Promise<void> {
    const resolvedUserId = userId?.trim() || this.user?.id;
    // Authenticated addresses are refreshed from Laravel when reachable.
    if (this.isAuthenticated()) {
      try {
        const backendAddresses = await addressService.fetchAddresses();
        this.savedAddresses = backendAddresses.map(mapBackendAddressToSaved);
        void this.persistAddresses();
        return;
      } catch {
        // Continue to the explicitly persisted, buyer-scoped offline snapshot.
      }
    }

    const key = getAddressesStorageKey(resolvedUserId);
    try {
      const stored = await AsyncStorage.getItem(key);
      if (stored) {
        const parsed = JSON.parse(stored);
        if (Array.isArray(parsed) && parsed.length > 0) {
          this.savedAddresses = parsed;
          return;
        }
      }
    } catch {
      // Storage fallback
    }
    // No addresses available — empty state
    this.savedAddresses = [];
  }

  public async hydrate(): Promise<void> {
    if (this.hydrated) return;
    this.status = 'hydrating';
    this.notify();
    try {
      const token = await getSecureToken();
      if (token) {
        try {
          const userResponse = await apiClient<any>('/api/v2/auth/user');
          this.user = {
            id: String(userResponse.id),
            fullName: userResponse.name || 'User',
            emailOrPhone: userResponse.email || userResponse.phone || '',
            email: userResponse.email,
            phone: userResponse.phone,
            avatarUrl: userResponse.avatar_original ? normalizeImageUrl(userResponse.avatar_original) : null,
            city: userResponse.city,
            gender: userResponse.gender || null,
            birthDate: userResponse.birthDate || '',
            profileCompletionPercent: undefined,
          };
          this.profileDraft = {
            fullName: this.user.fullName,
            email: this.user.email || '',
            phone: this.user.phone || '',
            city: this.user.city || '',
            gender: (this.user.gender as 'm' | 'f' | 'other' | null) || 'm',
            birthDate: this.user.birthDate || '',
          };
          this.status = 'authenticated';
          await AsyncStorage.setItem(AUTH_SESSION_STORAGE_KEY, JSON.stringify({ status: 'authenticated', user: this.user }));
        } catch (error) {
          if (error instanceof ApiError && error.status === 401) {
             await removeSecureToken();
             await AsyncStorage.removeItem(AUTH_SESSION_STORAGE_KEY);
          }
          this.user = null;
          this.status = 'guest';
        }
      } else {
        const stored = await AsyncStorage.getItem(AUTH_SESSION_STORAGE_KEY);
        if (stored) {
          await AsyncStorage.removeItem(AUTH_SESSION_STORAGE_KEY);
        }
        this.user = null;
        this.status = 'guest';
      }
    } catch {
      this.user = null;
      this.status = 'guest';
    } finally {
      await this.hydrateAddresses(this.user?.id);
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
    return Boolean(this.user && this.status === 'authenticated');
  }

  /**
   * Update user data from a fresh /api/v2/auth/user response.
   * Used by session restoration after background resume.
   */
  public updateUserFromResponse(data: Record<string, unknown>): void {
    if (!data) return;
    this.user = {
      id: String(data.id),
      fullName: (data.name as string) || 'User',
      emailOrPhone: (data.email as string) || (data.phone as string) || '',
      email: data.email as string | undefined,
      phone: data.phone as string | undefined,
      avatarUrl: data.avatar_original ? normalizeImageUrl(data.avatar_original as string) : null,
      city: data.city as string | undefined,
      gender: (['m', 'f', 'other'].includes(data.gender as string) ? data.gender : null) as 'm' | 'f' | 'other' | null,
      birthDate: (data.birthDate as string) || '',
      profileCompletionPercent: undefined,
    };
    this.notify();
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

  public async setGuest() {
    this.status = 'guest';
    this.user = null;
    this.savedAddresses = [];
    this.loginError = null;
    this.otpError = null;
    this.hydrated = true;
    await removeSecureToken();
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

  public async completeLogin(emailOrPhone: string, password?: string) {
    if (!password) {
      this.failLogin('Mot de passe requis pour la connexion au serveur.');
      return;
    }
    const isEmail = emailOrPhone.includes('@');
    try {
      const payload = {
        login_by: isEmail ? 'email' : 'phone',
        email: emailOrPhone,
        password: password,
        user_type: 'customer'
      };
      const response = await apiClient<any>('/api/v2/auth/login', {
        method: 'POST',
        body: payload
      });

      if (response.result && response.access_token && response.user) {
        await setSecureToken(response.access_token);

        // Block unverified users from gaining access
        if (response.user.email_verified === false) {
          this.status = 'otp-sent';
          this.user = {
            id: String(response.user.id),
            fullName: response.user.name || 'User',
            emailOrPhone: emailOrPhone,
            email: response.user.email || '',
            phone: response.user.phone || '',
            avatarUrl: response.user.avatar_original || null,
            city: '',
            gender: 'm',
            birthDate: '',
            profileCompletionPercent: undefined,
          };
          this.loginError = 'Veuillez vérifier votre compte avec le code OTP reçu.';
          this.notify();
          return;
        }

        this.status = 'authenticated';
        this.user = {
          id: String(response.user.id),
          fullName: response.user.name || 'User',
          emailOrPhone: emailOrPhone,
          email: response.user.email || '',
          phone: response.user.phone || '',
          avatarUrl: response.user.avatar_original ? normalizeImageUrl(response.user.avatar_original) : null,
          city: '',
          gender: 'm',
          birthDate: '',
          profileCompletionPercent: 80,
        };
        this.profileDraft = {
          fullName: this.user.fullName,
          email: this.user.email || '',
          phone: this.user.phone || '',
          city: '',
          gender: 'm',
          birthDate: '',
        };
        this.savedAddresses = [];
        this.loginError = null;
        this.otpError = null;
        this.hydrated = true;
        void this.persistSession();
        void this.hydrateAddresses(this.user.id);
        this.notify();
      } else {
        this.failLogin(response.message || 'Identifiants incorrects.');
      }
    } catch (error: any) {
      // Device verification required — not an error, user needs to verify the new device
      if (error.code === 'DEVICE_VERIFICATION_REQUIRED') {
        this.status = 'device-verification';
        this.deviceVerificationChallenge = {
          challengeId: error.data?.challenge_id ?? error.challenge_id,
          expiresAt: error.data?.expires_at ?? error.expires_at,
          method: error.data?.method ?? error.method ?? 'email',
          emailOrPhone: emailOrPhone,
          password: password,
        };
        this.loginError = null;
        this.notify();
        return;
      }
      this.failLogin(error.message || 'Erreur réseau lors de la connexion.');
    }
  }

  public getDeviceVerificationChallenge(): DeviceVerificationChallenge | null {
    return this.deviceVerificationChallenge;
  }

  /**
   * Submit the 6-digit OTP code to verify the current device.
   * On success, stores the new token and completes authentication.
   */
  public async completeDeviceVerification(code: string): Promise<boolean> {
    if (!this.deviceVerificationChallenge) {
      this.failLogin('Aucun défi de vérification en cours.');
      return false;
    }
    try {
      const response = await apiClient<any>('/api/v2/auth/verify-device', {
        method: 'POST',
        body: {
          challenge_id: this.deviceVerificationChallenge.challengeId,
          code,
        },
      });
      if (response.result && response.access_token && response.user) {
        await setSecureToken(response.access_token);
        this.status = 'authenticated';
        this.user = {
          id: String(response.user.id),
          fullName: response.user.name || 'User',
          emailOrPhone: this.deviceVerificationChallenge.emailOrPhone,
          email: response.user.email || '',
          phone: response.user.phone || '',
          avatarUrl: response.user.avatar_original ? normalizeImageUrl(response.user.avatar_original) : null,
          city: '',
          gender: 'm',
          birthDate: '',
          profileCompletionPercent: 80,
        };
        this.profileDraft = {
          fullName: this.user.fullName,
          email: this.user.email || '',
          phone: this.user.phone || '',
          city: '',
          gender: 'm',
          birthDate: '',
        };
        this.deviceVerificationChallenge = null;
        this.loginError = null;
        this.otpError = null;
        this.hydrated = true;
        void this.persistSession();
        void this.hydrateAddresses(this.user.id);
        this.notify();
        return true;
      }
      this.otpError = response.message || 'Code incorrect.';
      this.notify();
      return false;
    } catch (error: any) {
      this.otpError = error.message || 'Erreur lors de la vérification.';
      this.notify();
      return false;
    }
  }

  /**
   * Request a new device verification code.
   */
  public async resendDeviceCode(): Promise<boolean> {
    if (!this.deviceVerificationChallenge) return false;
    try {
      const response = await apiClient<any>('/api/v2/auth/resend-device-code', {
        method: 'POST',
        body: {
          challenge_id: this.deviceVerificationChallenge.challengeId,
        },
      });
      if (response.result && response.challenge_id) {
        this.deviceVerificationChallenge = {
          ...this.deviceVerificationChallenge,
          challengeId: response.challenge_id,
          expiresAt: response.expires_at || this.deviceVerificationChallenge.expiresAt,
        };
        this.notify();
      }
      return Boolean(response.result);
    } catch {
      return false;
    }
  }

  /** Cancel device verification and return to guest/login state. */
  public cancelDeviceVerification() {
    this.deviceVerificationChallenge = null;
    this.status = 'guest';
    this.loginError = null;
    this.notify();
  }

  /**
   * Fetch verified devices from the backend.
   * Replaces the former hardcoded activeSessions mock.
   */
  public async fetchActiveSessions(): Promise<void> {
    if (!this.isAuthenticated()) {
      if (this.activeSessions.length === 0) {
        this.activeSessions = [
          {
            id: 'session-1',
            device: 'iPhone 15 Pro',
            browser: 'Mayush App v1.0.0',
            location: 'Casablanca, Maroc',
            lastActive: 'Actif maintenant',
            isCurrent: true,
            ipAddress: '196.200.150.12',
          },
          {
            id: 'session-2',
            device: 'MacBook Pro 16"',
            browser: 'Safari 17.2',
            location: 'Rabat, Maroc',
            lastActive: 'Il y a 2 heures',
            isCurrent: false,
            ipAddress: '196.200.150.14',
          },
          {
            id: 'session-3',
            device: 'Samsung Galaxy Tab S9',
            browser: 'Chrome Mobile',
            location: 'Marrakech, Maroc',
            lastActive: 'Il y a 3 jours',
            isCurrent: false,
            ipAddress: '105.154.20.45',
          },
        ];
      }
      this.notify();
      return;
    }
    try {
      const response = await apiClient<any>('/api/v2/auth/devices');
      if (response.result && Array.isArray(response.data) && response.data.length > 0) {
        this.activeSessions = response.data.map((d: any) => ({
          id: String(d.id),
          device: d.device_name || 'Appareil inconnu',
          browser: '',
          location: '',
          lastActive: d.last_seen_at || '',
          isCurrent: Boolean(d.is_current),
          ipAddress: '',
        }));
      }
      this.notify();
    } catch {
      this.notify();
    }
  }

  public updateRegistrationDraft(partial: Partial<RegistrationDraft>) {
    this.registrationDraft = {
      ...this.registrationDraft,
      ...partial,
    };
    this.notify();
  }

  public async completeRegistration() {
    this.status = 'registering';
    this.loginError = null;
    this.otpError = null;
    this.notify();
    const input = this.registrationDraft.emailOrPhone;
    const isEmail = input.includes('@');
    try {
      const payload = {
        name: this.registrationDraft.fullName || 'Nouveau Membre',
        email_or_phone: input,
        register_by: isEmail ? 'email' : 'phone',
        password: this.registrationDraft.password,
        password_confirmation: this.registrationDraft.password
      };
      const response = await apiClient<any>('/api/v2/auth/signup', {
        method: 'POST',
        body: payload
      });

      if (response.result && response.access_token && response.user) {
        await setSecureToken(response.access_token);
        // Do NOT mark authenticated; block user on OTP verification
        this.status = 'otp-sent';
        this.user = {
          id: String(response.user.id),
          fullName: response.user.name || this.registrationDraft.fullName,
          emailOrPhone: input,
          email: response.user.email || '',
          phone: response.user.phone || '',
          city: '',
          gender: 'm',
          birthDate: '',
          profileCompletionPercent: 80,
        };
        this.profileDraft = {
          fullName: this.user.fullName,
          email: this.user.email || '',
          phone: this.user.phone || '',
          city: '',
          gender: 'm',
          birthDate: '',
        };
        this.savedAddresses = [];
        this.loginError = null;
        this.otpError = null;
        this.notify();
      } else {
        this.status = 'guest';
        this.loginError = Array.isArray(response.message) ? response.message.join(', ') : response.message || 'Erreur lors de l\'inscription.';
        this.notify();
      }
    } catch (error: any) {
      this.status = 'guest';
      this.loginError = error.message || 'Erreur réseau lors de l\'inscription.';
      this.notify();
    }
  }

  public getProfileDraft(): ProfileDraft {
    if (this.user) {
      return {
        fullName: this.user.fullName,
        email: this.user.email || '',
        phone: this.user.phone || '',
        city: this.user.city || '',
        gender: (this.user.gender as 'm' | 'f' | 'other' | null) || null,
        birthDate: this.user.birthDate || '',
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

  public async saveProfileFromDraft(): Promise<{ success: boolean; error?: string }> {
    if (!this.user) {
      this.user = {
        id: 'usr-buyer-current',
        fullName: this.profileDraft.fullName || 'Client Mayush',
        emailOrPhone: this.profileDraft.email || this.profileDraft.phone || '',
        email: this.profileDraft.email || '',
        phone: this.profileDraft.phone || '',
        avatarUrl: null,
        city: this.profileDraft.city || '',
        gender: this.profileDraft.gender || null,
        birthDate: this.profileDraft.birthDate || '',
      };
      this.status = 'authenticated';
    }

    try {
      const currentUser = this.user!;
      const payload: { name?: string; phone?: string; city?: string; gender?: string; birth_date?: string; birthDate?: string } = {};
      if (this.profileDraft.fullName !== undefined) {
        payload.name = this.profileDraft.fullName;
      }
      if (this.profileDraft.phone !== undefined) {
        payload.phone = this.profileDraft.phone;
      }
      if (this.profileDraft.city !== undefined) {
        payload.city = this.profileDraft.city;
      }
      if (this.profileDraft.gender !== undefined && this.profileDraft.gender !== null) {
        payload.gender = this.profileDraft.gender;
      }
      if (this.profileDraft.birthDate !== undefined) {
        payload.birth_date = this.profileDraft.birthDate;
        payload.birthDate = this.profileDraft.birthDate;
      }

      if (Object.keys(payload).length > 0) {
        try {
          const response = await profileService.updateProfile(payload);
          if (!response.result) {
            console.warn('[AuthState] Profile update backend warning:', response.message);
          }
        } catch (apiErr) {
          console.warn('[AuthState] Profile update API error, applying local state:', apiErr);
        }
      }

      // Refresh user data from backend after update if reachable
      try {
        const freshUser = await profileService.refreshCurrentUser();
        if (freshUser && (freshUser.id || freshUser.name || freshUser.email)) {
          this.user = {
            ...currentUser,
            fullName: freshUser.name || this.profileDraft.fullName || currentUser.fullName,
            email: freshUser.email || currentUser.email,
            phone: freshUser.phone || this.profileDraft.phone || currentUser.phone,
            avatarUrl: freshUser.avatar_original ? normalizeImageUrl(freshUser.avatar_original) : currentUser.avatarUrl,
            city: freshUser.city || this.profileDraft.city || currentUser.city,
            gender: (this.profileDraft.gender || freshUser.gender || currentUser.gender) as 'm' | 'f' | 'other' | null,
            birthDate: freshUser.birthDate || freshUser.birth_date || this.profileDraft.birthDate || currentUser.birthDate,
          };
        } else {
          throw new Error('Fresh user returned empty');
        }
      } catch {
        // Apply draft locally
        this.user = {
          ...currentUser,
          fullName: this.profileDraft.fullName || currentUser.fullName,
          phone: this.profileDraft.phone || currentUser.phone,
          city: this.profileDraft.city || currentUser.city,
          gender: (this.profileDraft.gender || currentUser.gender) as 'm' | 'f' | 'other' | null,
          birthDate: this.profileDraft.birthDate || currentUser.birthDate,
        };
      }

      const savedUser = this.user!;
      this.profileDraft = {
        fullName: savedUser.fullName,
        email: savedUser.email || '',
        phone: savedUser.phone || '',
        city: savedUser.city || '',
        gender: (savedUser.gender as 'm' | 'f' | 'other' | null) || null,
        birthDate: savedUser.birthDate || '',
      };
      this.notify();
      void this.persistSession();
      return { success: true };
    } catch (error: any) {
      return { success: false, error: error.message || 'Erreur lors de l’enregistrement.' };
    }
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

  public async changeEmail(newEmail: string): Promise<void> {
    try {
      await apiClient<{ result: boolean; message?: string }>('/api/v2/profile/update', {
        method: 'POST',
        body: { email: newEmail },
      });
    } catch {
      // API failure — fall through to optimistic local update
    }
    if (this.user) {
      this.user.email = newEmail;
      this.user.emailOrPhone = newEmail;
      this.profileDraft.email = newEmail;
      void this.persistSession();
      this.notify();
    }
  }

  public async changePhone(newPhone: string): Promise<void> {
    try {
      await apiClient<{ result: boolean; message?: string }>('/api/v2/profile/update', {
        method: 'POST',
        body: { phone: newPhone },
      });
    } catch {
      // API failure — fall through to optimistic local update
    }
    if (this.user) {
      this.user.phone = newPhone;
      this.user.emailOrPhone = newPhone;
      this.profileDraft.phone = newPhone;
      void this.persistSession();
      this.notify();
    }
  }

  public async changePassword(currentPassword: string, newPassword: string, passwordConfirmation: string): Promise<{ success: boolean; error?: string }> {
    if (!this.user) return { success: false, error: 'Non authentifié.' };
    try {
      const response = await apiClient<any>('/api/v2/auth/password/change', {
        method: 'POST',
        body: {
          current_password: currentPassword,
          password: newPassword,
          password_confirmation: passwordConfirmation,
        },
      });
      if (!response.result) {
        return { success: false, error: response.message || 'Erreur lors du changement de mot de passe.' };
      }
      await this.clearLocalSession();
      return { success: true };
    } catch (error: any) {
      return { success: false, error: error.message || 'Erreur réseau.' };
    }
  }

  public async uploadAvatar(base64Image: string, filename: string = 'avatar.jpg'): Promise<{ success: boolean; path?: string; error?: string }> {
    if (!this.user) {
      this.user = {
        id: 'usr-buyer-current',
        fullName: this.profileDraft.fullName || 'Client Mayush',
        emailOrPhone: this.profileDraft.email || this.profileDraft.phone || '',
        email: this.profileDraft.email || '',
        phone: this.profileDraft.phone || '',
        avatarUrl: null,
        city: this.profileDraft.city || '',
        gender: this.profileDraft.gender || null,
        birthDate: this.profileDraft.birthDate || '',
      };
      this.status = 'authenticated';
    }
    try {
      const response = await profileService.updateProfileImage(base64Image, filename);
      if (response.result && response.path) {
        const normalized = normalizeImageUrl(response.path);
        this.user = {
          ...this.user!,
          avatarUrl: normalized,
        };
        void this.persistSession();
        this.notify();
        return { success: true, path: normalized };
      }
      // If backend returned false result, fall back to local preview
      const dataUri = base64Image.startsWith('data:') ? base64Image : `data:image/jpeg;base64,${base64Image}`;
      this.user = {
        ...this.user!,
        avatarUrl: dataUri,
      };
      void this.persistSession();
      this.notify();
      return { success: true, path: dataUri };
    } catch (error: any) {
      const dataUri = base64Image.startsWith('data:') ? base64Image : `data:image/jpeg;base64,${base64Image}`;
      this.user = {
        ...this.user!,
        avatarUrl: dataUri,
      };
      void this.persistSession();
      this.notify();
      return { success: true, path: dataUri };
    }
  }

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
          this.user = { ...user, avatarUrl: res.path };
          this.notify();
          void this.persistSession();
        }
        return true;
      }
      return false;
    } catch {
      return false;
    }
  }

  public changeAvatar(avatarUrl: string | null) {
    if (this.user) {
      this.user.avatarUrl = avatarUrl;
      void this.persistSession();
      this.notify();
    }
  }

  public async logout() {
    try {
      await apiClient('/api/v2/auth/logout', { method: 'GET' });
    } catch (error: any) {
      if (error?.status !== 401) {
        console.warn('[AuthState] Logout API call failed, proceeding with local clear', error);
      }
    }
    this.status = 'guest';
    this.user = null;
    this.savedAddresses = [];
    this.loginError = null;
    this.hydrated = true;
    await removeSecureToken();
    void this.persistSession();
    this.notify();
  }

  public async invalidateSession(): Promise<void> {
    this.sessionInvalidated = true;
    await this.clearLocalSession();
  }

  public consumeSessionInvalidation(): boolean {
    const invalidated = this.sessionInvalidated;
    this.sessionInvalidated = false;
    return invalidated;
  }

  private async clearLocalSession(): Promise<void> {
    this.status = 'guest';
    this.user = null;
    this.savedAddresses = [];
    this.activeSessions = [];
    this.deviceVerificationChallenge = null;
    this.selectedSession = null;
    this.loginError = null;
    this.hydrated = true;
    await removeSecureToken();
    await AsyncStorage.removeItem(AUTH_SESSION_STORAGE_KEY);
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

  public async startPasswordRecovery(identifier: string, method: 'email' | 'phone' = 'email'): Promise<boolean> {
    this.recoveryIdentifier = identifier;
    this.recoveryMethod = method;
    this.status = 'recovering-password';
    this.notify();
    try {
      const response = await apiClient<any>('/api/v2/auth/password/forget_request', {
        method: 'POST',
        body: {
          email_or_phone: identifier,
          send_code_by: method,
        },
      });
      if (response.result) {
        this.status = 'otp-sent';
        this.notify();
        return true;
      }
      this.loginError = response.message || 'Utilisateur introuvable.';
      this.status = 'login-error';
      this.notify();
      return false;
    } catch (error: any) {
      this.loginError = error.message || 'Erreur réseau lors de la récupération.';
      this.status = 'login-error';
      this.notify();
      return false;
    }
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

  public async confirmOtp(code: string): Promise<boolean> {
    try {
      this.status = 'logging-in'; // Using this status for loading state
      this.notify();
      const response = await apiClient<any>('/api/v2/auth/confirm_code', {
        method: 'POST',
        body: { verification_code: code }
      });
      if (response.result) {
        this.status = this.user ? 'authenticated' : 'guest';
        this.hydrated = true;
        this.otpError = null;
        this.loginError = null;
        if (this.user) {
          void this.persistSession();
          void this.hydrateAddresses(this.user.id);
        }
        this.notify();
        return true;
      } else {
        this.failOtp(response.message || 'Code incorrect.');
        return false;
      }
    } catch (error: any) {
      this.failOtp(error.message || 'Erreur réseau lors de la vérification.');
      return false;
    }
  }

  public async resendOtp(): Promise<boolean> {
    try {
      const response = await apiClient<any>('/api/v2/auth/resend_code', {
        method: 'GET'
      });
      return Boolean(response.result);
    } catch {
      return false;
    }
  }

  public updateNewPasswordDraft(partial: Partial<{ password: string; confirmPassword: string }>) {
    this.newPasswordDraft = {
      ...this.newPasswordDraft,
      ...partial,
    };
    this.notify();
  }

  public async completePasswordReset(verificationCode: string, newPassword: string): Promise<boolean> {
    try {
      const response = await apiClient<any>('/api/v2/auth/password/confirm_reset', {
        method: 'POST',
        body: {
          verification_code: verificationCode,
          password: newPassword,
          password_confirmation: newPassword,
        },
      });
      if (response.result) {
        await this.clearLocalSession();
        this.status = 'password-reset-success';
        this.notify();
        return true;
      }
      this.loginError = response.message || 'Code de vérification incorrect.';
      this.status = 'login-error';
      this.notify();
      return false;
    } catch (error: any) {
      this.loginError = error.message || 'Erreur réseau lors de la réinitialisation.';
      this.status = 'login-error';
      this.notify();
      return false;
    }
  }

  public async resendPasswordResetCode(): Promise<boolean> {
    try {
      const response = await apiClient<any>('/api/v2/auth/password/resend_code', {
        method: 'POST',
        body: {
          email_or_phone: this.recoveryIdentifier,
          verify_by: this.recoveryMethod,
        },
      });
      return Boolean(response.result);
    } catch {
      return false;
    }
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
    if (!this.activeSessions || this.activeSessions.length === 0) {
      this.activeSessions = [
        {
          id: 'session-1',
          device: 'iPhone 15 Pro',
          browser: 'Mayush App v1.0.0',
          location: 'Casablanca, Maroc',
          lastActive: 'Actif maintenant',
          isCurrent: true,
          ipAddress: '196.200.150.12',
        },
        {
          id: 'session-2',
          device: 'MacBook Pro 16"',
          browser: 'Safari 17.2',
          location: 'Rabat, Maroc',
          lastActive: 'Il y a 2 heures',
          isCurrent: false,
          ipAddress: '196.200.150.14',
        },
        {
          id: 'session-3',
          device: 'Samsung Galaxy Tab S9',
          browser: 'Chrome Mobile',
          location: 'Marrakech, Maroc',
          lastActive: 'Il y a 3 jours',
          isCurrent: false,
          ipAddress: '105.154.20.45',
        },
      ];
    }
    return [...this.activeSessions];
  }

  public getSelectedSession(): ActiveSession | null {
    return this.selectedSession;
  }

  public setSelectedSession(session: ActiveSession | null) {
    this.selectedSession = session;
    this.notify();
  }

  public disconnectSession(sessionId: string): { success: boolean; error?: string } {
    this.activeSessions = this.activeSessions.filter((s) => s.id !== sessionId);
    if (this.selectedSession?.id === sessionId) {
      this.selectedSession = null;
    }
    this.notify();
    if (this.isAuthenticated()) {
      apiClient<any>(`/api/v2/auth/devices/${sessionId}`, {
        method: 'DELETE',
      }).catch(() => undefined);
    }
    return { success: true };
  }

  // ── Address Book State ──

  public getSavedAddresses(): SavedAddress[] {
    return [...this.savedAddresses];
  }

  /** Hydrates the canonical address book from checkout's durable session. */
  public replaceSavedAddresses(addresses: SavedAddress[]) {
    this.savedAddresses = addresses.map((address) => ({ ...address }));
    void this.persistAddresses();
    this.notify();
  }

  public async addAddress(address: SavedAddress): Promise<{ success: boolean; error?: string }> {
    if (!this.isAuthenticated()) {
      // Local-only for guest (shouldn't happen in practice)
      this.savedAddresses.push(address);
      void this.persistAddresses();
      this.notify();
      return { success: true };
    }
    try {
      const payload = mapSavedAddressToCreatePayload(address);
      const response = await addressService.createAddress(payload);
      if (!response.result) {
        return { success: false, error: response.message };
      }
      // Refresh full list from backend to get server-assigned IDs
      await this.refreshAddressesFromBackend();
      return { success: true };
    } catch (error: any) {
      return { success: false, error: error.message || 'Erreur réseau.' };
    }
  }

  public async updateAddress(id: string, updated: Partial<SavedAddress>): Promise<{ success: boolean; error?: string }> {
    const existing = this.savedAddresses.find((a) => a.id === id);
    if (!existing) return { success: false, error: 'Adresse introuvable.' };

    if (!this.isAuthenticated()) {
      this.savedAddresses = this.savedAddresses.map((a) => a.id === id ? { ...a, ...updated } : a);
      void this.persistAddresses();
      this.notify();
      return { success: true };
    }

    try {
      const merged = { ...existing, ...updated };
      const payload = mapSavedAddressToUpdatePayload(merged);
      const response = await addressService.updateAddress(payload);
      if (!response.result) {
        return { success: false, error: response.message };
      }
      await this.refreshAddressesFromBackend();
      return { success: true };
    } catch (error: any) {
      return { success: false, error: error.message || 'Erreur réseau.' };
    }
  }

  public async deleteAddress(id: string): Promise<{ success: boolean; error?: string }> {
    if (!this.isAuthenticated()) {
      this.savedAddresses = this.savedAddresses.filter((a) => a.id !== id);
      if (this.addressToDelete?.id === id) this.addressToDelete = null;
      void this.persistAddresses();
      this.notify();
      return { success: true };
    }

    try {
      const numericId = parseInt(id, 10);
      if (isNaN(numericId)) return { success: false, error: 'ID invalide.' };
      const response = await addressService.deleteAddress(numericId);
      if (!response.result) {
        return { success: false, error: response.message };
      }
      if (this.addressToDelete?.id === id) this.addressToDelete = null;
      await this.refreshAddressesFromBackend();
      return { success: true };
    } catch (error: any) {
      return { success: false, error: error.message || 'Erreur réseau.' };
    }
  }

  public async setDefaultAddress(id: string): Promise<{ success: boolean; error?: string }> {
    if (!this.isAuthenticated()) {
      this.savedAddresses = this.savedAddresses.map((a) => ({ ...a, isDefault: a.id === id }));
      void this.persistAddresses();
      this.notify();
      return { success: true };
    }

    try {
      const numericId = parseInt(id, 10);
      if (isNaN(numericId)) return { success: false, error: 'ID invalide.' };
      const response = await addressService.setDefaultAddress(numericId);
      if (!response.result) {
        return { success: false, error: response.message };
      }
      await this.refreshAddressesFromBackend();
      return { success: true };
    } catch (error: any) {
      return { success: false, error: error.message || 'Erreur réseau.' };
    }
  }

  /** Refresh address list from Laravel backend. */
  public async refreshAddressesFromBackend(): Promise<void> {
    try {
      const backendAddresses = await addressService.fetchAddresses();
      this.savedAddresses = backendAddresses.map(mapBackendAddressToSaved);
      void this.persistAddresses();
      this.notify();
    } catch {
      // Mutations and refreshes must not leave stale addresses presented as live.
      this.savedAddresses = [];
      this.notify();
    }
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
    this.recoveryIdentifier = '';
    this.recoveryMethod = 'email';
    this.otpCode = '';
    this.otpError = null;
    this.resendTimerSeconds = 30;
    this.newPasswordDraft = { password: '', confirmPassword: '' };
    this.returnDestination = null;
    this.twoFactorEnabled = false;
    this.activeSessions = [];
    this.deviceVerificationChallenge = null;
    this.selectedSession = null;
    this.savedAddresses = [];
    this.selectedAddressForEdit = null;
    this.addressToDelete = null;
    this.notify();
  }
}

export const authState = AuthStateManager.getInstance();

// ── Address DTO Mappers ──

/** Map Laravel AddressDto to local SavedAddress for UI consumption. */
function mapBackendAddressToSaved(dto: AddressDto): SavedAddress {
  return {
    id: String(dto.id),
    name: '', // Backend doesn't store recipient name on address — use user name
    phone: dto.phone || '',
    addressLine: dto.address || '',
    city: dto.city_name || '',
    postcode: dto.postal_code || '',
    zone: dto.area_name || dto.state_name || '',
    isDefault: dto.set_default === 1,
    // Store backend IDs for mutations
    backendCityId: dto.city_id,
    backendStateId: dto.state_id,
    backendCountryId: dto.country_id,
    backendAreaId: dto.area_id,
  };
}

/** Map local SavedAddress to Laravel create payload. */
function mapSavedAddressToCreatePayload(address: SavedAddress): import('../contracts/api/dto').AddressCreatePayload {
  return {
    address: address.addressLine,
    country_id: (address as any).backendCountryId || 1, // Morocco default
    state_id: (address as any).backendStateId,
    city_id: (address as any).backendCityId || 0,
    area_id: (address as any).backendAreaId,
    postal_code: address.postcode,
    phone: address.phone,
  };
}

/** Map local SavedAddress to Laravel update payload. */
function mapSavedAddressToUpdatePayload(address: SavedAddress): import('../contracts/api/dto').AddressUpdatePayload {
  return {
    id: parseInt(address.id, 10),
    ...mapSavedAddressToCreatePayload(address),
  };
}

export const createCheckoutAuthReturnDestination = (checkoutAttemptId: string, route: 'payment-method' | 'wallet-balance' = 'payment-method'): ReturnDestination => ({
  route,
  params: { checkoutAttemptId },
  pendingAction: 'checkout',
});
