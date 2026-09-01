/**
 * Live Figma REST API Client for Mayush Mobile.
 * Credentials are loaded from the local process environment and must never be
 * committed to source control.
 */

const https = require('https');
const fs = require('fs');

const FIGMA_FILE_KEY = process.env.FIGMA_FILE_KEY || 'wAdLNmlKanvI0AEPyEbrMs';

function getFigmaToken() {
  const token = process.env.FIGMA_ACCESS_TOKEN;
  if (!token) {
    throw new Error('FIGMA_ACCESS_TOKEN is required for live visual-QA scripts. Configure it in the local process environment.');
  }
  return token;
}

function fetchFigmaApi(endpoint) {
  return new Promise((resolve, reject) => {
    const options = {
      hostname: 'api.figma.com',
      path: endpoint,
      headers: {
        'X-Figma-Token': getFigmaToken(),
      },
    };

    https.get(options, (res) => {
      let data = '';
      res.on('data', (chunk) => (data += chunk));
      res.on('end', () => {
        try {
          const parsed = JSON.parse(data);
          resolve({ status: res.statusCode, data: parsed });
        } catch (err) {
          reject(err);
        }
      });
    }).on('error', reject);
  });
}

async function getNodeDetails(nodeIds) {
  const idsParam = Array.isArray(nodeIds) ? nodeIds.join(',') : nodeIds;
  const endpoint = `/v1/files/${FIGMA_FILE_KEY}/nodes?ids=${encodeURIComponent(idsParam)}`;
  return fetchFigmaApi(endpoint);
}

async function getNodeImages(nodeIds, scale = 1, format = 'png') {
  const idsParam = Array.isArray(nodeIds) ? nodeIds.join(',') : nodeIds;
  const endpoint = `/v1/images/${FIGMA_FILE_KEY}?ids=${encodeURIComponent(idsParam)}&scale=${scale}&format=${format}`;
  return fetchFigmaApi(endpoint);
}

async function downloadFile(url, destPath) {
  return new Promise((resolve, reject) => {
    const file = fs.createWriteStream(destPath);
    https.get(url, (response) => {
      response.pipe(file);
      file.on('finish', () => {
        file.close(resolve);
      });
    }).on('error', (err) => {
      fs.unlink(destPath, () => reject(err));
    });
  });
}

module.exports = {
  FIGMA_FILE_KEY,
  getNodeDetails,
  getNodeImages,
  downloadFile,
};

if (require.main === module) {
  (async () => {
    try {
      const result = await getNodeDetails(['309:680', '309:687', '309:690']);
      console.log('Figma Client Test Success! HTTP Status:', result.status);
      console.log('Nodes retrieved:', Object.keys(result.data.nodes || {}));
    } catch (err) {
      console.error('Figma Client Test Error:', err instanceof Error ? err.message : err);
      process.exitCode = 1;
    }
  })();
}
