# StarterNessa – Frontend

Frontend-Build-Setup für das StarterNessa TYPO3-Theme auf Basis von **Vite**.

## Voraussetzungen

- Node.js `>=20.19 <23`
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
4. **Icons pro Site registrieren** – alles läuft über die **Site Settings**
   (`config/sites/<identifier>/settings.yaml` oder den Settings-Editor im
   Backend), also pro Domain/Site einzeln. Kein `$GLOBALS`, keine PHP-Änderung.
   Es gibt genau zwei Listen; der **erste Eintrag ist per Default das mitgelieferte
   Set**. Zum Ergänzen eigener Icons die Liste inkl. Built-in-Pfad angeben:
   ```yaml
   starter:
     icons:
       # Frontend: Sprite-Dateien (inline im Body), in Reihenfolge
       spriteFiles:
         - 'EXT:starter_nessa/Resources/Public/Frontend/icons.svg'   # Built-in
         - 'EXT:kunde_theme/Resources/Public/Frontend/icons.svg'     # eigenes
       # Backend: Verzeichnisse, deren Icons im Dropdown auswählbar sind
       directories:
         - 'EXT:starter_nessa/Resources/Public/Frontend/Icons/'      # Built-in
         - 'EXT:kunde_theme/Resources/Public/Frontend/Icons/'        # eigenes
   ```

> **`spriteFiles` und `directories` gehören zusammen.** `spriteFiles` versorgt das
> Frontend (Sprite inline im Body), `directories` das Backend-Dropdown. Wird ein
> Icon nur in einer der beiden Listen abgedeckt, rendert es zwar im Frontend, ist
> aber im Backend nicht wählbar – oder umgekehrt.

> **Mitgelieferte Icons ganz ignorieren?** Einfach den Built-in-Pfad **aus beiden
> Listen weglassen** und nur die eigenen Pfade angeben – ohne den Frontend-Build
> anzupassen. Ein Überschreiben der Settings ersetzt die Default-Liste vollständig;
> es gibt daher keinen separaten Schalter mehr. Da die Konfiguration in den Site
> Settings liegt, kann eine Multi-Domain-Installation das pro Site unterschiedlich
> entscheiden.

### Symbol-IDs & Override-Verhalten

Jedes Icon bekommt eine Symbol-Kennung aus seinem Namen (`bi-<name>` bzw.
`custom-<name>`). Diese Kennungen müssen **projektweit eindeutig** sein.

Vergibt ein Kunden-Icon dieselbe Kennung wie ein Starter-Icon, liegen im Frontend
**beide** Sprites inline im DOM. Ein `<use href="#id">` greift auf das **erste**
passende Symbol in Dokumentreihenfolge zu. Der `SvgSpriteProcessor` hängt die
Sprites **in der Reihenfolge der `spriteFiles`-Liste** aneinander.

- Bei Kollision **gewinnt der zuerst gelistete Eintrag**. Steht der Built-in-Pfad
  (Default) vorne, gewinnt das Starter-Icon.
- Soll das **Kunden-Icon** ein Basis-Icon überschreiben, das eigene Sprite in der
  Liste **vor** den Built-in-Pfad setzen (oder den Built-in-Pfad weglassen).

> **Achtung:** Eine **unbeabsichtigte** Kollision (versehentlich gleiche Kennung)
> ersetzt bzw. verdeckt so still ein Icon. Kennungen bewusst und eindeutig wählen.

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
- **[Vite 7](https://vitejs.dev/)** – Build-Tool mit Watch-Modus
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
