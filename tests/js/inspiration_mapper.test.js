const fs = require('fs');
const path = require('path');

const html = `<!doctype html><html><body>
  <div id="mapperToolbar"><button class="mode-btn" data-mode="place"></button><button class="mode-btn" data-mode="drag"></button><span id="previewControls"><button data-width="390"></button><button data-width="768"></button><button data-width="1440"></button></span></div>
  <div id="mapperStage"><div id="mapperContainer"><img id="mapperImage"></div></div>
  <div id="itemsCard"><span id="itemCount"></span><div id="unavailableWarning"></div><div id="itemsList"></div></div>
  <span id="saveIndicator"></span><button id="saveRetry"></button>
  <div id="searchModal"><div class="search-panel"><input id="searchInput"><div id="searchResults"></div></div></div>
  <div id="mapperLiveRegion"></div>
</body></html>`;

const mapperConfig = {
  containerId: 'mapperContainer', imageId: 'mapperImage', inspirationId: 1, csrfToken: 'csrf',
  searchUrl: '/admin/products-search', storeUrl: '/admin/inspirations/1/hotspots',
  updateUrlTemplate: '/admin/inspirations/1/hotspots/__HOTSPOT_ID__',
  destroyUrlTemplate: '/admin/inspirations/1/hotspots/__HOTSPOT_ID__',
  existingItems: [{ id: 1, hotspot_id: 11, display_order: 1, x: .2, y: .4, product: { id: 10, name: 'Sofa', image: '/sofa.webp', price: '100 MAD', available: true } }],
  translations: { saved: 'Saved', saving: 'Saving', error: 'Error', noResults: 'None', loading: 'Loading', undone: 'Undone', redone: 'Redone', deleteConfirm: 'Delete?', unavailable: 'unavailable', recents: 'Recent', replace: 'Replace', delete: 'Delete', saveFailed: 'Failed' },
};

let nextId = 20;
let failNext = false;
let failCount = 0;
let resolveSlowSearch = null;
const requestLog = [];
const mockFetch = async (url, options = {}) => {
  requestLog.push({ url: String(url), method: options.method || 'GET', body: options.body });
  if (failNext || failCount > 0) {
    failNext = false;
    if (failCount > 0) failCount -= 1;
    return { ok: false, status: 500, json: async () => ({ message: 'server failed' }) };
  }
  if (String(url).includes('products-search') && String(url).includes('q=slow')) {
    return new Promise((resolve) => {
      resolveSlowSearch = () => resolve({ ok: true, status: 200, json: async () => ({ data: [{ id: 13, name: 'Slow result', image: '/slow.webp', price: '70 MAD', available: true, stock: 'in_stock' }] }) });
    });
  }
  if (String(url).includes('products-search') && String(url).includes('q=fast')) {
    return { ok: true, status: 200, json: async () => ({ data: [{ id: 14, name: 'Fast result', image: '/fast.webp', price: '80 MAD', available: true, stock: 'in_stock' }] }) };
  }
  if (String(url).includes('products-search')) {
    return { ok: true, status: 200, json: async () => ({ data: [{ id: 12, name: 'Lamp', image: '/lamp.webp', price: '50 MAD', available: true, stock: 'in_stock' }] }) };
  }
  if (options.method === 'POST') {
    nextId += 1;
    const body = JSON.parse(options.body);
    return { ok: true, status: 201, json: async () => ({ success: true, item: { id: nextId, hotspot_id: nextId + 100, display_order: body.display_order || 2, x: body.x, y: body.y, product: { id: body.product_id, name: 'Lamp', image: '/lamp.webp', price: '50 MAD', available: true, stock_status: 'in_stock' } } }) };
  }
  return { ok: true, status: 200, json: async () => ({ success: true }) };
};

const wait = (ms = 0) => new Promise((resolve) => setTimeout(resolve, ms));
const assert = (condition, message) => {
  if (!condition) throw new Error(message);
  console.log(`[PASS] ${message}`);
};

