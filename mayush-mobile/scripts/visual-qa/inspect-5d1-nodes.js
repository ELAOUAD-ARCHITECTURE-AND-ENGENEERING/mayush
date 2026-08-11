/**
 * Deep inspection of Figma nodes for Step 5D.1 — Marketing Preferences & Notification Settings
 */
const { getNodeDetails } = require('./figma-client');

const STEP_5D1_NODES = [
  '309:772', // 08-marketing-preferences-cart-reminders-fr
  '309:773', // 08-marketing-preferences-detailed-fr
  '309:774', // 08-marketing-preferences-toggles-fr
  '309:775', // 08-notification-management-channels-fr
  '309:776', // 08-notification-settings-toggles-fr
];

async function inspectNodes() {
  console.log('Fetching details for Step 5D.1 Figma nodes...');
  for (const nodeId of STEP_5D1_NODES) {
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
