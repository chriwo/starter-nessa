<?php

use StarterTeam\StarterNessa\Resource\ImageOrientType;
use TYPO3\CMS\Core\Resource\FileType;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') || die();

(function () {
    $translationFile = 'LLL:EXT:starter_nessa/Resources/Private/Language/locallang_be.xlf:';
    $cType = 'nessa_image_text';

    ExtensionManagementUtility::addTCAcolumns(
        'tt_content',
        [
            'tx_starter_hero_stats' => [
                'exclude' => true,
                'label' => $translationFile . 'tx_starter_hero_stats_formlabel',
                'description' => $translationFile . 'tx_starter_hero_stats.description',
                'config' => [
                    'type' => 'text',
                    'cols' => 40,
                    'rows' => 4,
                    'max' => 500,
                ],
            ],
        ]
    );

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
            --palette--;;nessaCtaDouble,
            tx_starter_hero_stats,
            --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.media,
            assets,
            imageorient,
            --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.appearance,
            tx_starter_background,
        ',
        [
            'columnsOverrides' => [
                'imageorient' => [
                    'label' => $translationFile . 'tx_starter_image_position_formlabel',
                    'config' => [
                        'default' => ImageOrientType::BESIDE_TEXT_LEFT->value,
                    ],
                ],
                'bodytext' => [
                    'config' => [
                        'enableRichtext' => true,
                    ],
                ],
                'assets' => [
                    'config' => [
                        'minitems' => 1,
                        'maxitems' => 1,
                        'allowed' => 'jpg,jpeg,png',
                        'overrideChildTca' => [
                            'columns' => [
                                'uid_local' => [
                                    'config' => [
                                        'appearance' => [
                                            'elementBrowserAllowed' => 'jpg,jpeg,png',
                                        ],
                                    ],
                                ],
                                'crop' => [
                                    'config' => [
                                        'cropVariants' => [
                                            'default' => [
                                                'title' => 'Default',
                                                'selectedRatio' => '3:2',
                                                'allowedAspectRatios' => [
                                                    '3:2' => [
                                                        'title' => 'LLL:EXT:core/Resources/Private/Language/locallang_wizards.xlf:imwizard.ratio.3_2',
                                                        'value' => 3 / 2,
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'types' => [
                                '0' => [
                                    'showitem' => '
                                        --palette--;;nessaMemberOverlayPalette,
                                        --palette--;;filePalette',
                                ],
                                FileType::IMAGE->value => [
                                    'showitem' => '
                                        --palette--;;nessaMemberOverlayPalette,
                                        --palette--;;filePalette',
                                ],
                            ],
                        ],
                    ],
                ],
                'tx_starter_ctalink' => [
                    'config' => [
                        'allowedTypes' => ['page', 'file', 'url', 'email', 'telephone', 'record'],
                    ],
                ],
                'tx_starter_ctalink2' => [
                    'config' => [
                        'allowedTypes' => ['page', 'file', 'url', 'email', 'telephone', 'record'],
                    ],
                ],
            ],
        ]
    );
})();
