/**
 * Mirrors the current Laravel `system_default_currency` configuration.
 * Product API display prices stay authoritative when the backend is connected.
 */
export const STORE_CURRENCY_CODE = 'MAD' as const;

export const formatStorePrice = (amount: string | number): string => `${amount} ${STORE_CURRENCY_CODE}`;
