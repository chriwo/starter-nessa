<?php

declare(strict_types=1);

namespace StarterTeam\StarterNessa\Updates;

use Doctrine\DBAL\Exception;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

/**
 * Migrates the inline team member records (bound to a single "nessa_team"
 * content element via tx_starternessa_team_element.tt_content_record) into
 * standalone, reusable records:
 *
 *   1. A storage sysfolder "Team-Mitglieder" is created (once).
 *   2. Members sharing the same realname are merged into a single canonical
 *      record (the one with the smallest uid wins; the duplicates are soft
 *      deleted). The merge intentionally keys on realname only.
 *   3. Canonical records are moved into the storage folder and detached from
 *      their content element (tt_content_record = 0). Their FAL image and
 *      inline social links reference the record by uid and therefore survive
 *      the move untouched.
 *   4. Each nessa_team content element receives the ordered, de-duplicated CSV
 *      list of its (canonical) member uids in nessa_team_member_element.
 *
 * The wizard runs exactly once: once every bound member has been detached,
 * {@see updateNecessary()} returns false and TYPO3 marks the wizard as done in
 * sys_registry.
 */
#[UpgradeWizard(self::class)]
class TeamMemberReusableRecordsUpgradeWizard implements UpgradeWizardInterface
{
    private const string MEMBER_TABLE = 'tx_starternessa_team_element';

    private const string CONTENT_TABLE = 'tt_content';

    private const string PAGES_TABLE = 'pages';

    private const string STORAGE_FOLDER_TITLE = 'Team-Mitglieder';

    private const int SYSFOLDER_DOKTYPE = 254;

    public function __construct(
        private readonly ConnectionPool $connectionPool,
    ) {
    }

    #[\Override]
    public function getTitle(): string
    {
        return 'EXT:starter_nessa: Migrate inline team members into reusable records.';
    }

    #[\Override]
    public function getDescription(): string
    {
        return sprintf(
            'Team members are turned into standalone, reusable records stored in a "%s" folder. '
            . 'Members with an identical name are merged, and every "nessa_team" element references '
            . 'its members by a sortable multi-selection instead of owning them inline. '
            . 'Bound members left to migrate: %d.',
            self::STORAGE_FOLDER_TITLE,
            $this->countBoundMembers(),
        );
    }

    #[\Override]
    public function updateNecessary(): bool
    {
        return $this->countBoundMembers() > 0;
    }

    /**
     * @return array<int, class-string>
     */
    #[\Override]
    public function getPrerequisites(): array
    {
        return [
            DatabaseUpdatedPrerequisite::class,
        ];
    }

    #[\Override]
    public function executeUpdate(): bool
    {
        $members = $this->fetchBoundMembers();
        if ($members === []) {
            return true;
        }

        $canonicalByUid = $this->buildCanonicalMap($members);
        $membersPerContentElement = $this->buildMembersPerContentElement($members, $canonicalByUid);

        $storageFolderPid = $this->findOrCreateStorageFolder();

        foreach ($this->uniqueCanonicalUids($canonicalByUid) as $canonicalUid) {
            $this->moveMemberToStorage($canonicalUid, $storageFolderPid);
        }

        foreach ($canonicalByUid as $memberUid => $canonicalUid) {
            if ($memberUid !== $canonicalUid) {
                $this->softDeleteDuplicate($memberUid);
            }
        }

        foreach ($membersPerContentElement as $contentElementUid => $memberUids) {
            $this->writeMemberReferences($contentElementUid, $memberUids);
        }

        return true;
    }

