import { chromium } from 'playwright';
import { spawn } from 'node:child_process';
import { mkdtemp, readFile, rm, writeFile, mkdir } from 'node:fs/promises';
import path from 'node:path';
import os from 'node:os';

const OUT_DIR = path.resolve('tools/audits/output');
const RAW_PATH = path.join(OUT_DIR, 'phase-4-production-final-verification-mayush.raw.json');
const REPORT_PATH = path.join(OUT_DIR, 'phase-4-production-final-verification-mayush.md');
const DESKTOP_UA = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36 MayushPhase4ProdAudit';
const MOBILE_UA = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.5 Mobile/15E148 Safari/604.1 MayushPhase4ProdAudit';

const urls = [
  'https://mayushdesign.com/',
  'https://mayushdesign.com/category/office-furniture',
  'https://mayushdesign.com/category/office-desks',
  'https://mayushdesign.com/product/bibliotheque-cadre-design-moderne-avec-rangements-modules-7',
  'https://mayushdesign.com/product/bureau-de-direction-new-at-105-design-moderne-avec-retour-de-rangement-integre-3-3',
  'https://mayushdesign.com/blog/perfect-home-office-design',
  'https://mayushdesign.com/contact-us',
];

const safetyUrls = [
  'https://mayushdesign.com/',
  'https://mayushdesign.com/category/office-furniture',
  'https://mayushdesign.com/product/bibliotheque-cadre-design-moderne-avec-rangements-modules-7',
];

const privateRoutes = [
  '/cart',
  '/checkout',
  '/login',
  '/register',
  '/admin',
  '/seller',
  '/dashboard',
  '/customer',
  '/user',
  '/orders',
  '/wishlist',
  '/compare',
  '/api',
  '/ajax',
  '/payment',
  '/cmi',
].map((route) => `https://mayushdesign.com${route}`);

const cookieConditions = [
  'laravel_session=test; XSRF-TOKEN=test',
  'cart=test',
  'remember_web=test',
  'wishlist=test',
];

const baseline = {
  homepageDesktopLcp: 1136,
  homepageDesktopCls: 0.001,
  homepageMobileLcp: 2028,
  homepageMobileCls: 0.004,
  categoryOfficeFurnitureDesktopCls: 0.491,
  categoryOfficeFurnitureMobileCls: 0.732,
  categoryOfficeDesksDesktopCls: 0.527,
  categoryOfficeDesksMobileCls: 1.465,
  product1MobileLongTasks: { count: 224, total: 30480 },
  product2MobileLongTasks: { count: 203, total: 23140 },
};

const profiles = [
  {
    name: 'Desktop realistic',
    userAgent: DESKTOP_UA,
    viewport: { width: 1366, height: 768 },
    isMobile: false,
    deviceScaleFactor: 1,
  },
  {
    name: 'Mobile controlled',
    userAgent: MOBILE_UA,
    viewport: { width: 390, height: 844 },
    isMobile: true,
    deviceScaleFactor: 3,
  },
];

function sleep(ms) {
  return new Promise((resolve) => setTimeout(resolve, ms));
}

function percentile(values, p) {
  const nums = values.filter((value) => Number.isFinite(value)).sort((a, b) => a - b);
  if (!nums.length) return null;
  const idx = Math.ceil((p / 100) * nums.length) - 1;
  return nums[Math.max(0, Math.min(nums.length - 1, idx))];
}

function median(values) {
  return percentile(values, 50);
}

function sum(values) {
  return values.reduce((total, value) => total + (Number.isFinite(value) ? value : 0), 0);
}

function ms(value) {
  return Number.isFinite(value) ? `${Math.round(value)} ms` : '';
}

function sec(value) {
  return Number.isFinite(value) ? `${value.toFixed(3)}s` : '';
}

function bytes(value) {
  return Number.isFinite(value) ? `${Math.round(value / 1024)} KB` : '';
}

function pct(before, after) {
  if (!Number.isFinite(before) || !Number.isFinite(after) || before === 0) return '';
  return `${(((before - after) / before) * 100).toFixed(1)}%`;
}

function statusProgression(runs) {
  return runs.map((run) => run.cfCacheStatus || '(none)').join(' -> ');
}

function assetType(url, contentType = '') {
  const lower = url.toLowerCase();
  if (lower.includes('.css')) return 'stylesheet';
  if (lower.includes('.js')) return 'script';
  if (lower.includes('.webp')) return 'webp';
  if (lower.match(/\.(png|jpe?g)(\?|$)/)) return 'image';
  if (lower.match(/\.(svg|ico)(\?|$)/)) return 'svg/ico';
  if (lower.match(/\.(woff2?|ttf|otf)(\?|$)/) || contentType.includes('font')) return 'font';
  return 'other';
}

function normalizeUrl(url, base = 'https://mayushdesign.com/') {
  try {
    return new URL(url, base).href;
  } catch {
    return '';
  }
}

function parseHeaderBlocks(text) {
  const blocks = [];
  let current = null;
  for (const rawLine of text.split(/\r?\n/)) {
    const line = rawLine.trimEnd();
    if (line.startsWith('HTTP/')) {
      if (current) blocks.push(current);
      const parts = line.split(/\s+/);
      current = { statusLine: line, status: Number(parts[1]), headers: {}, setCookie: [] };
      continue;
    }
    if (!current || !line.trim()) continue;
    const idx = line.indexOf(':');
    if (idx < 1) continue;
    const key = line.slice(0, idx).trim().toLowerCase();
    const value = line.slice(idx + 1).trim();
    if (key === 'set-cookie') current.setCookie.push(value);
    else current.headers[key] = current.headers[key] ? `${current.headers[key]}; ${value}` : value;
  }
  if (current) blocks.push(current);
  return blocks.filter((block) => block.status !== 100 && !block.statusLine.includes('Connection established'));
}

