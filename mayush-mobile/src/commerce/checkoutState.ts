export interface SavedAddress {
  id: string;
  name: string;
  phone: string;
  addressLine: string;
  city: string;
  postcode: string;
  zone: string;
  isDefault?: boolean;
}

export interface AddressDraft {
  name: string;
  phone: string;
  city: string;
  zone: string;
  addressLine: string;
  apartment: string;
  postcode: string;
  deliveryInstructions: string;
  label: 'Maison' | 'Bureau' | 'Autre';
  isDefault: boolean;
}

export type AddressDraftErrors = Partial<Record<keyof Pick<AddressDraft, 'name' | 'phone' | 'city' | 'zone' | 'addressLine' | 'postcode'>, string>>;

export const emptyAddressDraft = (): AddressDraft => ({
  name: '',
  phone: '+212 ',
  city: '',
  zone: '',
  addressLine: '',
  apartment: '',
  postcode: '',
  deliveryInstructions: '',
  label: 'Maison',
  isDefault: false,
});

export const defaultSavedAddresses: SavedAddress[] = [
  {
    id: 'address-youssef',
    name: 'Youssef El Amrani',
    phone: '+212 6 12 34 56 78',
    addressLine: '123, Avenue Mohammed V, Résidence Al Amal, Appartement 5, Étage 2',
    city: 'Casablanca',
    postcode: '20000',
    zone: 'Casablanca Centre',
    isDefault: true,
  },
  {
    id: 'address-salma',
    name: 'Salma Benali',
    phone: '+212 6 12 65 43 2',
    addressLine: '45, Rue des Écoles, Quartier Maarif, Résidence Yasmina, Appartement 12',
    city: 'Casablanca',
    postcode: '20100',
    zone: 'Maarif',
  },
];

export const isValidAddress = (address: Omit<SavedAddress, 'id'>): boolean => Boolean(
  address.name.trim()
  && /^\+212\s?\d(?:[\s.-]?\d){8}$/.test(address.phone.trim())
  && address.addressLine.trim()
  && address.city.trim()
  && address.postcode.trim()
  && address.zone.trim(),
);

export const validateAddressDraft = (draft: AddressDraft): AddressDraftErrors => {
  const errors: AddressDraftErrors = {};
  if (!draft.name.trim()) errors.name = 'Le nom complet est requis.';
  if (!/^\+212\s?\d(?:[\s.-]?\d){8}$/.test(draft.phone.trim())) errors.phone = 'Saisissez un numéro marocain valide.';
  if (!draft.city.trim()) errors.city = 'Sélectionnez votre ville.';
  if (!draft.zone.trim()) errors.zone = 'Sélectionnez votre zone de livraison.';
  if (!draft.addressLine.trim()) errors.addressLine = 'L’adresse est requise.';
  if (!draft.postcode.trim()) errors.postcode = 'Le code postal est requis.';
  return errors;
};

export const createSavedAddress = (draft: AddressDraft, id: string): SavedAddress => ({
  id,
  name: draft.name.trim(),
  phone: draft.phone.trim(),
  addressLine: [draft.addressLine.trim(), draft.apartment.trim()].filter(Boolean).join(', '),
  city: draft.city.trim(),
  postcode: draft.postcode.trim(),
  zone: draft.zone.trim(),
  isDefault: draft.isDefault,
});

export type DeliveryMethod = 'standard' | 'express' | 'relay';
export type PaymentMethod = 'cmi' | 'cash-on-delivery' | 'wallet';

export const CHECKOUT_SESSION_KEY = 'mayush-mobile:checkout-session';

export type ResumableCheckoutScreen = 'checkout-summary' | 'address-selection' | 'add-address' | 'delivery-method' | 'payment-method' | 'order-review';

export interface CheckoutSession {
  screen: ResumableCheckoutScreen;
  selectedAddressId: string;
  deliveryMethod: DeliveryMethod;
  paymentMethod: PaymentMethod;
  savedAddresses: SavedAddress[];
}

export const isResumableCheckoutScreen = (screen: string): screen is ResumableCheckoutScreen => [
  'checkout-summary',
  'address-selection',
  'add-address',
  'delivery-method',
  'payment-method',
  'order-review',
].includes(screen);

export const parseCheckoutSession = (value: string | null): CheckoutSession | null => {
  if (!value) return null;
  try {
    const parsed = JSON.parse(value) as Partial<CheckoutSession>;
    if (
      !parsed.screen
      || !isResumableCheckoutScreen(parsed.screen)
      || !parsed.selectedAddressId
      || !parsed.deliveryMethod
      || !parsed.paymentMethod
      || !Array.isArray(parsed.savedAddresses)
    ) return null;
    return parsed as CheckoutSession;
  } catch {
    return null;
  }
};
