<?php

return (function () {
    $defaultReferenceTable = 'tx_starternessa_interrupter';
    $tcaTable = 'tx_starternessa_interrupter_reference';
    $translateFile = 'LLL:EXT:starter_nessa/Resources/Private/Language/locallang_be.xlf:';

    return [
        'ctrl' => [
            'title' => $translateFile . $tcaTable,
            'label' => 'uid_local',
            'formattedLabel_userFunc' => 'StarterTeam\StarterNessa\Service\UserInterrupterInlineLabelService->getInlineLabel',
            'formattedLabel_userFunc_options' => [
                'tx_starternessa_interrupter' => [
                    'header',
                    'internal_title',
                ],
            ],
            'tstamp' => 'tstamp',
            'crdate' => 'crdate',
            'hideTable' => true,
            'delete' => 'deleted',
            'versioningWS' => true,
            'languageField' => 'sys_language_uid',
            'transOrigPointerField' => 'l10n_parent',
            'transOrigDiffSourceField' => 'l10n_diffsource',
            'rootLevel' => -1,
            'type' => 'uid_local:layout',
            // records can and should be edited in workspaces
            //'shadowColumnsForMovePlaceholders' => 'tablenames,fieldname,uid_local,table_local,uid_foreign',
            'enablecolumns' => [
                'disabled' => 'hidden',
            ],
            'typeicon_classes' => [
                'default' => 'content-widget-calltoaction',
            ],
            'security' => [
                'ignoreWebMountRestriction' => true,
                'ignoreRootLevelRestriction' => true,
                'ignorePageTypeRestriction' => true,
            ],
        ],
        'types' => [
            // Note that at the moment we define the same fields for every media type.
            // We leave the extensive definition of each type here anyway, to make clear that you can use it to differentiate between the types.
            '0' => [
                'showitem' => '
                    --palette--;;basicoverlayPalette,
                    --palette--;;filePalette
                ',
            ],
            '1' => [
                'showitem' => '
                    --palette--;;basicoverlayPalette,
                    --palette--;;filePalette
                ',
                'columnsOverrides' => [
                    'teaser' => [
                        'label' => $translateFile . $tcaTable . '.teaser_screen-reader',
                    ],
                ],
            ],
            '2' => [
                'showitem' => '
                    --palette--;;basicoverlayPalette,
                    --palette--;;filePalette
                ',
            ],
        ],
        'palettes' => [
            // Used for basic overlays: having a filelist etc
            'basicoverlayPalette' => [
                'label' => $translateFile . $tcaTable . '.basicoverlayPalette',
                'showitem' => 'header,--linebreak--,teaser,--linebreak--,link,link_text',
            ],
            // File palette, hidden but needs to be included all the time
            'filePalette' => [
                'showitem' => 'uid_local, hidden, sys_language_uid, l10n_parent',
                'isHiddenPalette' => true,
            ],
        ],
        'columns' => [
            'uid_local' => [
                'label' => $translateFile . $tcaTable . '.uid_local',
                'config' => [
                    'type' => 'group',
                    'size' => 1,
                    'relationship' => 'manyToOne',
                    'maxitems' => 1,
                    'minitems' => 0,
                    'allowed' => $defaultReferenceTable,
                    'hideSuggest' => true,
                ],
            ],
            'uid_foreign' => [
                'label' => $translateFile . $tcaTable . '.uid_foreign',
                'config' => [
                    'type' => 'number',
                    'size' => 10,
                ],
            ],
            'tablenames' => [
                'label' => $translateFile . $tcaTable . '.tablenames',
                'config' => [
                    'type' => 'input',
                    'size' => 30,
                    'eval' => 'trim',
                ],
            ],
            'fieldname' => [
                'label' => $translateFile . $tcaTable . '.fieldname',
                'config' => [
                    'type' => 'input',
                    'size' => 30,
                ],
            ],
            'sorting_foreign' => [
                'label' => $translateFile . $tcaTable . '.sorting_foreign',
                'config' => [
                    'type' => 'number',
                    'size' => 4,
                    'default' => 0,
                ],
            ],
            //'table_local' => [
            //    'label' => $translateFile . $tcaTable . '.table_local',
            //    'config' => [
            //        'type' => 'input',
            //        'size' => 20,
            //        'default' => $defaultReferenceTable,
            //    ],
            //],

            'header' => [
                'l10n_mode' => 'prefixLangTitle',
                'exclude' => true,
                'label' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:header',
                'config' => [
                    'type' => 'input',
                    'size' => 50,
                    'max' => 50,
                    'placeholder' => '__row|uid_local|header',
                    'mode' => 'useOrOverridePlaceholder',
                    'default' => null,
                    'nullable' => true,
                ],
            ],
            'teaser' => [
                'l10n_mode' => 'prefixLangTitle',
                'exclude' => true,
                'label' => $translateFile . $tcaTable . '.teaser',
                'config' => [
                    'type' => 'text',
                    'cols' => 50,
                    'rows' => 10,
                    'placeholder' => '__row|uid_local|teaser',
                    'mode' => 'useOrOverridePlaceholder',
                    'default' => null,
                    'nullable' => true,
                ],
            ],
            'link' => [
                'exclude' => true,
                'label' => $translateFile . $tcaTable . '.link',
                'config' => [
                    'type' => 'link',
                    'size' => 40,
                    'default' => null,
                    'placeholder' => '__row|uid_local|link',
                    'nullable' => true,
                ],
            ],
            'link_text' => [
                'label' => $translateFile . $tcaTable . '.link_text',
                'config' => [
                    'type' => 'input',
                    'size' => 40,
                    'max' => 50,
                    'placeholder' => '__row|uid_local|link_text',
                    'mode' => 'useOrOverridePlaceholder',
                    'default' => null,
                    'nullable' => true,
                ],
            ],
        ],
    ];
})();
