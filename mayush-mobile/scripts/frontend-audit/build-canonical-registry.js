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
const connections = routeMapData.connections || [];
const statusOverrides = routeMapData.connectionStatusOverrides || {};

if (connections.length !== 206) fail(`Expected 206 prototype connections, found ${connections.length}.`);

const screenKeyMatch = navigatorCode.match(/export type ScreenKey\s*=([\s\S]*?);\s*\n/);
if (!screenKeyMatch) fail('Unable to parse ScreenKey from RootNavigator.tsx.');
const screenKeys = new Set([...screenKeyMatch[1].matchAll(/'([^']+)'/g)].map((match) => match[1]));

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
  '309:592': { frameName: '02-categories-photo-grid-fr', file: 'src/screens/discovery/CategoriesScreen.tsx', route: 'categories', implementationType: 'ROUTE' },
  '309:594': { frameName: '02-subcategory-canapes-filtered-list', file: 'src/screens/discovery/CategoryProductListScreen.tsx', route: 'category-products', implementationType: 'ROUTE' },
  '309:604': { frameName: '03-product-detail-image-carousel-add-to-cart', file: 'src/screens/product/ProductDetailsScreen.tsx', route: 'product-details', implementationType: 'ROUTE' },
  '309:605': { frameName: '03-product-gallery-zoom-thumbnails', file: 'src/screens/product/ProductGalleryScreen.tsx', route: 'product-gallery', implementationType: 'ROUTE' },
  '309:607': { frameName: '03-product-variant-selector-color-material-size', file: 'src/screens/product/VariantSelectorSheet.tsx', route: null, implementationType: 'SHEET' },
  '309:611': { frameName: '03-product-added-to-cart-confirmation', file: 'src/screens/commerce/AddedToCartConfirmationScreen.tsx', route: 'added-to-cart', implementationType: 'ROUTE' },
  '309:658': { frameName: '05-cart-items-promo-code-summary-fr', file: 'src/screens/commerce/CartScreen.tsx', route: 'cart', implementationType: 'ROUTE' },
  '309:705': { frameName: '06-order-thank-you-confirmation-summary-v2-fr', file: 'src/screens/orders/OrderThankYouScreen.tsx', route: 'order-thank-you', implementationType: 'ROUTE' },
  '309:712': { frameName: '07-orders-list-all-tabs-fr', file: 'src/screens/orders/OrdersListScreen.tsx', route: 'orders-list', implementationType: 'ROUTE' },
};

const nonRouteCsvMappings = {
  '309:596': { implementationType: 'SHEET' },
  '309:761': { implementationType: 'MODAL' },
  '309:767': { implementationType: 'MODAL' },
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
  } else if (!['MODAL', 'SHEET', 'INLINE_STATE'].includes(mapping.implementationType)) {
    fail(`Unsupported non-route implementationType for ${figmaNodeId}: ${mapping.implementationType}.`);
  }
}

if (mappings.has('309:716')) fail('309:716 must remain MISSING until Step 8B implements the canonical order detail.');
if (mappings.has('309:737')) fail('309:737 is a delivery-delay notification and must not map to legacy Order Detail.');

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
if ((screenCounts.IMPLEMENTED || 0) !== 151) fail(`Expected evidence-backed screen count 151, found ${screenCounts.IMPLEMENTED || 0}.`);
if ((connectionCounts.IMPLEMENTED || 0) !== 45) fail(`Expected exact connection count 45, found ${connectionCounts.IMPLEMENTED || 0}.`);

const canonicalRegistry = {
  schemaVersion: '2.0.0',
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

This generated artifact is retained for historical continuity. The current implementation audit and Step 8B.0 preparation supersede earlier inferred node maps and invented filenames.

## Deterministic canonical metrics

- Figma screen/state completeness: **${canonicalRegistry.implementedNodes}/${canonicalRegistry.totalRelevantFigmaNodes} (${canonicalRegistry.screenCompletionPercentage})**
- Exact prototype connection completeness: **${prototypeGapAudit.prototypeConnections.implementedConnections}/${prototypeGapAudit.prototypeConnections.totalConnections} (${prototypeGapAudit.prototypeConnections.interactionCompletionPercentage})**
- Mismatched connections: **${prototypeGapAudit.prototypeConnections.mismatchedConnections}**
- Missing connections: **${prototypeGapAudit.prototypeConnections.missingConnections}**

## Order-node correction

- \`309:712\`: Buyer Orders list — implemented by \`OrdersListScreen.tsx\`.
- \`309:716\`: canonical buyer order detail in preparation — **MISSING** until Step 8B.
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
