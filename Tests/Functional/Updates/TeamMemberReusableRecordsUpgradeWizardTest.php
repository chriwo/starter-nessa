<?php

declare(strict_types=1);

namespace StarterTeam\StarterNessa\Tests\Functional\Updates;

use PHPUnit\Framework\Attributes\Test;
use StarterTeam\StarterNessa\Updates\TeamMemberReusableRecordsUpgradeWizard;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

final class TeamMemberReusableRecordsUpgradeWizardTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = ['starterteam/starter-nessa'];

    protected array $coreExtensionsToLoad = ['frontend'];

    #[Test]
    public function migratesInlineMembersIntoDeduplicatedReusableRecords(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/TeamMembersToMigrate.csv');

        $wizard = GeneralUtility::makeInstance(TeamMemberReusableRecordsUpgradeWizard::class);

        self::assertTrue($wizard->updateNecessary());
        self::assertTrue($wizard->executeUpdate());
        self::assertFalse($wizard->updateNecessary(), 'Wizard must not be repeatable after migration.');

        $storageFolderUid = $this->fetchStorageFolderUid();
        self::assertGreaterThan(0, $storageFolderUid, 'A "Team-Mitglieder" storage folder must be created.');

        // Canonical records (smallest uid per realname) survive and are moved
        // into the storage folder, detached from their content element.
        foreach ([1, 2, 5] as $canonicalUid) {
            $record = $this->fetchMember($canonicalUid);
            self::assertSame(0, self::toInt($record['deleted']), sprintf('Canonical member %d must survive.', $canonicalUid));
            self::assertSame(0, self::toInt($record['tt_content_record']), 'Canonical member must be detached from its CE.');
            self::assertSame($storageFolderUid, self::toInt($record['pid']), 'Canonical member must live in the storage folder.');
        }

        // Duplicates (same realname) are soft-deleted and detached.
        foreach ([3, 4] as $duplicateUid) {
            $record = $this->fetchMember($duplicateUid);
            self::assertSame(1, self::toInt($record['deleted']), sprintf('Duplicate member %d must be soft-deleted.', $duplicateUid));
            self::assertSame(0, self::toInt($record['tt_content_record']));
        }

        // The content elements reference their (canonical) members in original order.
        self::assertSame('1,2', $this->fetchMemberReferences(10));
        self::assertSame('1,2,5', $this->fetchMemberReferences(20));

        // The inline social link of member 1 is untouched and still attached.
        $social = $this->fetchRow('tx_starternessa_social_element', 1);
        self::assertSame(0, self::toInt($social['deleted']));
        self::assertSame(1, self::toInt($social['uid_foreign']));
    }

    #[Test]
    public function isNotNecessaryWhenNoBoundMembersExist(): void
    {
        $wizard = GeneralUtility::makeInstance(TeamMemberReusableRecordsUpgradeWizard::class);

        self::assertFalse($wizard->updateNecessary());
    }

    private function fetchStorageFolderUid(): int
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('pages');
        $queryBuilder->getRestrictions()->removeAll();
        $uid = $queryBuilder
            ->select('uid')
            ->from('pages')
            ->where(
                $queryBuilder->expr()->eq('doktype', $queryBuilder->createNamedParameter(254, Connection::PARAM_INT)),
                $queryBuilder->expr()->eq('title', $queryBuilder->createNamedParameter('Team-Mitglieder')),
            )
            ->executeQuery()
            ->fetchOne();

        return is_numeric($uid) ? (int)$uid : 0;
    }

    /**
     * @return array<string, mixed>
     */
    private function fetchMember(int $uid): array
    {
        return $this->fetchRow('tx_starternessa_team_element', $uid);
    }

    private function fetchMemberReferences(int $contentElementUid): string
    {
        $row = $this->fetchRow('tt_content', $contentElementUid);

        return is_string($row['nessa_team_member_element']) ? $row['nessa_team_member_element'] : '';
    }

    private static function toInt(mixed $value): int
    {
        return is_numeric($value) ? (int)$value : 0;
    }

    /**
     * @return array<string, mixed>
     */
    private function fetchRow(string $table, int $uid): array
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
        $queryBuilder->getRestrictions()->removeAll();
        $row = $queryBuilder
            ->select('*')
            ->from($table)
            ->where($queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($uid, Connection::PARAM_INT)))
            ->executeQuery()
            ->fetchAssociative();

        self::assertIsArray($row, sprintf('Row %d in %s must exist.', $uid, $table));

        return $row;
    }
}