    /**
     * @return array<int, array{uid: int, realname: string, tt_content_record: int}>
     * @throws Exception
     */
    private function fetchBoundMembers(): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::MEMBER_TABLE);
        $queryBuilder->getRestrictions()
            ->removeAll()
            ->add(new DeletedRestriction());

        $rows = $queryBuilder
            ->select('uid', 'realname', 'tt_content_record')
            ->from(self::MEMBER_TABLE)
            ->where(
                $queryBuilder->expr()->gt(
                    'tt_content_record',
                    $queryBuilder->createNamedParameter(0, Connection::PARAM_INT),
                )
            )
            ->orderBy('tt_content_record')
            ->addOrderBy('sorting')
            ->executeQuery()
            ->fetchAllAssociative();

        $members = [];
        foreach ($rows as $row) {
            $uid = is_numeric($row['uid']) ? (int)$row['uid'] : 0;
            if ($uid <= 0) {
                continue;
            }
            $members[] = [
                'uid' => $uid,
                'realname' => trim(is_string($row['realname']) ? $row['realname'] : ''),
                'tt_content_record' => is_numeric($row['tt_content_record']) ? (int)$row['tt_content_record'] : 0,
            ];
        }

        return $members;
    }

    /**
     * Maps every member uid to the canonical uid of its realname group (the
     * smallest uid wins).
     *
     * @param array<int, array{uid: int, realname: string, tt_content_record: int}> $members
     * @return array<int, int>
     */
    private function buildCanonicalMap(array $members): array
    {
        $canonicalByName = [];
        foreach ($members as $member) {
            $name = $member['realname'];
            if (!isset($canonicalByName[$name]) || $member['uid'] < $canonicalByName[$name]) {
                $canonicalByName[$name] = $member['uid'];
            }
        }

        $canonicalByUid = [];
        foreach ($members as $member) {
            $canonicalByUid[$member['uid']] = $canonicalByName[$member['realname']];
        }

        return $canonicalByUid;
    }

    /**
     * Builds the ordered, de-duplicated list of canonical member uids per
     * content element, preserving the original inline sorting.
     *
     * @param array<int, array{uid: int, realname: string, tt_content_record: int}> $members
     * @param array<int, int> $canonicalByUid
     * @return array<int, array<int, int>>
     */
    private function buildMembersPerContentElement(array $members, array $canonicalByUid): array
    {
        $result = [];
        foreach ($members as $member) {
            $contentElementUid = $member['tt_content_record'];
            $canonicalUid = $canonicalByUid[$member['uid']];
            if (!isset($result[$contentElementUid])) {
                $result[$contentElementUid] = [];
            }
            if (!in_array($canonicalUid, $result[$contentElementUid], true)) {
                $result[$contentElementUid][] = $canonicalUid;
            }
        }

        return $result;
    }

    /**
     * @param array<int, int> $canonicalByUid
     * @return array<int, int>
     */
    private function uniqueCanonicalUids(array $canonicalByUid): array
    {
        return array_values(array_unique(array_values($canonicalByUid)));
    }

    private function findOrCreateStorageFolder(): int
    {
        $existing = $this->findStorageFolder();
        if ($existing > 0) {
            return $existing;
        }

        $connection = $this->connectionPool->getConnectionForTable(self::PAGES_TABLE);
        $now = time();
        $connection->insert(
            self::PAGES_TABLE,
            [
                'pid' => 0,
                'tstamp' => $now,
                'crdate' => $now,
                'doktype' => self::SYSFOLDER_DOKTYPE,
                'title' => self::STORAGE_FOLDER_TITLE,
                'sorting' => 256,
            ]
        );

        return (int)$connection->lastInsertId();
    }

    private function findStorageFolder(): int
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::PAGES_TABLE);
        $queryBuilder->getRestrictions()
            ->removeAll()
            ->add(new DeletedRestriction());

        $uid = $queryBuilder
            ->select('uid')
            ->from(self::PAGES_TABLE)
            ->where(
                $queryBuilder->expr()->eq(
                    'doktype',
                    $queryBuilder->createNamedParameter(self::SYSFOLDER_DOKTYPE, Connection::PARAM_INT),
                ),
                $queryBuilder->expr()->eq(
                    'title',
                    $queryBuilder->createNamedParameter(self::STORAGE_FOLDER_TITLE),
                ),
                $queryBuilder->expr()->eq(
                    'pid',
                    $queryBuilder->createNamedParameter(0, Connection::PARAM_INT),
                )
            )
            ->setMaxResults(1)
            ->executeQuery()
            ->fetchOne();

        return is_numeric($uid) ? (int)$uid : 0;
    }

    private function moveMemberToStorage(int $memberUid, int $storageFolderPid): void
    {
        $connection = $this->connectionPool->getConnectionForTable(self::MEMBER_TABLE);
        $connection->update(
            self::MEMBER_TABLE,
            [
                'pid' => $storageFolderPid,
                'tt_content_record' => 0,
            ],
            ['uid' => $memberUid],
        );
    }

    private function softDeleteDuplicate(int $memberUid): void
    {
        $connection = $this->connectionPool->getConnectionForTable(self::MEMBER_TABLE);
        $connection->update(
            self::MEMBER_TABLE,
            [
                'deleted' => 1,
                'tt_content_record' => 0,
            ],
            ['uid' => $memberUid],
        );
    }

    /**
     * @param array<int, int> $memberUids
     */
    private function writeMemberReferences(int $contentElementUid, array $memberUids): void
    {
        $connection = $this->connectionPool->getConnectionForTable(self::CONTENT_TABLE);
        $connection->update(
            self::CONTENT_TABLE,
            ['nessa_team_member_element' => implode(',', $memberUids)],
            ['uid' => $contentElementUid],
        );
    }

    private function countBoundMembers(): int
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::MEMBER_TABLE);
        $queryBuilder->getRestrictions()
            ->removeAll()
            ->add(new DeletedRestriction());

        $count = $queryBuilder
            ->count('uid')
            ->from(self::MEMBER_TABLE)
            ->where(
                $queryBuilder->expr()->gt(
                    'tt_content_record',
                    $queryBuilder->createNamedParameter(0, Connection::PARAM_INT),
                )
            )
            ->executeQuery()
            ->fetchOne();

        return is_numeric($count) ? (int)$count : 0;
    }
}
