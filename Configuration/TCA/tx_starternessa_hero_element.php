<?php

use StarterTeam\StarterNessa\Resource\HeroContentPosition;
use TYPO3\CMS\Core\Resource\FileType;

defined('TYPO3') || die();

return (function () {
    $translationFile = 'LLL:EXT:starter_nessa/Resources/Private/Language/locallang_be.xlf:';
    $showItem = [
        '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general',
        'header,LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:header.ALT.html_formlabel',
        'bodytext',
        'content_position',
        '--palette--;;cta',
        '--div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.media',
        'assets',
        '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language',
        '--palette--;;language',
        '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access',
        '--palette--;;hidden',
        '--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.access;access',
        '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:extended',
    ];

    return [
        'ctrl' => [
            'label' => 'header',
            'sortby' => 'sorting',
            'tstamp' => 'tstamp',
            'crdate' => 'crdate',
            'title' => $translationFile . 'hero_element_label',
            'delete' => 'deleted',
            'versioningWS' => true,
            'origUid' => 't3_origuid',
            'hideTable' => true,
            'hideAtCopy' => true,
            'prependAtCopy' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.prependAtCopy',
            'transOrigPointerField' => 'l10n_parent',
            'transOrigDiffSourceField' => 'l10n_diffsource',
            'languageField' => 'sys_language_uid',
            'enablecolumns' => [
                'disabled' => 'hidden',
                'starttime' => 'starttime',
                'endtime' => 'endtime',
            ],
            'typeicon_classes' => [
                'default' => 'starter-table-tx_starternessa_hero_element',
            ],
            'security' => [
                'ignorePageTypeRestriction' => true,
            ],
        ],

        'types' => [
            '1' => [
                'showitem' => implode(',', $showItem),
            ],
        ],

        'palettes' => [
            'hidden' => [
                'showitem' => 'hidden',
            ],
            'language' => [
                'showitem' => 'sys_language_uid, l10n_parent',
            ],
            'access' => [
                'label' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.access',
                'showitem' => '
                    starttime,
                    endtime,
                    --linebreak--,
                    fe_group,
                    --linebreak--,
                    editlock
            ',
            ],
            'cta' => [
                'label' => $translationFile . 'palette.cta',
                'showitem' => 'ctalink, ctalink_text, --linebreak--, ctalink2, ctalink2_text',
            ],
        ],

        'columns' => [
            'header' => [
                'l10n_mode' => 'prefixLangTitle',
                'label' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:header',
                'config' => [
                    'type' => 'input',
                    'size' => 50,
                    'max' => 255,
                    'eval' => 'trim',
                    'required' => true,
                ],
            ],
            'content_position' => [
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
            'ctalink' => [
                'exclude' => true,
                'label' => $translationFile . 'tx_starternessa_hero.tx_starter_ctalink_formlabel',
                'config' => [
                    'type' => 'link',
                    'size' => 50,
                    'allowedTypes' => ['page', 'file', 'email', 'telephone', 'record'],
                    'appearance' => ['allowedOptions' => ['title', 'rel']],
                ],
            ],
            'ctalink_text' => [
                'l10n_mode' => 'prefixLangTitle',
                'exclude' => true,
                'label' => $translationFile . 'tx_starternessa_hero.tx_starter_ctalink_text_formlabel',
                'config' => [
                    'type' => 'input',
                    'size' => 40,
                    'max' => 255,
                ],
            ],
            'ctalink2' => [
                'exclude' => true,
                'label' => $translationFile . 'tx_starternessa_hero.tx_starter_ctalink2_formlabel',
                'config' => [
                    'type' => 'link',
                    'size' => 50,
                    'allowedTypes' => ['page', 'file', 'email', 'telephone', 'record'],
                    'appearance' => ['allowedOptions' => ['title', 'rel']],
                ],
            ],
            'ctalink2_text' => [
                'l10n_mode' => 'prefixLangTitle',
                'exclude' => true,
                'label' => $translationFile . 'tx_starternessa_hero.tx_starter_ctalink2_text_formlabel',
                'config' => [
                    'type' => 'input',
                    'size' => 40,
                    'max' => 255,
                ],
            ],
            'assets' => [
                'label' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:asset_references',
                'config' => [
                    'type' => 'file',
                    'allowed' => 'jpg,jpeg,png',
                    'appearance' => [
                        'createNewRelationLinkTitle' => 'LLL:EXT:frontend/Resources/Private/Language/Database.xlf:tt_content.asset_references.addFileReference',
                    ],
                    'minitems' => 1,
                    'maxitems' => 1,
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
                                        // Wide landscape crop for the fullscreen hero on desktop/tablet.
                                        'desktop' => [
                                            'title' => $translationFile . 'hero.crop.desktop',
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
                                        // Portrait crop for the tall, narrow mobile viewport (art direction).
                                        'mobile' => [
                                            'title' => $translationFile . 'hero.crop.mobile',
                                            'selectedRatio' => '3:4',
                                            'allowedAspectRatios' => [
                                                '3:4' => [
                                                    'title' => $translationFile . 'hero.crop.ratio.3_4',
                                                    'value' => 3 / 4,
                                                ],
                                                '9:16' => [
                                                    'title' => $translationFile . 'hero.crop.ratio.9_16',
                                                    'value' => 9 / 16,
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
            'bodytext' => [
                'l10n_mode' => 'prefixLangTitle',
                'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.text',
                'config' => [
                    'type' => 'text',
                    'cols' => '80',
                    'rows' => '10',
                ],
            ],
        ],
    ];
})();
