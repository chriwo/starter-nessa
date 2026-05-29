<?php

declare(strict_types=1);

namespace StarterTeam\StarterNessa\Resource;

use TYPO3\CMS\Core\Database\Connection;
use Doctrine\DBAL\Exception;
use Override;
use BadMethodCallException;
use InvalidArgumentException;
use RuntimeException;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\FrontendRestrictionContainer;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\RepositoryInterface;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Abstract repository implementing the basic repository methods
 *
 * @template T of object
 * @implements RepositoryInterface<T>
 */
abstract class AbstractRepository implements RepositoryInterface, SingletonInterface
{
    protected string $table = '';

    protected string $typeField = '';

    /**
     * The main object type of this class
     */
    protected string $objectType;

    /**
     * Creates this object.
     */
    public function __construct(
        protected ResourceFactory $factory,
    ) {
    }

    /**
     * Adds an object to this repository.
     *
     * @param object $object The object to add
     */
    #[Override]
    public function add($object): void
    {
    }

    /**
     * Removes an object from this repository.
     *
     * @param object $object The object to remove
     */
    #[Override]
    public function remove($object): void
    {
    }

    /**
     * Replaces an object by another.
     *
     * @param object $existingObject The existing object
     * @param object $newObject The new object
     */
    public function replace($existingObject, $newObject): void
    {
    }

    /**
     * Replaces an existing object with the same identifier by the given object
     *
     * @param object $modifiedObject The modified object
     */
    #[Override]
    public function update($modifiedObject): void
    {
    }

    /**
     * Returns all objects of this repository.
     *
     * @throws Exception
     */
    #[Override]
    public function findAll(): array
    {
        /**
        $items = [];
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($this->table);
        if ($this->getEnvironmentMode() === 'FE') {
            $queryBuilder->setRestrictions(GeneralUtility::makeInstance(FrontendRestrictionContainer::class));
        }
        $queryBuilder
            ->select('*')
            ->from($this->table);

        if (!empty($this->type)) {
            $queryBuilder->where(
                $queryBuilder->expr()->eq(
                    $this->typeField,
                    $queryBuilder->createNamedParameter($this->type, PDO::PARAM_STR)
                )
            );
        }
        $result = $queryBuilder->executeQuery();

        // fetch all records and create objects out of them
        while ($row = $result->fetchAssociative()) {
            $items[] = $this->createDomainObject($row);
        }
        return $items;
         * */
        return [];
    }

    /**
     * Creates an object managed by this repository.
     *
     * @abstract
     */
    abstract protected function createDomainObject(array $databaseRow): Interrupter;

    /**
     * Returns the total number objects of this repository.
     *
     * @return int The object count
     */
    #[Override]
    public function countAll(): int
    {
        return 0;
    }

    /**
     * Removes all objects of this repository as if remove() was called for
     * all of them.
     */
    #[Override]
    public function removeAll(): void
    {
    }

    /**
     * Finds an object matching the given identifier.
     *
     * @param int $uid The identifier of the object to find
     * @throws Exception
     */
    #[Override]
    public function findByUid($uid): Interrupter
    {
        if (!MathUtility::canBeInterpretedAsInteger($uid)) {
            throw new InvalidArgumentException('The UID has to be an integer. UID given: "' . $uid . '"', 1316779798);
        }
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($this->table);
        if ($this->getEnvironmentMode() === 'FE') {
            $queryBuilder->setRestrictions(GeneralUtility::makeInstance(FrontendRestrictionContainer::class));
        }
        $row = $queryBuilder
            ->select('*')
            ->from($this->table)
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($uid, Connection::PARAM_INT))
            )
            ->executeQuery()
            ->fetchOne();
        if (!is_array($row)) {
            throw new RuntimeException('Could not find row with UID "' . $uid . '" in table "' . $this->table . '"', 1314354065);
        }
        return $this->createDomainObject($row);
    }

    /**
     * Sets the property names to order the result by per default.
     * Expected like this:
     * array(
     * 'foo' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING,
     * 'bar' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING
     * )
     *
     * @param array $defaultOrderings The property names to order by
     * @throws BadMethodCallException
     */
    #[Override]
    public function setDefaultOrderings(array $defaultOrderings): void
    {
        throw new BadMethodCallException(
            'Repository does not support the setDefaultOrderings() method.',
            1313185906
        );
    }

    /**
     * Sets the default query settings to be used in this repository
     *
     * @param QuerySettingsInterface $defaultQuerySettings The query settings to be used by default
     * @throws BadMethodCallException
     */
    #[Override]
    public function setDefaultQuerySettings(QuerySettingsInterface $defaultQuerySettings): void
    {
        throw new BadMethodCallException(
            'Repository does not support the setDefaultQuerySettings() method.',
            1313185907
        );
    }

    /**
     * Returns a query for objects of this repository
     *
     * @throws BadMethodCallException
     */
    #[Override]
    public function createQuery(): QueryInterface
    {
        throw new BadMethodCallException(
            'Repository does not support the createQuery() method.',
            1313185908
        );
    }

    /**
     * Finds an object matching the given identifier.
     *
     * @param mixed $identifier The identifier of the object to find
     */
    #[Override]
    public function findByIdentifier(mixed $identifier): Interrupter
    {
        if (!is_int($identifier)) {
            throw new InvalidArgumentException('The identifier has to be an integer. Identifier given: "' . $identifier . '"', 1316579798);
        }
        return $this->findByUid($identifier);
    }

    /**
     * Magic call method for repository methods.
     *
     * @throws BadMethodCallException
     * @internal
     */
    public function __call(string $method, array $arguments): void
    {
        throw new BadMethodCallException(
            'Repository method "' . $method . '" is not implemented.',
            1378918410
        );
    }

    /**
     * Returns the object type this repository is managing.
     */
    public function getEntityClassName(): string
    {
        return $this->objectType;
    }

    /**
     * Function to return the current TYPO3_MODE based on $GLOBALS['TSFE'].
     * This function can be mocked in unit tests to be able to test frontend behaviour.
     */
    protected function getEnvironmentMode(): string
    {
        return ($GLOBALS['TSFE'] ?? null) instanceof TypoScriptFrontendController ? 'FE' : 'BE';
    }
}
