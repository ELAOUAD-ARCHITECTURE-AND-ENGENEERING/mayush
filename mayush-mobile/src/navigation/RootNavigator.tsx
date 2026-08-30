import AsyncStorage from '@react-native-async-storage/async-storage';
import React, { useEffect, useRef, useState } from 'react';
import { Platform, StatusBar, StyleSheet, View } from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { acceptCheckoutTerms, AddressDraft, addressToDraft, buildSellerDeliveryProjection, CheckoutTermsAcceptance, clearCheckoutSession, createCheckoutMaterialSignature, createLocalCheckoutAttemptId, createSavedAddress, DeliveryMethod, emptyAddressDraft, getCheckoutGrandTotalMad, getCityById, isCheckoutTermsAcceptanceValid, isResumableCheckoutScreen, loadCheckoutSession, PaymentMethod, resolveCheckoutRecovery, resolveFrontendPaymentVerificationOutcome, ResumableCheckoutScreen, saveCheckoutSession, setAddressDraftCity, setAddressDraftZone, validateAddressDraft } from '../commerce/checkoutState';
import {
  CartLine,
  CartState,
  CartVariantSelection,
  addCartLine,
  applyCartConflictChanges,
  applyPromotionCode,
  cartStateManager,
  createSelectedVariantCartLine,
  getCartTotals,
  parseMadPrice,
  removeCartPromotion,
  revalidateCartPromotion,
  updateCartLineQuantity,
  updateCartLineVariant,
} from '../commerce/cartState';
import { getCanonicalOrderDetailRoute, orderState } from '../commerce/orderState';
import { canCancelOrder, orderActionState, ReorderCartResult } from '../commerce/orderActionState';
import { hasOrderTrackingMetadata, orderViewState } from '../commerce/orderViewState';
import { supportState } from '../commerce/supportState';
import { systemRuntimeState } from '../commerce/systemRuntimeState';
import { cartService } from '../services/api/cartService';
import { checkoutService } from '../services/api/checkoutService';
import { CategoryDto, MvpAppLanguage, ProductDetailDto, ProductMiniDto } from '../contracts/api/dto';
import { BottomTabBar, TabKey } from '../design-system/components/navigation/BottomTabBar';
import { ThemeProvider } from '../design-system/theme/ThemeProvider';
import { SystemStatusGate } from '../components/system/SystemStatusGate';
import { AccountScreen } from '../screens/commerce/AccountScreen';
import { AddedToCartConfirmationScreen } from '../screens/commerce/AddedToCartConfirmationScreen';
import { CartScreen } from '../screens/commerce/CartScreen';
import { WishlistScreen } from '../screens/commerce/WishlistScreen';

import { AddAddressFormScreen } from '../screens/checkout/AddAddressFormScreen';
import { AddressSelectionScreen } from '../screens/checkout/AddressSelectionScreen';
import { AuthenticationGateScreen } from '../screens/checkout/AuthenticationGateScreen';
import { CheckoutSummaryScreen } from '../screens/checkout/CheckoutSummaryScreen';
import { DeliveryMethodScreen } from '../screens/checkout/DeliveryMethodScreen';
import { OrderProcessingScreen } from '../screens/checkout/OrderProcessingScreen';
import { OrderReviewScreen } from '../screens/checkout/OrderReviewScreen';
import { CashOnDeliveryConfirmationScreen, PaymentCancelledScreen, PaymentFailureScreen, PaymentVerificationScreen, SecurePaymentLoadingScreen, SecurePaymentRedirectScreen } from '../screens/checkout/PaymentFlowScreens';
import { PaymentMethodScreen } from '../screens/checkout/PaymentMethodScreen';
import { PaymentStepIntroScreen } from '../screens/checkout/PaymentStepIntroScreen';
import { PaymentSuccessScreen } from '../screens/checkout/PaymentSuccessScreen';
import { CitySelectorScreen, DeliveryZoneSelectorScreen, EditCheckoutAddressScreen, NoSavedAddressScreen } from '../screens/checkout/CheckoutAddressStateScreens';
import { DeliveryByVendorScreen, DeliveryUnavailableScreen } from '../screens/checkout/CheckoutDeliveryStateScreens';
import { SavedPaymentCardsScreen, WalletBalanceScreen } from '../screens/checkout/CheckoutPaymentDetailScreens';
import { CheckoutErrorScreen, CheckoutSkeletonScreen, CheckoutTermsConfirmationScreen, OrderAlreadyInProgressScreen, OrderNeedsUpdateScreen, PaymentConfirmationTakingLongerScreen, PaymentPendingConfirmationScreen } from '../screens/checkout/CheckoutPaymentConflictSystemScreens';

import { AuthenticationWelcomeScreen } from '../screens/auth/AuthenticationWelcomeScreen';
import { LoginScreen } from '../screens/auth/LoginScreen';
import { LoginErrorScreen } from '../screens/auth/LoginErrorScreen';
import { LoginLoadingScreen } from '../screens/auth/LoginLoadingScreen';
import { RegistrationScreen } from '../screens/auth/RegistrationScreen';
import { TermsConsentScreen } from '../screens/auth/TermsConsentScreen';
import { AccountCreatedSuccessScreen } from '../screens/auth/AccountCreatedSuccessScreen';
import { FavoritesAuthPromptOverlay } from '../screens/auth/FavoritesAuthPromptOverlay';
import { ForgotPasswordScreen } from '../screens/auth/ForgotPasswordScreen';
import { EmailVerificationSentScreen } from '../screens/auth/EmailVerificationSentScreen';
import { PhoneOtpVerificationScreen } from '../screens/auth/PhoneOtpVerificationScreen';
import { OtpErrorScreen } from '../screens/auth/OtpErrorScreen';
import { CreateNewPasswordScreen } from '../screens/auth/CreateNewPasswordScreen';
import { PasswordChangedSuccessScreen } from '../screens/auth/PasswordChangedSuccessScreen';
import { authState, createCheckoutAuthReturnDestination } from '../commerce/authState';
import { wishlistState } from '../commerce/wishlistState';
import { accountPreferencesState } from '../commerce/accountPreferencesState';
import { notificationPreferencesState } from '../commerce/notificationPreferencesState';

import { AccountSettingsScreen } from '../screens/account/AccountSettingsScreen';
import { MyInformationScreen } from '../screens/account/MyInformationScreen';
import { EditProfileScreen } from '../screens/account/EditProfileScreen';
import { ChangeEmailScreen } from '../screens/account/ChangeEmailScreen';
import { ChangePhoneScreen } from '../screens/account/ChangePhoneScreen';
import { AccountVerifyPhoneOtpScreen } from '../screens/account/AccountVerifyPhoneOtpScreen';
import { ChangePasswordFormScreen } from '../screens/account/ChangePasswordFormScreen';
import { AccountSecurityScreen } from '../screens/account/AccountSecurityScreen';
import { SecurityPrivacyMenuScreen } from '../screens/account/SecurityPrivacyMenuScreen';
import { TwoFactorAuthScreen } from '../screens/account/TwoFactorAuthScreen';
import { BiometricAppLockScreen } from '../screens/account/BiometricAppLockScreen';
import { ActiveSessionsScreen } from '../screens/account/ActiveSessionsScreen';
import { MyAddressesListScreen } from '../screens/account/MyAddressesListScreen';
import { MyAddressesListV2Screen } from '../screens/account/MyAddressesListV2Screen';
import { AccountAddAddressV2Screen } from '../screens/account/AccountAddAddressV2Screen';
import { AccountAddAddressSimpleScreen } from '../screens/account/AccountAddAddressSimpleScreen';
import { AccountEditAddressScreen } from '../screens/account/AccountEditAddressScreen';
import { PaymentMethodsScreen } from '../screens/account/PaymentMethodsScreen';
import { LanguageRegionPreferencesScreen } from '../screens/account/LanguageRegionPreferencesScreen';
import { LanguageSelectionAccountScreen } from '../screens/account/LanguageSelectionAccountScreen';
import { LogoutConfirmationModal } from '../screens/account/LogoutConfirmationModal';
import { MarketingCartRemindersScreen } from '../screens/account/MarketingCartRemindersScreen';
import { MarketingDetailedPreferencesScreen } from '../screens/account/MarketingDetailedPreferencesScreen';
import { MarketingTogglesScreen } from '../screens/account/MarketingTogglesScreen';
import { NotificationChannelsScreen } from '../screens/account/NotificationChannelsScreen';
import { NotificationSettingsTogglesScreen } from '../screens/account/NotificationSettingsTogglesScreen';
import { NotificationDetailPrepScreen } from '../screens/account/NotificationDetailPrepScreen';
import { NotificationDetailShippedScreen } from '../screens/account/NotificationDetailShippedScreen';
import { SilentHoursDaySelectionScreen } from '../screens/account/SilentHoursDaySelectionScreen';
import { SilentHoursDoNotDisturbScreen } from '../screens/account/SilentHoursDoNotDisturbScreen';
import { SettingsScreen } from '../screens/account/SettingsScreen';
import { ThemeAppearanceScreen } from '../screens/account/ThemeAppearanceScreen';
import { AboutAppVersionScreen } from '../screens/account/AboutAppVersionScreen';
import { AboutMayushCompanyScreen } from '../screens/account/AboutMayushCompanyScreen';
import { AccessibilitySettingsScreen } from '../screens/account/AccessibilitySettingsScreen';
import { AppPermissionsScreen } from '../screens/account/AppPermissionsScreen';
import { DataUsageScreen } from '../screens/account/DataUsageScreen';
import { StorageCacheScreen } from '../screens/account/StorageCacheScreen';
import { ClearCacheConfirmationModal } from '../screens/account/ClearCacheConfirmationModal';
import { OfflineModeScreen } from '../screens/account/OfflineModeScreen';
import { LegalCenterScreen } from '../screens/account/LegalCenterScreen';
import { PrivacyDataManagementScreen } from '../screens/account/PrivacyDataManagementScreen';
import { PrivacyPolicyDocumentScreen } from '../screens/account/PrivacyPolicyDocumentScreen';
import { FaqAccordionScreen } from '../screens/support/FaqAccordionScreen';
import { FaqDetailScreen } from '../screens/support/FaqDetailScreen';
import { FaqCategoriesScreen } from '../screens/support/FaqCategoriesScreen';
import { HelpCenterCategoriesScreen } from '../screens/support/HelpCenterCategoriesScreen';
import { HelpCenterRequestsScreen } from '../screens/support/HelpCenterRequestsScreen';
import { HelpSupportHubScreen } from '../screens/support/HelpSupportHubScreen';
import { HelpCenterHomeScreen } from '../screens/support/HelpCenterHomeScreen';
import { HelpCategoryOrdersDeliveryScreen } from '../screens/support/HelpCategoryOrdersDeliveryScreen';
import { HelpCenterSearchResultsScreen } from '../screens/support/HelpCenterSearchResultsScreen';
import { FaqTabCategoriesScreen } from '../screens/support/FaqTabCategoriesScreen';
import { FaqArticleTrackOrderStepsScreen } from '../screens/support/FaqArticleTrackOrderStepsScreen';
import { MySupportTicketsListScreen } from '../screens/support/MySupportTicketsListScreen';
import { NoSupportRequestsEmptyStateScreen } from '../screens/support/NoSupportRequestsEmptyStateScreen';
import { ContactSupportFormScreen } from '../screens/support/ContactSupportFormScreen';
import { AttachFilesDocumentsScreen } from '../screens/support/AttachFilesDocumentsScreen';
import { ReviewSendSupportRequestScreen } from '../screens/support/ReviewSendSupportRequestScreen';
import { SelectOrderForSupportScreen } from '../screens/support/SelectOrderForSupportScreen';
import { ReplyToSupportMessageScreen } from '../screens/support/ReplyToSupportMessageScreen';
import { TicketDetailConversationThreadScreen } from '../screens/support/TicketDetailConversationThreadScreen';
import { CloseRequestConfirmationScreen } from '../screens/support/CloseRequestConfirmationScreen';
import { SupportRequestSentSuccessScreen } from '../screens/support/SupportRequestSentSuccessScreen';
import { TicketResolvedRatingScreen } from '../screens/support/TicketResolvedRatingScreen';
import { SupportConnectionErrorScreen } from '../screens/support/SupportConnectionErrorScreen';
import { SupportTemporarilyUnavailableScreen } from '../screens/support/SupportTemporarilyUnavailableScreen';
import { MaintenanceModeServicesImpactedScreen } from '../screens/support/MaintenanceModeServicesImpactedScreen';
import { AppUpdateAvailableScreen } from '../screens/support/AppUpdateAvailableScreen';
import { ForcedUpdateRequiredScreen } from '../screens/support/ForcedUpdateRequiredScreen';
import { SettingsErrorLoadingStateScreen } from '../screens/support/SettingsErrorLoadingStateScreen';
import { SettingsSkeletonLoadingStateScreen } from '../screens/support/SettingsSkeletonLoadingStateScreen';

import { CategoriesScreen } from '../screens/discovery/CategoriesScreen';
import { CategoryLandingScreen } from '../screens/discovery/CategoryLandingScreen';
import { CategoryProductListScreen } from '../screens/discovery/CategoryProductListScreen';
import { CollectionShopTheLookScreen } from '../screens/discovery/CollectionShopTheLookScreen';
import { CollectionsListScreen } from '../screens/discovery/CollectionsListScreen';
import { FilterPanelModal } from '../screens/discovery/FilterPanelModal';
import { HomeScreen } from '../screens/discovery/HomeScreen';
import { RecentlyViewedScreen } from '../screens/discovery/RecentlyViewedScreen';
import { getRecentlyViewedFallbackProducts } from '../screens/discovery/homeCatalog';
import { resolveAboutMayushBackDestination, resolveHomeCanonicalDestination, resolveOrderProcessingDestination, resolvePaymentFailureRecoveryDestination, resolvePaymentVerificationDestination, resolveSettingsAboutDestination } from './canonicalRuntime';

import { LanguageSelectionScreen } from '../screens/entry/LanguageSelectionScreen';
import { OnboardingScreen } from '../screens/entry/OnboardingScreen';
import { PreparingExperienceScreen } from '../screens/entry/PreparingExperienceScreen';
import { SplashScreen } from '../screens/entry/SplashScreen';

import { OrderDetailsScreen } from '../screens/orders/OrderDetailsScreen';
import { OrderInvoiceScreen } from '../screens/orders/OrderInvoiceScreen';
import { OrderPackageDetailsScreen } from '../screens/orders/OrderPackageDetailsScreen';
import { OrderPackagesScreen } from '../screens/orders/OrderPackagesScreen';
import { OrderTrackingScreen } from '../screens/orders/OrderTrackingScreen';
import { OrdersListScreen } from '../screens/orders/OrdersListScreen';
import { OrderThankYouScreen } from '../screens/orders/OrderThankYouScreen';
import { OrderCancellationConfirmationScreen, OrderCancellationReasonScreen, OrderCancellationRegisteredScreen, OrderCannotBeCancelledScreen } from '../screens/orders/OrderCancellationScreens';
import { OrderProductReviewScreen } from '../screens/orders/OrderProductReviewScreen';
import { OrderReorderAddedScreen, OrderReorderAvailabilityScreen, OrderReorderChangesScreen } from '../screens/orders/OrderReorderScreens';
import { OrderReturnDetailScreen, OrderReturnSelectionScreen, OrderReturnTrackingScreen } from '../screens/orders/OrderReturnScreens';
import { OrderCancelledRefundRequestScreen, OrderRefundCompletedScreen } from '../screens/orders/OrderRefundScreens';
import { DeliveryDelayedScreen, DeliveryFailedScreen, TrackingUnavailableScreen } from '../screens/orders/OrderDeliveryIssueScreens';
import { OrderDetailSkeletonScreen, OrderNotFoundScreen, OrdersEmptyScreen, OrdersErrorScreen, OrdersSkeletonScreen } from '../screens/orders/OrderSystemStateScreens';

import { ProductDeliveryReturnsScreen } from '../screens/product/ProductDeliveryReturnsScreen';
import { ProductDetailsScreen } from '../screens/product/ProductDetailsScreen';
import { ProductFullDescriptionScreen } from '../screens/product/ProductFullDescriptionScreen';
import { ProductGalleryScreen } from '../screens/product/ProductGalleryScreen';
import InspirationDetailScreen from '../screens/discovery/InspirationDetailScreen';
import { ProductReviewsRatingsScreen } from '../screens/product/ProductReviewsRatingsScreen';
import { ProductSpecificationsScreen } from '../screens/product/ProductSpecificationsScreen';
import { VariantSelectorSheet } from '../screens/product/VariantSelectorSheet';

