<?php

declare(strict_types=1);

namespace StarterTeam\StarterNessa\Tests\Functional\ViewHelpers\Format;

use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use stdClass;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use TYPO3Fluid\Fluid\Core\Cache\FluidCacheInterface;
use TYPO3Fluid\Fluid\Core\Cache\SimpleFileCache;
use TYPO3Fluid\Fluid\View\TemplateView;

final class UpperViewHelperTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = ['starterteam/starter-nessa'];

    protected array $coreExtensionsToLoad = ['extbase', 'fluid'];

    protected static FluidCacheInterface $cache;

    /**
     * @var string Absolute path to cache directory
     */
    protected static string $cachePath;

    #[Override]
    public function setUp(): void
    {
        parent::setUp();

        self::$cachePath = sys_get_temp_dir() . '/' . 'starter_nessa-functional-tests-' . hash('xxh3', self::class);
        mkdir(self::$cachePath);
        self::$cache = (new SimpleFileCache(self::$cachePath));
    }

    #[Override]
    public function tearDown(): void
    {
        self::$cache->flush();
        rmdir(self::$cachePath);

        parent::tearDown();
    }

    public static function renderDataProvider(): array
    {
        return [
            'renderUsesValueAsSourceIfSpecified' => [
                '<starterteam:format.upper value="Some string" />',
                [],
                'SOME STRING',
            ],
            'int as input' => [
                '<starterteam:format.upper value="123" />',
                [],
                '123',
            ],
            'renderUsesChildnodesAsSourceIfSpecified' => [
                '<starterteam:format.upper>Some string</starterteam:format.upper>',
                [],
                'SOME STRING',
            ],
            'renderUsesChildnodesAsSourceIfSpecifiedInInlineNotation' => [
                '{myVariable -> starterteam:format.upper()}',
                ['myVariable' => 'Some string'],
                'SOME STRING',
            ],
        ];
    }

    #[DataProvider('renderDataProvider')]
    #[Test]
    public function render(string $template, array $variables, string $expected): void
    {
        $template = '{namespace starterteam=StarterTeam\StarterNessa\ViewHelpers}' . $template;

        $view = new TemplateView();
        $view->assignMultiple($variables);
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($template);
        self::assertSame($expected, $view->render());

        $view = new TemplateView();
        $view->assignMultiple($variables);
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($template);
        self::assertSame($expected, $view->render());
    }

    public static function throwsExceptionForInvalidInputDataProvider(): array
    {
        return [
            'array input' => [
                [1, 2, 3],
                1752329208,
                'Specified array cannot be converted to string.',
            ],
            'object input' => [
                new stdClass(),
                1752329228,
                'Specified object cannot be converted to string.',
            ],
            'null input' => [
                null,
                1752329547,
                'Specified value cannot be converted to string.',
            ],
        ];
    }

    #[DataProvider('throwsExceptionForInvalidInputDataProvider')]
    #[Test]
    public function throwsExceptionForInvalidInput(mixed $value, int $expectedExceptionCode, string $expectedExceptionMessage): void
    {
        self::expectExceptionCode($expectedExceptionCode);
        self::expectExceptionMessage($expectedExceptionMessage);

        $template = '{namespace starterteam=StarterTeam\StarterNessa\ViewHelpers}'
        . '<starterteam:format.upper>{value}</starterteam:format.upper>';

        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($template);
        $view->assign('value', $value);
        $view->render();
    }
}
