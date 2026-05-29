<?php

declare(strict_types=1);

namespace StarterTeam\StarterNessa\Resource;

use Doctrine\DBAL\Driver\Exception;
use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use StarterTeam\StarterNessa\Resource\Exception\InterrupterDoesNotExistException;
use Symfony\Component\PropertyAccess\Exception\InvalidPropertyPathException;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ResourceFactory implements SingletonInterface
{
    /**
     * @var Interrupter[]
     */
    protected array $interrupterInstances = [];

    /**
     * @var InterrupterReference[]
     */
    protected array $interrupterReferenceInstances = [];

    public function __construct(
        protected readonly ConnectionPool $connectionPool,
    ) {
    }

    public function getInterrupterReferenceObject(int $uid, array $interrupterReferenceData = [], bool $raw = false): InterrupterReference
    {
        if (!is_numeric($uid)) {
            throw new InvalidArgumentException(
                'the reference UID for the interrupter (tx_starternessa_interrupter_reference) has to be numeric. UID given: "' . $uid . '"',
                1697719112
            );
        }

        if (!isset($this->interrupterReferenceInstances[$uid])) {
            if (empty($interrupterReferenceData)) {
                $interrupterReferenceData = $this->getInterrupterReferenceData($uid, $raw);
                if (!is_array($interrupterReferenceData)) {
                    throw new InterrupterDoesNotExistException(
                        'No interrupter reference (tx_starternessa_interrupter_reference) was found for given UID: "' . $uid . '"',
                        1697719317
                    );
                }
            }

            $this->interrupterReferenceInstances[$uid] = $this->createInterrupterReferenceObject($interrupterReferenceData);
        }

        return $this->interrupterReferenceInstances[$uid];
    }

    public function createInterrupterObject(array $interrupterData): Interrupter
    {
        return GeneralUtility::makeInstance(Interrupter::class, $interrupterData);
    }

    public function createInterrupterReferenceObject(array $interrupterReferenceData): InterrupterReference
    {
        return GeneralUtility::makeInstance(InterrupterReference::class, $interrupterReferenceData, $this);
    }

    /**
     * @throws InterrupterDoesNotExistException
     * @throws Exception
     */
    public function getInterrupterObject(int $uid, array $interrupterData = []): Interrupter
    {
        if (!is_numeric($uid)) {
            throw new InvalidArgumentException('The UID of interrupter has to be numeric. UID given: "' . $uid . '"', 1697706802);
        }

        if (empty($this->interrupterInstances[$uid])) {
            if (empty($interrupterData)) {
                $interrupterData = $this->getInterrupterRepository()->findOneByUid($uid);
                if ($interrupterData === false) {
                    throw new InterrupterDoesNotExistException('No interrupter found for given UID: ' . $uid, 1697706997);
                }
            }
            $this->interrupterInstances[$uid] = $this->createInterrupterObject($interrupterData);
        }

        return $this->interrupterInstances[$uid];
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function getInterrupterReferenceData(int $uid, bool $raw = false): ?array
    {
        $request = $GLOBALS['TYPO3_REQUEST'] ?? null;
        if (!$raw
            && $request instanceof ServerRequestInterface
            && ApplicationType::fromRequest($request)->isBackend()
        ) {
            $interrupterReferenceData = BackendUtility::getRecordWSOL('tx_starternessa_interrupter_reference', $uid);
        } elseif (!$raw
            && $request instanceof ServerRequestInterface
            && ApplicationType::fromRequest($request)->isFrontend()
        ) {
            //if (!property_exists($GLOBALS['TSFE'], 'sys_page')) {
            //    throw new InvalidPropertyPathException(
            //        'Property "sys_page" does not exist in "$GLOBALS[\'TSFE\']"',
            //        1726744162
            //    );
            //}
            $interrupterReferenceData = GeneralUtility::makeInstance(PageRepository::class)->checkRecord('tx_starternessa_interrupter_reference', $uid);
        } else {
            $queryBuilder = $this->connectionPool
                ->getQueryBuilderForTable('tx_starternessa_interrupter_reference');

            $queryBuilder
                ->getRestrictions()
                ->removeAll()
                ->add(GeneralUtility::makeInstance(DeletedRestriction::class));

            $interrupterReferenceData = $queryBuilder
                ->select('*')
                ->from('tx_starternessa_interrupter_reference')
                ->where(
                    $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($uid, Connection::PARAM_INT))
                )
                ->executeQuery()
                ->fetchAllAssociative();
        }

        return $interrupterReferenceData;
    }

    protected function getInterrupterRepository(): InterrupterRepository
    {
        return GeneralUtility::makeInstance(InterrupterRepository::class);
    }
}
