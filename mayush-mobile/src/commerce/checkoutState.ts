import { CartState, CartTotals, getCartTotals } from './cartState';

export type MoroccoCityId = 'casablanca' | 'rabat' | 'marrakech' | 'tanger' | 'agadir' | 'fes' | 'mohammedia' | 'temara';

export interface MoroccoCity { cityId: MoroccoCityId; nameFr: string; nameAr: string; recent: boolean }
export interface DeliveryZone { zoneId: string; cityId: MoroccoCityId; nameFr: string; nameAr: string; baseFeeMad: number; serviced: boolean }

export const MOROCCO_CITIES: readonly MoroccoCity[] = [
  { cityId: 'casablanca', nameFr: 'Casablanca', nameAr: 'الدار البيضاء', recent: true },
  { cityId: 'rabat', nameFr: 'Rabat', nameAr: 'الرباط', recent: true },
  { cityId: 'marrakech', nameFr: 'Marrakech', nameAr: 'مراكش', recent: true },
  { cityId: 'tanger', nameFr: 'Tanger', nameAr: 'طنجة', recent: true },
  { cityId: 'agadir', nameFr: 'Agadir', nameAr: 'أكادير', recent: false },
  { cityId: 'fes', nameFr: 'Fès', nameAr: 'فاس', recent: false },
  { cityId: 'mohammedia', nameFr: 'Mohammedia', nameAr: 'المحمدية', recent: false },
  { cityId: 'temara', nameFr: 'Témara', nameAr: 'تمارة', recent: false },
] as const;

export const DELIVERY_ZONES: readonly DeliveryZone[] = [
  { zoneId: 'casablanca-centre', cityId: 'casablanca', nameFr: 'Casablanca Centre', nameAr: 'وسط الدار البيضاء', baseFeeMad: 20, serviced: true },
  { zoneId: 'racine', cityId: 'casablanca', nameFr: 'Racine', nameAr: 'راسين', baseFeeMad: 20, serviced: true },
  { zoneId: 'maarif', cityId: 'casablanca', nameFr: 'Maârif', nameAr: 'المعاريف', baseFeeMad: 20, serviced: true },
  { zoneId: 'ain-diab', cityId: 'casablanca', nameFr: 'Ain Diab', nameAr: 'عين الذئاب', baseFeeMad: 30, serviced: true },
  { zoneId: 'bourgogne', cityId: 'casablanca', nameFr: 'Bourgogne', nameAr: 'بوركون', baseFeeMad: 20, serviced: true },
  { zoneId: 'sidi-maarouf', cityId: 'casablanca', nameFr: 'Sidi Maârouf', nameAr: 'سيدي معروف', baseFeeMad: 30, serviced: true },
  { zoneId: 'californie', cityId: 'casablanca', nameFr: 'Californie', nameAr: 'كاليفورنيا', baseFeeMad: 20, serviced: true },
  { zoneId: 'agdal-rabat', cityId: 'rabat', nameFr: 'Agdal', nameAr: 'أكدال', baseFeeMad: 25, serviced: true },
  { zoneId: 'hay-riad', cityId: 'rabat', nameFr: 'Hay Riad', nameAr: 'حي الرياض', baseFeeMad: 25, serviced: true },
  { zoneId: 'gueliz', cityId: 'marrakech', nameFr: 'Guéliz', nameAr: 'كليز', baseFeeMad: 30, serviced: false },
  { zoneId: 'medina-marrakech', cityId: 'marrakech', nameFr: 'Médina', nameAr: 'المدينة القديمة', baseFeeMad: 30, serviced: true },
  { zoneId: 'malabata', cityId: 'tanger', nameFr: 'Malabata', nameAr: 'مالاباطا', baseFeeMad: 30, serviced: true },
] as const;

export const getCityById = (cityId?: string): MoroccoCity | undefined => MOROCCO_CITIES.find((city) => city.cityId === cityId);
export const getZonesForCity = (cityId?: string): DeliveryZone[] => DELIVERY_ZONES.filter((zone) => zone.cityId === cityId).map((zone) => ({ ...zone }));
export const isZoneInCity = (cityId?: string, zoneId?: string): boolean => Boolean(cityId && zoneId && DELIVERY_ZONES.some((zone) => zone.cityId === cityId && zone.zoneId === zoneId));
export const filterCities = (query: string): MoroccoCity[] => {
  const normalized = query.trim().toLocaleLowerCase('fr-MA');
  return MOROCCO_CITIES.filter((city) => !normalized || city.nameFr.toLocaleLowerCase('fr-MA').includes(normalized) || city.nameAr.includes(normalized)).map((city) => ({ ...city }));
};

