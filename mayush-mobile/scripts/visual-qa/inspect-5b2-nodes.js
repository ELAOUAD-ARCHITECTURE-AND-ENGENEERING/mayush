/**
 * Inspect live Figma nodes for Step 5B.2 — Account Addresses Management
 */
const { getNodeDetails } = require('./figma-client');

const ADDRESS_NODES = [
  '309:762', // 08-my-addresses-list-labels-fr
  '309:763', // 08-my-addresses-list-v2-fr
  '309:764', // 08-add-address-form-v2-fr
  '309:765', // 08-add-address-simple-form-fr
  '309:766', // 08-edit-address-form-fr
  '309:767', // 08-delete-address-confirmation-fr
];

function extractChildren(node, depth = 0) {
  const indent = '  '.repeat(depth);
  const parts = [];
  if (!node) return parts;
  const line = `${indent}[${node.type}] "${node.name}"${node.characters ? ` → "${node.characters}"` : ''}${node.visible === false ? ' (HIDDEN)' : ''}`;
  parts.push(line);
  if (node.children && depth < 4) {
    for (const child of node.children) {
      parts.push(...extractChildren(child, depth + 1));
    }
  }
  return parts;
}

function extractPrototypeConnections(node, results = []) {
  if (!node) return results;
  if (node.transitionNodeID) {
    results.push({
      source: node.name,
      trigger: node.transitionTrigger || 'ON_CLICK',
      destination: node.transitionNodeID,
      action: node.transitionType || 'NAVIGATE',
      easing: node.transitionEasing || '',
      duration: node.transitionDuration || 0,
    });
  }
  // Also check interactions array
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
              navigation: action.navigationType || '',
              transition: action.transition || {},
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
    console.log('=== Fetching Figma nodes for Step 5B.2 ===\n');
    const result = await getNodeDetails(ADDRESS_NODES);

    if (result.status !== 200) {
      console.error('Figma API error:', result.status, JSON.stringify(result.data).substring(0, 500));
      process.exit(1);
    }

    const nodes = result.data.nodes || {};

    for (const nodeId of ADDRESS_NODES) {
      const nodeKey = nodeId.replace(':', '-');
      const entry = nodes[nodeId] || nodes[nodeKey];

      console.log(`\n${'='.repeat(80)}`);
      console.log(`NODE: ${nodeId}`);

      if (!entry || !entry.document) {
        console.log('  ⚠ Node not found in response');
        continue;
      }

      const doc = entry.document;
      console.log(`NAME: ${doc.name}`);
      console.log(`TYPE: ${doc.type}`);
      console.log(`SIZE: ${doc.absoluteBoundingBox?.width}x${doc.absoluteBoundingBox?.height}`);

      // Extract visible text children (top 4 levels)
      console.log('\nVISIBLE STRUCTURE (4 levels):');
      const structure = extractChildren(doc, 0);
      for (const line of structure.slice(0, 80)) {
        console.log(line);
      }
      if (structure.length > 80) {
        console.log(`  ... and ${structure.length - 80} more children`);
      }

      // Extract prototype connections
      const connections = extractPrototypeConnections(doc);
      if (connections.length > 0) {
        console.log('\nPROTOTYPE CONNECTIONS:');
        for (const conn of connections) {
          console.log(`  ${conn.source} → ${conn.destination} (${conn.trigger}, ${conn.action || conn.navigation || ''})`);
        }
      } else {
        console.log('\nPROTOTYPE CONNECTIONS: None found at this level');
      }
    }

    console.log('\n\n=== DONE ===');
  } catch (err) {
    console.error('Error:', err);
    process.exit(1);
  }
})();
