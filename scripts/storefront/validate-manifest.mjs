import { lstat, readdir, readFile } from 'node:fs/promises';
import { resolve, sep } from 'node:path';

const publicDirectory = resolve('public');
const storefrontDirectory = resolve(publicDirectory, 'build/storefront');
const manifestPath = resolve(storefrontDirectory, 'manifest.json');
const expectedEntries = [
    'core.js',
    'home.js',
    'listing.js',
    'product.js',
    'cart.js',
    'checkout.js',
    'carousel.js',
    'notifications.js',
];

function fail(message) {
    throw new Error('Invalid storefront asset manifest: ' + message);
}

function resolvePublicAsset(path) {
    if (typeof path !== 'string' || !path.startsWith('build/storefront/') || path.includes('..')) {
        fail('invalid asset path: ' + String(path));
    }

    const assetPath = resolve(publicDirectory, path);
    if (!assetPath.startsWith(publicDirectory + sep)) {
        fail('asset escapes the public directory: ' + path);
    }

    return assetPath;
}

async function assertRegularNonEmptyFile(path, description) {
    let file;

    try {
        file = await lstat(path);
    } catch {
        fail(description + ' is missing');
    }

    if (file.isSymbolicLink() || !file.isFile()) {
        fail(description + ' must be a regular file');
    }

    if (file.size === 0) {
        fail(description + ' is empty');
    }
}

async function assertSourceMap(path, description) {
    await assertRegularNonEmptyFile(path, description);

    let sourceMap;
    try {
        sourceMap = JSON.parse(await readFile(path, 'utf8'));
    } catch {
        fail(description + ' is not valid JSON');
    }

    if (!sourceMap || Array.isArray(sourceMap) || typeof sourceMap !== 'object') {
        fail(description + ' must contain an object');
    }

    if ('sourcesContent' in sourceMap) {
        fail(description + ' must not embed source content');
    }
}

let manifest;
try {
    manifest = JSON.parse(await readFile(manifestPath, 'utf8'));
} catch {
    fail('manifest.json is missing or invalid JSON');
}

if (!manifest || Array.isArray(manifest) || typeof manifest !== 'object') {
    fail('manifest.json must contain an object');
}

const manifestEntries = Object.keys(manifest).sort();
if (JSON.stringify(manifestEntries) !== JSON.stringify([...expectedEntries].sort())) {
    fail('expected entries ' + expectedEntries.join(', ') + ', received ' + (manifestEntries.join(', ') || '(none)'));
}

const referencedAssets = [];
const referencedMaps = [];
for (const entry of expectedEntries) {
    const assetPath = resolvePublicAsset(manifest[entry]);
    if (!assetPath.endsWith('.js')) {
        fail(entry + ' does not reference a JavaScript bundle');
    }

    const sourceMapPath = assetPath + '.map';
    await assertRegularNonEmptyFile(assetPath, entry + ' bundle');
    await assertSourceMap(sourceMapPath, entry + ' source map');
    referencedAssets.push(assetPath);
    referencedMaps.push(sourceMapPath);
}

const generatedFiles = (await readdir(storefrontDirectory))
    .map((file) => resolve(storefrontDirectory, file))
    .sort();

const expectedFiles = [manifestPath, ...referencedAssets, ...referencedMaps].sort();
if (JSON.stringify(generatedFiles) !== JSON.stringify(expectedFiles)) {
    fail('generated files do not exactly match manifest.json');
}

console.log('Validated ' + expectedEntries.length + ' storefront bundles and source maps.');
