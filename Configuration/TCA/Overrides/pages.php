<?php

use StarterTeam\StarterNessa\Configuration;
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
        ]
    );

    ExtensionManagementUtility::addFieldsToPalette(
        'pages',
        'twittercards',
        '--linebreak--,nessa_social_element',
        'after:twitter_card'
    );
})();
