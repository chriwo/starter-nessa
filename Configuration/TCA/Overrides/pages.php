<?php

use StarterTeam\StarterNessa\Configuration;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') || die();

ExtensionManagementUtility::registerPageTSConfigFile(
    'starter_nessa',
    'Configuration/TSConfig/PageTs.typoscript',
    'Nessa Theme'
);

(function () {
    foreach (Configuration::getDefaultBackendLayouts() as $backendLayout) {
        ExtensionManagementUtility::registerPageTSConfigFile(
            'starter_nessa',
            'Configuration/TSConfig/BackendLayouts/' . $backendLayout . '.typoscript',
            'Backend-Layout ' . $backendLayout
        );
    }

    $translationFile = 'LLL:EXT:starter_nessa/Resources/Private/Language/locallang_be.xlf:';

    ExtensionManagementUtility::addTCAcolumns(
        'pages',
        [
            'nessa_social_element' => [
                'exclude' => true,
                'label' => $translationFile . 'social_element_formlabel',
                'config' => [
                    'type' => 'inline',
                    'foreign_table' => 'tx_starternessa_social_element',
                    'foreign_field' => 'uid_foreign',
                    'foreign_table_field' => 'tablenames',
                    'foreign_sortby' => 'sorting',
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
            'nessa_interrupter' => [
                'label' => $translationFile . 'pages.starternessa_interrupter',
                'config' => [
                    'type' => 'inline',
                    'foreign_table' => 'tx_starternessa_interrupter_reference',
                    'foreign_field' => 'uid_foreign',
                    'foreign_sortby' => 'sorting_foreign',
                    'foreign_table_field' => 'tablenames',
                    'foreign_match_fields' => [
                        'fieldname' => 'nessa_interrupter',
                    ],
                    'foreign_label' => 'uid_local',
                    'foreign_selector' => 'uid_local',
                    'appearance' => [
                        'useSortable' => true,
                        'createNewRelationLinkTitle' => $translationFile . 'pages.starternessa_interrupter.createNewRelationLinkTitle',
                        'enabledControls' => [
                            'info' => true,
                            'new' => false,
                            'dragdrop' => true,
                            'sort' => false,
                            'hide' => true,
                            'delete' => true,
                        ],
                    ],
                ],
            ],
            'nessa_interrupter_mode' => [
                'label' => $translationFile . 'pages.starternessa_interrupter_mode',
                'config' => [
                    'type' => 'select',
                    'renderType' => 'selectSingle',
                    'items' => [
                        [
                            'label' => $translationFile . 'pages.starternessa_interrupter_mode.0',
                            'value' => 0,
                        ],
                        [
                            'label' => $translationFile . 'pages.starternessa_interrupter_mode.1',
                            'value' => 1,
                        ],
                        [
                            'label' => $translationFile . 'pages.starternessa_interrupter_mode.2',
                            'value' => 2,
                        ],
                    ],
                    'default' => '0',
                    'fieldWizard' => [
                        'selectIcons' => [
                            'disabled' => true,
                        ],
                    ],
                ],
            ],
        ]
    );

    ExtensionManagementUtility::addFieldsToPalette(
        'pages',
        'twittercards',
        '--linebreak--,nessa_social_element',
        'after:twitter_card'
    );

    ArrayUtility::mergeRecursiveWithOverrule(
        $GLOBALS['TCA']['pages'],
        [
            'palettes' => [
                'starterNessaInterrupterPalette' => [
                    'label' => $translationFile . 'pages.palettes.interrupter',
                    'showitem' => 'nessa_interrupter_mode, --linebreak--, nessa_interrupter',
                ],
            ],
        ]
    );

    ExtensionManagementUtility::addToAllTCAtypes(
        'pages',
        '--palette--;;starterNessaInterrupterPalette,',
        (string)PageRepository::DOKTYPE_DEFAULT,
        'after:content_from_pid'
    );
})();
