import AsyncStorage from '@react-native-async-storage/async-storage';
import React, { useEffect, useRef, useState } from 'react';
import { View, StyleSheet } from 'react-native';
import { ThemeProvider } from '../design-system/theme/ThemeProvider';
import { SplashScreen } from '../screens/entry/SplashScreen';
import { LanguageSelectionScreen } from '../screens/entry/LanguageSelectionScreen';
import { PreparingExperienceScreen } from '../screens/entry/PreparingExperienceScreen';
import { OnboardingScreen } from '../screens/entry/OnboardingScreen';
import { WishlistScreen } from '../screens/commerce/WishlistScreen';
import { CartScreen } from '../screens/commerce/CartScreen';
import { AccountScreen } from '../screens/commerce/AccountScreen';
import { HomeScreen } from '../screens/discovery/HomeScreen';
import { CategoriesScreen } from '../screens/discovery/CategoriesScreen';
import { CategoryProductListScreen } from '../screens/discovery/CategoryProductListScreen';
import { ProductDetailsScreen } from '../screens/product/ProductDetailsScreen';
import { ProductGalleryScreen } from '../screens/product/ProductGalleryScreen';
import { VariantSelectorSheet } from '../screens/product/VariantSelectorSheet';
import { AddedToCartConfirmationScreen } from '../screens/commerce/AddedToCartConfirmationScreen';
import { addCartLine, CART_STORAGE_KEY, CartState, emptyCartState, parseMadPrice, updateCartLineQuantity } from '../commerce/cartState';
import { AddressDraft, CHECKOUT_SESSION_KEY, createSavedAddress, defaultSavedAddresses, DeliveryMethod, emptyAddressDraft, isResumableCheckoutScreen, parseCheckoutSession, PaymentMethod, ResumableCheckoutScreen, validateAddressDraft } from '../commerce/checkoutState';
import { createPrototypeOrder, PrototypeOrder } from '../commerce/orderState';
import { CheckoutSummaryScreen } from '../screens/checkout/CheckoutSummaryScreen';
import { AddressSelectionScreen } from '../screens/checkout/AddressSelectionScreen';
import { AddAddressFormScreen } from '../screens/checkout/AddAddressFormScreen';
import { DeliveryMethodScreen } from '../screens/checkout/DeliveryMethodScreen';
import { PaymentMethodScreen } from '../screens/checkout/PaymentMethodScreen';
import { PaymentSuccessScreen } from '../screens/checkout/PaymentSuccessScreen';
import { AuthenticationGateScreen } from '../screens/checkout/AuthenticationGateScreen';
import { OrderReviewScreen } from '../screens/checkout/OrderReviewScreen';
import { OrderProcessingScreen } from '../screens/checkout/OrderProcessingScreen';
import { PaymentStepIntroScreen } from '../screens/checkout/PaymentStepIntroScreen';
import { CashOnDeliveryConfirmationScreen, PaymentCancelledScreen, PaymentFailureScreen, PaymentVerificationScreen, SecurePaymentLoadingScreen, SecurePaymentRedirectScreen } from '../screens/checkout/PaymentFlowScreens';
import { OrderThankYouScreen } from '../screens/orders/OrderThankYouScreen';
import { OrdersListScreen } from '../screens/orders/OrdersListScreen';
import { OrderDetailsScreen } from '../screens/orders/OrderDetailsScreen';
import { CategoryDto, MvpAppLanguage, ProductMiniDto, ProductDetailDto } from '../contracts/api/dto';
import { TabKey } from '../design-system/components/navigation/BottomTabBar';

