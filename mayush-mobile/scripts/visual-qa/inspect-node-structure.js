const { getNodeDetails } = require('./figma-client');

const NODES = ['309:802', '309:803', '309:804'];

async function run() {
  for (const id of NODES) {
    try {
      const d = await getNodeDetails(id);
      const node = d?.data?.nodes?.[id]?.document;
      console.log(`\n=================== ${id} (${node?.name}) ===================`);
      console.log('Keys:', Object.keys(node || {}));
      console.log('Type:', node?.type);
      console.log('Children count:', node?.children?.length);
      if (node?.children) {
        console.log('Children sample:', node.children.slice(0, 5).map(c => ({ id: c.id, name: c.name, type: c.type })));
      }
    } catch (e) {
      console.error(id, e.message);
    }
  }
}

run();
