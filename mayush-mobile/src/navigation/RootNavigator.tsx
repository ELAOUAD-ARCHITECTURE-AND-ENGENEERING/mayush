import AsyncStorage from '@react-native-async-storage/async-storage';
import React, { useEffect, useRef, useState } from 'react';
import { StyleSheet, View } from 'react-native';
import { AddressDraft, clearCheckoutSession, createLocalCheckoutAttemptId, createSavedAddress, defaultSavedAddresses, DeliveryMethod, emptyAddressDraft, isResumableCheckoutScreen, loadCheckoutSession, PaymentMethod, ResumableCheckoutScreen, saveCheckoutSession, validateAddressDraft } from '../commerce/checkoutState';
import { CART_STORAGE_KEY, CartLine, CartState, addCartLine, createSelectedVariantCartLine, emptyCartState, parseMadPrice, updateCartLineQuantity } from '../commerce/cartState';
import { orderState } from '../commerce/orderState';
import { CategoryDto, MvpAppLanguage, ProductDetailDto, ProductMiniDto } from '../contracts/api/dto';
import { TabKey } from '../design-system/components/navigation/BottomTabBar';
import { ThemeProvider } from '../design-system/theme/ThemeProvider';
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
import { FilterPanelModal } from '../screens/discovery/FilterPanelModal';
import { HomeScreen } from '../screens/discovery/HomeScreen';
import { RecentlyViewedScreen } from '../screens/discovery/RecentlyViewedScreen';

import { LanguageSelectionScreen } from '../screens/entry/LanguageSelectionScreen';
import { OnboardingScreen } from '../screens/entry/OnboardingScreen';
import { PreparingExperienceScreen } from '../screens/entry/PreparingExperienceScreen';
import { SplashScreen } from '../screens/entry/SplashScreen';

import { OrderDetailsScreen } from '../screens/orders/OrderDetailsScreen';
import { OrdersListScreen } from '../screens/orders/OrdersListScreen';
import { OrderThankYouScreen } from '../screens/orders/OrderThankYouScreen';

import { ProductDeliveryReturnsScreen } from '../screens/product/ProductDeliveryReturnsScreen';
import { ProductDetailsScreen } from '../screens/product/ProductDetailsScreen';
import { ProductFullDescriptionScreen } from '../screens/product/ProductFullDescriptionScreen';
import { ProductGalleryScreen } from '../screens/product/ProductGalleryScreen';
import { ProductReviewsRatingsScreen } from '../screens/product/ProductReviewsRatingsScreen';
import { ProductSpecificationsScreen } from '../screens/product/ProductSpecificationsScreen';
import { VariantSelectorSheet } from '../screens/product/VariantSelectorSheet';

import { FlashDealsScreen } from '../screens/promotions/FlashDealsScreen';
import { PromotionsCampaignsScreen } from '../screens/promotions/PromotionsCampaignsScreen';

import { SearchLandingScreen } from '../screens/search/SearchLandingScreen';
import { SearchNoResultsScreen } from '../screens/search/SearchNoResultsScreen';
import { SearchResultsScreen } from '../screens/search/SearchResultsScreen';

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
  | 'category-products'
  | 'category-landing'
  | 'collection-shop-the-look'
  | 'flash-deals'
  | 'promotions-campaigns'
  | 'recently-viewed'
  | 'search-landing'
  | 'search-results'
  | 'search-no-results'
  | 'product-details'
  | 'product-gallery'
  | 'product-description'
  | 'product-specifications'
  | 'product-delivery-returns'
  | 'product-reviews'
  | 'added-to-cart'
  | 'checkout-summary'
  | 'address-selection'
  | 'add-address'
  | 'add-address-errors'
  | 'delivery-method'
  | 'payment-method'
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
  | 'order-processing'
  | 'payment-step-intro'
  | 'secure-payment-redirect'
  | 'secure-payment-loading'
  | 'payment-verification'
  | 'cash-on-delivery-confirmation'
  | 'payment-failed'
  | 'payment-cancelled'
  | 'payment-success'
  | 'order-thank-you'
  | 'orders-list'
  | 'order-details';

