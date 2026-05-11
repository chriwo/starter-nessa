<?php

use TYPO3\CMS\Core\Resource\FileType;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') || die();

(function () {
    $translationFile = 'LLL:EXT:starter_nessa/Resources/Private/Language/locallang_be.xlf:';
    $cType = 'nessa_partner';

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
            --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.media,
            assets',
        [
            'columnsOverrides' => [
                'bodytext' => [
                    'config' => [
                        'enableRichtext' => true,
                    ],
                ],
                'assets' => [
                    'label' => $translationFile . 'tt_content.nessa_partner.partner',
                    'config' => [
                        'minitems' => 1,
                        'allowed' => 'jpg,jpeg,png,svg',
                        'overrideChildTca' => [
                            'columns' => [
                                'uid_local' => [
                                    'config' => [
                                        'appearance' => [
                                            'elementBrowserAllowed' => 'jpg,jpeg,png,svg',
                                        ],
                                    ],
                                ],
                            ],
                            'types' => [
                                FileType::IMAGE->value => [
                                    'showitem' => '
                            --palette--;;nessaPartnerOverlayPalette,
                            --palette--;;filePalette',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]
    );
})();
