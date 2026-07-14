<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') || die();

(function () {
    $translationFile = 'LLL:EXT:starter_nessa/Resources/Private/Language/locallang_be.xlf:';
    $cType = 'nessa_team';

    ExtensionManagementUtility::addTCAcolumns(
        'tt_content',
        [
            'nessa_team_member_element' => [
                'exclude' => false,
                'label' => $translationFile . 'team_element_formlabel',
                'config' => [
                    'type' => 'group',
                    'allowed' => 'tx_starternessa_team_element',
                    'minitems' => 1,
                    'maxitems' => 99,
                    'size' => 8,
                    'behaviour' => [
                        'allowLanguageSynchronization' => true,
                    ],
                    'elementBrowserEntryPoints' => [
                        'tx_starternessa_team_element' => '###PAGE_TSCONFIG_ID###',
                    ],
                    'fieldControl' => [
                        'editPopup' => [
                            'disabled' => false,
                        ],
                        'addRecord' => [
                            'disabled' => false,
                            'options' => [
                                'table' => 'tx_starternessa_team_element',
                                'setValue' => 'prepend',
                                'pid' => '###PAGE_TSCONFIG_ID###',
                            ],
                        ],
                        'listModule' => [
                            'disabled' => false,
                        ],
                    ],
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
            --div--;' . $translationFile . 'tab.team_members,
            nessa_team_member_element,',
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
