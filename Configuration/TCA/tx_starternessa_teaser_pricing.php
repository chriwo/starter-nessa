<?php

use TYPO3\CMS\Core\Resource\FileType;

defined('TYPO3') || die();

return (function () {
    $translationFile = 'LLL:EXT:starter_nessa/Resources/Private/Language/locallang_be.xlf:';
    $showItem = [
        'header',
        '--palette--;;pricing',
        'bodytext',
        'feature_list',
        '--palette--;;cta',
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
            'title' => $translationFile . 'teaser_pricing_label',
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
                'default' => 'starter-table-tx_starternessa_teaser_pricing',
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
            'pricing' => [
                'showitem' => 'price, currency',
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
            'price' => [
                'l10n_mode' => '',
                'label' => $translationFile . 'tx_starternessa_teaser_pricing.price',
                'config' => [
                    'type' => 'input',
                    'size' => 10,
                    'max' => 10,
                    'eval' => 'trim',
                ],
            ],
            'currency' => [
                'l10n_mode' => '',
                'label' => $translationFile . 'tx_starternessa_teaser_pricing.currency',
                'config' => [
                    'type' => 'input',
                    'size' => 10,
                    'max' => 10,
                    'eval' => 'trim',
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
            'feature_list' => [
                'l10n_mode' => 'prefixLangTitle',
                'label' => $translationFile . 'tx_starternessa_teaser_pricing.feature_list',
                'config' => [
                    'type' => 'text',
                    'cols' => '80',
                    'rows' => '10',
                ],
            ],
            'link' => [
                'exclude' => true,
                'label' => $translationFile . 'tx_starternessa_teaser_pricing.link',
                'config' => [
                    'type' => 'link',
                    'size' => 50,
                    'allowedTypes' => ['page', 'file', 'url', 'email', 'record', 'telephone'],
                    'appearance' => ['allowedOptions' => ['title', 'rel']],
                ],
            ],
            'link_text' => [
                'l10n_mode' => '',
                'exclude' => true,
                'label' => $translationFile . 'tx_starternessa_teaser_pricing.link_text',
                'config' => [
                    'type' => 'input',
                    'size' => 40,
                    'max' => 255,
                ],
            ],
        ],
    ];
})();
