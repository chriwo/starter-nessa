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
        ]
    );

    ExtensionManagementUtility::addToAllTCAtypes(
        'tt_content',
        '--palette--;' . $translationFile . ':palette.cta;nessaCta,',
        '',
        'before:bodytext'
    );
})();
