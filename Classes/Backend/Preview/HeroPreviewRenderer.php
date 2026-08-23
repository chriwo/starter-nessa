<?php

declare(strict_types=1);

namespace StarterTeam\StarterNessa\Backend\Preview;

use TYPO3\CMS\Backend\View\Event\PageContentPreviewRenderingEvent;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Authentication\AbstractUserAuthentication;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Imaging\IconSize;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Renders a backend page-module preview for the nessa_hero content element.
 *
 * The slides live in the related tx_starternessa_hero_element table, so the
 * default preview shows nothing useful. This listener lists the slide headers
 * (in sorting order) so editors can tell the slides apart at a glance.
 *
 * Disabled or time-restricted slides are listed as well and flagged with the
 * core record icon (which carries the matching overlay) plus a muted row, so
 * the preview mirrors how the page module represents hidden content.
 */
#[AsEventListener('starter-nessa/hero-backend-preview')]
final class HeroPreviewRenderer
{
    private const string CTYPE = 'nessa_hero';

    private const string SLIDE_TABLE = 'tx_starternessa_hero_element';

    public function __construct(
        private readonly ConnectionPool $connectionPool,
        private readonly LanguageServiceFactory $languageServiceFactory,
        private readonly IconFactory $iconFactory,
        private readonly Context $context,
    ) {
    }

    public function __invoke(PageContentPreviewRenderingEvent $event): void
    {
        if ($event->getTable() !== 'tt_content' || $event->getRecordType() !== self::CTYPE) {
            return;
        }

        $record = $event->getRecord();
        $languageUid = $this->toInt($this->recordField($record, 'sys_language_uid'));

        // In connected mode the slides stay attached to the default-language
        // element, so a translation must resolve them via its l18n_parent.
        $translationParent = $this->toInt($this->recordField($record, 'l18n_parent'));
        $parentUid = $translationParent > 0
            ? $translationParent
            : $this->toInt($this->recordField($record, 'uid'));

        $slides = $this->fetchSlides($parentUid, $languageUid);

        $event->setPreviewContent($this->renderPreview($slides));
    }

    /**
     * Reads a single field from the record the event carries.
     *
     * TYPO3 v13 hands over the plain database row, v14 a RecordInterface whose
     * raw record still holds the untranslated system fields. The lookups stay
     * duck-typed on purpose: a version switch would reference an API that does
     * not exist in the other core version and break static analysis there.
     */
    private function recordField(mixed $record, string $field): mixed
    {
        if (is_array($record)) {
            return $record[$field] ?? null;
        }

        if (!is_object($record)) {
            return null;
        }

        if ($field === 'uid' && method_exists($record, 'getUid')) {
            return $record->getUid();
        }

        if (!method_exists($record, 'getRawRecord')) {
            return null;
        }

        $rawRecord = $record->getRawRecord();

        return is_object($rawRecord) && method_exists($rawRecord, 'get')
            ? $rawRecord->get($field)
            : null;
    }

    private function toInt(mixed $value): int
    {
        return is_numeric($value) ? (int)$value : 0;
    }

    /**
     * @return list<array{uid: int, header: string, hidden: int, starttime: int, endtime: int}>
     */
    private function fetchSlides(int $parentUid, int $languageUid): array
    {
        if ($parentUid === 0) {
            return [];
        }

        // Prefer the requested language, but fall back to the default language
        // (0) so heroes whose slides were never translated still list them —
        // mirroring the overlay the frontend DatabaseQueryProcessor applies.
        foreach (array_unique([$languageUid, 0]) as $language) {
            $slides = $this->querySlides($parentUid, $language);
            if ($slides !== []) {
                return $slides;
            }
        }

        return [];
    }

    /**
     * @return list<array{uid: int, header: string, hidden: int, starttime: int, endtime: int}>
     */
    private function querySlides(int $parentUid, int $languageUid): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::SLIDE_TABLE);
        // Keep disabled/time-restricted slides in the result; only deleted rows
        // are dropped. The preview flags the disabled ones instead of hiding
        // them, matching the core page module.
        $queryBuilder->getRestrictions()
            ->removeAll()
            ->add(GeneralUtility::makeInstance(DeletedRestriction::class));

        $rows = $queryBuilder
            ->select('uid', 'header', 'hidden', 'starttime', 'endtime')
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
            ->fetchAllAssociative();

        return array_map(
            fn(array $row): array => [
                'uid' => $this->toInt($row['uid']),
                'header' => is_string($row['header']) ? trim($row['header']) : '',
                'hidden' => $this->toInt($row['hidden']),
                'starttime' => $this->toInt($row['starttime']),
                'endtime' => $this->toInt($row['endtime']),
            ],
            $rows,
        );
    }

    /**
     * @param list<array{uid: int, header: string, hidden: int, starttime: int, endtime: int}> $slides
     */
    private function renderPreview(array $slides): string
    {
        // createForBackendUser() only exists since v14 and is marked @internal;
        // it does exactly this, and createFromUserPreferences() works on both.
        $backendUser = $GLOBALS['BE_USER'] ?? null;
        $languageService = $this->languageServiceFactory->createFromUserPreferences(
            $backendUser instanceof AbstractUserAuthentication ? $backendUser : null,
        );
        $label = 'LLL:EXT:starter_nessa/Resources/Private/Language/locallang_be.xlf:';

        if ($slides === []) {
            return '<p><em>' . htmlspecialchars($languageService->sL($label . 'hero.preview.empty')) . '</em></p>';
        }

        $now = $this->toInt($this->context->getPropertyFromAspect('date', 'timestamp') ?? time());

        $items = '';
        foreach ($slides as $slide) {
            // getIconForRecord evaluates the table's enablecolumns and adds the
            // matching hidden/schedule overlay to the record icon itself.
            $icon = $this->iconFactory
                ->getIconForRecord(self::SLIDE_TABLE, $slide, IconSize::SMALL)
                ->render();

            $header = $slide['header'] !== ''
                ? htmlspecialchars($slide['header'])
                : '<em>' . htmlspecialchars($languageService->sL($label . 'hero.preview.untitled')) . '</em>';

            $disabled = $slide['hidden'] !== 0
                || ($slide['starttime'] !== 0 && $slide['starttime'] > $now)
                || ($slide['endtime'] !== 0 && $slide['endtime'] < $now);

            $items .= '<li' . ($disabled ? ' class="text-muted"' : '') . '>'
                . $icon . ' ' . $header . '</li>';
        }

        return '<strong>' . htmlspecialchars($languageService->sL($label . 'hero.preview.slides')) . '</strong>'
            . '<ol>' . $items . '</ol>';
    }
}
