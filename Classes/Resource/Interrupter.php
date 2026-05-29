<?php

declare(strict_types=1);

namespace StarterTeam\StarterNessa\Resource;

use Override;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Resource\FileCollector;

final class Interrupter extends AbstractInterrupter
{
    public function __construct(
        array $interrupterData,
    ) {
        $this->internalTitle = $interrupterData['internalTitle'] ?? '';
        $this->properties = $interrupterData;
        $this->assets = $this->createAssets($interrupterData);
        $this->layout = $interrupterData['layout'] ?? '';
    }

    #[Override]
    public function toArray(): array
    {
        $array = [
            'uid' => $this->getUid(),
            'name' => $this->getInternalTitle(),
            'interval' => $this->getInterval(),
            'layout' => $this->getLayout(),
        ];

        foreach ($this->properties as $key => $value) {
            $array[$key] = $value;
        }

        return $array;
    }

    private function createAssets(array $interrupterData): array
    {
        $fileCollector = GeneralUtility::makeInstance(FileCollector::class);
        $fileCollector->addFilesFromRelation(
            'tx_starternessa_interrupter',
            'assets',
            $interrupterData
        );

        return $fileCollector->getFiles();
    }
}
