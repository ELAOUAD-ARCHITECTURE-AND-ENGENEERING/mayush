const { getNodeDetails } = require('./figma-client');

const TARGET_NODES = [
  '309:787', // 08-account-guest-welcome-login-fr
  '309:789', // 09-settings-menu-full-list-fr
  '309:790', // About app/version
  '309:791', // About Mayush
  '309:792', // Accessibility
  '309:793', // App permissions
  '309:794', // Data usage
  '309:795', // Storage/cache
  '309:797', // Language
  '309:798', // Notifications
  '309:799', // Marketing
  '309:800', // Silent hours
  '309:801', // Offline mode
  '309:802', // Legal
  '309:803', // Privacy/data
  '309:805', // Help Center
];

async function run() {
  for (const id of TARGET_NODES) {
    try {
      const d = await getNodeDetails(id);
      const doc = d?.data?.nodes?.[id]?.document;
      console.log(`\n=== ${id} === name: ${doc?.name || 'N/A'} | transition: ${doc?.transitionNodeID || 'NONE'}`);
      console.log(`type: ${doc?.type} | bounds: ${doc?.absoluteBoundingBox?.width}x${doc?.absoluteBoundingBox?.height}`);

      // Recursively collect text or node children names if available
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
          console.log(`Texts found (${textNodes.length}):`, textNodes.slice(0, 20).join(' | '));
        }
      }
    } catch (e) {
      console.error(id, e.message);
    }
  }
}

run();
