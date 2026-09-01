const fs = require('fs');
const path = require('path');
const typescript = require('typescript');

const modules = new Map();
const asyncStorageMock = { getItem: async () => null, setItem: async () => undefined, removeItem: async () => undefined };

function load(filePath) {
  const resolved = path.extname(filePath) ? filePath : `${filePath}.ts`;
  if (modules.has(resolved)) return modules.get(resolved).exports;
  const source = fs.readFileSync(resolved, 'utf8');
  const output = typescript.transpileModule(source, {
    compilerOptions: { module: typescript.ModuleKind.CommonJS, target: typescript.ScriptTarget.ES2022 },
  }).outputText;
  const module = { exports: {} };
  modules.set(resolved, module);
  const localRequire = (request) => {
    if (request === '@react-native-async-storage/async-storage') return { __esModule: true, default: asyncStorageMock };
    if (!request.startsWith('.')) return require(request);
    return load(path.resolve(path.dirname(resolved), request));
  };
  new Function('exports', 'require', 'module', '__dirname', output)(module.exports, localRequire, module, path.dirname(resolved));
  return module.exports;
}

(async () => {
  let passes = 0;
  let failures = 0;
  const assert = (condition, message) => {
    if (condition) { passes += 1; console.log(`[PASS] ${message}`); }
    else { failures += 1; console.error(`[FAIL] ${message}`); }
  };
  const suite = load(path.join(__dirname, '../tests/Step8DReturnsRefundsTrackingBehaviorTest.ts'));
  await suite.runStep8DReturnsRefundsTrackingBehaviorTests(assert);
  console.log(`STEP 8D BEHAVIOR SUMMARY: ${passes} PASSED, ${failures} FAILED`);
  if (failures || passes !== 24) process.exit(1);
})().catch((error) => {
  console.error('[CRITICAL FAILURE]', error);
  process.exit(1);
});
