<?php

use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') || die();

(function () {
    $translationFile = 'LLL:EXT:starter_nessa/Resources/Private/Language/locallang_be.xlf:';
    $cType = 'nessa_pricing';

    ExtensionManagementUtility::addTCAcolumns(
        'tt_content',
        [
            'nessa_pricing_featured_item' => [
                'label' => $translationFile . 'tt_content.nessa_pricing_featured_item',
                'config' => [
                    'type' => 'select',
                    'renderType' => 'selectSingle',
                    'foreign_table' => 'tx_starternessa_teaser_pricing',
                    'foreign_table_where' => '
                        AND {#tx_starternessa_teaser_pricing}.{#tt_content_record} = ###REC_FIELD_uid###
                        AND {#tx_starternessa_teaser_pricing}.{#sys_language_uid}
                            IN (-1, ###REC_FIELD_sys_language_uid###)
                    ',
                    'items' => [
                        ['label' => '', 'value' => 0],
                    ],
                    'sortItems' => [
                        'label' => 'asc',
                    ],
                    'minitems' => 0,
                    'maxitems' => 1,
                ],
            ],
            'nessa_pricing_featured_label' => [
                'l10n_mode' => 'prefixLangTitle',
                'label' => $translationFile . 'tt_content.nessa_pricing_featured_label',
                'config' => [
                    'type' => 'input',
                    'size' => 40,
                    'max' => 25,
                    'eval' => 'trim',
                ],
            ],
            'nessa_teaser_pricing' => [
                'exclude' => true,
                'label' => $translationFile . 'teaser_pricing_formlabel',
                'config' => [
                    'type' => 'inline',
                    'foreign_table' => 'tx_starternessa_teaser_pricing',
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
                'nessaPricingFeatured' => [
                    'label' => $translationFile . 'palette.nessaPricingFeatured',
                    'showitem' => 'nessa_pricing_featured_item, --linebreak--, nessa_pricing_featured_label',
                ],
                'nessaPricingTeaser' => [
                    'showitem' => '
                    nessa_teaser_pricing
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
                --palette--;;nessaPricingFeatured,
                --palette--;;nessaPricingTeaser,
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:appearance,
                --palette--;;frames,
                --palette--;;appearanceLinks
        ',
    );
})();
