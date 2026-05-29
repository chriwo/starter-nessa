<?php

use TYPO3\CMS\Core\Utility\ArrayUtility;

defined('TYPO3') || die();

ArrayUtility::mergeRecursiveWithOverrule(
    $GLOBALS['TCA']['sys_file_reference'],
    [
        'palettes' => [
            'nessaMemberOverlayPalette' => [
                'showitem' => 'title,alternative,--linebreak--,crop',
            ],
            'nessaTeaserBackgroundOverlayPalette' => [
                'showitem' => 'title,--linebreak--,crop',
            ],
            'nessaTeaserIconOverlayPalette' => [
                'showitem' => 'title',
            ],
            'nessaPartnerOverlayPalette' => [
                'showitem' => 'title,alternative,--linebreak--,link',
            ],
            'nessaHeroImageOverlayPalette' => [
                'showitem' => 'crop',
            ],
            'nessaInterrupterPalette' => [
                'showitem' => 'title, alternative, --linebreak--, crop',
            ],
        ],
    ]
);
