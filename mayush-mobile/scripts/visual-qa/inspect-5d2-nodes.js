/**
 * Deep inspection of Figma nodes for Step 5D.2 — Notification Details & Quiet Hours
 */
const { getNodeDetails } = require('./figma-client');

const STEP_5D2_NODES = [
  '309:777', // 08-notification-detail-order-preparation-fr
  '309:778', // 08-notification-detail-order-shipped-fr
  '309:779', // 08-silent-hours-day-selection-fr
  '309:780', // 08-silent-hours-do-not-disturb-fr
];

async function inspectNodes() {
  console.log('Fetching details for Step 5D.2 Figma nodes...');
  for (const nodeId of STEP_5D2_NODES) {
    try {
      const details = await getNodeDetails(nodeId);
      console.log(`\n=== NODE: ${nodeId} (${details.name || 'Unknown'}) ===`);
      console.log(JSON.stringify(details, null, 2));
    } catch (err) {
      console.error(`Error fetching node ${nodeId}:`, err.message);
    }
  }
}

inspectNodes();
