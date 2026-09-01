/**
 * ScreenKey Definitions & Registry
 */

export type ScreenKey =
  | 'splash'
  | 'language'
  | 'preparing'
  | 'onboarding-1'
  | 'onboarding-2'
  | 'onboarding-3'
  | 'home'
  | 'categories'
  | 'wishlist'
  | 'cart'
  | 'account'
  | 'account-settings'
  | 'settings'
  | 'my-information'
  | 'edit-profile'
  | 'change-email'
  | 'change-phone'
  | 'account-verify-phone'
  | 'change-password'
  | 'account-security'
  | 'security-privacy'
  | 'security-2fa'
  | 'biometric-app-lock'
  | 'active-sessions'
  | 'my-addresses'
  | 'my-addresses-v2'
  | 'account-add-address'
  | 'account-add-address-simple'
  | 'account-edit-address'
  | 'payment-methods'
  | 'language-region'
  | 'language-selection'
  | 'logout-confirmation'
  | 'marketing-cart-reminders'
  | 'marketing-detailed-preferences'
  | 'marketing-toggles'
  | 'notification-channels'
  | 'notification-settings-toggles'
  | 'notification-detail-prep'
  | 'notification-detail-shipped'
  | 'silent-hours-day-selection'
  | 'silent-hours-dnd'
  | 'theme-appearance'
  | 'about-app'
  | 'about-mayush'
  | 'accessibility'
  | 'app-permissions'
  | 'data-usage'
  | 'storage-cache'
  | 'offline-mode'
  | 'legal-center'
  | 'privacy-data'
  | 'privacy-policy'
  | 'faq'
  | 'faq-detail'
  | 'faq-categories'
  | 'help-center'
  | 'help-center-requests'
  | 'help-support'
  | 'help-center-home'
  | 'help-category-orders-delivery'
  | 'help-center-search-results'
  | 'faq-tab-categories'
  | 'faq-article-track-order-steps'
  | 'my-support-tickets-list'
  | 'no-support-requests-empty-state'
  | 'contact-support-form'
  | 'order-support-contact'
  | 'attach-files-documents'
  | 'review-send-support-request'
  | 'select-order-for-support'
  | 'reply-to-support-message'
  | 'ticket-detail-conversation-thread'
  | 'close-request-confirmation'
  | 'support-request-sent-success'
  | 'ticket-resolved-rating'
  | 'support-connection-error'
  | 'support-temporarily-unavailable'
  | 'maintenance-mode-services-impacted'
  | 'app-update-available'
  | 'forced-update-required'
  | 'settings-error-loading-state'
  | 'settings-skeleton-loading-state'
  | 'category-landing'
  | 'category-products'
  | 'collection-shop-the-look'
  | 'collections-list'
  | 'filter-panel-modal'
  | 'flash-deals'
  | 'promotions-campaigns'
  | 'recently-viewed'
  | 'search-landing'
  | 'search-results'
  | 'search-no-results'
  | 'product-details'
  | 'product-gallery'
  | 'inspiration-detail'
  | 'product-description'
  | 'product-specifications'
  | 'product-delivery-returns'
  | 'product-reviews'
  | 'variant-selector'
  | 'added-to-cart'
  | 'checkout-summary'
  | 'address-selection'
  | 'add-address'
  | 'add-address-errors'
  | 'city-selector'
  | 'delivery-zone-selector'
  | 'edit-checkout-address'
  | 'no-saved-address'
  | 'delivery-method'
  | 'delivery-by-vendor'
  | 'delivery-unavailable'
  | 'payment-method'
  | 'wallet-balance'
  | 'saved-payment-cards'
  | 'auth-gate'
  | 'auth-welcome'
  | 'login'
  | 'login-error'
  | 'login-loading'
  | 'registration'
  | 'terms-consent'
  | 'account-created'
  | 'favorites-auth-prompt'
  | 'forgot-password'
  | 'recovery-email-sent'
  | 'otp-verification'
  | 'otp-error'
  | 'create-new-password'
  | 'password-changed-success'
  | 'order-review'
  | 'checkout-terms-confirmation'
  | 'order-already-in-progress'
  | 'order-needs-update'
  | 'checkout-skeleton'
  | 'checkout-error'
  | 'order-processing'
  | 'payment-step-intro'
  | 'secure-payment-redirect'
  | 'secure-payment-loading'
  | 'payment-verification'
  | 'payment-confirmation-delayed'
  | 'payment-pending'
  | 'cash-on-delivery-confirmation'
  | 'payment-failed'
  | 'payment-cancelled'
  | 'payment-success'
  | 'order-thank-you'
  | 'orders-list'
  | 'order-detail-preparing'
  | 'order-detail-shipped'
  | 'order-detail-delivered'
  | 'order-detail-multi-vendor'
  | 'order-tracking'
  | 'order-packages'
  | 'order-package-detail'
  | 'order-invoice'
  | 'order-cancel-confirmation'
  | 'order-cancel-reason'
  | 'order-cancel-registered'
  | 'order-cannot-cancel'
  | 'order-product-review'
  | 'order-reorder-changes'
  | 'order-reorder-availability'
  | 'order-reorder-added'
  | 'order-return-selection'
  | 'order-return-detail'
  | 'order-return-tracking'
  | 'order-refund-request'
  | 'order-refund-completed'
  | 'delivery-delayed'
  | 'delivery-failed'
  | 'tracking-unavailable'
  | 'order-not-found'
  | 'orders-empty'
  | 'orders-error'
  | 'orders-skeleton'
  | 'order-detail-skeleton';

