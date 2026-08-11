const { getNodeDetails } = require('./figma-client');

const NODES = ['309:805', '309:806', '309:807', '309:808', '309:809'];

async function run() {
  for (const id of NODES) {
    try {
      const d = await getNodeDetails(id);
      const doc = d?.data?.nodes?.[id]?.document;
      console.log(`\n========================================`);
      console.log(`NODE: ${id} | NAME: ${doc?.name || 'N/A'}`);
      console.log(`BOUNDS: ${doc?.absoluteBoundingBox?.width}x${doc?.absoluteBoundingBox?.height}`);

      const reactions = [];
      const textNodes = [];

      function walk(node) {
        if (node.reactions) {
          node.reactions.forEach(r => {
            reactions.push({
              trigger: r.trigger?.type,
              action: r.action?.type,
              destination: r.action?.destinationId,
              nodeName: node.name
            });
          });
        }
        if (node.type === 'TEXT' && node.characters) {
          textNodes.push({ name: node.name, text: node.characters });
        }
        if (node.children) {
          node.children.forEach(walk);
        }
      }

      if (doc?.children) {
        doc.children.forEach(walk);
      }

      console.log(`\nReactions (${reactions.length}):`, JSON.stringify(reactions, null, 2));
      console.log(`\nText Nodes (${textNodes.length}):`, JSON.stringify(textNodes, null, 2));

    } catch (e) {
      console.error(id, e.message);
    }
  }
}

run();
