const { getNodeDetails } = require('./figma-client');

const NODES = ['309:802', '309:803', '309:804'];

async function run() {
  for (const id of NODES) {
    try {
      const d = await getNodeDetails(id);
      const node = d?.data?.nodes?.[id]?.document;
      console.log(`\n=================== ${id} (${node?.name}) ===================`);
      if (!node) continue;

      const texts = [];
      const transitions = [];

      function traverse(n) {
        if (!n) return;
        if (n.type === 'TEXT' && n.characters) {
          texts.push(n.characters);
        }
        if (n.transitionNodeID) {
          transitions.push({ fromName: n.name, toNodeID: n.transitionNodeID });
        }
        if (n.children) {
          n.children.forEach(traverse);
        }
      }

      traverse(node);
      console.log('TEXTS:', JSON.stringify(texts, null, 2));
      console.log('TRANSITIONS:', JSON.stringify(transitions, null, 2));
    } catch (e) {
      console.error(id, e.message);
    }
  }
}

run();
