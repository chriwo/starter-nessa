<?php

declare(strict_types=1);

namespace StarterTeam\StarterNessa\Tests\Functional\Service;

use PHPUnit\Framework\Attributes\Test;
use StarterTeam\StarterNessa\Service\IconRegistry;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

final class IconRegistryTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = ['starterteam/starter-nessa'];

    private const string ICON_DIRECTORY = 'EXT:starter_nessa/Resources/Public/Frontend/Icons/';

    #[Test]
    public function collectMapsIconsFromDirectory(): void
    {
        $icons = (new IconRegistry())->collect([self::ICON_DIRECTORY]);

        $builtIn = $this->getBuiltIconSymbolIds();
        self::assertNotEmpty($builtIn, 'No built icons found — run the frontend build first.');
        foreach ($builtIn as $symbolId) {
            self::assertArrayHasKey($symbolId, $icons);
        }
    }

    #[Test]
    public function collectReturnsEmptyWithoutDirectories(): void
    {
        self::assertNotEmpty($this->getBuiltIconSymbolIds(), 'No built icons found — run the frontend build first.');

        self::assertSame([], (new IconRegistry())->collect([]));
    }

    /**
     * @return array<int, string>
     */
    private function getBuiltIconSymbolIds(): array
    {
        $directory = GeneralUtility::getFileAbsFileName(self::ICON_DIRECTORY);

        return array_map(
            static fn (string $file): string => basename($file, '.svg'),
            glob($directory . '*.svg') ?: [],
        );
    }
}
