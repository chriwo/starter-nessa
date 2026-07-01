<?php

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
            tx_starter_background,
        ',
        [
            'columnsOverrides' => [
                'imageorient' => [
                    'label' => $translationFile . 'tx_starter_image_position_formlabel',
                    'config' => [
                        'default' => 25,
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
                    ],
                ],
            ],
        ]
    );
})();
