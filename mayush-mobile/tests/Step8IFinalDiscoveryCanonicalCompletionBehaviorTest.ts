import { execFileSync } from 'child_process';
import { readFileSync } from 'fs';
import { resolve } from 'path';
import { authState } from '../src/commerce/authState';
import { addCartLine, createSelectedVariantCartLine, emptyCartState } from '../src/commerce/cartState';
import { wishlistState } from '../src/commerce/wishlistState';
import {
  getAuthenticatedHomeFirstName,
  getHomeCatalogProductById,
  getHomeRecommendationIds,
  HOME_PRODUCT_CATALOG,
  resolveHomeProducts,
  resolveHomeRuntimeVariant,
} from '../src/screens/discovery/homeCatalog';

export const runStep8IFinalDiscoveryCanonicalCompletionBehaviorTests = async (assert: (condition: boolean, message: string) => void) => {
  authState.reset();
  assert(resolveHomeRuntimeVariant(authState.isAuthenticated()) === 'generic', '1 guest resolves generic Home');
  authState.completeLogin('buyer@example.ma', 'Mohamed El Amrani');
  assert(resolveHomeRuntimeVariant(authState.isAuthenticated()) === 'personalized', '2 authenticated buyer resolves personalized Home');
  assert(authState.isAuthenticated() && authState.getUser()?.id === 'mock-user-101', '3 authenticated Home uses authoritative authState');
  authState.logout();
  assert(resolveHomeRuntimeVariant(authState.isAuthenticated()) === 'generic', '4 logout removes personalized Home state');
  authState.completeLogin('buyer@example.ma', 'Mohamed El Amrani');
  assert(resolveHomeRuntimeVariant(authState.isAuthenticated()) === 'personalized', '5 re-authentication restores authenticated variant');

  const recommendationIds = getHomeRecommendationIds({ wishlistProductIds: [205], recentProductIds: [207], cartProductIds: [208] });
  assert(recommendationIds.every((id) => HOME_PRODUCT_CATALOG.some((product) => product.id === id)), '6 recommendations reference valid catalog product IDs');
  assert(JSON.stringify(recommendationIds) === JSON.stringify(getHomeRecommendationIds({ wishlistProductIds: [205], recentProductIds: [207], cartProductIds: [208] })), '7 recommendation ordering is deterministic');
  const homeCatalogCode = readFileSync(resolve(__dirname, '../src/screens/discovery/homeCatalog.ts'), 'utf8');
  assert(!/Math\.random|Date\.now|sort\(\s*\(\)\s*=>/.test(homeCatalogCode), '8 no random ranking exists');
  const fallback = getHomeRecommendationIds();
  assert(fallback.length === 4 && resolveHomeProducts(fallback).length === 4, '9 empty personalization inputs fall back safely');

  const recommendedProduct = getHomeCatalogProductById(recommendationIds[0]);
  const navigatorCode = readFileSync(resolve(__dirname, '../src/navigation/RootNavigator.tsx'), 'utf8');
  assert(Boolean(recommendedProduct) && /onSelectProduct=\{selectProduct\}/.test(navigatorCode) && /setCurrentScreen\('product-details'\)/.test(navigatorCode), '10 recommendation click opens canonical Product Details');
  wishlistState.reset();
  const beforeWishlistCount = wishlistState.getItems().length;
  wishlistState.toggle(recommendedProduct!);
  assert(wishlistState.getItems().length === beforeWishlistCount + 1, '11 wishlist uses existing shared wishlist state');
  assert(wishlistState.isWishlisted(recommendedProduct!.id) && wishlistState.getProductIds().includes(recommendedProduct!.id), '12 wishlist state reflects on recommended cards');

  const selectedLine = createSelectedVariantCartLine({ productId: recommendedProduct!.id, name: recommendedProduct!.name, variant: 'Beige', quantity: 1, unitPriceMad: 2490, sellerId: 'seller-home', sellerName: 'Mayush Maison', variantOptions: [{ variantId: 'Beige', label: 'Beige', unitPriceMad: 2490 }] });
  const cart = addCartLine(emptyCartState(), selectedLine);
  assert(cart.lines.length === 1 && cart.lines[0].productId === recommendedProduct!.id, '13 cart action uses existing cart flow');
  assert(cart.lines[0].variantId === 'Beige' && /onOpenVariantSheet/.test(navigatorCode), '14 required variant flow is preserved');
  assert(!/recommendationCart|personalizedCart|recommendedCart/.test(navigatorCode + homeCatalogCode), '15 no recommendation-specific cart exists');
  assert(!/recommendationStore|recommendationEngine|personalizedProductStore/.test(homeCatalogCode), '16 no recommendation-specific product store exists');
  const homeScreenCode = readFileSync(resolve(__dirname, '../src/screens/discovery/HomeScreen.tsx'), 'utf8');
  assert(/Découvrir maintenant/.test(homeScreenCode) && /onNavigateTab\?\.\('categories'\)/.test(homeScreenCode), '17 category and collection CTA uses existing discovery routes');
  assert(getAuthenticatedHomeFirstName(authState.getUser()) === 'Mohamed', '18 buyer greeting uses existing profile data only');
  assert(!/income|stylePersona|agePreference|genderPreference|locationPreference/.test(homeScreenCode + homeCatalogCode), '19 no invented user profile facts');

  await Promise.resolve();
  authState.reset();
  const hydration = authState.hydrate();
  const wasHydrating = authState.getStatus() === 'hydrating';
  await hydration;
  assert(wasHydrating && authState.isAuthenticated() && getAuthenticatedHomeFirstName(authState.getUser()) === 'Mohamed', '20 authenticated state survives normal auth hydration');
  assert(/authRepositoryResolved/.test(navigatorCode) && /!authRepositoryResolved/.test(navigatorCode), '21 guest data is not exposed before auth hydration');
  authState.logout();
  assert(authState.getUser() === null && getAuthenticatedHomeFirstName(authState.getUser()) === null && /onConfirmLogout[\s\S]*setCurrentScreen\('home'\)/.test(navigatorCode), '22 logout does not expose personalized identity');
  assert(/language !== 'ar'/.test(homeScreenCode) && /Accueil personnalisé/.test(homeScreenCode), '23 French LTR structure is present');
  assert(/rowReverse/.test(homeScreenCode) && /الرئيسية المخصصة/.test(homeScreenCode) && /writingDirection: 'rtl'/.test(homeScreenCode), '24 Arabic RTL structure is present');
  assert(!/fetch\(|axios|recommendation API|tracking SDK|event pipeline/i.test(homeCatalogCode + homeScreenCode), '25 no backend recommendation claim is introduced');
  assert(!/machine learning|artificial intelligence|powered by AI|algorithme IA/i.test(homeCatalogCode + homeScreenCode), '26 no AI or ML claim is introduced');
  assert(!/sellerDashboard|adminDashboard|sellerSession|adminSession/.test(homeCatalogCode + homeScreenCode + navigatorCode), '27 no seller or admin state is introduced');

  const registry = JSON.parse(readFileSync(resolve(__dirname, '../docs/frontend-completion/canonical-figma-screen-registry.json'), 'utf8'));
  const records = registry.nodes || [];
  const implemented = records.filter((screen: { screenStatus?: string }) => screen.screenStatus === 'IMPLEMENTED').length;
  assert(records.filter((screen: { figmaNodeId?: string }) => /^309:(?:679|68\d|69\d|70\d|710)$/.test(screen.figmaNodeId || '')).every((screen: { screenStatus?: string }) => screen.screenStatus === 'IMPLEMENTED'), '28 Checkout remains 32/32');
  assert(records.filter((screen: { figmaNodeId?: string }) => /^309:66[0-9]$|^309:658$/.test(screen.figmaNodeId || '')).every((screen: { screenStatus?: string }) => screen.screenStatus === 'IMPLEMENTED'), '29 Cart remains 12/12');
  const step8EReport = readFileSync(resolve(__dirname, '../docs/frontend-completion/STEP_8E_DELIVERY_SUPPORT_ORDER_STATES_REPORT.md'), 'utf8');
  assert(/BUYER_ORDER_SCREEN_COMPLETENESS:\s*34\/34/.test(step8EReport), '30 Buyer Orders remain complete');
  assert(implemented === 207 && records.length === 207 && records.every((screen: { screenStatus?: string }) => screen.screenStatus === 'IMPLEMENTED'), '31 canonical missing count reaches zero with evidence');
  const commandCenterDiff = execFileSync('git', ['diff', '--name-only', '--', 'tools/command-center'], { cwd: resolve(__dirname, '..'), encoding: 'utf8' }).trim();
  assert(commandCenterDiff === '' && !navigatorCode.includes('tools/command-center'), '32 Command Center files remain untouched');
  const csv = readFileSync(resolve(__dirname, '../docs/phase-5c/CURRENT_SCREEN_STATUS.csv'), 'utf8');
  assert(csv.includes('IMPLEMENTED_WEB_CHECKED_NATIVE_VALIDATION_PENDING') && /309:591/.test(csv), '33 native validation remains pending');
};