export interface SavedAddress {
  id: string;
  name: string;
  phone: string;
  addressLine: string;
  city: string;
  postcode: string;
  zone: string;
  cityId?: MoroccoCityId;
  zoneId?: string;
  apartment?: string;
  deliveryInstructions?: string;
  label?: AddressDraft['label'];
  isDefault?: boolean;
}

export interface AddressDraft {
  name: string;
  phone: string;
  city: string;
  zone: string;
  cityId?: MoroccoCityId | '';
  zoneId?: string;
  addressLine: string;
  apartment: string;
  postcode: string;
  deliveryInstructions: string;
  label: 'Maison' | 'Bureau' | 'Famille' | 'Autre';
  isDefault: boolean;
}

export type AddressDraftErrors = Partial<Record<keyof Pick<AddressDraft, 'name' | 'phone' | 'city' | 'zone' | 'addressLine' | 'postcode'>, string>>;

export const emptyAddressDraft = (): AddressDraft => ({ name: '', phone: '+212 ', city: '', zone: '', cityId: '', zoneId: '', addressLine: '', apartment: '', postcode: '', deliveryInstructions: '', label: 'Maison', isDefault: false });

export const defaultSavedAddresses: SavedAddress[] = [
  { id: 'address-youssef', name: 'Youssef El Amrani', phone: '+212 6 12 34 56 78', addressLine: '123, Avenue Mohammed V, Résidence Al Amal, Appartement 5, Étage 2', city: 'Casablanca', postcode: '20000', zone: 'Casablanca Centre', cityId: 'casablanca', zoneId: 'casablanca-centre', label: 'Maison', isDefault: true },
  { id: 'address-salma', name: 'Salma Benali', phone: '+212 6 12 65 43 21', addressLine: '45, Rue des Écoles, Quartier Maârif, Résidence Yasmina, Appartement 12', city: 'Casablanca', postcode: '20100', zone: 'Maârif', cityId: 'casablanca', zoneId: 'maarif', label: 'Bureau' },
];

const resolveDraftLocation = (draft: Pick<AddressDraft, 'cityId' | 'zoneId' | 'city' | 'zone'>) => {
  const city = getCityById(draft.cityId) || MOROCCO_CITIES.find((candidate) => candidate.nameFr.toLocaleLowerCase('fr-MA') === draft.city.trim().toLocaleLowerCase('fr-MA'));
  const normalizedZone = draft.zone.trim().toLocaleLowerCase('fr-MA');
  const legacyZoneId = city?.cityId === 'casablanca' && normalizedZone === 'centre' ? 'casablanca-centre' : undefined;
  const zone = DELIVERY_ZONES.find((candidate) => candidate.cityId === city?.cityId && candidate.zoneId === draft.zoneId)
    || DELIVERY_ZONES.find((candidate) => candidate.cityId === city?.cityId && candidate.zoneId === legacyZoneId)
    || DELIVERY_ZONES.find((candidate) => candidate.cityId === city?.cityId && candidate.nameFr.toLocaleLowerCase('fr-MA') === normalizedZone);
  return { city, zone };
};

export const isValidAddress = (address: Omit<SavedAddress, 'id'>): boolean => Boolean(address.name.trim() && /^\+212\s?\d(?:[\s.-]?\d){8}$/.test(address.phone.trim()) && address.addressLine.trim() && address.city.trim() && address.postcode.trim() && address.zone.trim() && address.cityId && address.zoneId && isZoneInCity(address.cityId, address.zoneId));

export const validateAddressDraft = (draft: AddressDraft): AddressDraftErrors => {
  const errors: AddressDraftErrors = {};
  const location = resolveDraftLocation(draft);
  if (!draft.name.trim()) errors.name = 'Le nom complet est requis.';
  if (!/^\+212\s?\d(?:[\s.-]?\d){8}$/.test(draft.phone.trim())) errors.phone = 'Saisissez un numéro marocain valide.';
  if (!draft.city.trim() || !location.city) errors.city = 'Sélectionnez votre ville.';
  if (!draft.zone.trim() || !location.zone) errors.zone = 'Sélectionnez une zone compatible avec la ville.';
  if (!draft.addressLine.trim()) errors.addressLine = 'L’adresse est requise.';
  if (!draft.postcode.trim()) errors.postcode = 'Le code postal est requis.';
  return errors;
};

