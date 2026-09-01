const fs = require('fs');
const path = require('path');
const typescript = require('typescript');

const modules = new Map();
const values = new Map();

const asyncStorageMock = {
  getItem: async (k) => values.get(k) ?? null,
  setItem: async (k, v) => { values.set(k, v); },
  removeItem: async (k) => { values.delete(k); },
};

function resolvePath(filePath) {
  if (fs.existsSync(filePath) && fs.statSync(filePath).isFile()) return filePath;
  if (fs.existsSync(`${filePath}.tsx`)) return `${filePath}.tsx`;
  if (fs.existsSync(`${filePath}.ts`)) return `${filePath}.ts`;
  if (fs.existsSync(`${filePath}/index.tsx`)) return `${filePath}/index.tsx`;
  if (fs.existsSync(`${filePath}/index.ts`)) return `${filePath}/index.ts`;
  return filePath;
}

function load(filePath) {
  const resolved = resolvePath(filePath);
  if (modules.has(resolved)) return modules.get(resolved).exports;
  const source = fs.readFileSync(resolved, 'utf8');
  const output = typescript.transpileModule(source, {
    compilerOptions: {
      module: typescript.ModuleKind.CommonJS,
      target: typescript.ScriptTarget.ES2022,
      jsx: typescript.JsxEmit.ReactJSX,
    },
  }).outputText;
  const module = { exports: {} };
  modules.set(resolved, module);
  const localRequire = (request) => {
    if (request === '@react-native-async-storage/async-storage') {
      return { __esModule: true, default: asyncStorageMock };
    }
    if (request === 'react-native') {
      return {
        __esModule: true,
        StyleSheet: { create: (s) => s, flatten: (s) => (Array.isArray(s) ? Object.assign({}, ...s) : s) },
        Platform: { OS: 'ios', select: (obj) => obj?.ios || obj?.default },
      };
    }
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
    if (condition) {
      passes += 1;
      console.log(`[PASS] ${message}`);
    } else {
      failures += 1;
      console.error(`[FAIL] ${message}`);
    }
  };

  const testModule = load(path.join(__dirname, '../tests/Step9B2ArchitectureBehaviorTest.tsx'));
  await testModule.runStep9B2ArchitectureBehaviorTests(assert);

  console.log(`STEP 9B.2 BEHAVIOR SUMMARY: ${passes} PASSED, ${failures} FAILED`);
  if (failures > 0 || passes < 26) {
    process.exit(1);
  }
})().catch((err) => {
  console.error('[CRITICAL STEP 9B.2 FAILURE]', err);
  process.exit(1);
});
