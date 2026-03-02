<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') || die();

(function () {
    $translationFile = 'LLL:EXT:starter_nessa/Resources/Private/Language/locallang_be.xlf:';

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
            'nessa_column_layout' => [
                'exclude' => true,
                'label' => $translationFile . 'tt_content.nessa_column_layout_formlabel',
                'config' => [
                    'type' => 'select',
                    'renderType' => 'selectSingle',
                    'items' =>  [
                        [
                            'label' => $translationFile . 'tt_content.nessa_column_layout.I.col6',
                            'value' => 'col-md-6',
                        ],
                        [
                            'label' => $translationFile . 'tt_content.nessa_column_layout.I.col4',
                            'value' => 'col-md-4',
                        ],
                        [
                            'label' => $translationFile . 'tt_content.nessa_column_layout.I.col3',
                            'value' => 'col-md-3',
                        ],
                    ],
                    'size' => 1,
                    'maxitems' => 1,
                    'default' => 'col-md-4',
                ],
            ],
        ]
    );

    ExtensionManagementUtility::addToAllTCAtypes(
        'tt_content',
        '--palette--;' . $translationFile . ':palette.cta;nessaCta,',
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
