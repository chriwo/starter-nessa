<?php

use StarterTeam\StarterNessa\Resource\HeroContentPosition;
use TYPO3\CMS\Core\Resource\FileType;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') || die();

(function () {
    $translationFile = 'LLL:EXT:starter_nessa/Resources/Private/Language/locallang_be.xlf:';
    $cType = 'nessa_banner';

    ExtensionManagementUtility::addTCAcolumns(
        'tt_content',
        [
            'tx_starter_hero_content_position' => [
                'exclude' => true,
                'label' => $translationFile . 'tx_starter_hero_content_position_formlabel',
                'description' => $translationFile . 'tx_starter_hero_content_position.description',
                'config' => [
                    'type' => 'select',
                    'renderType' => 'selectSingle',
                    'items' => [
                        ['label' => $translationFile . 'tx_starter_hero_content_position.I.bottom-left', 'value' => HeroContentPosition::BOTTOM_LEFT->value],
                        ['label' => $translationFile . 'tx_starter_hero_content_position.I.center-left', 'value' => HeroContentPosition::CENTER_LEFT->value],
                        ['label' => $translationFile . 'tx_starter_hero_content_position.I.center', 'value' => HeroContentPosition::CENTER->value],
                        ['label' => $translationFile . 'tx_starter_hero_content_position.I.none', 'value' => HeroContentPosition::NONE->value],
                    ],
                    'default' => HeroContentPosition::CENTER_LEFT->value,
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
            header,
            bodytext,
            --palette--;;nessaCtaDouble,
            --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.media,
            assets,
            tx_starter_hero_content_position,
            tx_starter_background,
        ',
        [
            'columnsOverrides' => [
                'tx_starter_background' => [
                    'config' => [
                        'default' => 'bg-dark',
                    ],
                ],
                'bodytext' => [
                    'config' => [
                        'enableRichtext' => true,
                    ],
                ],
                'assets' => [
                    'config' => [
                        'minitems' => 0,
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
                                            'desktop' => [
                                                'title' => $translationFile . 'banner.crop.desktop',
                                                'selectedRatio' => '16:9',
                                                'allowedAspectRatios' => [
                                                    '16:9' => [
                                                        'title' => 'LLL:EXT:core/Resources/Private/Language/locallang_wizards.xlf:imwizard.ratio.16_9',
                                                        'value' => 16 / 9,
                                                    ],
                                                    'NaN' => [
                                                        'title' => 'LLL:EXT:core/Resources/Private/Language/locallang_wizards.xlf:imwizard.ratio.free',
                                                        'value' => 0.0,
                                                    ],
                                                ],
                                            ],
                                            'mobile' => [
                                                'title' => $translationFile . 'banner.crop.mobile',
                                                'selectedRatio' => '4:3',
                                                'allowedAspectRatios' => [
                                                    '4:3' => [
                                                        'title' => 'LLL:EXT:core/Resources/Private/Language/locallang_wizards.xlf:imwizard.ratio.4_3',
                                                        'value' => 4 / 3,
                                                    ],
                                                    '1:1' => [
                                                        'title' => 'LLL:EXT:core/Resources/Private/Language/locallang_wizards.xlf:imwizard.ratio.1_1',
                                                        'value' => 1.0,
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
                                        --palette--;;nessaHeroImageOverlayPalette,
                                        --palette--;;filePalette',
                                ],
                                FileType::IMAGE->value => [
                                    'showitem' => '
                                        --palette--;;nessaHeroImageOverlayPalette,
                                        --palette--;;filePalette',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]
    );
})();
