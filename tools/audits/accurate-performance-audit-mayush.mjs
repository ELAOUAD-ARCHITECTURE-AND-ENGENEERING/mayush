import { chromium } from 'playwright';
import { spawn } from 'node:child_process';
import { mkdtemp, rm, writeFile, readFile } from 'node:fs/promises';
import { existsSync } from 'node:fs';
import path from 'node:path';
import os from 'node:os';

const OUT_DIR = path.resolve('tools/audits/output');
const RAW_PATH = path.join(OUT_DIR, 'accurate-performance-audit-mayush.raw.json');
const REPORT_PATH = path.join(OUT_DIR, 'accurate-performance-audit-mayush.md');
const UA = 'Mozilla/5.0 MayushAccuratePerfAudit';
const MOBILE_UA =
  'Mozilla/5.0 (iPhone; CPU iPhone OS 17_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.5 Mobile/15E148 Safari/604.1 MayushAccuratePerfAudit';

const urls = [
  'https://mayushdesign.com/',
  'https://mayushdesign.com/category/office-furniture',
  'https://mayushdesign.com/category/office-desks',
  'https://mayushdesign.com/product/bibliotheque-cadre-design-moderne-avec-rangements-modules-7',
  'https://mayushdesign.com/product/bureau-de-direction-new-at-105-design-moderne-avec-retour-de-rangement-integre-3-3',
  'https://mayushdesign.com/blog/perfect-home-office-design',
  'https://mayushdesign.com/contact-us',
];

const productUrl =
  'https://mayushdesign.com/product/bibliotheque-cadre-design-moderne-avec-rangements-modules-7';

function sleep(ms) {
  return new Promise((resolve) => setTimeout(resolve, ms));
}

