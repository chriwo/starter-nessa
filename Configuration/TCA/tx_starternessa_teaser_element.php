<?php

use TYPO3\CMS\Core\Resource\FileType;

defined('TYPO3') || die();

return (function () {
    $translationFile = 'LLL:EXT:starter_nessa/Resources/Private/Language/locallang_be.xlf:';
    $showItem = [
        'header',
        'bodytext',
        '--palette--;;cta',
        '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:media,',
        'icon',
        'assets',
        '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language',
        '--palette--;;language',
        '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access',
        '--palette--;;hidden',
        '--palette--;;access',
        '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:extended',
    ];

    return [
        'ctrl' => [
            'label' => 'header',
            'sortby' => 'sorting',
            'tstamp' => 'tstamp',
            'crdate' => 'crdate',
            'title' => $translationFile . 'teaser_element_label',
            'delete' => 'deleted',
            'versioningWS' => true,
            'origUid' => 't3_origuid',
            'hideTable' => true,
            'hideAtCopy' => true,
            'prependAtCopy' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.prependAtCopy',
            'transOrigPointerField' => 'l10n_parent',
            'transOrigDiffSourceField' => 'l10n_diffsource',
            'languageField' => 'sys_language_uid',
            'translationSource' => 'l10n_source',
            'enablecolumns' => [
                'disabled' => 'hidden',
                'starttime' => 'starttime',
                'endtime' => 'endtime',
            ],
            'typeicon_classes' => [
                'default' => 'starter-table-tx_starternessa_teaser_element',
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
                    starttime;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:starttime_formlabel,
                    endtime;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:endtime_formlabel,
                    --linebreak--,
                    fe_group;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:fe_group_formlabel,
                    --linebreak--,editlock
            ',
            ],
            'cta' => [
                'showitem' => 'link, link_text',
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
            'link' => [
                'exclude' => true,
                'label' => $translationFile . 'tx_starternessa_teaser_element.link',
                'config' => [
                    'type' => 'link',
                    'size' => 50,
                    'allowedTypes' => ['page', 'file', 'url', 'email', 'record', 'telephone'],
                    'appearance' => ['allowedOptions' => ['title', 'rel']],
                ],
            ],
            'link_text' => [
                'l10n_mode' => 'prefixLangTitle',
                'exclude' => true,
                'label' => $translationFile . 'tx_starternessa_teaser_element.link_text',
                'config' => [
                    'type' => 'input',
                    'size' => 40,
                    'max' => 255,
                ],
            ],
            'icon' => [
                'exclude' => true,
                'label' => $translationFile . 'tx_starternessa_teaser_element.icon',
                'config' => [
                    'type' => 'select',
                    'renderType' => 'selectSingle',
                    'items' => [
                        [
                            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.default_value',
                            'value' => '',
                        ],
                    ],
                    'default' => '',
                ],
            ],
            'assets' => [
                'label' => $translationFile . 'tx_starternessa_teaser_element.asset_references',
                'config' => [
                    'type' => 'file',
                    'allowed' => 'jpg,jpeg,png,svg',
                    'appearance' => [
                        'createNewRelationLinkTitle' => 'LLL:EXT:frontend/Resources/Private/Language/Database.xlf:tt_content.asset_references.addFileReference',
                    ],
                    'maxitems' => 1,
                    'overrideChildTca' => [
                        'columns' => [
                            'uid_local' => [
                                'config' => [
                                    'appearance' => [
                                        'elementBrowserAllowed' => 'jpg,jpeg,png,svg',
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
                                        'portrait' => [
                                            'title' => 'Portrait',
                                            'selectedRatio' => '4:5',
                                            'allowedAspectRatios' => [
                                                '4:5' => [
                                                    'title' => 'LLL:EXT:core/Resources/Private/Language/locallang_wizards.xlf:imwizard.ratio.4_5',
                                                    'value' => 4 / 5,
                                                ],
                                            ],
                                        ],
                                        'square' => [
                                            'title' => 'Quadrat',
                                            'selectedRatio' => '1:1',
                                            'allowedAspectRatios' => [
                                                '1:1' => [
                                                    'title' => 'LLL:EXT:core/Resources/Private/Language/locallang_wizards.xlf:imwizard.ratio.1_1',
                                                    'value' => 1,
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
                                        --palette--;;nessaTeaserBackgroundOverlayPalette,
                                        --palette--;;filePalette',
                            ],
                            FileType::IMAGE->value => [
                                'showitem' => '
                                        --palette--;;nessaTeaserBackgroundOverlayPalette,
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
