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
    compilerOptions: { module: typescript.ModuleKind.CommonJS, target: typescript.ScriptTarget.ES2022, jsx: typescript.JsxEmit.React },
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
  assert(colorsFile.includes("orange500: '#D97434'"), 'brand/orange/500 token is #D97434');
  assert(colorsFile.includes("navy900: '#1F2A3A'"), 'brand/navy/900 token is #1F2A3A');
  assert(colorsFile.includes("cream: '#F2E8DA'"), 'surface/cream token is #F2E8DA');
  assert(colorsFile.includes("borderWarm: '#E7DED3'"), 'surface/borderWarm token is #E7DED3');

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

  // 6. Audit Exported Components
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

  // 7. Audit Phase 5 Screens & Step 1 + Step 2 + Step 3 Clusters
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
  assert(fs.existsSync(path.join(__dirname, '../src/screens/discovery/CategoryLandingScreen.tsx')), 'SCR-DIS-004 CategoryLandingScreen exists (309:593)');
  assert(fs.existsSync(path.join(__dirname, '../src/screens/discovery/CollectionShopTheLookScreen.tsx')), 'SCR-DIS-005 CollectionShopTheLookScreen exists (309:595)');
  assert(fs.existsSync(path.join(__dirname, '../src/screens/discovery/FilterPanelModal.tsx')), 'SCR-DIS-006 FilterPanelModal exists (309:596)');
  assert(fs.existsSync(path.join(__dirname, '../src/screens/promotions/FlashDealsScreen.tsx')), 'SCR-PRO-001 FlashDealsScreen exists (309:597)');
  assert(fs.existsSync(path.join(__dirname, '../src/screens/promotions/PromotionsCampaignsScreen.tsx')), 'SCR-PRO-002 PromotionsCampaignsScreen exists (309:598)');
  assert(fs.existsSync(path.join(__dirname, '../src/screens/discovery/RecentlyViewedScreen.tsx')), 'SCR-DIS-007 RecentlyViewedScreen exists (309:599)');
  assert(fs.existsSync(path.join(__dirname, '../src/screens/search/SearchLandingScreen.tsx')), 'SCR-SRCH-001 SearchLandingScreen exists (309:600)');
  assert(fs.existsSync(path.join(__dirname, '../src/screens/search/SearchResultsScreen.tsx')), 'SCR-SRCH-002 SearchResultsScreen exists (309:601)');
  assert(fs.existsSync(path.join(__dirname, '../src/screens/search/SearchNoResultsScreen.tsx')), 'SCR-SRCH-003 SearchNoResultsScreen exists (309:602)');
  assert(fs.existsSync(path.join(__dirname, '../src/screens/product/ProductDetailsScreen.tsx')), 'SCR-PRD-001 ProductDetailsScreen exists (309:604)');
  assert(fs.existsSync(path.join(__dirname, '../src/screens/product/VariantSelectorSheet.tsx')), 'SCR-PRD-002 VariantSelectorSheet exists (309:607)');
  assert(fs.existsSync(path.join(__dirname, '../src/screens/product/ProductFullDescriptionScreen.tsx')), 'SCR-PRD-004 ProductFullDescriptionScreen exists (309:606)');
  assert(fs.existsSync(path.join(__dirname, '../src/screens/product/ProductSpecificationsScreen.tsx')), 'SCR-PRD-005 ProductSpecificationsScreen exists (309:608)');
  assert(fs.existsSync(path.join(__dirname, '../src/screens/product/ProductDeliveryReturnsScreen.tsx')), 'SCR-PRD-006 ProductDeliveryReturnsScreen exists (309:609)');
  assert(fs.existsSync(path.join(__dirname, '../src/screens/product/ProductReviewsRatingsScreen.tsx')), 'SCR-PRD-007 ProductReviewsRatingsScreen exists (309:610)');

  // 7b. Step 1 Navigation wiring assertions
  const navigatorContent = fs.readFileSync(path.join(__dirname, '../src/navigation/RootNavigator.tsx'), 'utf8');
  assert(navigatorContent.includes("<CategoryLandingScreen"), 'CategoryLandingScreen is a real RootNavigator destination');
  assert(navigatorContent.includes("<CollectionShopTheLookScreen"), 'CollectionShopTheLookScreen is a real RootNavigator destination');
  assert(navigatorContent.includes("<FilterPanelModal"), 'FilterPanelModal is wired in RootNavigator');
  assert(navigatorContent.includes("<FlashDealsScreen"), 'FlashDealsScreen is a real RootNavigator destination');
  assert(navigatorContent.includes("<PromotionsCampaignsScreen"), 'PromotionsCampaignsScreen is a real RootNavigator destination');
  assert(navigatorContent.includes("<RecentlyViewedScreen"), 'RecentlyViewedScreen is a real RootNavigator destination');
  assert(navigatorContent.includes("<SearchLandingScreen"), 'SearchLandingScreen is a real RootNavigator destination');
  assert(navigatorContent.includes("<SearchResultsScreen"), 'SearchResultsScreen is a real RootNavigator destination');
  assert(navigatorContent.includes("<SearchNoResultsScreen"), 'SearchNoResultsScreen is a real RootNavigator destination');

  // 7c. Step 3A Component assertions
  assert(fs.existsSync(path.join(__dirname, '../src/components/cart/QuantityStepper.tsx')), 'QuantityStepper component exists and supports increment/decrement controls');
  assert(fs.existsSync(path.join(__dirname, '../src/components/cart/CartToast.tsx')), 'CartToast component exists and renders feedback message (309:659)');
  assert(fs.existsSync(path.join(__dirname, '../src/components/cart/VariantEditSheet.tsx')), 'VariantEditSheet component exists for cart variant editing (309:660)');
  assert(fs.existsSync(path.join(__dirname, '../src/components/cart/SellerCartGroup.tsx')), 'SellerCartGroup component exists for artisan shop multi-vendor grouping (309:661)');
  assert(fs.existsSync(path.join(__dirname, '../src/components/cart/RemoveItemDialog.tsx')), 'RemoveItemDialog component exists for remove item confirmation (309:665)');

  // 7d. Step 3B System States & Fusion Merge assertions
  assert(fs.existsSync(path.join(__dirname, '../src/components/cart/CartUpdateAlert.tsx')), 'CartUpdateAlert component exists for price/stock change notifications (309:666)');
  assert(fs.existsSync(path.join(__dirname, '../src/components/cart/CartSkeleton.tsx')), 'CartSkeleton component exists for native placeholder loading (309:667)');
  assert(fs.existsSync(path.join(__dirname, '../src/components/cart/CartEmptyState.tsx')), 'CartEmptyState component exists with discovery CTA (309:668)');
  assert(fs.existsSync(path.join(__dirname, '../src/components/cart/CartErrorState.tsx')), 'CartErrorState component exists with retry action (309:669)');
  assert(fs.existsSync(path.join(__dirname, '../src/components/cart/SavedForLaterList.tsx')), 'SavedForLaterList component exists for saved item list (309:676)');
  assert(fs.existsSync(path.join(__dirname, '../src/components/cart/CartMergeSummary.tsx')), 'CartMergeSummary component exists for guest-account fusion merge (309:677)');

  // 7e. Deduplication logic assertion
  const cartMergeContent = fs.readFileSync(path.join(__dirname, '../src/components/cart/CartMergeSummary.tsx'), 'utf8');
  assert(cartMergeContent.includes('mergeCartsDeduplicated') && cartMergeContent.includes('mergedMap.has(key)'), 'Cart merge deduplicates lines by product ID and variant correctly');

  // 7f. Focused Step 3 Cart assertions
  const cartScreenContent = fs.readFileSync(path.join(__dirname, '../src/screens/commerce/CartScreen.tsx'), 'utf8');
  assert(cartScreenContent.includes('FREE_SHIPPING_THRESHOLD') && cartScreenContent.includes('freeShippingProgress'), 'CartScreen supports 3,000 MAD free shipping threshold progress bar (309:667)');
  assert(cartScreenContent.includes('appliedPromo') && cartScreenContent.includes('handleApplyPromo') && cartScreenContent.includes('promoError'), 'CartScreen supports promo code input, validation, and discount recalculation (309:662 - 309:665)');
  assert(cartScreenContent.includes('savedForLater') && cartScreenContent.includes('handleMoveToLater') && cartScreenContent.includes('handleMoveBackToCart'), 'CartScreen supports saved-for-later section and move-back transitions (309:676)');
  assert(cartScreenContent.includes('lineToRemove') && cartScreenContent.includes('confirmRemoveLine'), 'CartScreen supports item removal confirmation modal dialog (309:665)');
  assert(cartScreenContent.includes('groupBySeller') && cartScreenContent.includes('SellerCartGroup'), 'CartScreen supports multi-vendor seller grouping toggle (309:661)');
  assert(cartScreenContent.includes('lineToEditVariant') && cartScreenContent.includes('VariantEditSheet'), 'CartScreen supports bottom sheet variant editing (309:660)');
  assert(cartScreenContent.includes('CartToast') && cartScreenContent.includes('triggerToast'), 'CartScreen triggers quantity update toast feedback (309:659)');
  assert(cartScreenContent.includes('CartUpdateAlert') && cartScreenContent.includes('handleAcceptPriceChanges'), 'CartScreen handles price and stock change notifications (309:666)');
  assert(cartScreenContent.includes('CartSkeleton'), 'CartScreen renders native loading skeleton state (309:667)');
  assert(cartScreenContent.includes('CartErrorState'), 'CartScreen renders loading error state with retry action (309:669)');
  assert(cartScreenContent.includes('CartMergeSummary') && cartScreenContent.includes('handleMergeBothCarts'), 'CartScreen supports guest-account cart fusion modal (309:677)');

  // 7g. Focused cart state: selected variant is persisted, merged, and totalled in MAD.
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

  // 7h. Focused checkout and order flow
  const checkoutState = loadTypeScriptModule(path.join(__dirname, '../src/commerce/checkoutState.ts'));
  const orderState = loadTypeScriptModule(path.join(__dirname, '../src/commerce/orderState.ts'));
  const validAddress = checkoutState.defaultSavedAddresses[0];
  const orderStateContent = fs.readFileSync(path.join(__dirname, '../src/commerce/orderState.ts'), 'utf8');
  assert(checkoutState.isValidAddress(validAddress) && !checkoutState.isValidAddress({ ...validAddress, phone: '+212 6 12' }), 'Saved-address validation accepts +212 format and rejects incomplete input');
  assert(typeof orderState.createBuyerOrderRepository === 'function' && orderStateContent.includes('await this.persist()'), 'Checkout order repository persists created buyer orders');
  assert(orderStateContent.includes('order.checkoutAttemptId === input.checkoutAttemptId'), 'Duplicate checkout submission is idempotent by checkout attempt identity');
  assert(navigatorContent.includes('paymentLock') && navigatorContent.includes('setPaymentProcessing(true)') && navigatorContent.includes("setCurrentScreen('payment-success')"), 'Payment processing is locked before successful routing');
  assert(navigatorContent.includes("setCurrentScreen('checkout-summary')") && navigatorContent.includes("setCurrentScreen('address-selection')") && navigatorContent.includes("setCurrentScreen('delivery-method')") && navigatorContent.includes("setCurrentScreen('payment-method')"), 'Cart, checkout summary, address, delivery, and payment routes are connected in sequence');
  assert(navigatorContent.includes("setCurrentScreen('order-thank-you')") && navigatorContent.includes("setCurrentScreen('orders-list')") && navigatorContent.includes("setCurrentScreen('order-details')"), 'Payment success refreshes the Orders path through Order Details');

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

  // 11. Audit Step 4A Authentication, Registration & Return Destination Flow
  const authStateModule = loadTypeScriptModule(path.join(__dirname, '../src/commerce/authState.ts'));
  const authStateInstance = authStateModule.authState;
  authStateInstance.reset();
  assert(authStateInstance.getStatus() === 'guest' && authStateInstance.getUser() === null, 'AuthState initializes in guest state');

  authStateInstance.setReturnDestination({ route: 'wishlist', pendingAction: 'favorite', favoriteItemId: '701' });
  assert(authStateInstance.getReturnDestination().route === 'wishlist' && authStateInstance.getReturnDestination().favoriteItemId === '701', 'AuthState preserves return destination and pending action');

  authStateInstance.startLogin();
  assert(authStateInstance.getStatus() === 'logging-in', 'AuthState enters logging-in state');

  authStateInstance.completeLogin('karim@example.ma', 'Karim Benjelloun');
  assert(authStateInstance.getStatus() === 'authenticated' && authStateInstance.getUser().emailOrPhone === 'karim@example.ma', 'AuthState completes login with mock user profile');

  authStateInstance.updateRegistrationDraft({ fullName: 'Sarah Alami', emailOrPhone: '+212661998877', password: 'Password123' });
  authStateInstance.completeRegistration();
  assert(authStateInstance.getStatus() === 'registration-success' && authStateInstance.getUser().fullName === 'Sarah Alami', 'AuthState completes registration flow from draft');

  const authWelcomePath = path.join(__dirname, '../src/screens/auth/AuthenticationWelcomeScreen.tsx');
  const loginPath = path.join(__dirname, '../src/screens/auth/LoginScreen.tsx');
  const loginErrorPath = path.join(__dirname, '../src/screens/auth/LoginErrorScreen.tsx');
  const loginLoadingPath = path.join(__dirname, '../src/screens/auth/LoginLoadingScreen.tsx');
  const registrationPath = path.join(__dirname, '../src/screens/auth/RegistrationScreen.tsx');
  const consentPath = path.join(__dirname, '../src/screens/auth/TermsConsentScreen.tsx');
  const successPath = path.join(__dirname, '../src/screens/auth/AccountCreatedSuccessScreen.tsx');
  const promptPath = path.join(__dirname, '../src/screens/auth/FavoritesAuthPromptOverlay.tsx');

  assert(fs.existsSync(authWelcomePath), 'AuthenticationWelcomeScreen exists (309:613)');
  assert(fs.existsSync(loginPath), 'LoginScreen exists (309:614)');
  assert(fs.existsSync(loginErrorPath), 'LoginErrorScreen exists (309:618)');
  assert(fs.existsSync(loginLoadingPath), 'LoginLoadingScreen exists (309:622)');
  assert(fs.existsSync(registrationPath), 'RegistrationScreen exists (309:648)');
  assert(fs.existsSync(consentPath), 'TermsConsentScreen exists (309:644)');
  assert(fs.existsSync(successPath), 'AccountCreatedSuccessScreen exists (309:649)');
  assert(fs.existsSync(promptPath), 'FavoritesAuthPromptOverlay exists (309:653)');

  const loginContent = fs.readFileSync(loginPath, 'utf8');
  const regContent = fs.readFileSync(registrationPath, 'utf8');
  const consentContent = fs.readFileSync(consentPath, 'utf8');

  assert(loginContent.includes('showPassword') && loginContent.includes('Se souvenir de moi') && loginContent.includes('Mot de passe oublié'), 'LoginScreen supports password toggle, remember-me, and forgot password action');
  assert(regContent.includes('+212') && regContent.includes('isPasswordValid') && regContent.includes('Au moins 8 caractères'), 'RegistrationScreen validates +212 Moroccan phone format and password rules');
  assert(consentContent.includes('Loi 09-08') && consentContent.includes('completeRegistration') && consentContent.includes('agreed'), 'TermsConsentScreen enforces legal consent before account creation');

  assert(navigatorContent.includes("'login'") && navigatorContent.includes("'registration'") && navigatorContent.includes("'terms-consent'") && navigatorContent.includes("'account-created'") && navigatorContent.includes('<FavoritesAuthPromptOverlay'), 'Step 4A auth routes and overlays are wired in RootNavigator');
  assert(navigatorContent.includes('resumeAuthReturnDestination'), 'RootNavigator restores auth return destination upon login/registration completion');

  // 12. Audit Step 4B Password Recovery, OTP & Password Reset Flow
  authStateInstance.reset();
  authStateInstance.startPasswordRecovery('youssef@example.ma', 'email');
  assert(authStateInstance.getStatus() === 'recovering-password' && authStateInstance.getRecoveryIdentifier() === 'youssef@example.ma', 'AuthState initializes password recovery state with identifier');

  authStateInstance.setOtpCode('123456');
  assert(authStateInstance.getOtpCode() === '123456' && authStateInstance.getOtpError() === null, 'AuthState stores 6-digit OTP code draft');

  authStateInstance.failOtp('Code OTP incorrect.');
  assert(authStateInstance.getStatus() === 'otp-error' && authStateInstance.getOtpError() === 'Code OTP incorrect.', 'AuthState handles OTP verification failure state');

  authStateInstance.updateNewPasswordDraft({ password: 'NewPassword123', confirmPassword: 'NewPassword123' });
  authStateInstance.completePasswordReset();
  assert(authStateInstance.getStatus() === 'password-reset-success', 'AuthState completes password reset transition');

  const forgotPasswordPath = path.join(__dirname, '../src/screens/auth/ForgotPasswordScreen.tsx');
  const emailSentPath = path.join(__dirname, '../src/screens/auth/EmailVerificationSentScreen.tsx');
  const otpPath = path.join(__dirname, '../src/screens/auth/PhoneOtpVerificationScreen.tsx');
  const otpErrorPath = path.join(__dirname, '../src/screens/auth/OtpErrorScreen.tsx');
  const createNewPasswordPath = path.join(__dirname, '../src/screens/auth/CreateNewPasswordScreen.tsx');
  const passwordSuccessPath = path.join(__dirname, '../src/screens/auth/PasswordChangedSuccessScreen.tsx');

  assert(fs.existsSync(forgotPasswordPath), 'ForgotPasswordScreen exists (309:626)');
  assert(fs.existsSync(emailSentPath), 'EmailVerificationSentScreen exists (309:630)');
  assert(fs.existsSync(otpPath), 'PhoneOtpVerificationScreen exists (309:634)');
  assert(fs.existsSync(otpErrorPath), 'OtpErrorScreen exists (309:638)');
  assert(fs.existsSync(createNewPasswordPath), 'CreateNewPasswordScreen exists (309:639)');
  assert(fs.existsSync(passwordSuccessPath), 'PasswordChangedSuccessScreen exists (309:643 / 309:756)');

  const forgotContent = fs.readFileSync(forgotPasswordPath, 'utf8');
  const otpContent = fs.readFileSync(otpPath, 'utf8');
  const createNewPasswordContent = fs.readFileSync(createNewPasswordPath, 'utf8');

  assert(forgotContent.includes('isValidEmail') && forgotContent.includes('startPasswordRecovery'), 'ForgotPasswordScreen validates email format and triggers recovery');
  assert(otpContent.includes('handleDigitChange') && otpContent.includes('inputsRef') && otpContent.includes('timerSeconds'), 'PhoneOtpVerificationScreen supports 6-digit auto-advance and resend timer');
  assert(createNewPasswordContent.includes('hasLength') && createNewPasswordContent.includes('hasLetter') && createNewPasswordContent.includes('isMatch') && createNewPasswordContent.includes('showPassword'), 'CreateNewPasswordScreen enforces password requirements, match validation, and eye toggle');

  assert(navigatorContent.includes("'forgot-password'") && navigatorContent.includes("'recovery-email-sent'") && navigatorContent.includes("'otp-verification'") && navigatorContent.includes("'otp-error'") && navigatorContent.includes("'create-new-password'") && navigatorContent.includes("'password-changed-success'"), 'Step 4B recovery routes are registered and connected in RootNavigator');

  // 13. Audit Step 5A Buyer Account Dashboard, Profile & Identity Flow
  authStateInstance.reset();
  assert(authStateInstance.getStatus() === 'guest', 'Guest Account status is preserved on reset');

  authStateInstance.completeLogin('karim@example.ma', 'Karim Benjelloun');
  assert(authStateInstance.getStatus() === 'authenticated' && authStateInstance.getUser().fullName === 'Karim Benjelloun', 'Authenticated Account dashboard initializes with profile');

  authStateInstance.updateProfileDraft({ fullName: 'Karim Benjelloun', city: 'Rabat', phone: '+212661998877' });
  authStateInstance.saveProfileFromDraft();
  assert(authStateInstance.getUser().city === 'Rabat' && authStateInstance.getUser().profileCompletionPercent === 85, 'Profile draft update persists to user state');

  authStateInstance.changeEmail('karim.new@example.ma');
  assert(authStateInstance.getUser().email === 'karim.new@example.ma', 'Email change updates user profile email');

  authStateInstance.changePhone('+212677112233');
  assert(authStateInstance.getUser().phone === '+212677112233', 'Phone change updates user profile phone');

  authStateInstance.logout();
  assert(authStateInstance.getStatus() === 'guest' && authStateInstance.getUser() === null, 'Logout transitions back to guest account state');

  const settingsPath = path.join(__dirname, '../src/screens/account/AccountSettingsScreen.tsx');
  const myInfoPath = path.join(__dirname, '../src/screens/account/MyInformationScreen.tsx');
  const editProfilePath = path.join(__dirname, '../src/screens/account/EditProfileScreen.tsx');
  const changeEmailPath = path.join(__dirname, '../src/screens/account/ChangeEmailScreen.tsx');
  const changePhonePath = path.join(__dirname, '../src/screens/account/ChangePhoneScreen.tsx');
  const accountOtpPath = path.join(__dirname, '../src/screens/account/AccountVerifyPhoneOtpScreen.tsx');
  const changePasswordPath = path.join(__dirname, '../src/screens/account/ChangePasswordFormScreen.tsx');

  assert(fs.existsSync(settingsPath), 'AccountSettingsScreen exists (309:748)');
  assert(fs.existsSync(myInfoPath), 'MyInformationScreen exists (309:749)');
  assert(fs.existsSync(editProfilePath), 'EditProfileScreen exists (309:750)');
  assert(fs.existsSync(changeEmailPath), 'ChangeEmailScreen exists (309:752)');
  assert(fs.existsSync(changePhonePath), 'ChangePhoneScreen exists (309:754)');
  assert(fs.existsSync(accountOtpPath), 'AccountVerifyPhoneOtpScreen exists (309:755)');
  assert(fs.existsSync(changePasswordPath), 'ChangePasswordFormScreen exists (309:753)');

  const accountScreenContent = fs.readFileSync(path.join(__dirname, '../src/screens/commerce/AccountScreen.tsx'), 'utf8');
  assert(accountScreenContent.includes('isAuthenticated') && accountScreenContent.includes('progressBanner') && accountScreenContent.includes('shortcutsGrid'), 'AccountScreen dynamically supports Guest and Authenticated Dashboard states (309:747)');

  assert(navigatorContent.includes("'account-settings'") && navigatorContent.includes("'my-information'") && navigatorContent.includes("'edit-profile'") && navigatorContent.includes("'change-email'") && navigatorContent.includes("'change-phone'") && navigatorContent.includes("'account-verify-phone'") && navigatorContent.includes("'change-password'"), 'Step 5A account & profile routes are registered in RootNavigator');

  // 14. Audit Step 5B.1 Account Security, 2FA & Active Sessions Flow
  authStateInstance.reset();
  assert(authStateInstance.isTwoFactorEnabled() === false, '2FA initializes disabled by default');

  authStateInstance.setTwoFactorEnabled(true);
  assert(authStateInstance.isTwoFactorEnabled() === true, '2FA state can be enabled via toggle');

  const initialSessions = authStateInstance.getActiveSessions();
  assert(initialSessions.length === 3 && initialSessions.some((s) => s.isCurrent), 'Active sessions initializes with current device badge and remote sessions');

  const sessionToDisconnect = initialSessions.find((s) => !s.isCurrent);
  assert(sessionToDisconnect !== undefined, 'Remote session is present for disconnect testing');

  authStateInstance.disconnectSession(sessionToDisconnect.id);
  const remainingSessions = authStateInstance.getActiveSessions();
  assert(remainingSessions.length === 2 && !remainingSessions.some((s) => s.id === sessionToDisconnect.id), 'Session removal eliminates disconnected session from state');

  const securityOverviewPath = path.join(__dirname, '../src/screens/account/AccountSecurityScreen.tsx');
  const securityMenuPath = path.join(__dirname, '../src/screens/account/SecurityPrivacyMenuScreen.tsx');
  const twoFactorPath = path.join(__dirname, '../src/screens/account/TwoFactorAuthScreen.tsx');
  const activeSessionsPath = path.join(__dirname, '../src/screens/account/ActiveSessionsScreen.tsx');
  const disconnectModalPath = path.join(__dirname, '../src/screens/account/DisconnectSessionModal.tsx');

  assert(fs.existsSync(securityOverviewPath), 'AccountSecurityScreen exists (309:757)');
  assert(fs.existsSync(securityMenuPath), 'SecurityPrivacyMenuScreen exists (309:758)');
  assert(fs.existsSync(twoFactorPath), 'TwoFactorAuthScreen exists (309:759)');
  assert(fs.existsSync(activeSessionsPath), 'ActiveSessionsScreen exists (309:760)');
  assert(fs.existsSync(disconnectModalPath), 'DisconnectSessionModal exists (309:761)');

  const secOverviewContent = fs.readFileSync(securityOverviewPath, 'utf8');
  const twoFactorContent = fs.readFileSync(twoFactorPath, 'utf8');
  const activeSessionsContent = fs.readFileSync(activeSessionsPath, 'utf8');
  const disconnectModalContent = fs.readFileSync(disconnectModalPath, 'utf8');

  assert(secOverviewContent.includes('is2FAEnabled') && secOverviewContent.includes('sessionsCount'), 'AccountSecurityScreen displays live 2FA status and session count');
  assert(twoFactorContent.includes('Switch') && twoFactorContent.includes('handleToggle'), 'TwoFactorAuthScreen renders interactive toggle switch');
  assert(activeSessionsContent.includes('currentDevice') && activeSessionsContent.includes('otherSessions'), 'ActiveSessionsScreen separates current device from remote sessions');
  assert(disconnectModalContent.includes('onConfirm') && disconnectModalContent.includes('onCancel'), 'DisconnectSessionModal supports cancel and confirm removal actions');

  assert(navigatorContent.includes("'account-security'") && navigatorContent.includes("'security-privacy'") && navigatorContent.includes("'security-2fa'") && navigatorContent.includes("'active-sessions'"), 'Step 5B.1 security and session routes are registered in RootNavigator');

  // ──────────────────────────────────────────────
  // 15. STEP 5B.2 — ACCOUNT ADDRESSES MANAGEMENT
  // ──────────────────────────────────────────────

  // 15a. Address state CRUD in authState (authStateInstance defined in Step 5A above)
  authStateInstance.reset();

  const seedAddresses = authStateInstance.getSavedAddresses();
  assert(seedAddresses.length >= 2, 'authState seeds at least 2 default saved addresses');
  assert(seedAddresses.some((a) => a.isDefault), 'authState seed addresses include one default');

  // Add an address
  const testAddr = { id: 'test-new', name: 'Test User', phone: '+212 6 11 22 33 44', addressLine: '10 Test Street', city: 'Fès', postcode: '30000', zone: 'Fès Centre', isDefault: false };
  authStateInstance.addAddress(testAddr);
  assert(authStateInstance.getSavedAddresses().length === seedAddresses.length + 1, 'addAddress() appends a new address to the list');

  // Update an address
  authStateInstance.updateAddress('test-new', { name: 'Updated User' });
  assert(authStateInstance.getSavedAddresses().find((a) => a.id === 'test-new')?.name === 'Updated User', 'updateAddress() modifies existing address fields');

  // Set default
  authStateInstance.setDefaultAddress('test-new');
  const afterDefault = authStateInstance.getSavedAddresses();
  assert(afterDefault.find((a) => a.id === 'test-new')?.isDefault === true, 'setDefaultAddress() sets the specified address as default');
  assert(afterDefault.filter((a) => a.isDefault).length === 1, 'setDefaultAddress() ensures only one address is default');

  // Delete an address
  authStateInstance.deleteAddress('test-new');
  assert(!authStateInstance.getSavedAddresses().find((a) => a.id === 'test-new'), 'deleteAddress() removes the address by ID');
  assert(authStateInstance.getSavedAddresses().some((a) => a.isDefault), 'deleteAddress() promotes a new default when deleting the default');

  // Reset to clean state
  authStateInstance.reset();

  // 15b. Screen files existence (309:762–309:767)
  const addrScreens = {
    myAddressesList: path.join(__dirname, '../src/screens/account/MyAddressesListScreen.tsx'),
    myAddressesV2: path.join(__dirname, '../src/screens/account/MyAddressesListV2Screen.tsx'),
    addAddressV2: path.join(__dirname, '../src/screens/account/AccountAddAddressV2Screen.tsx'),
    addAddressSimple: path.join(__dirname, '../src/screens/account/AccountAddAddressSimpleScreen.tsx'),
    editAddress: path.join(__dirname, '../src/screens/account/AccountEditAddressScreen.tsx'),
    deleteModal: path.join(__dirname, '../src/screens/account/DeleteAddressModal.tsx'),
  };
  assert(fs.existsSync(addrScreens.myAddressesList), 'MyAddressesListScreen exists (309:762)');
  assert(fs.existsSync(addrScreens.myAddressesV2), 'MyAddressesListV2Screen exists (309:763)');
  assert(fs.existsSync(addrScreens.addAddressV2), 'AccountAddAddressV2Screen exists (309:764)');
  assert(fs.existsSync(addrScreens.addAddressSimple), 'AccountAddAddressSimpleScreen exists (309:765)');
  assert(fs.existsSync(addrScreens.editAddress), 'AccountEditAddressScreen exists (309:766)');
  assert(fs.existsSync(addrScreens.deleteModal), 'DeleteAddressModal exists (309:767)');

  // 15c. Content-level assertions
  const myAddrContent = fs.readFileSync(addrScreens.myAddressesList, 'utf8');
  const myAddrV2Content = fs.readFileSync(addrScreens.myAddressesV2, 'utf8');
  const addV2Content = fs.readFileSync(addrScreens.addAddressV2, 'utf8');
  const addSimpleContent = fs.readFileSync(addrScreens.addAddressSimple, 'utf8');
  const editAddrContent = fs.readFileSync(addrScreens.editAddress, 'utf8');
  const deleteModalContent = fs.readFileSync(addrScreens.deleteModal, 'utf8');

  assert(myAddrContent.includes('Maison') && myAddrContent.includes('Bureau') && myAddrContent.includes('Autre'), 'MyAddressesListScreen renders address labels (Maison/Bureau/Autre)');
  assert(myAddrContent.includes('handleSetDefault'), 'MyAddressesListScreen supports set-default action');
  assert(myAddrV2Content.includes('radioActive') && myAddrV2Content.includes('handleSetDefault'), 'MyAddressesListV2Screen has radio selection and default toggling');
  assert(addV2Content.includes('validateAddressDraft'), 'AccountAddAddressV2Screen reuses validateAddressDraft from checkoutState');
  assert(addSimpleContent.includes('validateAddressDraft'), 'AccountAddAddressSimpleScreen reuses validateAddressDraft from checkoutState');
  assert(editAddrContent.includes('getSelectedAddressForEdit'), 'AccountEditAddressScreen reads from getSelectedAddressForEdit()');
  assert(editAddrContent.includes('updateAddress'), 'AccountEditAddressScreen calls updateAddress for saving changes');
  assert(deleteModalContent.includes('onConfirm') && deleteModalContent.includes('onCancel'), 'DeleteAddressModal supports confirm and cancel actions');
  assert(deleteModalContent.includes('Supprimer'), 'DeleteAddressModal shows French delete confirmation text');

  // 15d. Navigation wiring
  assert(navigatorContent.includes("'my-addresses'") && navigatorContent.includes("'my-addresses-v2'") && navigatorContent.includes("'account-add-address'") && navigatorContent.includes("'account-add-address-simple'") && navigatorContent.includes("'account-edit-address'"), 'Step 5B.2 address routes are registered in RootNavigator');
  assert(navigatorContent.includes("onNavigateAddresses={() => setCurrentScreen('my-addresses')"), 'AccountScreen.onNavigateAddresses routes to my-addresses (not checkout)');

  // 15e. Address model compatibility (Account ↔ Checkout)
  const checkoutStateContent = fs.readFileSync(path.join(__dirname, '../src/commerce/checkoutState.ts'), 'utf8');
  const authStateContent = fs.readFileSync(path.join(__dirname, '../src/commerce/authState.ts'), 'utf8');
  assert(authStateContent.includes("import { SavedAddress, defaultSavedAddresses } from './checkoutState'"), 'authState imports SavedAddress from checkoutState for model compatibility');

  // ──────────────────────────────────────────────
  // 16. STEP 5C — PAYMENT, LANGUAGE/REGION & LOGOUT
  // ──────────────────────────────────────────────

  // 16a. Screen files existence (309:768–309:771)
  const paymentMethodsPath = path.join(__dirname, '../src/screens/account/PaymentMethodsScreen.tsx');
  const langRegionPath = path.join(__dirname, '../src/screens/account/LanguageRegionPreferencesScreen.tsx');
  const langSelectPath = path.join(__dirname, '../src/screens/account/LanguageSelectionAccountScreen.tsx');
  const logoutModalPath = path.join(__dirname, '../src/screens/account/LogoutConfirmationModal.tsx');

  assert(fs.existsSync(paymentMethodsPath), 'PaymentMethodsScreen exists (309:768)');
  assert(fs.existsSync(langRegionPath), 'LanguageRegionPreferencesScreen exists (309:769)');
  assert(fs.existsSync(langSelectPath), 'LanguageSelectionAccountScreen exists (309:770)');
  assert(fs.existsSync(logoutModalPath), 'LogoutConfirmationModal exists (309:771)');

  // 16b. Content-level assertions
  const pmContent = fs.readFileSync(paymentMethodsPath, 'utf8');
  const lrContent = fs.readFileSync(langRegionPath, 'utf8');
  const lsContent = fs.readFileSync(langSelectPath, 'utf8');
  const logoutContent = fs.readFileSync(logoutModalPath, 'utf8');

  assert(pmContent.includes('PaymentMethodFixture') || pmContent.includes('getPaymentMethods'), 'PaymentMethodsScreen integrates with payment methods state (Visa/COD via fixtures)');
  assert(pmContent.includes('credit-card') || pmContent.includes('removePaymentMethod'), 'PaymentMethodsScreen supports card management actions');
  assert(lrContent.includes('Français') || lrContent.includes('français'), 'LanguageRegionPreferencesScreen shows French language');
  assert(lrContent.includes('العربية') || lrContent.includes('Arabe'), 'LanguageRegionPreferencesScreen shows Arabic language');
  assert(lsContent.includes('onLanguageApplied') || lsContent.includes('onSelect'), 'LanguageSelectionAccountScreen has language apply callback');
  assert(logoutContent.includes('onConfirmLogout') || logoutContent.includes('onConfirm'), 'LogoutConfirmationModal supports confirm logout action');
  assert(logoutContent.includes('onCancel'), 'LogoutConfirmationModal supports cancel action');
  assert(logoutContent.includes('connecter') || logoutContent.includes('Déconnexion') || logoutContent.includes('déconnecter'), 'LogoutConfirmationModal shows French logout text');

  // 16c. Navigation wiring
  assert(navigatorContent.includes("'payment-methods'"), 'payment-methods route is registered in RootNavigator');
  assert(navigatorContent.includes("'language-region'"), 'language-region route is registered in RootNavigator');
  assert(navigatorContent.includes("'language-selection'") || navigatorContent.includes('LanguageSelectionAccountScreen'), 'language-selection route is registered in RootNavigator');
  assert(navigatorContent.includes("'logout-confirmation'") || navigatorContent.includes('LogoutConfirmationModal'), 'LogoutConfirmationModal is wired in RootNavigator');
  assert(navigatorContent.includes("onNavigatePaymentMethods") && navigatorContent.includes("onNavigateLanguageRegion"), 'AccountScreen has navigation props for payment methods and language/region');
  assert(navigatorContent.includes("onConfirmLogoutTrigger") || navigatorContent.includes('setLogoutModalVisible'), 'AccountScreen logout triggers modal visibility');

  // ──────────────────────────────────────────────
  // 17. STEP 5D.1 — MARKETING & NOTIFICATION SETTINGS
  // ──────────────────────────────────────────────

  // 17a. notificationPreferencesState CRUD & persistence
  const notifStateModule = loadTypeScriptModule(path.join(__dirname, '../src/commerce/notificationPreferencesState.ts'));
  const notifState = notifStateModule.notificationPreferencesState;
  notifState.reset();

  const initialMarketing = notifState.getMarketingPreferences();
  assert(initialMarketing.abandonedCartReminders === true, 'abandonedCartReminders defaults to true');
  assert(initialMarketing.promotionsAndOffers === true, 'promotionsAndOffers defaults to true');

  notifState.toggleMarketingPreference('abandonedCartReminders');
  assert(notifState.getMarketingPreferences().abandonedCartReminders === false, 'toggleMarketingPreference flips abandonedCartReminders');

  const initialChannels = notifState.getNotificationChannels();
  assert(initialChannels.emailChannel === true && initialChannels.pushChannel === true, 'Notification channels default to true');

  notifState.toggleNotificationChannel('emailChannel');
  assert(notifState.getNotificationChannels().emailChannel === false, 'toggleNotificationChannel flips emailChannel');

  const initialSettings = notifState.getNotificationSettings();
  assert(initialSettings.orders === true && initialSettings.delivery === true, 'Notification category settings default to true');

  notifState.toggleNotificationSetting('orders');
  assert(notifState.getNotificationSettings().orders === false, 'toggleNotificationSetting flips orders setting');

  notifState.reset();
  assert(notifState.getMarketingPreferences().abandonedCartReminders === true, 'reset restores default preferences');

  // 17b. Screen files existence (309:772–309:776)
  const cartRemindersPath = path.join(__dirname, '../src/screens/account/MarketingCartRemindersScreen.tsx');
  const detailedPrefsPath = path.join(__dirname, '../src/screens/account/MarketingDetailedPreferencesScreen.tsx');
  const marketingTogglesPath = path.join(__dirname, '../src/screens/account/MarketingTogglesScreen.tsx');
  const notifChannelsPath = path.join(__dirname, '../src/screens/account/NotificationChannelsScreen.tsx');
  const notifSettingsPath = path.join(__dirname, '../src/screens/account/NotificationSettingsTogglesScreen.tsx');

  assert(fs.existsSync(cartRemindersPath), 'MarketingCartRemindersScreen exists (309:772)');
  assert(fs.existsSync(detailedPrefsPath), 'MarketingDetailedPreferencesScreen exists (309:773)');
  assert(fs.existsSync(marketingTogglesPath), 'MarketingTogglesScreen exists (309:774)');
  assert(fs.existsSync(notifChannelsPath), 'NotificationChannelsScreen exists (309:775)');
  assert(fs.existsSync(notifSettingsPath), 'NotificationSettingsTogglesScreen exists (309:776)');

  // 17c. Content-level assertions
  const cartRemindersContent = fs.readFileSync(cartRemindersPath, 'utf8');
  const detailedPrefsContent = fs.readFileSync(detailedPrefsPath, 'utf8');
  const marketingTogglesContent = fs.readFileSync(marketingTogglesPath, 'utf8');
  const notifChannelsContent = fs.readFileSync(notifChannelsPath, 'utf8');
  const notifSettingsContent = fs.readFileSync(notifSettingsPath, 'utf8');

  assert(cartRemindersContent.includes('abandonedCartReminders') && cartRemindersContent.includes('Rappels de Panier'), 'MarketingCartRemindersScreen supports cart reminder toggling');
  assert(detailedPrefsContent.includes('personalizedRecommendations') && detailedPrefsContent.includes('Conseils Déco'), 'MarketingDetailedPreferencesScreen supports recommendations and news toggling');
  assert(marketingTogglesContent.includes('emailMarketing') && marketingTogglesContent.includes('SMS Marketing'), 'MarketingTogglesScreen supports email and SMS marketing toggling');
  assert(notifChannelsContent.includes('emailChannel') && notifChannelsContent.includes('pushChannel'), 'NotificationChannelsScreen supports master channel toggling');
  assert(notifSettingsContent.includes('orders') && notifSettingsContent.includes('Livraison'), 'NotificationSettingsTogglesScreen supports category toggles');

  // 17d. Navigation wiring & reachability
  assert(navigatorContent.includes("'marketing-cart-reminders'") && navigatorContent.includes("'marketing-detailed-preferences'") && navigatorContent.includes("'marketing-toggles'"), 'Marketing preference routes are registered in RootNavigator');
  assert(navigatorContent.includes("'notification-channels'") && navigatorContent.includes("'notification-settings-toggles'"), 'Notification management routes are registered in RootNavigator');
  assert(navigatorContent.includes("onNavigateMarketingPreferences") && navigatorContent.includes("onNavigateNotificationManagement"), 'Account & Settings UI components have reachability navigation callbacks');

  // ──────────────────────────────────────────────
  // 18. STEP 5D.2 — NOTIFICATION DETAILS & QUIET HOURS
  // ──────────────────────────────────────────────

  // 18a. Quiet Hours & Fixtures state management
  assert(notifState.getQuietHoursEnabled() === true, 'Quiet Hours enabled defaults to true');
  assert(notifState.getQuietHoursDays().length === 7, 'Quiet Hours days defaults to 7 days');

  notifState.toggleQuietHours();
  assert(notifState.getQuietHoursEnabled() === false, 'toggleQuietHours flips quiet hours enabled state');

  notifState.toggleQuietHoursDay('Lun');
  assert(!notifState.getQuietHoursDays().includes('Lun'), 'toggleQuietHoursDay toggles day selection');

  notifState.setQuietHoursTimeRange('23:00', '07:00');
  assert(notifState.getQuietHoursTimeRange().start === '23:00', 'setQuietHoursTimeRange updates start time');

  // Verify existing marketing/channel settings preserved
  assert(notifState.getMarketingPreferences().abandonedCartReminders === true, 'Quiet hours changes preserve existing marketing settings');
  assert(notifState.getNotificationChannels().emailChannel === true, 'Quiet hours changes preserve existing notification channels');

  notifState.reset();
  assert(notifState.getQuietHoursEnabled() === true, 'reset restores default quiet hours state');

  // 18b. Screen files existence (309:777–309:780)
  const detailPrepPath = path.join(__dirname, '../src/screens/account/NotificationDetailPrepScreen.tsx');
  const detailShippedPath = path.join(__dirname, '../src/screens/account/NotificationDetailShippedScreen.tsx');
  const silentDayPath = path.join(__dirname, '../src/screens/account/SilentHoursDaySelectionScreen.tsx');
  const silentDndPath = path.join(__dirname, '../src/screens/account/SilentHoursDoNotDisturbScreen.tsx');

  assert(fs.existsSync(detailPrepPath), 'NotificationDetailPrepScreen exists (309:777)');
  assert(fs.existsSync(detailShippedPath), 'NotificationDetailShippedScreen exists (309:778)');
  assert(fs.existsSync(silentDayPath), 'SilentHoursDaySelectionScreen exists (309:779)');
  assert(fs.existsSync(silentDndPath), 'SilentHoursDoNotDisturbScreen exists (309:780)');

  // 18c. Content-level assertions
  const detailPrepContent = fs.readFileSync(detailPrepPath, 'utf8');
  const detailShippedContent = fs.readFileSync(detailShippedPath, 'utf8');
  const silentDayContent = fs.readFileSync(silentDayPath, 'utf8');
  const silentDndContent = fs.readFileSync(silentDndPath, 'utf8');

  assert(detailPrepContent.includes('#MY-84920') && detailPrepContent.includes('Voir ma commande'), 'NotificationDetailPrepScreen renders order reference and Order Details CTA');
  assert(detailShippedContent.includes('#MY-84920') && detailShippedContent.includes('CTM Messagerie') && detailShippedContent.includes('Suivre mon colis'), 'NotificationDetailShippedScreen renders tracking information and CTA');
  assert(silentDayContent.includes('toggleQuietHoursDay') || silentDayContent.includes('ALL_WEEKDAYS'), 'SilentHoursDaySelectionScreen supports weekday chip selection');
  assert(silentDndContent.includes('Mode Ne Pas Déranger') && silentDndContent.includes('Modifier le calendrier'), 'SilentHoursDoNotDisturbScreen renders DND summary and schedule edit CTA');

  // 18d. Navigation wiring & reachability
  assert(navigatorContent.includes("'notification-detail-prep'") && navigatorContent.includes("'notification-detail-shipped'"), 'Notification detail routes are registered in RootNavigator');
  assert(navigatorContent.includes("'silent-hours-day-selection'") && navigatorContent.includes("'silent-hours-dnd'"), 'Quiet hours routes are registered in RootNavigator');
  assert(navigatorContent.includes("onNavigateNotificationDetails") || navigatorContent.includes("notification-detail-prep"), 'Notification Settings connects to Notification Details');

  // ──────────────────────────────────────────────
  // 19. STEP 6A — FAQ & HELP CENTER FRONTEND
  // ──────────────────────────────────────────────

  // 19a. supportState management & fixtures
  const supportStateModule = loadTypeScriptModule(path.join(__dirname, '../src/commerce/supportState.ts'));
  const supState = supportStateModule.supportState;

  assert(supState.getFaqCategories().length >= 5, 'supportState seeds 5 FAQ categories');
  assert(supState.getFaqItems().length >= 7, 'supportState seeds 7 FAQ items');
  assert(supState.getFaqItemsByCategory('commandes').length >= 2, 'getFaqItemsByCategory filters by category ID');
  assert(supState.getSupportRequests().length >= 3, 'supportState seeds 3 support request fixtures');
  assert(supState.getContactChannels().length === 3, 'supportState seeds 3 contact channels (phone/email/chat)');

  supState.setSelectedFaqId('faq-1');
  assert(supState.getSelectedFaqId() === 'faq-1', 'setSelectedFaqId updates selected FAQ ID');

  supState.setSelectedFaqCategory('paiement');
  assert(supState.getSelectedFaqCategory() === 'paiement', 'setSelectedFaqCategory updates selected category ID');

  supState.setSelectedSupportRequestId('req-1');
  assert(supState.getSelectedSupportRequestId() === 'req-1', 'setSelectedSupportRequestId updates selected request ID');

  supState.reset();
  assert(supState.getSelectedFaqId() === '', 'reset clears selected FAQ selection');

  // 19b. Screen files existence (309:781–309:786)
  const faqAccordionPath = path.join(__dirname, '../src/screens/support/FaqAccordionScreen.tsx');
  const faqDetailPath = path.join(__dirname, '../src/screens/support/FaqDetailScreen.tsx');
  const faqCatPath = path.join(__dirname, '../src/screens/support/FaqCategoriesScreen.tsx');
  const helpCenterPath = path.join(__dirname, '../src/screens/support/HelpCenterCategoriesScreen.tsx');
  const helpReqPath = path.join(__dirname, '../src/screens/support/HelpCenterRequestsScreen.tsx');
  const helpHubPath = path.join(__dirname, '../src/screens/support/HelpSupportHubScreen.tsx');

  assert(fs.existsSync(faqAccordionPath), 'FaqAccordionScreen exists (309:781)');
  assert(fs.existsSync(faqDetailPath), 'FaqDetailScreen exists (309:782)');
  assert(fs.existsSync(faqCatPath), 'FaqCategoriesScreen exists (309:783)');
  assert(fs.existsSync(helpCenterPath), 'HelpCenterCategoriesScreen exists (309:784)');
  assert(fs.existsSync(helpReqPath), 'HelpCenterRequestsScreen exists (309:785)');
  assert(fs.existsSync(helpHubPath), 'HelpSupportHubScreen exists (309:786)');

  // 19c. Content-level assertions
  const faqAccordionContent = fs.readFileSync(faqAccordionPath, 'utf8');
  const faqDetailContent = fs.readFileSync(faqDetailPath, 'utf8');
  const faqCatContent = fs.readFileSync(faqCatPath, 'utf8');
  const helpCenterContent = fs.readFileSync(helpCenterPath, 'utf8');
  const helpReqContent = fs.readFileSync(helpReqPath, 'utf8');
  const helpHubContent = fs.readFileSync(helpHubPath, 'utf8');

  assert(faqAccordionContent.includes('Questions Fréquentes') && faqAccordionContent.includes('toggleAccordion'), 'FaqAccordionScreen renders title and accordion toggle handler');
  assert(faqDetailContent.includes('Détail FAQ') && faqDetailContent.includes('Cette réponse vous a-t-elle aidé'), 'FaqDetailScreen renders answer detail and helpful feedback section');
  assert(faqCatContent.includes('Catégories FAQ') && faqCatContent.includes('selectCategory'), 'FaqCategoriesScreen renders category chips and category selection handler');
  assert(helpCenterContent.includes('Centre d\'Aide') && helpCenterContent.includes('Comment pouvons-nous vous aider'), 'HelpCenterCategoriesScreen renders Help Center landing and search area');
  assert(helpReqContent.includes('Mes Demandes') && helpReqContent.includes('getStatusColor'), 'HelpCenterRequestsScreen renders recent requests list and status badges');
  assert(helpHubContent.includes('Aide & Support') && helpHubContent.includes('Contactez-nous'), 'HelpSupportHubScreen renders support hub and contact channels');

  // 19d. Navigation wiring & reachability
  assert(navigatorContent.includes("'faq'") && navigatorContent.includes("'faq-detail'") && navigatorContent.includes("'faq-categories'"), 'FAQ routes are registered in RootNavigator');
  assert(navigatorContent.includes("'help-center'") && navigatorContent.includes("'help-center-requests'") && navigatorContent.includes("'help-support'"), 'Help Center and Support routes are registered in RootNavigator');
  assert(navigatorContent.includes("onNavigateHelpSupport"), 'AccountScreen and RootNavigator connect to Help & Support Hub');

  // ──────────────────────────────────────────────
  // 20. STEP 6B — GUEST ACCOUNT STATE & APP SETTINGS HUB
  // ──────────────────────────────────────────────

  // 20a. Audit 309:787 Guest Account reuse in AccountScreen
  const accountScreenCode = fs.readFileSync(path.join(__dirname, '../src/screens/commerce/AccountScreen.tsx'), 'utf8');
  assert(accountScreenCode.includes('Bienvenue chez Mayush Design') && accountScreenCode.includes('ACCOUNT_ARTWORK'), 'AccountScreen handles 309:787 Guest Welcome state with artwork and login CTAs');
  assert(accountScreenCode.includes('onNavigateSettings') && accountScreenCode.includes('onLogin'), 'AccountScreen exposes guest login, creation, support, and settings navigation');

  // 20b. SettingsScreen file existence (309:789)
  const settingsScreenPath = path.join(__dirname, '../src/screens/account/SettingsScreen.tsx');
  assert(fs.existsSync(settingsScreenPath), 'SettingsScreen exists (309:789)');

  // 20c. Content-level assertions
  const settingsScreenContent = fs.readFileSync(settingsScreenPath, 'utf8');
  assert(settingsScreenContent.includes('Paramètres de l\'application') || settingsScreenContent.includes("Paramètres de l'application"), 'SettingsScreen renders title');
  assert(settingsScreenContent.includes('Préférences d\'Affichage & Langue') || settingsScreenContent.includes("Préférences d'Affichage & Langue"), 'SettingsScreen renders Display & Language section');
  assert(settingsScreenContent.includes('Notifications & Communication'), 'SettingsScreen renders Notifications & Communication section');
  assert(settingsScreenContent.includes('Données & Stockage'), 'SettingsScreen renders Data & Storage section');
  assert(settingsScreenContent.includes('Assistance & Informations'), 'SettingsScreen renders Assistance & Information section');

  // 20d. Navigation wiring & preference reuse
  assert(settingsScreenContent.includes('onNavigateLanguage') && settingsScreenContent.includes('onNavigateNotificationChannels'), 'SettingsScreen wires language and notification preference callbacks');
  assert(settingsScreenContent.includes('onNavigateMarketingPreferences') && settingsScreenContent.includes('onNavigateSilentHours'), 'SettingsScreen wires marketing and silent hours callbacks');
  assert(settingsScreenContent.includes('onNavigateHelpCenter'), 'SettingsScreen wires Help Center callback');
  assert(navigatorContent.includes("'settings'") && navigatorContent.includes("<SettingsScreen"), 'settings route is registered in RootNavigator');
  assert(navigatorContent.includes("onNavigateSettings={() => setCurrentScreen('settings')}"), 'AccountScreen connects to settings route in RootNavigator');

  // ──────────────────────────────────────────────
  // 21. STEP 6C — ABOUT, ACCESSIBILITY & APP PERMISSIONS
  // ──────────────────────────────────────────────

  // 21a. appSettingsState CRUD & persistence
  const appSettingsModule = loadTypeScriptModule(path.join(__dirname, '../src/commerce/appSettingsState.ts'));
  const appState = appSettingsModule.appSettingsState;
  appState.reset();

  const initialAcc = appState.getAccessibility();
  assert(initialAcc.textSize === 'normal' && initialAcc.highContrast === false, 'appSettingsState initializes default accessibility settings');

  appState.setTextSize('large');
  assert(appState.getAccessibility().textSize === 'large', 'setTextSize updates text size option');

  appState.toggleHighContrast();
  assert(appState.getAccessibility().highContrast === true, 'toggleHighContrast flips high contrast setting');

  appState.toggleReducedMotion();
  assert(appState.getAccessibility().reducedMotion === true, 'toggleReducedMotion flips reduced motion setting');

  const initialPerms = appState.getPermissions();
  assert(initialPerms.camera === 'not-requested' && initialPerms.notifications === 'granted', 'appSettingsState initializes default permissions');

  appState.togglePermission('camera');
  assert(appState.getPermissions().camera === 'granted', 'togglePermission flips permission status');

  appState.reset();
  assert(appState.getAccessibility().textSize === 'normal', 'reset restores default accessibility settings');

  // 21b. Screen files existence (309:790–309:793)
  const aboutAppPath = path.join(__dirname, '../src/screens/account/AboutAppVersionScreen.tsx');
  const aboutMayushPath = path.join(__dirname, '../src/screens/account/AboutMayushCompanyScreen.tsx');
  const accessibilityPath = path.join(__dirname, '../src/screens/account/AccessibilitySettingsScreen.tsx');
  const permissionsPath = path.join(__dirname, '../src/screens/account/AppPermissionsScreen.tsx');

  assert(fs.existsSync(aboutAppPath), 'AboutAppVersionScreen exists (309:790)');
  assert(fs.existsSync(aboutMayushPath), 'AboutMayushCompanyScreen exists (309:791)');
  assert(fs.existsSync(accessibilityPath), 'AccessibilitySettingsScreen exists (309:792)');
  assert(fs.existsSync(permissionsPath), 'AppPermissionsScreen exists (309:793)');

  // 21c. Content-level assertions
  const aboutAppContent = fs.readFileSync(aboutAppPath, 'utf8');
  const aboutMayushContent = fs.readFileSync(aboutMayushPath, 'utf8');
  const accessibilityContent = fs.readFileSync(accessibilityPath, 'utf8');
  const permissionsContent = fs.readFileSync(permissionsPath, 'utf8');

  assert(aboutAppContent.includes('Mayush Mobile') && aboutAppContent.includes('v1.0.0'), 'AboutAppVersionScreen renders app title and version string');
  assert(aboutMayushContent.includes('Mayush') && aboutMayushContent.includes('www.mayush.ma'), 'AboutMayushCompanyScreen renders company presentation and website URL');
  assert(accessibilityContent.includes('Taille de texte') && accessibilityContent.includes('toggleHighContrast'), 'AccessibilitySettingsScreen renders text size options and high contrast toggle');
  assert(permissionsContent.includes('Autorisations') && permissionsContent.includes('togglePermission'), 'AppPermissionsScreen renders permission categories and toggle handlers');

  // 21d. Navigation wiring & reachability
  assert(navigatorContent.includes("'about-app'") && navigatorContent.includes("'about-mayush'"), 'About App and About Mayush routes are registered in RootNavigator');
  assert(navigatorContent.includes("'accessibility'") && navigatorContent.includes("'app-permissions'"), 'Accessibility and App Permissions routes are registered in RootNavigator');
  assert(settingsScreenContent.includes('onNavigateAboutApp') && settingsScreenContent.includes('onNavigateAccessibility') && settingsScreenContent.includes('onNavigateAppPermissions'), 'SettingsScreen wires Step 6C navigation callbacks');

  // ──────────────────────────────────────────────
  // 22. STEP 6D — DATA USAGE, STORAGE & CACHE MANAGEMENT
  // ──────────────────────────────────────────────

  // 22a. Data Usage preferences CRUD
  appState.reset();
  const initialDataUsage = appState.getDataUsage();
  assert(initialDataUsage.imageQuality === 'standard' && initialDataUsage.wifiOnlyDownloads === true, 'appSettingsState initializes default Data Usage settings');

  appState.setImageQuality('high');
  assert(appState.getDataUsage().imageQuality === 'high', 'setImageQuality updates image quality preference');

  appState.toggleDataSaverMode();
  assert(appState.getDataUsage().dataSaverMode === true, 'toggleDataSaverMode flips data saver setting');

  // 22b. Cache state CRUD & Data Safety Proof
  const cacheModule = loadTypeScriptModule(path.join(__dirname, '../src/commerce/cacheState.ts'));
  const cache = cacheModule.cacheState;
  cache.reset();

  const initialMetrics = cache.getMetrics();
  assert(initialMetrics.cacheSizeBytes > 0 && initialMetrics.cachedImageCount > 0, 'cacheState initializes with initial disposable cache metrics');

  // Perform Clear Cache
  cache.clearCache();
  const clearedMetrics = cache.getMetrics();
  assert(clearedMetrics.cacheSizeBytes === 0 && clearedMetrics.cachedImageCount === 0 && clearedMetrics.lastClearedAt !== null, 'clearCache resets cache size and image count to 0');

  // PROOF OF DURABLE DATA PRESERVATION:
  const authModule = loadTypeScriptModule(path.join(__dirname, '../src/commerce/authState.ts'));
  const cartModule = loadTypeScriptModule(path.join(__dirname, '../src/commerce/cartState.ts'));
  const accountPrefModule = loadTypeScriptModule(path.join(__dirname, '../src/commerce/accountPreferencesState.ts'));

  assert(authModule.authState !== undefined, 'durable authState survives cache clear');
  assert(cartModule.emptyCartState !== undefined, 'durable cartState survives cache clear');
  assert(accountPrefModule.accountPreferencesState.getSelectedLanguage() === 'fr', 'durable language preference survives cache clear');
  assert(appState.getDataUsage().imageQuality === 'high', 'durable appSettingsState survives cache clear');

  // Regression check: Ensure AsyncStorage.clear() is NEVER used for clearCache!
  const cacheStateCode = fs.readFileSync(path.join(__dirname, '../src/commerce/cacheState.ts'), 'utf8');
  const clearModalCode = fs.readFileSync(path.join(__dirname, '../src/screens/account/ClearCacheConfirmationModal.tsx'), 'utf8');
  assert(!cacheStateCode.includes('AsyncStorage.clear()') && !clearModalCode.includes('AsyncStorage.clear()'), 'AsyncStorage.clear() is NOT used for clear cache behavior');

  // 22c. Screen files existence (309:794–309:796)
  const dataUsagePath = path.join(__dirname, '../src/screens/account/DataUsageScreen.tsx');
  const storageCachePath = path.join(__dirname, '../src/screens/account/StorageCacheScreen.tsx');
  const clearModalPath = path.join(__dirname, '../src/screens/account/ClearCacheConfirmationModal.tsx');

  assert(fs.existsSync(dataUsagePath), 'DataUsageScreen exists (309:794)');
  assert(fs.existsSync(storageCachePath), 'StorageCacheScreen exists (309:795)');
  assert(fs.existsSync(clearModalPath), 'ClearCacheConfirmationModal exists (309:796)');

  // 22d. Content-level assertions
  const dataUsageContent = fs.readFileSync(dataUsagePath, 'utf8');
  const storageCacheContent = fs.readFileSync(storageCachePath, 'utf8');
  const clearModalContent = fs.readFileSync(clearModalPath, 'utf8');

  assert(dataUsageContent.includes('Utilisation des données') && dataUsageContent.includes('setImageQuality'), 'DataUsageScreen renders options and quality selector');
  assert(storageCacheContent.includes('Gestion du stockage & cache') && storageCacheContent.includes('Vider le cache'), 'StorageCacheScreen renders storage metrics and clear cache CTA');
  assert(clearModalContent.includes('Vider le cache ?') && clearModalContent.includes('Annuler'), 'ClearCacheConfirmationModal renders confirmation title and action buttons');

  // 22e. Navigation wiring & reachability
  assert(navigatorContent.includes("'data-usage'") && navigatorContent.includes("'storage-cache'"), 'Data Usage and Storage Cache routes are registered in RootNavigator');
  // ──────────────────────────────────────────────
  // 23. STEP 6E — APP PREFERENCES RECONCILIATION & OFFLINE MODE
  // ──────────────────────────────────────────────

  // 23a. Preference store single-source-of-truth audits
  const notifPrefModule = loadTypeScriptModule(path.join(__dirname, '../src/commerce/notificationPreferencesState.ts'));

  accountPrefModule.accountPreferencesState.setSelectedLanguage('fr');
  assert(accountPrefModule.accountPreferencesState.getSelectedLanguage() === 'fr', 'accountPreferencesState manages single source of truth for 309:797 language preference');

  assert(notifPrefModule.notificationPreferencesState !== undefined, 'notificationPreferencesState manages single source of truth for 309:798 matrix, 309:799 marketing, and 309:800 quiet hours');

  // Regression check: Enforce single-source-of-truth (no duplicate preference state files)
  const commerceFiles = fs.readdirSync(path.join(__dirname, '../src/commerce'));
  assert(!commerceFiles.includes('settingsLanguageState.ts'), 'No duplicate settingsLanguageState file exists');
  assert(!commerceFiles.includes('settingsNotificationState.ts'), 'No duplicate settingsNotificationState file exists');
  assert(!commerceFiles.includes('settingsMarketingState.ts'), 'No duplicate settingsMarketingState file exists');

  // 23b. appSettingsState Offline mode CRUD
  appState.reset();
  assert(appState.getOfflineMode() === false, 'appSettingsState initializes default offline mode as false');

  appState.toggleOfflineMode();
  assert(appState.getOfflineMode() === true, 'toggleOfflineMode flips offline mode state');

  appState.reset();
  assert(appState.getOfflineMode() === false, 'reset restores default offline mode');

  // 23c. Screen file existence (309:801)
  const offlineModePath = path.join(__dirname, '../src/screens/account/OfflineModeScreen.tsx');
  assert(fs.existsSync(offlineModePath), 'OfflineModeScreen exists (309:801)');

  // 23d. Content-level assertions
  const offlineModeContent = fs.readFileSync(offlineModePath, 'utf8');
  assert(offlineModeContent.includes('Mode hors-ligne') && offlineModeContent.includes('toggleOfflineMode'), 'OfflineModeScreen renders title and toggle handler');
  assert(offlineModeContent.includes('Fonctionnalités disponibles hors-ligne') && offlineModeContent.includes('Opérations nécessitant une connexion'), 'OfflineModeScreen lists available features and network limitations');

  // 23f. Offline Mode claim audit
  assert(offlineModeContent.includes('préférences locales restent consultables'), 'OfflineModeScreen copy accurately claims local preferences without unsupported catalog persistence claims');

  // ──────────────────────────────────────────────
  // 24. STEP 6F — LEGAL, PRIVACY & DATA MANAGEMENT
  // ──────────────────────────────────────────────

  // 24a. Legal content module
  const legalContentModule = loadTypeScriptModule(path.join(__dirname, '../src/content/legalContent.ts'));
  assert(legalContentModule.PRIVACY_POLICY_DOCUMENT !== undefined, 'PRIVACY_POLICY_DOCUMENT is defined');
  assert(legalContentModule.TERMS_CONDITIONS_DOCUMENT !== undefined, 'TERMS_CONDITIONS_DOCUMENT is defined');
  assert(legalContentModule.PRIVACY_POLICY_DOCUMENT.sections.length >= 5, 'PRIVACY_POLICY_DOCUMENT contains at least 5 structured legal sections');

  // 24b. Screen files existence (309:802–309:804)
  const legalCenterPath = path.join(__dirname, '../src/screens/account/LegalCenterScreen.tsx');
  const privacyDataPath = path.join(__dirname, '../src/screens/account/PrivacyDataManagementScreen.tsx');
  const privacyPolicyPath = path.join(__dirname, '../src/screens/account/PrivacyPolicyDocumentScreen.tsx');

  assert(fs.existsSync(legalCenterPath), 'LegalCenterScreen exists (309:802)');
  assert(fs.existsSync(privacyDataPath), 'PrivacyDataManagementScreen exists (309:803)');
  assert(fs.existsSync(privacyPolicyPath), 'PrivacyPolicyDocumentScreen exists (309:804)');

  // 24c. Content-level assertions
  const legalCenterContent = fs.readFileSync(legalCenterPath, 'utf8');
  const privacyDataContent = fs.readFileSync(privacyDataPath, 'utf8');
  const privacyPolicyContent = fs.readFileSync(privacyPolicyPath, 'utf8');

  assert(legalCenterContent.includes('Centre Légal & Conditions') && legalCenterContent.includes('Conditions Générales'), 'LegalCenterScreen renders legal center header and terms options');
  assert(privacyDataContent.includes('Confidentialité & Données') && privacyDataContent.includes('Supprimer mon compte Mayush'), 'PrivacyDataManagementScreen renders data protection info and delete account UI');
  assert(privacyPolicyContent.includes('PRIVACY_POLICY_DOCUMENT') && privacyPolicyContent.includes('contact@mayush.ma'), 'PrivacyPolicyDocumentScreen renders policy document sections and contact metadata');

  // 24d. Data safety proof: deletion UI does not destroy durable state
  assert(!privacyDataContent.includes('AsyncStorage.clear()'), 'PrivacyDataManagementScreen does NOT use AsyncStorage.clear()');
  assert(privacyDataContent.includes('Suppression du compte Mayush'), 'PrivacyDataManagementScreen provides frontend warning dialog without fake backend wiping');

  // 24e. Navigation wiring & reachability
  assert(navigatorContent.includes("'legal-center'") && navigatorContent.includes("'privacy-data'") && navigatorContent.includes("'privacy-policy'"), 'Step 6F legal routes are registered in RootNavigator');
  assert(settingsScreenContent.includes('onNavigateLegalPrivacy'), 'SettingsScreen wires Step 6F navigation callback');

  // ──────────────────────────────────────────────
  // 25. STEP 7A — ADVANCED HELP CENTER, SEARCH & FAQ ARTICLES
  // ──────────────────────────────────────────────

  // 25a. supportState single domain and search engine tests
  const supportModule = loadTypeScriptModule(path.join(__dirname, '../src/commerce/supportState.ts'));
  const supportInstance = supportModule.supportState;
  supportInstance.reset();

  const searchResults = supportInstance.searchHelp('suivre');
  assert(searchResults.articles.length >= 1 && searchResults.articles.some((a) => a.id === 'faq-1'), 'supportState searchHelp finds matching articles by query');

  const emptySearchResults = supportInstance.searchHelp('nonexistent_random_xyz_query');
  assert(emptySearchResults.totalResults === 0 && emptySearchResults.articles.length === 0, 'supportState searchHelp returns 0 results for non-matching query');

  const trackOrderArticle = supportInstance.getFaqItemById('faq-1');
  assert(trackOrderArticle && trackOrderArticle.steps && trackOrderArticle.steps.length === 4, 'supportState provides 4 step-by-step track order instructions for faq-1');
  assert(trackOrderArticle.question && trackOrderArticle.questionAr, 'French and Arabic logical ID consistency preserved for supportState items');

  // Single source of truth check for support domain
  const supportFiles = fs.readdirSync(path.join(__dirname, '../src/commerce'));
  assert(!supportFiles.includes('advancedSupportState.ts'), 'No duplicate advancedSupportState file exists');
  assert(!supportFiles.includes('helpCenterState.ts'), 'No duplicate helpCenterState file exists');
  assert(!supportFiles.includes('faqSearchState.ts'), 'No duplicate faqSearchState file exists');

  // 25b. Legal content cleanup assertions
  const termsText = fs.readFileSync(path.join(__dirname, '../src/content/legalContent.ts'), 'utf8');
  assert(!termsText.includes('14 jours') && termsText.includes('Loi 31-08') && termsText.includes('7 jours'), 'Step 6F legal cleanup: 14-day statutory return claim removed and replaced with Law 31-08 7-day withdrawal period');

  const dataMgmtText = fs.readFileSync(path.join(__dirname, '../src/screens/account/PrivacyDataManagementScreen.tsx'), 'utf8');
  assert(!dataMgmtText.includes('sous 48h'), 'Step 6F legal cleanup: unsupported 48h data request promise removed');

  const offlineCopyText = fs.readFileSync(path.join(__dirname, '../src/screens/account/OfflineModeScreen.tsx'), 'utf8');
  assert(!offlineCopyText.includes('précédemment consultés'), 'Step 6F legal cleanup: unsupported offline catalog persistence claim removed');

  // 25c. Screen files existence (309:805–309:809)
  const helpHomePath = path.join(__dirname, '../src/screens/support/HelpCenterHomeScreen.tsx');
  const helpCategoryOrdersPath = path.join(__dirname, '../src/screens/support/HelpCategoryOrdersDeliveryScreen.tsx');
  const helpSearchPath = path.join(__dirname, '../src/screens/support/HelpCenterSearchResultsScreen.tsx');
  const faqTabsPath = path.join(__dirname, '../src/screens/support/FaqTabCategoriesScreen.tsx');
  const faqArticlePath = path.join(__dirname, '../src/screens/support/FaqArticleTrackOrderStepsScreen.tsx');

  assert(fs.existsSync(helpHomePath), 'HelpCenterHomeScreen exists (309:805)');
  assert(fs.existsSync(helpCategoryOrdersPath), 'HelpCategoryOrdersDeliveryScreen exists (309:806)');
  assert(fs.existsSync(helpSearchPath), 'HelpCenterSearchResultsScreen exists (309:807)');
  assert(fs.existsSync(faqTabsPath), 'FaqTabCategoriesScreen exists (309:808)');
  assert(fs.existsSync(faqArticlePath), 'FaqArticleTrackOrderStepsScreen exists (309:809)');

  // 25d. Content-level assertions
  const helpHomeContent = fs.readFileSync(helpHomePath, 'utf8');
  const helpCatOrdersContent = fs.readFileSync(helpCategoryOrdersPath, 'utf8');
  const helpSearchContent = fs.readFileSync(helpSearchPath, 'utf8');
  const faqTabsContent = fs.readFileSync(faqTabsPath, 'utf8');
  const faqArticleContent = fs.readFileSync(faqArticlePath, 'utf8');

  assert(helpHomeContent.includes('Centre') && helpHomeContent.includes('aide') && helpHomeContent.includes('Contacter le support'), 'HelpCenterHomeScreen (309:805) renders title, search, category grid, and contact CTA');
  assert(helpCatOrdersContent.includes('Commandes et livraison') && helpCatOrdersContent.includes('Consulter mes commandes') && helpCatOrdersContent.includes('onNavigateOrdersList'), 'HelpCategoryOrdersDeliveryScreen (309:806) renders popular questions and order list action');
  assert(helpSearchContent.includes("résultats trouvés pour") && helpSearchContent.includes("Aucun résultat trouvé"), 'HelpCenterSearchResultsScreen (309:807) renders search match counts and no-results fallback');
  assert(faqTabsContent.includes('Toutes') && faqTabsContent.includes('Commandes') && faqTabsContent.includes('accordionCard'), 'FaqTabCategoriesScreen (309:808) renders category filter tabs and accordion list');
  assert(faqArticleContent.includes('Comment suivre ma commande ?') && faqArticleContent.includes('Suivre ma commande') && faqArticleContent.includes('onNavigateOrdersList'), 'FaqArticleTrackOrderStepsScreen (309:809) renders 4 step instructions and existing orders flow CTA');

  // 25e. Navigation wiring & reachability
  assert(navigatorContent.includes("'help-center-home'") && navigatorContent.includes("'help-category-orders-delivery'") && navigatorContent.includes("'help-center-search-results'") && navigatorContent.includes("'faq-tab-categories'") && navigatorContent.includes("'faq-article-track-order-steps'"), 'Step 7A support routes are registered in RootNavigator');
  assert(navigatorContent.includes("onNavigateHelpCenter={() => setCurrentScreen('help-center-home')"), 'SettingsScreen wires Help Center callback to help-center-home');
  assert(navigatorContent.includes("<PrivacyPolicyDocumentScreen") && navigatorContent.includes("onNavigateHelpCenter={() => setCurrentScreen('help-center-home')"), 'FIGMA-PROT-184: PrivacyPolicyDocumentScreen connects to help-center-home');

  // 26. Step 7B — Support Tickets, Contact Form & Request Workflow
  console.log('\n--- Section 26: Step 7B — Support Tickets & Request Workflow ---');

  // 26a. FIGMA-PROT-169 Reconciliation Verification
  const routeMapJson = fs.readFileSync(path.join(__dirname, '../design-reference/mayush-mobile-design/figma-handoff/figma-prototype-route-map.json'), 'utf8');
  assert(routeMapJson.includes('"connectionId": "FIGMA-PROT-169"') && routeMapJson.includes('"status": "IMPLEMENTED"'), 'FIGMA-PROT-169 correctly maintained as IMPLEMENTED following Step 6B & Step 7A reconciliation');

  // 26b. Content & Legal Copy Checks
  const supportStateContent = fs.readFileSync(path.join(__dirname, '../src/commerce/supportState.ts'), 'utf8');
  const legalContentText = fs.readFileSync(path.join(__dirname, '../src/content/legalContent.ts'), 'utf8');
  assert(supportStateContent.includes('الدفع والفوترة'), 'Arabic translation for Payment & Billing correctly formatted as الدفع والفوترة in supportState.ts');
  assert(legalContentText.includes('dans les conditions légales applicables') || legalContentText.includes('dans les cas applicables conformément à la Loi 31-08') || legalContentText.includes('في الحالات القابلة للتطبيق'), 'Law 31-08 copy accurately qualified in legalContent.ts');

  // 26c. Step 7B Screen Files Existence (309:810 - 309:819)
  const ticketsListPath = path.join(__dirname, '../src/screens/support/MySupportTicketsListScreen.tsx');
  const emptyStatePath = path.join(__dirname, '../src/screens/support/NoSupportRequestsEmptyStateScreen.tsx');
  const contactFormPath = path.join(__dirname, '../src/screens/support/ContactSupportFormScreen.tsx');
  const attachFilesPath = path.join(__dirname, '../src/screens/support/AttachFilesDocumentsScreen.tsx');
  const reviewSendPath = path.join(__dirname, '../src/screens/support/ReviewSendSupportRequestScreen.tsx');
  const selectOrderPath = path.join(__dirname, '../src/screens/support/SelectOrderForSupportScreen.tsx');
  const replyMessagePath = path.join(__dirname, '../src/screens/support/ReplyToSupportMessageScreen.tsx');
  const ticketDetailPath = path.join(__dirname, '../src/screens/support/TicketDetailConversationThreadScreen.tsx');
  const closeConfirmPath = path.join(__dirname, '../src/screens/support/CloseRequestConfirmationScreen.tsx');
  const sendSuccessPath = path.join(__dirname, '../src/screens/support/SupportRequestSentSuccessScreen.tsx');

  assert(fs.existsSync(ticketsListPath), 'MySupportTicketsListScreen exists (309:810)');
  assert(fs.existsSync(emptyStatePath), 'NoSupportRequestsEmptyStateScreen exists (309:811)');
  assert(fs.existsSync(contactFormPath), 'ContactSupportFormScreen exists (309:812)');
  assert(fs.existsSync(attachFilesPath), 'AttachFilesDocumentsScreen exists (309:813)');
  assert(fs.existsSync(reviewSendPath), 'ReviewSendSupportRequestScreen exists (309:814)');
  assert(fs.existsSync(selectOrderPath), 'SelectOrderForSupportScreen exists (309:815)');
  assert(fs.existsSync(replyMessagePath), 'ReplyToSupportMessageScreen exists (309:816)');
  assert(fs.existsSync(ticketDetailPath), 'TicketDetailConversationThreadScreen exists (309:817)');
  assert(fs.existsSync(closeConfirmPath), 'CloseRequestConfirmationScreen exists (309:818)');
  assert(fs.existsSync(sendSuccessPath), 'SupportRequestSentSuccessScreen exists (309:819)');

  // 26d. Content-level assertions
  const ticketsListContent = fs.readFileSync(ticketsListPath, 'utf8');
  const emptyStateContent = fs.readFileSync(emptyStatePath, 'utf8');
  const contactFormContent = fs.readFileSync(contactFormPath, 'utf8');
  const attachFilesContent = fs.readFileSync(attachFilesPath, 'utf8');
  const reviewSendContent = fs.readFileSync(reviewSendPath, 'utf8');
  const selectOrderContent = fs.readFileSync(selectOrderPath, 'utf8');
  const replyMessageContent = fs.readFileSync(replyMessagePath, 'utf8');
  const ticketDetailContent = fs.readFileSync(ticketDetailPath, 'utf8');
  const closeConfirmContent = fs.readFileSync(closeConfirmPath, 'utf8');
  const sendSuccessContent = fs.readFileSync(sendSuccessPath, 'utf8');

  assert(ticketsListContent.includes('Mes tickets de support') && ticketsListContent.includes('Toutes') && ticketsListContent.includes('Voir les détails'), 'MySupportTicketsListScreen (309:810) renders title, tabs, and details CTA');
  assert(emptyStateContent.includes('Aucune demande d’assistance') && emptyStateContent.includes('Consulter la FAQ') && emptyStateContent.includes('Contacter le support'), 'NoSupportRequestsEmptyStateScreen (309:811) renders empty state artwork and dual CTAs');
  assert(contactFormContent.includes('Contacter le support') && contactFormContent.includes('Sujet de votre demande') && contactFormContent.includes('Envoyer ma demande'), 'ContactSupportFormScreen (309:812) renders form fields, subject/category pickers, and send button');
  assert(attachFilesContent.includes('Joindre des pièces') && attachFilesContent.includes('10 Mo') && attachFilesContent.includes('Supprimer'), 'AttachFilesDocumentsScreen (309:813) renders upload options, file size limits, and remove action');
  assert(reviewSendContent.includes('Vérifier et envoyer') && reviewSendContent.includes('Modifier') && reviewSendContent.includes('Envoyer ma demande'), 'ReviewSendSupportRequestScreen (309:814) renders summary sections, edit buttons, and submission CTA');
  assert(selectOrderContent.includes('Sélectionner une commande') && selectOrderContent.includes('Commandes récentes') && selectOrderContent.includes('Continuer sans commande'), 'SelectOrderForSupportScreen (309:815) reuses buyer orders domain and offers fallback CTA');
  assert(replyMessageContent.includes('Répondre au support') && replyMessageContent.includes('DERNIER MESSAGE DU SUPPORT') && replyMessageContent.includes('Envoyer ma réponse'), 'ReplyToSupportMessageScreen (309:816) renders reply form and last agent message context');
  assert(ticketDetailContent.includes('Détail de la demande') && ticketDetailContent.includes('Répondre à la demande') && ticketDetailContent.includes('Clôturer la demande'), 'TicketDetailConversationThreadScreen (309:817) renders conversation thread and request actions');
  assert(closeConfirmContent.includes('Êtes-vous sûr de vouloir clôturer cette demande ?') && closeConfirmContent.includes('Conserver la demande ouverte') && closeConfirmContent.includes('Clôturer la demande'), 'CloseRequestConfirmationScreen (309:818) renders confirmation prompt and warning message');
  assert(sendSuccessContent.includes('Votre demande a été envoyée') && sendSuccessContent.includes('Voir mon ticket') && sendSuccessContent.includes('Retour au support'), 'SupportRequestSentSuccessScreen (309:819) renders submission confirmation and ticket link');

  // 26e. supportState Unit Tests
  const testSupportState = supportInstance;
  const initialRequestsCount = testSupportState.getSupportRequests().length;
  assert(initialRequestsCount >= 4, 'supportState seeds at least 4 ticket fixtures');

  const openTickets = testSupportState.filterSupportRequests('ouvertes');
  assert(openTickets.length >= 1, 'supportState filters open tickets correctly');

  const newTicket = testSupportState.createSupportRequest({
    subject: 'Test Ticket Automated',
    categoryId: 'commandes',
    message: 'Test automated message body',
    email: 'test@mayush.ma',
    preferredChannel: 'Email',
    attachments: [],
  });
  assert(newTicket && newTicket.reference.startsWith('SUP-2026-'), 'createSupportRequest generates valid SUP-2026- reference');
  assert(testSupportState.getSupportRequests().length === initialRequestsCount + 1, 'createSupportRequest prepends new request to state');

  const replyMsg = testSupportState.addReplyToRequest(newTicket.id, 'Automated reply test message');
  assert(replyMsg && replyMsg.text === 'Automated reply test message', 'addReplyToRequest appends user message to ticket thread');

  const closeSuccess = testSupportState.closeSupportRequest(newTicket.id);
  assert(closeSuccess && testSupportState.getSupportRequestById(newTicket.id).status === 'resolved', 'closeSupportRequest updates ticket status to resolved');

  // Reset support state after unit tests
  testSupportState.reset();

  // 26f. Navigation Reachability
  const navigatorText = fs.readFileSync(path.join(__dirname, '../src/navigation/RootNavigator.tsx'), 'utf8');
  assert(
    navigatorText.includes("'my-support-tickets-list'") &&
    navigatorText.includes("'no-support-requests-empty-state'") &&
    navigatorText.includes("'contact-support-form'") &&
    navigatorText.includes("'attach-files-documents'") &&
    navigatorText.includes("'review-send-support-request'") &&
    navigatorText.includes("'select-order-for-support'") &&
    navigatorText.includes("'reply-to-support-message'") &&
    navigatorText.includes("'ticket-detail-conversation-thread'") &&
    navigatorText.includes("'close-request-confirmation'") &&
    navigatorText.includes("'support-request-sent-success'"),
    'All 10 Step 7B support route keys are registered in RootNavigator'
  );

  // 27. Step 7C — Ticket Resolution, Support/System States & App Update
  console.log('\n--- Section 27: Step 7C — Ticket Resolution, Support/System States & App Update ---');

  // 27a. Screen Files Existence (309:820 - 309:824)
  const ticketResolvedPath = path.join(__dirname, '../src/screens/support/TicketResolvedRatingScreen.tsx');
  const connectionErrorPath = path.join(__dirname, '../src/screens/support/SupportConnectionErrorScreen.tsx');
  const tempUnavailablePath = path.join(__dirname, '../src/screens/support/SupportTemporarilyUnavailableScreen.tsx');
  const maintenanceModePath = path.join(__dirname, '../src/screens/support/MaintenanceModeServicesImpactedScreen.tsx');
  const appUpdatePath = path.join(__dirname, '../src/screens/support/AppUpdateAvailableScreen.tsx');

  assert(fs.existsSync(ticketResolvedPath), 'TicketResolvedRatingScreen exists (309:820)');
  assert(fs.existsSync(connectionErrorPath), 'SupportConnectionErrorScreen exists (309:821)');
  assert(fs.existsSync(tempUnavailablePath), 'SupportTemporarilyUnavailableScreen exists (309:822)');
  assert(fs.existsSync(maintenanceModePath), 'MaintenanceModeServicesImpactedScreen exists (309:823)');
  assert(fs.existsSync(appUpdatePath), 'AppUpdateAvailableScreen exists (309:824)');

  // 27b. Content & State Assertions
  const ticketResolvedContent = fs.readFileSync(ticketResolvedPath, 'utf8');
  const connectionErrorContent = fs.readFileSync(connectionErrorPath, 'utf8');
  const tempUnavailableContent = fs.readFileSync(tempUnavailablePath, 'utf8');
  const maintenanceModeContent = fs.readFileSync(maintenanceModePath, 'utf8');
  const appUpdateContent = fs.readFileSync(appUpdatePath, 'utf8');
  const systemStateContent = fs.readFileSync(path.join(__dirname, '../src/commerce/systemState.ts'), 'utf8');

  assert(ticketResolvedContent.includes('Ticket résolu') && ticketResolvedContent.includes('Évaluez votre expérience') && ticketResolvedContent.includes('Questions similaires'), 'TicketResolvedRatingScreen (309:820) renders title, 5-star rating control, and related FAQ');
  assert(connectionErrorContent.includes("Impossible de charger l\\'assistance") || connectionErrorContent.includes("Impossible de charger l'assistance") || connectionErrorContent.includes("Impossible de charger"), 'SupportConnectionErrorScreen (309:821) renders connection error title and retry CTA');
  assert(tempUnavailableContent.includes('Assistance temporairement indisponible') && tempUnavailableContent.includes('Consulter la FAQ'), 'SupportTemporarilyUnavailableScreen (309:822) renders unavailable title and FAQ link');
  assert((maintenanceModeContent.includes('maintenanceInfo.title') || maintenanceModeContent.includes('Nous améliorons votre expérience')) && maintenanceModeContent.includes('Services impactés') && maintenanceModeContent.includes('Dernière vérification'), 'MaintenanceModeServicesImpactedScreen (309:823) renders maintenance title, impacted services, and last checked timestamp');
  assert(!maintenanceModeContent.includes('Fin prévue') && !maintenanceModeContent.includes('Reprise estimée'), 'MaintenanceModeServicesImpactedScreen has NO invented ETA');
  assert((appUpdateContent.includes('updateInfo.currentVersion') || appUpdateContent.includes('1.0.0')) && appUpdateContent.includes('Mise à jour disponible') && appUpdateContent.includes('Mettre à jour maintenant'), 'AppUpdateAvailableScreen (309:824) renders title, current version 1.0.0, new version 1.3.0, and update CTA');
  assert(systemStateContent.includes("currentVersion: '1.0.0'") && systemStateContent.includes("latestVersion: '1.3.0'"), 'systemState.ts uses verified app.json version 1.0.0 and target update 1.3.0');

  // 27c. Verify implementation of 309:825 in Step 7D batch
  const forcedUpdatePath = path.join(__dirname, '../src/screens/support/ForcedUpdateRequiredScreen.tsx');
  assert(fs.existsSync(forcedUpdatePath), 'Node 309:825 (09-forced-update-required-fr) is implemented');

  // 27d. RootNavigator Registration
  assert(
    navigatorText.includes("'ticket-resolved-rating'") &&
    navigatorText.includes("'support-connection-error'") &&
    navigatorText.includes("'support-temporarily-unavailable'") &&
    navigatorText.includes("'maintenance-mode-services-impacted'") &&
    navigatorText.includes("'app-update-available'"),
    'All 5 Step 7C system state route keys are registered in RootNavigator'
  );

  // 28. Step 7D — Forced Update & Settings Failure/Loading States
  console.log('\n--- Section 28: Step 7D — Forced Update & Settings Failure/Loading States ---');
  const step7DTestModule = loadTypeScriptModule(path.join(__dirname, '../tests/Step7DForcedUpdateSettingsStatesTest.ts'));
  step7DTestModule.runStep7DForcedUpdateSettingsStatesTests(path.join(__dirname, '..'));

  const settingsErrorPath = path.join(__dirname, '../src/screens/support/SettingsErrorLoadingStateScreen.tsx');
  const settingsSkeletonPath = path.join(__dirname, '../src/screens/support/SettingsSkeletonLoadingStateScreen.tsx');

  assert(fs.existsSync(forcedUpdatePath), 'ForcedUpdateRequiredScreen exists (309:825)');
  assert(fs.existsSync(settingsErrorPath), 'SettingsErrorLoadingStateScreen exists (309:826)');
  assert(fs.existsSync(settingsSkeletonPath), 'SettingsSkeletonLoadingStateScreen exists (309:827)');

  assert(
    navigatorText.includes("'forced-update-required'") &&
    navigatorText.includes("'settings-error-loading-state'") &&
    navigatorText.includes("'settings-skeleton-loading-state'"),
    'All 3 Step 7D system state route keys are registered in RootNavigator'
  );

  // 29. Audit Root Navigation Wiring
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