export const createSavedAddress = (draft: AddressDraft, id: string): SavedAddress => {
  const location = resolveDraftLocation(draft);
  return { id, name: draft.name.trim(), phone: draft.phone.trim(), addressLine: [draft.addressLine.trim(), draft.apartment.trim()].filter(Boolean).join(', '), city: location.city?.nameFr || draft.city.trim(), postcode: draft.postcode.trim(), zone: location.zone?.nameFr || draft.zone.trim(), cityId: location.city?.cityId, zoneId: location.zone?.zoneId, apartment: draft.apartment.trim() || undefined, deliveryInstructions: draft.deliveryInstructions.trim() || undefined, label: draft.label, isDefault: draft.isDefault };
};

export const setAddressDraftCity = (draft: AddressDraft, cityId: MoroccoCityId): AddressDraft => {
  const city = getCityById(cityId);
  if (!city) return draft;
  const keepZone = isZoneInCity(cityId, draft.zoneId);
  return { ...draft, cityId, city: city.nameFr, zoneId: keepZone ? draft.zoneId : '', zone: keepZone ? draft.zone : '' };
};
export const setAddressDraftZone = (draft: AddressDraft, zoneId: string): AddressDraft => {
  const zone = DELIVERY_ZONES.find((candidate) => candidate.zoneId === zoneId && candidate.cityId === draft.cityId);
  return zone ? { ...draft, zoneId: zone.zoneId, zone: zone.nameFr } : draft;
};
export const addressToDraft = (address: SavedAddress): AddressDraft => ({ name: address.name, phone: address.phone, city: address.city, zone: address.zone, cityId: address.cityId || '', zoneId: address.zoneId || '', addressLine: address.addressLine, apartment: address.apartment || '', postcode: address.postcode, deliveryInstructions: address.deliveryInstructions || '', label: address.label || 'Maison', isDefault: Boolean(address.isDefault) });
export const isNoSavedAddressState = (addresses: readonly SavedAddress[]): boolean => addresses.length === 0;

export type DeliveryMethod = 'standard' | 'express' | 'relay';
export type PaymentMethod = 'cmi' | 'cash-on-delivery' | 'wallet';
export interface CheckoutPaymentReference { paymentPreferenceId: string; type: 'card' | 'wallet'; brand?: string; last4?: string; expiry?: string }
export const canPayWithWallet = (availableBalanceMad: number, checkoutTotalMad: number): boolean => Math.max(0, Math.round(availableBalanceMad)) >= Math.max(0, Math.round(checkoutTotalMad));

export interface CheckoutSellerInput {
  sellerId?: string;
  sellerName?: string;
  quantity: number;
}

export interface SellerDeliveryGroup {
  sellerId: string;
  sellerName: string;
  itemCount: number;
  packageCount: 1;
  deliveryFeeMad: number;
  etaFr: string;
  etaAr: string;
  available: boolean;
}

export interface CheckoutDeliveryProjection {
  available: boolean;
  groups: SellerDeliveryGroup[];
  packageCount: number;
  deliveryFeeMad: number;
  unavailableReason?: 'ADDRESS_UNSUPPORTED';
}

export const getDeliveryMethodFeeMad = (method: DeliveryMethod, zone?: DeliveryZone): number => {
  const base = zone?.baseFeeMad ?? 20;
  if (method === 'express') return base + 20;
  if (method === 'relay') return Math.max(0, base - 5);
  return base;
};

export const getSellerPackageDeliveryFeeMad = (method: DeliveryMethod): number =>
  method === 'express' ? 49 : method === 'relay' ? 24 : 29;

