<?php

use B13\Container\Tca\ContainerConfiguration;
use B13\Container\Tca\Registry;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

defined('TYPO3') || die();

(function () {
    if (ExtensionManagementUtility::isLoaded('container')) {
        GeneralUtility::makeInstance(Registry::class)->configureContainer(
            new ContainerConfiguration(
                '2columns-container',
                '2 Column Container',
                'Some Description of the Container',
                [
                    [
                        ['name' => 'left side', 'colPos' => 201, 'allowed' => ['CType' => 'text, textmedia']],
                        ['name' => 'right side', 'colPos' => 203, 'allowed' => ['CType' => 'text, textmedia']],
                    ],
                ]
            )
            ->setIcon('EXT:container/Resources/Public/Icons/container-2col.svg')
        );
    }
})();
