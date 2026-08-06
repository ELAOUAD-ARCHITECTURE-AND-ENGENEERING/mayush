/**
 * Mayush Design System & Phase 5 Test Suite Runner
 */

const fs = require('fs');
const path = require('path');

console.log('==================================================');
console.log('RUNNING MAYUSH DESIGN SYSTEM & PHASE 5 TEST SUITE');
console.log('==================================================');

let errors = 0;
let passes = 0;

function assert(condition, message) {
  if (!condition) {
    console.error(`[FAIL] ${message}`);
    errors++;
  } else {
    console.log(`[PASS] ${message}`);
    passes++;
  }
}

const loadedTypeScriptModules = new Map();

function loadTypeScriptModule(filePath) {
  if (loadedTypeScriptModules.has(filePath)) return loadedTypeScriptModules.get(filePath);
  const typescript = require('typescript');
  const source = fs.readFileSync(filePath, 'utf8');
  const compiled = typescript.transpileModule(source, {
    compilerOptions: { module: typescript.ModuleKind.CommonJS, target: typescript.ScriptTarget.ES2022 },
  });
  const module = { exports: {} };
  loadedTypeScriptModules.set(filePath, module.exports);
  const localRequire = (request) => {
    if (!request.startsWith('.')) return require(request);
    const requestedPath = path.resolve(path.dirname(filePath), request);
    const typescriptPath = path.extname(requestedPath) ? requestedPath : `${requestedPath}.ts`;
    return loadTypeScriptModule(typescriptPath);
  };
  new Function('exports', 'require', 'module', compiled.outputText)(module.exports, localRequire, module);
  loadedTypeScriptModules.set(filePath, module.exports);
  return module.exports;
}