/** Pure projection: it never mutates cart lines or seller ownership. */
export const buildSellerDeliveryProjection = (
  lines: readonly CheckoutSellerInput[],
  address: SavedAddress | undefined,
  method: DeliveryMethod = 'standard',
): CheckoutDeliveryProjection => {
  const zone = DELIVERY_ZONES.find((candidate) => candidate.zoneId === address?.zoneId);
  const grouped = new Map<string, SellerDeliveryGroup>();
  lines.forEach((line) => {
    const sellerId = line.sellerId || 'seller-mayush';
    const current = grouped.get(sellerId) || {
      sellerId,
      sellerName: line.sellerName || (sellerId === 'seller-mayush' ? 'Mayush Design' : sellerId),
      itemCount: 0,
      packageCount: 1 as const,
      deliveryFeeMad: getSellerPackageDeliveryFeeMad(method),
      etaFr: method === 'express' ? '24 \u00e0 48h ouvr\u00e9es' : '2 \u00e0 4 jours ouvr\u00e9s',
      etaAr: method === 'express' ? '24 \u0625\u0644\u0649 48 \u0633\u0627\u0639\u0629' : '2 \u0625\u0644\u0649 4 \u0623\u064a\u0627\u0645 \u0639\u0645\u0644',
      available: true,
    };
    current.itemCount += Math.max(0, Math.floor(line.quantity));
    grouped.set(sellerId, current);
  });
  const groups = [...grouped.values()];
  if (!address || !zone || zone.cityId !== address.cityId || !zone.serviced) {
    return { available: false, groups: groups.map((group) => ({ ...group, deliveryFeeMad: 0, available: false })), packageCount: groups.length, deliveryFeeMad: 0, unavailableReason: 'ADDRESS_UNSUPPORTED' };
  }
  return {
    available: true,
    groups,
    packageCount: groups.length,
    deliveryFeeMad: groups.reduce((sum, group) => sum + group.deliveryFeeMad, 0),
  };
};

export const getCheckoutGrandTotalMad = (cartTotalMad: number, deliveryFeeMad: number): number =>
  Math.max(0, Math.round(cartTotalMad)) + Math.max(0, Math.round(deliveryFeeMad));

export const CHECKOUT_SESSION_KEY = 'mayush-mobile:checkout-session';
export interface CheckoutStorage { getItem(key: string): Promise<string | null>; setItem(key: string, value: string): Promise<void>; removeItem(key: string): Promise<void> }
export const createLocalCheckoutAttemptId = (now: number = Date.now(), entropy: string = Math.random().toString(36).slice(2, 10)): string => `checkout-${now}-${entropy}`;
export type ResumableCheckoutScreen = 'checkout-summary' | 'checkout-skeleton' | 'checkout-error' | 'address-selection' | 'add-address' | 'city-selector' | 'delivery-zone-selector' | 'edit-checkout-address' | 'no-saved-address' | 'delivery-method' | 'delivery-by-vendor' | 'delivery-unavailable' | 'payment-method' | 'wallet-balance' | 'saved-payment-cards' | 'order-review' | 'checkout-terms-confirmation' | 'order-already-in-progress' | 'order-needs-update' | 'payment-step-intro' | 'payment-failed' | 'payment-cancelled' | 'payment-confirmation-delayed' | 'payment-pending';
export interface CheckoutTermsAcceptance { checkoutAttemptId: string; materialSignature: string; acceptedAt: string }
export interface CheckoutSession { checkoutAttemptId: string; screen: ResumableCheckoutScreen; selectedAddressId: string; deliveryMethod: DeliveryMethod; paymentMethod: PaymentMethod; savedAddresses: SavedAddress[]; selectedPaymentPreferenceId?: string; termsAcceptance?: CheckoutTermsAcceptance }
export const isResumableCheckoutScreen = (screen: string): screen is ResumableCheckoutScreen => ['checkout-summary','checkout-skeleton','checkout-error','address-selection','add-address','city-selector','delivery-zone-selector','edit-checkout-address','no-saved-address','delivery-method','delivery-by-vendor','delivery-unavailable','payment-method','wallet-balance','saved-payment-cards','order-review','checkout-terms-confirmation','order-already-in-progress','order-needs-update','payment-step-intro','payment-failed','payment-cancelled','payment-confirmation-delayed','payment-pending'].includes(screen);
export const getDurableCheckoutScreen = (screen: ResumableCheckoutScreen): ResumableCheckoutScreen => {
  if (['checkout-skeleton','checkout-error'].includes(screen)) return 'checkout-summary';
  if (['city-selector','delivery-zone-selector','edit-checkout-address','no-saved-address'].includes(screen)) return 'address-selection';
  if (['delivery-by-vendor','delivery-unavailable'].includes(screen)) return 'delivery-method';
  if (['wallet-balance','saved-payment-cards'].includes(screen)) return 'payment-method';
  if (screen === 'checkout-terms-confirmation' || screen === 'order-needs-update') return 'order-review';
  if (screen === 'payment-failed' || screen === 'payment-cancelled') return 'payment-method';
  if (screen === 'order-already-in-progress' || screen === 'payment-confirmation-delayed') return 'payment-pending';
  return screen;
};

