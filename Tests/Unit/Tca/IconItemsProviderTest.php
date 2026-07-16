<?php

declare(strict_types=1);

namespace StarterTeam\StarterNessa\Tests\Unit\Tca;

use PHPUnit\Framework\Attributes\Test;
use StarterTeam\StarterNessa\Service\IconRegistry;
use StarterTeam\StarterNessa\Tca\IconItemsProvider;
use TYPO3\CMS\Core\Settings\Settings;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteSettings;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class IconItemsProviderTest extends UnitTestCase
{
    private string $fixtureDirectory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fixtureDirectory = dirname(__DIR__) . '/Fixtures/Icons/';
    }

    /**
     * @param array<string, mixed> $iconSettings
     */
    private function siteWithIconSettings(array $iconSettings): Site
    {
        $settings = SiteSettings::create(new Settings($iconSettings));

        return new Site('test', 1, [], $settings);
    }

    #[Test]
    public function populateAppendsOneItemPerIconAndKeepsExistingEmptyItem(): void
    {
        $subject = new IconItemsProvider(new IconRegistry());

        $emptyItem = [
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.default_value',
            'value' => '',
        ];
        $params = [
            'items' => [$emptyItem],
            'site' => $this->siteWithIconSettings([
                'starter.icons.directories' => [$this->fixtureDirectory],
            ]),
        ];

        $subject->populate($params);

        $items = $params['items'];
        self::assertIsArray($items);

        // The four fixture icons are appended after the untouched empty item.
        self::assertSame($emptyItem, $items[0]);
        self::assertCount(5, $items);

        $appended = array_slice($items, 1);
        foreach ($appended as $item) {
            self::assertIsArray($item);
            self::assertSame(['label', 'value'], array_keys($item));
        }

        $byValue = array_column($appended, 'label', 'value');
        self::assertSame('Discord', $byValue['bi-discord']);
        self::assertSame('TYPO3', $byValue['custom-typo3']);
        self::assertSame('Twitter / X', $byValue['bi-twitter-x']);
    }

    #[Test]
    public function populateSortsItemsAlphabeticallyByLabel(): void
    {
        $subject = new IconItemsProvider(new IconRegistry());

        $params = [
            'items' => [],
            'site' => $this->siteWithIconSettings([
                'starter.icons.directories' => [$this->fixtureDirectory],
            ]),
        ];

        $subject->populate($params);

        $items = $params['items'];
        self::assertIsArray($items);

        $labels = array_column($items, 'label');
        // Fixture icons sorted case-insensitively by their derived label.
        self::assertSame(['Cement Mix', 'Discord', 'Twitter / X', 'TYPO3'], $labels);
    }

    #[Test]
    public function populateScansSeveralDirectoriesInOrder(): void
    {
        $subject = new IconItemsProvider(new IconRegistry());

        $params = [
            'items' => [],
            'site' => $this->siteWithIconSettings([
                'starter.icons.directories' => ['', $this->fixtureDirectory],
            ]),
        ];

        $subject->populate($params);

        $items = $params['items'];
        self::assertIsArray($items);
        // Empty entries are skipped; the fixture directory still contributes.
        self::assertSame('bi-discord', array_column($items, 'value', 'label')['Discord'] ?? null);
    }

    #[Test]
    public function populateWithoutSiteKeepsExistingItemsUnchanged(): void
    {
        $subject = new IconItemsProvider(new IconRegistry());

        $emptyItem = ['label' => 'Empty', 'value' => ''];
        $params = ['items' => [$emptyItem]];

        $subject->populate($params);

        // No site → no site settings → no directories are scanned, so no icons
        // are appended and nothing crashes.
        self::assertSame([$emptyItem], $params['items']);
    }
}
