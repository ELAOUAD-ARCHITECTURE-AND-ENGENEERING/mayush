import { build } from 'esbuild';
import { mkdir, rm, writeFile } from 'node:fs/promises';
import { dirname, relative } from 'node:path';

const outdir = 'public/build/storefront';
const entryPoints = {
    core: 'resources/js/storefront/core.js',
    home: 'resources/js/storefront/home.js',
    listing: 'resources/js/storefront/listing.js',
    product: 'resources/js/storefront/product.js',
    cart: 'resources/js/storefront/cart.js',
    checkout: 'resources/js/storefront/checkout.js',
    carousel: 'resources/js/storefront/carousel.js',
};

await rm(outdir, { recursive: true, force: true });
await mkdir(outdir, { recursive: true });

const result = await build({
    entryPoints,
    outdir,
    bundle: true,
    minify: true,
    sourcemap: true,
    metafile: true,
    entryNames: '[name]-[hash]',
    format: 'esm',
    target: ['es2020'],
});

const manifest = {};
for (const [output, metadata] of Object.entries(result.metafile.outputs)) {
    if (!metadata.entryPoint) continue;
    manifest[`${relative('resources/js/storefront', metadata.entryPoint).replace(/\\/g, '/')}`] = relative('public', output).replace(/\\/g, '/');
}

await writeFile(`${outdir}/manifest.json`, `${JSON.stringify(manifest, null, 2)}\n`);
