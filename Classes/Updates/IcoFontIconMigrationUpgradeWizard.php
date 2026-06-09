<?php

declare(strict_types=1);

namespace StarterTeam\StarterNessa\Updates;

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

#[UpgradeWizard(self::class)]
class IcoFontIconMigrationUpgradeWizard implements UpgradeWizardInterface
{
    private const array TABLES = [
        'tx_starternessa_social_element',
        'tx_starternessa_teaser_element',
    ];

    /**
     * Complete mapping of all IcoFont icon identifiers to Bootstrap Icons (bi-*)
     * or project-specific custom SVG identifiers (custom-*).
     */
    private const array ICON_MAP = [
        // UI / navigation icons
        'icofont-arrow-right'            => 'bi-arrow-right',
        'icofont-check'                  => 'bi-check',
        'icofont-check-alt'              => 'bi-check-lg',
        'icofont-check-circled'          => 'bi-check-circle',
        'icofont-checked'                => 'bi-check-square-fill',
        'icofont-close'                  => 'bi-x-lg',
        'icofont-download'               => 'bi-download',
        'icofont-navigation-menu'        => 'bi-list',
        'icofont-plus'                   => 'bi-plus-lg',
        'icofont-rounded-down'           => 'bi-chevron-compact-down',
        'icofont-rounded-left'           => 'bi-chevron-compact-left',
        'icofont-rounded-right'          => 'bi-chevron-compact-right',
        'icofont-rounded-up'             => 'bi-chevron-compact-up',
        'icofont-simple-down'            => 'bi-chevron-down',
        'icofont-simple-left'            => 'bi-chevron-left',
        'icofont-simple-right'           => 'bi-chevron-right',
        'icofont-simple-up'              => 'bi-chevron-up',
        'icofont-verification-check'     => 'bi-patch-check-fill',
        'icofont-world'                  => 'bi-globe',

        // Contact / social icons
        'icofont-discord'                => 'bi-discord',
        'icofont-email'                  => 'bi-envelope',
        'icofont-envelope'               => 'bi-envelope',
        'icofont-facebook'               => 'bi-facebook',
        'icofont-github'                 => 'bi-github',
        'icofont-instagram'              => 'bi-instagram',
        'icofont-link'                   => 'bi-link-45deg',
        'icofont-linkedin'               => 'bi-linkedin',
        'icofont-phone'                  => 'bi-telephone',
        'icofont-snapchat'               => 'bi-snapchat',
        'icofont-tiktok'                 => 'bi-tiktok',
        'icofont-vimeo'                  => 'bi-vimeo',
        'icofont-x'                      => 'bi-twitter-x',
        'icofont-youtube'                => 'bi-youtube',

        // Custom SVGs (no Bootstrap Icons equivalent)
        'icofont-typo3'                  => 'custom-typo3',
        'icofont-xing'                   => 'custom-xing',
        'icofont-youku'                  => 'custom-youku',

        // CE Teaser Icon
        'icofont-bathtub'                => 'custom-bathtub',
        'icofont-beverage'               => 'bi-cup-hot',
        'icofont-flora-flower'           => 'custom-flora-flower',
        'icofont-water-drop'             => 'bi-droplet',

        // Full IcoFont library — all 113 icons
        'icofont-access-levels'          => 'bi-person-lock',
        'icofont-accessibility'          => 'bi-universal-access',
        'icofont-address'                => 'bi-house',
        'icofont-architecture-alt'       => 'bi-building',
        'icofont-archive'                => 'bi-archive',
        'icofont-award'                  => 'bi-award',
        'icofont-bank-alt'               => 'bi-bank',
        'icofont-batch'                  => 'bi-stack',
        'icofont-bird'                   => 'custom-bird',
        'icofont-book'                   => 'bi-book',
        'icofont-brain-alt'              => 'custom-brain',
        'icofont-brand-android-robot'    => 'bi-android2',
        'icofont-brand-apple'            => 'bi-apple',
        'icofont-brand-linux'            => 'custom-linux',
        'icofont-brand-whatsapp'         => 'bi-whatsapp',
        'icofont-brand-youtube'          => 'bi-youtube',
        'icofont-briefcase-2'            => 'bi-briefcase',
        'icofont-briefcase-alt'          => 'bi-briefcase-fill',
        'icofont-broadcast'              => 'bi-broadcast',
        'icofont-bug'                    => 'bi-bug',
        'icofont-business-man-alt-1'     => 'bi-person-badge',
        'icofont-businessman'            => 'bi-person-badge',
        'icofont-businesswoman'          => 'bi-person-dress',
        'icofont-calculations'           => 'bi-calculator',
        'icofont-calculator'             => 'bi-calculator',
        'icofont-calendar'               => 'bi-calendar',
        'icofont-camera'                 => 'bi-camera',
        'icofont-cement-mix'             => 'custom-cement-mix',
        'icofont-chart'                  => 'bi-bar-chart',
        'icofont-chart-histogram'        => 'bi-bar-chart-fill',
        'icofont-cogs'                   => 'bi-gear-wide-connected',
        'icofont-compass'                => 'bi-compass',
        'icofont-components'             => 'bi-puzzle',
        'icofont-contacts'               => 'bi-person-lines-fill',
        'icofont-contacts-alt'           => 'bi-journal-text',
        'icofont-content'                => 'bi-file-text',
        'icofont-contract-alt'           => 'bi-file-earmark-text',
        'icofont-crown'                  => 'bi-crown',
        'icofont-dart'                   => 'bi-bullseye',
        'icofont-disabled'               => 'bi-slash-circle',
        'icofont-engineer'               => 'bi-person-gear',
        'icofont-exit'                   => 'bi-box-arrow-right',
        'icofont-expand-full'            => 'bi-fullscreen',
        'icofont-eye'                    => 'bi-eye',
        'icofont-eye-open'               => 'bi-eye-fill',
        'icofont-field-group'            => 'bi-input-cursor-text',
        'icofont-finger-print'           => 'bi-fingerprint',
        'icofont-first-aid'              => 'bi-bandaid',
        'icofont-focus'                  => 'bi-crosshair',
        'icofont-folder-open'            => 'bi-folder2-open',
        'icofont-fox-alt'                => 'custom-fox',
        'icofont-gift'                   => 'bi-gift',
        'icofont-girl-alt'               => 'bi-person-dress',
        'icofont-groups'                 => 'bi-people',
        'icofont-hand-power'             => 'bi-lightning-charge-fill',
        'icofont-headphone-alt'          => 'bi-headphones',
        'icofont-home'                   => 'bi-house',
        'icofont-hotel'                  => 'bi-building',
        'icofont-hotel-boy-alt'          => 'custom-hotel-boy',
        'icofont-id-card'                => 'bi-person-vcard',
        'icofont-info-circle'            => 'bi-info-circle',
        'icofont-info-square'            => 'bi-info-square',
        'icofont-laptop'                 => 'bi-laptop',
        'icofont-law'                    => 'bi-bank',
        'icofont-learn'                  => 'bi-mortarboard',
        'icofont-license'                => 'bi-file-earmark-check',
        'icofont-life-bouy'              => 'bi-life-preserver',
        'icofont-list-thin'              => 'bi-list',
        'icofont-location'               => 'bi-geo-alt',
        'icofont-loop'                   => 'bi-arrow-repeat',
        'icofont-manage'                 => 'bi-sliders',
        'icofont-mega-phone'             => 'bi-megaphone',
        'icofont-menu'                   => 'bi-list',
        'icofont-modules'                => 'bi-grid-3x3-gap',
        'icofont-move'                   => 'bi-arrows-move',
        'icofont-music'                  => 'bi-music-note',
        'icofont-paw'                    => 'bi-paw',
        'icofont-pencil'                 => 'bi-pencil',
        'icofont-pictures'               => 'bi-images',
        'icofont-pin'                    => 'bi-pin',
        'icofont-plugins'                => 'bi-puzzle-fill',
        'icofont-print'                  => 'bi-printer',
        'icofont-publish'                => 'bi-cloud-upload',
        'icofont-purge'                  => 'bi-trash3',
        'icofont-rebuild'                => 'bi-arrow-counterclockwise',
        'icofont-sand-clock'             => 'bi-hourglass',
        'icofont-save'                   => 'bi-floppy',
        'icofont-screen'                 => 'bi-display',
        'icofont-shield'                 => 'bi-shield',
        'icofont-shield-alt'             => 'bi-shield-fill',
        'icofont-skull-face'             => 'custom-skull',
        'icofont-space-shuttle'          => 'bi-rocket',
        'icofont-support'                => 'bi-headset',
        'icofont-table'                  => 'bi-table',
        'icofont-tag-double'             => 'bi-tags',
        'icofont-thumbs-down'            => 'bi-hand-thumbs-down',
        'icofont-thumbs-up'              => 'bi-hand-thumbs-up',
        'icofont-ui-laoding'             => 'bi-arrow-clockwise',
        'icofont-ui-rate-blank'          => 'bi-star',
        'icofont-ui-search'              => 'bi-search',
        'icofont-under-construction-alt' => 'bi-cone-striped',
        'icofont-user'                   => 'bi-person',
        'icofont-users'                  => 'bi-people',
        'icofont-vcard'                  => 'bi-person-vcard',
        'icofont-video-alt'              => 'bi-camera-video',
        'icofont-worker'                 => 'bi-person-gear',
        'icofont-wrench'                 => 'bi-wrench',
    ];