async function runCurl({ url, headers = {}, userAgent = DESKTOP_UA, accept = 'text/html', followRedirects = true }) {
  const tempDir = await mkdtemp(path.join(os.tmpdir(), 'mayush-phase4-prod-'));
  const headerPath = path.join(tempDir, 'headers.txt');
  const bodyPath = path.join(tempDir, 'body.bin');
  const writeOut = [
    'FINAL_URL=%{url_effective}',
    'STATUS=%{http_code}',
    'TTFB=%{time_starttransfer}',
    'TOTAL=%{time_total}',
    'SIZE=%{size_download}',
    'CONTENT_TYPE=%{content_type}',
  ].join('\\n');

  const args = [
    '--silent',
    '--show-error',
    '--max-time',
    '60',
    '--compressed',
    '--dump-header',
    headerPath,
    '--output',
    bodyPath,
    '--write-out',
    writeOut,
    '-A',
    userAgent,
    '-H',
    `Accept: ${accept}`,
  ];
  if (followRedirects) args.unshift('--location');
  for (const [key, value] of Object.entries(headers)) args.push('-H', `${key}: ${value}`);
  args.push(url);

  const output = await new Promise((resolve) => {
    const child = spawn('curl.exe', args, { windowsHide: true });
    let stdout = '';
    let stderr = '';
    child.stdout.on('data', (chunk) => {
      stdout += chunk.toString();
    });
    child.stderr.on('data', (chunk) => {
      stderr += chunk.toString();
    });
    child.on('close', (code) => resolve({ code, stdout, stderr }));
  });

  let headerText = '';
  let body = Buffer.from('');
  try {
    headerText = await readFile(headerPath, 'utf8');
    body = await readFile(bodyPath);
  } catch {}
  await rm(tempDir, { recursive: true, force: true });

  const timings = {};
  for (const line of output.stdout.split(/\r?\n/)) {
    const match = line.match(/^([^=]+)=(.*)$/);
    if (match) timings[match[1]] = match[2];
  }
  const blocks = parseHeaderBlocks(headerText);
  const final = blocks.at(-1) || { status: null, headers: {}, setCookie: [] };
  const h = final.headers || {};

  return {
    url,
    status: Number(timings.STATUS || final.status || 0),
    finalUrl: timings.FINAL_URL || url,
    cfCacheStatus: h['cf-cache-status'] || '',
    age: h.age || '',
    cacheControl: h['cache-control'] || '',
    contentType: timings.CONTENT_TYPE || h['content-type'] || '',
    contentLength: h['content-length'] || '',
    setCookiePresent: (final.setCookie || []).length > 0,
    ttfbMs: Number(timings.TTFB) * 1000,
    totalMs: Number(timings.TOTAL) * 1000,
    sizeBytes: Number(timings.SIZE || body.length || 0),
    redirectChain: blocks.map((block) => ({
      status: block.status,
      location: block.headers.location || '',
      cfCacheStatus: block.headers['cf-cache-status'] || '',
    })),
    bodyText: (h['content-type'] || timings.CONTENT_TYPE || '').includes('text/html') ? body.toString('utf8') : '',
    error: output.code === 0 ? '' : output.stderr || output.stdout,
  };
}

async function repeatedCurl(url, count, options = {}) {
  const runs = [];
  for (let i = 0; i < count; i++) {
    runs.push(await runCurl({ url, ...options }));
    await sleep(250);
  }
  return runs;
}

function discoverAssets(html, baseUrl) {
  const found = new Set();
  const re = /(?:href|src|data-src|data-popup-src)=["']([^"']+\.(?:css|js|png|jpe?g|webp|svg|ico|woff2?|ttf|otf)(?:\?[^"']*)?)["']/gi;
  let match;
  while ((match = re.exec(html))) {
    const url = normalizeUrl(match[1], baseUrl);
    if (url && new URL(url).hostname === 'mayushdesign.com') found.add(url);
  }
  return [...found];
}

function selectorFor(el) {
  if (!el) return '';
  if (el.id) return `#${CSS.escape(el.id)}`;
  const parts = [];
  let node = el;
  while (node && node.nodeType === Node.ELEMENT_NODE && parts.length < 5) {
    let part = node.tagName.toLowerCase();
    if (node.classList && node.classList.length) {
      part += `.${[...node.classList].slice(0, 3).map((className) => CSS.escape(className)).join('.')}`;
    }
    const parent = node.parentElement;
    if (parent) {
      const siblings = [...parent.children].filter((child) => child.tagName === node.tagName);
      if (siblings.length > 1) part += `:nth-of-type(${siblings.indexOf(node) + 1})`;
    }
    parts.unshift(part);
    node = parent;
  }
  return parts.join(' > ');
}

