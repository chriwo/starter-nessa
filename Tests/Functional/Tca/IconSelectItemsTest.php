<?php

declare(strict_types=1);

namespace StarterTeam\StarterNessa\Tests\Functional\Tca;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use StarterTeam\StarterNessa\Tca\IconItemsProvider;
use TYPO3\CMS\Core\Settings\Settings;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteSettings;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

final class IconSelectItemsTest extends FunctionalTestCase
{
    private const string ICON_DIRECTORY = 'EXT:starter_nessa/Resources/Public/Frontend/Icons/';
    protected array $testExtensionsToLoad = ['starterteam/starter-nessa'];

    /**
     * @return array<string, array{string}>
     */
    public static function iconFieldDataProvider(): array
    {
        return [
            'social element icon field' => ['tx_starternessa_social_element'],
            'teaser element icon field' => ['tx_starternessa_teaser_element'],
        ];
    }

    #[DataProvider('iconFieldDataProvider')]
    #[Test]
    public function iconFieldUsesItemsProcFunc(string $table): void
    {
        $config = $this->getIconFieldConfig($table);

        self::assertSame(IconItemsProvider::class . '->populate', $config['itemsProcFunc'] ?? null);

        // The static items list only holds the empty default entry now.
        $items = $this->getStaticItems($config);
        self::assertCount(1, $items);
        $firstItem = $items[0];
        self::assertIsArray($firstItem);
        self::assertSame('', $firstItem['value'] ?? null);
    }

    #[DataProvider('iconFieldDataProvider')]
    #[Test]
    public function itemsProcFuncYieldsOnlyIconsThatExistOnDisk(string $table): void
    {
        $config = $this->getIconFieldConfig($table);

        $params = [
            'items' => $this->getStaticItems($config),
            'site' => $this->siteWithBuiltInIcons(),
        ];
        GeneralUtility::makeInstance(IconItemsProvider::class)->populate($params);

        $availableSymbolIds = $this->getBuiltIconSymbolIds();
        self::assertNotEmpty($availableSymbolIds, 'No built icons found — run the frontend build first.');

        $resultItems = $params['items'];
        self::assertIsArray($resultItems);

        $offeredSymbolIds = [];
        foreach ($resultItems as $item) {
            self::assertIsArray($item);
            $value = $item['value'] ?? '';
            self::assertIsString($value);
            if ($value === '') {
                continue;
            }

            self::assertContains(
                $value,
                $availableSymbolIds,
                sprintf('Dropdown offers "%s" but no matching SVG file exists.', $value),
            );
            self::assertNotSame('', $item['label'] ?? null);

            $offeredSymbolIds[] = $value;
        }

        self::assertNotEmpty($offeredSymbolIds);

        // Every built icon must be offered — no icon is silently hidden.
        sort($offeredSymbolIds);
        sort($availableSymbolIds);
        self::assertSame($availableSymbolIds, $offeredSymbolIds);
    }

    private function siteWithBuiltInIcons(): Site
    {
        $settings = SiteSettings::create(new Settings([
            'starter.icons.directories' => [self::ICON_DIRECTORY],
        ]));

        return new Site('test', 1, [], $settings);
    }

    /**
     * @return array<mixed, mixed>
     */
    private function getIconFieldConfig(string $table): array
    {
        $tca = $GLOBALS['TCA'] ?? [];
        self::assertIsArray($tca);

        $tableTca = $tca[$table] ?? null;
        self::assertIsArray($tableTca);

        $columns = $tableTca['columns'] ?? null;
        self::assertIsArray($columns);

        $iconColumn = $columns['icon'] ?? null;
        self::assertIsArray($iconColumn);

        $config = $iconColumn['config'] ?? null;
        self::assertIsArray($config);

        return $config;
    }

    /**
     * @param array<mixed, mixed> $config
     * @return array<int, mixed>
     */
    private function getStaticItems(array $config): array
    {
        $items = $config['items'] ?? [];
        self::assertIsArray($items);

        return array_values($items);
    }

    /**
     * @return array<int, string>
     */
    private function getBuiltIconSymbolIds(): array
    {
        $directory = GeneralUtility::getFileAbsFileName(self::ICON_DIRECTORY);

        return array_map(
            static fn(string $file): string => basename($file, '.svg'),
            glob($directory . '*.svg') ?: [],
        );
    }
}