import { FlashDealsScreen } from '../screens/promotions/FlashDealsScreen';
import { PromotionsCampaignsScreen } from '../screens/promotions/PromotionsCampaignsScreen';

import { SearchLandingScreen } from '../screens/search/SearchLandingScreen';
import { SearchNoResultsScreen } from '../screens/search/SearchNoResultsScreen';
import { SearchResultsScreen, CatalogListType } from '../screens/search/SearchResultsScreen';
import { notificationService } from '../services/api/notificationService';

import * as Linking from 'expo-linking';
import { ScreenKey } from './screenKeys';
import { resolveScreen } from './resolveScreen';
import { useBackgroundSyncDetector } from '../services/sync/useBackgroundSyncDetector';
export { ScreenKey };

const ONBOARDING_COMPLETE_KEY = 'mayush-mobile:onboarding-complete';
const LANGUAGE_KEY = 'mayush-mobile:language';

const SCREENS_WITH_TABBAR = new Set<ScreenKey>([
  'about-app', 'about-mayush', 'accessibility', 'account-security', 'account-settings', 'account-verify-phone', 'active-sessions', 'app-permissions', 'change-email', 'change-password', 'change-phone', 'data-usage', 'edit-profile', 'language-region', 'legal-center', 'marketing-cart-reminders', 'marketing-detailed-preferences', 'marketing-toggles', 'my-addresses', 'my-addresses-v2', 'my-information', 'notification-channels', 'notification-detail-prep', 'notification-detail-shipped', 'notification-settings-toggles', 'offline-mode', 'payment-methods', 'privacy-data', 'privacy-policy', 'security-privacy', 'settings', 'silent-hours-day-selection', 'silent-hours-dnd', 'storage-cache', 'security-2fa', 'theme-appearance', 'account', 'cart', 'wishlist', 'categories', 'category-products', 'collections-list', 'home', 'orders-list', 'product-details', 'product-gallery', 'app-update-available', 'attach-files-documents', 'close-request-confirmation', 'contact-support-form', 'faq', 'faq-article-track-order-steps', 'faq-categories', 'faq-detail', 'faq-tab-categories', 'help-category-orders-delivery', 'help-center', 'help-center-home', 'help-center-requests', 'help-center-search-results', 'help-support', 'maintenance-mode-services-impacted', 'my-support-tickets-list', 'no-support-requests-empty-state', 'reply-to-support-message', 'review-send-support-request', 'select-order-for-support', 'support-connection-error', 'support-request-sent-success', 'support-temporarily-unavailable', 'ticket-detail-conversation-thread', 'ticket-resolved-rating'
]);

interface RootNavigatorContentProps {
  hasCompletedOnboarding: boolean | null;
  onLanguageSelected: (language: MvpAppLanguage) => void;
  onOnboardingCompleted: () => void;
}