const ONBOARDING_COMPLETE_KEY = 'mayush-mobile:onboarding-complete';
const LANGUAGE_KEY = 'mayush-mobile:language';

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
  const [selectedCategory, setSelectedCategory] = useState<CategoryDto>();
  const [selectedProduct, setSelectedProduct] = useState<ProductMiniDto>();
  const [variantProduct, setVariantProduct] = useState<ProductDetailDto | null>(null);
  const [variantSheetVisible, setVariantSheetVisible] = useState(false);
  const [filterModalVisible, setFilterModalVisible] = useState(false);
  const [logoutModalVisible, setLogoutModalVisible] = useState(false);
  const [searchQuery, setSearchQuery] = useState('Fauteuil');
  const [cart, setCart] = useState<CartState>(emptyCartState);
  const [savedAddresses, setSavedAddresses] = useState(defaultSavedAddresses);
  const [selectedAddressId, setSelectedAddressId] = useState(defaultSavedAddresses[0].id);
  const [addressDraft, setAddressDraft] = useState<AddressDraft>(emptyAddressDraft);
  const [deliveryMethod, setDeliveryMethod] = useState<DeliveryMethod>('standard');
  const [paymentMethod, setPaymentMethod] = useState<PaymentMethod>('cmi');
  const [paymentProcessing, setPaymentProcessing] = useState(false);
  const [checkoutAttemptId, setCheckoutAttemptId] = useState(createLocalCheckoutAttemptId);
  const [restoredCheckoutScreen, setRestoredCheckoutScreen] = useState<ResumableCheckoutScreen | null>(null);
  const [checkoutSessionResolved, setCheckoutSessionResolved] = useState(false);
  const [orderRepositoryResolved, setOrderRepositoryResolved] = useState(false);
  const [, setDomainRevision] = useState(0);
  const [favoritesPromptVisible, setFavoritesPromptVisible] = useState(false);
  const [favoritesPromptItemId, setFavoritesPromptItemId] = useState<string | undefined>();
  const [isClearCacheModalVisible, setIsClearCacheModalVisible] = useState(false);
  const paymentLock = useRef(false);
  const isAuthenticated = authState.isAuthenticated();
  const orders = orderState.getOrders();
  const activeOrder = orderState.getSelectedOrder();

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

  const selectCategory = (category: CategoryDto) => { setSelectedCategory(category); setCurrentScreen('category-products'); };
  const selectProduct = (product: ProductMiniDto) => { setSelectedProduct(product); setCurrentScreen('product-details'); };
  const activeTab: TabKey = currentScreen === 'categories' || currentScreen === 'category-products' || currentScreen === 'category-landing'
    ? 'categories'
    : currentScreen === 'wishlist'
      ? 'wishlist'
      : currentScreen === 'cart'
        ? 'cart'
        : currentScreen === 'account'
          ? 'account'
          : 'home';

  useEffect(() => {
    if (!splashFinished || hasCompletedOnboarding === null || !checkoutSessionResolved || !orderRepositoryResolved) return;
    setCurrentScreen(hasCompletedOnboarding ? (restoredCheckoutScreen || 'home') : 'language');
  }, [hasCompletedOnboarding, splashFinished, checkoutSessionResolved, orderRepositoryResolved, restoredCheckoutScreen]);

  useEffect(() => {
    const refresh = () => setDomainRevision((revision) => revision + 1);
    const unsubscribeAuth = authState.subscribe(refresh);
    const unsubscribeOrders = orderState.subscribe(refresh);
    void orderState.hydrate().finally(() => setOrderRepositoryResolved(true));
    return () => { unsubscribeAuth(); unsubscribeOrders(); };
  }, []);

  useEffect(() => {
    let isMounted = true;
    void AsyncStorage.getItem(CART_STORAGE_KEY).then((storedCart) => {
      if (!isMounted || !storedCart) return;
      try {
        const parsed = JSON.parse(storedCart) as CartState;
        if (Array.isArray(parsed.lines)) setCart(parsed);
      } catch {
        setCart(emptyCartState());
      }
    });
    return () => { isMounted = false; };
  }, []);

  useEffect(() => {
    let isMounted = true;
    void loadCheckoutSession(AsyncStorage).then((parsedSession) => {
      if (!isMounted) return;
      if (parsedSession) {
        setRestoredCheckoutScreen(parsedSession.screen);
        setCheckoutAttemptId(parsedSession.checkoutAttemptId);
        setSelectedAddressId(parsedSession.selectedAddressId);
        setDeliveryMethod(parsedSession.deliveryMethod);
        setPaymentMethod(parsedSession.paymentMethod);
        setSavedAddresses(parsedSession.savedAddresses);
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
        savedAddresses,
      }).catch(() => undefined);
    }
  }, [checkoutAttemptId, checkoutSessionResolved, currentScreen, deliveryMethod, paymentMethod, savedAddresses, selectedAddressId]);


  const updateCartQuantity = (lineId: string, delta: number) => {
    setCart((prev) => {
      const line = prev.lines.find((item) => item.id === lineId);
      if (!line) return prev;
      const nextQuantity = line.quantity + delta;
      const nextCart = updateCartLineQuantity(prev, lineId, nextQuantity);
      void AsyncStorage.setItem(CART_STORAGE_KEY, JSON.stringify(nextCart)).catch(() => undefined);
      return nextCart;
    });
  };

  const addSelectedVariantToCart = (variant: string, quantity: number) => {
    if (!variantProduct) return;
    const unitPriceMad = parseMadPrice(variantProduct.main_price || '2950 MAD');
    const primaryImage = variantProduct.photos?.[0] || 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?q=80&w=350&auto=format&fit=crop';
    setCart((prevCart) => {
      const lineName = variantProduct.name || 'Produit Mayush';
      const nextCart = addCartLine(prevCart, createSelectedVariantCartLine({
        productId: variantProduct.id,
        name: lineName,
        variant,
        quantity,
        unitPriceMad,
        imageUri: primaryImage,
      }));
      void AsyncStorage.setItem(CART_STORAGE_KEY, JSON.stringify(nextCart)).catch(() => undefined);
      return nextCart;
    });
    setVariantSheetVisible(false);
    setCurrentScreen('added-to-cart');
  };

  const moveWishlistItemToCart = (product: ProductMiniDto) => {
    const unitPriceMad = product.priceMad || parseMadPrice(product.main_price || '1500 MAD');
    setCart((prevCart) => {
      const imgSrc = product.thumbnail_image || 'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?q=80&w=350&auto=format&fit=crop';
      const nextCart = addCartLine(prevCart, {
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
      void AsyncStorage.setItem(CART_STORAGE_KEY, JSON.stringify(nextCart)).catch(() => undefined);
      return nextCart;
    });
    setCurrentScreen('added-to-cart');
  };

  const saveAddress = () => {
    const errors = validateAddressDraft(addressDraft);
    if (Object.keys(errors).length > 0) {
      setCurrentScreen('add-address-errors');
      return;
    }
    const newId = `address-${Date.now()}`;
    const newAddress = createSavedAddress(addressDraft, newId);
    setSavedAddresses((prev) => [newAddress, ...prev]);
    setSelectedAddressId(newId);
    setCurrentScreen('address-selection');
  };

  const selectedAddress = savedAddresses.find((item) => item.id === selectedAddressId) || savedAddresses[0];

  const navigateTab = (tab: TabKey) => {
    if (tab === 'home') setCurrentScreen('home');
    if (tab === 'categories') setCurrentScreen('categories');
    if (tab === 'wishlist') setCurrentScreen('wishlist');
    if (tab === 'cart') setCurrentScreen('cart');
    if (tab === 'account') setCurrentScreen('account');
  };

  const startCheckout = () => {
    const attemptId = createLocalCheckoutAttemptId();
    setCheckoutAttemptId(attemptId);
    setRestoredCheckoutScreen(null);
    setCurrentScreen('checkout-summary');
  };

  const finishOrderProcessing = () => {
    paymentLock.current = false;
    setPaymentProcessing(false);
    setCart(emptyCartState());
    void AsyncStorage.removeItem(CART_STORAGE_KEY).catch(() => undefined);
    void clearCheckoutSession(AsyncStorage).catch(() => undefined);
    setCheckoutAttemptId(createLocalCheckoutAttemptId());
    setRestoredCheckoutScreen(null);
    setCurrentScreen('payment-success');
  };

  const completeCheckout = () => {
    if (paymentMethod === 'wallet' && !isAuthenticated) {
      authState.setReturnDestination(createCheckoutAuthReturnDestination(checkoutAttemptId));
      setCurrentScreen('auth-gate');
      return;
    }
    setCurrentScreen('order-review');
  };

  const openSelectedNotificationOrder = () => {
    const orderId = notificationPreferencesState.getSelectedNotification()?.orderId;
    if (!orderId) {
      setCurrentScreen('orders-list');
      return;
    }
    void orderState.selectOrder(orderId).then((selected) => {
      setCurrentScreen(selected ? 'order-details' : 'orders-list');
    });
  };

  const beginOrderReview = async () => {
    if (paymentLock.current) return;
    paymentLock.current = true;
    setPaymentProcessing(true);
    await orderState.createOrder({
      cart,
      address: selectedAddress,
      deliveryMethod,
      paymentMethod,
      checkoutAttemptId,
    });
    setCurrentScreen('order-processing');
  };

  const handleSearchSubmit = (query: string) => {
    setSearchQuery(query);
    if (query.toLowerCase().includes('xyz') || query.toLowerCase().includes('000')) {
      setCurrentScreen('search-no-results');
    } else {
      setCurrentScreen('search-results');
    }
  };

  const updateCartVariantText = (lineId: string, newVariantText: string) => {
    setCart((prev) => {
      const nextLines = prev.lines.map((l) =>
        l.id === lineId ? { ...l, variant: newVariantText, selectedVariantText: newVariantText } : l
      );
      const nextCart = { ...prev, lines: nextLines };
      void AsyncStorage.setItem(CART_STORAGE_KEY, JSON.stringify(nextCart)).catch(() => undefined);
      return nextCart;
    });
  };

  const handleMergeCartLines = (mergedLines: CartLine[]) => {
    const nextCart = { lines: mergedLines };
    setCart(nextCart);
    void AsyncStorage.setItem(CART_STORAGE_KEY, JSON.stringify(nextCart)).catch(() => undefined);
  };

  return (
    <View style={styles.container}>
      {currentScreen === 'splash' ? <SplashScreen onFinish={() => setSplashFinished(true)} /> : null}
      {currentScreen === 'language' ? <LanguageSelectionScreen onContinue={(language) => { onLanguageSelected(language); setCurrentScreen('preparing'); }} /> : null}
      {currentScreen === 'preparing' ? <PreparingExperienceScreen onFinish={() => setCurrentScreen('onboarding-1')} /> : null}
      {currentScreen === 'onboarding-1' ? <OnboardingScreen step={1} onNext={() => setCurrentScreen('onboarding-2')} onSkip={onOnboardingCompleted} /> : null}
      {currentScreen === 'onboarding-2' ? <OnboardingScreen step={2} onNext={() => setCurrentScreen('onboarding-3')} onSkip={onOnboardingCompleted} /> : null}
      {currentScreen === 'onboarding-3' ? <OnboardingScreen step={3} onNext={onOnboardingCompleted} onSkip={onOnboardingCompleted} /> : null}

      {currentScreen === 'home' ? <HomeScreen activeTab={activeTab} onSelectCategory={selectCategory} onSelectProduct={selectProduct} onNavigateTab={navigateTab} /> : null}
      {currentScreen === 'categories' ? <CategoriesScreen activeTab={activeTab} onSelectCategory={(cat) => { setSelectedCategory(cat); setCurrentScreen('category-landing'); }} onNavigateTab={navigateTab} /> : null}
      {currentScreen === 'category-landing' ? <CategoryLandingScreen category={selectedCategory} onBack={() => setCurrentScreen('categories')} onSelectSubcategory={() => setCurrentScreen('category-products')} onOpenCollection={() => setCurrentScreen('collection-shop-the-look')} onSelectProduct={selectProduct} onOpenSearch={() => setCurrentScreen('search-landing')} /> : null}
      {currentScreen === 'category-products' ? <CategoryProductListScreen activeTab={activeTab} category={selectedCategory} onBack={() => setCurrentScreen('category-landing')} onSelectProduct={selectProduct} onNavigateTab={navigateTab} /> : null}
      {currentScreen === 'collection-shop-the-look' ? <CollectionShopTheLookScreen onBack={() => setCurrentScreen('category-landing')} onSelectProduct={selectProduct} onAddAllToCart={() => setCurrentScreen('cart')} /> : null}
      {currentScreen === 'flash-deals' ? <FlashDealsScreen onBack={() => setCurrentScreen('home')} onSelectProduct={selectProduct} /> : null}
      {currentScreen === 'promotions-campaigns' ? <PromotionsCampaignsScreen onBack={() => setCurrentScreen('home')} onExploreDeals={() => setCurrentScreen('flash-deals')} /> : null}
      {currentScreen === 'recently-viewed' ? <RecentlyViewedScreen onBack={() => setCurrentScreen('home')} onSelectProduct={selectProduct} /> : null}

      {currentScreen === 'search-landing' ? <SearchLandingScreen onBack={() => setCurrentScreen('home')} onSearchSubmit={handleSearchSubmit} onSelectCategoryShortcut={() => setCurrentScreen('category-landing')} /> : null}
      {currentScreen === 'search-results' ? <SearchResultsScreen searchQuery={searchQuery} onBack={() => setCurrentScreen('search-landing')} onOpenFilter={() => setFilterModalVisible(true)} onSelectProduct={selectProduct} onToggleWishlist={(pid) => { if (!isAuthenticated) { setFavoritesPromptItemId(String(pid)); setFavoritesPromptVisible(true); } }} /> : null}
      {currentScreen === 'search-no-results' ? <SearchNoResultsScreen searchQuery={searchQuery} onBack={() => setCurrentScreen('search-landing')} onClearSearch={() => setCurrentScreen('search-landing')} onBrowseCategories={() => setCurrentScreen('categories')} /> : null}

      {currentScreen === 'wishlist' ? <WishlistScreen onNavigateTab={navigateTab} onBrowseCollections={() => setCurrentScreen('categories')} onSelectProduct={selectProduct} onMoveToCart={moveWishlistItemToCart} /> : null}
      {currentScreen === 'cart' ? <CartScreen cart={cart} onNavigateTab={navigateTab} onStartShopping={() => setCurrentScreen('home')} onViewWishlist={() => setCurrentScreen('wishlist')} onSelectProduct={(pid) => selectProduct({ id: pid, name: 'Produit Mayush', thumbnail_image: '', has_discount: false, discount: '', stroked_price: '', priceMad: 1000, formattedPrice: '1 000 MAD', main_price: '1 000 MAD', rating: 5, sales: 1, links: { details: '' } })} onUpdateQuantity={updateCartQuantity} onUpdateVariantText={updateCartVariantText} onCheckout={startCheckout} onMergeCartLines={handleMergeCartLines} /> : null}
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
        />
      ) : null}

      {currentScreen === 'security-2fa' ? (
        <TwoFactorAuthScreen
          onNavigateTab={navigateTab}
          onBack={() => setCurrentScreen('account-security')}
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
        />
      ) : null}

      {currentScreen === 'settings' ? (
        <SettingsScreen
          onNavigateTab={navigateTab}
          onBack={() => setCurrentScreen('account')}
          onNavigateLanguage={() => setCurrentScreen('language-selection')}
          onNavigateNotificationChannels={() => setCurrentScreen('notification-channels')}
          onNavigateMarketingPreferences={() => setCurrentScreen('marketing-cart-reminders')}
          onNavigateSilentHours={() => setCurrentScreen('silent-hours-dnd')}
          onNavigateHelpCenter={() => setCurrentScreen('help-center-home')}
          onNavigateAboutApp={() => setCurrentScreen('about-app')}
          onNavigateAccessibility={() => setCurrentScreen('accessibility')}
          onNavigateAppPermissions={() => setCurrentScreen('app-permissions')}
          onNavigateDataUsage={() => setCurrentScreen('data-usage')}
          onNavigateStorageCache={() => setCurrentScreen('storage-cache')}
          onNavigateOfflineMode={() => setCurrentScreen('offline-mode')}
          onNavigateLegalPrivacy={() => setCurrentScreen('legal-center')}
        />
      ) : null}

      {currentScreen === 'about-app' ? (
        <AboutAppVersionScreen
          onNavigateTab={navigateTab}
          onBack={() => setCurrentScreen('settings')}
        />
      ) : null}

      {currentScreen === 'about-mayush' ? (
        <AboutMayushCompanyScreen
          onNavigateTab={navigateTab}
          onBack={() => setCurrentScreen('settings')}
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
        />
      ) : null}

      {currentScreen === 'data-usage' ? (
        <DataUsageScreen
          onNavigateTab={navigateTab}
          onBack={() => setCurrentScreen('settings')}
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
          onNavigateConnectionError={() => setCurrentScreen('support-connection-error')}
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
          onNavigateTemporarilyUnavailable={() => setCurrentScreen('support-temporarily-unavailable')}
        />
      ) : null}

      {currentScreen === 'support-temporarily-unavailable' ? (
        <SupportTemporarilyUnavailableScreen
          onNavigateTab={navigateTab}
          onBack={() => setCurrentScreen('help-center-home')}
          onRetry={() => setCurrentScreen('support-temporarily-unavailable')}
          onNavigateFaq={() => setCurrentScreen('help-center-home')}
          onNavigateMaintenanceMode={() => setCurrentScreen('maintenance-mode-services-impacted')}
        />
      ) : null}

      {currentScreen === 'maintenance-mode-services-impacted' ? (
        <MaintenanceModeServicesImpactedScreen
          onNavigateTab={navigateTab}
          onBack={() => setCurrentScreen('home')}
          onRetry={() => setCurrentScreen('maintenance-mode-services-impacted')}
          onContactSupport={() => setCurrentScreen('contact-support-form')}
          onNavigateAppUpdate={() => setCurrentScreen('app-update-available')}
        />
      ) : null}

      {currentScreen === 'app-update-available' ? (
        <AppUpdateAvailableScreen
          onNavigateTab={navigateTab}
          onBack={() => setCurrentScreen('settings')}
          onUpdateNow={() => {}}
          onLater={() => setCurrentScreen('home')}
          onNavigateLegalCenter={() => setCurrentScreen('legal-center')}
          onNavigatePrivacyPolicy={() => setCurrentScreen('privacy-policy')}
          onNavigateForcedUpdate={() => setCurrentScreen('forced-update-required')}
        />
      ) : null}

      {currentScreen === 'forced-update-required' ? (
        <ForcedUpdateRequiredScreen
          onUpdateNow={() => {}}
          onNavigatePrototypeNext={() => setCurrentScreen('settings-error-loading-state')}
        />
      ) : null}

      {currentScreen === 'settings-error-loading-state' ? (
        <SettingsErrorLoadingStateScreen
          onRetry={() => setCurrentScreen('settings-skeleton-loading-state')}
          onGoHome={() => setCurrentScreen('home')}
          onNavigatePrototypeNext={() => setCurrentScreen('settings-skeleton-loading-state')}
        />
      ) : null}

      {currentScreen === 'settings-skeleton-loading-state' ? (
        <SettingsSkeletonLoadingStateScreen
          onSimulateComplete={() => setCurrentScreen('settings')}
        />
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

      {currentScreen === 'product-details' ? <ProductDetailsScreen activeTab={activeTab} productId={selectedProduct?.id || 101} initialProduct={selectedProduct} onBack={() => setCurrentScreen('home')} onOpenGallery={() => setCurrentScreen('product-gallery')} onOpenVariantSheet={(product) => { setVariantProduct(product); setVariantSheetVisible(true); }} onOpenDescription={() => setCurrentScreen('product-description')} onOpenSpecifications={() => setCurrentScreen('product-specifications')} onOpenDeliveryReturns={() => setCurrentScreen('product-delivery-returns')} onOpenReviews={() => setCurrentScreen('product-reviews')} onNavigateTab={navigateTab} /> : null}
      {currentScreen === 'product-gallery' ? <ProductGalleryScreen activeTab={activeTab} onBack={() => setCurrentScreen('product-details')} onNavigateTab={navigateTab} /> : null}
      {currentScreen === 'product-description' ? <ProductFullDescriptionScreen productTitle={selectedProduct?.name || 'Fauteuil Lounge Luna'} onBack={() => setCurrentScreen('product-details')} /> : null}
      {currentScreen === 'product-specifications' ? <ProductSpecificationsScreen productTitle={selectedProduct?.name || 'Fauteuil Lounge Luna'} onBack={() => setCurrentScreen('product-details')} /> : null}
      {currentScreen === 'product-delivery-returns' ? <ProductDeliveryReturnsScreen onBack={() => setCurrentScreen('product-details')} /> : null}
      {currentScreen === 'product-reviews' ? <ProductReviewsRatingsScreen productTitle={selectedProduct?.name || 'Fauteuil Lounge Luna'} onBack={() => setCurrentScreen('product-details')} /> : null}
      {currentScreen === 'added-to-cart' ? <AddedToCartConfirmationScreen cart={cart} onViewCart={() => setCurrentScreen('cart')} /> : null}

      {currentScreen === 'checkout-summary' ? <CheckoutSummaryScreen cart={cart} address={selectedAddress} deliveryMethod={deliveryMethod} paymentMethod={paymentMethod} onBack={() => setCurrentScreen('cart')} onChooseAddress={() => setCurrentScreen('address-selection')} /> : null}
      {currentScreen === 'address-selection' ? <AddressSelectionScreen addresses={savedAddresses} selectedAddressId={selectedAddressId} onBack={() => setCurrentScreen('checkout-summary')} onSelect={setSelectedAddressId} onContinue={() => setCurrentScreen('delivery-method')} onAddAddress={() => { setAddressDraft(emptyAddressDraft()); setCurrentScreen('add-address'); }} /> : null}
      {currentScreen === 'add-address' || currentScreen === 'add-address-errors' ? <AddAddressFormScreen draft={addressDraft} errors={currentScreen === 'add-address-errors' ? validateAddressDraft(addressDraft) : {}} onChange={(next) => { setAddressDraft(next); if (currentScreen === 'add-address-errors') setCurrentScreen('add-address'); }} onBack={() => setCurrentScreen('address-selection')} onSave={saveAddress} /> : null}
      {currentScreen === 'delivery-method' ? <DeliveryMethodScreen address={selectedAddress} selectedMethod={deliveryMethod} onBack={() => setCurrentScreen('address-selection')} onSelect={setDeliveryMethod} onContinue={() => setCurrentScreen('payment-method')} /> : null}
      {currentScreen === 'payment-method' ? <PaymentMethodScreen totalMad={cart.lines.reduce((total, line) => total + (line.unitPriceMad * line.quantity), 0)} selectedMethod={paymentMethod} processing={paymentProcessing} onBack={() => setCurrentScreen('delivery-method')} onSelect={setPaymentMethod} onContinue={completeCheckout} /> : null}
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
          onLoginSubmit={(emailOrPhone, pass) => {
            if (pass === 'error' || emailOrPhone === 'error') {
              authState.failLogin('Identifiants incorrects.');
              setCurrentScreen('login-error');
            } else {
              authState.startLogin();
              authState.completeLogin(emailOrPhone);
              setCurrentScreen('login-loading');
            }
          }}
          onForgotPassword={() => setCurrentScreen('forgot-password')}
          onCreateAccount={() => setCurrentScreen('registration')}
          onBack={() => setCurrentScreen('auth-welcome')}
        />
      ) : null}
      {currentScreen === 'login-error' ? (
        <LoginErrorScreen
          onRetry={() => setCurrentScreen('login')}
          onForgotPassword={() => setCurrentScreen('forgot-password')}
          onBack={() => setCurrentScreen('login')}
        />
      ) : null}
      {currentScreen === 'login-loading' ? (
        <LoginLoadingScreen
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
          onAccept={() => setCurrentScreen('otp-verification')}
          onDecline={() => setCurrentScreen('registration')}
        />
      ) : null}
      {currentScreen === 'account-created' ? (
        <AccountCreatedSuccessScreen
          onContinue={() => resumeAuthReturnDestination()}
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
          onBack={() => setCurrentScreen('terms-consent')}
          onSuccess={() => setCurrentScreen('account-created')}
          onError={() => setCurrentScreen('otp-error')}
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

      {currentScreen === 'order-review' ? <OrderReviewScreen cart={cart} address={selectedAddress} deliveryMethod={deliveryMethod} paymentMethod={paymentMethod} onBack={() => setCurrentScreen('payment-method')} onConfirm={beginOrderReview} /> : null}
      {currentScreen === 'order-processing' && activeOrder ? <OrderProcessingScreen order={activeOrder} onFinish={finishOrderProcessing} /> : null}
      {currentScreen === 'payment-step-intro' && activeOrder ? <PaymentStepIntroScreen order={activeOrder} onBack={() => setCurrentScreen('payment-method')} onContinue={() => setCurrentScreen('secure-payment-redirect')} /> : null}
      {currentScreen === 'secure-payment-redirect' && activeOrder ? <SecurePaymentRedirectScreen order={activeOrder} onContinue={() => setCurrentScreen('secure-payment-loading')} onCancel={() => setCurrentScreen('payment-cancelled')} /> : null}
      {currentScreen === 'secure-payment-loading' && activeOrder ? <SecurePaymentLoadingScreen order={activeOrder} onContinue={() => setCurrentScreen('payment-verification')} /> : null}
      {currentScreen === 'payment-verification' && activeOrder ? <PaymentVerificationScreen order={activeOrder} onContinue={() => setCurrentScreen('cash-on-delivery-confirmation')} /> : null}
      {currentScreen === 'cash-on-delivery-confirmation' && activeOrder ? <CashOnDeliveryConfirmationScreen order={activeOrder} onContinue={() => setCurrentScreen('payment-success')} /> : null}
      {currentScreen === 'payment-failed' && activeOrder ? <PaymentFailureScreen order={activeOrder} onContinue={() => setCurrentScreen('payment-method')} /> : null}
      {currentScreen === 'payment-cancelled' && activeOrder ? <PaymentCancelledScreen order={activeOrder} onContinue={() => setCurrentScreen('payment-method')} /> : null}
      {currentScreen === 'payment-success' && activeOrder ? <PaymentSuccessScreen order={activeOrder} onNext={() => setCurrentScreen('order-thank-you')} onContinueShopping={() => setCurrentScreen('home')} /> : null}
      {currentScreen === 'order-thank-you' && activeOrder ? <OrderThankYouScreen order={activeOrder} onTrack={() => setCurrentScreen('orders-list')} onContinueShopping={() => setCurrentScreen('home')} /> : null}
      {currentScreen === 'orders-list' ? <OrdersListScreen orders={orders} onOpenOrder={(orderId) => { void orderState.selectOrder(orderId).then((selected) => { if (selected) setCurrentScreen('order-details'); }); }} onNavigateTab={navigateTab} /> : null}
      {currentScreen === 'order-details' && activeOrder ? <OrderDetailsScreen order={activeOrder} onBack={() => setCurrentScreen('orders-list')} /> : null}

      <VariantSelectorSheet visible={variantSheetVisible} product={variantProduct} onClose={() => setVariantSheetVisible(false)} onConfirmAddToCart={addSelectedVariantToCart} />
      <FilterPanelModal visible={filterModalVisible} onClose={() => setFilterModalVisible(false)} onApplyFilters={() => setFilterModalVisible(false)} />
      <FavoritesAuthPromptOverlay
        visible={favoritesPromptVisible}
        onClose={() => setFavoritesPromptVisible(false)}
        onSignIn={() => setCurrentScreen('login')}
        onCreateAccount={() => setCurrentScreen('registration')}
        favoriteItemId={favoritesPromptItemId}
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
          setCurrentScreen('auth-welcome');
        }}
      />
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
      <RootNavigatorContent
        hasCompletedOnboarding={hasCompletedOnboarding}
        onLanguageSelected={rememberLanguage}
        onOnboardingCompleted={completeOnboarding}
      />
    </ThemeProvider>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1 },
});
