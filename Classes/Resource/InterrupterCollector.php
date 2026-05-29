<?php

declare(strict_types=1);

namespace StarterTeam\StarterNessa\Resource;

use Countable;
use InvalidArgumentException;
use Override;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use StarterTeam\StarterNessa\Resource\Exception\InterrupterDoesNotExistException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class InterrupterCollector implements Countable
{
    protected array $interrupters = [];

    public function __construct(
        private readonly InterrupterRepository $interrupterRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function addInterrupterFromRelation(string $relationTable, string $relationField, array $referenceRecord): void
    {
        $interrupterReferences = $this->getInterrupterReferences($relationTable, $relationField, $referenceRecord);
        if (!empty($interrupterReferences)) {
            $this->addInterrupterObjects($interrupterReferences);
        }
    }

    public function addInterrupterReferences(array $interrupterReferencesUids = []): void
    {
        foreach ($interrupterReferencesUids as $interrupterReferenceUid) {
            $interrupterObject = $this->getInterrupterRepository()->findInterrupterReferenceByUid($interrupterReferenceUid);
            if (!$interrupterObject instanceof InterrupterInterface) {
                continue;
            }
            $this->addInterrupterObject($interrupterObject);
        }
    }

    public function addInterrupterObject(InterrupterInterface $interrupter): void
    {
        $this->interrupters[] = $interrupter;
    }

    /**
     * Add multiple interrupter objects to the collection
     *
     * @param InterrupterInterface[] $interrupters The interrupter objects
     */
    public function addInterrupterObjects(array $interrupters): void
    {
        foreach (array_reverse($interrupters) as $interrupter) {
            array_unshift($this->interrupters, $interrupter);
        }
    }

    public function getInterrupterReferences(string $tableName, string $fieldName, array $element): array
    {
        $currentId = !empty($element['uid']) ? $element['uid'] : 0;

        try {
            $references = $this->interrupterRepository->findByRelation($tableName, $fieldName, $currentId);
        } catch (InterrupterDoesNotExistException) {
            return [];
        } catch (InvalidArgumentException $invalidArgumentException) {
            $logMessage = $invalidArgumentException->getMessage() . ' (table: "' . $tableName . '", fieldName: "' . $fieldName . '", currentId: ' . $currentId . ')';
            $this->logger->log(LogLevel::ERROR, $logMessage, ['exception' => $invalidArgumentException]);
            return [];
        }

        $localizedId = null;
        if (isset($element['_LOCALIZED_UID'])) {
            $localizedId = $element['_LOCALIZED_UID'];
        }// elseif (isset($element['_PAGES_OVERLAY_UID'])) {
        //    $localizedId = $element['_PAGES_OVERLAY_UID'];
        //}

        $isTableLocalizable = (
            !empty($GLOBALS['TCA'][$tableName]['ctrl']['languageField'])
            && !empty($GLOBALS['TCA'][$tableName]['ctrl']['transOrigPointerField'])
        );
        if ($isTableLocalizable && $localizedId !== null) {
            $localizedReferences = $this->interrupterRepository->findByRelation($tableName, $fieldName, $localizedId);
            $references = $localizedReferences;
        }

        return $references;
    }

    #[Override]
    public function count(): int
    {
        return count($this->interrupters);
    }

    public function getInterrupters(): array
    {
        return $this->interrupters;
    }

    private function getInterrupterRepository(): InterrupterRepository
    {
        return GeneralUtility::makeInstance(InterrupterRepository::class);
    }
}
