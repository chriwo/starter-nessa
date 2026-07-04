<?php

use B13\Container\Tca\ContainerConfiguration;
use B13\Container\Tca\Registry;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

defined('TYPO3') || die();

(function () {
    if (ExtensionManagementUtility::isLoaded('container')) {
        $translationFile = 'LLL:EXT:starter_nessa/Resources/Private/Language/locallang_be.xlf:';

        $typo3 = (new Typo3Version())->getMajorVersion();
        $restrictionKey = 'allowedContentTypes';
        $restrictions = 'text, textmedia';
        if ($typo3 < 14) {
            $restrictionKey = 'allowed';
            $restrictions = ['CType' => 'text, textmedia'];
        }

        GeneralUtility::makeInstance(Registry::class)->configureContainer(
            new ContainerConfiguration(
                '2columns-container',
                $translationFile . 'CType.I.2columns-container',
                $translationFile . 'CType.I.2columns-container.description',
                [
                    [
                        ['name' => $translationFile . 'tt_content.label.container.left', 'colPos' => 201, $restrictionKey => $restrictions],
                        ['name' => $translationFile . 'tt_content.label.container.right', 'colPos' => 203, $restrictionKey => $restrictions],
                    ],
                ]
            )
            ->setIcon('EXT:container/Resources/Public/Icons/container-2col.svg')
        );
    }
})();
