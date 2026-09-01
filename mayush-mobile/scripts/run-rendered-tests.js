const fs = require('fs');
const path = require('path');
const typescript = require('typescript');
const sharedReact = require('react');

const modules = new Map();
const values = new Map();

// Save unshimmed React hooks
const realHooks = {
  useState: sharedReact.useState,
  useRef: sharedReact.useRef,
  useContext: sharedReact.useContext,
  useEffect: sharedReact.useEffect,
  useCallback: sharedReact.useCallback,
  useMemo: sharedReact.useMemo,
};

const asyncStorageMock = {
  getItem: async (k) => values.get(k) ?? null,
  setItem: async (k, v) => { values.set(k, v); },
  removeItem: async (k) => { values.delete(k); },
};

// Shimming for structural component harness
function enableHookShim() {
  sharedReact.useState = (init) => [typeof init === 'function' ? init() : init, () => {}];
  sharedReact.useRef = (init) => ({ current: init });
  sharedReact.useContext = (ctx) => (ctx && (ctx._currentValue ?? ctx._currentValue2)) || { language: 'fr', isRTL: false, theme: {} };
  sharedReact.useEffect = () => {};
  sharedReact.useCallback = (fn) => fn;
  sharedReact.useMemo = (fn) => fn();
}

function restoreRealHooks() {
  Object.assign(sharedReact, realHooks);
}

function resolvePath(filePath) {
  if (fs.existsSync(filePath) && fs.statSync(filePath).isFile()) return filePath;
  if (fs.existsSync(`${filePath}.tsx`)) return `${filePath}.tsx`;
  if (fs.existsSync(`${filePath}.ts`)) return `${filePath}.ts`;
  if (fs.existsSync(`${filePath}/index.tsx`)) return `${filePath}/index.tsx`;
  if (fs.existsSync(`${filePath}/index.ts`)) return `${filePath}/index.ts`;
  return filePath;
}

function cleanDomStyle(style) {
  if (!style) return undefined;
  const list = Array.isArray(style) ? style.flat(Infinity) : [style];
  const obj = {};
  for (const item of list) {
    if (item && typeof item === 'object' && !Array.isArray(item)) {
      Object.assign(obj, item);
    }
  }
  const clean = {};
  for (const [k, v] of Object.entries(obj)) {
    if (v !== undefined && v !== null && typeof v !== 'object') {
      clean[k] = v;
    }
  }
  return Object.keys(clean).length > 0 ? clean : undefined;
}

function load(filePath, useRealReact = false) {
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
    if (request === 'react') {
      return sharedReact;
    }
    if (request === '@react-native-async-storage/async-storage') {
      return { __esModule: true, default: asyncStorageMock };
    }
    if (request === 'react-native') {
      const makeComp = (name, tag = 'div') => {
        const Component = (props) => {
          if (!props) return sharedReact.createElement(tag, { className: name });
          const {
            style,
            onPress,
            onClick,
            children,
            numberOfLines,
            activeOpacity,
            underlayColor,
            showsVerticalScrollIndicator,
            showsHorizontalScrollIndicator,
            keyboardType,
            autoCapitalize,
            secureTextEntry,
            contentContainerStyle,
            ...rest
          } = props;
          const flatStyle = cleanDomStyle(style);
          const domProps = {
            ...rest,
            className: name,
            style: flatStyle,
            onClick: onPress || onClick,
          };
          return sharedReact.createElement(tag, domProps, children);
        };
        Component.displayName = name;
        return Component;
      };
      return {
        __esModule: true,
        View: makeComp('View', 'div'),
        Text: makeComp('Text', 'span'),
        TouchableOpacity: makeComp('TouchableOpacity', 'button'),
        Image: makeComp('Image', 'img'),
        ScrollView: makeComp('ScrollView', 'div'),
        TextInput: makeComp('TextInput', 'input'),
        Modal: makeComp('Modal', 'div'),
        ActivityIndicator: makeComp('ActivityIndicator', 'div'),
        Switch: makeComp('Switch', 'input'),
        Pressable: makeComp('Pressable', 'button'),
        KeyboardAvoidingView: makeComp('KeyboardAvoidingView', 'div'),
        StyleSheet: {
          create: (s) => s,
          flatten: (s) => (Array.isArray(s) ? Object.assign({}, ...s.filter(Boolean)) : (s || {})),
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
      const Icon = (props) => sharedReact.createElement('span', { className: 'icon' }, props ? props.name : null);
      return { __esModule: true, Ionicons: Icon, MaterialIcons: Icon, Feather: Icon, FontAwesome: Icon, default: Icon };
    }
    if (!request.startsWith('.')) return require(request);
    return load(path.resolve(path.dirname(resolved), request), useRealReact);
  };
  new Function('exports', 'require', 'module', '__dirname', output)(module.exports, localRequire, module, path.dirname(resolved));
  return module.exports;
}

(async () => {
  let structuralPasses = 0;
  let structuralFailures = 0;
  let realPasses = 0;
  let realFailures = 0;

  const assertStructural = (condition, message) => {
    if (condition) {
      structuralPasses += 1;
      console.log(`[STRUCTURAL HARNESS PASS] ${message}`);
    } else {
      structuralFailures += 1;
      console.log(`[STRUCTURAL HARNESS FAIL] ${message}`);
    }
  };

  const assertReal = (condition, message) => {
    if (condition) {
      realPasses += 1;
      console.log(`[REAL RENDERED PASS] ${message}`);
    } else {
      realFailures += 1;
      console.error(`[REAL RENDERED FAIL] ${message}`);
    }
  };

  // 1. Run Structural Component Harness (shimmed hooks)
  enableHookShim();
  const structuralModule = load(path.join(__dirname, '../tests/RenderedComponentBehaviorTest.tsx'));
  await structuralModule.runRenderedComponentBehaviorTests(assertStructural);

  // 2. Setup happy-dom & React DOM client for Real Component Renderer
  const { Window } = await import('happy-dom');
  const window = new Window();
  global.window = window;
  global.document = window.document;
  global.navigator = window.navigator;
  global.HTMLElement = window.HTMLElement;
  global.IS_REACT_ACT_ENVIRONMENT = true;

  // Restore unshimmed React hooks
  restoreRealHooks();

  // Clear module cache to re-load modules with real React
  modules.clear();

  const ReactDOMClient = require('react-dom/client');
  const container = window.document.createElement('div');
  window.document.body.appendChild(container);

  const realModule = load(path.join(__dirname, '../tests/RealRenderedComponentBehaviorTest.tsx'), true);
  await realModule.runRealRenderedComponentBehaviorTests(assertReal, container, ReactDOMClient);

  console.log(`STRUCTURAL HARNESS SUMMARY: ${structuralPasses} PASSED, ${structuralFailures} FAILED`);
  console.log(`REAL RENDERED SUMMARY: ${realPasses} PASSED, ${realFailures} FAILED`);

  if (structuralFailures > 0 || realFailures > 0 || realPasses < 6) {
    process.exit(1);
  }
})().catch((error) => {
  console.error('[CRITICAL RENDERED TEST FAILURE]', error);
  process.exit(1);
});