const inferAddressIds = (address: SavedAddress): SavedAddress | null => {
  const city = getCityById(address.cityId) || MOROCCO_CITIES.find((candidate) => candidate.nameFr.toLocaleLowerCase('fr-MA') === address.city.toLocaleLowerCase('fr-MA'));
  const legacyZoneId = city?.cityId === 'casablanca' && address.zone.toLocaleLowerCase('fr-MA') === 'centre' ? 'casablanca-centre' : undefined;
  const zone = DELIVERY_ZONES.find((candidate) => candidate.zoneId === address.zoneId)
    || DELIVERY_ZONES.find((candidate) => candidate.zoneId === legacyZoneId)
    || DELIVERY_ZONES.find((candidate) => candidate.cityId === city?.cityId && candidate.nameFr.toLocaleLowerCase('fr-MA') === address.zone.toLocaleLowerCase('fr-MA'));
  return city && zone && zone.cityId === city.cityId ? { ...address, cityId: city.cityId, zoneId: zone.zoneId, city: city.nameFr, zone: zone.nameFr } : null;
};

export const parseCheckoutSession = (value: string | null): CheckoutSession | null => {
  if (!value) return null;
  try {
    const parsed = JSON.parse(value) as Partial<CheckoutSession>;
    if (!parsed.screen || !parsed.checkoutAttemptId || !isResumableCheckoutScreen(parsed.screen) || !parsed.deliveryMethod || !parsed.paymentMethod || !Array.isArray(parsed.savedAddresses)) return null;
    const savedAddresses = parsed.savedAddresses.map(inferAddressIds).filter((address): address is SavedAddress => Boolean(address));
    const selectedAddressId = savedAddresses.some((address) => address.id === parsed.selectedAddressId) ? parsed.selectedAddressId || '' : savedAddresses.find((address) => address.isDefault)?.id || savedAddresses[0]?.id || '';
    const termsAcceptance = parsed.termsAcceptance
      && parsed.termsAcceptance.checkoutAttemptId === parsed.checkoutAttemptId
      && typeof parsed.termsAcceptance.materialSignature === 'string'
      && typeof parsed.termsAcceptance.acceptedAt === 'string'
      ? { ...parsed.termsAcceptance }
      : undefined;
    return { checkoutAttemptId: parsed.checkoutAttemptId, screen: parsed.screen, selectedAddressId, deliveryMethod: parsed.deliveryMethod, paymentMethod: parsed.paymentMethod, savedAddresses, selectedPaymentPreferenceId: typeof parsed.selectedPaymentPreferenceId === 'string' ? parsed.selectedPaymentPreferenceId : undefined, termsAcceptance };
  } catch { return null; }
};
export const saveCheckoutSession = async (storage: CheckoutStorage, session: CheckoutSession): Promise<void> => storage.setItem(CHECKOUT_SESSION_KEY, JSON.stringify({ ...session, screen: getDurableCheckoutScreen(session.screen) }));
export const loadCheckoutSession = async (storage: CheckoutStorage): Promise<CheckoutSession | null> => parseCheckoutSession(await storage.getItem(CHECKOUT_SESSION_KEY));
export const clearCheckoutSession = async (storage: CheckoutStorage): Promise<void> => { await storage.removeItem(CHECKOUT_SESSION_KEY); };

export interface CheckoutMaterialFacts {
  cart: CartState;
  selectedAddressId: string;
  deliveryMethod: DeliveryMethod;
  paymentMethod: PaymentMethod;
  deliveryFeeMad: number;
}

