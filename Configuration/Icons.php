<?php

use StarterTeam\StarterNessa\Configuration;
use StarterTeam\StarterNessa\Service\IconRegistry;
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

    // Register all built SVG icons (Bootstrap Icons + custom) for the TYPO3
    // icon registry so that TCA select fields can show inline previews. The
    // IconRegistry is the single source of truth for the icon glob — if the
    // directory is not built yet (fresh checkout before first build) it
    // returns an empty list safely. This runs before DI, so IconRegistry must
    // be instantiated directly.
    foreach ((new IconRegistry())->collect() as $icon) {
        $icons[$icon['identifier']] = [
            'provider' => SvgIconProvider::class,
            'source' => $icon['source'],
        ];
    }

    return $icons;
})();
