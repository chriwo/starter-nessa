import { existsSync, readFileSync } from 'node:fs';
import { createRequire } from 'node:module';
import { dirname, resolve } from 'node:path';

/**
 * Merge multiple sprite manifests into one, de-duplicating icon names per group
 * (e.g. "bootstrap-icons", "custom"). Order is preserved and duplicates are
 * dropped, so importing the starter base manifest and adding your own names
 * never yields the same icon twice in the sprite.
 *
 * @param {...Record<string, string[]>} manifests
 * @returns {Record<string, string[]>}
 */
export function mergeManifests(...manifests) {
    const groups = {};

    for (const manifest of manifests) {
        for (const [group, names] of Object.entries(manifest ?? {})) {
            if (!Array.isArray(names)) {
                continue;
            }
            const set = groups[group] ?? new Set();
            for (const name of names) {
                set.add(name);
            }
            groups[group] = set;
        }
    }

    const merged = {};
    for (const [group, set] of Object.entries(groups)) {
        merged[group] = [...set];
    }

    return merged;
}

/**
 * Resolve the Bootstrap Icons source directory. When no explicit directory is
 * given, resolve the `bootstrap-icons` package from the *consuming* project's
 * node_modules so the plugin works from any project, not just starter-nessa.
 *
 * @param {string|undefined} bootstrapIconsDir
 * @param {(message: string) => never} fail
 * @returns {string}
 */
function resolveBootstrapIconsDir(bootstrapIconsDir, fail) {
    if (bootstrapIconsDir) {
        return bootstrapIconsDir;
    }

    try {
        const require = createRequire(import.meta.url);
        const packageJson = require.resolve('bootstrap-icons/package.json');
        return resolve(dirname(packageJson), 'icons');
    } catch {
        return fail(
            'SVG sprite: could not resolve the "bootstrap-icons" package. Install it as a ' +
                'dependency, or pass an explicit "bootstrapIconsDir" option to svgSpritePlugin().',
        );
    }
}

/**
 * Vite/Rollup plugin that turns a sprite manifest into a single sprite file
 * (`<symbol>` per icon) plus one standalone SVG per icon, emitted into the Vite
 * build output directory via Rollup's emitFile API.
 *
 * Bootstrap Icons are read from the installed `bootstrap-icons` package; custom
 * icons are read from `customIconsDir`. Symbol ids are prefixed `bi-` / `custom-`.
 *
 * @param {object} options
 * @param {Record<string, string[]>} options.manifest
 *   Icon manifest, e.g. `{ "bootstrap-icons": ["github"], "custom": ["brand"] }`.
 * @param {string} [options.customIconsDir]
 *   Absolute path to the directory holding the custom `*.svg` source files.
 *   Required only when the manifest lists custom icons.
 * @param {string} [options.bootstrapIconsDir]
 *   Absolute path to the Bootstrap Icons source directory. Defaults to the
 *   `bootstrap-icons` package resolved from the consuming project.
 * @param {string} [options.spriteFileName]
 *   Output file name of the combined sprite. Defaults to `icons.svg`; customer
 *   projects typically use `icons-custom.svg`.
 * @param {string} [options.iconsDir]
 *   Subdirectory (inside the Vite output) for the standalone per-icon SVGs.
 *   Defaults to `Icons`.
 * @returns {import('vite').Plugin}
 */
export function svgSpritePlugin(options = {}) {
    const {
        manifest,
        customIconsDir,
        bootstrapIconsDir,
        spriteFileName = 'icons.svg',
        iconsDir = 'Icons',
    } = options;

    return {
        name: 'svg-sprite',
        generateBundle() {
            if (!manifest || typeof manifest !== 'object') {
                this.error('SVG sprite: "manifest" option is required and must be an object.');
            }

            const biNames = manifest['bootstrap-icons'] ?? [];
            const customNames = manifest['custom'] ?? [];

            const biDir =
                biNames.length > 0
                    ? resolveBootstrapIconsDir(bootstrapIconsDir, (message) => this.error(message))
                    : '';

            if (customNames.length > 0 && !customIconsDir) {
                this.error(
                    'SVG sprite: the manifest lists custom icons but no "customIconsDir" option was given.',
                );
            }

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
                    fileName: `${iconsDir}/${id}.svg`,
                    source: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="${viewBox}">\n${inner}\n</svg>`,
                });
            };

            for (const name of biNames) {
                processIcon(resolve(biDir, `${name}.svg`), `bi-${name}`);
            }
            for (const name of customNames) {
                processIcon(resolve(customIconsDir, `${name}.svg`), `custom-${name}`);
            }

            this.emitFile({
                type: 'asset',
                fileName: spriteFileName,
                source: [
                    '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"',
                    '     aria-hidden="true" style="display:none">',
                    ...symbols,
                    '</svg>',
                ].join('\n'),
            });

            const total = biNames.length + customNames.length - errors;
            console.log(
                `SVG sprite: ${total} icons (${biNames.length - errors} Bootstrap Icons + ${customNames.length} custom) → ${spriteFileName}`,
            );

            if (errors > 0) {
                this.error(`SVG sprite: ${errors} icon(s) missing — aborting build`);
            }
        },
    };
}