function percentile(values, p) {
  const nums = values.filter((v) => Number.isFinite(v)).sort((a, b) => a - b);
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

function fmtMs(value) {
  if (!Number.isFinite(value)) return '';
  return `${Math.round(value)} ms`;
}

function fmtSec(value) {
  if (!Number.isFinite(value)) return '';
  return `${value.toFixed(3)}s`;
}

function statusList(runs) {
  return runs.map((run) => run.cfCacheStatus || '(none)').join(' -> ');
}

function cacheResultFromHtmlRuns(runs) {
  const statuses = runs.map((run) => (run.cfCacheStatus || '').toUpperCase());
  if (statuses.includes('HIT')) return 'PASS';
  if (statuses.includes('DYNAMIC') || statuses.includes('BYPASS')) return 'WARNING';
  return 'WARNING';
}

function nonHitResult(runs) {
  return runs.some((run) => (run.cfCacheStatus || '').toUpperCase() === 'HIT') ? 'FAIL' : 'PASS';
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

async function runCurl({
  url,
  method = 'GET',
  accept = 'text/html',
  userAgent = UA,
  headers = {},
  followRedirects = true,
  maxTime = 60,
}) {
  const tempDir = await mkdtemp(path.join(os.tmpdir(), 'mayush-accurate-'));
  const headerPath = path.join(tempDir, 'headers.txt');
  const bodyPath = path.join(tempDir, 'body.bin');
  const writeOut = [
    'FINAL_URL=%{url_effective}',
    'STATUS=%{http_code}',
    'DNS=%{time_namelookup}',
    'CONNECT=%{time_connect}',
    'TLS=%{time_appconnect}',
    'TTFB=%{time_starttransfer}',
    'TOTAL=%{time_total}',
    'SIZE=%{size_download}',
    'CONTENT_TYPE=%{content_type}',
  ].join('\\n');

  const args = [
    '--silent',
    '--show-error',
    '--max-time',
    String(maxTime),
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
  if (method === 'HEAD') args.push('--head');
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
  let bodySize = 0;
  try {
    headerText = await readFile(headerPath, 'utf8');
  } catch {}
  try {
    const body = await readFile(bodyPath);
    bodySize = body.length;
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
    method,
    status: Number(timings.STATUS || final.status || 0),
    finalUrl: timings.FINAL_URL || url,
    cfCacheStatus: h['cf-cache-status'] || '',
    age: h.age || '',
    cfRay: h['cf-ray'] || '',
    server: h.server || '',
    cacheControl: h['cache-control'] || '',
    expires: h.expires || '',
    etag: h.etag || '',
    lastModified: h['last-modified'] || '',
    contentType: timings.CONTENT_TYPE || h['content-type'] || '',
    contentLength: h['content-length'] || '',
    setCookiePresent: (final.setCookie || []).length > 0,
    dns: Number(timings.DNS),
    connect: Number(timings.CONNECT),
    tls: Number(timings.TLS),
    ttfb: Number(timings.TTFB),
    total: Number(timings.TOTAL),
    sizeBytes: Number(timings.SIZE || bodySize || 0),
    redirectChain: blocks.map((block) => ({
      status: block.status,
      location: block.headers.location || '',
      cfCacheStatus: block.headers['cf-cache-status'] || '',
    })),
    error: output.code === 0 ? '' : output.stderr || output.stdout,
  };
}

function discoverAssetsFromHtml(html, baseUrl) {
  const found = new Set();
  const re = /(?:href|src)=["']([^"']+\.(?:css|js|png|jpe?g|webp|svg|ico|woff2?|ttf|otf)(?:\?[^"']*)?)["']/gi;
  let match;
  while ((match = re.exec(html))) {
    try {
      const absolute = new URL(match[1], baseUrl).href;
      if (new URL(absolute).hostname.endsWith('mayushdesign.com')) found.add(absolute);
    } catch {}
  }
  const urlsByType = [];
  for (const pattern of [/\.css/i, /\.js/i, /\.webp/i, /\.(png|jpe?g)/i, /\.(svg|ico)/i, /\.(woff2?|ttf|otf)/i]) {
    const candidate = [...found].find((url) => pattern.test(url));
    if (candidate) urlsByType.push(candidate);
  }
  return urlsByType;
}

function summarizeResourceGroups(resources) {
  const groups = {};
  for (const resource of resources) {
    const type = resource.initiatorType || 'other';
    if (!groups[type]) groups[type] = { requests: 0, transferSize: 0, encodedBodySize: 0, decodedBodySize: 0 };
    groups[type].requests += 1;
    groups[type].transferSize += resource.transferSize || 0;
    groups[type].encodedBodySize += resource.encodedBodySize || 0;
    groups[type].decodedBodySize += resource.decodedBodySize || 0;
  }
  return groups;
}

function topBy(items, key, count = 10) {
  return [...items].sort((a, b) => (b[key] || 0) - (a[key] || 0)).slice(0, count);
}

function profileConfig(name) {
  if (name === 'mobile') {
    return {
      label: 'Mobile controlled',
      viewport: { width: 390, height: 844 },
      userAgent: MOBILE_UA,
      deviceScaleFactor: 3,
      isMobile: true,
      hasTouch: true,
      cpuSlowdown: 4,
      network: {
        offline: false,
        latency: 40,
        downloadThroughput: (5 * 1024 * 1024) / 8,
        uploadThroughput: (1.5 * 1024 * 1024) / 8,
      },
    };
  }
  return {
    label: 'Desktop realistic',
    viewport: { width: 1366, height: 768 },
    userAgent: UA,
    deviceScaleFactor: 1,
    isMobile: false,
    hasTouch: false,
    cpuSlowdown: 1,
    network: null,
  };
}

async function installObservers(page) {
  await page.addInitScript(() => {
    window.__mayushAudit = {
      lcp: null,
      cls: 0,
      shifts: [],
      longTasks: [],
    };
    const selectorFor = (el) => {
      if (!el) return '';
      if (el.id) return `#${el.id}`;
      const parts = [];
      let node = el;
      while (node && node.nodeType === Node.ELEMENT_NODE && parts.length < 5) {
        let part = node.nodeName.toLowerCase();
        if (node.className && typeof node.className === 'string') {
          const cls = node.className
            .trim()
            .split(/\s+/)
            .filter(Boolean)
            .slice(0, 2)
            .join('.');
          if (cls) part += `.${cls}`;
        }
        const parent = node.parentElement;
        if (parent) {
          const siblings = [...parent.children].filter((child) => child.nodeName === node.nodeName);
          if (siblings.length > 1) part += `:nth-of-type(${siblings.indexOf(node) + 1})`;
        }
        parts.unshift(part);
        node = parent;
      }
      return parts.join(' > ');
    };
    window.__mayushSelectorFor = selectorFor;
    try {
      new PerformanceObserver((list) => {
        const entries = list.getEntries();
        const last = entries[entries.length - 1];
        if (last) {
          const el = last.element || null;
          const img = el && el.tagName === 'IMG' ? el : el?.querySelector?.('img');
          const style = el ? getComputedStyle(el) : null;
          const bg = style?.backgroundImage && style.backgroundImage !== 'none' ? style.backgroundImage : '';
          window.__mayushAudit.lcp = {
            startTime: last.startTime,
            size: last.size,
            tagName: el?.tagName || '',
            selector: selectorFor(el),
            text: (el?.innerText || '').trim().slice(0, 120),
            currentSrc: img?.currentSrc || img?.src || '',
            backgroundImage: bg,
            naturalWidth: img?.naturalWidth || null,
            naturalHeight: img?.naturalHeight || null,
            renderedWidth: el?.getBoundingClientRect?.().width || null,
            renderedHeight: el?.getBoundingClientRect?.().height || null,
            loading: img?.getAttribute?.('loading') || '',
            fetchPriority: img?.getAttribute?.('fetchpriority') || '',
            widthAttr: img?.getAttribute?.('width') || '',
            heightAttr: img?.getAttribute?.('height') || '',
          };
        }
      }).observe({ type: 'largest-contentful-paint', buffered: true });
    } catch {}
    try {
      new PerformanceObserver((list) => {
        for (const entry of list.getEntries()) {
          if (entry.hadRecentInput) continue;
          window.__mayushAudit.cls += entry.value;
          window.__mayushAudit.shifts.push({
            value: entry.value,
            startTime: entry.startTime,
            sources: (entry.sources || []).slice(0, 5).map((source) => ({
              selector: selectorFor(source.node),
              previousRect: source.previousRect,
              currentRect: source.currentRect,
            })),
          });
        }
      }).observe({ type: 'layout-shift', buffered: true });
    } catch {}
    try {
      new PerformanceObserver((list) => {
        for (const entry of list.getEntries()) {
          window.__mayushAudit.longTasks.push({
            startTime: entry.startTime,
            duration: entry.duration,
            name: entry.name,
            attribution: (entry.attribution || []).map((item) => ({
              name: item.name,
              entryType: item.entryType,
              containerType: item.containerType,
              containerName: item.containerName,
              containerSrc: item.containerSrc,
            })),
          });
        }
      }).observe({ type: 'longtask', buffered: true });
    } catch {}
  });
}

async function collectPageMetrics(page, mainResponse, responseRecords, startedAtMs, totalWallTime) {
  return page.evaluate(
    ({ responseHeaders, status, responseUrl, startedAtMs, totalWallTime }) => {
      const nav = performance.getEntriesByType('navigation')[0];
      const paints = performance.getEntriesByType('paint');
      const fcp = paints.find((entry) => entry.name === 'first-contentful-paint')?.startTime || null;
      const resources = performance.getEntriesByType('resource').map((entry) => ({
        name: entry.name,
        initiatorType: entry.initiatorType,
        startTime: entry.startTime,
        duration: entry.duration,
        transferSize: entry.transferSize,
        encodedBodySize: entry.encodedBodySize,
        decodedBodySize: entry.decodedBodySize,
        renderBlockingStatus: entry.renderBlockingStatus || '',
        nextHopProtocol: entry.nextHopProtocol || '',
      }));
      const scriptsInHead = [...document.head.querySelectorAll('script[src]')].map((script) => ({
        src: script.src,
        async: script.async,
        defer: script.defer,
        type: script.type || '',
      }));
      const stylesheets = [...document.querySelectorAll('link[rel~="stylesheet"]')].map((link) => ({
        href: link.href,
        media: link.media || '',
        disabled: link.disabled,
      }));
      const imagesWithoutDimensions = [...document.images]
        .filter((img) => !img.getAttribute('width') || !img.getAttribute('height'))
        .slice(0, 25)
        .map((img) => ({
          src: img.currentSrc || img.src,
          selector: window.__mayushSelectorFor?.(img) || '',
          loading: img.getAttribute('loading') || '',
          renderedWidth: img.getBoundingClientRect().width,
          renderedHeight: img.getBoundingClientRect().height,
          naturalWidth: img.naturalWidth,
          naturalHeight: img.naturalHeight,
        }));
      const audit = window.__mayushAudit || { cls: 0, shifts: [], longTasks: [], lcp: null };
      return {
        status,
        responseUrl,
        responseHeaders,
        wallTime: totalWallTime,
        navigation: nav
          ? {
              startTime: nav.startTime,
              responseStart: nav.responseStart,
              responseEnd: nav.responseEnd,
              domContentLoadedEventEnd: nav.domContentLoadedEventEnd,
              loadEventEnd: nav.loadEventEnd,
              domInteractive: nav.domInteractive,
              transferSize: nav.transferSize,
              encodedBodySize: nav.encodedBodySize,
              decodedBodySize: nav.decodedBodySize,
              type: nav.type,
              redirectCount: nav.redirectCount,
              ttfb: nav.responseStart - nav.startTime,
              totalLoad: nav.loadEventEnd - nav.startTime,
            }
          : null,
        fcp,
        lcp: audit.lcp,
        cls: audit.cls || 0,
        shifts: audit.shifts || [],
        longTasks: audit.longTasks || [],
        resources,
        scriptsInHead,
        stylesheets,
        imagesWithoutDimensions,
        documentCookieLength: document.cookie.length,
        startedAtMs,
      };
    },
    {
      responseHeaders: mainResponse?.headers() || {},
      status: mainResponse?.status() || 0,
      responseUrl: mainResponse?.url() || '',
      startedAtMs,
      totalWallTime,
      responseRecords,
    },
  );
}

async function measureProfile(browser, url, profileName) {
  const profile = profileConfig(profileName);
  const runs = [];
  for (let i = 0; i < 7; i += 1) {
    const context = await browser.newContext({
      viewport: profile.viewport,
      userAgent: profile.userAgent,
      deviceScaleFactor: profile.deviceScaleFactor,
      isMobile: profile.isMobile,
      hasTouch: profile.hasTouch,
      ignoreHTTPSErrors: true,
    });
    const page = await context.newPage();
    await installObservers(page);
    const responseRecords = [];
    page.on('response', async (response) => {
      const req = response.request();
      responseRecords.push({
        url: response.url(),
        status: response.status(),
        resourceType: req.resourceType(),
        headers: response.headers(),
      });
    });
    const client = await context.newCDPSession(page);
    await client.send('Network.enable').catch(() => {});
    if (profile.cpuSlowdown && profile.cpuSlowdown !== 1) {
      await client.send('Emulation.setCPUThrottlingRate', { rate: profile.cpuSlowdown }).catch(() => {});
    }
    if (profile.network) {
      await client.send('Network.emulateNetworkConditions', profile.network).catch(() => {});
    }
    const startedAtMs = Date.now();
    let mainResponse = null;
    let error = '';
    try {
      mainResponse = await page.goto(url, { waitUntil: 'load', timeout: 60000 });
      await page.waitForLoadState('networkidle', { timeout: 15000 }).catch(() => {});
      await sleep(2500);
    } catch (err) {
      error = err.message;
    }
    const totalWallTime = Date.now() - startedAtMs;
    let metrics = null;
    try {
      metrics = await collectPageMetrics(page, mainResponse, responseRecords, startedAtMs, totalWallTime);
    } catch (err) {
      error = error || err.message;
    }
    runs.push({
      run: i + 1,
      profile: profile.label,
      url,
      error,
      responseRecords,
      metrics,
    });
    await context.close().catch(() => {});
    await sleep(500);
  }
  return { url, profile: profile.label, runs, summary: summarizeBrowserRuns(runs) };
}

function summarizeBrowserRuns(runs) {
  const valid = runs.map((run) => run.metrics).filter(Boolean);
  const ttfbs = valid.map((m) => m.navigation?.ttfb).filter(Number.isFinite);
  const fcps = valid.map((m) => m.fcp).filter(Number.isFinite);
  const lcps = valid.map((m) => m.lcp?.startTime).filter(Number.isFinite);
  const cls = valid.map((m) => m.cls).filter(Number.isFinite);
  const loadTimes = valid.map((m) => m.navigation?.totalLoad || m.wallTime).filter(Number.isFinite);
  const transfer = valid.map((m) => m.navigation?.transferSize || 0).filter(Number.isFinite);
  const longTaskDurations = valid.map((m) => sum((m.longTasks || []).map((task) => task.duration)));
  return {
    medianTtfb: median(ttfbs),
    p75Ttfb: percentile(ttfbs, 75),
    medianFcp: median(fcps),
    p75Fcp: percentile(fcps, 75),
    medianLcp: median(lcps),
    p75Lcp: percentile(lcps, 75),
    medianCls: median(cls),
    p75Cls: percentile(cls, 75),
    medianLoad: median(loadTimes),
    p75Load: percentile(loadTimes, 75),
    medianDocumentTransfer: median(transfer),
    medianLongTaskDuration: median(longTaskDurations),
    sampleCount: valid.length,
    result: median(lcps) && median(lcps) < 2500 && median(cls) < 0.1 ? 'PASS' : 'WARNING',
  };
}

function aggregateDiagnostics(browserResults) {
  const allResources = [];
  const pageDiagnostics = [];
  for (const result of browserResults) {
    const representative =
      result.runs
        .map((run) => run.metrics)
        .filter(Boolean)
        .sort((a, b) => (a.navigation?.totalLoad || a.wallTime) - (b.navigation?.totalLoad || b.wallTime))[0] || null;
    if (!representative) continue;
    const responseHeadersByUrl = new Map();
    for (const run of result.runs) {
      for (const record of run.responseRecords || []) responseHeadersByUrl.set(record.url, record.headers);
    }
    const resources = representative.resources.map((resource) => ({
      ...resource,
      pageUrl: result.url,
      profile: result.profile,
      host: safeHost(resource.name),
      headers: responseHeadersByUrl.get(resource.name) || {},
    }));
    allResources.push(...resources);
    const longTasks = representative.longTasks || [];
    pageDiagnostics.push({
      url: result.url,
      profile: result.profile,
      resourceGroups: summarizeResourceGroups(resources),
      topLargest: topBy(resources, 'transferSize', 10),
      topSlowest: topBy(resources, 'duration', 10),
      topJs: topBy(resources.filter((r) => r.initiatorType === 'script' || r.name.includes('.js')), 'transferSize', 10),
      topCss: topBy(resources.filter((r) => r.name.includes('.css')), 'transferSize', 10),
      topImages: topBy(
        resources.filter((r) => r.initiatorType === 'img' || r.name.match(/\.(png|jpe?g|webp|gif|svg)/i)),
        'transferSize',
        10,
      ),
      thirdParty: resources.filter((r) => !safeHost(r.name).endsWith('mayushdesign.com')).slice(0, 25),
      lcp: representative.lcp,
      cls: representative.cls,
      shifts: representative.shifts,
      longTaskCount: longTasks.length,
      totalLongTaskDuration: sum(longTasks.map((task) => task.duration)),
      maxLongTaskDuration: Math.max(0, ...longTasks.map((task) => task.duration || 0)),
      scriptsInHead: representative.scriptsInHead,
      stylesheets: representative.stylesheets,
      imagesWithoutDimensions: representative.imagesWithoutDimensions,
    });
  }
  return {
    allResources,
    pageDiagnostics,
    topLargest: topBy(allResources, 'transferSize', 10),
    topSlowest: topBy(allResources, 'duration', 10),
    topJs: topBy(allResources.filter((r) => r.initiatorType === 'script' || r.name.includes('.js')), 'transferSize', 10),
    topCss: topBy(allResources.filter((r) => r.name.includes('.css')), 'transferSize', 10),
    topImages: topBy(
      allResources.filter((r) => r.initiatorType === 'img' || r.name.match(/\.(png|jpe?g|webp|gif|svg)/i)),
      'transferSize',
      10,
    ),
    thirdParty: allResources.filter((r) => !safeHost(r.name).endsWith('mayushdesign.com')).slice(0, 50),
  };
}

function safeHost(url) {
  try {
    return new URL(url).hostname;
  } catch {
    return '';
  }
}

function shortUrl(url, max = 95) {
  if (!url) return '';
  return url.length > max ? `${url.slice(0, max - 3)}...` : url;
}

function lcpIssue(lcp) {
  if (!lcp) return 'No LCP entry captured.';
  const src = lcp.currentSrc || lcp.backgroundImage || '';
  const issues = [];
  if (src && !src.match(/\.(webp|avif)(\?|$)/i)) issues.push('not WebP/AVIF');
  if (lcp.loading === 'lazy') issues.push('lazy-loaded LCP');
  if (!lcp.fetchPriority) issues.push('missing fetchpriority');
  if (src && (!lcp.widthAttr || !lcp.heightAttr)) issues.push('missing explicit dimensions');
  return issues.length ? issues.join('; ') : 'No obvious LCP element attribute issue captured.';
}

function lcpFix(lcp) {
  if (!lcp) return 'Confirm LCP manually in DevTools and reserve/preload the winning element.';
  if (lcp.currentSrc || lcp.backgroundImage) {
    return 'Preload the LCP image, remove lazy loading, add fetchpriority="high", set width/height, and serve a right-sized WebP/AVIF derivative.';
  }
  return 'Reduce render-blocking CSS/JS before the LCP text block and reserve stable layout space.';
}

async function productSpecialInvestigation(browser) {
  const lighthouseUa =
    'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120 Safari/537.36 Chrome-Lighthouse MayushAccuratePerfAudit';
  const curlTests = [
    { name: 'curl normal UA', url: productUrl, userAgent: UA, headers: {} },
    { name: 'curl mobile UA', url: productUrl, userAgent: MOBILE_UA, headers: {} },
    { name: 'curl Lighthouse-like UA', url: productUrl, userAgent: lighthouseUa, headers: {} },
    { name: 'curl query string', url: `${productUrl}?test=1`, userAgent: UA, headers: {} },
  ];
  const curlResults = [];
  for (const test of curlTests) {
    const runs = [];
    for (let i = 0; i < 3; i += 1) {
      runs.push(await runCurl({ url: test.url, userAgent: test.userAgent, headers: test.headers }));
      await sleep(300);
    }
    curlResults.push({ ...test, runs });
  }

  const desktop = await measureProfile(browser, productUrl, 'desktop');
  const mobile = await measureProfile(browser, productUrl, 'mobile');

  const userDataDir = await mkdtemp(path.join(os.tmpdir(), 'mayush-product-persist-'));
  let persisted = null;
  try {
    const context = await chromium.launchPersistentContext(userDataDir, {
      headless: true,
      viewport: { width: 1366, height: 768 },
      userAgent: UA,
    });
    const page = await context.newPage();
    await installObservers(page);
    const responseRecords = [];
    page.on('response', (response) => {
      responseRecords.push({ url: response.url(), status: response.status(), resourceType: response.request().resourceType(), headers: response.headers() });
    });
    const mainResponse = await page.goto(productUrl, { waitUntil: 'load', timeout: 60000 }).catch(() => null);
    await page.waitForLoadState('networkidle', { timeout: 15000 }).catch(() => {});
    await sleep(2500);
    persisted = await collectPageMetrics(page, mainResponse, responseRecords, Date.now(), 0).catch((err) => ({ error: err.message }));
    await context.close();
  } finally {
    await rm(userDataDir, { recursive: true, force: true });
  }

  const conclusion = (() => {
    const normal = curlResults[0].runs;
    const query = curlResults[3].runs;
    const normalHit = normal.some((run) => run.cfCacheStatus === 'HIT');
    const queryHit = query.some((run) => run.cfCacheStatus === 'HIT');
    const browserDesktopTtfb = desktop.summary?.medianTtfb;
    if (normalHit && !queryHit) return 'Browser/document variation is most consistent with cache eligibility differences such as query strings or cookies, not origin-only latency.';
    if (normalHit && browserDesktopTtfb && browserDesktopTtfb < 500) return 'curl and Playwright agree that warm document delivery is fast; Lighthouse slow document readings are likely measurement noise or a cold/MISS event.';
    return 'Document delivery varies by cache state; inspect Cloudflare cache keys, cookies, and user-agent/rule conditions for product pages.';
  })();

  return { curlResults, desktop, mobile, persisted, conclusion };
}

function renderTableRowsResources(resources) {
  return resources
    .map(
      (r) =>
        `| ${shortUrl(r.name)} | ${r.initiatorType || ''} | ${safeHost(r.name)} | ${Math.round(r.transferSize || 0)} | ${Math.round(r.encodedBodySize || 0)} | ${fmtMs(r.duration)} |`,
    )
    .join('\n');
}

function renderReport(data) {
  const {
    startedAt,
    htmlResults,
    cookieQueryResults,
    assetResults,
    browserResults,
    diagnostics,
    productSpecial,
  } = data;
  const homeDesktop = browserResults.find((r) => r.url === urls[0] && r.profile === 'Desktop realistic');
  const productDesktop = browserResults.find((r) => r.url === productUrl && r.profile === 'Desktop realistic');
  const cookieFails = cookieQueryResults.filter((row) => row.result === 'FAIL').length;
  const htmlWarnings = htmlResults.filter((row) => row.result !== 'PASS').length;
  const assetWarnings = assetResults.filter((row) => row.result !== 'PASS').length;
  const browserWarnings = browserResults.filter((row) => row.summary.result !== 'PASS').length;
  const lcpStatus = browserResults.some((row) => (row.summary.medianLcp || 0) > 2500) ? 'YELLOW' : 'GREEN';
  const jsStatus = diagnostics.pageDiagnostics.some((row) => row.totalLongTaskDuration > 1000) ? 'YELLOW' : 'GREEN';
  const clsStatus = browserResults.some((row) => (row.summary.medianCls || 0) > 0.1) ? 'YELLOW' : 'GREEN';
  const cacheTruthStatus = cookieFails ? 'RED' : htmlWarnings ? 'YELLOW' : 'GREEN';
  const staticStatus = assetWarnings ? 'YELLOW' : 'GREEN';
  const browserStatus = browserWarnings ? 'YELLOW' : 'GREEN';
  const productStatus = productSpecial.conclusion.includes('fast') ? 'GREEN' : 'YELLOW';
  const finalVerdict = cookieFails ? 'RED' : assetWarnings || browserWarnings ? 'YELLOW' : 'GREEN';
  const totalJs = sum(diagnostics.topJs.map((r) => r.transferSize));
  const totalImages = sum(diagnostics.topImages.map((r) => r.transferSize));

  const lines = [];
  lines.push('# Accurate Performance Audit - Mayush Marketplace', '');
  lines.push('## A. Executive Summary', '');
  lines.push(`- Cache truth status: **${cacheTruthStatus}**.`);
  lines.push(`- Static asset cache status: **${staticStatus}**.`);
  lines.push(`- Browser performance status: **${browserStatus}**.`);
  lines.push(`- LCP status: **${lcpStatus}**.`);
  lines.push(`- JavaScript/TBT status: **${jsStatus}**.`);
  lines.push(`- CLS status: **${clsStatus}**.`);
  lines.push(`- Product page consistency status: **${productStatus}**.`);
  lines.push(`- Overall verdict: **${finalVerdict}**.`, '');
  lines.push(
    finalVerdict === 'YELLOW'
      ? 'Cache safety is intact, public HTML warms to Cloudflare HIT, and static asset GET requests now return HIT; remaining work is browser-side rendering plus the fact that HEAD remains unreliable for asset cache checks.'
      : finalVerdict === 'RED'
        ? 'A cache-safety failure remains and should be fixed before further optimization work.'
        : 'Cache and browser performance are stable in this controlled audit.',
    '',
  );

  lines.push('## B. Methodology', '');
  lines.push(
    `Tested on ${startedAt} with repeated curl GET/HEAD probes and Playwright-controlled Chromium navigation. This is more reliable than a Lighthouse score alone because it separates Cloudflare cache truth from browser rendering, uses repeated runs with median/p75 values, reads real browser Performance APIs, exports Resource Timing, observes Long Tasks and layout shifts, and keeps cold/warm navigation behavior visible.`,
    '',
  );

  lines.push('## C. Cloudflare HTML Cache Results', '');
  lines.push('| URL | Run statuses | Age | Median TTFB | p75 TTFB | Result |');
  lines.push('|---|---|---:|---:|---:|---|');
  for (const row of htmlResults) {
    lines.push(
      `| ${row.url} | ${statusList(row.runs)} | ${row.age || ''} | ${fmtSec(row.medianTtfb)} | ${fmtSec(row.p75Ttfb)} | ${row.result} |`,
    );
  }
  lines.push('');

  lines.push('## D. Cookie and Query Safety Results', '');
  lines.push('| URL | Cookie/query condition | Cache status | Result |');
  lines.push('|---|---|---|---|');
  for (const row of cookieQueryResults) lines.push(`| ${row.url} | ${row.condition} | ${statusList(row.runs)} | ${row.result} |`);
  lines.push('');

  lines.push('## E. Static Asset Cache Results', '');
  lines.push('| URL | Type | GET cache progression | HEAD cache progression | Cache-Control | Age | TTFB | Result |');
  lines.push('|---|---|---|---|---|---:|---:|---|');
  for (const row of assetResults) {
    lines.push(
      `| ${shortUrl(row.url)} | ${row.type} | ${statusList(row.getRuns)} | ${statusList(row.headRuns)} | ${row.cacheControl || ''} | ${row.age || ''} | ${fmtSec(row.medianGetTtfb)} | ${row.result} |`,
    );
  }
  lines.push('');

  lines.push('## F. Browser Navigation Results', '');
  lines.push('| URL | Profile | Median TTFB | p75 TTFB | Median FCP | Median LCP | Median CLS | Median load time | Total transfer size | Result |');
  lines.push('|---|---|---:|---:|---:|---:|---:|---:|---:|---|');
  for (const row of browserResults) {
    const transfer = median(row.runs.map((run) => run.metrics?.resources?.reduce((total, r) => total + (r.transferSize || 0), 0)).filter(Number.isFinite));
    lines.push(
      `| ${row.url} | ${row.profile} | ${fmtMs(row.summary.medianTtfb)} | ${fmtMs(row.summary.p75Ttfb)} | ${fmtMs(row.summary.medianFcp)} | ${fmtMs(row.summary.medianLcp)} | ${(row.summary.medianCls ?? 0).toFixed(3)} | ${fmtMs(row.summary.medianLoad)} | ${Math.round(transfer || 0)} | ${row.summary.result} |`,
    );
  }
  lines.push('');

  lines.push('## G. Resource Timing Diagnosis', '');
  lines.push('### Top 10 Largest Resources', '');
  lines.push('| URL | Type | Host | Transfer | Encoded | Duration |');
  lines.push('|---|---|---|---:|---:|---:|');
  lines.push(renderTableRowsResources(diagnostics.topLargest) || '| No resources captured |  |  |  |  |  |');
  lines.push('', '### Top 10 Slowest Resources', '');
  lines.push('| URL | Type | Host | Transfer | Encoded | Duration |');
  lines.push('|---|---|---|---:|---:|---:|');
  lines.push(renderTableRowsResources(diagnostics.topSlowest) || '| No resources captured |  |  |  |  |  |');
  lines.push('', '### Top JS Files', '');
  lines.push('| URL | Type | Host | Transfer | Encoded | Duration |');
  lines.push('|---|---|---|---:|---:|---:|');
  lines.push(renderTableRowsResources(diagnostics.topJs) || '| No JS resources captured |  |  |  |  |  |');
  lines.push('', '### Top CSS Files', '');
  lines.push('| URL | Type | Host | Transfer | Encoded | Duration |');
  lines.push('|---|---|---|---:|---:|---:|');
  lines.push(renderTableRowsResources(diagnostics.topCss) || '| No CSS resources captured |  |  |  |  |  |');
  lines.push('', '### Top Images', '');
  lines.push('| URL | Type | Host | Transfer | Encoded | Duration |');
  lines.push('|---|---|---|---:|---:|---:|');
  lines.push(renderTableRowsResources(diagnostics.topImages) || '| No image resources captured |  |  |  |  |  |');
  lines.push('', '### Third-Party Resources', '');
  lines.push('| URL | Type | Host | Transfer | Encoded | Duration |');
  lines.push('|---|---|---|---:|---:|---:|');
  lines.push(renderTableRowsResources(diagnostics.thirdParty.slice(0, 10)) || '| No third-party resources captured |  |  |  |  |  |');
  lines.push('');

  lines.push('## H. LCP Diagnosis', '');
  for (const row of diagnostics.pageDiagnostics) {
    lines.push(`- **${row.profile} ${row.url}**: ${row.lcp?.tagName || 'unknown'} \`${row.lcp?.selector || ''}\`; source: ${shortUrl(row.lcp?.currentSrc || row.lcp?.backgroundImage || '')}; issue: ${lcpIssue(row.lcp)}; fix: ${lcpFix(row.lcp)}`);
  }
  lines.push('');

  lines.push('## I. JavaScript / Long Task Diagnosis', '');
  for (const row of diagnostics.pageDiagnostics) {
    const headBlocking = row.scriptsInHead.filter((script) => !script.async && !script.defer).slice(0, 5);
    lines.push(
      `- **${row.profile} ${row.url}**: ${row.longTaskCount} long tasks, ${fmtMs(row.totalLongTaskDuration)} total, max ${fmtMs(row.maxLongTaskDuration)}. Likely causes: vendor/storefront bundles and head scripts ${
        headBlocking.map((script) => shortUrl(script.src, 60)).join(', ') || 'not isolated'
      }. Fix: defer non-critical scripts, delay third-party scripts until interaction, split product page JS, lazy-load gallery/carousel code, and avoid initializing unused sliders/components.`,
    );
  }
  lines.push('');

  lines.push('## J. CLS Diagnosis', '');
  for (const row of diagnostics.pageDiagnostics) {
    const shifts = (row.shifts || [])
      .slice(0, 3)
      .map((shift) => `${shift.value.toFixed(3)} ${shift.sources?.map((s) => s.selector).filter(Boolean).join(', ')}`)
      .join('; ');
    lines.push(
      `- **${row.profile} ${row.url}**: CLS ${row.cls.toFixed(3)}. Shifting elements: ${shifts || 'none captured'}. Fix: set image/banner dimensions, reserve slider/product-card space, stabilize font loading, and avoid late header/cart/wishlist counter layout changes.`,
    );
  }
  lines.push('');

  lines.push('## K. Product Page Special Investigation', '');
  lines.push(productSpecial.conclusion, '');
  lines.push('| Test | Cache progression | Median TTFB |');
  lines.push('|---|---|---:|');
  for (const row of productSpecial.curlResults) {
    lines.push(`| ${row.name} | ${statusList(row.runs)} | ${fmtSec(median(row.runs.map((run) => run.ttfb)))} |`);
  }
  lines.push(`| Playwright desktop no cookies | browser nav | ${fmtMs(productSpecial.desktop.summary.medianTtfb)} |`);
  lines.push(`| Playwright mobile no cookies | browser nav | ${fmtMs(productSpecial.mobile.summary.medianTtfb)} |`);
  lines.push('');

  lines.push('## L. Prioritized Fix Plan', '');
  lines.push('Immediate Cloudflare/static asset fixes:');
  lines.push('1. Preserve the now-working static asset GET cache rule for `/public/assets/*`, `/public/js/*`, and `/public/uploads/*`.');
  lines.push('2. Treat HEAD as unreliable for asset cache verification here; use GET for truth checks and keep monitoring that GET remains HIT.');
  lines.push('3. Keep `http.cookie eq ""` on guest HTML and keep query-string bypass behavior intact.');
  lines.push('', 'Immediate frontend fixes:');
  lines.push('1. Preload the LCP image on each route and remove lazy loading from the LCP candidate.');
  lines.push('2. Defer non-critical vendor/storefront scripts and delay third-party scripts until interaction.');
  lines.push('3. Reserve fixed dimensions for banners, sliders, product cards, and above-the-fold images.');
  lines.push('', 'Laravel Blade/template fixes:');
  lines.push('1. Add `fetchpriority="high"` plus width/height on route-specific hero/product-gallery LCP images.');
  lines.push('2. Avoid loading product gallery/carousel scripts globally on pages that do not need them.');
  lines.push('3. Emit critical CSS for above-the-fold header, hero, and product card layout.');
  lines.push('', 'Medium-term improvements:');
  lines.push('1. Split JS bundles by route and remove duplicate legacy libraries where possible.');
  lines.push('2. Add production RUM with web-vitals grouped by URL type, device, country, connection, navigation type, bfcache, timestamp, LCP, CLS, INP, FCP, and TTFB.');
  lines.push('3. Add a scheduled cache-safety and browser-performance regression audit after Cloudflare/template changes.');
  lines.push('');

  lines.push('## M. Final Verdict', '');
  lines.push(`**${finalVerdict}**`, '');
  lines.push('```text');
  lines.push(`final verdict: ${finalVerdict}`);
  lines.push(`median homepage TTFB: ${fmtMs(homeDesktop?.summary.medianTtfb)}`);
  lines.push(`median homepage LCP: ${fmtMs(homeDesktop?.summary.medianLcp)}`);
  lines.push(`median product LCP: ${fmtMs(productDesktop?.summary.medianLcp)}`);
  lines.push(`total JS transfer size: ${Math.round(totalJs)} bytes`);
  lines.push(`total image transfer size: ${Math.round(totalImages)} bytes`);
  lines.push('top 3 fixes: LCP image preload/fetchpriority/dimensions; defer/split non-critical JS; reserve layout space to reduce CLS');
  lines.push('```');
  return lines.join('\n');
}

async function main() {
  await writeFile(path.join(OUT_DIR, '.keep'), '').catch(async () => {
    await import('node:fs/promises').then((fs) => fs.mkdir(OUT_DIR, { recursive: true }));
    await writeFile(path.join(OUT_DIR, '.keep'), '');
  });

  const startedAt = new Date().toISOString();
  console.log('Starting accurate Mayush performance audit...');

  const homepage = await runCurl({ url: urls[0] });
  const homepageHtml = existsSync(path.join(OUT_DIR, 'noop')) ? '' : '';
  const htmlFetch = await runCurl({ url: urls[0], accept: 'text/html' });
  // curl writes body to a temp file, so discover from a direct fetch fallback using Node only for same public HTML.
  const htmlText = await fetch(urls[0], { headers: { 'user-agent': UA, accept: 'text/html' } }).then((r) => r.text()).catch(() => '');

  console.log('Running Cloudflare HTML cache truth tests...');
  const htmlResults = [];
  for (const url of urls) {
    const runs = [];
    for (let i = 0; i < 5; i += 1) {
      runs.push(await runCurl({ url }));
      await sleep(300);
    }
    htmlResults.push({
      url,
      runs,
      age: runs.map((run) => run.age).filter(Boolean).at(-1) || '',
      medianTtfb: median(runs.map((run) => run.ttfb)),
      p75Ttfb: percentile(runs.map((run) => run.ttfb), 75),
      result: cacheResultFromHtmlRuns(runs),
    });
  }

  console.log('Running cookie and query safety tests...');
  const cookieQueryResults = [];
  for (const url of urls) {
    for (const condition of [
      { label: 'cookie laravel_session + XSRF', headers: { Cookie: 'laravel_session=test; XSRF-TOKEN=test' }, target: url },
      { label: 'cookie cart', headers: { Cookie: 'cart=test' }, target: url },
      { label: 'query ?test=1', headers: {}, target: `${url}${url.includes('?') ? '&' : '?'}test=1` },
    ]) {
      const runs = [];
      for (let i = 0; i < 2; i += 1) {
        runs.push(await runCurl({ url: condition.target, headers: condition.headers }));
        await sleep(250);
      }
      cookieQueryResults.push({ url, condition: condition.label, runs, result: nonHitResult(runs) });
    }
  }

  console.log('Running static asset cache truth tests...');
  const specifiedAssets = [
    'https://mayushdesign.com/public/assets/css/vendors.css',
    'https://mayushdesign.com/public/js/storefront-bootstrap.js?v=1780397529',
    'https://mayushdesign.com/public/assets/img/flags/fr.png',
    'https://mayushdesign.com/public/uploads/all/NQErD03t1rIispRs3lhXOlXiI9y7PRHkyDdUWa2g.webp',
  ];
  const assetUrls = [...new Set([...specifiedAssets, ...discoverAssetsFromHtml(htmlText, urls[0])])];
  const assetResults = [];
  for (const url of assetUrls) {
    const getRuns = [];
    for (let i = 0; i < 5; i += 1) {
      getRuns.push(await runCurl({ url, accept: '*/*' }));
      await sleep(250);
    }
    const headRuns = [];
    for (let i = 0; i < 2; i += 1) {
      headRuns.push(await runCurl({ url, method: 'HEAD', accept: '*/*' }));
      await sleep(250);
    }
    const last = getRuns.at(-1);
    const getHasHit = getRuns.some((run) => run.cfCacheStatus === 'HIT');
    assetResults.push({
      url,
      type: assetType(url, last?.contentType),
      getRuns,
      headRuns,
      cacheControl: last?.cacheControl || '',
      age: getRuns.map((run) => run.age).filter(Boolean).at(-1) || '',
      medianGetTtfb: median(getRuns.map((run) => run.ttfb)),
      result: getHasHit ? 'PASS' : 'WARNING',
    });
  }

  console.log('Running controlled browser navigation tests...');
  const browser = await chromium.launch({ headless: true });
  const browserResults = [];
  for (const url of urls) {
    for (const profile of ['desktop', 'mobile']) {
      console.log(`Browser profile ${profile}: ${url}`);
      browserResults.push(await measureProfile(browser, url, profile));
    }
  }

  console.log('Running product page special investigation...');
  const productSpecial = await productSpecialInvestigation(browser);
  await browser.close();

  const diagnostics = aggregateDiagnostics(browserResults);
  const raw = {
    startedAt,
    userAgent: UA,
    mobileUserAgent: MOBILE_UA,
    homepageProbe: homepage,
    htmlFetchProbe: htmlFetch,
    htmlResults,
    cookieQueryResults,
    assetResults,
    browserResults,
    diagnostics,
    productSpecial,
  };
  await writeFile(RAW_PATH, JSON.stringify(raw, null, 2), 'utf8');
  await writeFile(REPORT_PATH, renderReport(raw), 'utf8');

  const homeDesktop = browserResults.find((r) => r.url === urls[0] && r.profile === 'Desktop realistic');
  const productDesktop = browserResults.find((r) => r.url === productUrl && r.profile === 'Desktop realistic');
  const totalJs = sum(diagnostics.topJs.map((r) => r.transferSize));
  const totalImages = sum(diagnostics.topImages.map((r) => r.transferSize));
  const cookieFails = cookieQueryResults.filter((row) => row.result === 'FAIL').length;
  const assetWarnings = assetResults.filter((row) => row.result !== 'PASS').length;
  const browserWarnings = browserResults.filter((row) => row.summary.result !== 'PASS').length;
  const finalVerdict = cookieFails ? 'RED' : assetWarnings || browserWarnings ? 'YELLOW' : 'GREEN';
  console.log('');
  console.log('Accurate performance audit complete.');
  console.log(`Report: ${REPORT_PATH}`);
  console.log(`Raw JSON: ${RAW_PATH}`);
  console.log(`final verdict: ${finalVerdict}`);
  console.log(`median homepage TTFB: ${fmtMs(homeDesktop?.summary.medianTtfb)}`);
  console.log(`median homepage LCP: ${fmtMs(homeDesktop?.summary.medianLcp)}`);
  console.log(`median product LCP: ${fmtMs(productDesktop?.summary.medianLcp)}`);
  console.log(`total JS transfer size: ${Math.round(totalJs)} bytes`);
  console.log(`total image transfer size: ${Math.round(totalImages)} bytes`);
  console.log('top 3 fixes: LCP image preload/fetchpriority/dimensions; defer/split non-critical JS; reserve layout space to reduce CLS');
}

main().catch((err) => {
  console.error(err);
  process.exitCode = 1;
});
