/**
 * Deep inspection of Figma nodes for Step 5B.2 — get child tree with depth=6
 */
const { getNodeDetails } = require('./figma-client');

const ADDRESS_NODES = [
  '309:762', '309:763', '309:764', '309:765', '309:766', '309:767',
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

function extractInputFields(node, results = []) {
  if (!node) return results;
  // Look for text input hints (frames named *input*, *field*, *text*)
  const nameLower = (node.name || '').toLowerCase();
  if (nameLower.includes('input') || nameLower.includes('field') || nameLower.includes('text-field') || nameLower.includes('textfield')) {
    const texts = extractText(node);
    results.push({ name: node.name, type: node.type, texts });
  }
  if (node.children) {
    for (const child of node.children) {
      extractInputFields(child, results);
    }
  }
  return results;
}

function extractTopStructure(node, depth = 0, maxDepth = 3) {
  const indent = '  '.repeat(depth);
  const parts = [];
  if (!node) return parts;
  const chars = node.characters ? ` → "${node.characters.substring(0, 60)}"` : '';
  const hidden = node.visible === false ? ' (HIDDEN)' : '';
  parts.push(`${indent}[${node.type}] "${node.name}"${chars}${hidden}`);
  if (node.children && depth < maxDepth) {
    for (const child of node.children) {
      parts.push(...extractTopStructure(child, depth + 1, maxDepth));
    }
  }
  return parts;
}

(async () => {
  try {
    console.log('=== Deep Figma Inspection for Step 5B.2 ===\n');

    // Fetch one at a time with depth parameter
    for (const nodeId of ADDRESS_NODES) {
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
      console.log(`SIZE: ${doc.absoluteBoundingBox?.width}x${doc.absoluteBoundingBox?.height}`);

      // All visible text
      const allTexts = extractText(doc);
      console.log(`\nALL VISIBLE TEXT (${allTexts.length} items):`);
      for (const t of allTexts) {
        console.log(`  "${t}"`);
      }

      // Top-level structure
      console.log('\nSTRUCTURE (3 levels):');
      const structure = extractTopStructure(doc, 0, 3);
      for (const line of structure.slice(0, 120)) {
        console.log(line);
      }
      if (structure.length > 120) console.log(`  ... (${structure.length - 120} more)`);

      // Input fields
      const inputs = extractInputFields(doc);
      if (inputs.length > 0) {
        console.log('\nINPUT FIELDS:');
        for (const inp of inputs) {
          console.log(`  ${inp.name}: [${inp.texts.join(', ')}]`);
        }
      }
    }

    console.log('\n\n=== DONE ===');
  } catch (err) {
    console.error('Error:', err);
  }
})();
