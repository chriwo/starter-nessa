<?php

declare(strict_types=1);

namespace StarterTeam\StarterNessa\Tests\Unit\Tca;

use PHPUnit\Framework\Attributes\Test;
use StarterTeam\StarterNessa\Service\IconRegistry;
use StarterTeam\StarterNessa\Tca\IconItemsProvider;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class IconItemsProviderTest extends UnitTestCase
{
    private string $fixtureDirectory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fixtureDirectory = dirname(__DIR__) . '/Fixtures/Icons/';
        $this->registerIconDirectories([$this->fixtureDirectory]);
    }

    protected function tearDown(): void
    {
        $this->registerIconDirectories([]);
        parent::tearDown();
    }

    /**
     * @param array<int, mixed> $directories
     */
    private function registerIconDirectories(array $directories): void
    {
        $GLOBALS['TYPO3_CONF_VARS'] = [
            'EXTENSIONS' => [
                'starter_nessa' => [
                    'iconDirectories' => $directories,
                ],
            ],
        ];
    }

    #[Test]
    public function populateAppendsOneItemPerIconAndKeepsExistingEmptyItem(): void
    {
        $subject = new IconItemsProvider(new IconRegistry());

        $emptyItem = [
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.default_value',
            'value' => '',
        ];
        $params = ['items' => [$emptyItem]];

        $subject->populate($params);

        $items = $params['items'];
        self::assertIsArray($items);

        // The four fixture icons are appended after the untouched empty item.
        self::assertSame($emptyItem, $items[0]);
        self::assertCount(5, $items);

        $appended = array_slice($items, 1);
        foreach ($appended as $item) {
            self::assertIsArray($item);
            self::assertArrayHasKey('label', $item);
            self::assertArrayHasKey('value', $item);
            self::assertArrayHasKey('icon', $item);
            $value = $item['value'];
            self::assertIsString($value);
            self::assertSame('starter-nessa-' . $value, $item['icon']);
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

        $params = ['items' => []];

        $subject->populate($params);

        $items = $params['items'];
        self::assertIsArray($items);

        $labels = array_column($items, 'label');
        // Fixture icons sorted case-insensitively by their derived label.
        self::assertSame(['Cement Mix', 'Discord', 'Twitter / X', 'TYPO3'], $labels);
    }
}
