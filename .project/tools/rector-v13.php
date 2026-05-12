<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Ssch\TYPO3Rector\Set\Typo3LevelSetList;
use Ssch\TYPO3Rector\Set\Typo3SetList;

return RectorConfig::configure()
    ->withPaths([
        getcwd() . '/Classes',
        getcwd() . '/Configuration',
        getcwd() . '/Tests',
        getcwd() . '/ext_localconf.php',
    ])
    ->withImportNames(false, true, false, true)
    ->withSets([
        Typo3SetList::CODE_QUALITY,
        Typo3SetList::GENERAL,
        Typo3LevelSetList::UP_TO_TYPO3_13,
        PHPUnitSetList::ANNOTATIONS_TO_ATTRIBUTES,
    ])
    ->withSkip([
        // Skip paths
        getcwd() . '/.build',
        getcwd() . '/.ddev',
        getcwd() . '/.github',
        getcwd() . '/.project/build',
        getcwd() . '/.project/data',
        getcwd() . '/.project/tools',
        getcwd() . '/config',
        getcwd() . '/Resources',
        getcwd() . '/var',
    ]);
