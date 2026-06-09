import { existsSync, readFileSync } from 'node:fs';
import { resolve } from 'node:path';

import inject from '@rollup/plugin-inject';
import { fileURLToPath } from 'url';
import { defineConfig } from 'vite';
import { viteStaticCopy } from 'vite-plugin-static-copy';

const __dirname = fileURLToPath(new URL('.', import.meta.url));

const dist = resolve(__dirname, '../../Public/Frontend/');
const srcAssets = resolve(__dirname, 'src');

/**
 * Reads sprite-manifest.json and emits icons.svg + individual Icons/*.svg
 * directly into the Vite output directory via Rollup's emitFile API.
 * Add icon names to src/Icons/sprite-manifest.json to include them in the sprite.
 */
function svgSpritePlugin() {
    return {
        name: 'svg-sprite',
        generateBundle() {
            const manifest = JSON.parse(
                readFileSync(resolve(srcAssets, 'Icons/sprite-manifest.json'), 'utf8'),
            );
            const biNames = manifest['bootstrap-icons'] ?? [];
            const customNames = manifest['custom'] ?? [];
            const biDir = resolve(__dirname, 'node_modules/bootstrap-icons/icons');
            const customDir = resolve(srcAssets, 'Icons/custom');

            const extractViewBox = (svg) => {
                const m = svg.match(/viewBox="([^"]+)"/i);
                return m ? m[1] : '0 0 16 16';
            };

            const extractInner = (svg, filePath) => {
                const cleaned = svg
                    .replace(/<\?xml[^>]*\?>/g, '')
                    .replace(/<!--[\s\S]*?-->/g, '')
                    .replace(/<title>[^<]*<\/title>/gi, '')
                    .replace(/<desc>[^<]*<\/desc>/gi, '');
                const openTags = cleaned.match(/<svg\b[^>]*>/gi) ?? [];
                const closeTags = cleaned.match(/<\/svg>/gi) ?? [];
                if (openTags.length !== 1 || closeTags.length !== 1) {
                    this.error(
                        `SVG sprite: ${filePath} contains ${openTags.length} <svg> open and ${closeTags.length} </svg> close tags — exactly one of each is required`,
                    );
                }
                return cleaned
                    .replace(/<svg[^>]*>/i, '')
                    .replace(/<\/svg>/i, '')
                    .trim();
            };

            const symbols = [];
            let errors = 0;

            const processIcon = (filePath, id) => {
                if (!existsSync(filePath)) {
                    this.warn(`SVG sprite: icon not found — ${filePath}`);
                    errors++;
                    return;
                }
                const svg = readFileSync(filePath, 'utf8');
                const viewBox = extractViewBox(svg);
                const inner = extractInner(svg, filePath);

                symbols.push(`<symbol id="${id}" viewBox="${viewBox}">\n${inner}\n</symbol>`);
                this.emitFile({
                    type: 'asset',
                    fileName: `Icons/${id}.svg`,
                    source: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="${viewBox}">\n${inner}\n</svg>`,
                });
            };

            for (const name of biNames) {
                processIcon(resolve(biDir, `${name}.svg`), `bi-${name}`);
            }
            for (const name of customNames) {
                processIcon(resolve(customDir, `${name}.svg`), `custom-${name}`);
            }

            this.emitFile({
                type: 'asset',
                fileName: 'icons.svg',
                source: [
                    '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"',
                    '     aria-hidden="true" style="display:none">',
                    ...symbols,
                    '</svg>',
                ].join('\n'),
            });

            const total = biNames.length + customNames.length - errors;
            console.log(
                `SVG sprite: ${total} icons (${biNames.length - errors} Bootstrap Icons + ${customNames.length} custom) → icons.svg`,
            );

            if (errors > 0) {
                this.error(`SVG sprite: ${errors} icon(s) missing — aborting build`);
            }
        },
    };
}

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
        svgSpritePlugin(),
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
                // node_modules in Sass search path so 'bootstrap/scss/...' works without ~
                loadPaths: [resolve(__dirname, 'node_modules')],
            },
        },
    },
}));
