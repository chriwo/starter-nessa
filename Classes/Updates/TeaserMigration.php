<?php

declare(strict_types=1);

namespace StarterTeam\StarterNessa\Updates;

use Doctrine\DBAL\Exception;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Core\Utility\StringUtility;
use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

#[UpgradeWizard(self::class)]
class TeaserMigration implements UpgradeWizardInterface
{
    final public const string TABLE_NAME = 'tt_content';

    private const array MIGRATION_SETTINGS = [
        [
            'CType' => 'nessa_teaser',
            'teaserType' => 'fonts_icons',
        ],
        [
            'CType' => 'nessa_teaser_background',
            'teaserType' => 'background',
        ],
        [
            'CType' => 'nessa_teaser_icon',
            'teaserType' => 'file_icons',
        ],
    ];

    public function __construct(
        private readonly ConnectionPool $connectionPool,
    ) {
    }

    #[\Override]
    public function getTitle(): string
    {
        return 'EXT:starter_nessa: Migrate multiple Teaser CE\'s into one Teaser CE.';
    }

    #[\Override]
    public function getDescription(): string
    {
        $description = 'The existing teaser content elements "nessa_teaser", "nessa_teaser_background", and';
        $description .= '"nessa_teaser_icon" are now combined into one teaser content element "nessa_teaser". ';
        $description .= 'This update wizard migrates all existing teaser content elements';
        $description .= 'to use the new only one ("nessa_teaser"). Count of CE: ' . $this->hasRecordToUpdate();
        return $description;
    }

    #[\Override]
    public function executeUpdate(): bool
    {
        return $this->performMigration();
    }

    #[\Override]
    public function updateNecessary(): bool
    {
        return (bool)$this->hasRecordToUpdate();
    }

    #[\Override]
    public function getPrerequisites(): array
    {
        return [
            DatabaseUpdatedPrerequisite::class,
        ];
    }

    protected function performMigration(): bool
    {
        $records = $this->getRecordsToMigrate();

        foreach ($records as $record) {
            $teaserType = $this->getTargetTeaserType($record['CType']);
            if ($teaserType === '') {
                continue;
            }

            $this->updateContentElement($record['uid'], $teaserType);
        }

        return true;
    }

    protected function getTargetTeaserType(string $contentElementType): string
    {
        foreach (self::MIGRATION_SETTINGS as $setting) {
            if ($setting['CType'] === $contentElementType) {
                return $setting['teaserType'];
            }
        }

        return '';
    }

    protected function hasRecordToUpdate(): int
    {
        $resultValue = $this->getPreparedQueryBuilder()
            ->count('uid')
            ->executeQuery()
            ->fetchOne();

        return is_numeric($resultValue) ? (int)$resultValue : 0;
    }

    /**
     * @return array<int<0, max>, array{uid: int, CType: string}>
     * @throws Exception
     */
    protected function getRecordsToMigrate(): array
    {
        $result = [];
        $results = $this->getPreparedQueryBuilder()
            ->select('uid', 'CType')
            ->executeQuery()
            ->fetchAllAssociative();

        foreach ($results as $key => $resultRow) {
            $result[$key]['uid'] = MathUtility::forceIntegerInRange($resultRow['uid'], 0, PHP_INT_MAX);
            $result[$key]['CType'] = StringUtility::cast($resultRow['CType']) ?? '';
        }

        return $result;
    }

    protected function getPreparedQueryBuilder(): QueryBuilder
    {
        $orWhereConstraints = [];
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(static::TABLE_NAME);
        $queryBuilder->getRestrictions()->removeAll();

        foreach (self::MIGRATION_SETTINGS as $setting) {
            $orWhereConstraints[] = $queryBuilder->expr()->eq(
                'CType',
                $queryBuilder->createNamedParameter($setting['CType'])
            );
        }

        $constraint = [
            $queryBuilder->expr()->or(...$orWhereConstraints),
            $queryBuilder->expr()->eq('nessa_teaser_type', $queryBuilder->createNamedParameter('')),
        ];

        $queryBuilder
            ->from(static::TABLE_NAME)
            ->where(...$constraint);

        return $queryBuilder;
    }

    protected function updateContentElement(int $uid, string $teaserType): void
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tt_content');
        $queryBuilder
            ->update('tt_content')
            ->set('nessa_teaser_type', $teaserType)
            ->set('CType', 'nessa_teaser')
            ->where(
                $queryBuilder->expr()->eq(
                    'uid',
                    $queryBuilder->createNamedParameter($uid, Connection::PARAM_INT)
                )
            )
            ->executeStatement();
    }
}
