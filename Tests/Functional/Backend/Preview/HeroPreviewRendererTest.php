<?php

declare(strict_types=1);

namespace StarterTeam\StarterNessa\Tests\Functional\Backend\Preview;

use PHPUnit\Framework\Attributes\Test;
use StarterTeam\StarterNessa\Backend\Preview\HeroPreviewRenderer;
use TYPO3\CMS\Backend\View\Event\PageContentPreviewRenderingEvent;
use TYPO3\CMS\Backend\View\PageLayoutContext;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Domain\RecordFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

final class HeroPreviewRendererTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = ['starterteam/starter-nessa'];

    protected array $coreExtensionsToLoad = ['frontend', 'backend'];

    #[Test]
    public function listsSlideHeadersInSortingOrderSkippingEmptyAndDeleted(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/HeroElements.csv');

        $content = $this->renderPreviewFor(1);

        self::assertIsString($content);
        self::assertStringContainsString('First slide', $content);
        self::assertStringContainsString('Second slide', $content);
        // Sorting is by "sorting", so "First slide" (10) precedes "Second slide" (20).
        self::assertLessThan(
            (int)strpos($content, 'Second slide'),
            (int)strpos($content, 'First slide'),
        );
        // The empty-header and the deleted slide are filtered out — exactly two items.
        self::assertSame(2, substr_count($content, '<li>'));
        self::assertStringNotContainsString('Deleted slide', $content);
    }

    #[Test]
    public function showsEmptyNoticeWhenHeroHasNoSlides(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/HeroElements.csv');

        $content = $this->renderPreviewFor(2);

        self::assertIsString($content);
        self::assertStringContainsString('No slides yet', $content);
        self::assertStringNotContainsString('<li>', $content);
    }

    #[Test]
    public function ignoresContentElementsThatAreNotHeroes(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/HeroElements.csv');

        // Same record, but announced as a different CType — the listener must bail out.
        $content = $this->renderPreviewFor(1, 'textmedia');

        self::assertNull($content);
    }

    private function renderPreviewFor(int $uid, string $recordType = 'nessa_hero'): ?string
    {
        $row = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('tt_content')
            ->select(['*'], 'tt_content', ['uid' => $uid])
            ->fetchAssociative();
        self::assertIsArray($row);

        $record = GeneralUtility::makeInstance(RecordFactory::class)
            ->createFromDatabaseRow('tt_content', $row);

        $event = new PageContentPreviewRenderingEvent(
            'tt_content',
            $recordType,
            $record,
            self::createStub(PageLayoutContext::class),
        );

        GeneralUtility::makeInstance(HeroPreviewRenderer::class)($event);

        return $event->getPreviewContent();
    }
}