(async () => {
  const { Window } = await import('../../mayush-mobile/node_modules/happy-dom/lib/index.js');
  const window = new Window({ url: 'https://mayush.test/admin/inspirations/1/mapper' });
  window.document.write(html);
  window.ResizeObserver = class { observe() {} };
  window.requestAnimationFrame = (callback) => setTimeout(callback, 0);
  window.cancelAnimationFrame = clearTimeout;
  window.confirm = () => true;
  window.MAPPER_CONFIG = mapperConfig;
  window.fetch = mockFetch;
  const image = window.document.getElementById('mapperImage');
  image.getBoundingClientRect = () => ({ left: 0, top: 0, width: 1000, height: 500, right: 1000, bottom: 500 });
  const script = fs.readFileSync(path.join(__dirname, '../../public/js/inspiration-mapper.js'), 'utf8');
  window.eval(script);
  window.document.dispatchEvent(new window.Event('DOMContentLoaded'));
  const mapper = window.mapper;

  assert(mapper.items.length === 1, 'Mapper hydrates persisted hotspots');
  const marker = window.document.querySelector('.hotspot-marker');
  assert(marker?.getAttribute('role') === 'button' && marker?.getAttribute('aria-label').includes('Sofa'), 'Markers expose accessible product labels');
  marker.dispatchEvent(new window.KeyboardEvent('keydown', { key: 'Enter', bubbles: true }));
  assert(Boolean(window.document.querySelector('.marker-context-menu')), 'Keyboard activation opens the marker context menu');

  window.document.getElementById('mapperContainer').dispatchEvent(new window.MouseEvent('click', { clientX: 300, clientY: 250, bubbles: true }));
  const input = window.document.getElementById('searchInput');
  input.value = 'la';
  input.dispatchEvent(new window.Event('input', { bubbles: true }));
  await wait(350);
  window.document.querySelector('.search-result-item').click();
  await wait();
  assert(mapper.items.length === 2 && mapper.items[1].x === .3 && mapper.items[1].y === .5, 'Placement persists normalized coordinates and renders optimistically');

  mapper.openProductSearch();
  input.value = 'slow';
  const slowSearch = mapper.doSearch();
  await wait();
  input.value = 'fast';
  await mapper.doSearch();
  resolveSlowSearch();
  await slowSearch;
  assert(window.document.querySelector('.search-result-item .name')?.textContent === 'Fast result', 'A stale product search cannot replace newer results');
  mapper.closeProductSearch();

  await mapper.undo();
  assert(mapper.items.length === 1, 'Undo reverses a persisted placement');
  await mapper.redo();
  assert(mapper.items.length === 2, 'Redo recreates a persisted placement');

  requestLog.length = 0;
  mapper.items[0].x = .21;
  mapper.saveHotspot(mapper.items[0], { x: .2, y: .4 });
  mapper.items[1].x = .31;
  mapper.saveHotspot(mapper.items[1], { x: .3, y: .5 });
  await wait(600);
  const moveRequests = requestLog.filter((entry) => entry.method === 'PUT');
  assert(moveRequests.length === 2, 'Rapid changes to different hotspots are each persisted');

  failCount = 2;
  mapper.items[0].x = .22;
  mapper.saveHotspot(mapper.items[0], { x: .21, y: mapper.items[0].y });
  mapper.items[1].x = .32;
  mapper.saveHotspot(mapper.items[1], { x: .31, y: mapper.items[1].y });
  await wait(600);
  assert(mapper.failedActions.size === 2, 'Concurrent failed hotspot saves remain independently retryable');
  assert(mapper.hasPendingSaves(), 'Failed operations keep the unload protection active');
  await mapper.retryFailedActions();
  await wait(600);
  assert(mapper.failedActions.size === 0 && !mapper.hasPendingSaves(), 'Retry drains every failed hotspot save after success');

  const historyItem = mapper.items[0];
  const historyAction = { type: 'move', item: historyItem, oldX: historyItem.x, oldY: historyItem.y, newX: .42, newY: .44 };
  historyItem.x = historyAction.newX;
  historyItem.y = historyAction.newY;
  mapper.pushUndo(historyAction);
  failNext = true;
  await mapper.undo();
  assert(mapper.undoStack.includes(historyAction) && historyItem.x === historyAction.newX, 'Failed undo restores UI state and keeps history in the source stack');
  await mapper.retryFailedActions();
  assert(mapper.redoStack.includes(historyAction) && historyItem.x === historyAction.oldX, 'Retrying a failed undo moves history only after persistence succeeds');

  mapper.togglePreview();
  mapper.setPreviewWidth(768);
  assert(window.document.body.classList.contains('preview-mode') && window.document.getElementById('mapperStage').style.maxWidth === '768px', 'Preview mode applies tablet device width');

  failNext = true;
  const deleteCount = mapper.items.length;
  await mapper.deleteItem(0);
  assert(mapper.items.length === deleteCount, 'Failed optimistic deletion restores the deleted item');
  await mapper.retryFailedActions();
  assert(mapper.items.length === deleteCount - 1, 'Retrying a failed deletion persists and removes the item');

  failNext = true;
  const itemCountBeforeFailure = mapper.items.length;
  await mapper.placeProduct({ id: 99, name: 'Failing' }, { x: .1, y: .1 });
  assert(mapper.items.length === itemCountBeforeFailure, 'Failed optimistic placement rolls back cleanly');
  assert(window.document.getElementById('saveIndicator').classList.contains('error'), 'Failed saves enter the visible error state');
  assert(window.document.getElementById('saveRetry').style.display === 'inline-block', 'Failed saves expose a retry action');
})().catch((error) => {
  console.error(`[FAIL] ${error.message}`);
  process.exit(1);
});