export const RootNavigatorContent: React.FC<RootNavigatorContentProps> = ({
  hasCompletedOnboarding,
  onLanguageSelected,
  onOnboardingCompleted,
}) => {
  const [currentScreen, setCurrentScreen] = useState<ScreenKey>('splash');
  const [splashFinished, setSplashFinished] = useState(false);
  const categoryProductsBackRef = useRef<ScreenKey>('category-landing');
  const searchResultsBackRef = useRef<ScreenKey>('search-landing');
  const collectionsListBackRef = useRef<ScreenKey>('home');
  const [catalogListType, setCatalogListType] = useState<CatalogListType>('search');
  const [catalogListTitle, setCatalogListTitle] = useState<string | undefined>();
  const [selectedCategory, setSelectedCategory] = useState<CategoryDto>();
  const [selectedProduct, setSelectedProduct] = useState<ProductMiniDto>();
  const [variantProduct, setVariantProduct] = useState<ProductDetailDto | null>(null);
  const [galleryImages, setGalleryImages] = useState<string[]>([]);
  const [galleryProductName, setGalleryProductName] = useState<string | undefined>();
  const [inspirationSlug, setInspirationSlug] = useState<string | undefined>();
  const [variantSheetVisible, setVariantSheetVisible] = useState(false);
  const [filterModalVisible, setFilterModalVisible] = useState(false);
  const [logoutModalVisible, setLogoutModalVisible] = useState(false);
  const [searchQuery, setSearchQuery] = useState('Fauteuil');
  const [cart, setCart] = useState<CartState>(() => cartStateManager.getState());
  const [selectedAddressId, setSelectedAddressId] = useState('');
  const [addressDraft, setAddressDraft] = useState<AddressDraft>(emptyAddressDraft);
  const [addressEditorMode, setAddressEditorMode] = useState<'add' | 'edit'>('add');
  const [editingAddressId, setEditingAddressId] = useState<string | null>(null);
  const [deliveryMethod, setDeliveryMethod] = useState<DeliveryMethod>('standard');
  const [paymentMethod, setPaymentMethod] = useState<PaymentMethod>('cmi');
  const [paymentProcessing, setPaymentProcessing] = useState(false);
  const [checkoutAttemptId, setCheckoutAttemptId] = useState(createLocalCheckoutAttemptId);
  const [termsAcceptance, setTermsAcceptance] = useState<CheckoutTermsAcceptance>();
  const [restoredCheckoutScreen, setRestoredCheckoutScreen] = useState<ResumableCheckoutScreen | null>(null);
  const [checkoutSessionResolved, setCheckoutSessionResolved] = useState(false);
  const [orderRepositoryResolved, setOrderRepositoryResolved] = useState(false);
  const [authRepositoryResolved, setAuthRepositoryResolved] = useState(false);
  const [domainRevision, setDomainRevision] = useState(0);
  const [favoritesPromptVisible, setFavoritesPromptVisible] = useState(false);
  const [favoritesPromptItemId, setFavoritesPromptItemId] = useState<string | undefined>();
  const [authPromptType, setAuthPromptType] = useState<'wishlist' | 'cart'>('wishlist');
  const [isClearCacheModalVisible, setIsClearCacheModalVisible] = useState(false);
  const [lastReorderResult, setLastReorderResult] = useState<ReorderCartResult | null>(null);
  const [unreadNotificationCount, setUnreadNotificationCount] = useState(0);

  useEffect(() => {
    let mounted = true;
    notificationService
      .getUnreadCount()
      .then((count) => {
        if (mounted) setUnreadNotificationCount(count);
      })
      .catch(() => {});
    return () => {
      mounted = false;
    };
  }, [domainRevision]);

  const paymentLock = useRef(false);
  const isAuthenticated = authState.isAuthenticated();
  const authenticatedUser = authState.getUser();
  const wishlistedProductIds = wishlistState.getProductIds();
  const savedAddresses = authState.getSavedAddresses();
  const paymentPreferences = accountPreferencesState.getPaymentMethods();
  const selectedPaymentPreferenceId = accountPreferencesState.getSelectedPaymentMethodId();
  const selectedPaymentPreference = paymentPreferences.find((method) => method.id === selectedPaymentPreferenceId);
  const orders = orderState.getOrders();
  const activeOrder = orderState.getSelectedOrder();
  const activePackage = orderState.getSelectedPackage();
  const cancellationDraft = orderActionState.getCancellationDraft();
  const cancellationRequest = activeOrder ? orderActionState.getCancellationRequest(activeOrder.orderId) : null;
  const reviewDraft = orderActionState.getReviewDraft();
  const reorderPlan = orderActionState.getReorderPlan();
  const returnDraft = orderActionState.getReturnDraft();
  const activeReturnRequest = orderActionState.getSelectedReturnRequest();
  const cancelledRefundDraft = orderActionState.getCancelledOrderRefundDraft();
  const activeRefund = orderActionState.getSelectedRefund();
  const activeDeliveryIssue = orderActionState.getSelectedDeliveryIssue();
  const activeRescheduleRequest = activeDeliveryIssue
    ? orderActionState.getDeliveryRescheduleRequests(activeDeliveryIssue.orderId).find((request) => request.deliveryIssueId === activeDeliveryIssue.deliveryIssueId) || null
    : null;
  const orderViewSnapshot = orderViewState.getSnapshot();

  const openActiveOrderDetails = () => {
    const selectedOrder = orderState.getSelectedOrder();
    if (!selectedOrder) { setCurrentScreen('orders-list'); return; }
    const route = getCanonicalOrderDetailRoute(selectedOrder);
    if (route === 'order-refund-request') orderActionState.beginCancelledOrderRefund(selectedOrder);
    setCurrentScreen(route);
  };

  const openOrderById = async (orderId: string) => {
    orderViewState.beginDetailLoad(orderId);
    setCurrentScreen('order-detail-skeleton');
    const selected = await orderState.selectOrder(orderId);
    const selectedOrder = selected ? orderState.getSelectedOrder() : null;
    orderViewState.resolveDetail(selectedOrder);
    if (!selectedOrder) { await orderState.selectOrder(null); setCurrentScreen('order-not-found'); return; }
    const route = getCanonicalOrderDetailRoute(selectedOrder);
    if (route === 'order-refund-request') orderActionState.beginCancelledOrderRefund(selectedOrder);
    setCurrentScreen(route);
  };

  const openOrdersList = () => {
    orderViewState.beginListLoad();
    setCurrentScreen('orders-skeleton');
    Promise.resolve().then(() => {
      const status = orderViewState.resolveList(orderState.getOrders());
      setCurrentScreen(status === 'empty' ? 'orders-empty' : 'orders-list');
    }).catch(() => { orderViewState.failListLoad(); setCurrentScreen('orders-error'); });
  };

  const openSupportForActiveOrder = (returnRequestId?: string, refundId?: string) => {
    if (activeOrder) supportState.setContactDraft({ selectedOrderId: activeOrder.orderId, returnRequestId, refundId });
    setCurrentScreen('contact-support-form');
  };

  const openOrderSupport = () => {
    if (activeOrder) supportState.setContactDraft({ selectedOrderId: activeOrder.orderId });
    setCurrentScreen('order-support-contact');
  };

  const openTrackingForActiveOrder = (packageId?: string) => {
    if (!activeOrder) return;
    const issue = orderActionState.selectDeliveryIssueForOrder(activeOrder, packageId);
    if (issue?.type === 'delayed') { setCurrentScreen('delivery-delayed'); return; }
    if (issue?.type === 'delivery_failed') { setCurrentScreen('delivery-failed'); return; }
    if (!hasOrderTrackingMetadata(activeOrder, packageId)) { setCurrentScreen('tracking-unavailable'); return; }
    setCurrentScreen('order-tracking');
  };

  const submitDeliveryReschedule = async (slot: string): Promise<boolean> => {
    if (!activeOrder || !activeDeliveryIssue) return false;
    return Boolean(await orderActionState.requestDeliveryReschedule(activeOrder, activeDeliveryIssue.deliveryIssueId, slot));
  };

  const openReturnForActiveOrder = () => {
    if (!activeOrder || !orderActionState.beginReturn(activeOrder)) return;
    setCurrentScreen('order-return-selection');
  };

  const submitReturnForActiveOrder = async (): Promise<boolean> => {
    if (!activeOrder) return false;
    const request = await orderActionState.submitReturnRequest(activeOrder);
    if (!request) return false;
    setCurrentScreen('order-return-detail');
    return true;
  };

  const confirmCancelledRefund = async (): Promise<boolean> => {
    if (!activeOrder) return false;
    const processing = await orderActionState.requestCancelledOrderRefund(activeOrder);
    if (!processing) return false;
    const completed = await orderActionState.completeRefundFixture(processing.refundId);
    if (!completed) return false;
    setCurrentScreen('order-refund-completed');
    return true;
  };

  const openCancellationForActiveOrder = () => {
    if (!activeOrder) return;
    const eligibility = orderActionState.beginCancellation(activeOrder);
    setCurrentScreen(eligibility === 'eligible' ? 'order-cancel-confirmation' : 'order-cannot-cancel');
  };

  const continueCancellationForActiveOrder = () => {
    if (!activeOrder) return;
    const draft = orderActionState.getCancellationDraft();
    setCurrentScreen(canCancelOrder(activeOrder) && draft?.orderId === activeOrder.orderId ? 'order-cancel-reason' : 'order-cannot-cancel');
  };

  const submitCancellationForActiveOrder = async (): Promise<boolean> => {
    if (!activeOrder) return false;
    const request = await orderActionState.submitCancellationRequest(activeOrder);
    if (!request) return false;
    setCurrentScreen('order-cancel-registered');
    return true;
  };

  const openReviewForActiveOrder = () => {
    if (!activeOrder || !orderActionState.beginReview(activeOrder)) return;
    setCurrentScreen('order-product-review');
  };

  const submitReviewForActiveOrder = async (): Promise<boolean> => {
    if (!activeOrder) return false;
    const reviews = await orderActionState.submitProductReviews(activeOrder);
    if (!reviews) return false;
    openActiveOrderDetails();
    return true;
  };

  const commitReorderToCart = (): boolean => {
    const result = orderActionState.addSelectedReorderItemsToCart(cartStateManager.getState());
    if (!result || result.addedLineIds.length === 0) return false;
    cartStateManager.setCart(result.cart);
    setLastReorderResult(result);
    setCurrentScreen('order-reorder-added');
    return true;
  };

  const openReorderForActiveOrder = () => {
    if (!activeOrder) return;
    setLastReorderResult(null);
    const plan = orderActionState.beginReorder(activeOrder);
    if (plan.resultVariant === 'changed_unavailable') setCurrentScreen('order-reorder-changes');
    else if (plan.resultVariant === 'availability_changes') setCurrentScreen('order-reorder-availability');
    else {
      plan.lines.forEach((line) => orderActionState.setReorderLineSelected(line.orderLineId, true));
      commitReorderToCart();
    }
  };

  const resumeAuthReturnDestination = () => {
    const destination = authState.getReturnDestination();
    authState.clearReturnDestination();
    if (destination && destination.route) {
      if (destination.route === 'cart') {
        setCurrentScreen('cart');
      } else if (destination.route === 'checkout') {
        setCurrentScreen('checkout-summary');
      } else if (destination.route === 'wishlist') {
        setCurrentScreen('wishlist');
      } else {
        setCurrentScreen(destination.route as ScreenKey);
      }
    } else {
      setCurrentScreen('home');
    }
  };

  const selectCategory = (category: CategoryDto) => {
    setSelectedCategory(category);
    categoryProductsBackRef.current = currentScreen;
    setCurrentScreen('category-landing');
  };
  const selectProduct = (product: ProductMiniDto) => { setSelectedProduct(product); setCurrentScreen('product-details'); };

  const handleToggleWishlist = (product: ProductMiniDto) => {
    if (isAuthenticated) {
      wishlistState.toggle(product);
    } else {
      setAuthPromptType('wishlist');
      setFavoritesPromptItemId(String(product.id));
      setFavoritesPromptVisible(true);
    }
  };
  const activeTab: TabKey = currentScreen === 'categories' || currentScreen === 'category-products' || currentScreen === 'category-landing'
    ? 'categories'
    : currentScreen === 'wishlist'
      ? 'wishlist'
      : currentScreen === 'cart'
        ? 'cart'
        : currentScreen === 'account'
          ? 'account'
          : 'home';

  // Background sync detector — triggers sync after 15+ min, session restore after 30+ min
  useBackgroundSyncDetector();

  // Deep link handler — resolves incoming URLs or shows page-not-found
  const previousScreenRef = useRef<ScreenKey>('home');
  useEffect(() => { previousScreenRef.current = currentScreen; }, [currentScreen]);

  useEffect(() => {
    if (!splashFinished) return;
    const handleDeepLink = (event: { url: string }) => {
      const resolution = resolveScreen(event.url);
      if (resolution.key) {
        setCurrentScreen(resolution.key);
      } else {
        // Unknown destination: could integrate page-not-found system state here
        setCurrentScreen('home');
      }
    };
    const subscription = Linking.addEventListener('url', handleDeepLink);
    return () => subscription.remove();
  }, [splashFinished]);

  useEffect(() => {
    if (!splashFinished || hasCompletedOnboarding === null || !checkoutSessionResolved || !orderRepositoryResolved || !authRepositoryResolved) return;
    setCurrentScreen(hasCompletedOnboarding ? (restoredCheckoutScreen || 'home') : 'language');
  }, [hasCompletedOnboarding, splashFinished, checkoutSessionResolved, orderRepositoryResolved, authRepositoryResolved, restoredCheckoutScreen]);

  useEffect(() => {
    const refresh = () => setDomainRevision((revision) => revision + 1);
    const unsubscribeAuth = authState.subscribe(refresh);
    const unsubscribeWishlist = wishlistState.subscribe(refresh);
    const unsubscribePreferences = accountPreferencesState.subscribe(refresh);
    const unsubscribeOrders = orderState.subscribe(refresh);
    const unsubscribeOrderActions = orderActionState.subscribe(refresh);
    const unsubscribeOrderViews = orderViewState.subscribe(refresh);
    let active = true;
    const restorationToken = systemRuntimeState.begin('data-restoration', 0);
    const restorePersistedData = async () => {
      try {
        await wishlistState.hydrate();
      } finally {
        if (active) systemRuntimeState.update(restorationToken, 1 / 3);
      }
      try {
        await authState.hydrate();
      } finally {
        if (active) {
          setAuthRepositoryResolved(true);
          systemRuntimeState.update(restorationToken, 2 / 3);
        }
      }
      try {
        await Promise.all([orderState.hydrate(), orderActionState.hydrate()]);
      } finally {
        if (active) {
          setOrderRepositoryResolved(true);
          systemRuntimeState.complete(restorationToken);
        }
      }
    };
    void restorePersistedData();
    return () => {
      active = false;
      systemRuntimeState.clear(restorationToken);
      unsubscribeAuth(); unsubscribeWishlist(); unsubscribePreferences(); unsubscribeOrders(); unsubscribeOrderActions(); unsubscribeOrderViews();
    };
  }, []);

  useEffect(() => {
    if (authState.consumeSessionInvalidation()) {
      authState.setReturnDestination({ route: currentScreen });
      setCurrentScreen('login');
    }
  }, [currentScreen, domainRevision]);

  useEffect(() => {
    if (currentScreen !== 'order-package-detail' || activePackage) return;
    setCurrentScreen(activeOrder?.packages.length ? 'order-packages' : 'orders-list');
  }, [activeOrder?.orderId, activeOrder?.packages.length, activePackage?.packageId, currentScreen]);

  // Subscribe to cartStateManager so React state stays in sync across
  // app reload, login, logout, and background hydration.
  useEffect(() => {
    // Immediately pick up whatever cartStateManager already has (may be
    // hydrated from AsyncStorage by the time this effect runs).
    setCart(cartStateManager.getState());

    const unsubscribeCart = cartStateManager.subscribe(() => {
      setCart(cartStateManager.getState());
    });

    return () => { unsubscribeCart(); };
  }, []);

  useEffect(() => {
    let isMounted = true;
    void loadCheckoutSession(AsyncStorage).then((parsedSession) => {
      if (!isMounted) return;
      const terminalPaymentScreens = ['payment-pending', 'payment-failed', 'payment-cancelled', 'payment-confirmation-delayed'];
      if (parsedSession && !terminalPaymentScreens.includes(parsedSession.screen)) {
        setRestoredCheckoutScreen(parsedSession.screen);
        setCheckoutAttemptId(parsedSession.checkoutAttemptId);
        setSelectedAddressId(parsedSession.selectedAddressId);
        setDeliveryMethod(parsedSession.deliveryMethod);
        setPaymentMethod(parsedSession.paymentMethod);
        setTermsAcceptance(parsedSession.termsAcceptance);
        if (parsedSession.selectedPaymentPreferenceId) {
          accountPreferencesState.setSelectedPaymentMethod(parsedSession.selectedPaymentPreferenceId);
        }
      } else if (parsedSession) {
        void clearCheckoutSession(AsyncStorage);
      }
      setCheckoutSessionResolved(true);
    }).catch(() => {
      if (isMounted) setCheckoutSessionResolved(true);
    });
    return () => { isMounted = false; };
  }, []);

  useEffect(() => {
    if (!checkoutSessionResolved) return;
    if (isResumableCheckoutScreen(currentScreen)) {
      void saveCheckoutSession(AsyncStorage, {
        checkoutAttemptId,
        screen: currentScreen,
        selectedAddressId,
        deliveryMethod,
        paymentMethod,
        selectedPaymentPreferenceId,
        termsAcceptance,
      }).catch(() => undefined);
    }
  }, [checkoutAttemptId, checkoutSessionResolved, currentScreen, deliveryMethod, paymentMethod, savedAddresses, selectedAddressId, selectedPaymentPreferenceId, termsAcceptance]);


  const updateCartQuantity = (lineId: string, delta: number) => {
    const currentCart = cartStateManager.getState();
    const line = currentCart.lines.find((item) => item.id === lineId);
    if (!line) return;
    const nextQuantity = line.quantity + delta;
    const nextCart = updateCartLineQuantity(currentCart, lineId, nextQuantity);
    cartStateManager.setCart(nextCart);

    const numericLineId = Number.parseInt(lineId.replace(/\D/g, ''), 10);
    if (Number.isFinite(numericLineId)) {
      if (nextQuantity <= 0) {
        cartService.removeCartItem(numericLineId).catch(() => undefined);
      } else {
        cartService.changeQuantity(numericLineId, nextQuantity).catch(() => undefined);
      }
    }
  };

  const addSelectedVariantToCart = (variant: string, quantity: number, selectedUnitPriceMad?: number) => {
    if (!variantProduct) return;
    // Use the authoritative numeric price from the backend; fall back to parsing formatted strings
    const basePrice = variantProduct.calculable_price > 0
      ? Math.round(variantProduct.calculable_price)
      : parseMadPrice(variantProduct.main_price || variantProduct.stroked_price || variantProduct.priceFormatted || '0');
    const unitPriceMad = (selectedUnitPriceMad && selectedUnitPriceMad > 0) ? selectedUnitPriceMad : basePrice;
    const primaryImage = variantProduct.photos?.[0] || variantProduct.thumbnail_image || variantProduct.thumbnail_img || '';
    const lineName = variantProduct.name || 'Produit Mayush';
    const sellerId = variantProduct.seller_id ? String(variantProduct.seller_id) : undefined;
    const sellerName = variantProduct.shop_name || undefined;
    const nextCart = addCartLine(cartStateManager.getState(), createSelectedVariantCartLine({
      productId: variantProduct.id,
      name: lineName,
      variant,
      quantity,
      unitPriceMad,
      imageUri: primaryImage,
      sellerId,
      sellerName,
      variantOptions: [{ variantId: variant, label: variant, unitPriceMad }],
    }));
    cartStateManager.setCart(nextCart);

    const user = authState.getUser();
    cartService.addToCart({
      productId: variantProduct.id,
      variant,
      quantity,
      userId: user?.id,
    }).then(() => {
      // Re-sync from server to get authoritative prices
      void cartStateManager.hydrate(user?.id ? String(user.id) : undefined);
    }).catch(() => undefined);

    setVariantSheetVisible(false);
    setCurrentScreen('added-to-cart');
  };

  const moveWishlistItemToCart = (product: ProductMiniDto) => {
    const unitPriceMad = product.priceMad
      || product.base_discounted_price
      || product.base_price
      || parseMadPrice(product.main_price || product.stroked_price || '0');
    const imgSrc = product.thumbnail_image || '';
    const nextCart = addCartLine(cartStateManager.getState(), {
      id: `${product.id}:wishlist`,
      productId: product.id,
      name: product.name,
      productName: product.name,
      variant: 'Standard',
      selectedVariantText: 'Standard',
      quantity: 1,
      unitPriceMad,
      imageUri: imgSrc,
      imageAsset: imgSrc,
    });
    cartStateManager.setCart(nextCart);

    const user = authState.getUser();
    cartService.addToCart({
      productId: product.id,
      variant: 'Standard',
      quantity: 1,
      userId: user?.id,
    }).then(() => {
      void cartStateManager.hydrate(user?.id ? String(user.id) : undefined);
    }).catch(() => undefined);

    setCurrentScreen('added-to-cart');
  };

  const saveAddress = async () => {
    const errors = validateAddressDraft(addressDraft);
    if (Object.keys(errors).length > 0) {
      setCurrentScreen('add-address-errors');
      return;
    }
    const newId = `address-${Date.now()}`;
    const newAddress = createSavedAddress(addressDraft, newId);
    await authState.addAddress(newAddress);
    setSelectedAddressId(newId);
    setCurrentScreen('address-selection');
  };

  const saveEditedAddress = async () => {
    const errors = validateAddressDraft(addressDraft);
    if (!editingAddressId || Object.keys(errors).length > 0) return;
    await authState.updateAddress(editingAddressId, createSavedAddress(addressDraft, editingAddressId));
    setSelectedAddressId(editingAddressId);
    setCurrentScreen('address-selection');
  };

  const openAddCheckoutAddress = () => {
    setAddressEditorMode('add');
    setEditingAddressId(null);
    setAddressDraft(emptyAddressDraft());
    setCurrentScreen('city-selector');
  };

  const openEditCheckoutAddress = () => {
    const address = savedAddresses.find((item) => item.id === selectedAddressId) || savedAddresses[0];
    if (!address) { setCurrentScreen('no-saved-address'); return; }
    setAddressEditorMode('edit');
    setEditingAddressId(address.id);
    setAddressDraft(addressToDraft(address));
    setCurrentScreen('edit-checkout-address');
  };

  const selectedAddress = savedAddresses.find((item) => item.id === selectedAddressId) || savedAddresses.find((item) => item.isDefault) || savedAddresses[0];
  const deliveryProjection = buildSellerDeliveryProjection(cart.lines, selectedAddress, deliveryMethod);
  const checkoutTotalMad = getCheckoutGrandTotalMad(getCartTotals(cart).totalMad, deliveryProjection.deliveryFeeMad);
  const checkoutMaterialSignature = createCheckoutMaterialSignature({ cart, selectedAddressId, deliveryMethod, paymentMethod, deliveryFeeMad: deliveryProjection.deliveryFeeMad });

  useEffect(() => {
    if (termsAcceptance && !isCheckoutTermsAcceptanceValid(termsAcceptance, checkoutAttemptId, checkoutMaterialSignature)) {
      setTermsAcceptance(undefined);
    }
  }, [checkoutAttemptId, checkoutMaterialSignature, termsAcceptance]);

  useEffect(() => {
    if (!checkoutSessionResolved || !isResumableCheckoutScreen(currentScreen)) return;
    const needsAddress = !['checkout-summary', 'address-selection', 'add-address', 'city-selector', 'delivery-zone-selector', 'no-saved-address'].includes(currentScreen);
    if (needsAddress && !selectedAddress) { setCurrentScreen('no-saved-address'); return; }
    if (selectedAddressId && savedAddresses.length && !savedAddresses.some((address) => address.id === selectedAddressId)) {
      setSelectedAddressId(savedAddresses.find((address) => address.isDefault)?.id || savedAddresses[0].id);
    }
  }, [checkoutSessionResolved, currentScreen, savedAddresses, selectedAddress, selectedAddressId]);

  const navigateTab = (tab: TabKey) => {
    if (tab === 'home') setCurrentScreen('home');
    if (tab === 'categories') setCurrentScreen('categories');
    if (tab === 'wishlist') {
      if (!isAuthenticated) { setAuthPromptType('wishlist'); setFavoritesPromptVisible(true); return; }
      setCurrentScreen('wishlist');
    }
    if (tab === 'cart') {
      if (!isAuthenticated) { setAuthPromptType('cart'); setFavoritesPromptVisible(true); return; }
      setCurrentScreen('cart');
    }
    if (tab === 'account') setCurrentScreen('account');
  };

  const startCheckout = () => {
    const attemptId = createLocalCheckoutAttemptId();
    setCheckoutAttemptId(attemptId);
    setTermsAcceptance(undefined);
    setRestoredCheckoutScreen(null);
    setCurrentScreen('checkout-skeleton');
    Promise.resolve().then(() => setCurrentScreen(savedAddresses.length ? 'checkout-summary' : 'no-saved-address')).catch(() => setCurrentScreen('checkout-error'));
  };

  const finalizeSuccessfulCheckout = () => {
    paymentLock.current = false;
    setPaymentProcessing(false);
    cartStateManager.reset();
    void clearCheckoutSession(AsyncStorage).catch(() => undefined);
    setCheckoutAttemptId(createLocalCheckoutAttemptId());
    setTermsAcceptance(undefined);
    setRestoredCheckoutScreen(null);
    setCurrentScreen('payment-success');
  };

  const finishOrderProcessing = async () => {
    paymentLock.current = false;
    setPaymentProcessing(false);
    const order = orderState.getSelectedOrder();
    if (!order) { setCurrentScreen('checkout-error'); return; }
    const destination = resolveOrderProcessingDestination(order.paymentMethod);
    if (destination === 'secure-payment-redirect' || destination === 'cash-on-delivery-confirmation') {
      setCurrentScreen(destination);
      return;
    }
    await orderState.transitionPaymentStatus(order.checkoutAttemptId, 'confirmed');
    finalizeSuccessfulCheckout();
  };

  const verifyActivePayment = async () => {
    const order = orderState.getSelectedOrder();
    if (!order) { setCurrentScreen('checkout-error'); return; }
    const outcome = resolveFrontendPaymentVerificationOutcome(order);
    if (outcome === 'not_applicable') {
      setCurrentScreen('cash-on-delivery-confirmation');
      return;
    }
    if (outcome === 'pending') {
      const pendingDestination = resolvePaymentVerificationDestination('pending');
      setCurrentScreen(pendingDestination === 'payment-confirmation-delayed' ? 'payment-confirmation-delayed' : 'payment-pending');
      return;
    }
    const updated = await orderState.transitionPaymentStatus(order.checkoutAttemptId, outcome);
    if (!updated) { setCurrentScreen('payment-pending'); return; }
    if (outcome === 'confirmed') {
      finalizeSuccessfulCheckout();
      return;
    }
    setCurrentScreen(resolvePaymentVerificationDestination(outcome));
  };

  const cancelActivePayment = async () => {
    const order = orderState.getSelectedOrder();
    if (!order) { setCurrentScreen('checkout-error'); return; }
    await orderState.transitionPaymentStatus(order.checkoutAttemptId, 'cancelled');
    setCurrentScreen(resolvePaymentVerificationDestination('cancelled'));
  };

  const recoverActivePayment = async (action: 'retry' | 'change_method') => {
    const order = orderState.getSelectedOrder();
    if (!order) { setCurrentScreen('checkout-error'); return; }
    const updated = await orderState.transitionPaymentStatus(order.checkoutAttemptId, 'prototype_pending_confirmation');
    if (!updated) { setCurrentScreen('payment-pending'); return; }
    setCurrentScreen(resolvePaymentFailureRecoveryDestination(action));
  };

  const completeCheckout = () => {
    if (paymentMethod === 'wallet' && !isAuthenticated) {
      authState.setReturnDestination(createCheckoutAuthReturnDestination(checkoutAttemptId, 'wallet-balance'));
      setCurrentScreen('auth-gate');
      return;
    }
    setCurrentScreen('order-review');
  };

  const continueFromPaymentMethod = () => {
    if (paymentMethod === 'wallet') { setCurrentScreen('wallet-balance'); return; }
    if (paymentMethod === 'cmi') { setCurrentScreen('saved-payment-cards'); return; }
    completeCheckout();
  };

  const openSelectedNotificationOrder = () => {
    const orderId = notificationPreferencesState.getSelectedNotification()?.orderId;
    if (!orderId) {
      setCurrentScreen('orders-list');
      return;
    }
    void openOrderById(orderId);
  };

  const beginOrderReview = async (acceptedTerms?: CheckoutTermsAcceptance) => {
    if (paymentLock.current || !selectedAddress || !deliveryProjection.available) return;
    const hasCheckoutConflict = cart.lines.some((line) => line.id === 'line-fs-1023' && line.unitPriceMad === 2890)
      || cart.lines.some((line) => line.id === 'line-tb-2045' && line.quantity > 2);
    if (hasCheckoutConflict) {
      setTermsAcceptance(undefined);
      setCurrentScreen('order-needs-update');
      return;
    }
    const effectiveAcceptance = acceptedTerms || termsAcceptance;
    if (!isCheckoutTermsAcceptanceValid(effectiveAcceptance, checkoutAttemptId, checkoutMaterialSignature)) {
      setCurrentScreen('checkout-terms-confirmation');
      return;
    }
    paymentLock.current = true;
    setPaymentProcessing(true);
    const existingOrder = orderState.getOrderByCheckoutAttemptId(checkoutAttemptId);
    if (existingOrder && existingOrder.paymentStatus !== 'confirmed') {
      const resumedOrder = await orderState.preparePaymentRetry(checkoutAttemptId, {
        paymentMethod,
        paymentPreferenceId: selectedPaymentPreferenceId,
        paymentCardLast4: selectedPaymentPreference?.last4,
        paymentVerificationScenario: selectedPaymentPreference?.verificationScenario,
      });
      if (resumedOrder) {
        setCurrentScreen('order-processing');
        return;
      }
    }
    const result = await orderState.createOrder({
      cart,
      address: selectedAddress,
      deliveryMethod,
      paymentMethod,
      checkoutAttemptId,
      deliveryFeeMad: deliveryProjection.deliveryFeeMad,
      deliveryPackageCount: deliveryProjection.packageCount,
      paymentPreferenceId: selectedPaymentPreferenceId,
      paymentCardLast4: selectedPaymentPreference?.last4,
      paymentVerificationScenario: selectedPaymentPreference?.verificationScenario,
    });
    if (!result.created) {
      paymentLock.current = false;
      setPaymentProcessing(false);
      setCurrentScreen('order-already-in-progress');
      return;
    }

    if (authState.isAuthenticated()) {
      const backendPaymentType = paymentMethod === 'cmi' ? 'cmi' : paymentMethod === 'wallet' ? 'wallet' : 'cash_on_delivery';
      checkoutService.submitOrder({
        payment_type: backendPaymentType,
        user_id: authState.getUser()?.id,
        address_id: selectedAddress?.id,
      }).catch(() => undefined);
    }

    setCurrentScreen('order-processing');
  };

  const acceptCheckoutTermsAndContinue = () => {
    const acceptance = acceptCheckoutTerms(checkoutAttemptId, checkoutMaterialSignature);
    setTermsAcceptance(acceptance);
    void beginOrderReview(acceptance);
  };

  const acceptCheckoutConflictChanges = () => {
    const currentCart = cartStateManager.getState();
    const nextCart = applyCartConflictChanges(currentCart, [
      { kind: 'price', lineId: 'line-fs-1023', oldPriceMad: 2890, newPriceMad: 3190 },
      { kind: 'stock', lineId: 'line-tb-2045', oldQuantity: 5, newQuantity: 2 },
      ...(currentCart.appliedPromotionId ? [{ kind: 'promotion_invalidated' as const, promotionId: currentCart.appliedPromotionId }] : []),
    ]);
    cartStateManager.setCart(nextCart);
    setTermsAcceptance(undefined);
    setCurrentScreen('checkout-skeleton');
    Promise.resolve().then(() => setCurrentScreen('order-review')).catch(() => setCurrentScreen('checkout-error'));
  };

  const retryCheckoutLoad = () => {
    setCurrentScreen('checkout-skeleton');
    Promise.resolve().then(() => {
      const recovery = resolveCheckoutRecovery({ cart, selectedAddress, deliveryMethod });
      setCurrentScreen(recovery.destination);
    }).catch(() => setCurrentScreen('checkout-error'));
  };

  const handleSearchSubmit = (query: string) => {
    setSearchQuery(query.trim());
    setCurrentScreen('search-results');
  };

  const updateCartVariant = (lineId: string, selection: CartVariantSelection): boolean => {
    const result = updateCartLineVariant(cartStateManager.getState(), lineId, selection);
    if (!result.updated) return false;
    cartStateManager.setCart(result.cart);
    return true;
  };

  const applyCartPromotion = (code: string) => {
    const cleanCode = code.trim().toUpperCase();
    const result = applyPromotionCode(cartStateManager.getState(), cleanCode);
    if (result.validation.code === 'VALID') {
      cartStateManager.setCart(result.cart);
    }
    if (authState.isAuthenticated()) {
      cartService.applyCoupon({
        couponCode: cleanCode,
        userId: authState.getUser()?.id,
      }).catch(() => undefined);
    }
    return result.validation;
  };

  const clearCartPromotion = () => {
    const nextCart = removeCartPromotion(cartStateManager.getState());
    cartStateManager.setCart(nextCart);
  };

  const handleMergeCartLines = (mergedLines: CartLine[]) => {
    const currentCart = cartStateManager.getState();
    const nextCart = revalidateCartPromotion({ ...currentCart, lines: mergedLines });
    cartStateManager.setCart(nextCart);
  };

  const insets = useSafeAreaInsets();
  const safeTopPadding = currentScreen === 'splash'
    ? 0
    : Math.max(
        insets.top,
        Platform.OS === 'android' ? (StatusBar.currentHeight || 24) : (Platform.OS === 'ios' ? 44 : 0)
      );

  return (
    <View style={[styles.container, { paddingTop: safeTopPadding }]}>
      {currentScreen === 'splash' ? <SplashScreen onFinish={() => setSplashFinished(true)} /> : null}
      {currentScreen === 'language' ? <LanguageSelectionScreen onContinue={(language) => { onLanguageSelected(language); setCurrentScreen('preparing'); }} /> : null}
      {currentScreen === 'preparing' ? <PreparingExperienceScreen onFinish={() => setCurrentScreen('onboarding-1')} /> : null}
      {currentScreen === 'onboarding-1' ? <OnboardingScreen step={1} onNext={() => setCurrentScreen('onboarding-2')} onSkip={onOnboardingCompleted} /> : null}
      {currentScreen === 'onboarding-2' ? <OnboardingScreen step={2} onNext={() => setCurrentScreen('onboarding-3')} onSkip={onOnboardingCompleted} /> : null}
      {currentScreen === 'onboarding-3' ? <OnboardingScreen step={3} onNext={onOnboardingCompleted} onSkip={onOnboardingCompleted} /> : null}

      {currentScreen === 'home' ? (
        <HomeScreen
          activeTab={activeTab}
          isAuthenticated={isAuthenticated}
          authenticatedUser={authenticatedUser}
          orders={orders}
          cartProductIds={cart.lines.map((line) => Number(line.productId)).filter(Number.isFinite)}
          wishlistedProductIds={wishlistedProductIds}
          cartBadgeCount={isAuthenticated ? cart.lines.reduce((sum, line) => sum + line.quantity, 0) : 0}
          onSelectCategory={selectCategory}
          onSelectProduct={selectProduct}
          onNavigateTab={navigateTab}
          onOpenSearch={() => setCurrentScreen('search-landing')}
          onOpenWishlist={() => setCurrentScreen('wishlist')}
          onOpenPromotions={() => {
            setCatalogListType('promotions');
            setCatalogListTitle(undefined);
            setSearchQuery('');
            searchResultsBackRef.current = 'home';
            setCurrentScreen('search-results');
          }}
          onOpenRecentlyViewed={() => setCurrentScreen(resolveHomeCanonicalDestination('recently_viewed'))}
          onOpenBestSellers={() => {
            setCatalogListType('best_sellers');
            setCatalogListTitle(undefined);
            setSearchQuery('');
            searchResultsBackRef.current = 'home';
            setCurrentScreen('search-results');
          }}
          onOpenNewArrivals={() => {
            setCatalogListType('new_arrivals');
            setCatalogListTitle(undefined);
            setSearchQuery('');
            searchResultsBackRef.current = 'home';
            setCurrentScreen('search-results');
          }}
          onOpenInspiration={() => {
            setCatalogListType('inspiration');
            setCatalogListTitle(undefined);
            setSearchQuery('');
            searchResultsBackRef.current = 'home';
            setCurrentScreen('search-results');
          }}
          onOpenRecommended={() => {
            setCatalogListType('recommended');
            setCatalogListTitle(undefined);
            setSearchQuery('');
            searchResultsBackRef.current = 'home';
            setCurrentScreen('search-results');
          }}
          onOpenCollections={() => {
            collectionsListBackRef.current = 'home';
            setCurrentScreen('collections-list');
          }}
          onSelectCollection={(collection) => {
            setCatalogListType('collections');
            setCatalogListTitle(collection.name);
            setSearchQuery('');
            searchResultsBackRef.current = 'home';
            setCurrentScreen('search-results');
          }}
          onOpenPartners={() => {
            setCatalogListType('partners');
            setCatalogListTitle(undefined);
            setSearchQuery('');
            searchResultsBackRef.current = 'home';
            setCurrentScreen('search-results');
          }}
          onOpenAmbiances={() => {
            setCatalogListType('ambiances');
            setCatalogListTitle(undefined);
            setSearchQuery('');
            searchResultsBackRef.current = 'home';
            setCurrentScreen('search-results');
          }}
          onOpenOrder={(orderId) => { void openOrderById(orderId); }}
          onToggleWishlist={handleToggleWishlist}
        />
      ) : null}
      {currentScreen === 'categories' ? (
        <CategoriesScreen
          activeTab={activeTab}
          onSelectCategory={(cat) => {
            setSelectedCategory(cat);
            categoryProductsBackRef.current = 'categories';
            setCurrentScreen('category-landing');
          }}
          onNavigateTab={navigateTab}
          onOpenSearch={() => setCurrentScreen('search-landing')}
          onOpenCart={() => setCurrentScreen('cart')}
          onOpenNotifications={() => setCurrentScreen('notification-settings-toggles')}
          cartCount={cart.lines.reduce((sum, line) => sum + line.quantity, 0)}
          notificationCount={unreadNotificationCount}
        />
      ) : null}
      {currentScreen === 'category-landing' ? (
        <CategoryLandingScreen
          category={selectedCategory}
          onBack={() => setCurrentScreen(categoryProductsBackRef.current)}
          onSelectSubcategory={(subcat) => {
            if (typeof subcat === 'object') {
              setSelectedCategory(subcat);
              categoryProductsBackRef.current = 'category-landing';
              setCurrentScreen('category-landing');
            } else {
              setSelectedCategory({ id: 0, name: subcat, slug: subcat, banner: '', icon: '', number_of_children: 0, links: { products: '', sub_categories: '' } });
              categoryProductsBackRef.current = 'category-landing';
              setCurrentScreen('category-landing');
            }
          }}
          onOpenCollection={(colId) => {
            setCatalogListType('collections');
            setCatalogListTitle(`Collection ${colId}`);
            searchResultsBackRef.current = 'category-landing';
            setCurrentScreen('search-results');
          }}
          onViewAllCollections={() => {
            collectionsListBackRef.current = 'category-landing';
            setCurrentScreen('collections-list');
          }}
          onViewAllPopular={() => {
            setCatalogListType('best_sellers');
            setCatalogListTitle(selectedCategory?.name ? `Populaires - ${selectedCategory.name}` : undefined);
            searchResultsBackRef.current = 'category-landing';
            setCurrentScreen('search-results');
          }}
          onViewAllNewArrivals={() => {
            setCatalogListType('new_arrivals');
            setCatalogListTitle(selectedCategory?.name ? `Nouveautés - ${selectedCategory.name}` : undefined);
            searchResultsBackRef.current = 'category-landing';
            setCurrentScreen('search-results');
          }}
          onSelectProduct={(p) => {
            selectProduct(p);
            setCurrentScreen('product-details');
          }}
          onToggleWishlist={handleToggleWishlist}
          wishlistedProductIds={wishlistedProductIds}
          onOpenSearch={() => setCurrentScreen('search-landing')}
          onOpenCart={() => setCurrentScreen('cart')}
          onOpenWishlist={() => setCurrentScreen('wishlist')}
          onOpenNotifications={() => setCurrentScreen('notification-settings-toggles')}
          onNavigateTab={navigateTab}
          activeTab={activeTab}
          cartCount={cart.lines.reduce((sum, line) => sum + line.quantity, 0)}
          notificationCount={unreadNotificationCount}
        />
      ) : null}
      {currentScreen === 'category-products' ? <CategoryProductListScreen activeTab={activeTab} category={selectedCategory || null} onBack={() => { const dest = categoryProductsBackRef.current; categoryProductsBackRef.current = 'category-landing'; setCurrentScreen(dest); }} onSelectProduct={selectProduct} onNavigateTab={navigateTab} onOpenSearch={() => setCurrentScreen('search-landing')} wishlistedProductIds={wishlistedProductIds} onToggleWishlist={handleToggleWishlist} /> : null}
      {currentScreen === 'collection-shop-the-look' ? <CollectionShopTheLookScreen onBack={() => setCurrentScreen('category-landing')} onSelectProduct={selectProduct} onAddAllToCart={() => setCurrentScreen('cart')} onOpenFilter={() => setCurrentScreen('filter-panel-modal')} /> : null}
      {currentScreen === 'collections-list' ? (
        <CollectionsListScreen
          onBack={() => setCurrentScreen(collectionsListBackRef.current)}
          onSelectCollection={(col) => {
            setCatalogListType('collections');
            setCatalogListTitle(col.name);
            setSearchQuery('');
            searchResultsBackRef.current = 'collections-list';
            setCurrentScreen('search-results');
          }}
          onOpenSearch={() => setCurrentScreen('search-landing')}
          onOpenCart={() => setCurrentScreen('cart')}
          onOpenWishlist={() => setCurrentScreen('wishlist')}
          onOpenNotifications={() => setCurrentScreen('notification-settings-toggles')}
          cartBadgeCount={cart.lines.reduce((sum, line) => sum + line.quantity, 0)}
          wishlistBadgeCount={wishlistedProductIds.length}
          notificationBadgeCount={unreadNotificationCount}
        />
      ) : null}
      {currentScreen === 'flash-deals' ? (
        <FlashDealsScreen
          onBack={() => setCurrentScreen('home')}
          onSelectProduct={selectProduct}
          onOpenProductDetails={(id) => { selectProduct({ id, name: 'Produit Flash', thumbnail_image: '', has_discount: false, discount: '', stroked_price: '', priceMad: 1000, formattedPrice: '1 000 MAD', main_price: '1 000 MAD', rating: 5, sales: 1, links: { details: '' } }); setCurrentScreen('product-details'); }}
          onOpenCart={() => setCurrentScreen('cart')}
          onOpenNotifications={() => setCurrentScreen('notification-settings-toggles')}
          cartCount={cart.lines.reduce((sum, line) => sum + line.quantity, 0)}
          notificationCount={unreadNotificationCount}
        />
      ) : null}
      {currentScreen === 'promotions-campaigns' ? <PromotionsCampaignsScreen onBack={() => setCurrentScreen('home')} onExploreDeals={() => setCurrentScreen('flash-deals')} /> : null}
      {currentScreen === 'recently-viewed' ? (
        <RecentlyViewedScreen
          onBack={() => setCurrentScreen('home')}
          onSelectProduct={selectProduct}
          onOpenSearch={() => setCurrentScreen('search-landing')}
          onOpenCart={() => setCurrentScreen('cart')}
          onOpenWishlist={() => setCurrentScreen('wishlist')}
          onOpenNotifications={() => setCurrentScreen('notification-settings-toggles')}
          cartCount={cart.lines.reduce((sum, line) => sum + line.quantity, 0)}
          notificationCount={unreadNotificationCount}
        />
      ) : null}

      {currentScreen === 'search-landing' ? <SearchLandingScreen onBack={() => setCurrentScreen('home')} onSearchSubmit={(q) => { setCatalogListType('search'); searchResultsBackRef.current = 'search-landing'; handleSearchSubmit(q); }} onSelectProduct={(p) => { selectProduct(p); setCurrentScreen('product-details'); }} onOpenCart={() => setCurrentScreen('cart')} onOpenNotifications={() => setCurrentScreen('notification-settings-toggles')} onBrowseCategories={() => setCurrentScreen('categories')} onSelectCategoryShortcut={(catSlug) => { setSelectedCategory({ id: 0, name: catSlug, slug: catSlug, banner: '', icon: '', number_of_children: 0, links: { products: '', sub_categories: '' } }); categoryProductsBackRef.current = 'search-landing'; setCurrentScreen('category-products'); }} cartCount={cart.lines.reduce((sum, line) => sum + line.quantity, 0)} notificationCount={unreadNotificationCount} /> : null}
      {currentScreen === 'search-results' ? <SearchResultsScreen searchQuery={searchQuery} listType={catalogListType} listTitle={catalogListTitle} onBack={() => setCurrentScreen(searchResultsBackRef.current)} onBrowseCategories={() => setCurrentScreen('categories')} onSelectProduct={(p) => { selectProduct(p); setCurrentScreen('product-details'); }} onOpenCart={() => setCurrentScreen('cart')} onOpenWishlist={() => setCurrentScreen('wishlist')} onOpenNotifications={() => setCurrentScreen('notification-settings-toggles')} onSearchAgain={(q) => { setCatalogListType('search'); handleSearchSubmit(q); }} wishlistedProductIds={wishlistedProductIds} onToggleWishlist={handleToggleWishlist} cartCount={cart.lines.reduce((sum, line) => sum + line.quantity, 0)} wishlistCount={wishlistedProductIds.length} notificationCount={unreadNotificationCount} /> : null}
      {currentScreen === 'search-no-results' ? <SearchNoResultsScreen searchQuery={searchQuery} onBack={() => setCurrentScreen(searchResultsBackRef.current)} onClearSearch={() => setCurrentScreen('search-landing')} onBrowseCategories={() => setCurrentScreen('categories')} onSearchAgain={(q) => { setCatalogListType('search'); handleSearchSubmit(q); }} onDiscoverNewArrivals={() => setCurrentScreen('categories')} onOpenCart={() => setCurrentScreen('cart')} onOpenWishlist={() => setCurrentScreen('wishlist')} onOpenNotifications={() => setCurrentScreen('notification-settings-toggles')} onSelectCategoryShortcut={(catSlug) => { setSelectedCategory({ id: 0, name: catSlug, slug: catSlug, banner: '', icon: '', number_of_children: 0, links: { products: '', sub_categories: '' } }); categoryProductsBackRef.current = 'search-landing'; setCurrentScreen('category-products'); }} cartCount={cart.lines.reduce((sum, line) => sum + line.quantity, 0)} notificationCount={unreadNotificationCount} /> : null}

      {currentScreen === 'cart' ? (
        <CartScreen
          cart={cart}
          onNavigateTab={navigateTab}
          onStartShopping={() => setCurrentScreen('home')}
          onViewWishlist={() => setCurrentScreen('wishlist')}
          onOpenNotifications={() => setCurrentScreen('notification-settings-toggles')}
          notificationCount={unreadNotificationCount}
          onViewAllSuggestions={() => {
            setCatalogListType('recommended');
            setCatalogListTitle(undefined);
            searchResultsBackRef.current = 'cart';
            setCurrentScreen('search-results');
          }}
          onSelectCategory={(catSlug) => {
            setSelectedCategory({
              id: 0,
              name: catSlug,
              slug: catSlug,
              banner: '',
              icon: '',
              number_of_children: 0,
              links: { products: '', sub_categories: '' },
            });
            setCurrentScreen('category-products');
          }}
          onSelectProduct={(pid) =>
            selectProduct({
              id: pid,
              name: 'Produit Mayush',
              thumbnail_image: '',
              has_discount: false,
              discount: '',
              stroked_price: '',
              priceMad: 1000,
              formattedPrice: '1 000 MAD',
              main_price: '1 000 MAD',
              rating: 5,
              sales: 1,
              links: { details: '' },
            })
          }
          onUpdateQuantity={updateCartQuantity}
          onUpdateVariant={updateCartVariant}
          onApplyPromotion={applyCartPromotion}
          onRemovePromotion={clearCartPromotion}
          onCheckout={startCheckout}
          onMergeCartLines={handleMergeCartLines}
        />
      ) : null}
      {currentScreen === 'wishlist' ? (
        <WishlistScreen
          onNavigateTab={navigateTab}
          onBrowseCollections={() => {
            collectionsListBackRef.current = 'wishlist';
            setCurrentScreen('collections-list');
          }}
          onSelectCategory={(catSlug) => {
            setSelectedCategory({
              id: 0,
              name: catSlug,
              slug: catSlug,
              banner: '',
              icon: '',
              number_of_children: 0,
              links: { products: '', sub_categories: '' },
            });
            categoryProductsBackRef.current = 'wishlist';
            setCurrentScreen('category-products');
          }}
          onSelectProduct={(p) => {
            selectProduct(p);
            setCurrentScreen('product-details');
          }}
          onOpenSearch={() => setCurrentScreen('search-landing')}
          onOpenNotifications={() => setCurrentScreen('notification-settings-toggles')}
          notificationCount={unreadNotificationCount}
        />
      ) : null}
      {currentScreen === 'account' ? (
        <AccountScreen
          onNavigateTab={navigateTab}
          onExplore={() => setCurrentScreen('home')}
          onLogin={() => setCurrentScreen('login')}
          onCreateAccount={() => setCurrentScreen('registration')}
          onNavigateSettings={() => setCurrentScreen('settings')}
          onNavigateMyInformation={() => setCurrentScreen('my-information')}
          onNavigateOrders={() => setCurrentScreen('orders-list')}
          onNavigateWishlist={() => setCurrentScreen('wishlist')}
          onNavigateAddresses={() => setCurrentScreen('my-addresses')}
          onNavigateSecurity={() => setCurrentScreen('account-security')}
          onNavigatePaymentMethods={() => setCurrentScreen('payment-methods')}
          onNavigateLanguageRegion={() => setCurrentScreen('language-region')}
          onNavigateLanguageSelection={() => setCurrentScreen('language-selection')}
          onNavigateMarketingPreferences={() => setCurrentScreen('marketing-cart-reminders')}
          onNavigateNotificationManagement={() => setCurrentScreen('notification-channels')}
          onNavigateHelpSupport={() => setCurrentScreen('help-support')}
          onConfirmLogoutTrigger={() => setLogoutModalVisible(true)}
        />
      ) : null}

      {currentScreen === 'account-security' ? (
        <AccountSecurityScreen
          onNavigateTab={navigateTab}
          onBack={() => setCurrentScreen('account')}
          onNavigateSecurityPrivacyMenu={() => setCurrentScreen('security-privacy')}
          onNavigateTwoFactor={() => setCurrentScreen('security-2fa')}
          onNavigateActiveSessions={() => setCurrentScreen('active-sessions')}
          onNavigateChangePassword={() => setCurrentScreen('change-password')}
        />
      ) : null}

      {currentScreen === 'security-privacy' ? (
        <SecurityPrivacyMenuScreen
          onNavigateTab={navigateTab}
          onBack={() => setCurrentScreen('account-security')}
          onNavigateChangePassword={() => setCurrentScreen('change-password')}
          onNavigateTwoFactor={() => setCurrentScreen('security-2fa')}
          onNavigateActiveSessions={() => setCurrentScreen('active-sessions')}
          onNavigateBiometricAppLock={() => setCurrentScreen('biometric-app-lock')}
        />
      ) : null}

      {currentScreen === 'security-2fa' ? (
        <TwoFactorAuthScreen
          onNavigateTab={navigateTab}
          onBack={() => setCurrentScreen('account-security')}
        />
      ) : null}

      {currentScreen === 'biometric-app-lock' ? (
        <BiometricAppLockScreen
          onNavigateTab={navigateTab}
          onBack={() => setCurrentScreen('security-privacy')}
        />
      ) : null}

      {currentScreen === 'active-sessions' ? (
        <ActiveSessionsScreen
          onNavigateTab={navigateTab}
          onBack={() => setCurrentScreen('account-security')}
        />
      ) : null}

      {currentScreen === 'my-addresses' ? (
        <MyAddressesListScreen
          onNavigateTab={navigateTab}
          onBack={() => setCurrentScreen('account')}
          onAddAddress={() => setCurrentScreen('account-add-address-simple')}
          onEditAddress={(address) => { authState.setSelectedAddressForEdit(address); setCurrentScreen('account-edit-address'); }}
          onNavigateV2={() => setCurrentScreen('my-addresses-v2')}
        />
      ) : null}

      {currentScreen === 'my-addresses-v2' ? (
        <MyAddressesListV2Screen
          onNavigateTab={navigateTab}
          onBack={() => setCurrentScreen('my-addresses')}
          onAddAddress={() => setCurrentScreen('account-add-address')}
          onEditAddress={(address) => { authState.setSelectedAddressForEdit(address); setCurrentScreen('account-edit-address'); }}
        />
      ) : null}

      {currentScreen === 'account-add-address' ? (
        <AccountAddAddressV2Screen
          onBack={() => setCurrentScreen('my-addresses-v2')}
          onSaved={() => setCurrentScreen('my-addresses-v2')}
        />
      ) : null}

      {currentScreen === 'account-add-address-simple' ? (
        <AccountAddAddressSimpleScreen
          onBack={() => setCurrentScreen('my-addresses')}
          onSaved={() => setCurrentScreen('my-addresses')}
          onEditAddress={(id) => setCurrentScreen('account-edit-address')}
        />
      ) : null}

      {currentScreen === 'account-edit-address' ? (
        <AccountEditAddressScreen
          onBack={() => setCurrentScreen('my-addresses')}
          onSaved={() => setCurrentScreen('my-addresses')}
          onDelete={() => setCurrentScreen('my-addresses')}
        />
      ) : null}

      {currentScreen === 'account-settings' ? (
        <AccountSettingsScreen
          onNavigateTab={navigateTab}
          onBack={() => setCurrentScreen('account')}
          onNavigateMyInformation={() => setCurrentScreen('my-information')}
          onNavigateEditProfile={() => setCurrentScreen('edit-profile')}
          onNavigateChangeEmail={() => setCurrentScreen('change-email')}
          onNavigateChangePhone={() => setCurrentScreen('change-phone')}
          onNavigateChangePassword={() => setCurrentScreen('change-password')}
          onNavigatePaymentMethods={() => setCurrentScreen('payment-methods')}
          onNavigateLanguageRegion={() => setCurrentScreen('language-region')}
          onNavigateMarketingPreferences={() => setCurrentScreen('marketing-cart-reminders')}
          onNavigateNotificationManagement={() => setCurrentScreen('notification-channels')}
          onLogout={() => setLogoutModalVisible(true)}
        />
      ) : null}

      {currentScreen === 'payment-methods' ? (
        <PaymentMethodsScreen
          onNavigateTab={navigateTab}
          onBack={() => setCurrentScreen('account-settings')}
          onContinueToLanguage={() => setCurrentScreen('language-region')}
        />
      ) : null}

      {currentScreen === 'language-region' ? (
        <LanguageRegionPreferencesScreen
          onNavigateTab={navigateTab}
          onBack={() => setCurrentScreen('payment-methods')}
          onNavigateLanguageSelection={() => setCurrentScreen('language-selection')}
        />
      ) : null}

      {currentScreen === 'language-selection' ? (
        <LanguageSelectionAccountScreen
          onBack={() => setCurrentScreen('language-region')}
          onLanguageApplied={(lang) => {
            setCurrentScreen('account');
          }}
        />
      ) : null}

      {currentScreen === 'marketing-cart-reminders' ? (
        <MarketingCartRemindersScreen
          onNavigateTab={navigateTab}
          onBack={() => setCurrentScreen('account')}
          onNavigateDetailedPreferences={() => setCurrentScreen('marketing-detailed-preferences')}
        />
      ) : null}

      {currentScreen === 'marketing-detailed-preferences' ? (
        <MarketingDetailedPreferencesScreen
          onNavigateTab={navigateTab}
          onBack={() => setCurrentScreen('marketing-cart-reminders')}
          onNavigateToggles={() => setCurrentScreen('marketing-toggles')}
        />
      ) : null}

      {currentScreen === 'marketing-toggles' ? (
        <MarketingTogglesScreen
          onNavigateTab={navigateTab}
          onBack={() => setCurrentScreen('marketing-detailed-preferences')}
          onNavigateNotificationManagement={() => setCurrentScreen('notification-channels')}
        />
      ) : null}

      {currentScreen === 'notification-channels' ? (
        <NotificationChannelsScreen
          onNavigateTab={navigateTab}
          onBack={() => setCurrentScreen('account')}
          onNavigateNotificationSettings={() => setCurrentScreen('notification-settings-toggles')}
        />
      ) : null}

      {currentScreen === 'notification-settings-toggles' ? (
        <NotificationSettingsTogglesScreen
          onNavigateTab={navigateTab}
          onBack={() => setCurrentScreen('notification-channels')}
          onNavigateNotificationDetails={() => setCurrentScreen('notification-detail-prep')}
        />
      ) : null}

      {currentScreen === 'notification-detail-prep' ? (
        <NotificationDetailPrepScreen
          onNavigateTab={navigateTab}
          onBack={() => setCurrentScreen('notification-settings-toggles')}
          onNavigateOrderDetails={openSelectedNotificationOrder}
          onNavigateShippedNotif={() => setCurrentScreen('notification-detail-shipped')}
        />
      ) : null}

      {currentScreen === 'notification-detail-shipped' ? (
        <NotificationDetailShippedScreen
          onNavigateTab={navigateTab}
          onBack={() => setCurrentScreen('notification-detail-prep')}
          onNavigateOrderDetails={openSelectedNotificationOrder}
          onNavigateSilentHours={() => setCurrentScreen('silent-hours-day-selection')}
        />
      ) : null}

      {currentScreen === 'silent-hours-day-selection' ? (
        <SilentHoursDaySelectionScreen
          onNavigateTab={navigateTab}
          onBack={() => setCurrentScreen('notification-detail-shipped')}
          onSaveSchedule={() => setCurrentScreen('silent-hours-dnd')}
        />
      ) : null}

      {currentScreen === 'silent-hours-dnd' ? (
        <SilentHoursDoNotDisturbScreen
          onNavigateTab={navigateTab}
          onBack={() => setCurrentScreen('silent-hours-day-selection')}
          onEditSchedule={() => setCurrentScreen('silent-hours-day-selection')}
          onNavigateFaq={() => setCurrentScreen('faq')}
        />
      ) : null}

      {currentScreen === 'settings' ? (
        <SettingsScreen
          onNavigateTab={navigateTab}
          onBack={() => setCurrentScreen('account')}
          onNavigateLanguage={() => setCurrentScreen('language-selection')}
          onNavigateTheme={() => setCurrentScreen('theme-appearance')}
          onNavigateNotificationChannels={() => setCurrentScreen('notification-channels')}
          onNavigateMarketingPreferences={() => setCurrentScreen('marketing-cart-reminders')}
          onNavigateSilentHours={() => setCurrentScreen('silent-hours-dnd')}
          onNavigateHelpCenter={() => setCurrentScreen('help-center-home')}
          onNavigateAboutApp={() => setCurrentScreen('about-app')}
          onNavigateAboutMayush={() => setCurrentScreen(resolveSettingsAboutDestination())}
          onNavigateAccessibility={() => setCurrentScreen('accessibility')}
          onNavigateAppPermissions={() => setCurrentScreen('app-permissions')}
          onNavigateDataUsage={() => setCurrentScreen('data-usage')}
          onNavigateStorageCache={() => setCurrentScreen('storage-cache')}
          onNavigateOfflineMode={() => setCurrentScreen('offline-mode')}
          onNavigateLegalPrivacy={() => setCurrentScreen('legal-center')}
        />
      ) : null}

      {currentScreen === 'theme-appearance' ? (
        <ThemeAppearanceScreen
          onBack={() => setCurrentScreen('settings')}
        />
      ) : null}

      {currentScreen === 'about-app' ? (
        <AboutAppVersionScreen
          onNavigateTab={navigateTab}
          onBack={() => setCurrentScreen('settings')}
          onNavigateAboutMayush={() => setCurrentScreen('about-mayush')}
        />
      ) : null}

      {currentScreen === 'about-mayush' ? (
        <AboutMayushCompanyScreen
          onNavigateTab={navigateTab}
          onBack={() => setCurrentScreen(resolveAboutMayushBackDestination())}
          onNavigateAccessibility={() => setCurrentScreen('accessibility')}
        />
      ) : null}

      {currentScreen === 'accessibility' ? (
        <AccessibilitySettingsScreen
          onNavigateTab={navigateTab}
          onBack={() => setCurrentScreen('settings')}
        />
      ) : null}

      {currentScreen === 'app-permissions' ? (
        <AppPermissionsScreen
          onNavigateTab={navigateTab}
          onBack={() => setCurrentScreen('settings')}
          onNavigateDataUsage={() => setCurrentScreen('data-usage')}
        />
      ) : null}

      {currentScreen === 'data-usage' ? (
        <DataUsageScreen
          onNavigateTab={navigateTab}
          onBack={() => setCurrentScreen('settings')}
          onNavigateStorageCache={() => setCurrentScreen('storage-cache')}
        />
      ) : null}

      {currentScreen === 'storage-cache' ? (
        <StorageCacheScreen
          onNavigateTab={navigateTab}
          onBack={() => setCurrentScreen('settings')}
          onOpenClearCacheModal={() => setIsClearCacheModalVisible(true)}
        />
      ) : null}

      {currentScreen === 'offline-mode' ? (
        <OfflineModeScreen
          onNavigateTab={navigateTab}
          onBack={() => setCurrentScreen('settings')}
        />
      ) : null}

      {currentScreen === 'legal-center' ? (
        <LegalCenterScreen
          onNavigateTab={navigateTab}
          onBack={() => setCurrentScreen('settings')}
          onNavigatePrivacyData={() => setCurrentScreen('privacy-data')}
          onNavigatePrivacyPolicy={() => setCurrentScreen('privacy-policy')}
        />
      ) : null}

      {currentScreen === 'privacy-data' ? (
        <PrivacyDataManagementScreen
          onNavigateTab={navigateTab}
          onBack={() => setCurrentScreen('legal-center')}
          onNavigatePrivacyPolicy={() => setCurrentScreen('privacy-policy')}
        />
      ) : null}

      {currentScreen === 'privacy-policy' ? (
        <PrivacyPolicyDocumentScreen
          onNavigateTab={navigateTab}
          onBack={() => setCurrentScreen('legal-center')}
          onNavigateHelpCenter={() => setCurrentScreen('help-center-home')}
        />
      ) : null}

      <ClearCacheConfirmationModal
        visible={isClearCacheModalVisible}
        onCancel={() => setIsClearCacheModalVisible(false)}
        onConfirm={() => setIsClearCacheModalVisible(false)}
      />

      {currentScreen === 'help-center-home' ? (
        <HelpCenterHomeScreen
          onNavigateTab={navigateTab}
          onBack={() => setCurrentScreen('settings')}
          onNavigateCategory={(catId) => {
            if (catId === 'commandes' || catId === 'livraison') {
              setCurrentScreen('help-category-orders-delivery');
            } else {
              setCurrentScreen('faq-tab-categories');
            }
          }}
          onNavigateSearch={(query) => setCurrentScreen('help-center-search-results')}
          onNavigateFaq={() => setCurrentScreen('faq-tab-categories')}
          onNavigateRequests={() => setCurrentScreen('my-support-tickets-list')}
          onNavigateContactSupport={() => setCurrentScreen('contact-support-form')}
        />
      ) : null}

      {currentScreen === 'help-category-orders-delivery' ? (
        <HelpCategoryOrdersDeliveryScreen
          onNavigateTab={navigateTab}
          onBack={() => setCurrentScreen('help-center-home')}
          onNavigateArticleTrackOrder={() => setCurrentScreen('faq-article-track-order-steps')}
          onNavigateOrdersList={() => setCurrentScreen('orders-list')}
          onNavigateReturnRefund={() => setCurrentScreen('help-center-home')}
          onNavigateReportDeliveryIssue={() => setCurrentScreen('contact-support-form')}
          onNavigateContactSupport={() => setCurrentScreen('contact-support-form')}
        />
      ) : null}

      {currentScreen === 'help-center-search-results' ? (
        <HelpCenterSearchResultsScreen
          onNavigateTab={navigateTab}
          onBack={() => setCurrentScreen('help-center-home')}
          onNavigateArticle={(articleId) => {
            if (articleId === 'faq-1') {
              setCurrentScreen('faq-article-track-order-steps');
            } else {
              setCurrentScreen('faq-tab-categories');
            }
          }}
          onNavigateCategory={(catId) => {
            if (catId === 'commandes' || catId === 'livraison') {
              setCurrentScreen('help-category-orders-delivery');
            } else {
              setCurrentScreen('faq-tab-categories');
            }
          }}
          onNavigateContactSupport={() => setCurrentScreen('contact-support-form')}
        />
      ) : null}

      {currentScreen === 'faq-tab-categories' ? (
        <FaqTabCategoriesScreen
          onNavigateTab={navigateTab}
          onBack={() => setCurrentScreen('help-center-home')}
          onNavigateArticle={(articleId) => {
            if (articleId === 'faq-1') {
              setCurrentScreen('faq-article-track-order-steps');
            } else {
              setCurrentScreen('faq-detail');
            }
          }}
          onNavigateContactSupport={() => setCurrentScreen('contact-support-form')}
        />
      ) : null}

      {currentScreen === 'faq-article-track-order-steps' ? (
        <FaqArticleTrackOrderStepsScreen
          onNavigateTab={navigateTab}
          onBack={() => setCurrentScreen('help-category-orders-delivery')}
          onNavigateOrdersList={() => setCurrentScreen('orders-list')}
          onNavigateRelatedArticle={(articleId) => {
            if (articleId === 'faq-1') {
              setCurrentScreen('faq-article-track-order-steps');
            } else {
              setCurrentScreen('faq-tab-categories');
            }
          }}
          onNavigateContactSupport={() => setCurrentScreen('contact-support-form')}
        />
      ) : null}

      {currentScreen === 'my-support-tickets-list' ? (
        <MySupportTicketsListScreen
          onNavigateTab={navigateTab}
          onBack={() => setCurrentScreen('help-center-home')}
          onSelectTicket={(ticketId) => setCurrentScreen('ticket-detail-conversation-thread')}
          onNavigateContactForm={() => setCurrentScreen('contact-support-form')}
          onNavigateEmptyState={() => setCurrentScreen('no-support-requests-empty-state')}
        />
      ) : null}

      {currentScreen === 'no-support-requests-empty-state' ? (
        <NoSupportRequestsEmptyStateScreen
          onNavigateTab={navigateTab}
          onBack={() => setCurrentScreen('help-center-home')}
          onNavigateFaq={() => setCurrentScreen('faq-tab-categories')}
          onNavigateContactSupport={() => setCurrentScreen('contact-support-form')}
        />
      ) : null}

      {currentScreen === 'contact-support-form' ? (
        <ContactSupportFormScreen
          onNavigateTab={navigateTab}
          onBack={() => setCurrentScreen('help-center-home')}
          onNavigateAttachFiles={() => setCurrentScreen('attach-files-documents')}
          onNavigateSelectOrder={() => setCurrentScreen('select-order-for-support')}
          onNavigateReview={() => setCurrentScreen('review-send-support-request')}
        />
      ) : null}

      {currentScreen === 'order-support-contact' && activeOrder ? (
        <ContactSupportFormScreen
          orderContext={activeOrder}
          onNavigateTab={navigateTab}
          onBack={openActiveOrderDetails}
          onNavigateAttachFiles={() => setCurrentScreen('attach-files-documents')}
          onNavigateSelectOrder={openOrdersList}
          onNavigateReview={() => setCurrentScreen('review-send-support-request')}
        />
      ) : null}

      {currentScreen === 'attach-files-documents' ? (
        <AttachFilesDocumentsScreen
          onNavigateTab={navigateTab}
          onBack={() => setCurrentScreen('contact-support-form')}
          onContinue={() => setCurrentScreen('contact-support-form')}
        />
      ) : null}

      {currentScreen === 'review-send-support-request' ? (
        <ReviewSendSupportRequestScreen
          onNavigateTab={navigateTab}
          onBack={() => setCurrentScreen('contact-support-form')}
          onEditForm={() => setCurrentScreen('contact-support-form')}
          onEditOrder={() => setCurrentScreen('select-order-for-support')}
          onEditAttachments={() => setCurrentScreen('attach-files-documents')}
          onSendSuccess={(ticketId) => setCurrentScreen('support-request-sent-success')}
        />
      ) : null}

      {currentScreen === 'select-order-for-support' ? (
        <SelectOrderForSupportScreen
          onNavigateTab={navigateTab}
          onBack={() => setCurrentScreen('contact-support-form')}
          onSelectOrder={(orderId) => setCurrentScreen('contact-support-form')}
        />
      ) : null}

      {currentScreen === 'reply-to-support-message' ? (
        <ReplyToSupportMessageScreen
          onNavigateTab={navigateTab}
          onBack={() => setCurrentScreen('ticket-detail-conversation-thread')}
          onSendReplySuccess={() => setCurrentScreen('ticket-detail-conversation-thread')}
        />
      ) : null}

      {currentScreen === 'ticket-detail-conversation-thread' ? (
        <TicketDetailConversationThreadScreen
          onNavigateTab={navigateTab}
          onBack={() => setCurrentScreen('my-support-tickets-list')}
          onNavigateReply={() => setCurrentScreen('reply-to-support-message')}
          onNavigateCloseRequest={() => setCurrentScreen('close-request-confirmation')}
          onNavigateOrdersList={() => setCurrentScreen('orders-list')}
          onNavigateRating={() => setCurrentScreen('ticket-resolved-rating')}
        />
      ) : null}

      {currentScreen === 'close-request-confirmation' ? (
        <CloseRequestConfirmationScreen
          onNavigateTab={navigateTab}
          onBack={() => setCurrentScreen('ticket-detail-conversation-thread')}
          onConfirmClose={() => setCurrentScreen('ticket-detail-conversation-thread')}
        />
      ) : null}

      {currentScreen === 'support-request-sent-success' ? (
        <SupportRequestSentSuccessScreen
          onNavigateTab={navigateTab}
          onBack={() => setCurrentScreen('help-center-home')}
          onViewTicket={(ticketId) => setCurrentScreen('ticket-detail-conversation-thread')}
          onReturnToHelpCenter={() => setCurrentScreen('help-center-home')}
          onNavigateRating={() => setCurrentScreen('ticket-resolved-rating')}
        />
      ) : null}

      {currentScreen === 'ticket-resolved-rating' ? (
        <TicketResolvedRatingScreen
          onNavigateTab={navigateTab}
          onBack={() => setCurrentScreen('my-support-tickets-list')}
          onNavigateFaqDetail={() => setCurrentScreen('faq-detail')}
          onNavigateTrackOrderFaq={() => setCurrentScreen('faq-article-track-order-steps')}
        />
      ) : null}

      {currentScreen === 'support-connection-error' ? (
        <SupportConnectionErrorScreen
          onNavigateTab={navigateTab}
          onBack={() => setCurrentScreen('help-center-home')}
          onRetry={() => setCurrentScreen('support-connection-error')}
          onContinueInApp={() => setCurrentScreen('home')}
        />
      ) : null}

      {currentScreen === 'support-temporarily-unavailable' ? (
        <SupportTemporarilyUnavailableScreen
          onNavigateTab={navigateTab}
          onBack={() => setCurrentScreen('help-center-home')}
          onRetry={() => setCurrentScreen('support-temporarily-unavailable')}
          onNavigateFaq={() => setCurrentScreen('help-center-home')}
        />
      ) : null}

      {currentScreen === 'maintenance-mode-services-impacted' ? (
        <MaintenanceModeServicesImpactedScreen
          onNavigateTab={navigateTab}
          onBack={() => setCurrentScreen('home')}
          onRetry={() => setCurrentScreen('maintenance-mode-services-impacted')}
          onContactSupport={() => setCurrentScreen('contact-support-form')}
        />
      ) : null}

      {currentScreen === 'app-update-available' ? (
        <AppUpdateAvailableScreen
          onNavigateTab={navigateTab}
          onBack={() => setCurrentScreen('settings')}
          onLater={() => setCurrentScreen('home')}
          onNavigateLegalCenter={() => setCurrentScreen('legal-center')}
          onNavigatePrivacyPolicy={() => setCurrentScreen('privacy-policy')}
        />
      ) : null}

      {currentScreen === 'forced-update-required' ? (
        <ForcedUpdateRequiredScreen />
      ) : null}

      {currentScreen === 'settings-error-loading-state' ? (
        <SettingsErrorLoadingStateScreen
          onRetry={() => setCurrentScreen('settings')}
          onGoHome={() => setCurrentScreen('home')}
        />
      ) : null}

      {currentScreen === 'settings-skeleton-loading-state' ? (
        <SettingsSkeletonLoadingStateScreen />
      ) : null}

      {currentScreen === 'faq' ? (
        <FaqAccordionScreen
          onNavigateTab={navigateTab}
          onBack={() => setCurrentScreen('help-support')}
          onNavigateFaqDetail={() => setCurrentScreen('faq-detail')}
          onNavigateFaqCategories={() => setCurrentScreen('faq-categories')}
        />
      ) : null}

      {currentScreen === 'faq-detail' ? (
        <FaqDetailScreen
          onNavigateTab={navigateTab}
          onBack={() => setCurrentScreen('faq')}
          onNavigateFaqCategories={() => setCurrentScreen('faq-categories')}
        />
      ) : null}

      {currentScreen === 'faq-categories' ? (
        <FaqCategoriesScreen
          onNavigateTab={navigateTab}
          onBack={() => setCurrentScreen('faq')}
          onNavigateHelpCenter={() => setCurrentScreen('help-center')}
          onNavigateFaqDetail={() => setCurrentScreen('faq-detail')}
        />
      ) : null}

      {currentScreen === 'help-center' ? (
        <HelpCenterCategoriesScreen
          onNavigateTab={navigateTab}
          onBack={() => setCurrentScreen('help-support')}
          onNavigateFaq={() => setCurrentScreen('faq')}
          onNavigateRecentRequests={() => setCurrentScreen('help-center-requests')}
          onNavigateOrders={() => setCurrentScreen('orders-list')}
          onNavigateAccountSettings={() => setCurrentScreen('account-settings')}
        />
      ) : null}

      {currentScreen === 'help-center-requests' ? (
        <HelpCenterRequestsScreen
          onNavigateTab={navigateTab}
          onBack={() => setCurrentScreen('help-center')}
          onNavigateSupportHub={() => setCurrentScreen('help-support')}
        />
      ) : null}

      {currentScreen === 'help-support' ? (
        <HelpSupportHubScreen
          onNavigateTab={navigateTab}
          onBack={() => setCurrentScreen('account')}
          onNavigateFaq={() => setCurrentScreen('faq')}
          onNavigateHelpCenter={() => setCurrentScreen('help-center')}
          onNavigateRecentRequests={() => setCurrentScreen('help-center-requests')}
        />
      ) : null}

      {currentScreen === 'my-information' ? (
        <MyInformationScreen
          onNavigateTab={navigateTab}
          onBack={() => setCurrentScreen('account-settings')}
          onEditProfile={() => setCurrentScreen('edit-profile')}
          onChangeEmail={() => setCurrentScreen('change-email')}
          onChangePhone={() => setCurrentScreen('change-phone')}
        />
      ) : null}

      {currentScreen === 'edit-profile' ? (
        <EditProfileScreen
          onNavigateTab={navigateTab}
          onBack={() => setCurrentScreen('my-information')}
          onSaveSuccess={() => setCurrentScreen('my-information')}
        />
      ) : null}

      {currentScreen === 'change-email' ? (
        <ChangeEmailScreen
          onNavigateTab={navigateTab}
          onBack={() => setCurrentScreen('account-settings')}
          onSuccess={() => setCurrentScreen('my-information')}
          onChangePassword={() => setCurrentScreen('change-password')}
        />
      ) : null}

      {currentScreen === 'change-phone' ? (
        <ChangePhoneScreen
          onNavigateTab={navigateTab}
          onBack={() => setCurrentScreen('account-settings')}
          onContinueToOtp={() => setCurrentScreen('account-verify-phone')}
        />
      ) : null}

      {currentScreen === 'account-verify-phone' ? (
        <AccountVerifyPhoneOtpScreen
          onNavigateTab={navigateTab}
          onBack={() => setCurrentScreen('change-phone')}
          onSuccess={() => setCurrentScreen('my-information')}
        />
      ) : null}

      {currentScreen === 'change-password' ? (
        <ChangePasswordFormScreen
          onNavigateTab={navigateTab}
          onBack={() => setCurrentScreen('account-settings')}
          onSuccess={() => setCurrentScreen('password-changed-success')}
        />
      ) : null}

      {currentScreen === 'product-details' ? (
        <ProductDetailsScreen
          activeTab={activeTab}
          productId={selectedProduct?.id || 101}
          initialProduct={selectedProduct}
          onBack={() => setCurrentScreen('home')}
          onOpenGallery={(imgs, name) => { setGalleryImages(imgs); setGalleryProductName(name); setCurrentScreen('product-gallery'); }}
          onOpenVariantSheet={(product) => {
            setVariantProduct(product);
            setVariantSheetVisible(true);
          }}
          onOpenDescription={(prod) => {
            if (prod) setVariantProduct(prod);
            setCurrentScreen('product-description');
          }}
          onOpenSpecifications={(prod) => {
            if (prod) setVariantProduct(prod);
            setCurrentScreen('product-specifications');
          }}
          onOpenDeliveryReturns={(prod) => {
            if (prod) setVariantProduct(prod);
            setCurrentScreen('product-delivery-returns');
          }}
          onOpenReviews={(prod) => {
            if (prod) setVariantProduct(prod);
            setCurrentScreen('product-reviews');
          }}
          onSelectProduct={(item) => {
            setSelectedProduct(item);
            setCurrentScreen('product-details');
          }}
          onNavigateTab={navigateTab}
        />
      ) : null}
      {currentScreen === 'product-gallery' ? <ProductGalleryScreen activeTab={activeTab} onBack={() => setCurrentScreen('product-details')} onNavigateTab={navigateTab} images={galleryImages.length > 0 ? galleryImages : variantProduct?.photos || (selectedProduct?.thumbnail_image ? [selectedProduct.thumbnail_image] : undefined)} productName={galleryProductName || variantProduct?.name || selectedProduct?.name} /> : null}
      {currentScreen === 'inspiration-detail' ? (
        <InspirationDetailScreen
          activeTab={activeTab}
          onBack={() => setCurrentScreen('home')}
          onNavigateTab={navigateTab}
          slug={inspirationSlug as string}
          onSelectProduct={(product) => {
            setSelectedProduct(product);
            setCurrentScreen('product-details');
          }}
        />
      ) : null}
      {currentScreen === 'product-description' ? <ProductFullDescriptionScreen productTitle={variantProduct?.name || selectedProduct?.name || 'Mayush Collection'} description={variantProduct?.description} onBack={() => setCurrentScreen('product-details')} /> : null}
      {currentScreen === 'product-specifications' ? <ProductSpecificationsScreen product={variantProduct || selectedProduct} productTitle={variantProduct?.name || selectedProduct?.name || 'Mayush Collection'} customSpecs={variantProduct?.choice_options && variantProduct.choice_options.length > 0 ? variantProduct.choice_options.map((opt) => ({ label: opt.title, value: opt.options.join(', ') })) : (selectedProduct?.choice_options && selectedProduct.choice_options.length > 0 ? selectedProduct.choice_options.map((opt) => ({ label: opt.title, value: opt.options.join(', ') })) : undefined)} onBack={() => setCurrentScreen('product-details')} /> : null}
      {currentScreen === 'product-delivery-returns' ? <ProductDeliveryReturnsScreen product={variantProduct || selectedProduct} onBack={() => setCurrentScreen('product-details')} /> : null}
      {currentScreen === 'product-reviews' ? <ProductReviewsRatingsScreen productId={variantProduct?.id || selectedProduct?.id} productTitle={variantProduct?.name || selectedProduct?.name || 'Fauteuil Lounge Luna'} onBack={() => setCurrentScreen('product-details')} /> : null}
      {currentScreen === 'added-to-cart' ? <AddedToCartConfirmationScreen cart={cart} onViewCart={() => setCurrentScreen('cart')} onContinueShopping={() => setCurrentScreen('product-details')} /> : null}

      {currentScreen === 'checkout-summary' && selectedAddress ? (
        <CheckoutSummaryScreen
          cart={cart}
          address={selectedAddress}
          deliveryMethod={deliveryMethod}
          paymentMethod={paymentMethod}
          deliveryFeeMad={deliveryProjection.deliveryFeeMad}
          onBack={() => setCurrentScreen('cart')}
          onChooseAddress={() => setCurrentScreen(savedAddresses.length ? 'address-selection' : 'no-saved-address')}
          onChooseDeliveryMethod={() => setCurrentScreen('delivery-method')}
          onChoosePaymentMethod={() => setCurrentScreen('payment-method')}
          onContinue={() => setCurrentScreen('delivery-method')}
        />
      ) : null}
      {currentScreen === 'checkout-summary' && !selectedAddress ? <NoSavedAddressScreen onBack={() => setCurrentScreen('cart')} onAddAddress={openAddCheckoutAddress} /> : null}
      {currentScreen === 'address-selection' ? (
        <AddressSelectionScreen
          addresses={savedAddresses}
          selectedAddressId={selectedAddressId}
          onBack={() => setCurrentScreen('cart')}
          onSelect={setSelectedAddressId}
          onContinue={() => setCurrentScreen(savedAddresses.length ? 'delivery-method' : 'no-saved-address')}
          onAddAddress={openAddCheckoutAddress}
          onEdit={(addressId) => {
            setSelectedAddressId(addressId);
            const address = savedAddresses.find((item) => item.id === addressId);
            if (!address) return;
            setAddressEditorMode('edit');
            setEditingAddressId(address.id);
            setAddressDraft(addressToDraft(address));
            setCurrentScreen('edit-checkout-address');
          }}
        />
      ) : null}
      {currentScreen === 'add-address' || currentScreen === 'add-address-errors' ? <AddAddressFormScreen draft={addressDraft} errors={currentScreen === 'add-address-errors' ? validateAddressDraft(addressDraft) : {}} onChange={(next) => { setAddressDraft(next); if (currentScreen === 'add-address-errors') setCurrentScreen('add-address'); }} onBack={() => setCurrentScreen(savedAddresses.length ? 'address-selection' : 'no-saved-address')} onSave={saveAddress} onChooseCity={() => { setAddressEditorMode('add'); setCurrentScreen('city-selector'); }} onChooseZone={() => setCurrentScreen(addressDraft.cityId ? 'delivery-zone-selector' : 'city-selector')} /> : null}
      {currentScreen === 'city-selector' ? <CitySelectorScreen selectedCityId={addressDraft.cityId} onBack={() => setCurrentScreen(addressEditorMode === 'edit' ? 'edit-checkout-address' : (savedAddresses.length ? 'address-selection' : 'no-saved-address'))} onSelect={(city) => { setAddressDraft((draft) => setAddressDraftCity(draft, city.cityId)); setCurrentScreen('delivery-zone-selector'); }} /> : null}
      {currentScreen === 'delivery-zone-selector' && getCityById(addressDraft.cityId) ? <DeliveryZoneSelectorScreen city={getCityById(addressDraft.cityId)!} selectedZoneId={addressDraft.zoneId} onBack={() => setCurrentScreen('city-selector')} onSelect={(zone) => setAddressDraft((draft) => setAddressDraftZone(draft, zone.zoneId))} onContinue={() => setCurrentScreen(addressEditorMode === 'edit' ? 'edit-checkout-address' : 'add-address')} /> : null}
      {currentScreen === 'edit-checkout-address' ? <EditCheckoutAddressScreen draft={addressDraft} errors={validateAddressDraft(addressDraft)} onChange={setAddressDraft} onBack={() => setCurrentScreen('address-selection')} onChooseCity={() => setCurrentScreen('city-selector')} onChooseZone={() => setCurrentScreen(addressDraft.cityId ? 'delivery-zone-selector' : 'city-selector')} onSave={saveEditedAddress} onDelete={() => { if (!editingAddressId) return; authState.deleteAddress(editingAddressId); const remaining = authState.getSavedAddresses(); setSelectedAddressId(remaining.find((address) => address.isDefault)?.id || remaining[0]?.id || ''); setEditingAddressId(null); setCurrentScreen(remaining.length ? 'address-selection' : 'no-saved-address'); }} /> : null}
      {currentScreen === 'no-saved-address' ? <NoSavedAddressScreen onBack={() => setCurrentScreen('cart')} onAddAddress={openAddCheckoutAddress} /> : null}
      {currentScreen === 'delivery-method' && selectedAddress ? (
        <DeliveryMethodScreen
          address={selectedAddress}
          selectedMethod={deliveryMethod}
          onBack={() => setCurrentScreen(savedAddresses.length ? 'address-selection' : 'cart')}
          onSelect={setDeliveryMethod}
          onChangeAddress={() => setCurrentScreen(savedAddresses.length ? 'address-selection' : 'no-saved-address')}
          onContinue={() => {
            const next = buildSellerDeliveryProjection(cart.lines, selectedAddress, deliveryMethod);
            setCurrentScreen(!next.available ? 'delivery-unavailable' : next.groups.length > 1 ? 'delivery-by-vendor' : 'payment-method');
          }}
        />
      ) : null}
      {currentScreen === 'delivery-by-vendor' ? (
        <DeliveryByVendorScreen
          projection={deliveryProjection}
          onBack={() => setCurrentScreen('delivery-method')}
          onContinue={() => setCurrentScreen('payment-method')}
        />
      ) : null}
      {currentScreen === 'delivery-unavailable' && selectedAddress ? (
        <DeliveryUnavailableScreen
          address={selectedAddress}
          lines={cart.lines}
          onBack={() => setCurrentScreen('delivery-method')}
          onEditAddress={openEditCheckoutAddress}
          onRemoveAffected={() => {
            cartStateManager.reset();
            setCurrentScreen('cart');
          }}
          onSupport={() => setCurrentScreen('contact-support-form')}
        />
      ) : null}
      {currentScreen === 'payment-method' ? (
        <PaymentMethodScreen
          totalMad={checkoutTotalMad}
          selectedMethod={paymentMethod}
          processing={paymentProcessing}
          onBack={() => setCurrentScreen(deliveryProjection.groups.length > 1 ? 'delivery-by-vendor' : 'delivery-method')}
          onSelect={setPaymentMethod}
          onContinue={continueFromPaymentMethod}
        />
      ) : null}
      {currentScreen === 'wallet-balance' ? <WalletBalanceScreen balanceMad={accountPreferencesState.getWalletBalanceMad()} totalMad={checkoutTotalMad} onBack={() => setCurrentScreen('payment-method')} onUseWallet={() => { accountPreferencesState.setSelectedPaymentMethod('pm-wallet'); completeCheckout(); }} /> : null}
      {currentScreen === 'saved-payment-cards' ? <SavedPaymentCardsScreen methods={paymentPreferences} selectedId={selectedPaymentPreferenceId} onBack={() => setCurrentScreen('payment-method')} onSelect={(id) => accountPreferencesState.setSelectedPaymentMethod(id)} onDelete={(id) => accountPreferencesState.removePaymentMethod(id)} onAdd={() => undefined} onContinue={() => { setPaymentMethod('cmi'); completeCheckout(); }} /> : null}
      {currentScreen === 'auth-gate' || currentScreen === 'auth-welcome' ? (
        <AuthenticationWelcomeScreen
          onSignIn={() => setCurrentScreen('login')}
          onCreateAccount={() => setCurrentScreen('registration')}
          onContinueAsGuest={() => {
            if (paymentMethod === 'wallet') setPaymentMethod('cmi');
            const dest = authState.getReturnDestination();
            authState.clearReturnDestination();
            if (dest && dest.route) {
              setCurrentScreen(dest.route as ScreenKey);
            } else {
              setCurrentScreen('home');
            }
          }}
          onTermsClick={() => setCurrentScreen('terms-consent')}
        />
      ) : null}
      {currentScreen === 'login' ? (
        <LoginScreen
          onLoginSubmit={async (emailOrPhone, pass) => {
            setCurrentScreen('login-loading');
            await authState.completeLogin(emailOrPhone, pass);
            if (authState.getStatus() === 'authenticated') {
              resumeAuthReturnDestination();
            } else if (authState.getStatus() === 'otp-sent') {
              setCurrentScreen('otp-verification');
            } else {
              setCurrentScreen('login-error');
            }
          }}
          onForgotPassword={() => setCurrentScreen('forgot-password')}
          onCreateAccount={() => setCurrentScreen('registration')}
          onBack={() => setCurrentScreen('auth-welcome')}
        />
      ) : null}
      {currentScreen === 'login-error' ? (
        <LoginErrorScreen
          errorMessage={authState.getLoginError() || undefined}
          onRetry={() => setCurrentScreen('login')}
          onForgotPassword={() => setCurrentScreen('forgot-password')}
          onBack={() => setCurrentScreen('login')}
          onSupport={() => setCurrentScreen('contact-support-form')}
        />
      ) : null}
      {currentScreen === 'login-loading' ? (
        <LoginLoadingScreen
          autoProgress={false}
          onComplete={() => resumeAuthReturnDestination()}
        />
      ) : null}
      {currentScreen === 'registration' ? (
        <RegistrationScreen
          onNextToConsent={() => setCurrentScreen('terms-consent')}
          onSignInClick={() => setCurrentScreen('login')}
          onBack={() => setCurrentScreen('auth-welcome')}
        />
      ) : null}
      {currentScreen === 'terms-consent' ? (
        <TermsConsentScreen
          onAccept={async () => {
            setCurrentScreen('login-loading'); // Use loading screen
            await authState.completeRegistration();
            if (authState.getStatus() === 'otp-sent' || authState.getStatus() === 'registration-success') {
              setCurrentScreen('otp-verification');
            } else {
              // Registration failed
              alert(authState.getLoginError() || 'Erreur lors de l\'inscription');
              setCurrentScreen('registration');
            }
          }}
          onDecline={() => setCurrentScreen('registration')}
        />
      ) : null}
      {currentScreen === 'account-created' ? (
        <AccountCreatedSuccessScreen
          onContinue={() => {
            authState.setReturnDestination({ route: 'home' });
            setCurrentScreen('login');
          }}
          onCompleteProfile={() => {
            authState.setReturnDestination({ route: 'edit-profile' });
            setCurrentScreen('login');
          }}
        />
      ) : null}
      {currentScreen === 'forgot-password' ? (
        <ForgotPasswordScreen
          onBack={() => setCurrentScreen('login')}
          onSubmitSuccess={() => setCurrentScreen('recovery-email-sent')}
        />
      ) : null}
      {currentScreen === 'recovery-email-sent' ? (
        <EmailVerificationSentScreen
          onBack={() => setCurrentScreen('login')}
          onContinueToNewPassword={() => setCurrentScreen('create-new-password')}
        />
      ) : null}
      {currentScreen === 'otp-verification' ? (
        <PhoneOtpVerificationScreen
          onBack={() => setCurrentScreen('registration')}
          onHome={() => setCurrentScreen('home')}
          onSuccess={() => setCurrentScreen('account-created')}
          onError={() => setCurrentScreen('otp-error')}
        />
      ) : null}
      {currentScreen === 'account-created' ? (
        <AccountCreatedSuccessScreen
          onContinue={() => setCurrentScreen('home')}
          onCompleteProfile={() => setCurrentScreen('edit-profile')}
        />
      ) : null}
      {currentScreen === 'otp-error' ? (
        <OtpErrorScreen
          onBack={() => setCurrentScreen('otp-verification')}
          onRetry={() => setCurrentScreen('otp-verification')}
          onRequestNewPassword={() => setCurrentScreen('create-new-password')}
        />
      ) : null}
      {currentScreen === 'create-new-password' ? (
        <CreateNewPasswordScreen
          onBack={() => setCurrentScreen('login')}
          onSuccess={() => setCurrentScreen('password-changed-success')}
        />
      ) : null}
      {currentScreen === 'password-changed-success' ? (
        <PasswordChangedSuccessScreen
          onBackToLogin={() => setCurrentScreen('login')}
        />
      ) : null}

      {currentScreen === 'order-review' && selectedAddress ? (
        <OrderReviewScreen
          cart={cart}
          address={selectedAddress}
          deliveryMethod={deliveryMethod}
          paymentMethod={paymentMethod}
          deliveryFeeMad={deliveryProjection.deliveryFeeMad}
          onBack={() => setCurrentScreen('payment-method')}
          onConfirm={() => void beginOrderReview()}
          onChangeAddress={() => setCurrentScreen(savedAddresses.length ? 'address-selection' : 'no-saved-address')}
          onChangeDelivery={() => setCurrentScreen('delivery-method')}
          onChangePayment={() => setCurrentScreen('payment-method')}
          onTermsClick={() => setCurrentScreen('checkout-terms-confirmation')}
        />
      ) : null}
      {currentScreen === 'checkout-terms-confirmation' ? <CheckoutTermsConfirmationScreen onBack={() => setCurrentScreen('order-review')} onAccept={acceptCheckoutTermsAndContinue} onTerms={() => setCurrentScreen('legal-center')} onPrivacy={() => setCurrentScreen('privacy-policy')} /> : null}
      {currentScreen === 'order-already-in-progress' && activeOrder ? <OrderAlreadyInProgressScreen order={activeOrder} onBack={() => setCurrentScreen('order-review')} onOrder={openActiveOrderDetails} onStatus={() => setCurrentScreen(activeOrder.paymentStatus === 'prototype_pending_confirmation' ? 'payment-pending' : getCanonicalOrderDetailRoute(activeOrder))} onSupport={openOrderSupport} /> : null}
      {currentScreen === 'order-needs-update' ? <OrderNeedsUpdateScreen onBack={() => setCurrentScreen('order-review')} onAccept={acceptCheckoutConflictChanges} onCart={() => setCurrentScreen('cart')} /> : null}
      {currentScreen === 'checkout-skeleton' ? <CheckoutSkeletonScreen /> : null}
      {currentScreen === 'checkout-error' ? <CheckoutErrorScreen onBack={() => setCurrentScreen('cart')} onRetry={retryCheckoutLoad} onCart={() => setCurrentScreen('cart')} /> : null}
      {currentScreen === 'order-processing' && activeOrder ? <OrderProcessingScreen order={activeOrder} onFinish={finishOrderProcessing} /> : null}
      {currentScreen === 'payment-step-intro' && activeOrder ? <PaymentStepIntroScreen order={activeOrder} onBack={() => setCurrentScreen('payment-method')} onContinue={() => setCurrentScreen('secure-payment-redirect')} /> : null}
      {currentScreen === 'secure-payment-redirect' && activeOrder ? <SecurePaymentRedirectScreen order={activeOrder} onContinue={() => setCurrentScreen('secure-payment-loading')} onCancel={() => { void cancelActivePayment(); }} /> : null}
      {currentScreen === 'secure-payment-loading' && activeOrder ? <SecurePaymentLoadingScreen order={activeOrder} onContinue={() => setCurrentScreen('payment-verification')} /> : null}
      {currentScreen === 'payment-verification' && activeOrder ? <PaymentVerificationScreen order={activeOrder} onContinue={() => { void verifyActivePayment(); }} /> : null}
      {currentScreen === 'payment-confirmation-delayed' && activeOrder ? <PaymentConfirmationTakingLongerScreen order={activeOrder} onCheckAgain={() => setCurrentScreen('payment-pending')} onOrder={openActiveOrderDetails} onSupport={openOrderSupport} /> : null}
      {currentScreen === 'payment-pending' && activeOrder ? <PaymentPendingConfirmationScreen order={activeOrder} onRefresh={() => setCurrentScreen('payment-pending')} onOrder={openOrdersList} onSupport={openOrderSupport} /> : null}
      {currentScreen === 'cash-on-delivery-confirmation' && activeOrder ? <CashOnDeliveryConfirmationScreen order={activeOrder} onContinue={finalizeSuccessfulCheckout} /> : null}
      {currentScreen === 'payment-failed' && activeOrder ? <PaymentFailureScreen order={activeOrder} onRetry={() => { void recoverActivePayment('retry'); }} onChangePayment={() => { void recoverActivePayment('change_method'); }} /> : null}
      {currentScreen === 'payment-cancelled' && activeOrder ? <PaymentCancelledScreen order={activeOrder} onContinue={() => { void recoverActivePayment('change_method'); }} /> : null}
      {currentScreen === 'payment-success' && activeOrder ? <PaymentSuccessScreen order={activeOrder} onNext={() => setCurrentScreen('order-thank-you')} onContinueShopping={() => setCurrentScreen('home')} /> : null}
      {currentScreen === 'order-thank-you' && activeOrder ? <OrderThankYouScreen order={activeOrder} onTrack={openOrdersList} onContinueShopping={() => setCurrentScreen('home')} /> : null}
      {currentScreen === 'orders-list' ? <OrdersListScreen orders={orders} onOpenOrder={(orderId) => { void openOrderById(orderId); }} onNavigateTab={navigateTab} /> : null}
      {currentScreen === 'order-detail-preparing' && activeOrder ? <OrderDetailsScreen order={activeOrder} variant="preparing" onBack={openOrdersList} onTrack={() => openTrackingForActiveOrder()} onOpenPackages={() => setCurrentScreen('order-packages')} onOpenInvoice={() => setCurrentScreen('order-invoice')} onSupport={openOrderSupport} onCancel={openCancellationForActiveOrder} onReorder={openReorderForActiveOrder} onRate={openReviewForActiveOrder} onReturn={openReturnForActiveOrder} /> : null}
      {currentScreen === 'order-detail-shipped' && activeOrder ? <OrderDetailsScreen order={activeOrder} variant="shipped" onBack={openOrdersList} onTrack={() => openTrackingForActiveOrder()} onOpenPackages={() => setCurrentScreen('order-packages')} onOpenInvoice={() => setCurrentScreen('order-invoice')} onSupport={openOrderSupport} onCancel={openCancellationForActiveOrder} onReorder={openReorderForActiveOrder} onRate={openReviewForActiveOrder} onReturn={openReturnForActiveOrder} /> : null}
      {currentScreen === 'order-detail-delivered' && activeOrder ? <OrderDetailsScreen order={activeOrder} variant="delivered" onBack={() => setCurrentScreen('orders-list')} onTrack={() => setCurrentScreen('order-tracking')} onOpenPackages={() => setCurrentScreen('order-packages')} onOpenInvoice={() => setCurrentScreen('order-invoice')} onSupport={openSupportForActiveOrder} onCancel={openCancellationForActiveOrder} onReorder={openReorderForActiveOrder} onRate={openReviewForActiveOrder} onReturn={openReturnForActiveOrder} /> : null}
      {currentScreen === 'order-detail-multi-vendor' && activeOrder ? <OrderDetailsScreen order={activeOrder} variant="multi-vendor" onBack={() => setCurrentScreen('orders-list')} onTrack={() => setCurrentScreen('order-tracking')} onOpenPackages={() => setCurrentScreen('order-packages')} onOpenInvoice={() => setCurrentScreen('order-invoice')} onSupport={openSupportForActiveOrder} onCancel={openCancellationForActiveOrder} onReorder={openReorderForActiveOrder} onRate={openReviewForActiveOrder} onReturn={openReturnForActiveOrder} /> : null}
      {currentScreen === 'order-tracking' && activeOrder ? <OrderTrackingScreen order={activeOrder} onBack={openActiveOrderDetails} onOpenDetails={openActiveOrderDetails} onSupport={openSupportForActiveOrder} /> : null}
      {currentScreen === 'order-packages' && activeOrder ? <OrderPackagesScreen order={activeOrder} onBack={openActiveOrderDetails} onOpenPackage={(packageId) => { if (orderState.selectPackage(packageId)) setCurrentScreen('order-package-detail'); }} /> : null}
      {currentScreen === 'order-package-detail' && activeOrder && activePackage ? <OrderPackageDetailsScreen order={activeOrder} orderPackage={activePackage} onBack={() => setCurrentScreen('order-packages')} onTrack={() => openTrackingForActiveOrder(activePackage.packageId)} onOpenInvoice={() => setCurrentScreen('order-invoice')} onSupport={openOrderSupport} /> : null}
      {currentScreen === 'order-invoice' && activeOrder ? <OrderInvoiceScreen order={activeOrder} onBack={openActiveOrderDetails} onOpenOrder={openActiveOrderDetails} /> : null}
      {currentScreen === 'order-cancel-confirmation' && activeOrder ? <OrderCancellationConfirmationScreen order={activeOrder} onBack={openActiveOrderDetails} onContinue={continueCancellationForActiveOrder} /> : null}
      {currentScreen === 'order-cancel-reason' && activeOrder && cancellationDraft?.orderId === activeOrder.orderId ? <OrderCancellationReasonScreen order={activeOrder} draft={cancellationDraft} onBack={() => setCurrentScreen('order-cancel-confirmation')} onReasonChange={(reason) => orderActionState.setCancellationReason(reason)} onMessageChange={(message) => orderActionState.setCancellationMessage(message)} onSubmit={submitCancellationForActiveOrder} /> : null}
      {currentScreen === 'order-cancel-registered' && activeOrder && cancellationRequest ? <OrderCancellationRegisteredScreen order={activeOrder} request={cancellationRequest} onBack={openActiveOrderDetails} onOpenOrders={() => setCurrentScreen('orders-list')} onContinueShopping={() => setCurrentScreen('home')} /> : null}
      {currentScreen === 'order-cannot-cancel' && activeOrder ? <OrderCannotBeCancelledScreen order={activeOrder} onBack={openActiveOrderDetails} onSupport={openSupportForActiveOrder} /> : null}
      {currentScreen === 'order-product-review' && activeOrder && reviewDraft?.orderId === activeOrder.orderId ? <OrderProductReviewScreen order={activeOrder} entries={reviewDraft.entries} onBack={openActiveOrderDetails} onRate={(lineId, rating) => { orderActionState.setReviewRating(lineId, rating); }} onSubmit={submitReviewForActiveOrder} onLater={openActiveOrderDetails} /> : null}
      {currentScreen === 'order-reorder-changes' && activeOrder && reorderPlan?.orderId === activeOrder.orderId ? <OrderReorderChangesScreen order={activeOrder} plan={reorderPlan} onBack={openActiveOrderDetails} onSelect={(lineId, selected) => { orderActionState.setReorderLineSelected(lineId, selected); }} onOpenSelection={() => setCurrentScreen('order-reorder-availability')} onAddSelected={commitReorderToCart} /> : null}
      {currentScreen === 'order-reorder-availability' && activeOrder && reorderPlan?.orderId === activeOrder.orderId ? <OrderReorderAvailabilityScreen order={activeOrder} plan={reorderPlan} onBack={() => setCurrentScreen('order-reorder-changes')} onSelect={(lineId, selected) => { orderActionState.setReorderLineSelected(lineId, selected); }} onAddSelected={commitReorderToCart} onOpenCart={() => setCurrentScreen('cart')} /> : null}
      {currentScreen === 'order-reorder-added' && activeOrder && lastReorderResult ? <OrderReorderAddedScreen order={activeOrder} result={lastReorderResult} onBack={openActiveOrderDetails} onOpenCart={() => setCurrentScreen('cart')} onContinueShopping={() => setCurrentScreen('home')} /> : null}
      {currentScreen === 'order-return-selection' && activeOrder && returnDraft?.orderId === activeOrder.orderId ? <OrderReturnSelectionScreen order={activeOrder} draft={returnDraft} onBack={openActiveOrderDetails} onSelect={(lineId, selected) => { orderActionState.setReturnLineSelected(activeOrder, lineId, selected); }} onQuantity={(lineId, quantity) => { orderActionState.setReturnLineQuantity(activeOrder, lineId, quantity); }} onReason={(reason) => orderActionState.setReturnReason(reason)} onMessage={(message) => orderActionState.setReturnMessage(message)} onSubmit={submitReturnForActiveOrder} /> : null}
      {currentScreen === 'order-return-detail' && activeOrder && activeReturnRequest?.orderId === activeOrder.orderId ? <OrderReturnDetailScreen order={activeOrder} request={activeReturnRequest} onBack={openActiveOrderDetails} onTrack={() => setCurrentScreen('order-return-tracking')} onSupport={() => openSupportForActiveOrder(activeReturnRequest.returnRequestId)} /> : null}
      {currentScreen === 'order-return-tracking' && activeOrder && activeReturnRequest?.orderId === activeOrder.orderId ? <OrderReturnTrackingScreen order={activeOrder} request={activeReturnRequest} onBack={() => setCurrentScreen('order-return-detail')} onDetails={() => setCurrentScreen('order-return-detail')} onSupport={() => openSupportForActiveOrder(activeReturnRequest.returnRequestId)} /> : null}
      {currentScreen === 'order-refund-request' && activeOrder && cancelledRefundDraft?.orderId === activeOrder.orderId ? <OrderCancelledRefundRequestScreen order={activeOrder} draft={cancelledRefundDraft} onBack={() => setCurrentScreen('orders-list')} onConfirm={confirmCancelledRefund} /> : null}
      {currentScreen === 'order-refund-completed' && activeOrder && activeRefund?.orderId === activeOrder.orderId && activeRefund.status === 'completed' ? <OrderRefundCompletedScreen order={activeOrder} refund={activeRefund} returnRequest={activeRefund.returnRequestId ? activeReturnRequest : null} onOrders={() => setCurrentScreen('orders-list')} onShop={() => setCurrentScreen('home')} /> : null}
      {currentScreen === 'delivery-delayed' && activeOrder && activeDeliveryIssue?.type === 'delayed' ? <DeliveryDelayedScreen order={activeOrder} issue={activeDeliveryIssue} onBack={openActiveOrderDetails} onTrack={() => setCurrentScreen('order-tracking')} onSupport={openOrderSupport} /> : null}
      {currentScreen === 'delivery-failed' && activeOrder && activeDeliveryIssue?.type === 'delivery_failed' ? <DeliveryFailedScreen order={activeOrder} issue={activeDeliveryIssue} request={activeRescheduleRequest} onBack={openActiveOrderDetails} onReschedule={submitDeliveryReschedule} onSupport={openOrderSupport} /> : null}
      {currentScreen === 'tracking-unavailable' && activeOrder ? <TrackingUnavailableScreen order={activeOrder} onBack={openActiveOrderDetails} onRefresh={() => openTrackingForActiveOrder()} onDetails={openActiveOrderDetails} /> : null}
      {currentScreen === 'order-not-found' ? <OrderNotFoundScreen orderId={orderViewSnapshot.requestedOrderId} onOrders={openOrdersList} onSupport={() => setCurrentScreen('contact-support-form')} /> : null}
      {currentScreen === 'orders-empty' ? <OrdersEmptyScreen onDiscover={() => setCurrentScreen('home')} onFavorites={() => setCurrentScreen('wishlist')} /> : null}
      {currentScreen === 'orders-error' ? <OrdersErrorScreen onRetry={openOrdersList} onAccount={() => setCurrentScreen('account')} /> : null}
      {currentScreen === 'orders-skeleton' ? <OrdersSkeletonScreen /> : null}
      {currentScreen === 'order-detail-skeleton' ? <OrderDetailSkeletonScreen onBack={openOrdersList} /> : null}

      <VariantSelectorSheet visible={variantSheetVisible} product={variantProduct} onClose={() => setVariantSheetVisible(false)} onConfirmAddToCart={addSelectedVariantToCart} />
      <FilterPanelModal visible={filterModalVisible} onClose={() => setFilterModalVisible(false)} onApplyFilters={() => setFilterModalVisible(false)} />
      <FavoritesAuthPromptOverlay
        visible={favoritesPromptVisible}
        onClose={() => setFavoritesPromptVisible(false)}
        onSignIn={() => setCurrentScreen('login')}
        onCreateAccount={() => setCurrentScreen('registration')}
        favoriteItemId={favoritesPromptItemId}
        promptType={authPromptType}
      />
      <LogoutConfirmationModal
        visible={logoutModalVisible || currentScreen === 'logout-confirmation'}
        onCancel={() => {
          setLogoutModalVisible(false);
          if (currentScreen === 'logout-confirmation') setCurrentScreen('account');
        }}
        onConfirmLogout={() => {
          authState.logout();
          setLogoutModalVisible(false);
          setCurrentScreen('home');
        }}
      />

      {SCREENS_WITH_TABBAR.has(currentScreen) ? (
        <BottomTabBar
          activeTab={activeTab}
          onTabPress={navigateTab}
          cartBadgeCount={isAuthenticated ? cart.lines.reduce((sum, line) => sum + line.quantity, 0) : 0}
        />
      ) : null}
    </View>
  );
};