    public function __construct(
        private readonly ConnectionPool $connectionPool,
    ) {
    }

    #[\Override]
    public function getTitle(): string
    {
        return 'EXT:starter_nessa: Migrate IcoFont icon identifiers to Bootstrap Icons';
    }

    #[\Override]
    public function getDescription(): string
    {
        $count = $this->countRecordsToMigrate();
        return sprintf(
            'Migrates all icofont-* icon values in social and teaser element records '
            . 'to the new Bootstrap Icons (bi-*) or custom SVG (custom-*) identifiers. '
            . 'Records to update: %d.',
            $count
        );
    }

    #[\Override]
    public function executeUpdate(): bool
    {
        foreach (self::TABLES as $table) {
            $this->migrateTable($table);
        }
        return true;
    }

    #[\Override]
    public function updateNecessary(): bool
    {
        return $this->countRecordsToMigrate() > 0;
    }

    #[\Override]
    public function getPrerequisites(): array
    {
        return [
            DatabaseUpdatedPrerequisite::class,
        ];
    }

    private function countRecordsToMigrate(): int
    {
        $total = 0;
        foreach (self::TABLES as $table) {
            $queryBuilder = $this->connectionPool->getQueryBuilderForTable($table);
            $queryBuilder->getRestrictions()->removeAll();
            $count = $queryBuilder
                ->count('uid')
                ->from($table)
                ->where(
                    $queryBuilder->expr()->like(
                        'icon',
                        $queryBuilder->createNamedParameter('icofont-%')
                    )
                )
                ->executeQuery()
                ->fetchOne();
            $total += is_numeric($count) ? (int)$count : 0;
        }
        return $total;
    }

    private function migrateTable(string $table): void
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable($table);
        $queryBuilder->getRestrictions()->removeAll();
        $rows = $queryBuilder
            ->select('uid', 'icon')
            ->from($table)
            ->where(
                $queryBuilder->expr()->like(
                    'icon',
                    $queryBuilder->createNamedParameter('icofont-%')
                )
            )
            ->executeQuery()
            ->fetchAllAssociative();

        foreach ($rows as $row) {
            $currentIcon = is_string($row['icon']) ? $row['icon'] : '';
            $newIcon = self::ICON_MAP[$currentIcon] ?? null;
            if ($newIcon === null) {
                continue;
            }
            $uid = is_numeric($row['uid']) ? (int)$row['uid'] : 0;
            if ($uid === 0) {
                continue;
            }
            $updateQueryBuilder = $this->connectionPool->getQueryBuilderForTable($table);
            $updateQueryBuilder
                ->update($table)
                ->set('icon', $newIcon)
                ->where(
                    $updateQueryBuilder->expr()->eq(
                        'uid',
                        $updateQueryBuilder->createNamedParameter($uid, Connection::PARAM_INT)
                    )
                )
                ->executeStatement();
        }
    }
}
