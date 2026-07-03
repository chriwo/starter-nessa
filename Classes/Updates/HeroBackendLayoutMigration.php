<?php

declare(strict_types=1);

namespace StarterTeam\StarterNessa\Updates;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

/**
 * The dedicated "Hero" backend layout was removed; its hero column (colPos 1) is
 * now part of the Default backend layout. This wizard switches all pages that
 * still reference the old Hero layout to the Default layout so their hero content
 * keeps rendering in the correct column.
 */
#[UpgradeWizard(self::class)]
class HeroBackendLayoutMigration implements UpgradeWizardInterface
{
    private const string TABLE_NAME = 'pages';

    private const string OLD_LAYOUT = 'pagets__HeroLayout';

    private const string NEW_LAYOUT = 'pagets__DefaultLayout';

    /**
     * @var array<int, string>
     */
    private const array LAYOUT_FIELDS = [
        'backend_layout',
        'backend_layout_next_level',
    ];

    public function __construct(
        private readonly ConnectionPool $connectionPool,
    ) {
    }

    #[\Override]
    public function getTitle(): string
    {
        return 'EXT:starter_nessa: Migrate the Hero backend layout to the Default backend layout.';
    }

    #[\Override]
    public function getDescription(): string
    {
        return sprintf(
            'The dedicated Hero backend layout was merged into the Default backend layout. '
            . 'This wizard updates all pages still using "%s" to "%s". Affected pages: %d',
            self::OLD_LAYOUT,
            self::NEW_LAYOUT,
            $this->countAffectedRows(),
        );
    }

    #[\Override]
    public function executeUpdate(): bool
    {
        foreach (self::LAYOUT_FIELDS as $field) {
            $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLE_NAME);
            $queryBuilder->getRestrictions()->removeAll();
            $queryBuilder
                ->update(self::TABLE_NAME)
                ->set($field, self::NEW_LAYOUT)
                ->where(
                    $queryBuilder->expr()->eq(
                        $field,
                        $queryBuilder->createNamedParameter(self::OLD_LAYOUT)
                    )
                )
                ->executeStatement();
        }

        return true;
    }

    #[\Override]
    public function updateNecessary(): bool
    {
        return $this->countAffectedRows() > 0;
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

    private function countAffectedRows(): int
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLE_NAME);
        $queryBuilder->getRestrictions()->removeAll();

        $orConstraints = [];
        foreach (self::LAYOUT_FIELDS as $field) {
            $orConstraints[] = $queryBuilder->expr()->eq(
                $field,
                $queryBuilder->createNamedParameter(self::OLD_LAYOUT)
            );
        }

        $count = $queryBuilder
            ->count('uid')
            ->from(self::TABLE_NAME)
            ->where($queryBuilder->expr()->or(...$orConstraints))
            ->executeQuery()
            ->fetchOne();

        return is_numeric($count) ? (int)$count : 0;
    }
}