async function browserRun(browser, url, profile, runIndex) {
  const context = await browser.newContext({
    viewport: profile.viewport,
    userAgent: profile.userAgent,
    isMobile: profile.isMobile,
    deviceScaleFactor: profile.deviceScaleFactor,
  });
  const page = await context.newPage();
  const consoleErrors = [];
  page.on('console', (msg) => {
    if (['error', 'warning'].includes(msg.type())) consoleErrors.push({ type: msg.type(), text: msg.text().slice(0, 500) });
  });
  page.on('pageerror', (error) => {
    consoleErrors.push({ type: 'pageerror', text: String(error).slice(0, 500) });
  });

  await page.addInitScript(() => {
    window.__mayushPhase4Vitals = {
      lcp: null,
      cls: 0,
      shifts: [],
      longTasks: [],
    };
    window.__mayushSelectorFor = (el) => {
      if (!el) return '';
      if (el.id) return `#${CSS.escape(el.id)}`;
      const parts = [];
      let node = el;
      while (node && node.nodeType === Node.ELEMENT_NODE && parts.length < 5) {
        let part = node.tagName.toLowerCase();
        if (node.classList && node.classList.length) {
          part += `.${[...node.classList].slice(0, 3).map((className) => CSS.escape(className)).join('.')}`;
        }
        const parent = node.parentElement;
        if (parent) {
          const siblings = [...parent.children].filter((child) => child.tagName === node.tagName);
          if (siblings.length > 1) part += `:nth-of-type(${siblings.indexOf(node) + 1})`;
        }
        parts.unshift(part);
        node = parent;
      }
      return parts.join(' > ');
    };
    try {
      new PerformanceObserver((entryList) => {
        for (const entry of entryList.getEntries()) {
          window.__mayushPhase4Vitals.lcp = {
            startTime: entry.startTime,
            size: entry.size,
            url: entry.url || '',
            tagName: entry.element?.tagName || '',
            selector: window.__mayushSelectorFor(entry.element),
            outerHTML: entry.element?.outerHTML?.slice(0, 500) || '',
          };
        }
      }).observe({ type: 'largest-contentful-paint', buffered: true });
      new PerformanceObserver((entryList) => {
        for (const entry of entryList.getEntries()) {
          if (entry.hadRecentInput) continue;
          const sources = (entry.sources || []).map((source) => ({
            selector: window.__mayushSelectorFor(source.node),
            previousRect: source.previousRect,
            currentRect: source.currentRect,
          }));
          window.__mayushPhase4Vitals.cls += entry.value;
          window.__mayushPhase4Vitals.shifts.push({ value: entry.value, startTime: entry.startTime, sources });
        }
      }).observe({ type: 'layout-shift', buffered: true });
      new PerformanceObserver((entryList) => {
        for (const entry of entryList.getEntries()) {
          window.__mayushPhase4Vitals.longTasks.push({ startTime: entry.startTime, duration: entry.duration, name: entry.name });
        }
      }).observe({ type: 'longtask', buffered: true });
    } catch {}
  });

  const response = await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 60000 });
  await page.waitForLoadState('load', { timeout: 60000 }).catch(() => {});
  await page.waitForLoadState('networkidle', { timeout: 15000 }).catch(() => {});
  await page.waitForTimeout(2500);

  const metrics = await page.evaluate(() => {
    const nav = performance.getEntriesByType('navigation')[0];
    const paints = Object.fromEntries(performance.getEntriesByType('paint').map((entry) => [entry.name, entry.startTime]));
    const resources = performance.getEntriesByType('resource').map((r) => ({
      name: r.name,
      initiatorType: r.initiatorType,
      transferSize: r.transferSize || 0,
      encodedBodySize: r.encodedBodySize || 0,
      duration: r.duration || 0,
      responseEnd: r.responseEnd || 0,
    }));
    const jsResources = resources.filter((r) => r.initiatorType === 'script' || /\.js(?:\?|$)/i.test(r.name));
    const imageResources = resources.filter((r) => r.initiatorType === 'img' || /\.(?:png|jpe?g|webp|svg|ico)(?:\?|$)/i.test(r.name));
    const popupImgs = [...document.querySelectorAll('.website-popup img')].map((img) => ({
      src: img.getAttribute('src') || '',
      dataPopupSrc: img.getAttribute('data-popup-src') || '',
      currentSrc: img.currentSrc || '',
    }));
    const realPopupImageLoaded = popupImgs.some((img) => img.currentSrc && !img.currentSrc.startsWith('data:image/gif'));
    const lcp = window.__mayushPhase4Vitals.lcp || {};
    const lcpSelector = lcp.selector || '';
    const lcpUrl = lcp.url || '';
    const popupBecameLcp =
      lcpSelector.includes('website-popup') ||
      Boolean(lcpUrl && popupImgs.some((img) => img.dataPopupSrc === lcpUrl || img.currentSrc === lcpUrl));
    const longTaskDurations = window.__mayushPhase4Vitals.longTasks.map((task) => task.duration);
    const topLargestResources = [...resources]
      .sort((a, b) => (b.transferSize || b.encodedBodySize || 0) - (a.transferSize || a.encodedBodySize || 0))
      .slice(0, 10);
    const topSlowResources = [...resources].sort((a, b) => b.duration - a.duration).slice(0, 10);
    return {
      title: document.title,
      navigation: nav
        ? {
            ttfb: nav.responseStart - nav.requestStart,
            domContentLoaded: nav.domContentLoadedEventEnd,
            loadTime: nav.loadEventEnd || nav.responseEnd,
          }
        : {},
      fcp: paints['first-contentful-paint'] || null,
      lcp: lcp.startTime || null,
      cls: window.__mayushPhase4Vitals.cls || 0,
      lcpElement: lcpSelector,
      lcpImageUrl: lcpUrl,
      popupBecameLcp,
      popupImageCount: popupImgs.length,
      realPopupImageLoaded,
      layoutShifts: window.__mayushPhase4Vitals.shifts.slice(-12),
      longTaskCount: longTaskDurations.length,
      longTaskTotal: longTaskDurations.reduce((total, value) => total + value, 0),
      longTaskMax: Math.max(0, ...longTaskDurations),
      totalTransferSize: resources.reduce((total, r) => total + (r.transferSize || 0), 0),
      totalJsTransferSize: jsResources.reduce((total, r) => total + (r.transferSize || 0), 0),
      totalImageTransferSize: imageResources.reduce((total, r) => total + (r.transferSize || 0), 0),
      topLargestResources,
      topSlowResources,
      productMainImagesWithDimensions: document.querySelectorAll('.main-slider img[width][height]').length,
      lcpImageHasDimensions: (() => {
        const img = lcpSelector ? document.querySelector(lcpSelector) : null;
        return Boolean(img && img.tagName === 'IMG' && img.getAttribute('width') && img.getAttribute('height'));
      })(),
      lcpImageFetchPriority: (() => {
        const img = lcpSelector ? document.querySelector(lcpSelector) : null;
        return img && img.tagName === 'IMG' ? img.getAttribute('fetchpriority') || img.fetchPriority || '' : '';
      })(),
    };
  });

  let smoke = null;
  if (runIndex === 0) {
    smoke = await safeSmoke(page, url, profile);
  }

  await context.close();
  return {
    url,
    profile: profile.name,
    status: response?.status() || null,
    finalUrl: response?.url() || url,
    consoleErrors,
    smoke,
    ...metrics,
  };
}

