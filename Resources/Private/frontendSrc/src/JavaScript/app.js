// eslint-disable-next-line simple-import-sort/imports
import './jquery-global.js';

import '../Sass/app.scss';

import 'bootstrap';

import backToTop from './Modules/BackToTop';
import ceHeroCarousel from './Modules/CeHero';
import cePortfolio from './Modules/CePortfolio';
import cePortfolioFileCollections from './Modules/CePortfolioFileCollections';
import menuMain from './Modules/MenuMain';
import InterrupterQueue from './Libs/InterrupterQueue';

menuMain();
ceHeroCarousel();
cePortfolio();
cePortfolioFileCollections();
backToTop();

const interrupterQueue = new InterrupterQueue();

interrupterQueue.init();
