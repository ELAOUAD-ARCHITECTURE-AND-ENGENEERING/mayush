const { getNodeDetails } = require('./figma-client');
const NODES = ['309:781','309:782','309:783','309:784','309:785','309:786'];
async function run() {
  for (const id of NODES) {
    try {
      const d = await getNodeDetails(id);
      const doc = d?.data?.nodes?.[id]?.document;
      console.log(`\n=== ${id} === name: ${doc?.name || 'N/A'} | transition: ${doc?.transitionNodeID || 'NONE'}`);
      console.log(`size: ${doc?.absoluteBoundingBox?.width}x${doc?.absoluteBoundingBox?.height}`);
      console.log(`fills: ${JSON.stringify(doc?.fills?.map(f=>f.type))}`);
    } catch(e) { console.error(id, e.message); }
  }
}
run();
