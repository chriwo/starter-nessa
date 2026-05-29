<?php

use TYPO3\CMS\Core\Resource\FileType;

defined('TYPO3') || die();

return (function () {
    $tcaTable = 'tx_starternessa_interrupter';
    $translateFile = 'LLL:EXT:starter_nessa/Resources/Private/Language/locallang_be.xlf:';

    return [
        'ctrl' => [
            'title' => $translateFile . $tcaTable,
            'label' => 'internal_title',
            'label_alt' => 'header',
            'label_alt_force' => true,
            'sortby' => 'sorting',
            'tstamp' => 'tstamp',
            'crdate' => 'crdate',
            'versioningWS' => true,
            'origUid' => 't3_origuid',
            'languageField' => 'sys_language_uid',
            'transOrigPointerField' => 'l10n_parent',
            'transOrigDiffSourceField' => 'l10n_diffsource',
            'delete' => 'deleted',
            'type' => 'layout',
            'typeicon_column' => 'layout',
            'typeicon_classes' => [
                'default' => 'content-widget-calltoaction',
                '1' => 'content-widget-calltoaction',
                '2' => 'content-widget-calltoaction',
            ],
            'enablecolumns' => [
                'disabled' => 'hidden',
                'starttime' => 'starttime',
                'endtime' => 'endtime',
            ],
        ],

        'types' => [
            '0' => [
                'showitem' => '
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
                    --palette--;;basic,
                    header, teaser,
                    --palette--;' . $translateFile . 'palette.link;link_element,
                    assets,
                    interval,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,
                    --palette--;;language,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
                    --palette--;;hidden,
                    --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.access;access,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:extended,
            ',
            ],
            '1' => [
                'showitem' => '
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
                    --palette--;;basic,
                    header, teaser,
                    --palette--;' . $translateFile . 'palette.link;link_element,
                    assets,
                    interval,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,
                    --palette--;;language,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
                    --palette--;;hidden,
                    --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.access;access,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:extended,
            ',
                'columnsOverrides' => [
                    'teaser' => [
                        'label' => $translateFile . $tcaTable . '.teaser_screen-reader',
                    ],
                ],
            ],
            '2' => [
                'showitem' => '
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
                    --palette--;;basic,
                    header, teaser,
                    --palette--;' . $translateFile . 'palette.link;link_element,
                    interval,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,
                    --palette--;;language,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
                    --palette--;;hidden,
                    --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.access;access,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:extended,
            ',
            ],
        ],

        'palettes' => [
            'basic' => [
                'showitem' => 'internal_title, layout',
            ],
            'link_element' => [
                'showitem' => 'link, link_text',
            ],
            'hidden' => [
                'showitem' => '
                hidden;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:field.default.hidden
            ',
            ],
            'language' => [
                'showitem' => '
                sys_language_uid;LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.language,l10n_parent
            ',
            ],
            'access' => [
                'showitem' => '
                starttime;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:starttime_formlabel,
                endtime;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:endtime_formlabel,
            ',
            ],
        ],

        'columns' => [
            'internal_title' => [
                'l10n_mode' => 'prefixLangTitle',
                'label' => $translateFile . $tcaTable . '.internal_title',
                'config' => [
                    'type' => 'input',
                    'size' => 50,
                    'max' => 50,
                    'eval' => 'trim',
                ],
            ],
            'header' => [
                'l10n_mode' => 'prefixLangTitle',
                'label' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:header',
                'config' => [
                    'type' => 'input',
                    'size' => 50,
                    'max' => 50,
                    'required' => true,
                    'eval' => 'trim',
                ],
            ],
            'layout' => [
                'label' => $translateFile . $tcaTable . '.layout',
                'config' => [
                    'type' => 'select',
                    'renderType' => 'selectSingle',
                    'items' => [
                        [
                            'label' => $translateFile . $tcaTable . '.layout.item.wide_image_and_text',
                            'value' => 'wide_image_and_text',
                        ],
                        [
                            'label' => $translateFile . $tcaTable . '.layout.item.square_image_no_text',
                            'value' => 'square_image_no_text',
                        ],
                        [
                            'label' => $translateFile . $tcaTable . '.layout.item.text_only',
                            'value' => 'text_only',
                        ],
                    ],
                    'default' => 'wide_image_and_text',
                ],
            ],
            'teaser' => [
                'l10n_mode' => 'prefixLangTitle',
                'label' => $translateFile . $tcaTable . '.teaser',
                'config' => [
                    'type' => 'text',
                    'cols' => 50,
                    'rows' => 10,
                    'eval' => 'trim,req',
                ],
            ],
            'assets' => [
                'label' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:asset_references',
                'config' => [
                    'type' => 'file',
                    'allowed' => 'jpg,jpeg,png',
                    'maxitems' => 1,
                    'appearance' => [
                        'createNewRelationLinkTitle' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:asset_references.addFileReference',
                    ],
                    'overrideChildTca' => [
                        'types' => [
                            FileType::UNKNOWN->value => [
                                'showitem' => '
                                        --palette--;;nessaInterrupterPalette,
                                        --palette--;;filePalette',
                            ],
                            FileType::IMAGE->value => [
                                'showitem' => '
                                        --palette--;;nessaInterrupterPalette,
                                        --palette--;;filePalette',
                            ],
                        ],
                        'columns' => [
                            'uid_local' => [
                                'config' => [
                                    'appearance' => [
                                        'fileUploadAllowed' => false,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'link_text' => [
                'label' => $translateFile . $tcaTable . '.link_text',
                'config' => [
                    'type' => 'input',
                    'size' => 40,
                    'max' => 50,
                    'eval' => 'trim',
                ],
            ],
            'link' => [
                'exclude' => false,
                'label' => $translateFile . $tcaTable . '.link',
                'config' => [
                    'type' => 'link',
                    'size' => 40,
                    'required' => true,
                ],
            ],
            'interval' => [
                'label' => $translateFile . $tcaTable . '.interval',
                'config' => [
                    'type' => 'input',
                    'size' => 30,
                    'default' => 24,
                    'equal' => 'trim,int',
                ],
            ],
        ],
    ];
})();
