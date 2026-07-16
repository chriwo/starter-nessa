/**
 * Example Vite config for a CUSTOMER project that extends the starter-nessa
 * icon set with its own SVGs. Copy this into your project's frontend build and
 * adapt the paths. It is NOT used by the starter-nessa build itself.
 *
 * Prerequisites in the customer project:
 *   - `@chriwo/starter-nessa` and `bootstrap-icons` installed as dependencies
 *   - your own SVG source files in `src/Icons/*.svg`
 *   - a manifest listing your icons (see sprite-manifest.example.json)
 *
 * Output (into the Vite build outDir):
 *   - `icons.svg` → register via TypoScript `spriteFiles`
 *   - `Icons/<symbol>.svg` → register the directory via `iconDirectories`
 */
import { readFileSync } from 'node:fs';
import { resolve } from 'node:path';

import { svgSpritePlugin } from '@chriwo/starter-nessa/plugins/SvgSpritePlugin';
import { fileURLToPath } from 'url';
import { defineConfig } from 'vite';

// Optional: import the starter-nessa base manifest if you want to build a
// self-contained sprite that also includes selected base icons. In the
// recommended layered setup below you do NOT need it — the starter sprite is
// loaded separately, and only your own icons go into icons.svg.
//
// import baseManifest from '@chriwo/starter-nessa/src/Icons/sprite-manifest.json';
// import { mergeManifests } from '@chriwo/starter-nessa/plugins/SvgSpritePlugin';

const __dirname = fileURLToPath(new URL('.', import.meta.url));
const srcAssets = resolve(__dirname, 'src');

const customManifest = JSON.parse(
    readFileSync(resolve(srcAssets, 'Icons/sprite-manifest.json'), 'utf8'),
);

export default defineConfig({
    build: {
        outDir: resolve(__dirname, '../../Public/Frontend/'),
        emptyOutDir: false,
    },
    plugins: [
        svgSpritePlugin({
            // Layered model: build only your own icons into a separate sprite.
            manifest: customManifest,
            // Only needed if you have custom icons
            customIconsDir: resolve(srcAssets, 'Icons'),
        }),
    ],
});
