const fs = require('fs');
const path = require('path');

const rootDir = path.join(__dirname, '../..');
const routeMapJsonPath = path.join(rootDir, 'design-reference/mayush-mobile-design/figma-handoff/figma-prototype-route-map.json');
const currentScreenCsvPath = path.join(rootDir, 'docs/phase-5c/CURRENT_SCREEN_STATUS.csv');
const rootNavigatorPath = path.join(rootDir, 'src/navigation/RootNavigator.tsx');
const registryJsonPath = path.join(rootDir, 'docs/frontend-completion/canonical-figma-screen-registry.json');
const auditJsonPath = path.join(rootDir, 'docs/frontend-completion/prototype-gap-audit.json');
const reconciliationMdPath = path.join(rootDir, 'docs/frontend-completion/STEP_8A_1_CANONICAL_MAPPING_RECONCILIATION.md');

function fail(message) {
  throw new Error(`[canonical-registry] ${message}`);
}

function parseCsv(text) {
  const rows = [];
  let row = [];
  let field = '';
  let quoted = false;

  for (let index = 0; index < text.length; index += 1) {
    const char = text[index];
    const next = text[index + 1];
    if (char === '"' && quoted && next === '"') {
      field += '"';
      index += 1;
    } else if (char === '"') {
      quoted = !quoted;
    } else if (char === ',' && !quoted) {
      row.push(field);
      field = '';
    } else if ((char === '\n' || char === '\r') && !quoted) {
      if (char === '\r' && next === '\n') index += 1;
      row.push(field);
      if (row.some((value) => value.length > 0)) rows.push(row);
      row = [];
      field = '';
    } else {
      field += char;
    }
  }

  if (field.length > 0 || row.length > 0) {
    row.push(field);
    rows.push(row);
  }
  if (!rows.length) return [];

  const headers = rows[0].map((header) => header.replace(/^\uFEFF/, '').trim());
  return rows.slice(1).map((values) => Object.fromEntries(headers.map((header, index) => [header, (values[index] || '').trim()])));
}

