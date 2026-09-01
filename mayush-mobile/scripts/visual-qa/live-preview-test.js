/**
 * Live Preview Test for Mayush Mobile Step 1 Discovery and Search Cluster
 */

const fs = require('fs');
const path = require('path');
const http = require('http');
const mime = require('mime-types');
const { chromium } = require('playwright');

const DIST_DIR = path.join(__dirname, '../../dist');
const PREVIEWS_DIR = path.join(__dirname, '../../docs/frontend-completion/previews');
fs.mkdirSync(PREVIEWS_DIR, { recursive: true });

const PORT = 8086;

const server = http.createServer((req, res) => {
  let filePath = path.join(DIST_DIR, req.url === '/' || req.url.startsWith('/?') ? 'index.html' : req.url.split('?')[0]);
  if (!fs.existsSync(filePath)) filePath = path.join(DIST_DIR, 'index.html');
  const ext = path.extname(filePath);
  const contentType = mime.lookup(ext) || 'application/octet-stream';
  res.writeHead(200, { 'Content-Type': contentType });
  res.end(fs.readFileSync(filePath));
}).listen(PORT, async () => {
  console.log(`Live Preview Test Server running at http://localhost:${PORT}`);
  const browser = await chromium.launch();
  const page = await browser.newPage({ viewport: { width: 393, height: 852 }, deviceScaleFactor: 1 });

  const testScreens = [
    { key: '06-add-new-address-form-v2-fr', name: '01_add_address_default' },
    { key: '06-add-address-validation-errors-fr', name: '02_add_address_errors' },
  ];

  console.log('Capturing preview screenshots...');
  for (const item of testScreens) {
    await page.goto(`http://localhost:${PORT}/?qaScreen=${item.key}`);
    await page.waitForTimeout(600);
    const savePath = path.join(PREVIEWS_DIR, `${item.name}.png`);
    await page.screenshot({ path: savePath });
    console.log(`[PASS] Captured preview: ${item.name}.png`);
  }

  // Also capture the main app bundle home screen
  await page.goto(`http://localhost:${PORT}/`);
  await page.waitForTimeout(800);
  const homePath = path.join(PREVIEWS_DIR, `00_main_app_home.png`);
  await page.screenshot({ path: homePath });
  console.log(`[PASS] Captured preview: 00_main_app_home.png`);

  await browser.close();
  server.close();
  console.log('LIVE PREVIEW TEST COMPLETE! Previews saved to docs/frontend-completion/previews/');
});
