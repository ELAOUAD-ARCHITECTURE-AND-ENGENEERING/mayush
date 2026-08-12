const fs = require('fs');
const path = require('path');
const typescript = require('typescript');
const sharedReact = require('react');

const modules = new Map();
const values = new Map();

// Hook shimming for node rendering without Fiber loop
const originalUseState = sharedReact.useState;
const originalUseRef = sharedReact.useRef;
const originalUseContext = sharedReact.useContext;
const originalUseEffect = sharedReact.useEffect;
const originalUseCallback = sharedReact.useCallback;
const originalUseMemo = sharedReact.useMemo;

function enableHookShim() {
  sharedReact.useState = (init) => [typeof init === 'function' ? init() : init, () => {}];
  sharedReact.useRef = (init) => ({ current: init });
  sharedReact.useContext = (ctx) => (ctx && (ctx._currentValue ?? ctx._currentValue2)) || { language: 'fr', isRTL: false, theme: {} };
  sharedReact.useEffect = () => {};
  sharedReact.useCallback = (fn) => fn;
  sharedReact.useMemo = (fn) => fn();
}

enableHookShim();

const asyncStorageMock = {
  getItem: async (k) => values.get(k) ?? null,
  setItem: async (k, v) => { values.set(k, v); },
  removeItem: async (k) => { values.delete(k); },
};

function load(filePath) {
  const resolved = path.extname(filePath) ? filePath : (fs.existsSync(`${filePath}.tsx`) ? `${filePath}.tsx` : `${filePath}.ts`);
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
    if (request === 'react') {
      return sharedReact;
    }
    if (request === '@react-native-async-storage/async-storage') {
      return { __esModule: true, default: asyncStorageMock };
    }
    if (request === 'react-native') {
      const makeComp = (name) => {
        const Component = (props) => sharedReact.createElement(name, props, props ? props.children : null);
        Component.displayName = name;
        return Component;
      };
      return {
        __esModule: true,
        View: makeComp('View'),
        Text: makeComp('Text'),
        TouchableOpacity: makeComp('TouchableOpacity'),
        Image: makeComp('Image'),
        ScrollView: makeComp('ScrollView'),
        TextInput: makeComp('TextInput'),
        Modal: makeComp('Modal'),
        ActivityIndicator: makeComp('ActivityIndicator'),
        Switch: makeComp('Switch'),
        Pressable: makeComp('Pressable'),
        KeyboardAvoidingView: makeComp('KeyboardAvoidingView'),
        StyleSheet: {
          create: (s) => s,
          flatten: (s) => (Array.isArray(s) ? Object.assign({}, ...s) : s),
        },
        Platform: {
          OS: 'ios',
          select: (obj) => (obj ? (obj.ios ?? obj.default ?? obj.web ?? Object.values(obj)[0]) : undefined),
        },
        Dimensions: { get: () => ({ width: 375, height: 812 }) },
        useWindowDimensions: () => ({ width: 375, height: 812 }),
      };
    }
    if (request.startsWith('@expo/vector-icons')) {
      const Icon = (props) => sharedReact.createElement('Icon', props);
      return { __esModule: true, Ionicons: Icon, MaterialIcons: Icon, Feather: Icon, FontAwesome: Icon, default: Icon };
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

  const testModule = load(path.join(__dirname, '../tests/RenderedComponentBehaviorTest.ts'));
  await testModule.runRenderedComponentBehaviorTests(assert);
  console.log(`RENDERED COMPONENT SUMMARY: ${passes} PASSED, ${failures} FAILED`);
  if (failures || passes < 8) process.exit(1);
})().catch((error) => {
  console.error('[CRITICAL RENDERED TEST FAILURE]', error);
  process.exit(1);
});
