<?php

use TYPO3\CMS\Core\Utility\ArrayUtility;

defined('TYPO3') || die();

// @codingStandardsIgnoreStart

(function () {
    // define new palettes for content elements
    ArrayUtility::mergeRecursiveWithOverrule(
        $GLOBALS['TCA']['tt_content'],
        [
            'palettes' => [
                'nessaCta' => [
                    'label' => 'LLL:EXT:starter_nessa/Resources/Private/Language/locallang_be.xlf:palette.cta',
                    'showitem' => 'tx_starter_ctalink, tx_starter_ctalink_text',
                ],
                'nessaCtaDouble' => [
                    'label' => 'LLL:EXT:starter_nessa/Resources/Private/Language/locallang_be.xlf:palette.cta',
                    'showitem' => 'tx_starter_ctalink, tx_starter_ctalink_text,'
                        . ' --linebreak--, tx_starter_ctalink2, tx_starter_ctalink2_text',
                ],
            ],
        ]
    );
})();

// @codingStandardsIgnoreEnd
