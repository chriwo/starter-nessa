<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') || die();

(function () {
    $translationFile = 'LLL:EXT:starter_nessa/Resources/Private/Language/locallang_be.xlf:';

    $heroBackgroundItems = [
        ['label' => $translationFile . 'tx_starter_background.I.none', 'value' => ''],
        ['label' => $translationFile . 'tx_starter_background.I.bg-light', 'value' => 'bg-light'],
        ['label' => $translationFile . 'tx_starter_background.I.bg-primary', 'value' => 'bg-primary'],
        ['label' => $translationFile . 'tx_starter_background.I.bg-secondary', 'value' => 'bg-secondary'],
        ['label' => $translationFile . 'tx_starter_background.I.bg-dark', 'value' => 'bg-dark'],
        ['label' => $translationFile . 'tx_starter_background.I.bg-gradient-primary', 'value' => 'bg-gradient-primary'],
        ['label' => $translationFile . 'tx_starter_background.I.bg-gradient-dark', 'value' => 'bg-gradient-dark'],
    ];

    ExtensionManagementUtility::addTCAcolumns(
        'tt_content',
        [
            'tx_starter_ctalink' => [
                'exclude' => true,
                'label' => $translationFile . 'tt_content.tx_starter_ctalink_formlabel',
                'config' => [
                    'type' => 'link',
                    'size' => 80,
                    'allowedTypes' => ['page', 'file', 'url', 'email', 'record'],
                    'appearance' => [
                        'browserTitle' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:header_link_formlabel',
                        'allowedOptions' => ['target', 'title', 'rel'],
                    ],
                ],
            ],
            'tx_starter_ctalink_text' => [
                'l10n_mode' => 'prefixLangTitle',
                'exclude' => true,
                'label' => $translationFile . 'tt_content.tx_starter_ctalink_text_formlabel',
                'config' => [
                    'type' => 'input',
                    'size' => 40,
                    'max' => 255,
                ],
            ],
            'tx_starter_ctalink2' => [
                'exclude' => true,
                'label' => $translationFile . 'tt_content.tx_starter_ctalink2_formlabel',
                'config' => [
                    'type' => 'link',
                    'size' => 80,
                    'allowedTypes' => ['page', 'file', 'url', 'email', 'record'],
                    'appearance' => [
                        'browserTitle' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:header_link_formlabel',
                        'allowedOptions' => ['target', 'title', 'rel'],
                    ],
                ],
            ],
            'tx_starter_ctalink2_text' => [
                'l10n_mode' => 'prefixLangTitle',
                'exclude' => true,
                'label' => $translationFile . 'tt_content.tx_starter_ctalink2_text_formlabel',
                'config' => [
                    'type' => 'input',
                    'size' => 40,
                    'max' => 255,
                ],
            ],
            'tx_starter_background' => [
                'exclude' => true,
                'label' => $translationFile . 'tx_starter_background_formlabel',
                'config' => [
                    'type' => 'select',
                    'renderType' => 'selectSingle',
                    'items' => $heroBackgroundItems,
                    'default' => '',
                ],
            ],
        ]
    );

    ExtensionManagementUtility::addToAllTCAtypes(
        'tt_content',
        '--palette--;;nessaCta,',
        '',
        'before:bodytext'
    );

    ExtensionManagementUtility::addFieldsToPalette(
        'tt_content',
        'frames',
        'nessa_column_layout',
        'before:layout'
    );
})();
