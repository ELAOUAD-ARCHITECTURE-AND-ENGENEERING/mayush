/**
 * Sync Live Figma Source Renders & Node Metadata
 * File: Mayush Mobile — Design System & Buyer App (wAdLNmlKanvI0AEPyEbrMs)
 */

const fs = require('fs');
const path = require('path');
const { getNodeDetails, getNodeImages, downloadFile } = require('./figma-client');
const config = require('./visual-qa-config.json');

(async () => {
  console.log('==================================================');
  console.log('SYNCING LIVE FIGMA REST API DATA FOR 17 FRAMES');
  console.log('==================================================');

  const nodeMap = {};
  config.screens.forEach((s) => {
    nodeMap[s.figmaNodeId] = s;
  });

  const nodeIds = Object.keys(nodeMap);
  console.log(`[Figma API] Requesting image renders for ${nodeIds.length} nodes at scale=1...`);

  const imgRes = await getNodeImages(nodeIds, 1, 'png');
  if (imgRes.status !== 200 || !imgRes.data.images) {
    console.error('[Figma API Error] Failed to retrieve node images:', imgRes);
    process.exit(1);
  }

  const imageUrls = imgRes.data.images;
  let downloadedCount = 0;

  for (const nodeId of nodeIds) {
    const screenInfo = nodeMap[nodeId];
    const url = imageUrls[nodeId];
    if (!url) {
      console.warn(`[Warning] No image URL returned for node ${nodeId} (${screenInfo.name})`);
      continue;
    }

    const destDir = path.join(
      __dirname,
      '../../design-reference/mayush-mobile-design/validation/phase-5b-pixel-parity',
      screenInfo.name
    );
    fs.mkdirSync(destDir, { recursive: true });

    const destPath = path.join(destDir, 'figma-source-live.png');
    console.log(`[Downloading] ${screenInfo.name} (${nodeId}) -> ${destPath}`);
    await downloadFile(url, destPath);
    downloadedCount++;
  }

  console.log('==================================================');
  console.log(`SYNC COMPLETE: Successfully downloaded ${downloadedCount}/${nodeIds.length} live Figma renders!`);
  console.log('==================================================');
})();