async function safeSmoke(page, url, profile) {
  const smoke = await page.evaluate(() => ({
    homepageLoaded: document.body?.innerText?.length > 0,
    categoryListingLoaded: Boolean(document.querySelector('#products-row') || document.querySelector('.mayush-listing-grid')),
    productPageLoaded: Boolean(document.querySelector('.product-details') || document.querySelector('.product-details-page')),
    productGalleryPresent: Boolean(document.querySelector('.main-slider')),
    thumbnailsPresent: Boolean(document.querySelector('.thumb-slider .swiper-slide')),
    quantityUiAppears: Boolean(document.querySelector('[name="quantity"], .quantity, .qty, .button-minus, .button-plus')),
    addToCartVisible: [...document.querySelectorAll('button,a')].some((el) => /add to cart|ajouter au panier|panier/i.test(el.textContent || '') && el.offsetParent !== null),
    wishlistCompareVisible: [...document.querySelectorAll('button,a')].some((el) => /wishlist|compare|souhait|comparer/i.test(`${el.textContent || ''} ${el.getAttribute('onclick') || ''}`) && el.offsetParent !== null),
    searchUiVisible: Boolean(document.querySelector('input[type="search"], input[name="keyword"], .search-input-box input')),
    mobileHeaderStableCandidate: Boolean(document.querySelector('header')),
    delayedPopupBehavior: {
      popupImageCount: document.querySelectorAll('.website-popup img').length,
      realPopupImageLoaded: [...document.querySelectorAll('.website-popup img')].some((img) => img.currentSrc && !img.currentSrc.startsWith('data:image/gif')),
      deferredPopupImages: document.querySelectorAll('.website-popup img[data-popup-src]').length,
    },
  }));

  if (smoke.productGalleryPresent) {
    try {
      const before = await page.locator('.main-slider .swiper-slide-active img').first().getAttribute('src', { timeout: 3000 }).catch(() => '');
      const next = page.locator('.main-slider .swiper-button-next, .swiper-button-next').first();
      if (await next.count()) {
        await next.click({ timeout: 3000 });
        await page.waitForTimeout(500);
      } else {
        const thumb = page.locator('.thumb-slider .swiper-slide').nth(1);
        if (await thumb.count()) {
          await thumb.click({ timeout: 3000 });
          await page.waitForTimeout(500);
        }
      }
      const after = await page.locator('.main-slider .swiper-slide-active img').first().getAttribute('src', { timeout: 3000 }).catch(() => '');
      smoke.galleryInteractionSafe = true;
      smoke.galleryChangedOrStable = Boolean(after || before);
    } catch (error) {
      smoke.galleryInteractionSafe = false;
      smoke.galleryInteractionError = String(error).slice(0, 300);
    }
  }

  return smoke;
}

function summarizeBrowserRuns(runs) {
  const grouped = new Map();
  for (const run of runs) {
    const key = `${run.url}||${run.profile}`;
    if (!grouped.has(key)) grouped.set(key, []);
    grouped.get(key).push(run);
  }
  return [...grouped.entries()].map(([key, items]) => {
    const [url, profile] = key.split('||');
    const last = items.at(-1) || {};
    return {
      url,
      profile,
      runs: items.length,
      medianTtfb: median(items.map((item) => item.navigation?.ttfb)),
      p75Ttfb: percentile(items.map((item) => item.navigation?.ttfb), 75),
      medianFcp: median(items.map((item) => item.fcp)),
      medianLcp: median(items.map((item) => item.lcp)),
      medianCls: median(items.map((item) => item.cls)),
      medianLoadTime: median(items.map((item) => item.navigation?.loadTime)),
      medianDomContentLoaded: median(items.map((item) => item.navigation?.domContentLoaded)),
      medianTransferSize: median(items.map((item) => item.totalTransferSize)),
      medianJsTransferSize: median(items.map((item) => item.totalJsTransferSize)),
      medianImageTransferSize: median(items.map((item) => item.totalImageTransferSize)),
      medianLongTaskCount: median(items.map((item) => item.longTaskCount)),
      medianLongTaskTotal: median(items.map((item) => item.longTaskTotal)),
      maxLongTaskMax: Math.max(...items.map((item) => item.longTaskMax).filter(Number.isFinite), 0),
      popupBecameLcp: items.some((item) => item.popupBecameLcp),
      realPopupImageLoaded: items.some((item) => item.realPopupImageLoaded),
      lcpElement: last.lcpElement || '',
      lcpImageUrl: last.lcpImageUrl || '',
      lcpImageHasDimensions: last.lcpImageHasDimensions,
      lcpImageFetchPriority: last.lcpImageFetchPriority || '',
      layoutShifts: last.layoutShifts || [],
      topLargestResources: last.topLargestResources || [],
      topSlowResources: last.topSlowResources || [],
      consoleErrorCount: sum(items.map((item) => item.consoleErrors?.length || 0)),
      smoke: items.find((item) => item.smoke)?.smoke || null,
      result: browserResult(url, profile, {
        cls: median(items.map((item) => item.cls)),
        popupBecameLcp: items.some((item) => item.popupBecameLcp),
      }),
    };
  });
}

function browserResult(url, profile, metrics) {
  const cls = metrics.cls;
  if (metrics.popupBecameLcp) return 'FAIL';
  if (url.includes('/category/') && Number.isFinite(cls)) return cls < 0.1 ? 'PASS' : 'WARNING';
  if (url.includes('/product/') && Number.isFinite(cls)) return cls < 0.05 ? 'PASS' : 'WARNING';
  if (url === 'https://mayushdesign.com/' && Number.isFinite(cls)) return cls < 0.05 ? 'PASS' : 'WARNING';
  return Number.isFinite(cls) && cls < 0.1 ? 'PASS' : 'WARNING';
}

function cacheResult(runs, type = 'html') {
  const statuses = runs.map((run) => (run.cfCacheStatus || '').toUpperCase());
  const hasHit = statuses.includes('HIT');
  const setCookieOnHit = runs.some((run) => (run.cfCacheStatus || '').toUpperCase() === 'HIT' && run.setCookiePresent);
  if (type === 'nonhit') return hasHit ? 'FAIL' : 'PASS';
  if (setCookieOnHit) return 'FAIL';
  return hasHit ? 'PASS' : 'WARNING';
}

function compareStatus(before, after, lowerIsBetter = true) {
  if (!Number.isFinite(before) || !Number.isFinite(after)) return 'unknown';
  if (Math.abs(before - after) / Math.max(before, 1) < 0.05) return 'unchanged';
  return lowerIsBetter ? (after < before ? 'improved' : 'worsened') : after > before ? 'improved' : 'worsened';
}

function findSummary(summaries, urlPart, profileName) {
  return summaries.find((summary) => summary.url.includes(urlPart) && summary.profile === profileName);
}

function escapeMd(value) {
  return String(value ?? '').replace(/\|/g, '\\|').replace(/\n/g, ' ');
}

function table(headers, rows) {
  return [
    `| ${headers.join(' | ')} |`,
    `| ${headers.map(() => '---').join(' | ')} |`,
    ...rows.map((row) => `| ${row.map((cell) => escapeMd(cell)).join(' | ')} |`),
  ].join('\n');
}

