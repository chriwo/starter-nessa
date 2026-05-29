<?php

declare(strict_types=1);

namespace StarterTeam\StarterNessa\Service;

use TYPO3\CMS\Backend\Utility\BackendUtility;

class UserInterrupterInlineLabelService
{
    /**
     * Get the user function label for the file_reference table
     */
    public function getInlineLabel(array &$params): void
    {
        $interrupterFields = isset($params['options']['tx_starternessa_interrupter']) && is_array($params['options']['tx_starternessa_interrupter'])
            ? $params['options']['tx_starternessa_interrupter']
            : [];

        if (empty($interrupterFields)) {
            // Nothing to do
            $params['title'] = $params['row']['uid'];
            return;
        }

        // In case of a group field uid_local is a resolved array
        $interrupterRecord = $params['row']['uid_local'][0]['row'];
        $interrupterReferenceRecord = $params['row'];

        // Configuration
        $title = [];
        foreach ($interrupterFields as $field) {
            if (!empty($interrupterReferenceRecord[$field])) {
                $value = $interrupterReferenceRecord[$field];
            } else {
                $value = $interrupterRecord[$field];
            }

            if ((string)$value === '') {
                continue;
            }

            $title[] = BackendUtility::getRecordTitlePrep($value);
        }
        $params['title'] = implode(' » ', $title);
    }
}
