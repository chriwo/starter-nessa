<?php

declare(strict_types=1);

namespace StarterTeam\StarterNessa\Tests\Unit\Service;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use StarterTeam\StarterNessa\Service\IconRegistry;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class IconRegistryTest extends UnitTestCase
{
    private string $fixtureDirectory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fixtureDirectory = dirname(__DIR__) . '/Fixtures/Icons/';
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function labelDataProvider(): array
    {
        return [
            'strips bi- prefix and title-cases' => ['bi-discord', 'Discord'],
            'strips custom- prefix' => ['custom-bathtub', 'Bathtub'],
            'dashes become spaces and title-case' => ['custom-cement-mix', 'Cement Mix'],
            'multiple words are title-cased' => ['bi-chevron-compact-down', 'Chevron Compact Down'],
            'override map: twitter-x' => ['bi-twitter-x', 'Twitter / X'],
            'override map: typo3' => ['custom-typo3', 'TYPO3'],
            'override map: linkedin' => ['bi-linkedin', 'LinkedIn'],
            'override map: link-45deg' => ['bi-link-45deg', 'Web-Link'],
            'unknown prefix is kept as-is but title-cased' => ['foo-bar', 'Foo Bar'],
        ];
    }

    #[DataProvider('labelDataProvider')]
    #[Test]
    public function deriveLabelReturnsExpectedLabel(string $symbolId, string $expectedLabel): void
    {
        self::assertSame($expectedLabel, (new IconRegistry())->deriveLabel($symbolId));
    }

    #[Test]
    public function collectScansDirectoryAndMapsLabels(): void
    {
        $icons = (new IconRegistry())->collect([$this->fixtureDirectory]);

        self::assertSame('Discord', $icons['bi-discord'] ?? null);
        self::assertSame('TYPO3', $icons['custom-typo3'] ?? null);
        self::assertArrayHasKey('bi-twitter-x', $icons);
        self::assertArrayHasKey('custom-cement-mix', $icons);
    }

    #[Test]
    public function collectReturnsEmptyWithoutDirectories(): void
    {
        self::assertSame([], (new IconRegistry())->collect([]));
    }

    #[Test]
    public function collectSkipsEmptyDirectoryEntries(): void
    {
        $icons = (new IconRegistry())->collect(['', $this->fixtureDirectory]);

        self::assertArrayHasKey('bi-discord', $icons);
    }
}
