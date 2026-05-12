<?php

declare(strict_types=1);

use a9f\Fractor\Configuration\FractorConfiguration;
use a9f\Typo3Fractor\Set\Typo3LevelSetList;

return FractorConfiguration::configure()
    ->withPaths([
        getcwd() . '/',
    ])
    ->withSets([
        Typo3LevelSetList::UP_TO_TYPO3_14,
    ])
    ->withSkip([
        // Skip paths
        getcwd() . '/.idea',
        getcwd() . '/.build',
        getcwd() . '/.ddev',
        getcwd() . '/config',
        getcwd() . '/var',
        getcwd() . '/Resources/Private/frontendSrc',
        getcwd() . '/.project/tools/*.xml',
    ]);
