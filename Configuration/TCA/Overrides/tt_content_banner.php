<?php

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
                        ['label' => $translationFile . 'tx_starter_hero_content_position.I.bottom-left', 'value' => 'bottom-left'],
                        ['label' => $translationFile . 'tx_starter_hero_content_position.I.center-left', 'value' => 'center-left'],
                        ['label' => $translationFile . 'tx_starter_hero_content_position.I.center', 'value' => 'center'],
                        ['label' => $translationFile . 'tx_starter_hero_content_position.I.none', 'value' => 'none'],
                    ],
                    'default' => 'center-left',
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
                    ],
                ],
            ],
        ]
    );
})();
