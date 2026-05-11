<?php

use B13\Container\Tca\ContainerConfiguration;
use B13\Container\Tca\Registry;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

defined('TYPO3') || die();

(function () {
    if (ExtensionManagementUtility::isLoaded('container')) {
        $translationFile = 'LLL:EXT:starter_nessa/Resources/Private/Language/locallang_be.xlf:';

        GeneralUtility::makeInstance(Registry::class)->configureContainer(
            new ContainerConfiguration(
                '3columns-container',
                $translationFile . 'CType.I.3columns-container',
                $translationFile . 'CType.I.3columns-container.description',
                [
                    [
                        ['name' => $translationFile . 'tt_content.label.container.left', 'colPos' => 201, 'allowed' => ['CType' => 'text, textmedia']],
                        ['name' => $translationFile . 'tt_content.label.container.middle', 'colPos' => 202, 'allowed' => ['CType' => 'text, textmedia']],
                        ['name' => $translationFile . 'tt_content.label.container.right', 'colPos' => 203, 'allowed' => ['CType' => 'text, textmedia']],
                    ],
                ]
            )
            ->setIcon('EXT:container/Resources/Public/Icons/container-3col.svg')
        );
    }
})();
