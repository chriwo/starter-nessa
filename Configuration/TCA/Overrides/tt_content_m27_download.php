<?php

use StarterTeam\StarterNessa\Configuration;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') || die();

(function () {
    $translationFile = 'LLL:EXT:starter_nessa/Resources/Private/Language/locallang_be.xlf:';
    $cType = 'starter_m27_download';

    ExtensionManagementUtility::addRecordType(
        [
            'label' => $translationFile . 'CType.I.' . $cType,
            'description' => $translationFile . 'CType.I.' . $cType . '.description',
            'value' => $cType,
            'icon' => 'starter-ctype-' . $cType,
        ],
        '
        --palette--;;headers,
        bodytext,
        --div--;' . $translationFile . 'tab.download;download,
        --palette--;;uploads,
        --palette--;;uploadslayout,',
        [
            'columnsOverrides' => [
                'bodytext' => [
                    'config' => [
                        'enableRichtext' => true,
                        'richtextConfiguration' => 'default',
                    ],
                ],
                'media' => [
                    'config' => [
                        'minitems' => 0,
                        'maxitems' => 10,
                        'overrideChildTca' => [
                            'columns' => [
                                'uid_local' => [
                                    'config' => [
                                        'appearance' => [
                                            'elementBrowserAllowed' => Configuration::getAllowedFileExtensions('starter_m27_download'),
                                        ],
                                    ],
                                ],
                                'title' => [
                                    'config' => [
                                        'max' => 80,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]
    );
})();
