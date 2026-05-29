<?php

declare(strict_types=1);

namespace StarterTeam\StarterNessa\Resource;

use Doctrine\DBAL\Exception;
use InvalidArgumentException;
use Override;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\FrontendRestrictionContainer;
use TYPO3\CMS\Core\Database\RelationHandler;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Resource\Exception\ResourceDoesNotExistException;
use TYPO3\CMS\Core\Schema\TcaSchemaFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * @extends AbstractRepository<Interrupter>
 */
class InterrupterRepository extends AbstractRepository
{
    protected const string REFERENCE_TABLE = 'tx_starternessa_interrupter_reference';

    protected string $objectType = Interrupter::class;

    protected string $table = 'tx_starternessa_interrupter';

    protected array $fields = ['uid', 'pid', 'internal_title', 'layout', 'header', 'teaser', 'link', 'link_text', 'assets', 'interval'];

    public function __construct(
        protected readonly ConnectionPool $connectionPool,
        protected ResourceFactory $factory,
        private readonly TcaSchemaFactory $tcaSchemaFactory,
    ) {
        parent::__construct($factory);
    }

    #[Override]
    protected function createDomainObject(array $databaseRow): Interrupter
    {
        return $this->factory->getInterrupterObject($databaseRow['uid'], $databaseRow);
    }

    public function findByRelation(string $tableName, string $fieldName, int $uid, ?int $workspaceId = null): array
    {
        $itemList = [];
        if (!MathUtility::canBeInterpretedAsInteger($uid)) {
            throw new InvalidArgumentException(
                'UID of related record has to be an integer. UID given: "' . $uid . '"',
                1316789798
            );
        }

        $referenceUids = [];
        if ($this->isFrontend()) {
            //$queryBuilder = $this->connectionPool
            //    ->getQueryBuilderForTable(self::REFERENCE_TABLE);
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
                ->getQueryBuilderForTable(self::REFERENCE_TABLE);
            #$queryBuilder->setRestrictions(GeneralUtility::makeInstance(FrontendRestrictionContainer::class));
            $queryBuilder
                ->select('uid')
                ->from(self::REFERENCE_TABLE)
                ->where(
                    $queryBuilder->expr()->eq(
                        'uid_foreign',
                        $queryBuilder->createNamedParameter($uid, Connection::PARAM_INT)
                    ),
                    $queryBuilder->expr()->eq(
                        'tablenames',
                        $queryBuilder->createNamedParameter($tableName, Connection::PARAM_STR)
                    ),
                    $queryBuilder->expr()->eq(
                        'fieldname',
                        $queryBuilder->createNamedParameter($fieldName, Connection::PARAM_STR)
                    )
                )
                ->orderBy('sorting_foreign')
                ;//->executeQuery();

            $sql = $queryBuilder->getSQL();
            $params = $queryBuilder->getParameters();
$res = $queryBuilder->executeQuery();
            while ($row = $res->fetchAssociative()) {
                $referenceUids[] = $row['uid'];
            }
        } else {
            $schema = $this->tcaSchemaFactory->get($tableName);
            $workspaceId ??= GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect('workspace', 'id', 0);
            $relationHandler = GeneralUtility::makeInstance(RelationHandler::class);
            $relationHandler->setWorkspaceId($workspaceId);
            $relationHandler->initializeForField(
                $tableName,
                $schema->getField($fieldName),
                $uid
            );

            if (!empty($relationHandler->tableArray[self::REFERENCE_TABLE])) {
                $relationHandler->processDeletePlaceholder();
                $referenceUids = $relationHandler->tableArray[self::REFERENCE_TABLE];
            }
        }

        if (!empty($referenceUids)) {
            foreach ($referenceUids as $referenceUid) {
                try {
                    // Just passing the reference uid, the factory is doing workspace
                    // overlays automatically depending on the current environment
                    $itemList[] = $this->factory->getInterrupterReferenceObject($referenceUid);
                } catch (ResourceDoesNotExistException) {
                    // No handling, just omit the invalid reference uid
                }
            }
            $itemList = $this->reapplySorting($itemList);
        }

        return $itemList;
    }

    public function findInterrupterReferenceByUid(int $uid): bool|InterrupterReference
    {
        if (!MathUtility::canBeInterpretedAsInteger($uid)) {
            throw new InvalidArgumentException(
                'The UID of record has to be an integer. UID given: "' . $uid . '"',
                1697118125
            );
        }

        try {
            $interrupterReferenceObject = $this->factory->getInterrupterReferenceObject($uid);
        } catch (InvalidArgumentException) {
            $interrupterReferenceObject = false;
        }

        return $interrupterReferenceObject;
    }

    /**
     * @throws Exception
     */
    public function findOneByUid(int $interrupterUid): false|array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable($this->table);

        $row = $queryBuilder
            ->select(...$this->fields)
            ->from($this->table)
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($interrupterUid, Connection::PARAM_INT))
            )
            ->executeQuery()
            ->fetchAssociative();

        return is_array($row) ? $row : false;
    }

    /**
     * As sorting might have changed due to workspace overlays, PHP does the sorting again.
     */
    protected function reapplySorting(array $itemList): array
    {
        uasort(
            $itemList,
            function (InterrupterReference $a, InterrupterReference $b) {
                $sortA = (int)$a->getReferenceProperty('sorting_foreign');
                $sortB = (int)$b->getReferenceProperty('sorting_foreign');

                if ($sortA === $sortB) {
                    return 0;
                }

                return $sortA <=> $sortB;
            }
        );
        return $itemList;
    }

    protected function isFrontend(): bool
    {
        if (($GLOBALS['TYPO3_REQUEST'] ?? null) instanceof ServerRequestInterface
            && ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isFrontend()
        ) {
            return true;
        }
        return false;
    }
}
