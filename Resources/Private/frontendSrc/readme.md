# StarterNessa – Frontend

Frontend-Build-Setup für das StarterNessa TYPO3-Theme auf Basis von **Vite**.

## Voraussetzungen

- Node.js `>=20 <23`
- npm `>=10`

## Installation

```sh
npm install
```

## Verfügbare Scripts

| Script | Beschreibung |
|---|---|
| `npm start` | Alias für `npm run dev` |
| `npm run dev` | Entwicklungsmodus mit Watch (Source Maps aktiviert) |
| `npm run build` | Produktions-Build (minifiziert) |
| `npm run lint` | JS und SCSS prüfen |
| `npm run lint:js` | Nur JavaScript prüfen (ESLint) |
| `npm run lint:scss` | Nur SCSS prüfen (Stylelint) |
| `npm run lint:fix:js` | JavaScript-Fehler automatisch beheben |
| `npm run lint:fix:scss` | SCSS-Fehler automatisch beheben |

## Ausgabeverzeichnis

Der Build schreibt die Assets nach:

```
Resources/Public/Frontend/
├── app.min.js
├── app.min.css
├── rte.min.css
├── Fonts/
└── Images/
```

## Icons erweitern (Kundenprojekte)

Ein nachgelagertes Projekt kann eigene SVG-Icons ergänzen, ohne den
starter-nessa-Build zu kopieren oder zu forken. Das Sprite-Build-Plugin und das
Basis-Icon-Manifest werden über das npm-Paket exportiert:

| Import | Zweck |
|---|---|
| `@chriwo/starter-nessa/plugins/SvgSpritePlugin` | Das Vite-Plugin `svgSpritePlugin` (+ `mergeManifests`) |
| `@chriwo/starter-nessa/src/Icons/sprite-manifest.json` | Das Basis-Icon-Manifest |

**Empfohlener Ablauf (Layered-Modell):**

1. **Abhängigkeiten installieren** (im Kundenprojekt):
   ```sh
   npm install @chriwo/starter-nessa bootstrap-icons
   ```
2. **Eigene Icons anlegen:** SVG-Quelldateien nach `src/Icons/*.svg` und ein
   eigenes Manifest, das nur die eigenen Icons listet (siehe
   [`plugins/SvgSpritePlugin/example/sprite-manifest.example.json`](plugins/SvgSpritePlugin/example/sprite-manifest.example.json)).
3. **Vite-Build** mit dem Plugin aufrufen (siehe
   [`plugins/SvgSpritePlugin/example/vite.config.example.js`](plugins/SvgSpritePlugin/example/vite.config.example.js)):
   ```js
   import { svgSpritePlugin } from '@chriwo/starter-nessa/plugins/SvgSpritePlugin';

   svgSpritePlugin({
       manifest: customManifest,
       customIconsDir: resolve(srcAssets, 'Icons'),
       spriteFileName: 'icons.svg',
   });
   ```
   Erzeugt werden `icons.svg` (Sprite) und je Icon eine Datei unter
   `Icons/<symbol>.svg`.
4. **Sprite registrieren** – über die vorhandene TypoScript-Liste des
   `SvgSpriteProcessor`, ohne PHP-Änderung:
   ```typoscript
   page.10.dataProcessing.5.spriteFiles.5 = EXT:kunde_theme/Resources/Public/Frontend/icons.svg
   ```
5. **Icon-Verzeichnis registrieren** – damit die Icons im Backend-Dropdown
   auswählbar sind (PROJ-6-Erweiterungspunkt, z. B. in der `ext_localconf.php` des
   Kundenprojekts):
   ```php
   $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['starter_nessa']['iconDirectories'][]
       = 'EXT:kunde_theme/Resources/Public/Frontend/Icons/';
   ```

> **Beide Registrierungsschritte gehören zusammen.** Wird nur das Sprite (Schritt 4)
> registriert, rendert das Frontend das Icon, aber es ist im Backend nicht wählbar –
> und umgekehrt.

### Symbol-IDs & Override-Verhalten

Jedes Icon bekommt eine Symbol-Kennung aus seinem Namen (`bi-<name>` bzw.
`custom-<name>`). Diese Kennungen müssen **projektweit eindeutig** sein.

