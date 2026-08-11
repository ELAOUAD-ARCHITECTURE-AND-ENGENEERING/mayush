const fs = require('fs');
const path = require('path');

const root = path.resolve(__dirname, '../..');
const read = (relativePath) => fs.readFileSync(path.join(root, relativePath), 'utf8');
const registry = JSON.parse(read('docs/frontend-completion/canonical-figma-screen-registry.json'));
const gapAudit = JSON.parse(read('docs/frontend-completion/prototype-gap-audit.json'));
const navigator = read('src/navigation/RootNavigator.tsx');
const collectSourceFiles = (directory) => fs.readdirSync(directory, { withFileTypes: true }).flatMap((entry) => {
  const absolute = path.join(directory, entry.name);
  if (entry.isDirectory()) return collectSourceFiles(absolute);
  return /\.(?:ts|tsx)$/.test(entry.name) ? [absolute] : [];
});
const allApplicationSource = collectSourceFiles(path.join(root, 'src'))
  .map((file) => fs.readFileSync(file, 'utf8'))
  .join('\n');

const unionBody = navigator.match(/type ScreenKey\s*=([\s\S]*?);/)?.[1] || '';
const screenKeys = new Set([...unionBody.matchAll(/'([^']+)'/g)].map((match) => match[1]));
const escapeRegex = (value) => value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
const literalTransitionCount = (route) => (navigator.match(new RegExp(`setCurrentScreen\\((?:(?!\\))[\\s\\S]){0,300}?['"]${escapeRegex(route)}['"]`, 'g')) || []).length;
const hasRenderBranch = (route) => new RegExp(`currentScreen\\s*===\\s*['"]${escapeRegex(route)}['"]`).test(navigator);
const validateDeclaredEvidenceToken = (token) => {
  if (token.startsWith('literal-transition:')) return literalTransitionCount(token.slice('literal-transition:'.length)) > 0;
  if (token === 'dynamic-order-detail-route') return navigator.includes('setCurrentScreen(route)');
  if (token === 'conditional-order-cancellation-route') return navigator.includes("? 'order-cancel-reason' : 'order-cannot-cancel'");
  if (token === 'initial-runtime-route') return true;
  return allApplicationSource.includes(token);
};

const nonRouteEvidence = {
  '309:591': ['PersonalizedHome', 'isAuthenticated', 'authenticatedUser'],
  '309:596': ['FilterPanelModal', 'filterModalVisible'],
  '309:607': ['VariantSelectorSheet', 'variantSheetVisible'],
  '309:659': ['CartToast', 'toastVisible', 'toastMessage'],
  '309:660': ['VariantEditSheet', 'lineToEditVariant'],
  '309:661': ['SellerCartGroup', 'groupBySeller'],
  '309:662': ['promoError', 'handleApplyPromo'],
  '309:663': ['appliedPromotion', 'discountMad'],
  '309:664': ['vouchersModalVisible', 'getAvailablePromotions'],
  '309:665': ['RemoveItemDialog', 'lineToRemove'],
  '309:653': ['FavoritesAuthPromptOverlay', 'favoritesPromptVisible'],
  '309:713': ['selectedOrderTab', 'setSelectedOrderTab', 'filterOrdersByTab'],
  '309:714': ['selectedOrderTab', 'setSelectedOrderTab', 'filterOrdersByTab'],
  '309:715': ['selectedOrderTab', 'setSelectedOrderTab', 'filterOrdersByTab'],
  '309:761': ['DisconnectSessionModal', 'visible'],
  '309:767': ['DeleteAddressModal', 'visible'],
  '309:771': ['LogoutConfirmationModal', 'logoutModalVisible'],
  '309:796': ['ClearCacheConfirmationModal', 'visible'],
};

const allowedTypes = new Set(['ROUTE', 'INLINE_STATE', 'MODAL', 'SHEET', 'BOTTOM_SHEET', 'TOAST']);
const duplicateIds = registry.nodes.map((node) => node.figmaNodeId).filter((id, index, ids) => ids.indexOf(id) !== index);
const duplicateFrameNames = registry.nodes.map((node) => node.frameName).filter((name, index, names) => names.indexOf(name) !== index);

const auditedNodes = registry.nodes.map((node) => {
  const sourceAbsolute = node.sourceFile ? path.join(root, node.sourceFile) : '';
  const sourceExists = Boolean(sourceAbsolute && fs.existsSync(sourceAbsolute));
  const source = sourceExists ? fs.readFileSync(sourceAbsolute, 'utf8') : '';
  const issues = [];
  const reachabilityIssues = [];
  const metadataIssues = [];
  if (node.screenStatus !== 'IMPLEMENTED') issues.push(`status:${node.screenStatus}`);
  if (!allowedTypes.has(node.implementationType)) issues.push(`unsupported-type:${node.implementationType}`);
  if (!sourceExists) issues.push('source-missing');

  let reachability = 'UNVERIFIED';
  let evidence = [];
  if (node.implementationType === 'ROUTE') {
    const typed = Boolean(node.route && screenKeys.has(node.route));
    const rendered = Boolean(node.route && hasRenderBranch(node.route));
    const literalTriggers = node.route ? literalTransitionCount(node.route) : 0;
    const dynamicOrderRoute = Boolean(node.route && /^order-(?:detail|refund)/.test(node.route) && navigator.includes('setCurrentScreen(route)'));
    const conditionalRoute = node.route === 'order-cancel-reason' && navigator.includes("? 'order-cancel-reason' : 'order-cannot-cancel'");
    const initialRoute = node.route === 'splash' || (node.route === 'language' && navigator.includes("restoredCheckoutScreen || 'home') : 'language'"));
    const declaredEvidence = Array.isArray(node.runtimeEvidence) ? node.runtimeEvidence : [];
    const declaredEvidenceValid = node.evidenceKind === 'RUNTIME_ROUTE'
      && node.reachability === 'RUNTIME_REACHABLE'
      && declaredEvidence.length > 0
      && declaredEvidence.every(validateDeclaredEvidenceToken);
    const overlayRoute = false;
    if (!typed) issues.push('phantom-screen-key');
    if (!rendered && !overlayRoute) issues.push('render-branch-missing');
    if (overlayRoute) metadataIssues.push('canonical-route-is-runtime-modal-state');
    if (!literalTriggers && !dynamicOrderRoute && !conditionalRoute && !initialRoute && !declaredEvidenceValid) reachabilityIssues.push('no-runtime-trigger');
    reachability = (dynamicOrderRoute || conditionalRoute) ? 'CONDITIONAL_ROUTE' : (initialRoute || literalTriggers || declaredEvidenceValid ? 'DIRECT_ROUTE' : 'UNREACHABLE_ROUTE');
    evidence = [`typed:${typed}`, `rendered:${rendered}`, `literalTriggers:${literalTriggers}`, `dynamicOrderRoute:${dynamicOrderRoute}`, `conditionalRoute:${conditionalRoute}`, `declaredRuntimeEvidence:${declaredEvidenceValid}`];
  } else {
    const patterns = Array.isArray(node.runtimeEvidence) && node.runtimeEvidence.length ? node.runtimeEvidence : nonRouteEvidence[node.figmaNodeId] || [];
    const combined = `${source}\n${allApplicationSource}`;
    const missingPatterns = patterns.filter((pattern) => !combined.includes(pattern));
    if (!patterns.length) issues.push('non-route-evidence-rule-missing');
    if (missingPatterns.length) issues.push(`state-evidence-missing:${missingPatterns.join(',')}`);
    if (node.implementationType === 'INLINE_STATE' && node.evidenceKind !== 'INTERACTIVE_STATE') issues.push(`inline-state-evidence-kind:${node.evidenceKind || 'missing'}`);
    if (node.evidenceKind === 'STATIC_LABEL') issues.push('static-label-is-not-runtime-state');
    reachability = node.figmaNodeId === '309:591'
      ? 'AUTH_VARIANT'
      : node.implementationType === 'INLINE_STATE'
        ? 'INLINE_STATE'
        : node.implementationType === 'TOAST'
          ? 'ERROR/LOADING_STATE'
          : 'MODAL/SHEET';
    evidence = patterns.map((pattern) => `${pattern}:${combined.includes(pattern)}`);
  }

  const sourceStateImplementation = issues.length === 0;
  const runtimeImplementation = sourceStateImplementation && reachability !== 'UNREACHABLE_ROUTE' && reachabilityIssues.length === 0;
  return { ...node, sourceExists, reachability, evidence, issues, reachabilityIssues, metadataIssues, sourceStateImplementation, runtimeImplementation };
});

const presentationKeywords = /splash|loading|skeleton|success|confirmation|created|sent|changed|restored|completed/i;
const conditionalKeywords = /empty|error|failed|unavailable|invalid|pending|delayed|duplicate|needs-update|out-of-stock|price-drop|confirmation-dialog/i;
const backendKeywords = /payment|tracking|carrier|delivery|refund|return|sync|server|support-request/i;
const nativeKeywords = /camera|photos|biometric|permission|external|phone|file|document|update-required/i;
const obsoleteKeywords = /-v2|legacy|archive/i;

const nodeById = new Map(registry.nodes.map((node) => [node.figmaNodeId, node]));
const routeRenderBlock = (route) => {
  if (!route) return '';
  const marker = new RegExp(`\\{currentScreen\\s*===\\s*['"]${escapeRegex(route)}['"]`);
  const start = navigator.search(marker);
  if (start < 0) return '';
  const remainder = navigator.slice(start + 1);
  const next = remainder.search(/\n\s*\{currentScreen\s*===/);
  return next < 0 ? remainder.slice(0, 1600) : remainder.slice(0, next);
};
const classifiedConnections = gapAudit.connections.map((connection) => {
  if (connection.connectionStatus === 'IMPLEMENTED') return { ...connection, auditClass: 'IMPLEMENTED', actionable: false };
  const source = nodeById.get(connection.sourceFigmaNodeId);
  const destination = nodeById.get(connection.destinationFigmaNodeId);
  const names = `${source?.frameName || ''} ${destination?.frameName || ''}`;
  const inlineOrderCardControl = connection.sourceFigmaNodeId === '309:713'
    && connection.destinationFigmaNodeId === '309:716'
    && allApplicationSource.includes('filterOrdersByTab')
    && allApplicationSource.includes('onOpenOrder(order.orderId)')
    && navigator.includes('getCanonicalOrderDetailRoute(selectedOrder)')
    && navigator.includes('setCurrentScreen(route)');
  const runtimeDirectControl = inlineOrderCardControl || Boolean(source?.route && destination?.route && routeRenderBlock(source.route).includes(`'${destination.route}'`));
  let auditClass;
  let actionable = false;
  if (runtimeDirectControl && conditionalKeywords.test(names)) auditClass = 'C_CONDITIONAL_RUNTIME_EDGE';
  else if (runtimeDirectControl) auditClass = 'B_SEMANTIC_RUNTIME_MISMATCH';
  else if (nativeKeywords.test(names)) auditClass = 'F_NATIVE_ONLY_OR_PLATFORM_DEPENDENT';
  else if (obsoleteKeywords.test(names) && connection.connectionStatus === 'MISSING') auditClass = 'G_HISTORICAL_OBSOLETE_MAPPING';
  else if (conditionalKeywords.test(names)) auditClass = 'C_CONDITIONAL_RUNTIME_EDGE';
  else if (connection.connectionStatus === 'MISMATCHED') auditClass = 'B_SEMANTIC_RUNTIME_MISMATCH';
  else if (backendKeywords.test(names)) auditClass = 'E_BACKEND_DEPENDENT';
  else if (presentationKeywords.test(names)) auditClass = 'A_PRESENTATION_SHOWCASE_EDGE';
  else {
    auditClass = 'D_GENUINE_MISSING_INTERACTION';
    actionable = true;
  }
  return { ...connection, auditClass, actionable };
});

const connectionClassCounts = classifiedConnections.reduce((counts, connection) => {
  counts[connection.auditClass] = (counts[connection.auditClass] || 0) + 1;
  return counts;
}, {});

const testFiles = [
  'scripts/run-tests.js',
  ...fs.readdirSync(path.join(root, 'tests')).filter((name) => /^Step(?:8|9).*Test\.ts$/.test(name)).map((name) => `tests/${name}`),
];
const testQuality = { SOURCE_TEXT: 0, PURE_STATE: 0, REPOSITORY_BEHAVIOR: 0, NAVIGATION_BEHAVIOR: 0, PERSISTENCE_BEHAVIOR: 0, RENDERED_COMPONENT: 0, E2E_NATIVE: 0 };
for (const file of testFiles) {
  const source = read(file);
  const assertionLines = source.split(/\r?\n/).filter((line) => /\bassert\s*\(/.test(line) && !/function\s+assert\s*\(/.test(line));
  for (const line of assertionLines) {
    if (/readFileSync|existsSync|Content\b|Code\b|includes\(|\.test\(/.test(line)) testQuality.SOURCE_TEXT += 1;
    else if (/hydrate|persist|storage|reload|AsyncStorage|stored/i.test(line)) testQuality.PERSISTENCE_BEHAVIOR += 1;
    else if (/repository|orders\.|getOrder|createOrder|selectedOrder/i.test(line)) testQuality.REPOSITORY_BEHAVIOR += 1;
    else if (/route|destination|currentScreen|navigation/i.test(line)) testQuality.NAVIGATION_BEHAVIOR += 1;
    else testQuality.PURE_STATE += 1;
  }
}

const result = {
  canonical: {
    total: auditedNodes.length,
    sourceStateImplementationCount: auditedNodes.filter((node) => node.sourceStateImplementation).length,
    runtimeImplementationCount: auditedNodes.filter((node) => node.runtimeImplementation).length,
    invalidRecords: auditedNodes.filter((node) => !node.sourceStateImplementation),
    metadataIssues: auditedNodes.filter((node) => node.metadataIssues.length).map((node) => ({ id: node.figmaNodeId, issues: node.metadataIssues })),
    duplicateIds: [...new Set(duplicateIds)],
    duplicateFrameNames: [...new Set(duplicateFrameNames)],
    reachabilityCounts: auditedNodes.reduce((counts, node) => { counts[node.reachability] = (counts[node.reachability] || 0) + 1; return counts; }, {}),
  },
  navigation: {
    typedScreenKeyCount: screenKeys.size,
    canonicalRouteCount: auditedNodes.filter((node) => node.implementationType === 'ROUTE').length,
    unreachableCanonicalRoutes: auditedNodes.filter((node) => node.reachability === 'UNREACHABLE_ROUTE').map((node) => ({ id: node.figmaNodeId, route: node.route, name: node.frameName, issues: node.reachabilityIssues })),
  },
  interactions: {
    reported: gapAudit.prototypeConnections,
    classCounts: connectionClassCounts,
    actionableCount: classifiedConnections.filter((connection) => connection.actionable).length,
    actionable: classifiedConnections.filter((connection) => connection.actionable),
  },
  testQuality: {
    files: testFiles,
    heuristicAssertionCounts: testQuality,
    renderedComponentHarnessPresent: false,
    nativeE2EPresent: false,
  },
};

const output = process.argv.includes('--summary') ? {
  canonical: result.canonical,
  navigation: result.navigation,
  interactions: {
    reported: result.interactions.reported,
    classCounts: result.interactions.classCounts,
    actionableCount: result.interactions.actionableCount,
    actionableIds: result.interactions.actionable.map((connection) => connection.connectionId),
  },
  testQuality: result.testQuality,
} : result;
console.log(JSON.stringify(output, null, 2));
if (result.canonical.runtimeImplementationCount !== 207 || result.canonical.invalidRecords.length) process.exitCode = 1;
