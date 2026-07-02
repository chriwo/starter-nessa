<?php

declare(strict_types=1);

namespace StarterTeam\StarterNessa\Tests\Unit\DataProcessing;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use StarterTeam\StarterNessa\DataProcessing\NessaImageTextProcessor;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class NessaImageTextProcessorTest extends UnitTestCase
{
    /**
     * @return array<string, array{array<string, mixed>, array<string, mixed>}>
     */
    public static function processDataProvider(): array
    {
        return [
            'beside text left → imageLeft true' => [
                ['imageorient' => 26],
                [
                    'imageLeft' => true,
                    'backgroundClass' => '',
                    'isDarkBackground' => false,
                    'ctaOutlineClass' => 'btn-nessa btn-nessa-outline-dark',
                ],
            ],
            'beside text right → imageLeft false' => [
                ['imageorient' => 25],
                [
                    'imageLeft' => false,
                    'backgroundClass' => '',
                    'isDarkBackground' => false,
                    'ctaOutlineClass' => 'btn-nessa btn-nessa-outline-dark',
                ],
            ],
            'above center → imageLeft false' => [
                ['imageorient' => 0],
                [
                    'imageLeft' => false,
                    'backgroundClass' => '',
                    'isDarkBackground' => false,
                    'ctaOutlineClass' => 'btn-nessa btn-nessa-outline-dark',
                ],
            ],
            'dark background → dark styling + white outline CTA' => [
                ['imageorient' => 26, 'tx_starter_background' => 'bg-dark'],
                [
                    'imageLeft' => true,
                    'backgroundClass' => 'hero-bg-dark',
                    'isDarkBackground' => true,
                    'ctaOutlineClass' => 'btn-nessa btn-nessa-outline-white',
                ],
            ],
            'light background is treated as light surface' => [
                ['imageorient' => 26, 'tx_starter_background' => 'bg-light'],
                [
                    'imageLeft' => true,
                    'backgroundClass' => 'hero-bg-light',
                    'isDarkBackground' => false,
                    'ctaOutlineClass' => 'btn-nessa btn-nessa-outline-dark',
                ],
            ],
            'empty data falls back to light two-state defaults' => [
                [],
                [
                    'imageLeft' => false,
                    'backgroundClass' => '',
                    'isDarkBackground' => false,
                    'ctaOutlineClass' => 'btn-nessa btn-nessa-outline-dark',
                ],
            ],
            'whitespace-only background is ignored' => [
                ['tx_starter_background' => '   '],
                [
                    'imageLeft' => false,
                    'backgroundClass' => '',
                    'isDarkBackground' => false,
                    'ctaOutlineClass' => 'btn-nessa btn-nessa-outline-dark',
                ],
            ],
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $expected
     */
    #[DataProvider('processDataProvider')]
    #[Test]
    public function processDerivesPresentationState(array $data, array $expected): void
    {
        $subject = new NessaImageTextProcessor();

        $result = $subject->process(
            self::createStub(ContentObjectRenderer::class),
            [],
            [],
            ['data' => $data],
        );

        foreach ($expected as $key => $value) {
            self::assertSame($value, $result[$key], sprintf('Unexpected value for "%s".', $key));
        }
    }

    /**
     * @return array<string, array{array<string, mixed>, string}>
     */
    public static function backgroundClassPrefixDataProvider(): array
    {
        return [
            'custom prefix is applied' => [
                ['backgroundClassPrefix' => 'ce-'],
                'ce-bg-dark',
            ],
            'empty prefix keeps the raw value' => [
                ['backgroundClassPrefix' => ''],
                'bg-dark',
            ],
            'non-string prefix falls back to the default' => [
                ['backgroundClassPrefix' => 123],
                'hero-bg-dark',
            ],
        ];
    }

    /**
     * @param array<string, mixed> $processorConfiguration
     */
    #[DataProvider('backgroundClassPrefixDataProvider')]
    #[Test]
    public function processAppliesConfigurableBackgroundClassPrefix(array $processorConfiguration, string $expected): void
    {
        $subject = new NessaImageTextProcessor();

        $result = $subject->process(
            self::createStub(ContentObjectRenderer::class),
            [],
            $processorConfiguration,
            ['data' => ['tx_starter_background' => 'bg-dark']],
        );

        self::assertSame($expected, $result['backgroundClass']);
    }

    /**
     * @return array<string, array{array<string, mixed>, string, string}>
     */
    public static function ctaOutlineClassDataProvider(): array
    {
        return [
            'defaults on dark background' => [
                [],
                'bg-dark',
                'btn-nessa btn-nessa-outline-white',
            ],
            'defaults on light background' => [
                [],
                'bg-light',
                'btn-nessa btn-nessa-outline-dark',
            ],
            'custom classes on dark background' => [
                [
                    'ctaOutlineBaseClass' => 'btn',
                    'ctaOutlineClassOnDark' => 'btn-outline-light',
                    'ctaOutlineClassOnLight' => 'btn-outline-dark',
                ],
                'bg-dark',
                'btn btn-outline-light',
            ],
            'custom classes on light background' => [
                [
                    'ctaOutlineBaseClass' => 'btn',
                    'ctaOutlineClassOnDark' => 'btn-outline-light',
                    'ctaOutlineClassOnLight' => 'btn-outline-dark',
                ],
                'bg-light',
                'btn btn-outline-dark',
            ],
            'empty base class is trimmed away' => [
                ['ctaOutlineBaseClass' => ''],
                'bg-dark',
                'btn-nessa-outline-white',
            ],
        ];
    }

    /**
     * @param array<string, mixed> $processorConfiguration
     */
    #[DataProvider('ctaOutlineClassDataProvider')]
    #[Test]
    public function processBuildsConfigurableCtaOutlineClass(array $processorConfiguration, string $background, string $expected): void
    {
        $subject = new NessaImageTextProcessor();

        $result = $subject->process(
            self::createStub(ContentObjectRenderer::class),
            [],
            $processorConfiguration,
            ['data' => ['tx_starter_background' => $background]],
        );

        self::assertSame($expected, $result['ctaOutlineClass']);
    }

    /**
     * @return array<string, array{array<string, mixed>, string}>
     */
    public static function imageLoadingDataProvider(): array
    {
        return [
            'hero region (colPos 1) loads eagerly' => [
                ['colPos' => 1],
                'eager',
            ],
            'main content (colPos 0) loads lazily' => [
                ['colPos' => 0],
                'lazy',
            ],
            'other columns load lazily' => [
                ['colPos' => 2],
                'lazy',
            ],
            'missing colPos falls back to lazy' => [
                [],
                'lazy',
            ],
            'numeric string colPos is cast' => [
                ['colPos' => '1'],
                'eager',
            ],
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    #[DataProvider('imageLoadingDataProvider')]
    #[Test]
    public function processDerivesImageLoadingFromColPos(array $data, string $expected): void
    {
        $subject = new NessaImageTextProcessor();

        $result = $subject->process(
            self::createStub(ContentObjectRenderer::class),
            [],
            [],
            ['data' => $data],
        );

        self::assertSame($expected, $result['imageLoading']);
    }

    #[Test]
    public function processKeepsExistingProcessedData(): void
    {
        $subject = new NessaImageTextProcessor();

        $result = $subject->process(
            self::createStub(ContentObjectRenderer::class),
            [],
            [],
            ['data' => ['imageorient' => 26], 'stats' => ['keep me']],
        );

        self::assertSame(['keep me'], $result['stats']);
    }
}
