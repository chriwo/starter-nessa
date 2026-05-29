<?php

declare(strict_types=1);

namespace StarterTeam\StarterNessa\DataProcessing;

use Override;
use StarterTeam\StarterNessa\Resource\InterrupterCollector;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;

readonly class InterrupterProcessor implements DataProcessorInterface
{
    public function __construct(
        private SiteFinder $siteFinder,
    ) {
    }

    #[Override]
    public function process(
        ContentObjectRenderer $cObj,
        array $contentObjectConfiguration,
        array $processorConfiguration,
        array $processedData
    ): array {
        if (isset($processorConfiguration['if.']) && !$cObj->checkIf($processorConfiguration['if.'])) {
            return $processedData;
        }

        /**@var InterrupterCollector $interrupterCollector*/
        $interrupterCollector = GeneralUtility::makeInstance(InterrupterCollector::class);

        if (
            (isset($processorConfiguration['references']) && $processorConfiguration['references'])
            || (isset($processorConfiguration['references.']) && $processorConfiguration['references.'])
        ) {
            if (($processedData['data']['nessa_interrupter_mode'] === 1 && isset($processorConfiguration['references.']['data'])) ||
                $this->isCurrentPageRootPage($processedData)
            ) {
                unset($processorConfiguration['references.']['data']);
            }

            $referencesUidList = $cObj->stdWrapValue('references', $processorConfiguration);
            $referencesUids = [];
            if (is_string($referencesUidList)) {
                $referencesUids = GeneralUtility::intExplode(',', $referencesUidList, true);
            }

            $interrupterCollector->addInterrupterReferences($referencesUids);

            if (!empty($processorConfiguration['references.'])) {
                $referenceConfiguration = $processorConfiguration['references.'];
                $relationField = $cObj->stdWrapValue('fieldName', $referenceConfiguration);

                if (is_string($relationField) && $relationField !== '') {
                    $relationTable = $cObj->stdWrapValue('table', $referenceConfiguration, $cObj->getCurrentTable());
                    if (is_string($relationTable) && $relationTable !== '') {
                        $interrupterCollector->addInterrupterFromRelation($relationTable, $relationField, $cObj->data);
                    }
                }
            }
        }

        $targetVariableName = $cObj->stdWrapValue('as', $processorConfiguration, 'interrupters');
        $processedData[$targetVariableName] = $interrupterCollector->getInterrupters();

        return $processedData;
    }

    protected function isCurrentPageRootPage(array $processedData): bool
    {
        return $this->getRootPageId($processedData['data']['uid']) === $processedData['data']['uid'];
    }

    protected function getRootPageId(int $pageUid): int
    {
        return $this->siteFinder->getSiteByPageId($pageUid)->getRootPageId();
    }
}
