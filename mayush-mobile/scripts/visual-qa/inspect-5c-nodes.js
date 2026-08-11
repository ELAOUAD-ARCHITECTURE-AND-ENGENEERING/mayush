/**
 * Deep inspection of Figma nodes for Step 5C — Payment Methods, Language/Region & Logout
 */
const { getNodeDetails } = require('./figma-client');

const STEP_5C_NODES = [
  '309:768', // 08-payment-methods-card-cod-wallet-fr
  '309:769', // 08-language-region-preferences-fr
  '309:770', // 08-language-selection-3-languages-fr
  '309:771', // 08-logout-confirmation-dialog-fr
];

function extractText(node, results = []) {
  if (!node) return results;
  if (node.characters && node.characters.trim()) {
    results.push(node.characters.trim());
  }
  if (node.children) {
    for (const child of node.children) {
      extractText(child, results);
    }
  }
  return results;
}

function extractPrototypeConnections(node, results = []) {
  if (!node) return results;
  if (node.transitionNodeID) {
    results.push({
      source: node.name,
      trigger: node.transitionTrigger || 'ON_CLICK',
      destination: node.transitionNodeID,
      action: node.transitionType || 'NAVIGATE',
    });
  }
  if (node.interactions) {
    for (const interaction of node.interactions) {
      if (interaction.actions) {
        for (const action of interaction.actions) {
          if (action.destinationId) {
            results.push({
              source: node.name,
              trigger: interaction.trigger?.type || 'ON_CLICK',
              destination: action.destinationId,
              action: action.type || 'NAVIGATE',
            });
          }
        }
      }
    }
  }
  if (node.children) {
    for (const child of node.children) {
      extractPrototypeConnections(child, results);
    }
  }
  return results;
}

(async () => {
  try {
    console.log('=== Deep Live Figma Inspection for Step 5C ===\n');

    for (const nodeId of STEP_5C_NODES) {
      const result = await getNodeDetails([nodeId]);

      if (result.status !== 200) {
        console.error(`Figma API error for ${nodeId}:`, result.status);
        continue;
      }

      const nodes = result.data.nodes || {};
      const entry = nodes[nodeId];

      console.log(`\n${'='.repeat(80)}`);
      console.log(`NODE: ${nodeId}`);

      if (!entry || !entry.document) {
        console.log('  ⚠ Node not found');
        continue;
      }

      const doc = entry.document;
      console.log(`NAME: ${doc.name}`);
      console.log(`TYPE: ${doc.type}`);
      console.log(`SIZE: ${doc.absoluteBoundingBox?.width}x${doc.absoluteBoundingBox?.height}`);

      const allTexts = extractText(doc);
      console.log(`\nALL VISIBLE TEXT (${allTexts.length} items):`);
      for (const t of allTexts) {
        console.log(`  "${t}"`);
      }

      const connections = extractPrototypeConnections(doc);
      if (connections.length > 0) {
        console.log('\nPROTOTYPE CONNECTIONS:');
        for (const conn of connections) {
          console.log(`  ${conn.source} → ${conn.destination} (${conn.trigger}, ${conn.action})`);
        }
      } else {
        console.log('\nPROTOTYPE CONNECTIONS: None explicitly declared inside frame root');
      }
    }

    console.log('\n\n=== DONE ===');
  } catch (err) {
    console.error('Error:', err);
  }
})();