Vergibt ein Kunden-Icon dieselbe Kennung wie ein Starter-Icon, liegen im Frontend
**beide** Sprites inline im DOM. Ein `<use href="#id">` greift auf das **erste**
passende Symbol in Dokumentreihenfolge zu. Der `SvgSpriteProcessor` hängt die
Sprites nach ihrem `spriteFiles`-Schlüssel (`ksort`) aneinander:

- Standard `10` (Starter) vor `20` (Kunde) → bei Kollision **gewinnt das
  Starter-Icon**.
- Soll das **Kunden-Icon** ein Basis-Icon überschreiben, das Kunden-Sprite mit einem
  **kleineren Schlüssel** registrieren, z. B. `spriteFiles.5`, damit es im DOM zuerst
  steht:
  ```typoscript
  page.10.dataProcessing.5.spriteFiles.5 = EXT:kunde_theme/Resources/Public/Frontend/icons.svg
  ```

> **Achtung:** Eine **unbeabsichtigte** Kollision (versehentlich gleiche Kennung)
> ersetzt bzw. verdeckt so still ein Icon. Kennungen bewusst und eindeutig wählen;
> Overrides bewusst über die Reihenfolge steuern.

### Fehlerfälle

- **Fehlende Bootstrap-Icons-Quelle:** Listet das Manifest `bootstrap-icons`, muss das
  Paket `bootstrap-icons` installiert sein (oder ein expliziter `bootstrapIconsDir`
  übergeben werden) – sonst bricht der Build mit klarer Meldung ab.
- **Verweis auf fehlende SVG-Datei:** Nennt das Manifest ein Icon, dessen Datei fehlt,
  bricht der Build ab, statt ein kaputtes Sprite zu erzeugen.

## Verzeichnisstruktur

```
frontendSrc/
├── src/
│   ├── Fonts/               Schriftarten-Quelldateien
│   ├── Images/              Bild-Quelldateien
│   ├── JavaScript/          JavaScript-Quelldateien
│   │   ├── Modules/         Einzelne JS-Module
│   │   ├── app.js           Haupt-Entry-Point
│   │   ├── jquery-global.js jQuery-Initialisierung (wird zuerst geladen)
│   │   └── rte.js           RTE-Entry-Point (nur CSS)
│   └── Sass/                SCSS-Quelldateien (ITCSS-Struktur)
│       ├── 0-settings/
│       ├── 1-tools/
│       ├── 2-generic/
│       ├── 3-elements/
│       ├── 4-objects/
│       ├── 5-components/
│       ├── 6-utilities/
│       ├── app.scss
│       └── rte.scss
├── plugins/                 Wiederverwendbare, npm-exportierte Vite-Plugins
│   └── SvgSpritePlugin/
│       ├── svg-sprite-plugin.js Plugin für Icon-Sprite + Einzel-SVGs
│       └── example/         Referenz: Beispiel-Config + Beispiel-Manifest
├── vite.config.js           Vite-Konfiguration
├── eslint.config.mjs        ESLint-Konfiguration (Flat Config)
├── .stylelintrc.yml         Stylelint-Konfiguration
└── .prettierrc.yml          Prettier-Konfiguration
```

## Technologie-Stack

### Build
- **[Vite 6](https://vitejs.dev/)** – Build-Tool mit Watch-Modus
- **[Sass](https://sass-lang.com/)** – CSS-Präprozessor
- **[ITCSS](https://www.xfive.co/blog/itcss-scalable-maintainable-css-architecture/)** – SCSS-Architektur

### Frontend-Libraries
- **[Bootstrap 4](https://getbootstrap.com/docs/4.6/)** – CSS-Framework
- **[jQuery 3](https://jquery.com/)** – Pflicht-Abhängigkeit für Bootstrap 4 und Plugins
- **[isotope-layout](https://isotope.metafizzy.co/)** – Filter- und Layout-Library
- **[venobox](https://veno.es/venobox/)** – Lightbox
- **[lazysizes](https://afarkas.github.io/lazysizes/)** – Lazy Loading für Bilder
- **[animate.css](https://animate.style/)** – CSS-Animationen

### Code-Qualität
- **[ESLint 9](https://eslint.org/)** – JavaScript-Linting (Flat Config)
- **[Stylelint 17](https://stylelint.io/)** – SCSS-Linting
- **[Prettier 3](https://prettier.io/)** – Code-Formatierung
