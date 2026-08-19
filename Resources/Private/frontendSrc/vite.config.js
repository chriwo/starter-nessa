import { readFileSync } from 'node:fs';
import { resolve } from 'node:path';

import inject from '@rollup/plugin-inject';
import { fileURLToPath } from 'url';
import { defineConfig } from 'vite';
import { viteStaticCopy } from 'vite-plugin-static-copy';

import { svgSpritePlugin } from './plugins/SvgSpritePlugin/svg-sprite-plugin.js';

const __dirname = fileURLToPath(new URL('.', import.meta.url));

const dist = resolve(__dirname, '../../Public/Frontend/');
const srcAssets = resolve(__dirname, 'src');

// Base icon manifest — customer projects import this file to extend the sprite.
// Add icon names here to include them in the built sprite / backend selection.
const spriteManifest = JSON.parse(
    readFileSync(resolve(srcAssets, 'Icons/sprite-manifest.json'), 'utf8'),
);

export default defineConfig(({ mode }) => ({
    base: './',
    build: {
        outDir: dist,
        emptyOutDir: true,
        sourcemap: mode === 'development',
        target: 'es2015',
        rollupOptions: {
            input: {
                app: resolve(srcAssets, 'JavaScript/app.js'),
                rte: resolve(srcAssets, 'JavaScript/rte.js'),
            },
            output: {
                entryFileNames: '[name].min.js',
                chunkFileNames: '[name].min.js',
                assetFileNames: ({ name }) => {
                    if (name?.endsWith('.css')) return '[name].min.css';
                    if (/\.(woff2?|eot|ttf|otf)$/i.test(name ?? '')) return 'Fonts/[name][extname]';
                    return 'Images/[name][extname]';
                },
            },
        },
    },

    plugins: [
        // Provides $ and jQuery as globals in source modules (replaces webpack ProvidePlugin)
        // node_modules excluded to avoid mixing ESM/CJS modules
        inject({
            $: 'jquery',
            jQuery: 'jquery',
            exclude: [/node_modules/, /\.scss$/],
        }),
        svgSpritePlugin({
            manifest: spriteManifest,
            customIconsDir: resolve(srcAssets, 'Icons/custom'),
        }),
        viteStaticCopy({
            // Copy the directory as one target, not 'Images/*'. The glob turns
            // every file into its own copy target, and the plugin runs those
            // concurrently — each one creating the shared 'Images' directory.
            // On the DDEV bind mount those parallel mkdir calls race and the
            // build fails with a random ENOENT roughly every other run.
            targets: [
                {
                    src: resolve(srcAssets, 'Images'),
                    dest: '.',
                },
            ],
        }),
    ],

    css: {
        preprocessorOptions: {
            scss: {
                // node_modules in Sass search path so 'bootstrap/scss/...' works without ~
                loadPaths: [resolve(__dirname, 'node_modules')],
            },
        },
    },
}));