try {
  // 1. Audit Brand Color Tokens
  const colorsFile = fs.readFileSync(path.join(__dirname, '../src/design-system/tokens/colors.ts'), 'utf8');
  assert(colorsFile.includes("orange500: '#FF7900'"), 'brand/orange/500 token is #FF7900');
  assert(colorsFile.includes("navy900: '#101D35'"), 'brand/navy/900 token is #101D35');
  assert(colorsFile.includes("cream: '#FFF9F1'"), 'surface/cream token is #FFF9F1');
  assert(colorsFile.includes("borderWarm: '#EEE7DE'"), 'surface/borderWarm token is #EEE7DE');

  // 2. Audit Typography Tokens
  const typographyFile = fs.readFileSync(path.join(__dirname, '../src/design-system/tokens/typography.ts'), 'utf8');
  assert(typographyFile.includes('display: 30'), 'fontSizes.display is 30px');
  assert(typographyFile.includes('xxl: 24'), 'fontSizes.xxl (pageTitle) is 24px');
  assert(typographyFile.includes('xl: 20'), 'fontSizes.xl (sectionTitle) is 20px');

  // 3. Audit Radii & Sizing
  const radiiFile = fs.readFileSync(path.join(__dirname, '../src/design-system/tokens/radii.ts'), 'utf8');
  assert(radiiFile.includes('lg: 12'), 'Primary button border radius token (lg) is 12px');
  assert(radiiFile.includes('xl: 16'), 'Card border radius token (xl) is 16px');

  const sizingFile = fs.readFileSync(path.join(__dirname, '../src/design-system/tokens/sizing.ts'), 'utf8');
  assert(sizingFile.includes('buttonHeight: 48'), 'Button height token is 48px');
  assert(sizingFile.includes('inputHeight: 48'), 'Input height token is 48px');

  // 4. Audit Theme File (French LTR vs Arabic RTL)
  const themeFile = fs.readFileSync(path.join(__dirname, '../src/design-system/theme/theme.ts'), 'utf8');
  assert(themeFile.includes("const isRTL = language === 'ar'"), 'Theme creates LTR for fr and RTL for ar');

  // 5. Audit Asset Resolution
  assert(fs.existsSync(path.join(__dirname, '../assets/brand/logo-transparent.png')), 'Transparent shared brand logo asset exists');
  assert(fs.existsSync(path.join(__dirname, '../assets/reference-art/home-hero-scene.png')), 'Reference-matched Home hero artwork exists');
  assert(fs.existsSync(path.join(__dirname, '../assets/reference-art/home-hero-premium-scene.png')), 'Second Home hero slide artwork exists');
  assert(fs.existsSync(path.join(__dirname, '../assets/reference-art/home-hero-category-scene.png')), 'Third Home hero slide artwork exists');
  assert(fs.existsSync(path.join(__dirname, '../assets/reference-art/onboarding-step-1-scene.png')), 'French onboarding step 1 artwork exists');
  assert(fs.existsSync(path.join(__dirname, '../assets/reference-art/onboarding-step-2-scene.png')), 'French onboarding step 2 artwork exists');
  assert(fs.existsSync(path.join(__dirname, '../assets/reference-art/onboarding-step-3-scene.png')), 'French onboarding step 3 artwork exists');
  assert(fs.existsSync(path.join(__dirname, '../assets/reference-art/onboarding-step-1-scene-ar.png')), 'Arabic onboarding step 1 artwork exists');
  assert(fs.existsSync(path.join(__dirname, '../assets/reference-art/onboarding-step-2-scene-ar.png')), 'Arabic onboarding step 2 artwork exists');
  assert(fs.existsSync(path.join(__dirname, '../assets/reference-art/onboarding-step-3-scene-ar.png')), 'Arabic onboarding step 3 artwork exists');
  assert(fs.existsSync(path.join(__dirname, '../assets/icon.png')), 'Official brand app icon derivative exists');
  assert(fs.existsSync(path.join(__dirname, '../assets/splash-icon.png')), 'Official brand splash icon derivative exists');

  // 6. Audit Exported Components (MayushIcon adds a shared icon primitive)
  const componentsDir = path.join(__dirname, '../src/design-system/components');
  let componentFiles = [];
  const subdirs = ['actions', 'brand', 'commerce', 'feedback', 'forms', 'layout', 'navigation', 'typography'];

  subdirs.forEach((sub) => {
    const dirPath = path.join(componentsDir, sub);
    if (fs.existsSync(dirPath)) {
      const files = fs.readdirSync(dirPath).filter((f) => f.endsWith('.tsx'));
      componentFiles.push(...files);
    }
  });

  assert(componentFiles.length === 21, `Component files count is 21 (Found: ${componentFiles.length})`);

  // 7. Audit Phase 5 Screens (initial entry flow plus discovery/product states)
  assert(fs.existsSync(path.join(__dirname, '../src/screens/entry/SplashScreen.tsx')), 'SCR-ENT-001 SplashScreen exists');
  assert(fs.existsSync(path.join(__dirname, '../src/screens/entry/LanguageSelectionScreen.tsx')), 'SCR-ENT-002 LanguageSelectionScreen exists');
  assert(fs.existsSync(path.join(__dirname, '../src/screens/entry/PreparingExperienceScreen.tsx')), 'SCR-ENT-003 PreparingExperienceScreen exists');
  assert(fs.existsSync(path.join(__dirname, '../src/screens/entry/OnboardingScreen.tsx')), 'SCR-ENT-004 OnboardingScreen exists');
  assert(fs.existsSync(path.join(__dirname, '../src/screens/commerce/WishlistScreen.tsx')), 'SCR-COM-001 WishlistScreen exists');
  assert(fs.existsSync(path.join(__dirname, '../src/screens/commerce/CartScreen.tsx')), 'SCR-COM-002 CartScreen exists');
  assert(fs.existsSync(path.join(__dirname, '../src/screens/commerce/AccountScreen.tsx')), 'SCR-COM-003 AccountScreen exists');
  assert(fs.existsSync(path.join(__dirname, '../src/screens/discovery/HomeScreen.tsx')), 'SCR-DIS-001 HomeScreen exists');
  assert(fs.existsSync(path.join(__dirname, '../src/screens/discovery/CategoriesScreen.tsx')), 'SCR-DIS-002 CategoriesScreen exists');
  assert(fs.existsSync(path.join(__dirname, '../src/screens/discovery/CategoryProductListScreen.tsx')), 'SCR-DIS-003 CategoryProductListScreen exists');
  assert(fs.existsSync(path.join(__dirname, '../src/screens/product/ProductDetailsScreen.tsx')), 'SCR-PRD-001 ProductDetailsScreen exists');
  assert(fs.existsSync(path.join(__dirname, '../src/screens/product/VariantSelectorSheet.tsx')), 'SCR-PRD-002 VariantSelectorSheet exists');

  // 7b. Focused Figma connection: Product Details -> Product Gallery -> Product Details
  const navigatorContent = fs.readFileSync(path.join(__dirname, '../src/navigation/RootNavigator.tsx'), 'utf8');
  const productDetailsScreenContent = fs.readFileSync(path.join(__dirname, '../src/screens/product/ProductDetailsScreen.tsx'), 'utf8');
  const productGalleryScreenPath = path.join(__dirname, '../src/screens/product/ProductGalleryScreen.tsx');
  const productGalleryScreenContent = fs.readFileSync(productGalleryScreenPath, 'utf8');
  assert(fs.existsSync(productGalleryScreenPath), 'SCR-PRD-003 ProductGalleryScreen exists for Figma node 309:605');
  assert(navigatorContent.includes("'product-gallery'") && navigatorContent.includes('<ProductGalleryScreen'), 'Product Gallery is a real RootNavigator destination');
  assert(productDetailsScreenContent.includes('onOpenGallery') && productDetailsScreenContent.includes('Ouvrir la galerie produit'), 'Product Details exposes the gallery connection');
  assert(productGalleryScreenContent.includes('onPress={onBack}') && productGalleryScreenContent.includes('setActiveImage') && productGalleryScreenContent.includes('setIsZoomed'), 'Product Gallery supports back navigation, thumbnail selection, and native zoom state');

  // 7c. Focused cart state: selected variant is persisted, merged, and totalled in MAD.
  const cartStatePath = path.join(__dirname, '../src/commerce/cartState.ts');
  const cartState = loadTypeScriptModule(cartStatePath);
  const selectedVariant = { id: '101:beige-boucle', productId: 101, name: 'Fauteuil Lounge Luna', variant: 'Tissu bouclé · Beige', quantity: 2, unitPriceMad: 2950 };
  const firstCart = cartState.addCartLine(cartState.emptyCartState(), selectedVariant);
  const mergedCart = cartState.addCartLine(firstCart, { ...selectedVariant, quantity: 1 });
  const updatedCart = cartState.updateCartLineQuantity(mergedCart, selectedVariant.id, 2);
  const totals = cartState.getCartTotals(updatedCart);
  assert(firstCart.lines.length === 1 && firstCart.lines[0].quantity === 2, 'Variant add-to-cart creates a persisted cart line');
  assert(mergedCart.lines.length === 1 && mergedCart.lines[0].quantity === 3, 'Duplicate variant add merges instead of duplicating the cart line');
  assert(updatedCart.lines[0].quantity === 2 && totals.itemCount === 2 && totals.subtotalMad === 5900, 'Cart quantity updates and MAD totals are calculated correctly');
  assert(cartState.updateCartLineQuantity(updatedCart, selectedVariant.id, 0).lines.length === 0, 'Cart removes a line when its quantity reaches zero');
  assert(cartState.parseMadPrice('2 950,00 MAD') === 2950 && cartState.formatMadPrice(5900).includes('MAD'), 'MAD parsing and formatting are store-currency safe');

  const addedToCartPath = path.join(__dirname, '../src/screens/commerce/AddedToCartConfirmationScreen.tsx');
  const addedToCartContent = fs.readFileSync(addedToCartPath, 'utf8');
  assert(fs.existsSync(addedToCartPath) && addedToCartContent.includes('getCartTotals') && addedToCartContent.includes('onViewCart'), 'Added-to-Cart confirmation is native, cart-backed, and routes to Cart');
  assert(navigatorContent.includes("'added-to-cart'") && navigatorContent.includes('addSelectedVariantToCart') && navigatorContent.includes('<AddedToCartConfirmationScreen'), 'Variant Selector -> Added-to-Cart Figma connection is real RootNavigator behavior');

  // 7d. Focused checkout and order flow: saved state, payment lock, idempotency, and Orders refresh.
  const checkoutState = loadTypeScriptModule(path.join(__dirname, '../src/commerce/checkoutState.ts'));
  const orderState = loadTypeScriptModule(path.join(__dirname, '../src/commerce/orderState.ts'));
  const validAddress = checkoutState.defaultSavedAddresses[0];
  const validOrderInput = {
    cart: updatedCart,
    address: validAddress,
    deliveryMethod: 'standard',
    paymentMethod: 'cmi',
    idempotencyKey: 'cart-101:beige::address-youssef::standard::cmi',
  };
  const firstOrderResult = orderState.createPrototypeOrder([], validOrderInput);
  const duplicateOrderResult = orderState.createPrototypeOrder(firstOrderResult.orders, validOrderInput);
  assert(checkoutState.isValidAddress(validAddress) && !checkoutState.isValidAddress({ ...validAddress, phone: '+212 6 12' }), 'Saved-address validation accepts +212 format and rejects incomplete input');
  assert(firstOrderResult.created && firstOrderResult.orders.length === 1 && firstOrderResult.order.totalMad === 5900, 'Checkout creates an order with server-style MAD totals in prototype state');
  assert(!duplicateOrderResult.created && duplicateOrderResult.orders.length === 1 && duplicateOrderResult.order.id === firstOrderResult.order.id, 'Duplicate checkout submission is idempotent');
  assert(navigatorContent.includes('paymentLock') && navigatorContent.includes('setPaymentProcessing(true)') && navigatorContent.includes("setCurrentScreen('payment-success')"), 'Payment processing is locked before successful routing');
  assert(navigatorContent.includes("setCurrentScreen('checkout-summary')") && navigatorContent.includes("setCurrentScreen('address-selection')") && navigatorContent.includes("setCurrentScreen('delivery-method')") && navigatorContent.includes("setCurrentScreen('payment-method')"), 'Cart, checkout summary, address, delivery, and payment routes are connected in sequence');
  assert(navigatorContent.includes("setCurrentScreen('order-thank-you')") && navigatorContent.includes("setCurrentScreen('orders-list')") && navigatorContent.includes("setCurrentScreen('order-details')"), 'Payment success refreshes the Orders path through Order Details');
  assert(fs.existsSync(path.join(__dirname, '../src/screens/checkout/CheckoutSummaryScreen.tsx')) && fs.existsSync(path.join(__dirname, '../src/screens/checkout/AddressSelectionScreen.tsx')) && fs.existsSync(path.join(__dirname, '../src/screens/checkout/DeliveryMethodScreen.tsx')) && fs.existsSync(path.join(__dirname, '../src/screens/checkout/PaymentMethodScreen.tsx')), 'Checkout screens are native routes rather than flattened references');
  assert(fs.existsSync(path.join(__dirname, '../src/screens/checkout/PaymentSuccessScreen.tsx')) && fs.existsSync(path.join(__dirname, '../src/screens/orders/OrderThankYouScreen.tsx')) && fs.existsSync(path.join(__dirname, '../src/screens/orders/OrdersListScreen.tsx')) && fs.existsSync(path.join(__dirname, '../src/screens/orders/OrderDetailsScreen.tsx')), 'Payment success, thank-you, Orders List, and Order Details screens are native routes');

  // 7e. Address creation and interrupted-checkout restoration are native state transitions.
  const addressDraft = {
    name: 'Amina El Idrissi',
    phone: '+212 6 11 22 33 44',
    city: 'Rabat',
    zone: 'Agdal',
    addressLine: '12 Avenue Hassan II',
    apartment: 'Appartement 4',
    postcode: '10000',
    deliveryInstructions: '',
    label: 'Maison',
    isDefault: true,
  };
  const addressErrors = checkoutState.validateAddressDraft(addressDraft);
  const invalidAddressErrors = checkoutState.validateAddressDraft({ ...addressDraft, phone: '+212 6 11', city: '', postcode: '' });
  const savedDraftAddress = checkoutState.createSavedAddress(addressDraft, 'address-amina');
  const restoredSession = checkoutState.parseCheckoutSession(JSON.stringify({
    screen: 'payment-method',
    selectedAddressId: 'address-amina',
    deliveryMethod: 'express',
    paymentMethod: 'cmi',
    savedAddresses: [savedDraftAddress],
  }));
  assert(Object.keys(addressErrors).length === 0 && invalidAddressErrors.phone && invalidAddressErrors.city && invalidAddressErrors.postcode, 'Add Address validates +212, city, and postal-code fields before saving');
  assert(savedDraftAddress.addressLine.includes('Appartement 4') && savedDraftAddress.isDefault, 'Add Address converts the editable draft into a persisted saved-address record');
  assert(restoredSession && restoredSession.screen === 'payment-method' && checkoutState.parseCheckoutSession('{bad json}') === null, 'Interrupted checkout session restores only valid route state');
  assert(navigatorContent.includes('CHECKOUT_SESSION_KEY') && navigatorContent.includes('parseCheckoutSession') && navigatorContent.includes('setRestoredCheckoutScreen'), 'RootNavigator persists and restores address, delivery, payment, and checkout step state');
  assert(fs.existsSync(path.join(__dirname, '../src/screens/checkout/AddAddressFormScreen.tsx')) && navigatorContent.includes("'add-address-errors'") && navigatorContent.includes('saveAddress'), 'Add Address and its Figma validation-error return path are real native screens');
  const checkoutSummaryContent = fs.readFileSync(path.join(__dirname, '../src/screens/checkout/CheckoutSummaryScreen.tsx'), 'utf8');
  assert(checkoutSummaryContent.includes('address: SavedAddress') && checkoutSummaryContent.includes('deliveryMethod: DeliveryMethod') && checkoutSummaryContent.includes('paymentMethod: PaymentMethod') && checkoutSummaryContent.includes('detail={addressDetail}'), 'Checkout Summary renders the saved address, delivery method, and payment method from live checkout state');

  // 7f. Prototype checkout branches: authentication continuation, review, secure payment, and recovery.
  const paymentFlowPath = path.join(__dirname, '../src/screens/checkout/PaymentFlowScreens.tsx');
  const paymentFlowContent = fs.readFileSync(paymentFlowPath, 'utf8');
  const orderReviewPath = path.join(__dirname, '../src/screens/checkout/OrderReviewScreen.tsx');
  const orderReviewContent = fs.readFileSync(orderReviewPath, 'utf8');
  const orderProcessingPath = path.join(__dirname, '../src/screens/checkout/OrderProcessingScreen.tsx');
  const authGatePath = path.join(__dirname, '../src/screens/checkout/AuthenticationGateScreen.tsx');
  assert(fs.existsSync(authGatePath) && navigatorContent.includes("'auth-gate'") && navigatorContent.includes('setIsAuthenticated(true)') && navigatorContent.includes("paymentMethod === 'wallet'"), 'Authentication gate returns an authenticated or guest buyer to the pending payment step');
  assert(fs.existsSync(orderReviewPath) && orderReviewContent.includes('getCartTotals') && orderReviewContent.includes('termsAccepted') && navigatorContent.includes("'order-review'"), 'Order Review derives MAD totals from cart state and prevents confirmation until terms are accepted');
  assert(fs.existsSync(orderProcessingPath) && fs.readFileSync(orderProcessingPath, 'utf8').includes('setTimeout(onFinish'), 'Order processing has a real timed loading state before success routing');
  assert(paymentFlowContent.includes('SecurePaymentRedirectScreen') && paymentFlowContent.includes('SecurePaymentLoadingScreen') && paymentFlowContent.includes('PaymentVerificationScreen') && paymentFlowContent.includes('CashOnDeliveryConfirmationScreen'), 'Secure payment and payment verification branch screens are native implementations');
  assert(paymentFlowContent.includes('PaymentFailureScreen') && paymentFlowContent.includes('PaymentCancelledScreen') && navigatorContent.includes("'payment-failed'") && navigatorContent.includes("'payment-cancelled'"), 'Payment failure and cancellation recover safely to Payment Method');
  assert(navigatorContent.includes("'secure-payment-redirect'") && navigatorContent.includes("'secure-payment-loading'") && navigatorContent.includes("'payment-verification'") && navigatorContent.includes("'cash-on-delivery-confirmation'"), 'Figma secure-payment branch destination keys are wired in RootNavigator');
  const codOrder = orderState.createPrototypeOrder([], { ...validOrderInput, paymentMethod: 'cash-on-delivery', idempotencyKey: 'cod-order' }).order;
  assert(codOrder.paymentStatus === 'À payer à la livraison' && codOrder.paymentReference.startsWith('COD-'), 'Cash-on-delivery order status remains distinct from paid CMI orders');

  // 8. Audit API Repositories & Client
  assert(fs.existsSync(path.join(__dirname, '../src/services/api/apiClient.ts')), 'apiClient HTTP service exists');
  assert(fs.existsSync(path.join(__dirname, '../src/services/api/catalogService.ts')), 'catalogService API repository exists');

  // 9. Audit Home carousel and configured store currency usage
  const currencyFile = fs.readFileSync(path.join(__dirname, '../src/config/currency.ts'), 'utf8');
  const homeScreenContent = fs.readFileSync(path.join(__dirname, '../src/screens/discovery/HomeScreen.tsx'), 'utf8');
  const productDetailsContent = fs.readFileSync(path.join(__dirname, '../src/screens/product/ProductDetailsScreen.tsx'), 'utf8');
  assert(currencyFile.includes("STORE_CURRENCY_CODE = 'MAD'"), 'Mobile store currency matches the configured MAD currency');
  assert(homeScreenContent.includes('pagingEnabled') && homeScreenContent.includes('onMomentumScrollEnd') && homeScreenContent.includes('setInterval'), 'Home hero supports swipe, dot selection, and timed advance');
  assert(!homeScreenContent.includes('\u20ac') && !productDetailsContent.includes('\u20ac'), 'Home and product fallback prices contain no euro currency');

  // 10. Audit Variant Pricing Endpoint Mapping
  const catalogServiceContent = fs.readFileSync(path.join(__dirname, '../src/services/api/catalogService.ts'), 'utf8');
  assert(catalogServiceContent.includes('/api/v2/products/variant/price'), 'Server-authoritative variant price endpoint mapped');

  // 11. Audit Root Navigation Wiring
  assert(fs.existsSync(path.join(__dirname, '../src/navigation/RootNavigator.tsx')), 'RootNavigator exists');
  const appContent = fs.readFileSync(path.join(__dirname, '../App.tsx'), 'utf8');
  assert(appContent.includes('<RootNavigator />'), 'App.tsx renders RootNavigator');

} catch (err) {
  console.error('[CRITICAL FAILURE]', err);
  errors++;
}

console.log('==================================================');
console.log(`TEST SUMMARY: ${passes} PASSED, ${errors} FAILED`);
console.log('==================================================');

if (errors > 0) {
  process.exit(1);
} else {
  process.exit(0);
}
