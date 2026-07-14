<?php

declare(strict_types=1);

namespace StarterTeam\StarterNessa\DataProcessing;

use Doctrine\DBAL\Exception;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\FrontendRestrictionContainer;
use TYPO3\CMS\Core\Domain\RecordFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\StringUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;

/**
 * Resolves the reusable team member records referenced by a "nessa_team"
 * content element (TCA type=group, CSV of tx_starternessa_team_element uids)
 * into fully resolved core records:
 *
 *   {teamMembers} = [Record, Record, ...]
 *
 * Each record is produced by {@see RecordFactory}, which resolves the relations
 * declared in TCA on its own: the "assets" FAL image becomes a FileReference and
 * the inline "nessa_social_element" links become a collection of sub-records.
 * The Fluid template therefore reads member fields and relations straight off the
 * record ({teamMember.realname}, {teamMember.assets}, {teamMember.nessa_social_element}).
 *
 * Members are returned in the order they are referenced in the group field.
 * Deleted and hidden members (and their timing/access restrictions) are respected
 * via the frontend restriction container, so a dangling reference to a removed
 * record is silently skipped.
 *
 * TypoScript:
 *
 *   10 = StarterTeam\StarterNessa\DataProcessing\TeamMembersProcessor
 *   10 {
 *     fieldName = nessa_team_member_element
 *     as = teamMembers
 *   }
 */
final readonly class TeamMembersProcessor implements DataProcessorInterface
{
    private const string MEMBER_TABLE = 'tx_starternessa_team_element';

    private const string DEFAULT_FIELD_NAME = 'nessa_team_member_element';

    private const string DEFAULT_TARGET_VARIABLE_NAME = 'teamMembers';

    public function __construct(
        private ConnectionPool $connectionPool,
        private RecordFactory $recordFactory,
    ) {
    }

    /**
     * @param array<string, mixed> $contentObjectConfiguration
     * @param array<string, mixed> $processorConfiguration
     * @param array<string, mixed> $processedData
     * @return array<string, mixed>
     * @throws Exception
     */
    #[\Override]
    public function process(
        ContentObjectRenderer $cObj,
        array $contentObjectConfiguration,
        array $processorConfiguration,
        array $processedData,
    ): array {
        $fieldName = $this->stringConfig($cObj, $processorConfiguration, 'fieldName', self::DEFAULT_FIELD_NAME);
        $targetVariableName = $this->stringConfig($cObj, $processorConfiguration, 'as', self::DEFAULT_TARGET_VARIABLE_NAME);

        $data = is_array($processedData['data'] ?? null) ? $processedData['data'] : [];
        $memberUids = $this->parseUidList(StringUtility::cast($data[$fieldName] ?? null) ?? '');

        $memberRows = $memberUids === [] ? [] : $this->fetchMembers($memberUids);

        $teamMembers = [];
        foreach ($memberUids as $memberUid) {
            if (isset($memberRows[$memberUid])) {
                $teamMembers[] = $this->recordFactory->createResolvedRecordFromDatabaseRow(
                    self::MEMBER_TABLE,
                    $memberRows[$memberUid],
                );
            }
        }

        $processedData[$targetVariableName] = $teamMembers;

        return $processedData;
    }

    /**
     * Fetches the referenced member records keyed by uid, honouring the
     * frontend enable-field restrictions.
     *
     * @param array<int, int> $uids
     * @return array<int, array<string, mixed>>
     * @throws Exception
     */
    private function fetchMembers(array $uids): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::MEMBER_TABLE);
        $queryBuilder->getRestrictions()
            ->removeAll()
            ->add(GeneralUtility::makeInstance(FrontendRestrictionContainer::class));

        $rows = $queryBuilder
            ->select('*')
            ->from(self::MEMBER_TABLE)
            ->where(
                $queryBuilder->expr()->in(
                    'uid',
                    $queryBuilder->createNamedParameter($uids, Connection::PARAM_INT_ARRAY),
                )
            )
            ->executeQuery()
            ->fetchAllAssociative();

        $result = [];
        foreach ($rows as $row) {
            $uid = is_numeric($row['uid']) ? (int)$row['uid'] : 0;
            if ($uid > 0) {
                $result[$uid] = $row;
            }
        }

        return $result;
    }

    /**
     * Extracts the ordered list of member uids from a TCA type=group value.
     * A group value is a CSV of uids; a "table_uid" prefix (used when a group
     * allows multiple tables) is tolerated by only reading the trailing number.
     *
     * @return array<int, int>
     */
    private function parseUidList(string $value): array
    {
        $uids = [];
        foreach (GeneralUtility::trimExplode(',', $value, true) as $item) {
            if (preg_match('/(\d+)$/', $item, $matches) === 1) {
                $uid = (int)$matches[1];
                if ($uid > 0) {
                    $uids[] = $uid;
                }
            }
        }

        return $uids;
    }

    /**
     * @param array<string, mixed> $processorConfiguration
     */
    private function stringConfig(
        ContentObjectRenderer $cObj,
        array $processorConfiguration,
        string $key,
        string $default,
    ): string {
        $value = StringUtility::cast($cObj->stdWrapValue($key, $processorConfiguration, $default));

        return ($value === null || $value === '') ? $default : $value;
    }
}