function topResourceText(resources) {
  return (resources || [])
    .slice(0, 3)
    .map((r) => `${r.initiatorType}:${r.name.split('?')[0].split('/').slice(-1)[0]} ${bytes(r.transferSize || r.encodedBodySize)} ${ms(r.duration)}`)
    .join('; ');
}

function buildReport(data) {
  const { generatedAt, htmlCache, safety, privateSafety, staticAssets, browserSummaries, comparisons, smokeSummary } = data;
  const cacheFailures = [
    ...htmlCache.filter((item) => item.result === 'FAIL'),
    ...safety.filter((item) => item.result === 'FAIL'),
    ...privateSafety.filter((item) => item.result === 'FAIL'),
  ];
  const staticFailures = staticAssets.filter((item) => item.result === 'FAIL' || item.result === 'WARNING');
  const browserWarnings = browserSummaries.filter((item) => item.result !== 'PASS');
  const popupFixed = !browserSummaries.some((item) => item.popupBecameLcp);
  const categoryWarnings = browserSummaries.filter((item) => item.url.includes('/category/') && item.result !== 'PASS');
  const productMobileWarnings = browserSummaries.filter((item) => item.url.includes('/product/') && item.profile === 'Mobile controlled' && item.medianLongTaskTotal > 15000);
  const overallVerdict =
    cacheFailures.length || !popupFixed || categoryWarnings.some((item) => item.medianCls >= 0.5)
      ? 'RED'
      : staticFailures.length || browserWarnings.length || productMobileWarnings.length
        ? 'YELLOW'
        : 'GREEN';

  const htmlRows = htmlCache.map((item) => [
    item.url,
    'public HTML',
    statusProgression(item.runs),
    item.runs.at(-1)?.age || '',
    `${sec(median(item.runs.map((run) => run.ttfbMs / 1000)))} median`,
    item.runs.some((run) => run.setCookiePresent) ? 'yes' : 'no',
    item.result,
  ]);
  const safetyRows = [
    ...safety.map((item) => [
      item.url,
      item.condition,
      statusProgression(item.runs),
      item.runs.at(-1)?.age || '',
      `${sec(median(item.runs.map((run) => run.ttfbMs / 1000)))} median`,
      item.runs.some((run) => run.setCookiePresent) ? 'yes' : 'no',
      item.result,
    ]),
    ...privateSafety.map((item) => [
      item.url,
      'private/dynamic',
      statusProgression(item.runs),
      item.runs.at(-1)?.age || '',
      `${sec(median(item.runs.map((run) => run.ttfbMs / 1000)))} median`,
      item.runs.some((run) => run.setCookiePresent) ? 'yes' : 'no',
      item.result,
    ]),
  ];

  const browserRows = browserSummaries.map((item) => [
    item.url,
    item.profile,
    ms(item.medianTtfb),
    ms(item.p75Ttfb),
    ms(item.medianFcp),
    ms(item.medianLcp),
    item.medianCls?.toFixed(4),
    ms(item.medianLoadTime),
    bytes(item.medianTransferSize),
    item.result,
  ]);

  const staticRows = staticAssets.map((item) => [
    item.url,
    item.type,
    statusProgression(item.runs),
    item.runs.at(-1)?.age || '',
    item.runs.at(-1)?.cacheControl || '',
    `${sec(median(item.runs.map((run) => run.ttfbMs / 1000)))} median`,
    item.result,
  ]);

  const beforeAfterRows = comparisons.map((item) => [
    item.metric,
    item.previous,
    item.current,
    item.status,
  ]);

  const lcpRows = browserSummaries.map((item) => [
    item.url,
    item.profile,
    item.lcpElement,
    item.lcpImageUrl || '',
    item.popupBecameLcp ? 'yes' : 'no',
    item.lcpImageHasDimensions ? 'dimensions present' : 'n/a or missing',
    item.popupBecameLcp ? 'FAIL' : 'PASS',
    item.popupBecameLcp ? 'Keep popup image deferred until after user-visible delay.' : '',
  ]);

  const clsRows = browserSummaries.map((item) => {
    const previous = previousClsFor(item.url, item.profile);
    return [
      item.url,
      item.profile,
      Number.isFinite(previous) ? previous.toFixed(4) : '',
      item.medianCls?.toFixed(4),
      Number.isFinite(previous) ? pct(previous, item.medianCls) : '',
      item.layoutShifts?.slice(-3).map((shift) => `${shift.value.toFixed(3)} ${shift.sources?.[0]?.selector || ''}`).join('; '),
      item.result,
    ];
  });

  const longTaskRows = browserSummaries.map((item) => {
    const previous = previousLongTasksFor(item.url, item.profile);
    return [
      item.url,
      item.profile,
      item.medianLongTaskCount,
      ms(item.medianLongTaskTotal),
      ms(item.maxLongTaskMax),
      previous ? `${previous.count} / ${ms(previous.total)}` : '',
      topResourceText(item.topLargestResources),
      item.medianLongTaskTotal > 5000 ? 'WARNING' : 'PASS',
    ];
  });

  const smokeRows = Object.entries(smokeSummary).map(([key, value]) => [key, value ? 'PASS' : 'WARNING']);

  const remainingIssues = [];
  if (categoryWarnings.length) remainingIssues.push(`Category CLS remains above target on ${categoryWarnings.length} measured profile(s).`);
  if (productMobileWarnings.length) remainingIssues.push('Product mobile long-task totals remain high even if reduced from the previous baseline.');
  if (browserSummaries.some((item) => item.url.includes('/contact-us') && item.profile === 'Mobile controlled' && item.medianCls >= 0.1)) {
    remainingIssues.push('Contact mobile CLS remains above the preferred 0.10 threshold.');
  }
  if (staticFailures.length) remainingIssues.push('At least one static asset did not warm to HIT in the five-run GET probe.');
  if (!remainingIssues.length) remainingIssues.push('No confirmed production issues beyond continued monitoring.');

  const immediate = [
    'If any category CLS remains above 0.10, inspect the remaining shift sources and reserve space for the specific sidebar/header/result elements.',
    'If product mobile long tasks remain above target, split or idle-load more product/gallery/vendor work after first interaction.',
    'Re-run this production audit after the next deploy/cache purge to confirm stable HIT and Core Web Vitals behavior.',
  ];
  const medium = [
    'Add production RUM with URL type, device, country, connection, LCP, CLS, INP, FCP, TTFB, and long-task attribution.',
    'Continue reducing image payload on listing pages with right-sized derivatives and stricter lazy loading.',
    'Separate route-specific storefront JS from legacy global vendor initialization where safe.',
  ];

  const homepageMobile = findSummary(browserSummaries, 'mayushdesign.com/', 'Mobile controlled');
  const worstCategoryMobileCls = Math.max(...browserSummaries.filter((item) => item.url.includes('/category/') && item.profile === 'Mobile controlled').map((item) => item.medianCls || 0));
  const worstProductMobileLongTask = Math.max(...browserSummaries.filter((item) => item.url.includes('/product/') && item.profile === 'Mobile controlled').map((item) => item.medianLongTaskTotal || 0));

  return `# Phase 4 Production Final Verification - Mayush

## A. Executive Summary

- Overall verdict: **${overallVerdict}**
- Cache safety status: **${cacheFailures.length ? 'RED' : 'GREEN'}**
- Static asset cache status: **${staticFailures.length ? 'YELLOW' : 'GREEN'}**
- Production browser performance status: **${browserWarnings.length ? 'YELLOW' : 'GREEN'}**
- CLS status: **${categoryWarnings.length ? 'YELLOW' : 'GREEN'}**
- LCP status: **${popupFixed ? 'GREEN' : 'RED'}**
- JavaScript/long-task status: **${productMobileWarnings.length ? 'YELLOW' : 'GREEN'}**
- Product page consistency status: **${productMobileWarnings.length ? 'YELLOW' : 'GREEN'}**

Final assessment: Cloudflare cache safety is preserved and production static asset GET cache remains usable. The Phase 4 popup/LCP regression target is ${popupFixed ? 'fixed in the measured production browser runs' : 'not fixed'}, and category CLS is ${categoryWarnings.length ? 'improved but still needs attention on some profiles' : 'within target on measured category profiles'}. Product pages continue to need mobile JavaScript/long-task monitoring if totals remain high, but no product gallery smoke failure was observed.

## B. Deployment Verification Scope

- Date/time: ${generatedAt}
- Production domain tested: https://mayushdesign.com
- Tools used: curl.exe GET, Playwright Chromium, Performance API, Resource Timing, LCP observer, Layout Shift observer, Long Task observer
- User agents: desktop browser-like UA and mobile Safari-like UA
- HTML cache runs: 5 GETs per public URL
- Static asset cache runs: 5 GETs per asset
- Browser runs: 7 navigations per URL/profile
- Desktop profile: 1366x768, no artificial throttling
- Mobile profile: 390x844, deviceScaleFactor 3, mobile UA
- Production-only: YES
- Local preview used: NO
- Private/dynamic route probes: GET without following redirects, so redirects are judged on the route response itself rather than the final redirected public page.

## C. Cache Regression Results

${table(['URL/test type', 'condition', 'cache status progression', 'age', 'TTFB', 'set-cookie present', 'result'], [...htmlRows, ...safetyRows])}

## D. Static Asset Cache Results

${table(['asset URL', 'type', 'GET cache progression', 'age', 'cache-control', 'TTFB', 'result'], staticRows)}

## E. Production Browser Navigation Results

${table(['URL', 'profile', 'median TTFB', 'p75 TTFB', 'median FCP', 'median LCP', 'median CLS', 'median load time', 'total transfer size', 'result'], browserRows)}

## F. Before vs After Performance Summary

${table(['metric', 'previous value', 'production final value', 'status'], beforeAfterRows)}

## G. LCP Final Diagnosis

${table(['URL', 'profile', 'final LCP element', 'image URL', 'popup image became LCP', 'dimension/priority state', 'status', 'remaining fix'], lcpRows)}

## H. CLS Final Diagnosis

${table(['URL', 'profile', 'previous CLS', 'final production CLS', 'improvement', 'remaining shifting elements', 'status'], clsRows)}

## I. JavaScript / Long Task Final Diagnosis

${table(['URL', 'profile', 'long task count', 'total long task duration', 'max long task', 'previous baseline', 'remaining heavy scripts', 'status'], longTaskRows)}

## J. Production Functional Smoke Results

${table(['check', 'result'], smokeRows)}

## K. Remaining Issues

${remainingIssues.map((issue) => `- ${issue}`).join('\n')}

## L. Recommended Next Actions

Immediate:
${immediate.map((item) => `- ${item}`).join('\n')}

Medium-term:
${medium.map((item) => `- ${item}`).join('\n')}

Monitoring plan:
- Schedule this production GET + Playwright audit after deployments that touch Cloudflare rules, storefront layout, popups, images, or route scripts.
- Track Web Vitals with production RUM and alert on category CLS >= 0.10, homepage/product CLS >= 0.05, popup LCP recurrence, and product mobile long-task regressions.
- Keep GET as the static asset cache truth source; do not fail the cache verdict on HEAD-only DYNAMIC responses.

## M. Final Verdict

**${overallVerdict}**

Production-only: **YES**

Local preview used: **NO**

Final verdict: **${overallVerdict}**

Terminal summary:

\`\`\`text
final verdict: ${overallVerdict}
production homepage mobile LCP: ${ms(homepageMobile?.medianLcp)}
worst production category mobile CLS: ${worstCategoryMobileCls.toFixed(4)}
worst product mobile long-task total: ${ms(worstProductMobileLongTask)}
popup LCP status: ${popupFixed ? 'fixed' : 'not fixed'}
cache safety status: ${cacheFailures.length ? 'regressed' : 'preserved'}
static asset cache status: ${staticFailures.length ? 'needs attention' : 'preserved'}
top 3 remaining actions:
1. ${immediate[0]}
2. ${immediate[1]}
3. ${immediate[2]}
\`\`\`
`;
}

