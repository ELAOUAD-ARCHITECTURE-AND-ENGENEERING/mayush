import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue2';
import react from '@vitejs/plugin-react';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/js/dashboard.jsx',
                'resources/js/analytics-tracker.js',
            ],
            refresh: true,
        }),
        vue(),
        react(),
    ],
    ssr: {
        noExternal: ['react-simple-maps', 'd3-geo', 'topojson-client', 'd3-array', 'd3-interpolate', 'd3-scale'],
    },
    resolve: {
        alias: {
            'vue': 'vue/dist/vue.esm.js',
        },
    },
});
