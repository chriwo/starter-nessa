import inject from '@rollup/plugin-inject';
import { resolve } from 'path';
import { fileURLToPath } from 'url';
import { defineConfig } from 'vite';
import { viteStaticCopy } from 'vite-plugin-static-copy';

const __dirname = fileURLToPath(new URL('.', import.meta.url));

const dist = resolve(__dirname, '../../Public/Frontend/');
const srcAssets = resolve(__dirname, 'src');

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
        // Stellt $ und jQuery in Source-Modulen als globale Variablen bereit (Ersatz für webpack ProvidePlugin)
        // node_modules ausgeschlossen, damit CJS-Pakete nicht zu gemischten ESM/CJS-Modulen werden
        inject({
            $: 'jquery',
            jQuery: 'jquery',
            exclude: [/node_modules/, /\.scss$/],
        }),
        // Kopiert statische Assets, die nicht per Import referenziert werden
        viteStaticCopy({
            targets: [
                {
                    src: resolve(srcAssets, 'Images/*'),
                    dest: 'Images',
                },
            ],
        }),
    ],

    css: {
        preprocessorOptions: {
            scss: {
                // Bootstrap 4 nutzt @import – Deprecation-Warnungen unterdrücken
                quietDeps: true,
                silenceDeprecations: ['import', 'global-builtin'],
                // node_modules im Sass-Suchpfad, damit 'bootstrap/scss/...' ohne ~ funktioniert
                loadPaths: [resolve(__dirname, 'node_modules')],
            },
        },
    },
}));
