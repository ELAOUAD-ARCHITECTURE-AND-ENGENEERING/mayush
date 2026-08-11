const { getNodeDetails } = require('./figma-client');

const NODES = ['309:802', '309:803', '309:804'];

async function run() {
  for (const id of NODES) {
    try {
      const d = await getNodeDetails(id);
      const doc = d?.data?.nodes?.[id]?.document;
      console.log(`\n=================== ${id} (${doc?.name}) ===================`);

      function printTree(node, depth = 0) {
        const indent = '  '.repeat(depth);
        const text = node.characters ? ` -> "${node.characters.replace(/\n/g, ' \\n ')}"` : '';
        const trans = node.transitionNodeID ? ` [Transition to: ${node.transitionNodeID}]` : '';
        console.log(`${indent}- [${node.type}] ${node.name}${text}${trans}`);
        if (node.children) {
          node.children.forEach(c => printTree(c, depth + 1));
        }
      }

      if (doc?.children) {
        doc.children.forEach(c => printTree(c, 1));
      }
    } catch (e) {
      console.error(id, e.message);
    }
  }
}

run();
