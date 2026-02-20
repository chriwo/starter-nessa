// eslint-disable-next-line simple-import-sort/imports
import './jquery-global.js';

import '../Sass/app.scss';

import 'bootstrap';

import backToTop from './Modules/BackToTop';
import ceHeroCarousel from './Modules/CeHero';
import cePortfolio from './Modules/CePortfolio';
import cePortfolioFileCollections from './Modules/CePortfolioFileCollections';
import menuMain from './Modules/MenuMain';

menuMain();
ceHeroCarousel();
cePortfolio();
cePortfolioFileCollections();
backToTop();
