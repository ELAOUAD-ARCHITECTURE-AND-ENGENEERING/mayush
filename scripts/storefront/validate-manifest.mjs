import { access, readdir, readFile, stat } from 'node:fs/promises';
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
    throw new Error(`Invalid storefront asset manifest: ${message}`);
}

function resolvePublicAsset(path) {
    if (typeof path !== 'string' || !path.startsWith('build/storefront/') || path.includes('..')) {
        fail(`invalid asset path: ${String(path)}`);
    }

    const assetPath = resolve(publicDirectory, path);
    if (!assetPath.startsWith(`${publicDirectory}${sep}`)) {
        fail(`asset escapes the public directory: ${path}`);
    }

    return assetPath;
}

async function assertNonEmptyFile(path, description) {
    try {
        await access(path);
        if ((await stat(path)).size === 0) {
            fail(`${description} is empty`);
        }
    } catch (error) {
        if (error instanceof Error && error.message.startsWith('Invalid storefront asset manifest:')) {
            throw error;
        }

        fail(`${description} is missing`);
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
    fail(`expected entries ${expectedEntries.join(', ')}, received ${manifestEntries.join(', ') || '(none)'}`);
}

const referencedAssets = [];
for (const entry of expectedEntries) {
    const assetPath = resolvePublicAsset(manifest[entry]);
    if (!assetPath.endsWith('.js')) {
        fail(`${entry} does not reference a JavaScript bundle`);
    }

    await assertNonEmptyFile(assetPath, `${entry} bundle`);
    await assertNonEmptyFile(`${assetPath}.map`, `${entry} source map`);
    referencedAssets.push(assetPath);
}

const generatedJavaScript = (await readdir(storefrontDirectory))
    .filter((file) => file.endsWith('.js'))
    .map((file) => resolve(storefrontDirectory, file))
    .sort();

const expectedJavaScript = [...referencedAssets].sort();
if (JSON.stringify(generatedJavaScript) !== JSON.stringify(expectedJavaScript)) {
    fail('generated JavaScript bundles do not exactly match manifest.json');
}

console.log(`Validated ${expectedEntries.length} storefront bundles and source maps.`);