/** Runtime set of all valid screen keys for destination validation. */
export const ALL_SCREEN_KEYS: ReadonlySet<string> = new Set<ScreenKey>([
  'splash', 'language', 'preparing', 'onboarding-1', 'onboarding-2', 'onboarding-3',
  'home', 'categories', 'wishlist', 'cart', 'account',
  'account-settings', 'settings', 'my-information', 'edit-profile', 'change-email',
  'change-phone', 'account-verify-phone', 'change-password', 'account-security',
  'security-privacy', 'security-2fa', 'biometric-app-lock', 'active-sessions', 'my-addresses', 'my-addresses-v2',
  'account-add-address', 'account-add-address-simple', 'account-edit-address',
  'payment-methods', 'language-region', 'language-selection', 'logout-confirmation',
  'marketing-cart-reminders', 'marketing-detailed-preferences', 'marketing-toggles',
  'notification-channels', 'notification-settings-toggles', 'notification-detail-prep',
  'notification-detail-shipped', 'silent-hours-day-selection', 'silent-hours-dnd',
  'about-app', 'about-mayush', 'accessibility', 'app-permissions', 'data-usage',
  'storage-cache', 'offline-mode', 'legal-center', 'privacy-data', 'privacy-policy',
  'faq', 'faq-detail', 'faq-categories', 'help-center', 'help-center-requests',
  'help-support', 'help-center-home', 'help-category-orders-delivery',
  'help-center-search-results', 'faq-tab-categories', 'faq-article-track-order-steps',
  'my-support-tickets-list', 'no-support-requests-empty-state', 'contact-support-form',
  'order-support-contact', 'attach-files-documents', 'review-send-support-request',
  'select-order-for-support', 'reply-to-support-message', 'ticket-detail-conversation-thread',
  'close-request-confirmation', 'support-request-sent-success', 'ticket-resolved-rating',
  'support-connection-error', 'support-temporarily-unavailable',
  'maintenance-mode-services-impacted', 'app-update-available', 'forced-update-required',
  'settings-error-loading-state', 'settings-skeleton-loading-state',
  'category-landing', 'category-products', 'collection-shop-the-look', 'filter-panel-modal',
  'flash-deals', 'promotions-campaigns', 'recently-viewed', 'search-landing',
  'search-results', 'search-no-results', 'product-details', 'product-gallery', 'inspiration-detail',
  'product-description', 'product-specifications', 'product-delivery-returns',
  'product-reviews', 'variant-selector', 'added-to-cart',
  'checkout-summary', 'address-selection', 'add-address', 'add-address-errors',
  'city-selector', 'delivery-zone-selector', 'edit-checkout-address', 'no-saved-address',
  'delivery-method', 'delivery-by-vendor', 'delivery-unavailable',
  'payment-method', 'wallet-balance', 'saved-payment-cards',
  'auth-gate', 'auth-welcome', 'login', 'login-error', 'login-loading',
  'registration', 'terms-consent', 'account-created', 'favorites-auth-prompt',
  'forgot-password', 'recovery-email-sent', 'otp-verification', 'otp-error',
  'create-new-password', 'password-changed-success',
  'order-review', 'checkout-terms-confirmation', 'order-already-in-progress',
  'order-needs-update', 'checkout-skeleton', 'checkout-error',
  'order-processing', 'payment-step-intro', 'secure-payment-redirect',
  'secure-payment-loading', 'payment-verification', 'payment-confirmation-delayed',
  'payment-pending', 'cash-on-delivery-confirmation', 'payment-failed',
  'payment-cancelled', 'payment-success', 'order-thank-you',
  'orders-list', 'order-detail-preparing', 'order-detail-shipped',
  'order-detail-delivered', 'order-detail-multi-vendor', 'order-tracking',
  'order-packages', 'order-package-detail', 'order-invoice',
  'order-cancel-confirmation', 'order-cancel-reason', 'order-cancel-registered',
  'order-cannot-cancel', 'order-product-review', 'order-reorder-changes',
  'order-reorder-availability', 'order-reorder-added', 'order-return-selection',
  'order-return-detail', 'order-return-tracking', 'order-refund-request',
  'order-refund-completed', 'delivery-delayed', 'delivery-failed',
  'tracking-unavailable', 'order-not-found', 'orders-empty', 'orders-error',
  'orders-skeleton', 'order-detail-skeleton',
]);

export const AUTH_PROTECTED_SCREENS: ScreenKey[] = [
  'account-settings',
  'my-information',
  'edit-profile',
  'change-email',
  'change-phone',
  'account-security',
  'security-privacy',
  'security-2fa',
  'active-sessions',
  'my-addresses',
  'my-addresses-v2',
  'account-add-address',
  'payment-methods',
  'my-support-tickets-list',
];