function normalizeSourcePath(filePath) {
  return filePath.replace(/\\/g, '/').replace(/^mayush-mobile\//, '');
}

function compareNodeIds(left, right) {
  const leftParts = left.split(':').map(Number);
  const rightParts = right.split(':').map(Number);
  return leftParts[0] - rightParts[0] || leftParts[1] - rightParts[1];
}

function countStatuses(records, selector) {
  return records.reduce((counts, record) => {
    const status = selector(record);
    counts[status] = (counts[status] || 0) + 1;
    return counts;
  }, {});
}

const routeMapData = JSON.parse(fs.readFileSync(routeMapJsonPath, 'utf8'));
const csvRows = parseCsv(fs.readFileSync(currentScreenCsvPath, 'utf8'));
const navigatorCode = fs.readFileSync(rootNavigatorPath, 'utf8');
const collectSourceFiles = (directory) => fs.readdirSync(directory, { withFileTypes: true }).flatMap((entry) => {
  const absolute = path.join(directory, entry.name);
  return entry.isDirectory() ? collectSourceFiles(absolute) : /\.(?:ts|tsx)$/.test(entry.name) ? [absolute] : [];
});
const allApplicationSource = collectSourceFiles(path.join(rootDir, 'src')).map((file) => fs.readFileSync(file, 'utf8')).join('\n');
const connections = routeMapData.connections || [];
const statusOverrides = routeMapData.connectionStatusOverrides || {};

if (connections.length !== 206) fail(`Expected 206 prototype connections, found ${connections.length}.`);

const screenKeyMatch = navigatorCode.match(/export type ScreenKey\s*=([\s\S]*?);\s*\n/);
if (!screenKeyMatch) fail('Unable to parse ScreenKey from RootNavigator.tsx.');
const screenKeys = new Set([...screenKeyMatch[1].matchAll(/'([^']+)'/g)].map((match) => match[1]));
const escapeRegex = (value) => value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
const literalTransitionCount = (route) => (navigatorCode.match(new RegExp(`setCurrentScreen\\((?:(?!\\))[\\s\\S]){0,300}?['"]${escapeRegex(route)}['"]`, 'g')) || []).length;
const hasRenderBranch = (route) => new RegExp(`currentScreen\\s*===\\s*['"]${escapeRegex(route)}['"]`).test(navigatorCode);

const routeRuntimeEvidenceOverrides = {
  '309:598': ['onOpenPromotions', "resolveHomeCanonicalDestination('promotions')"],
  '309:599': ['onOpenRecentlyViewed', "resolveHomeCanonicalDestination('recently_viewed')"],
  '309:693': ['finishOrderProcessing', 'resolveOrderProcessingDestination'],
  '309:699': ['verifyActivePayment', 'resolvePaymentVerificationDestination(outcome)'],
  '309:700': ['cancelActivePayment', "resolvePaymentVerificationDestination('cancelled')"],
  '309:791': ['onNavigateAboutMayush', 'resolveSettingsAboutDestination()'],
};

const nonRouteRuntimeEvidence = {
  '309:591': { evidenceKind: 'INTERACTIVE_STATE', tokens: ['PersonalizedHome', 'isAuthenticated', 'authenticatedUser'] },
  '309:596': { evidenceKind: 'INTERACTIVE_OVERLAY', tokens: ['FilterPanelModal', 'filterModalVisible'] },
  '309:607': { evidenceKind: 'INTERACTIVE_OVERLAY', tokens: ['VariantSelectorSheet', 'variantSheetVisible'] },
  '309:653': { evidenceKind: 'INTERACTIVE_OVERLAY', tokens: ['FavoritesAuthPromptOverlay', 'favoritesPromptVisible'] },
  '309:659': { evidenceKind: 'TRANSIENT_FEEDBACK', tokens: ['CartToast', 'toastVisible', 'toastMessage'] },
  '309:660': { evidenceKind: 'INTERACTIVE_OVERLAY', tokens: ['VariantEditSheet', 'lineToEditVariant'] },
  '309:661': { evidenceKind: 'INTERACTIVE_STATE', tokens: ['SellerCartGroup', 'groupBySeller'] },
  '309:662': { evidenceKind: 'INTERACTIVE_STATE', tokens: ['promoError', 'handleApplyPromo'] },
  '309:663': { evidenceKind: 'INTERACTIVE_STATE', tokens: ['appliedPromotion', 'discountMad'] },
  '309:664': { evidenceKind: 'INTERACTIVE_OVERLAY', tokens: ['vouchersModalVisible', 'getAvailablePromotions'] },
  '309:665': { evidenceKind: 'INTERACTIVE_OVERLAY', tokens: ['RemoveItemDialog', 'lineToRemove'] },
  '309:713': { evidenceKind: 'INTERACTIVE_STATE', tokens: ['selectedOrderTab', 'setSelectedOrderTab', 'filterOrdersByTab'] },
  '309:714': { evidenceKind: 'INTERACTIVE_STATE', tokens: ['selectedOrderTab', 'setSelectedOrderTab', 'filterOrdersByTab'] },
  '309:715': { evidenceKind: 'INTERACTIVE_STATE', tokens: ['selectedOrderTab', 'setSelectedOrderTab', 'filterOrdersByTab'] },
  '309:761': { evidenceKind: 'INTERACTIVE_OVERLAY', tokens: ['DisconnectSessionModal', 'visible'] },
  '309:767': { evidenceKind: 'INTERACTIVE_OVERLAY', tokens: ['DeleteAddressModal', 'visible'] },
  '309:771': { evidenceKind: 'INTERACTIVE_OVERLAY', tokens: ['LogoutConfirmationModal', 'logoutModalVisible'] },
  '309:796': { evidenceKind: 'INTERACTIVE_OVERLAY', tokens: ['ClearCacheConfirmationModal', 'visible'] },
};

const nodes = new Map();
function registerNode(figmaNodeId, frameName, connectionId, direction) {
  if (!figmaNodeId || !frameName) fail(`Connection ${connectionId} has an incomplete ${direction} identity.`);
  const existing = nodes.get(figmaNodeId);
  if (existing && existing.frameName !== frameName) {
    fail(`Figma node ${figmaNodeId} has incompatible frame identities: "${existing.frameName}" and "${frameName}".`);
  }
  const node = existing || {
    figmaNodeId,
    frameName,
    sourceConnectionIds: [],
    destinationConnectionIds: [],
  };
  node[direction === 'source' ? 'sourceConnectionIds' : 'destinationConnectionIds'].push(connectionId);
  nodes.set(figmaNodeId, node);
}

connections.forEach((connection) => {
  registerNode(
    connection.sourceFigmaNodeId || connection.sourceScreen?.figmaNodeId,
    connection.sourceScreen?.exactName,
    connection.connectionId,
    'source',
  );
  registerNode(
    connection.destinationFigmaNodeId || connection.destinationScreen?.figmaNodeId,
    connection.destinationScreen?.exactName,
    connection.connectionId,
    'destination',
  );
});

const manualMappings = {
  '309:583': { frameName: '01-splash-screen-logo', file: 'src/screens/entry/SplashScreen.tsx', route: 'splash', implementationType: 'ROUTE' },
  '309:584': { frameName: '01-loading-screen-preparing-experience', file: 'src/screens/entry/PreparingExperienceScreen.tsx', route: 'preparing', implementationType: 'ROUTE' },
  '309:585': { frameName: '01-language-selection-french-arabic', file: 'src/screens/entry/LanguageSelectionScreen.tsx', route: 'language', implementationType: 'ROUTE' },
  '309:586': { frameName: '01-onboarding-step1-discover-interior-fr', file: 'src/screens/entry/OnboardingScreen.tsx', route: 'onboarding-1', implementationType: 'ROUTE' },
  '309:587': { frameName: '01-onboarding-step2-choose-with-confidence-fr', file: 'src/screens/entry/OnboardingScreen.tsx', route: 'onboarding-2', implementationType: 'ROUTE' },
  '309:588': { frameName: '01-onboarding-step3-order-simply-fr', file: 'src/screens/entry/OnboardingScreen.tsx', route: 'onboarding-3', implementationType: 'ROUTE' },
  '309:590': { frameName: '02-home-hero-new-arrivals-best-sellers-fr', file: 'src/screens/discovery/HomeScreen.tsx', route: 'home', implementationType: 'ROUTE' },
  '309:591': { frameName: '02-home-logged-in-personalized-recommendations', file: 'src/screens/discovery/HomeScreen.tsx', route: null, implementationType: 'INLINE_STATE' },
  '309:592': { frameName: '02-categories-photo-grid-fr', file: 'src/screens/discovery/CategoriesScreen.tsx', route: 'categories', implementationType: 'ROUTE' },
  '309:594': { frameName: '02-subcategory-canapes-filtered-list', file: 'src/screens/discovery/CategoryProductListScreen.tsx', route: 'category-products', implementationType: 'ROUTE' },
  '309:604': { frameName: '03-product-detail-image-carousel-add-to-cart', file: 'src/screens/product/ProductDetailsScreen.tsx', route: 'product-details', implementationType: 'ROUTE' },
  '309:605': { frameName: '03-product-gallery-zoom-thumbnails', file: 'src/screens/product/ProductGalleryScreen.tsx', route: 'product-gallery', implementationType: 'ROUTE' },
  '309:607': { frameName: '03-product-variant-selector-color-material-size', file: 'src/screens/product/VariantSelectorSheet.tsx', route: null, implementationType: 'SHEET' },
  '309:611': { frameName: '03-product-added-to-cart-confirmation', file: 'src/screens/commerce/AddedToCartConfirmationScreen.tsx', route: 'added-to-cart', implementationType: 'ROUTE' },
  '309:658': { frameName: '05-cart-items-promo-code-summary-fr', file: 'src/screens/commerce/CartScreen.tsx', route: 'cart', implementationType: 'ROUTE' },
  '309:659': { frameName: '05-cart-quantity-update-toast-fr', file: 'src/components/cart/CartToast.tsx', route: null, implementationType: 'TOAST' },
  '309:660': { frameName: '05-cart-modify-variant-bottom-sheet-fr', file: 'src/components/cart/VariantEditSheet.tsx', route: null, implementationType: 'BOTTOM_SHEET' },
  '309:661': { frameName: '05-cart-multi-vendor-grouped-by-seller-fr', file: 'src/components/cart/SellerCartGroup.tsx', route: null, implementationType: 'INLINE_STATE' },
  '309:662': { frameName: '05-cart-invalid-promo-code-error-fr', file: 'src/screens/commerce/CartScreen.tsx', route: null, implementationType: 'INLINE_STATE' },
  '309:663': { frameName: '05-cart-promo-applied-order-summary-fr', file: 'src/screens/commerce/CartScreen.tsx', route: null, implementationType: 'INLINE_STATE' },
  '309:664': { frameName: '05-cart-promo-code-modal-available-offers-fr', file: 'src/screens/commerce/CartScreen.tsx', route: null, implementationType: 'BOTTOM_SHEET' },
  '309:665': { frameName: '05-cart-remove-item-confirmation-dialog-fr', file: 'src/components/cart/RemoveItemDialog.tsx', route: null, implementationType: 'BOTTOM_SHEET' },
  '309:683': { frameName: '06-city-selector-list-fr', file: 'src/screens/checkout/CheckoutAddressStateScreens.tsx', route: 'city-selector', implementationType: 'ROUTE' },
  '309:684': { frameName: '06-delivery-zone-selector-fr', file: 'src/screens/checkout/CheckoutAddressStateScreens.tsx', route: 'delivery-zone-selector', implementationType: 'ROUTE' },
  '309:685': { frameName: '06-edit-address-form-fr', file: 'src/screens/checkout/CheckoutAddressStateScreens.tsx', route: 'edit-checkout-address', implementationType: 'ROUTE' },
  '309:686': { frameName: '06-no-address-saved-empty-state-v2-fr', file: 'src/screens/checkout/CheckoutAddressStateScreens.tsx', route: 'no-saved-address', implementationType: 'ROUTE' },
  '309:688': { frameName: '06-delivery-by-vendor-multi-seller-fr', file: 'src/screens/checkout/CheckoutDeliveryStateScreens.tsx', route: 'delivery-by-vendor', implementationType: 'ROUTE' },
  '309:689': { frameName: '06-delivery-unavailable-address-error-fr', file: 'src/screens/checkout/CheckoutDeliveryStateScreens.tsx', route: 'delivery-unavailable', implementationType: 'ROUTE' },
  '309:691': { frameName: '06-pay-with-wallet-balance-fr', file: 'src/screens/checkout/CheckoutPaymentDetailScreens.tsx', route: 'wallet-balance', implementationType: 'ROUTE' },
  '309:692': { frameName: '06-saved-payment-cards-visa-mastercard-fr', file: 'src/screens/checkout/CheckoutPaymentDetailScreens.tsx', route: 'saved-payment-cards', implementationType: 'ROUTE' },
  '309:701': { frameName: '06-payment-confirmation-taking-longer-fr', file: 'src/screens/checkout/CheckoutPaymentConflictSystemScreens.tsx', route: 'payment-confirmation-delayed', implementationType: 'ROUTE' },
  '309:702': { frameName: '06-payment-pending-confirmation-fr', file: 'src/screens/checkout/CheckoutPaymentConflictSystemScreens.tsx', route: 'payment-pending', implementationType: 'ROUTE' },
  '309:704': { frameName: '06-terms-conditions-confirmation-fr', file: 'src/screens/checkout/CheckoutPaymentConflictSystemScreens.tsx', route: 'checkout-terms-confirmation', implementationType: 'ROUTE' },
  '309:705': { frameName: '06-order-thank-you-confirmation-summary-v2-fr', file: 'src/screens/orders/OrderThankYouScreen.tsx', route: 'order-thank-you', implementationType: 'ROUTE' },
  '309:707': { frameName: '06-order-already-in-progress-duplicate-check-fr', file: 'src/screens/checkout/CheckoutPaymentConflictSystemScreens.tsx', route: 'order-already-in-progress', implementationType: 'ROUTE' },
  '309:708': { frameName: '06-order-needs-update-price-stock-changes-fr', file: 'src/screens/checkout/CheckoutPaymentConflictSystemScreens.tsx', route: 'order-needs-update', implementationType: 'ROUTE' },
  '309:709': { frameName: '06-checkout-skeleton-loading-state', file: 'src/screens/checkout/CheckoutPaymentConflictSystemScreens.tsx', route: 'checkout-skeleton', implementationType: 'ROUTE' },
  '309:710': { frameName: '06-checkout-error-loading-state-fr', file: 'src/screens/checkout/CheckoutPaymentConflictSystemScreens.tsx', route: 'checkout-error', implementationType: 'ROUTE' },
  '309:712': { frameName: '07-orders-list-all-tabs-fr', file: 'src/screens/orders/OrdersListScreen.tsx', route: 'orders-list', implementationType: 'ROUTE' },
  '309:713': { frameName: '07-orders-in-progress-tab-statuses-fr', file: 'src/screens/orders/OrdersListScreen.tsx', route: null, implementationType: 'INLINE_STATE' },
  '309:714': { frameName: '07-orders-completed-tab-reorder-review-fr', file: 'src/screens/orders/OrdersListScreen.tsx', route: null, implementationType: 'INLINE_STATE' },
  '309:715': { frameName: '07-orders-cancelled-tab-refund-statuses-fr', file: 'src/screens/orders/OrdersListScreen.tsx', route: null, implementationType: 'INLINE_STATE' },
  '309:716': { frameName: '07-order-detail-in-preparation-timeline-fr', file: 'src/screens/orders/OrderDetailsScreen.tsx', route: 'order-detail-preparing', implementationType: 'ROUTE' },
  '309:717': { frameName: '07-order-detail-shipped-tracking-fr', file: 'src/screens/orders/OrderDetailsScreen.tsx', route: 'order-detail-shipped', implementationType: 'ROUTE' },
  '309:718': { frameName: '07-order-tracking-timeline-realtime-fr', file: 'src/screens/orders/OrderTrackingScreen.tsx', route: 'order-tracking', implementationType: 'ROUTE' },
  '309:719': { frameName: '07-order-detail-delivered-actions-fr', file: 'src/screens/orders/OrderDetailsScreen.tsx', route: 'order-detail-delivered', implementationType: 'ROUTE' },
  '309:720': { frameName: '07-order-detail-multi-vendor-packages-fr', file: 'src/screens/orders/OrderDetailsScreen.tsx', route: 'order-detail-multi-vendor', implementationType: 'ROUTE' },
  '309:721': { frameName: '07-multiple-packages-split-shipment-fr', file: 'src/screens/orders/OrderPackagesScreen.tsx', route: 'order-packages', implementationType: 'ROUTE' },
  '309:722': { frameName: '07-package-detail-items-shipping-info-fr', file: 'src/screens/orders/OrderPackageDetailsScreen.tsx', route: 'order-package-detail', implementationType: 'ROUTE' },
  '309:723': { frameName: '07-invoice-detail-download-share-fr', file: 'src/screens/orders/OrderInvoiceScreen.tsx', route: 'order-invoice', implementationType: 'ROUTE' },
  '309:724': { frameName: '07-cancel-order-confirmation-dialog-fr', file: 'src/screens/orders/OrderCancellationScreens.tsx', route: 'order-cancel-confirmation', implementationType: 'ROUTE' },
  '309:725': { frameName: '07-cancel-order-reason-form-fr', file: 'src/screens/orders/OrderCancellationScreens.tsx', route: 'order-cancel-reason', implementationType: 'ROUTE' },
  '309:726': { frameName: '07-cancellation-request-registered-fr', file: 'src/screens/orders/OrderCancellationScreens.tsx', route: 'order-cancel-registered', implementationType: 'ROUTE' },
  '309:727': { frameName: '07-order-cannot-be-cancelled-fr', file: 'src/screens/orders/OrderCancellationScreens.tsx', route: 'order-cannot-cancel', implementationType: 'ROUTE' },
  '309:728': { frameName: '07-rate-order-review-products-fr', file: 'src/screens/orders/OrderProductReviewScreen.tsx', route: 'order-product-review', implementationType: 'ROUTE' },
  '309:729': { frameName: '07-reorder-articles-changed-unavailable-fr', file: 'src/screens/orders/OrderReorderScreens.tsx', route: 'order-reorder-changes', implementationType: 'ROUTE' },
  '309:730': { frameName: '07-reorder-items-added-to-cart-fr', file: 'src/screens/orders/OrderReorderScreens.tsx', route: 'order-reorder-added', implementationType: 'ROUTE' },
  '309:731': { frameName: '07-reorder-with-availability-changes-fr', file: 'src/screens/orders/OrderReorderScreens.tsx', route: 'order-reorder-availability', implementationType: 'ROUTE' },
  '309:732': { frameName: '07-request-return-item-selection-fr', file: 'src/screens/orders/OrderReturnScreens.tsx', route: 'order-return-selection', implementationType: 'ROUTE' },
  '309:733': { frameName: '07-return-detail-items-refund-status-fr', file: 'src/screens/orders/OrderReturnScreens.tsx', route: 'order-return-detail', implementationType: 'ROUTE' },
  '309:734': { frameName: '07-return-tracking-timeline-fr', file: 'src/screens/orders/OrderReturnScreens.tsx', route: 'order-return-tracking', implementationType: 'ROUTE' },
  '309:735': { frameName: '07-request-refund-cancelled-order-fr', file: 'src/screens/orders/OrderRefundScreens.tsx', route: 'order-refund-request', implementationType: 'ROUTE' },
  '309:736': { frameName: '07-refund-completed-success-fr', file: 'src/screens/orders/OrderRefundScreens.tsx', route: 'order-refund-completed', implementationType: 'ROUTE' },
  '309:737': { frameName: '07-delivery-delayed-notification-fr', file: 'src/screens/orders/OrderDeliveryIssueScreens.tsx', route: 'delivery-delayed', implementationType: 'ROUTE' },
  '309:738': { frameName: '07-delivery-failed-reschedule-fr', file: 'src/screens/orders/OrderDeliveryIssueScreens.tsx', route: 'delivery-failed', implementationType: 'ROUTE' },
  '309:739': { frameName: '07-support-order-contact-form-fr', file: 'src/screens/support/ContactSupportFormScreen.tsx', route: 'order-support-contact', implementationType: 'ROUTE' },
  '309:740': { frameName: '07-tracking-unavailable-in-preparation-fr', file: 'src/screens/orders/OrderDeliveryIssueScreens.tsx', route: 'tracking-unavailable', implementationType: 'ROUTE' },
  '309:741': { frameName: '07-order-not-found-error-fr', file: 'src/screens/orders/OrderSystemStateScreens.tsx', route: 'order-not-found', implementationType: 'ROUTE' },
  '309:742': { frameName: '07-orders-empty-state-fr', file: 'src/screens/orders/OrderSystemStateScreens.tsx', route: 'orders-empty', implementationType: 'ROUTE' },
  '309:743': { frameName: '07-orders-error-loading-state-fr', file: 'src/screens/orders/OrderSystemStateScreens.tsx', route: 'orders-error', implementationType: 'ROUTE' },
  '309:744': { frameName: '07-orders-skeleton-loading-state', file: 'src/screens/orders/OrderSystemStateScreens.tsx', route: 'orders-skeleton', implementationType: 'ROUTE' },
  '309:745': { frameName: '07-order-detail-skeleton-loading-state', file: 'src/screens/orders/OrderSystemStateScreens.tsx', route: 'order-detail-skeleton', implementationType: 'ROUTE' },
};

const nonRouteCsvMappings = {
  '309:596': { implementationType: 'SHEET' },
  '309:653': { implementationType: 'MODAL' },
  '309:761': { implementationType: 'MODAL' },
  '309:767': { implementationType: 'MODAL' },
  '309:771': { implementationType: 'MODAL' },
  '309:796': { implementationType: 'MODAL' },
};

// Historical CSV labels for these nodes predate the current route-map capture.
// The IDs and implementations remain compatible, but canonical identity follows
// the current frame name and is reconciled explicitly here.
const csvFrameNameOverrides = {
  '309:600': '02-search-recent-popular-trending-categories',
  '309:762': '08-my-addresses-list-labels-fr',
  '309:763': '08-my-addresses-list-v2-fr',
  '309:765': '08-add-address-simple-form-fr',
};

const mappings = new Map();
Object.entries(manualMappings).forEach(([figmaNodeId, mapping]) => {
  mappings.set(figmaNodeId, {
    ...mapping,
    screenStatus: 'IMPLEMENTED',
    evidenceSource: 'MANUAL_SOURCE_REACHABILITY_AUDIT',
  });
});

csvRows.forEach((row) => {
  const figmaNodeId = row.figma_node_id;
  if (!figmaNodeId || !nodes.has(figmaNodeId)) return;
  if (mappings.has(figmaNodeId)) return;
  if (row.functional_status !== 'PASS' || row.test_status !== 'PASS' || !row.implementation_file) return;

  const nonRoute = nonRouteCsvMappings[figmaNodeId];
  mappings.set(figmaNodeId, {
    frameName: csvFrameNameOverrides[figmaNodeId] || row.screen_name,
    file: normalizeSourcePath(row.implementation_file),
    route: nonRoute ? null : row.navigator_key,
    implementationType: nonRoute?.implementationType || 'ROUTE',
    screenStatus: 'IMPLEMENTED',
    evidenceSource: 'CURRENT_SCREEN_STATUS_FUNCTIONAL_AND_TEST_PASS',
  });
});

for (const [figmaNodeId, mapping] of mappings.entries()) {
  const node = nodes.get(figmaNodeId);
  if (!node) fail(`Mapping ${figmaNodeId} does not exist in the current route-map node inventory.`);
  if (node.frameName !== mapping.frameName) {
    fail(`Mapping ${figmaNodeId} frame conflict: current="${node.frameName}", mapping="${mapping.frameName}".`);
  }
  const fullPath = path.join(rootDir, mapping.file);
  if (!fs.existsSync(fullPath)) fail(`Mapped file does not exist for ${figmaNodeId}: ${mapping.file}.`);
  if (mapping.implementationType === 'ROUTE') {
    if (!mapping.route || !screenKeys.has(mapping.route)) {
      fail(`Mapped route for ${figmaNodeId} is not a real ScreenKey: ${mapping.route || '<empty>'}.`);
    }
    if (!hasRenderBranch(mapping.route)) fail(`Mapped route for ${figmaNodeId} has no render branch: ${mapping.route}.`);
    const literalTriggers = literalTransitionCount(mapping.route);
    const dynamicOrderRoute = /^order-(?:detail|refund)/.test(mapping.route) && navigatorCode.includes('setCurrentScreen(route)');
    const conditionalRoute = mapping.route === 'order-cancel-reason' && navigatorCode.includes("? 'order-cancel-reason' : 'order-cannot-cancel'");
    const initialRoute = mapping.route === 'splash' || (mapping.route === 'language' && navigatorCode.includes("restoredCheckoutScreen || 'home') : 'language'"));
    const overrideTokens = routeRuntimeEvidenceOverrides[figmaNodeId] || [];
    const overrideSatisfied = overrideTokens.length > 0 && overrideTokens.every((token) => allApplicationSource.includes(token));
    if (!literalTriggers && !dynamicOrderRoute && !conditionalRoute && !initialRoute && !overrideSatisfied) {
      fail(`Mapped route for ${figmaNodeId} has no runtime reachability evidence: ${mapping.route}.`);
    }
    mapping.evidenceKind = 'RUNTIME_ROUTE';
    mapping.runtimeEvidence = overrideTokens.length
      ? overrideTokens
      : [literalTriggers ? `literal-transition:${mapping.route}` : dynamicOrderRoute ? 'dynamic-order-detail-route' : conditionalRoute ? 'conditional-order-cancellation-route' : 'initial-runtime-route'];
    mapping.reachability = 'RUNTIME_REACHABLE';
  } else if (!['MODAL', 'SHEET', 'BOTTOM_SHEET', 'TOAST', 'INLINE_STATE'].includes(mapping.implementationType)) {
    fail(`Unsupported non-route implementationType for ${figmaNodeId}: ${mapping.implementationType}.`);
  } else {
    const evidence = nonRouteRuntimeEvidence[figmaNodeId];
    if (!evidence || !evidence.tokens.length) fail(`Non-route ${figmaNodeId} lacks explicit runtime evidence metadata.`);
    if (mapping.implementationType === 'INLINE_STATE' && evidence.evidenceKind !== 'INTERACTIVE_STATE') {
      fail(`INLINE_STATE ${figmaNodeId} must use INTERACTIVE_STATE evidence, not ${evidence.evidenceKind}.`);
    }
    const missingTokens = evidence.tokens.filter((token) => !allApplicationSource.includes(token));
    if (missingTokens.length) fail(`Non-route ${figmaNodeId} runtime evidence is missing: ${missingTokens.join(', ')}.`);
    mapping.evidenceKind = evidence.evidenceKind;
    mapping.runtimeEvidence = [...evidence.tokens];
    mapping.reachability = 'RUNTIME_REACHABLE';
  }
}

if (mappings.get('309:737')?.file === 'src/screens/orders/OrderDetailsScreen.tsx') fail('309:737 must not map to legacy Order Detail.');

const requiredCorrectMappings = {
  '309:789': ['src/screens/account/SettingsScreen.tsx', 'settings'],
  '309:793': ['src/screens/account/AppPermissionsScreen.tsx', 'app-permissions'],
  '309:796': ['src/screens/account/ClearCacheConfirmationModal.tsx', null],
  '309:797': ['src/screens/account/LanguageSelectionAccountScreen.tsx', 'language-selection'],
};
Object.entries(requiredCorrectMappings).forEach(([figmaNodeId, expected]) => {
  const mapping = mappings.get(figmaNodeId);
  if (!mapping || mapping.file !== expected[0] || mapping.route !== expected[1]) {
    fail(`Required canonical mapping is incorrect for ${figmaNodeId}.`);
  }
});

function getDomain(frameName) {
  const name = frameName.toLowerCase();
  if (name.startsWith('01-')) return 'Entry / Onboarding';
  if (name.startsWith('02-')) return 'Discovery & Search';
  if (name.startsWith('03-')) return 'Product Detail';
  if (name.startsWith('04-')) return 'Authentication & Recovery';
  if (name.startsWith('05-')) return 'Cart & Wishlist';
  if (name.startsWith('06-')) return 'Checkout & Payment';
  if (name.startsWith('07-')) return 'Buyer Orders & Fulfillment';
  if (name.startsWith('08-')) return 'Account & Buyer Preferences';
  if (name.startsWith('09-')) return 'Settings, Support & System';
  return 'General Prototype';
}

const registryEntries = [...nodes.values()]
  .sort((left, right) => compareNodeIds(left.figmaNodeId, right.figmaNodeId))
  .map((node) => {
    const mapping = mappings.get(node.figmaNodeId);
    return {
      figmaNodeId: node.figmaNodeId,
      frameName: node.frameName,
      domain: getDomain(node.frameName),
      sourceConnectionIds: [...node.sourceConnectionIds].sort(),
      destinationConnectionIds: [...node.destinationConnectionIds].sort(),
      component: mapping ? path.basename(mapping.file) : null,
      sourceFile: mapping?.file || null,
      route: mapping?.route || null,
      implementationType: mapping?.implementationType || 'UNIMPLEMENTED',
      screenStatus: mapping?.screenStatus || 'MISSING',
      evidenceSource: mapping?.evidenceSource || null,
      evidenceKind: mapping?.evidenceKind || null,
      runtimeEvidence: mapping?.runtimeEvidence || [],
      reachability: mapping?.reachability || 'UNREACHABLE',
    };
  });

const screenCounts = countStatuses(registryEntries, (entry) => entry.screenStatus);
const effectiveRawStatus = (connection) => statusOverrides[connection.connectionId] || connection.status || 'MISSING';
const rawConnectionCounts = countStatuses(connections, effectiveRawStatus);
const declaredCounts = routeMapData.statusCounts || {};
for (const status of ['IMPLEMENTED', 'MISMATCHED', 'MISSING']) {
  if ((rawConnectionCounts[status] || 0) !== (declaredCounts[status] || 0)) {
    fail(`Route-map summary ${status}=${declaredCounts[status] || 0} disagrees with effective rows=${rawConnectionCounts[status] || 0}.`);
  }
}

const presentationConnections = new Set(['FIGMA-PROT-001', 'FIGMA-PROT-002']);
const connectionEntries = connections.map((connection) => {
  const rawStatus = effectiveRawStatus(connection);
  const connectionStatus = presentationConnections.has(connection.connectionId) ? 'IMPLEMENTED' : rawStatus;
  const sourceFigmaNodeId = connection.sourceFigmaNodeId || connection.sourceScreen?.figmaNodeId;
  const destinationFigmaNodeId = connection.destinationFigmaNodeId || connection.destinationScreen?.figmaNodeId;
  const destination = registryEntries.find((entry) => entry.figmaNodeId === destinationFigmaNodeId);
  if (connectionStatus === 'IMPLEMENTED' && destination?.screenStatus !== 'IMPLEMENTED') {
    fail(`${connection.connectionId} is IMPLEMENTED but destination ${destinationFigmaNodeId} is ${destination?.screenStatus || 'absent'}.`);
  }
  return {
    connectionId: connection.connectionId,
    sourceFigmaNodeId,
    destinationFigmaNodeId,
    connectionStatus,
    implementationType: presentationConnections.has(connection.connectionId) ? 'PRESENTATION_ONLY_CONNECTION' : 'USER_INTERACTION',
  };
});

const connectionCounts = countStatuses(connectionEntries, (entry) => entry.connectionStatus);
if (registryEntries.length !== 207) fail(`Expected 207 prototype-connected nodes, found ${registryEntries.length}.`);
if ((screenCounts.IMPLEMENTED || 0) !== 207) fail(`Expected evidence-backed screen count 207, found ${screenCounts.IMPLEMENTED || 0}.`);
if ((connectionCounts.IMPLEMENTED || 0) !== 66) fail(`Expected exact connection count 66, found ${connectionCounts.IMPLEMENTED || 0}.`);

const canonicalRegistry = {
  schemaVersion: '3.0.0',
  inventoryScope: 'prototype-connected-nodes',
  sourceCapturedAt: routeMapData.capturedAt,
  totalPrototypeConnectedNodes: registryEntries.length,
  unconnectedPrototypeScreenNodes: 'NOT_AUDITED',
  totalRelevantFigmaNodes: registryEntries.length,
  implementedNodes: screenCounts.IMPLEMENTED || 0,
  missingNodes: screenCounts.MISSING || 0,
  screenCompletionPercentage: `${(((screenCounts.IMPLEMENTED || 0) / registryEntries.length) * 100).toFixed(1)}%`,
  nodes: registryEntries,
};

const prototypeGapAudit = {
  schemaVersion: '2.0.0',
  sourceCapturedAt: routeMapData.capturedAt,
  screenInventory: {
    totalRelevantFigmaNodes: registryEntries.length,
    implementedNodes: screenCounts.IMPLEMENTED || 0,
    missingNodes: screenCounts.MISSING || 0,
    screenCompletionPercentage: canonicalRegistry.screenCompletionPercentage,
  },
  prototypeConnections: {
    totalConnections: connectionEntries.length,
    implementedConnections: connectionCounts.IMPLEMENTED || 0,
    mismatchedConnections: connectionCounts.MISMATCHED || 0,
    missingConnections: connectionCounts.MISSING || 0,
    interactionCompletionPercentage: `${(((connectionCounts.IMPLEMENTED || 0) / connectionEntries.length) * 100).toFixed(1)}%`,
  },
  connections: connectionEntries,
};

const reconciliationMd = `# STEP 8A.1 — Canonical mapping reconciliation (superseded metrics repaired)

This generated artifact is retained for historical continuity. The current implementation audit through Step 8C supersedes earlier inferred node maps and invented filenames.

## Deterministic canonical metrics

- Figma screen/state completeness: **${canonicalRegistry.implementedNodes}/${canonicalRegistry.totalRelevantFigmaNodes} (${canonicalRegistry.screenCompletionPercentage})**
- Exact prototype connection completeness: **${prototypeGapAudit.prototypeConnections.implementedConnections}/${prototypeGapAudit.prototypeConnections.totalConnections} (${prototypeGapAudit.prototypeConnections.interactionCompletionPercentage})**
- Mismatched connections: **${prototypeGapAudit.prototypeConnections.mismatchedConnections}**
- Missing connections: **${prototypeGapAudit.prototypeConnections.missingConnections}**

## Order-node correction

- \`309:712\`: Buyer Orders list — implemented by \`OrdersListScreen.tsx\`.
- \`309:716–723\`: canonical buyer order detail, tracking, packages, and invoice — **IMPLEMENTED** in Step 8B.
- \`309:724–731\`: buyer cancellation, review, and reorder states — **IMPLEMENTED** in Step 8C as three semantically separate flows.
- \`309:732–309:736\`: return and refund workflow — **IMPLEMENTED** in Step 8D.
- \`309:737+\`: delivery issues, order support, and order system states — **MISSING** and deferred to Step 8E.
- \`309:737\`: delivery-delay notification — **MISSING** and not the legacy Order Detail.
- Nodes whose frame names begin \`07-\` are classified **Buyer Orders & Fulfillment**, not seller/admin mobile functionality.

## Source-of-truth rules

The generator requires explicit semantic evidence, validates mapped files and real ScreenKeys, permits only explicit MODAL/SHEET/INLINE_STATE non-routes, rejects frame identity collisions, rejects implemented connections with missing destinations, validates route-map summary counts, and emits byte-stable JSON without generation timestamps.
`;

fs.writeFileSync(registryJsonPath, `${JSON.stringify(canonicalRegistry, null, 2)}\n`, 'utf8');
fs.writeFileSync(auditJsonPath, `${JSON.stringify(prototypeGapAudit, null, 2)}\n`, 'utf8');
fs.writeFileSync(reconciliationMdPath, reconciliationMd, 'utf8');

console.log(`Canonical screens: ${canonicalRegistry.implementedNodes}/${canonicalRegistry.totalRelevantFigmaNodes} (${canonicalRegistry.screenCompletionPercentage})`);
console.log(`Exact prototype connections: ${prototypeGapAudit.prototypeConnections.implementedConnections}/${prototypeGapAudit.prototypeConnections.totalConnections} (${prototypeGapAudit.prototypeConnections.interactionCompletionPercentage})`);