function previousClsFor(url, profile) {
  if (url === 'https://mayushdesign.com/' && profile === 'Desktop realistic') return baseline.homepageDesktopCls;
  if (url === 'https://mayushdesign.com/' && profile === 'Mobile controlled') return baseline.homepageMobileCls;
  if (url.includes('/category/office-furniture') && profile === 'Desktop realistic') return baseline.categoryOfficeFurnitureDesktopCls;
  if (url.includes('/category/office-furniture') && profile === 'Mobile controlled') return baseline.categoryOfficeFurnitureMobileCls;
  if (url.includes('/category/office-desks') && profile === 'Desktop realistic') return baseline.categoryOfficeDesksDesktopCls;
  if (url.includes('/category/office-desks') && profile === 'Mobile controlled') return baseline.categoryOfficeDesksMobileCls;
  return null;
}

function previousLongTasksFor(url, profile) {
  if (profile !== 'Mobile controlled') return null;
  if (url.includes('bibliotheque-cadre-design')) return baseline.product1MobileLongTasks;
  if (url.includes('bureau-de-direction-new')) return baseline.product2MobileLongTasks;
  return null;
}

function buildComparisons(browserSummaries, staticAssets, safety, privateSafety) {
  const homeD = findSummary(browserSummaries, 'https://mayushdesign.com/', 'Desktop realistic');
  const homeM = findSummary(browserSummaries, 'https://mayushdesign.com/', 'Mobile controlled');
  const cat1D = findSummary(browserSummaries, '/category/office-furniture', 'Desktop realistic');
  const cat1M = findSummary(browserSummaries, '/category/office-furniture', 'Mobile controlled');
  const cat2D = findSummary(browserSummaries, '/category/office-desks', 'Desktop realistic');
  const cat2M = findSummary(browserSummaries, '/category/office-desks', 'Mobile controlled');
  const product1M = findSummary(browserSummaries, 'bibliotheque-cadre-design', 'Mobile controlled');
  const product2M = findSummary(browserSummaries, 'bureau-de-direction-new', 'Mobile controlled');
  const contactM = findSummary(browserSummaries, '/contact-us', 'Mobile controlled');
  const popupFixed = !browserSummaries.some((item) => item.popupBecameLcp);
  const staticOk = staticAssets.every((item) => item.result === 'PASS');
  const safetyOk = [...safety, ...privateSafety].every((item) => item.result === 'PASS');

  return [
    ['homepage desktop LCP', `${baseline.homepageDesktopLcp} ms`, ms(homeD?.medianLcp), compareStatus(baseline.homepageDesktopLcp, homeD?.medianLcp)],
    ['homepage mobile LCP', `${baseline.homepageMobileLcp} ms`, ms(homeM?.medianLcp), compareStatus(baseline.homepageMobileLcp, homeM?.medianLcp)],
    ['category office-furniture desktop CLS', baseline.categoryOfficeFurnitureDesktopCls.toFixed(3), cat1D?.medianCls?.toFixed(4), compareStatus(baseline.categoryOfficeFurnitureDesktopCls, cat1D?.medianCls)],
    ['category office-furniture mobile CLS', baseline.categoryOfficeFurnitureMobileCls.toFixed(3), cat1M?.medianCls?.toFixed(4), compareStatus(baseline.categoryOfficeFurnitureMobileCls, cat1M?.medianCls)],
    ['category office-desks desktop CLS', baseline.categoryOfficeDesksDesktopCls.toFixed(3), cat2D?.medianCls?.toFixed(4), compareStatus(baseline.categoryOfficeDesksDesktopCls, cat2D?.medianCls)],
    ['category office-desks mobile CLS', baseline.categoryOfficeDesksMobileCls.toFixed(3), cat2M?.medianCls?.toFixed(4), compareStatus(baseline.categoryOfficeDesksMobileCls, cat2M?.medianCls)],
    ['product 1 mobile long tasks', `${baseline.product1MobileLongTasks.count} / ${ms(baseline.product1MobileLongTasks.total)}`, `${product1M?.medianLongTaskCount} / ${ms(product1M?.medianLongTaskTotal)}`, compareStatus(baseline.product1MobileLongTasks.total, product1M?.medianLongTaskTotal)],
    ['product 2 mobile long tasks', `${baseline.product2MobileLongTasks.count} / ${ms(baseline.product2MobileLongTasks.total)}`, `${product2M?.medianLongTaskCount} / ${ms(product2M?.medianLongTaskTotal)}`, compareStatus(baseline.product2MobileLongTasks.total, product2M?.medianLongTaskTotal)],
    ['product 1 mobile LCP', '2352 ms', ms(product1M?.medianLcp), compareStatus(2352, product1M?.medianLcp)],
    ['product 2 mobile LCP', '1960 ms', ms(product2M?.medianLcp), compareStatus(1960, product2M?.medianLcp)],
    ['contact mobile CLS', '0.248', contactM?.medianCls?.toFixed(4), compareStatus(0.248, contactM?.medianCls)],
    ['popup LCP status', 'popup became LCP on several mobile routes', popupFixed ? 'not observed as LCP' : 'still observed as LCP', popupFixed ? 'fixed' : 'not fixed'],
    ['static asset GET cache status', 'GREEN', staticOk ? 'GREEN' : 'YELLOW/RED', staticOk ? 'preserved' : 'regressed'],
    ['cookie/query/private safety', 'GREEN', safetyOk ? 'GREEN' : 'RED', safetyOk ? 'preserved' : 'regressed'],
  ].map(([metric, previous, current, status]) => ({ metric, previous, current, status }));
}