export type ScreenKey = 'splash' | 'language' | 'preparing' | 'onboarding-1' | 'onboarding-2' | 'onboarding-3' | 'home' | 'categories' | 'wishlist' | 'cart' | 'account' | 'category-products' | 'product-details' | 'product-gallery' | 'added-to-cart' | 'checkout-summary' | 'address-selection' | 'add-address' | 'add-address-errors' | 'delivery-method' | 'payment-method' | 'auth-gate' | 'order-review' | 'order-processing' | 'payment-step-intro' | 'secure-payment-redirect' | 'secure-payment-loading' | 'payment-verification' | 'cash-on-delivery-confirmation' | 'payment-failed' | 'payment-cancelled' | 'payment-success' | 'order-thank-you' | 'orders-list' | 'order-details';

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
  const [cart, setCart] = useState<CartState>(emptyCartState);
  const [savedAddresses, setSavedAddresses] = useState(defaultSavedAddresses);
  const [selectedAddressId, setSelectedAddressId] = useState(defaultSavedAddresses[0].id);
  const [addressDraft, setAddressDraft] = useState<AddressDraft>(emptyAddressDraft);
  const [deliveryMethod, setDeliveryMethod] = useState<DeliveryMethod>('standard');
  const [paymentMethod, setPaymentMethod] = useState<PaymentMethod>('cmi');
  const [paymentProcessing, setPaymentProcessing] = useState(false);
  const [isAuthenticated, setIsAuthenticated] = useState(false);
  const [restoredCheckoutScreen, setRestoredCheckoutScreen] = useState<ResumableCheckoutScreen | null>(null);
  const [checkoutSessionResolved, setCheckoutSessionResolved] = useState(false);
  const [orders, setOrders] = useState<PrototypeOrder[]>([]);
  const [activeOrder, setActiveOrder] = useState<PrototypeOrder | null>(null);
  const paymentLock = useRef(false);

  const selectCategory = (category: CategoryDto) => { setSelectedCategory(category); setCurrentScreen('category-products'); };
  const selectProduct = (product: ProductMiniDto) => { setSelectedProduct(product); setCurrentScreen('product-details'); };
  const activeTab: TabKey = currentScreen === 'categories' || currentScreen === 'category-products'
    ? 'categories'
    : currentScreen === 'wishlist'
      ? 'wishlist'
      : currentScreen === 'cart'
        ? 'cart'
        : currentScreen === 'account'
          ? 'account'
          : 'home';

  useEffect(() => {
    if (!splashFinished || hasCompletedOnboarding === null || !checkoutSessionResolved) return;
    setCurrentScreen(hasCompletedOnboarding ? (restoredCheckoutScreen || 'home') : 'language');
  }, [hasCompletedOnboarding, splashFinished, checkoutSessionResolved, restoredCheckoutScreen]);

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
    }).catch(() => undefined);
    return () => { isMounted = false; };
  }, []);

  useEffect(() => {
    let isMounted = true;
    void AsyncStorage.getItem(CHECKOUT_SESSION_KEY).then((storedSession) => {
      if (!isMounted) return;
      const session = parseCheckoutSession(storedSession);
      if (session) {
        setSelectedAddressId(session.selectedAddressId);
        setDeliveryMethod(session.deliveryMethod);
        setPaymentMethod(session.paymentMethod);
        setSavedAddresses(session.savedAddresses);
        setRestoredCheckoutScreen(session.screen);
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
      const session = {
        screen: currentScreen,
        selectedAddressId,
        deliveryMethod,
        paymentMethod,
        savedAddresses,
      };
      void AsyncStorage.setItem(CHECKOUT_SESSION_KEY, JSON.stringify(session)).catch(() => undefined);
      return;
    }
    if (currentScreen === 'payment-success' || currentScreen === 'order-thank-you' || currentScreen === 'orders-list' || currentScreen === 'order-details') {
      setRestoredCheckoutScreen(null);
      void AsyncStorage.removeItem(CHECKOUT_SESSION_KEY).catch(() => undefined);
    }
  }, [checkoutSessionResolved, currentScreen, deliveryMethod, paymentMethod, savedAddresses, selectedAddressId]);

  const navigateTab = (tab: TabKey) => {
    const destinations: Record<TabKey, ScreenKey> = {
      home: 'home',
      categories: 'categories',
      wishlist: 'wishlist',
      cart: 'cart',
      account: 'account',
    };
    setCurrentScreen(destinations[tab]);
  };

  const addSelectedVariantToCart = (variant: string, quantity: number) => {
    if (!variantProduct) return;
    const imageUri = typeof variantProduct.thumbnail_img === 'string'
      ? variantProduct.thumbnail_img
      : typeof variantProduct.photos?.[0] === 'string'
        ? variantProduct.photos[0]
        : undefined;
    setCart((current) => {
      const next = addCartLine(current, {
        id: `${variantProduct.id}:${variant || 'default'}`,
        productId: variantProduct.id,
        name: variantProduct.name,
        variant: variant || 'Tissu bouclé · Beige',
        quantity,
        unitPriceMad: parseMadPrice(variantProduct.main_price),
        imageUri,
      });
      void AsyncStorage.setItem(CART_STORAGE_KEY, JSON.stringify(next)).catch(() => undefined);
      return next;
    });
    setVariantSheetVisible(false);
    setCurrentScreen('added-to-cart');
  };

  const updateCartQuantity = (lineId: string, quantity: number) => {
    setCart((current) => {
      const next = updateCartLineQuantity(current, lineId, quantity);
      void AsyncStorage.setItem(CART_STORAGE_KEY, JSON.stringify(next)).catch(() => undefined);
      return next;
    });
  };

  const selectedAddress = savedAddresses.find((address) => address.id === selectedAddressId) || savedAddresses[0];

  const saveAddress = () => {
    const errors = validateAddressDraft(addressDraft);
    if (Object.keys(errors).length) {
      setCurrentScreen('add-address-errors');
      return;
    }
    const address = createSavedAddress(addressDraft, `address-${Date.now()}`);
    setSavedAddresses((current) => {
      const withoutPriorDefault = address.isDefault ? current.map((item) => ({ ...item, isDefault: false })) : current;
      return [...withoutPriorDefault, address];
    });
    setSelectedAddressId(address.id);
    setAddressDraft(emptyAddressDraft());
    setCurrentScreen('address-selection');
  };

  const completeCheckout = () => {
    if (paymentLock.current || !cart.lines.length) return;
    if (paymentMethod === 'wallet' && !isAuthenticated) {
      setCurrentScreen('auth-gate');
      return;
    }
    paymentLock.current = true;
    setPaymentProcessing(true);
    const idempotencyKey = [cart.lines.map((line) => `${line.id}:${line.quantity}`).join('|'), selectedAddress.id, deliveryMethod, paymentMethod].join('::');
    const result = createPrototypeOrder(orders, {
      cart,
      address: selectedAddress,
      deliveryMethod,
      paymentMethod,
      idempotencyKey,
    });
    if (result.created) setOrders(result.orders);
    setActiveOrder(result.order);
    setTimeout(() => {
      paymentLock.current = false;
      setPaymentProcessing(false);
      setCurrentScreen('payment-success');
    }, 650);
  };

  const beginOrderReview = () => {
    if (!cart.lines.length) return;
    paymentLock.current = true;
    const idempotencyKey = [cart.lines.map((line) => `${line.id}:${line.quantity}`).join('|'), selectedAddress.id, deliveryMethod, paymentMethod, 'review'].join('::');
    const result = createPrototypeOrder(orders, {
      cart,
      address: selectedAddress,
      deliveryMethod,
      paymentMethod,
      idempotencyKey,
    });
    if (result.created) setOrders(result.orders);
    setActiveOrder(result.order);
    setCurrentScreen('order-processing');
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
      {currentScreen === 'categories' ? <CategoriesScreen activeTab={activeTab} onSelectCategory={selectCategory} onNavigateTab={navigateTab} /> : null}
      {currentScreen === 'wishlist' ? <WishlistScreen onNavigateTab={navigateTab} onBrowseCollections={() => setCurrentScreen('categories')} /> : null}
      {currentScreen === 'cart' ? <CartScreen cart={cart} onNavigateTab={navigateTab} onStartShopping={() => setCurrentScreen('home')} onViewWishlist={() => setCurrentScreen('wishlist')} onUpdateQuantity={updateCartQuantity} onCheckout={() => setCurrentScreen('checkout-summary')} /> : null}
      {currentScreen === 'account' ? <AccountScreen onNavigateTab={navigateTab} onExplore={() => setCurrentScreen('home')} /> : null}
      {currentScreen === 'category-products' ? <CategoryProductListScreen activeTab={activeTab} category={selectedCategory} onBack={() => setCurrentScreen('categories')} onSelectProduct={selectProduct} onNavigateTab={navigateTab} /> : null}
      {currentScreen === 'product-details' ? <ProductDetailsScreen activeTab={activeTab} productId={selectedProduct?.id || 101} initialProduct={selectedProduct} onBack={() => setCurrentScreen('home')} onOpenGallery={() => setCurrentScreen('product-gallery')} onOpenVariantSheet={(product) => { setVariantProduct(product); setVariantSheetVisible(true); }} onNavigateTab={navigateTab} /> : null}
      {currentScreen === 'product-gallery' ? <ProductGalleryScreen activeTab={activeTab} onBack={() => setCurrentScreen('product-details')} onNavigateTab={navigateTab} /> : null}
      {currentScreen === 'added-to-cart' ? <AddedToCartConfirmationScreen cart={cart} onViewCart={() => setCurrentScreen('cart')} /> : null}
      {currentScreen === 'checkout-summary' ? <CheckoutSummaryScreen cart={cart} address={selectedAddress} deliveryMethod={deliveryMethod} paymentMethod={paymentMethod} onBack={() => setCurrentScreen('cart')} onChooseAddress={() => setCurrentScreen('address-selection')} /> : null}
      {currentScreen === 'address-selection' ? <AddressSelectionScreen addresses={savedAddresses} selectedAddressId={selectedAddressId} onBack={() => setCurrentScreen('checkout-summary')} onSelect={setSelectedAddressId} onContinue={() => setCurrentScreen('delivery-method')} onAddAddress={() => { setAddressDraft(emptyAddressDraft()); setCurrentScreen('add-address'); }} /> : null}
      {currentScreen === 'add-address' || currentScreen === 'add-address-errors' ? <AddAddressFormScreen draft={addressDraft} errors={currentScreen === 'add-address-errors' ? validateAddressDraft(addressDraft) : {}} onChange={(next) => { setAddressDraft(next); if (currentScreen === 'add-address-errors') setCurrentScreen('add-address'); }} onBack={() => setCurrentScreen('address-selection')} onSave={saveAddress} /> : null}
      {currentScreen === 'delivery-method' ? <DeliveryMethodScreen address={selectedAddress} selectedMethod={deliveryMethod} onBack={() => setCurrentScreen('address-selection')} onSelect={setDeliveryMethod} onContinue={() => setCurrentScreen('payment-method')} /> : null}
      {currentScreen === 'payment-method' ? <PaymentMethodScreen totalMad={cart.lines.reduce((total, line) => total + (line.unitPriceMad * line.quantity), 0)} selectedMethod={paymentMethod} processing={paymentProcessing} onBack={() => setCurrentScreen('delivery-method')} onSelect={setPaymentMethod} onContinue={completeCheckout} /> : null}
      {currentScreen === 'auth-gate' ? <AuthenticationGateScreen onSignIn={() => { setIsAuthenticated(true); setCurrentScreen('payment-method'); }} onCreateAccount={() => { setIsAuthenticated(true); setCurrentScreen('payment-method'); }} onContinueAsGuest={() => { if (paymentMethod === 'wallet') setPaymentMethod('cmi'); setCurrentScreen('payment-method'); }} /> : null}
      {currentScreen === 'order-review' ? <OrderReviewScreen cart={cart} address={selectedAddress} deliveryMethod={deliveryMethod} paymentMethod={paymentMethod} onBack={() => setCurrentScreen('payment-method')} onConfirm={beginOrderReview} /> : null}
      {currentScreen === 'order-processing' && activeOrder ? <OrderProcessingScreen order={activeOrder} onFinish={() => { paymentLock.current = false; setCurrentScreen('payment-success'); }} /> : null}
      {currentScreen === 'payment-step-intro' && activeOrder ? <PaymentStepIntroScreen order={activeOrder} onBack={() => setCurrentScreen('payment-method')} onContinue={() => setCurrentScreen('secure-payment-redirect')} /> : null}
      {currentScreen === 'secure-payment-redirect' && activeOrder ? <SecurePaymentRedirectScreen order={activeOrder} onContinue={() => setCurrentScreen('secure-payment-loading')} onCancel={() => setCurrentScreen('payment-cancelled')} /> : null}
      {currentScreen === 'secure-payment-loading' && activeOrder ? <SecurePaymentLoadingScreen order={activeOrder} onContinue={() => setCurrentScreen('payment-verification')} /> : null}
      {currentScreen === 'payment-verification' && activeOrder ? <PaymentVerificationScreen order={activeOrder} onContinue={() => setCurrentScreen('cash-on-delivery-confirmation')} /> : null}
      {currentScreen === 'cash-on-delivery-confirmation' && activeOrder ? <CashOnDeliveryConfirmationScreen order={activeOrder} onContinue={() => setCurrentScreen('payment-success')} /> : null}
      {currentScreen === 'payment-failed' && activeOrder ? <PaymentFailureScreen order={activeOrder} onContinue={() => setCurrentScreen('payment-method')} /> : null}
      {currentScreen === 'payment-cancelled' && activeOrder ? <PaymentCancelledScreen order={activeOrder} onContinue={() => setCurrentScreen('payment-method')} /> : null}
      {currentScreen === 'payment-success' && activeOrder ? <PaymentSuccessScreen order={activeOrder} onNext={() => setCurrentScreen('order-thank-you')} onContinueShopping={() => setCurrentScreen('home')} /> : null}
      {currentScreen === 'order-thank-you' && activeOrder ? <OrderThankYouScreen order={activeOrder} onTrack={() => setCurrentScreen('orders-list')} onContinueShopping={() => setCurrentScreen('home')} /> : null}
      {currentScreen === 'orders-list' ? <OrdersListScreen orders={orders} onOpenOrder={(orderId) => { const order = orders.find((item) => item.id === orderId); if (order) { setActiveOrder(order); setCurrentScreen('order-details'); } }} onNavigateTab={navigateTab} /> : null}
      {currentScreen === 'order-details' && activeOrder ? <OrderDetailsScreen order={activeOrder} onBack={() => setCurrentScreen('orders-list')} /> : null}
      <VariantSelectorSheet visible={variantSheetVisible} product={variantProduct} onClose={() => setVariantSheetVisible(false)} onConfirmAddToCart={addSelectedVariantToCart} />
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
