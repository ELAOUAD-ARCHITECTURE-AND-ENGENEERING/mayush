const { getNodeDetails } = require('./figma-client');

const NODES = ['309:794', '309:795', '309:796'];

async function run() {
  for (const id of NODES) {
    try {
      const d = await getNodeDetails(id);
      const doc = d?.data?.nodes?.[id]?.document;
      console.log(`\n=== ${id} === name: ${doc?.name || 'N/A'} | transition: ${doc?.transitionNodeID || 'NONE'}`);
      console.log(`bounds: ${doc?.absoluteBoundingBox?.width}x${doc?.absoluteBoundingBox?.height}`);

      if (doc?.children) {
        const textNodes = [];
        function findText(node) {
          if (node.type === 'TEXT' && node.characters) {
            textNodes.push(node.characters);
          }
          if (node.children) {
            node.children.forEach(findText);
          }
        }
        doc.children.forEach(findText);
        if (textNodes.length > 0) {
          console.log(`Texts (${textNodes.length}):`, JSON.stringify(textNodes, null, 2));
        }
      }
    } catch (e) {
      console.error(id, e.message);
    }
  }
}

run();
