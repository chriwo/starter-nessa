<?php

use TYPO3\CMS\Core\Utility\ArrayUtility;

defined('TYPO3') || die();

(function () {
    ArrayUtility::mergeRecursiveWithOverrule(
        $GLOBALS['TCA']['tt_content']['types']['text'],
        [
            'columnsOverrides' => [
                'tx_starter_ctalink' => [
                    'config' => [
                        'allowedTypes' => ['page', 'file', 'url', 'email', 'telephone', 'record'],
                    ],
                ],
            ],
        ],
    );
})();
