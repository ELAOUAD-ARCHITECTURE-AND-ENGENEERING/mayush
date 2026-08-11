/**
 * Regional Visual QA Diff Analyzer for Mayush Mobile
 * Analyzes pixel differences across specific UI regions of 393x852 screen captures.
 */

const fs = require('fs');
const path = require('path');
const { PNG } = require('pngjs');

(async () => {
  const targetScreen = process.argv.includes('--screen')
    ? process.argv[process.argv.indexOf('--screen') + 1]
    : '06-add-address-validation-errors-fr';

  const TARGET_DIR = path.join(
    __dirname,
    '../../design-reference/mayush-mobile-design/validation/phase-5b-pixel-parity',
    targetScreen
  );

  const sourcePath = path.join(TARGET_DIR, 'figma-source-393x852.png');
  const appPath = fs.existsSync(path.join(TARGET_DIR, 'app-after-393x852.png'))
    ? path.join(TARGET_DIR, 'app-after-393x852.png')
    : path.join(TARGET_DIR, 'app-before-393x852.png');

  if (!fs.existsSync(sourcePath) || !fs.existsSync(appPath)) {
    console.error(`Source or App image missing in ${TARGET_DIR}`);
    process.exit(1);
  }

  const sourcePng = PNG.sync.read(fs.readFileSync(sourcePath));
  const appPng = PNG.sync.read(fs.readFileSync(appPath));

  const width = 393;
  const height = 852;

  const regions = [
    { name: 'Safe Area', x: 0, y: 0, w: 393, h: 44 },
    { name: 'Header', x: 0, y: 44, w: 393, h: 64 },
    { name: 'Checkout Progress Indicator', x: 0, y: 108, w: 393, h: 50 },
    { name: 'Screen Title & Introduction', x: 0, y: 158, w: 393, h: 50 },
    { name: 'First Error Field (Name)', x: 0, y: 208, w: 393, h: 80 },
    { name: 'Phone Field & Error', x: 0, y: 288, w: 393, h: 80 },
    { name: 'City and Postal Fields & Error', x: 0, y: 368, w: 393, h: 100 },
    { name: 'Address Field & Error', x: 0, y: 468, w: 393, h: 80 },
    { name: 'Default-Address Control', x: 0, y: 548, w: 393, h: 50 },
    { name: 'Validation/Helper Area', x: 0, y: 598, w: 393, h: 40 },
    { name: 'Fixed CTA Area', x: 0, y: 638, w: 393, h: 130 },
    { name: 'Bottom Safe Area', x: 0, y: 768, w: 393, h: 84 },
  ];

  const results = {
    screenName: targetScreen,
    analyzedAt: new Date().toISOString(),
    targetImage: path.basename(appPath),
    totalPixels: width * height,
    regionalBreakdown: [],
  };

  regions.forEach((r) => {
    let mismatchedPixels = 0;
    const regionTotalPixels = r.w * r.h;

    for (let y = r.y; y < r.y + r.h; y++) {
      for (let x = r.x; x < r.x + r.w; x++) {
        const idx = (width * y + x) << 2;
        const r1 = sourcePng.data[idx];
        const g1 = sourcePng.data[idx + 1];
        const b1 = sourcePng.data[idx + 2];

        const r2 = appPng.data[idx];
        const g2 = appPng.data[idx + 1];
        const b2 = appPng.data[idx + 2];

        const diff = Math.abs(r1 - r2) + Math.abs(g1 - g2) + Math.abs(b1 - b2);
        if (diff > 35) {
          mismatchedPixels++;
        }
      }
    }

    const mismatchPercent = Number(((mismatchedPixels / regionTotalPixels) * 100).toFixed(2));
    results.regionalBreakdown.push({
      region: r.name,
      boundingBox: { x: r.x, y: r.y, width: r.w, height: r.h },
      totalRegionPixels: regionTotalPixels,
      mismatchedPixels,
      mismatchPercent,
    });
  });

  const outputFilename =
    targetScreen === '06-add-address-validation-errors-fr'
      ? 'ADDRESS_VALIDATION_REGION_ANALYSIS.json'
      : 'ADD_ADDRESS_REGION_ANALYSIS.json';
  const outputPath = path.join(__dirname, '../../docs/phase-5d', outputFilename);
  fs.mkdirSync(path.dirname(outputPath), { recursive: true });
  fs.writeFileSync(outputPath, JSON.stringify(results, null, 2));

  console.log('Regional analysis complete for', targetScreen, 'Saved to:', outputPath);
  console.log(JSON.stringify(results.regionalBreakdown, null, 2));
})();