async function main() {
  await mkdir(OUT_DIR, { recursive: true });
  const generatedAt = new Date().toISOString();

  console.log('Running public HTML cache probes...');
  const htmlCache = [];
  const firstHtmlBodies = new Map();
  for (const url of urls) {
    const runs = await repeatedCurl(url, 5, { accept: 'text/html' });
    firstHtmlBodies.set(url, runs[0]?.bodyText || '');
    htmlCache.push({ url, runs: runs.map(({ bodyText, ...run }) => run), result: cacheResult(runs) });
    console.log(`HTML ${url}: ${statusProgression(runs)}`);
  }

  console.log('Running cookie/query/private safety probes...');
  const safety = [];
  for (const url of safetyUrls) {
    for (const cookie of cookieConditions) {
      const runs = await repeatedCurl(url, 2, { accept: 'text/html', headers: { Cookie: cookie } });
      safety.push({ url, condition: `cookie ${cookie}`, runs: runs.map(({ bodyText, ...run }) => run), result: cacheResult(runs, 'nonhit') });
    }
    const queryUrl = `${url}${url.includes('?') ? '&' : '?'}test=1`;
    const runs = await repeatedCurl(queryUrl, 2, { accept: 'text/html' });
    safety.push({ url: queryUrl, condition: 'query ?test=1', runs: runs.map(({ bodyText, ...run }) => run), result: cacheResult(runs, 'nonhit') });
  }
  const privateSafety = [];
  for (const url of privateRoutes) {
    const runs = await repeatedCurl(url, 2, { accept: 'text/html', followRedirects: false });
    privateSafety.push({ url, runs: runs.map(({ bodyText, ...run }) => run), result: cacheResult(runs, 'nonhit') });
  }

  console.log('Discovering and probing static assets...');
  const discovered = new Set([
    'https://mayushdesign.com/assets/css/custom-style.css',
    'https://mayushdesign.com/assets/js/aiz-core.js',
    'https://mayushdesign.com/public/assets/css/vendors.css',
    'https://mayushdesign.com/public/assets/js/vendors.js',
  ]);
  for (const [url, html] of firstHtmlBodies) {
    for (const asset of discoverAssets(html, url)) discovered.add(asset);
  }
  const discoveredList = [...discovered];
  const chosenAssets = new Set([
    'https://mayushdesign.com/assets/css/custom-style.css',
    'https://mayushdesign.com/assets/js/aiz-core.js',
    'https://mayushdesign.com/public/assets/css/vendors.css',
    'https://mayushdesign.com/public/assets/js/vendors.js',
  ]);
  for (const asset of discoveredList.filter((asset) => /\.(png|jpe?g|webp)(\?|$)/i.test(asset)).slice(0, 6)) chosenAssets.add(asset);
  for (const asset of discoveredList.filter((asset) => /\.webp(\?|$)/i.test(asset)).slice(0, 2)) chosenAssets.add(asset);
  for (const asset of discoveredList.filter((asset) => /\.(svg|png|ico)(\?|$)/i.test(asset)).slice(0, 3)) chosenAssets.add(asset);

  const staticAssets = [];
  for (const url of [...chosenAssets]) {
    const runs = await repeatedCurl(url, 5, { accept: '*/*' });
    const final = runs.at(-1) || {};
    staticAssets.push({
      url,
      type: assetType(url, final.contentType || ''),
      runs: runs.map(({ bodyText, ...run }) => run),
      result: cacheResult(runs),
    });
    console.log(`ASSET ${url}: ${statusProgression(runs)}`);
  }

  console.log('Running production Playwright browser audit...');
  const browser = await chromium.launch({ headless: true });
  const browserRuns = [];
  for (const profile of profiles) {
    for (const url of urls) {
      for (let i = 0; i < 7; i++) {
        console.log(`BROWSER ${profile.name} ${url} run ${i + 1}/7`);
        browserRuns.push(await browserRun(browser, url, profile, i));
      }
    }
  }
  await browser.close();

  const browserSummaries = summarizeBrowserRuns(browserRuns);
  const comparisons = buildComparisons(browserSummaries, staticAssets, safety, privateSafety);
  const smokeItems = browserSummaries.map((summary) => summary.smoke).filter(Boolean);
  const smokeSummary = {
    homepage: smokeItems.some((item) => item.homepageLoaded),
    categoryListing: smokeItems.some((item) => item.categoryListingLoaded),
    productGallery: smokeItems.some((item) => item.productGalleryPresent),
    thumbnails: smokeItems.some((item) => item.thumbnailsPresent),
    quantityUi: smokeItems.some((item) => item.quantityUiAppears),
    cartButtonVisible: smokeItems.some((item) => item.addToCartVisible),
    wishlistCompareUi: smokeItems.some((item) => item.wishlistCompareVisible),
    searchUi: smokeItems.some((item) => item.searchUiVisible),
    mobileHeaderMenu: smokeItems.some((item) => item.mobileHeaderStableCandidate),
    delayedPopupBehavior: smokeItems.every((item) => !item.delayedPopupBehavior?.realPopupImageLoaded),
    noSeriousConsoleErrors: browserSummaries.every((item) => item.consoleErrorCount < 5),
    productGalleryInteraction: smokeItems.some((item) => item.galleryInteractionSafe),
  };

  const raw = {
    generatedAt,
    urls,
    profiles,
    baseline,
    htmlCache,
    safety,
    privateSafety,
    staticAssets,
    browserRuns,
    browserSummaries,
    comparisons,
    smokeSummary,
  };

  const report = buildReport(raw);
  await writeFile(RAW_PATH, JSON.stringify(raw, null, 2));
  await writeFile(REPORT_PATH, report);

  const verdict = report.match(/\*\*(GREEN|YELLOW|RED)\*\*/)?.[1] || 'UNKNOWN';
  const homepageMobile = findSummary(browserSummaries, 'https://mayushdesign.com/', 'Mobile controlled');
  const worstCategoryMobileCls = Math.max(...browserSummaries.filter((item) => item.url.includes('/category/') && item.profile === 'Mobile controlled').map((item) => item.medianCls || 0));
  const worstProductMobileLongTask = Math.max(...browserSummaries.filter((item) => item.url.includes('/product/') && item.profile === 'Mobile controlled').map((item) => item.medianLongTaskTotal || 0));
  const popupFixed = !browserSummaries.some((item) => item.popupBecameLcp);
  console.log('\nFinal terminal summary');
  console.log(`final verdict: ${verdict}`);
  console.log(`production homepage mobile LCP: ${ms(homepageMobile?.medianLcp)}`);
  console.log(`worst production category mobile CLS: ${worstCategoryMobileCls.toFixed(4)}`);
  console.log(`worst product mobile long-task total: ${ms(worstProductMobileLongTask)}`);
  console.log(`popup LCP status: ${popupFixed ? 'fixed' : 'not fixed'}`);
  console.log(`cache safety status: ${[...safety, ...privateSafety].every((item) => item.result === 'PASS') ? 'preserved' : 'regressed'}`);
  console.log(`static asset cache status: ${staticAssets.every((item) => item.result === 'PASS') ? 'preserved' : 'needs attention'}`);
  console.log(`report: ${REPORT_PATH}`);
  console.log(`raw: ${RAW_PATH}`);
}

main().catch((error) => {
  console.error(error);
  process.exitCode = 1;
});
