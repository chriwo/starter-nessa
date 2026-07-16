<?php

use StarterTeam\StarterNessa\Tca\IconItemsProvider;

defined('TYPO3') || die();

return (function () {
    $translationFile = 'LLL:EXT:starter_nessa/Resources/Private/Language/locallang_be.xlf:';
    $showItem = [
        '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general',
        'header',
        'icon',
        'social_link',
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
            'title' => $translationFile . 'social_element_label',
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
                'default' => 'starter-table-tx_starternessa_social_element',
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
        ],

        'columns' => [
            'header' => [
                'l10n_mode' => 'prefixLangTitle',
                'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.name',
                'config' => [
                    'type' => 'input',
                    'size' => 50,
                    'max' => 255,
                    'eval' => 'trim',
                    'required' => true,
                ],
            ],
            'social_link' => [
                'exclude' => true,
                'l10n_mode' => 'exclude',
                'label' => $translationFile . 'tx_starternessa_social_element.social_link',
                'config' => [
                    'type' => 'link',
                    'size' => 50,
                    'allowedTypes' => ['page', 'url', 'email', 'record'],
                    'appearance' => ['allowedOptions' => ['title', 'rel']],
                ],
            ],
            'icon' => [
                'exclude' => true,
                'l10n_mode' => 'exclude',
                'label' => $translationFile . 'tx_starternessa_social_element.icon',
                'config' => [
                    'type' => 'select',
                    'renderType' => 'selectSingle',
                    'items' => [
                        [
                            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.default_value',
                            'value' => '',
                        ],
                    ],
                    'itemsProcFunc' => IconItemsProvider::class . '->populate',
                    'default' => '',
                ],
            ],

            'uid_foreign' => [
                'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.uid_foreign',
                'config' => [
                    'type' => 'number',
                    'size' => 10,
                ],
            ],
            'tablenames' => [
                'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.tablenames',
                'config' => [
                    'type' => 'input',
                    'size' => 30,
                    'max' => 64,
                    'eval' => 'trim',
                ],
            ],
        ],
    ];
})();
