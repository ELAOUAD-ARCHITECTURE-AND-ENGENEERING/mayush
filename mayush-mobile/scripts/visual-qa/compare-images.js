const fs = require('fs');
const path = require('path');
const { PNG } = require('pngjs');

async function compareImages(sourcePath, appPath, outputDir, prefix = 'before') {
  if (!fs.existsSync(sourcePath) || !fs.existsSync(appPath)) {
    return { error: 'Source or App PNG does not exist' };
  }

  const { default: pixelmatch } = await import('pixelmatch');

  const img1 = PNG.sync.read(fs.readFileSync(sourcePath));
  const img2 = PNG.sync.read(fs.readFileSync(appPath));

  const width = Math.max(img1.width, img2.width);
  const height = Math.max(img1.height, img2.height);

  // 1. Side-by-side PNG
  const sideBySide = new PNG({ width: width * 2, height });
  PNG.bitblt(img1, sideBySide, 0, 0, img1.width, img1.height, 0, 0);
  PNG.bitblt(img2, sideBySide, 0, 0, img2.width, img2.height, width, 0);
  const sideBySidePath = path.join(outputDir, `side-by-side-${prefix}.png`);
  fs.writeFileSync(sideBySidePath, PNG.sync.write(sideBySide));

  // 2. 50% Opacity Overlay PNG
  const overlay = new PNG({ width, height });
  for (let y = 0; y < height; y++) {
    for (let x = 0; x < width; x++) {
      const idx = (width * y + x) << 2;
      const idx1 = (img1.width * y + x) << 2;
      const idx2 = (img2.width * y + x) << 2;

      const r1 = x < img1.width && y < img1.height ? img1.data[idx1] : 255;
      const g1 = x < img1.width && y < img1.height ? img1.data[idx1 + 1] : 255;
      const b1 = x < img1.width && y < img1.height ? img1.data[idx1 + 2] : 255;

      const r2 = x < img2.width && y < img2.height ? img2.data[idx2] : 255;
      const g2 = x < img2.width && y < img2.height ? img2.data[idx2 + 1] : 255;
      const b2 = x < img2.width && y < img2.height ? img2.data[idx2 + 2] : 255;

      overlay.data[idx] = Math.round(r1 * 0.5 + r2 * 0.5);
      overlay.data[idx + 1] = Math.round(g1 * 0.5 + g2 * 0.5);
      overlay.data[idx + 2] = Math.round(b1 * 0.5 + b2 * 0.5);
      overlay.data[idx + 3] = 255;
    }
  }
  const overlayPath = path.join(outputDir, `overlay-${prefix}-50.png`);
  fs.writeFileSync(overlayPath, PNG.sync.write(overlay));

  // 3. Pixel Difference PNG
  const diff = new PNG({ width, height });
  const mismatchedPixels = pixelmatch(
    img1.data,
    img2.data,
    diff.data,
    width,
    height,
    { threshold: 0.1, includeAA: true }
  );

  const diffPath = path.join(outputDir, `pixel-diff-${prefix}.png`);
  fs.writeFileSync(diffPath, PNG.sync.write(diff));

  const totalPixels = width * height;
  const mismatchPercentage = parseFloat(((mismatchedPixels / totalPixels) * 100).toFixed(2));

  return {
    width,
    height,
    mismatchedPixels,
    totalPixels,
    mismatchPercentage,
    sideBySidePath: path.relative(process.cwd(), sideBySidePath),
    overlayPath: path.relative(process.cwd(), overlayPath),
    diffPath: path.relative(process.cwd(), diffPath),
  };
}

module.exports = { compareImages };