export const createCheckoutMaterialSignature = (facts: CheckoutMaterialFacts): string => JSON.stringify({
  lines: facts.cart.lines.map((line) => ({
    id: line.id,
    productId: String(line.productId),
    variantId: line.variantId || line.variant,
    quantity: line.quantity,
    unitPriceMad: Math.max(0, Math.round(line.unitPriceMad)),
    sellerId: line.sellerId || 'seller-mayush',
  })),
  appliedPromotionId: facts.cart.appliedPromotionId || null,
  selectedAddressId: facts.selectedAddressId,
  deliveryMethod: facts.deliveryMethod,
  paymentMethod: facts.paymentMethod,
  deliveryFeeMad: Math.max(0, Math.round(facts.deliveryFeeMad)),
});

export const acceptCheckoutTerms = (
  checkoutAttemptId: string,
  materialSignature: string,
  acceptedAt: string = new Date().toISOString(),
): CheckoutTermsAcceptance => ({ checkoutAttemptId, materialSignature, acceptedAt });

export const isCheckoutTermsAcceptanceValid = (
  acceptance: CheckoutTermsAcceptance | undefined,
  checkoutAttemptId: string,
  materialSignature: string,
): boolean => Boolean(acceptance
  && acceptance.checkoutAttemptId === checkoutAttemptId
  && acceptance.materialSignature === materialSignature);

export type CheckoutViewStatus = 'idle' | 'loading' | 'ready' | 'error';
export type CheckoutRecoveryDestination = 'checkout-summary' | 'cart' | 'address-selection' | 'delivery-unavailable';

export interface CheckoutRecoverySnapshot {
  cart: CartState;
  selectedAddress?: SavedAddress;
  deliveryMethod: DeliveryMethod;
}

export interface CheckoutRecoveryResult {
  destination: CheckoutRecoveryDestination;
  cartTotals: CartTotals;
  deliveryProjection?: CheckoutDeliveryProjection;
}

/** Pure, non-destructive retry projection over the existing durable domains. */
export const resolveCheckoutRecovery = (snapshot: CheckoutRecoverySnapshot): CheckoutRecoveryResult => {
  const cartTotals = getCartTotals(snapshot.cart);
  if (!snapshot.cart.lines.length) return { destination: 'cart', cartTotals };
  if (!snapshot.selectedAddress) return { destination: 'address-selection', cartTotals };
  const deliveryProjection = buildSellerDeliveryProjection(snapshot.cart.lines, snapshot.selectedAddress, snapshot.deliveryMethod);
  return {
    destination: deliveryProjection.available ? 'checkout-summary' : 'delivery-unavailable',
    cartTotals,
    deliveryProjection,
  };
};

export type PaymentVerificationPresentation = 'not_applicable' | 'taking_longer' | 'pending' | 'confirmed' | 'failed' | 'cancelled';
export interface PaymentStatusLike { paymentMethod: PaymentMethod; paymentStatus: 'prototype_pending_confirmation' | 'cash_on_delivery_pending' | 'confirmed' | 'failed' | 'cancelled' }
export type FrontendPaymentVerificationScenario = 'confirmed_fixture' | 'failed_fixture' | 'pending_fixture';
export type FrontendPaymentVerificationOutcome = 'confirmed' | 'failed' | 'cancelled' | 'pending' | 'not_applicable';

export const getPaymentVerificationPresentation = (
  order: PaymentStatusLike,
  prolonged = false,
): PaymentVerificationPresentation => {
  if (order.paymentMethod !== 'cmi') return 'not_applicable';
  if (order.paymentStatus === 'confirmed') return 'confirmed';
  if (order.paymentStatus === 'failed') return 'failed';
  if (order.paymentStatus === 'cancelled') return 'cancelled';
  return prolonged ? 'taking_longer' : 'pending';
};

/**
 * Deterministic frontend-only verification used by the prototype. It does not
 * contact CMI or claim settlement; the explicit saved-card fixture chooses the
 * outcome so failure is reproducible and never random.
 */
export const resolveFrontendPaymentVerificationOutcome = (
  order: PaymentStatusLike & { paymentVerificationScenario?: FrontendPaymentVerificationScenario },
): FrontendPaymentVerificationOutcome => {
  if (order.paymentMethod !== 'cmi') return 'not_applicable';
  if (order.paymentStatus === 'confirmed' || order.paymentStatus === 'failed' || order.paymentStatus === 'cancelled') return order.paymentStatus;
  if (order.paymentVerificationScenario === 'confirmed_fixture') return 'confirmed';
  if (order.paymentVerificationScenario === 'failed_fixture') return 'failed';
  return 'pending';
};