export const RootNavigator: React.FC = () => {
  const [hasCompletedOnboarding, setHasCompletedOnboarding] = useState<boolean | null>(null);
  const [initialLanguage, setInitialLanguage] = useState<MvpAppLanguage>('fr');

  useEffect(() => {
    let isMounted = true;

    void Promise.all([
      AsyncStorage.getItem(ONBOARDING_COMPLETE_KEY),
      AsyncStorage.getItem(LANGUAGE_KEY),
    ]).then(([onboardingValue, storedLanguage]) => {
      if (!isMounted) return;
      setHasCompletedOnboarding(onboardingValue === 'true');
      if (storedLanguage === 'fr' || storedLanguage === 'ar') {
        setInitialLanguage(storedLanguage);
      }
    }).catch(() => {
      if (isMounted) setHasCompletedOnboarding(false);
    });

    return () => { isMounted = false; };
  }, []);

  const rememberLanguage = (language: MvpAppLanguage) => {
    setInitialLanguage(language);
    void AsyncStorage.setItem(LANGUAGE_KEY, language).catch(() => undefined);
  };

  const completeOnboarding = () => {
    setHasCompletedOnboarding(true);
    void AsyncStorage.setItem(ONBOARDING_COMPLETE_KEY, 'true').catch(() => undefined);
  };

  return (
    <ThemeProvider initialLanguage={initialLanguage}>
      <SystemStatusGate>
        <RootNavigatorContent
          hasCompletedOnboarding={hasCompletedOnboarding}
          onLanguageSelected={rememberLanguage}
          onOnboardingCompleted={completeOnboarding}
        />
      </SystemStatusGate>
    </ThemeProvider>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1 },
});
