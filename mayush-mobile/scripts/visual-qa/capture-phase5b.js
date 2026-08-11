const fs = require('fs');
const http = require('http');
const path = require('path');
const { chromium } = require('playwright');
const config = require('./visual-qa-config.json');
const { compareImages } = require('./compare-images');

const prefix = process.argv.includes('--after') ? 'after' : 'before';

function serveStatic(distDir, port) {
  return new Promise((resolve) => {
    const mimeTypes = {
      '.html': 'text/html',
      '.js': 'text/javascript',
      '.css': 'text/css',
      '.json': 'application/json',
      '.png': 'image/png',
      '.jpg': 'image/jpeg',
      '.gif': 'image/gif',
      '.svg': 'image/svg+xml',
      '.ttf': 'font/ttf',
    };

    const server = http.createServer((req, res) => {
      const urlPath = req.url.split('?')[0];
      let filePath = path.join(distDir, urlPath === '/' ? 'index.html' : urlPath);

      if (!fs.existsSync(filePath) || fs.statSync(filePath).isDirectory()) {
        filePath = path.join(distDir, 'index.html');
      }

      const ext = path.extname(filePath).toLowerCase();
      const contentType = mimeTypes[ext] || 'application/octet-stream';

      fs.readFile(filePath, (err, content) => {
        if (err) {
          res.writeHead(500);
          res.end('Error loading file');
        } else {
          res.writeHead(200, { 'Content-Type': contentType });
          res.end(content, 'utf-8');
        }
      });
    });

    server.listen(port, () => {
      console.log(`[QA Pipeline] Serving ${distDir} on http://localhost:${port}`);
      resolve(server);
    });
  });
}

async function runCapturePipeline() {
  const distDir = path.resolve(__dirname, '../../', config.webBuildDir);
  if (!fs.existsSync(distDir)) {
    console.error(`[QA Pipeline Error] Build directory ${distDir} does not exist. Run npx expo export --platform web first.`);
    process.exit(1);
  }

  const server = await serveStatic(distDir, config.port);
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({
    viewport: {
      width: config.viewport.width,
      height: config.viewport.height,
    },
    deviceScaleFactor: config.viewport.deviceScaleFactor,
    locale: config.locale,
    timezoneId: config.timezone,
  });

  const page = await context.newPage();

  console.log(`\n==================================================`);
  console.log(`STARTING DETERMINISTIC 393×852 CAPTURE (${prefix.toUpperCase()})`);
  console.log(`==================================================\n`);

  for (const screen of config.screens) {
    const screenDir = path.resolve(__dirname, '../../', config.screensDir, screen.name);
    if (!fs.existsSync(screenDir)) {
      fs.mkdirSync(screenDir, { recursive: true });
    }

    const url = `http://localhost:${config.port}/?qaScreen=${encodeURIComponent(screen.qaScreenKey)}`;
    console.log(`[Capturing ${screen.name}] -> ${url}`);

    await page.goto(url, { waitUntil: 'networkidle' });

    try {
      await page.waitForSelector('#visual-qa-ready', { timeout: 3000 });
    } catch (e) {
      console.warn(`  [Warning] #visual-qa-ready timeout for ${screen.name}, capturing anyway.`);
    }

    await page.waitForTimeout(200);

    const appCapturePath = path.join(screenDir, `app-${prefix}-393x852.png`);
    await page.screenshot({ path: appCapturePath, fullPage: false });
    console.log(`  [Saved] ${path.relative(process.cwd(), appCapturePath)}`);

    const sourcePath = path.join(screenDir, 'figma-source-393x852.png');
    let comparisonResult = null;

    if (fs.existsSync(sourcePath)) {
      comparisonResult = await compareImages(sourcePath, appCapturePath, screenDir, prefix);
      console.log(`  [Diff ${prefix}] Mismatch: ${comparisonResult.mismatchPercentage}% (${comparisonResult.mismatchedPixels} px)`);
    } else {
      console.warn(`  [Warning] Missing source frame: ${path.relative(process.cwd(), sourcePath)}`);
    }

    const resultJsonPath = path.join(screenDir, 'result.json');
    let existingResult = {};
    if (fs.existsSync(resultJsonPath)) {
      try {
        existingResult = JSON.parse(fs.readFileSync(resultJsonPath, 'utf8'));
      } catch (e) {
        existingResult = {};
      }
    }

    const updatedResult = {
      ...existingResult,
      screenName: screen.name,
      figmaNodeId: screen.figmaNodeId,
      applicationScreenKey: screen.qaScreenKey,
      implementationFile: existingResult.implementationFile || `mayush-mobile/src/screens/checkout/${screen.name}.tsx`,
      sourceDimensions: { width: 393, height: 852 },
      applicationDimensions: { width: 393, height: 852 },
      comparisonDate: new Date().toISOString().split('T')[0],
      captureMethod: {
        engine: 'Playwright Chromium',
        viewport: '393×852',
        deviceScaleFactor: 1,
        source: 'Figma get_screenshot at 393×852',
        application: `Deterministically rendered via VisualQaApp (${prefix})`,
      },
      finalStatus: comparisonResult && comparisonResult.mismatchPercentage < 5 ? 'PIXEL_PARITY_PASS' : 'NEEDS_PIXEL_CORRECTION',
      evidencePaths: {
        ...(existingResult.evidencePaths || {}),
        figmaSource: 'figma-source-393x852.png',
        [`app${prefix.charAt(0).toUpperCase() + prefix.slice(1)}`]: `app-${prefix}-393x852.png`,
        [`overlay${prefix.charAt(0).toUpperCase() + prefix.slice(1)}`]: `overlay-${prefix}-50.png`,
        [`sideBySide${prefix.charAt(0).toUpperCase() + prefix.slice(1)}`]: `side-by-side-${prefix}.png`,
        [`pixelDiff${prefix.charAt(0).toUpperCase() + prefix.slice(1)}`]: `pixel-diff-${prefix}.png`,
      },
      diffMetrics: comparisonResult
        ? {
            mismatchedPixels: comparisonResult.mismatchedPixels,
            totalPixels: comparisonResult.totalPixels,
            mismatchPercentage: comparisonResult.mismatchPercentage,
          }
        : null,
    };

    fs.writeFileSync(resultJsonPath, JSON.stringify(updatedResult, null, 2));
  }

  await browser.close();
  server.close();
  console.log(`\n==================================================`);
  console.log(`393×852 CAPTURE & COMPARISON PIPELINE COMPLETE`);
  console.log(`==================================================\n`);
}

runCapturePipeline().catch((err) => {
  console.error('[QA Pipeline Fatal Error]', err);
  process.exit(1);
});
