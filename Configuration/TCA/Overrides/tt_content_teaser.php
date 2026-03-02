<?php

use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') || die();

(function () {
    $translationFile = 'LLL:EXT:starter_nessa/Resources/Private/Language/locallang_be.xlf:';
    $cType = 'nessa_teaser';

    ExtensionManagementUtility::addTCAcolumns(
        'tt_content',
        [
            'nessa_teaser_type' => [
                'exclude' => true,
                'label' => $translationFile . 'tt_content.nessa_teaser_type_formlabel',
                'config' => [
                    'type' => 'select',
                    'renderType' => 'selectSingle',
                    'items' =>  [
                        [
                            'label' => $translationFile . 'tt_content.nessa_teaser_type.I.default',
                            'value' => 'default',
                            'icon' => 'starter-table-tx_starternessa_teaser_element',
                        ],
                        [
                            'label' => $translationFile . 'tt_content.nessa_teaser_type.I.div_image',
                            'value' => '--div--',
                        ],
                        [
                            'label' => $translationFile . 'tt_content.nessa_teaser_type.I.background',
                            'value' => 'background',
                            'icon' => 'starter-table-tx_starternessa_teaser_element',
                        ],
                        [
                            'label' => $translationFile . 'tt_content.nessa_teaser_type.I.image_text_hover',
                            'value' => 'image_text_hover',
                            'icon' => 'starter-table-tx_starternessa_teaser_element',
                        ],
                        [
                            'label' => $translationFile . 'tt_content.nessa_teaser_type.I.image_text_below',
                            'value' => 'image_text_below',
                            'icon' => 'starter-table-tx_starternessa_teaser_element',
                        ],
                        [
                            'label' => $translationFile . 'tt_content.nessa_teaser_type.I.div_icon',
                            'value' => '--div--',
                        ],
                        [
                            'label' => $translationFile . 'tt_content.nessa_teaser_type.I.file_icons',
                            'value' => 'file_icons',
                            'icon' => 'starter-table-tx_starternessa_teaser_element',
                        ],
                        [
                            'label' => $translationFile . 'tt_content.nessa_teaser_type.I.font_icons',
                            'value' => 'font_icons',
                            'icon' => 'starter-table-tx_starternessa_teaser_element',
                        ],
                    ],
                    'size' => 1,
                    'maxitems' => 1,
                    'default' => 'default',
                ],
            ],
            'nessa_teaser_element' => [
                'exclude' => true,
                'label' => $translationFile . 'teaser_element_formlabel',
                'config' => [
                    'type' => 'inline',
                    'foreign_table' => 'tx_starternessa_teaser_element',
                    'foreign_field' => 'tt_content_record',
                    'foreign_sortby' => 'sorting',
                    'minitems' => 1,
                    'maxitems' => 99,
                    'behaviour' => [
                        'allowLanguageSynchronization' => false,
                    ],
                    'appearance' => [
                        'collapseAll' => true,
                        'expandSingle' => true,
                        'levelLinksPosition' => 'bottom',
                        'useSortable' => true,
                        'showPossibleLocalizationRecords' => true,
                        'showAllLocalizationLink' => true,
                        'showSynchronizationLink' => true,
                        'enabledControls' => [
                            'info' => false,
                        ],
                    ],
                ],
            ],
        ]
    );

    ArrayUtility::mergeRecursiveWithOverrule(
        $GLOBALS['TCA']['tt_content'],
        [
            'palettes' => [
                'nessaTeaser' => [
                    'showitem' => '
                    nessa_teaser_type,
                    --linebreak--,
                    nessa_teaser_element
                    ',
                ],
            ],
        ]
    );

    ExtensionManagementUtility::addRecordType(
        [
            'label' => $translationFile . 'CType.I.' . $cType,
            'description' => $translationFile . 'CType.I.' . $cType . '.description',
            'value' => $cType,
            'icon' =>  'starter-ctype-' . $cType,
        ],
        '
                --palette--;;headers,
                bodytext,
            --div--;' . $translationFile . 'tabs.teaser,
                --palette--;;nessaTeaser,
            --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.appearance,
                --palette--;;frames,
                --palette--;;appearanceLinks,
        ',
        [
            'columnsOverrides' => [
                'bodytext' => [
                    'config' => [
                        'enableRichtext' => true,
                    ],
                ],
            ],
        ]
    );
})();
