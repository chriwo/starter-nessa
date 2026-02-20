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
