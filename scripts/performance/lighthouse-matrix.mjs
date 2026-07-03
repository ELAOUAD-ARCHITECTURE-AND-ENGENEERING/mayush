import { launch } from 'chrome-launcher';
import lighthouse from 'lighthouse';
import { chromium } from 'playwright';
import { mkdir, writeFile } from 'node:fs/promises';
import { execFileSync } from 'node:child_process';

const baseUrl = (process.env.LIGHTHOUSE_BASE_URL || 'http://127.0.0.1:8000').replace(/\/$/, '');
const categorySlug = process.env.LIGHTHOUSE_CATEGORY_SLUG;
const productSlug = process.env.LIGHTHOUSE_PRODUCT_SLUG;
const outputDir = process.env.LIGHTHOUSE_OUTPUT_DIR || 'storage/app/lighthouse';
const cloudflareMode = process.env.LIGHTHOUSE_CLOUDFLARE_MODE || 'restricted-bypass';
const runs = Number(process.env.LIGHTHOUSE_RUNS || 3);

if (!categorySlug || !productSlug) {
    throw new Error('Set LIGHTHOUSE_CATEGORY_SLUG and LIGHTHOUSE_PRODUCT_SLUG to stable staging fixtures.');
}

const profiles = [
    ['home', '/', true],
    ['category', `/category/${categorySlug}`, true],
    ['product', `/product/${productSlug}`, true],
    ['cart', '/cart', false],
    ['checkout', '/checkout', false],
];

const budgets = {
    performance: 99,
    accessibility: 95,
    bestPractices: 95,
    seo: 95,
    lcp: 1800,
    fcp: 1500,
    tbt: 100,
    cls: 0.05,
    serverResponse: 400,
    htmlBytes: 100 * 1024,
    scriptBytes: 120 * 1024,
    imageBytes: 250 * 1024,
};

const sha = process.env.GITHUB_SHA || execFileSync('git', ['rev-parse', 'HEAD'], { encoding: 'utf8' }).trim();
const assetManifestVersion = process.env.STOREFRONT_ASSET_MANIFEST || 'working-tree';
const imageQueueDepth = process.env.IMAGE_QUEUE_DEPTH || 'unknown';
const chrome = await launch({ chromeFlags: ['--headless', '--no-sandbox', '--disable-dev-shm-usage'] });
let guestCookie = '';

async function prepareGuestSession() {
    if (process.env.LIGHTHOUSE_PREPARE_GUEST_CART === '0') return;

    const browser = await chromium.connectOverCDP(`http://127.0.0.1:${chrome.port}`);
    try {
        const context = browser.contexts()[0];
        if (! context) {
            throw new Error('Chrome did not expose a default browser context for guest cart setup.');
        }

        const page = await context.newPage();
        await page.goto(`${baseUrl}/product/${productSlug}`, { waitUntil: 'domcontentloaded' });
        const addToCart = page.locator(process.env.LIGHTHOUSE_ADD_TO_CART_SELECTOR || '.add-to-cart').first();
        await addToCart.waitFor({ state: 'visible', timeout: 10000 });
        await addToCart.click({ force: true });
        await page.waitForTimeout(1500);
        guestCookie = (await context.cookies())
            .map(cookie => `${cookie.name}=${cookie.value}`)
            .join('; ');
        await page.close();
    } finally {
        // Lighthouse owns this Chrome process and closes it after the matrix finishes.
    }
}

function auditValue(lhr, id) {
    return lhr.audits[id]?.numericValue ?? 0;
}

function extract(lhr, indexable) {
    const summary = Object.fromEntries((lhr.audits['resource-summary']?.details?.items || []).map(item => [item.resourceType, item.transferSize]));
    const root = (lhr.audits['network-requests']?.details?.items || []).find(item => item.url === lhr.finalDisplayedUrl);
    return {
        performance: Math.round(lhr.categories.performance.score * 100),
        accessibility: Math.round(lhr.categories.accessibility.score * 100),
        bestPractices: Math.round(lhr.categories['best-practices'].score * 100),
        seo: indexable ? Math.round(lhr.categories.seo.score * 100) : null,
        lcp: auditValue(lhr, 'largest-contentful-paint'),
        fcp: auditValue(lhr, 'first-contentful-paint'),
        tbt: auditValue(lhr, 'total-blocking-time'),
        cls: auditValue(lhr, 'cumulative-layout-shift'),
        serverResponse: auditValue(lhr, 'server-response-time'),
        htmlBytes: root?.transferSize || 0,
        scriptBytes: summary.script || 0,
        imageBytes: summary.image || 0,
    };
}

function median(values) {
    const sorted = [...values].sort((a, b) => a - b);
    return sorted[Math.floor(sorted.length / 2)];
}

function medianMetrics(results) {
    return Object.fromEntries(Object.keys(results[0]).map(key => {
        const values = results.map(result => result[key]).filter(value => value !== null);
        return [key, values.length ? median(values) : null];
    }));
}

function failures(metrics) {
    return [
        ['performance', 'min'], ['accessibility', 'min'], ['bestPractices', 'min'], ['seo', 'min'],
        ['lcp', 'max'], ['fcp', 'max'], ['tbt', 'max'], ['cls', 'max'], ['serverResponse', 'max'],
        ['htmlBytes', 'max'], ['scriptBytes', 'max'], ['imageBytes', 'max'],
    ].filter(([name, direction]) => metrics[name] !== null && (direction === 'min' ? metrics[name] < budgets[name] : metrics[name] > budgets[name]))
        .map(([name]) => `${name}=${metrics[name]} budget=${budgets[name]}`);
}

await mkdir(outputDir, { recursive: true });
const matrix = [];

try {
    await prepareGuestSession();

    for (const [name, path, indexable] of profiles) {
        const results = [];
        for (let run = 1; run <= runs; run += 1) {
            const report = await lighthouse(`${baseUrl}${path}`, {
                port: chrome.port,
                output: 'json',
                logLevel: 'error',
                onlyCategories: ['performance', 'accessibility', 'best-practices', 'seo'],
                extraHeaders: ['cart', 'checkout'].includes(name) && guestCookie ? { Cookie: guestCookie } : undefined,
                formFactor: 'mobile',
                screenEmulation: { mobile: true, width: 390, height: 844, deviceScaleFactor: 1, disabled: false },
            });
            await writeFile(`${outputDir}/${name}-${run}.json`, report.report);
            results.push(extract(report.lhr, indexable));
        }

        const medians = medianMetrics(results);
        matrix.push({ route: path, profile: name, medians, failures: failures(medians) });
    }
} finally {
    await chrome.kill();
}

const summary = {
    generatedAt: new Date().toISOString(),
    sha,
    assetManifestVersion,
    imageQueueDepth,
    cloudflareMode,
    cacheState: process.env.LIGHTHOUSE_CACHE_STATE || 'cold',
    budgets,
    matrix,
};

await writeFile(`${outputDir}/summary.json`, `${JSON.stringify(summary, null, 2)}\n`);
console.table(matrix.map(profile => ({ profile: profile.profile, ...profile.medians, failures: profile.failures.join('; ') })));

if (matrix.some(profile => profile.failures.length)) process.exitCode = 1;
