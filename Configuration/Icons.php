<?php

use StarterTeam\StarterNessa\Configuration;
use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;

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

    return $icons;
})();
