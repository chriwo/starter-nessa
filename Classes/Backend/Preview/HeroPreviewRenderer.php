<?php

declare(strict_types=1);

namespace StarterTeam\StarterNessa\Backend\Preview;

use TYPO3\CMS\Backend\View\Event\PageContentPreviewRenderingEvent;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Renders a backend page-module preview for the nessa_hero content element.
 *
 * The slides live in the related tx_starternessa_hero_element table, so the
 * default preview shows nothing useful. This listener lists the slide headers
 * (in sorting order) so editors can tell the slides apart at a glance.
 */
#[AsEventListener('starter-nessa/hero-backend-preview')]
final class HeroPreviewRenderer
{
    private const string CTYPE = 'nessa_hero';

    private const string SLIDE_TABLE = 'tx_starternessa_hero_element';

    public function __construct(
        private readonly ConnectionPool $connectionPool,
        private readonly LanguageServiceFactory $languageServiceFactory,
    ) {
    }

    public function __invoke(PageContentPreviewRenderingEvent $event): void
    {
        if ($event->getTable() !== 'tt_content' || $event->getRecordType() !== self::CTYPE) {
            return;
        }

        $record = $event->getRecord();
        $languageValue = $record->toArray()['sys_language_uid'] ?? 0;
        $languageUid = is_numeric($languageValue) ? (int)$languageValue : 0;
        $headers = $this->fetchSlideHeaders($record->getUid(), $languageUid);

        $event->setPreviewContent($this->renderPreview($headers));
    }

    /**
     * @return list<string>
     */
    private function fetchSlideHeaders(int $parentUid, int $languageUid): array
    {
        if ($parentUid === 0) {
            return [];
        }

        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::SLIDE_TABLE);
        $queryBuilder->getRestrictions()
            ->removeAll()
            ->add(GeneralUtility::makeInstance(DeletedRestriction::class));

        $headers = $queryBuilder
            ->select('header')
            ->from(self::SLIDE_TABLE)
            ->where(
                $queryBuilder->expr()->eq(
                    'tt_content_record',
                    $queryBuilder->createNamedParameter($parentUid, Connection::PARAM_INT),
                ),
                $queryBuilder->expr()->eq(
                    'sys_language_uid',
                    $queryBuilder->createNamedParameter($languageUid, Connection::PARAM_INT),
                ),
            )
            ->orderBy('sorting')
            ->executeQuery()
            ->fetchFirstColumn();

        $normalised = array_map(
            static fn (mixed $value): string => is_string($value) ? trim($value) : '',
            $headers,
        );

        return array_values(array_filter($normalised, static fn (string $header): bool => $header !== ''));
    }

    /**
     * @param list<string> $headers
     */
    private function renderPreview(array $headers): string
    {
        $languageService = $this->languageServiceFactory->createForBackendUser();
        $label = 'LLL:EXT:starter_nessa/Resources/Private/Language/locallang_be.xlf:';

        if ($headers === []) {
            return '<p><em>' . htmlspecialchars($languageService->sL($label . 'hero.preview.empty')) . '</em></p>';
        }

        $items = '';
        foreach ($headers as $header) {
            $items .= '<li>' . htmlspecialchars($header) . '</li>';
        }

        return '<strong>' . htmlspecialchars($languageService->sL($label . 'hero.preview.slides')) . '</strong>'
            . '<ol>' . $items . '</ol>';
    }
}
