<?php

use StarterTeam\StarterNessa\Configuration;
use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

return (function () {
    $icons = [];

    foreach (Configuration::getContentElements() as $identifier => $property) {
        $icons['starter-ctype-' . $identifier] = [
            'provider' => SvgIconProvider::class,
            'source' => $property['typeIconPath'],
        ];
    }

    foreach (Configuration::getContentElementTables() as $identifier => $property) {
        $icons['starter-table-' . $identifier] = [
            'provider' => SvgIconProvider::class,
            'source' => $property['typeIconPath'],
        ];
    }

    // Register all built SVG icons (Bootstrap Icons + custom) for the TYPO3
    // icon registry so that TCA select fields can show inline previews.
    // The directory is populated by `npm run build:sprite` — if it does not
    // exist yet (fresh checkout before first build) glob() returns [] safely.
    $iconsDir = ExtensionManagementUtility::extPath('starter_nessa')
        . 'Resources/Public/Frontend/Icons/';

    foreach (glob($iconsDir . '*.svg') ?: [] as $svgFile) {
        $name = basename($svgFile, '.svg');
        $icons['starter-nessa-' . $name] = [
            'provider' => SvgIconProvider::class,
            'source'   => 'EXT:starter_nessa/Resources/Public/Frontend/Icons/' . $name . '.svg',
        ];
    }

    return $icons;
})();
