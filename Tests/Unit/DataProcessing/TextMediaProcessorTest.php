<?php

declare(strict_types=1);

namespace StarterTeam\StarterNessa\Tests\Unit\DataProcessing;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use StarterTeam\StarterNessa\DataProcessing\TextMediaProcessor;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class TextMediaProcessorTest extends UnitTestCase
{
    /**
     * @return array<string, array{array<string, mixed>, string}>
     */
    public static function imagePositionDataProvider(): array
    {
        return [
            'beside text left (26) → left' => [['imageorient' => 26], 'left'],
            'beside text right (25) → right' => [['imageorient' => 25], 'right'],
            'above center (0) → above' => [['imageorient' => 0], 'above'],
            'above right (1) → above' => [['imageorient' => 1], 'above'],
            'above left (2) → above' => [['imageorient' => 2], 'above'],
            'numeric string is cast (25) → right' => [['imageorient' => '25'], 'right'],
            'below position falls back to left' => [['imageorient' => 8], 'left'],
            'in-text position falls back to left' => [['imageorient' => 17], 'left'],
            'unknown numeric value falls back to left' => [['imageorient' => 99], 'left'],
            'missing imageorient defaults to above (0)' => [[], 'above'],
            'non-numeric imageorient defaults to above (0)' => [['imageorient' => 'foo'], 'above'],
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    #[DataProvider('imagePositionDataProvider')]
    #[Test]
    public function processDerivesImagePositionFromImageorient(array $data, string $expected): void
    {
        $result = (new TextMediaProcessor())->process(
            self::createStub(ContentObjectRenderer::class),
            [],
            [],
            ['data' => $data],
        );

        self::assertSame($expected, $result['imagePosition']);
    }

    #[Test]
    public function processResolvesLayoutMatchingTheImagePosition(): void
    {
        $result = (new TextMediaProcessor())->process(
            self::createStub(ContentObjectRenderer::class),
            [],
            self::layoutConfiguration(),
            ['data' => ['imageorient' => 0]],
        );

        self::assertSame(
            ['mediaCol' => 'col-lg-9 mx-auto', 'modifier' => 'content--media-above'],
            $result['layout'],
        );
    }

    #[Test]
    public function processFallsBackToLeftLayoutForUnconfiguredPosition(): void
    {
        $configuration = [
            'layouts.' => [
                'left.' => ['mediaCol' => 'col-lg-6'],
                // no 'right.' entry configured
            ],
        ];

        $result = (new TextMediaProcessor())->process(
            self::createStub(ContentObjectRenderer::class),
            [],
            $configuration,
            ['data' => ['imageorient' => 25]],
        );

        self::assertSame(['mediaCol' => 'col-lg-6'], $result['layout']);
    }

    #[Test]
    public function processReturnsEmptyLayoutWithoutConfiguration(): void
    {
        $result = (new TextMediaProcessor())->process(
            self::createStub(ContentObjectRenderer::class),
            [],
            [],
            ['data' => ['imageorient' => 0]],
        );

        self::assertSame([], $result['layout']);
    }

    #[Test]
    public function processFlattensLayoutToStringValuesAndDropsNonScalars(): void
    {
        $configuration = [
            'layouts.' => [
                'above.' => [
                    'imageWidth' => 900,
                    'modifier' => 'content--media-above',
                    'nested.' => ['dropped' => 'me'],
                ],
            ],
        ];

        $result = (new TextMediaProcessor())->process(
            self::createStub(ContentObjectRenderer::class),
            [],
            $configuration,
            ['data' => ['imageorient' => 0]],
        );

        self::assertSame(
            ['imageWidth' => '900', 'modifier' => 'content--media-above'],
            $result['layout'],
        );
    }

    #[Test]
    public function processKeepsExistingProcessedData(): void
    {
        $result = (new TextMediaProcessor())->process(
            self::createStub(ContentObjectRenderer::class),
            [],
            [],
            ['data' => ['imageorient' => 26], 'images' => ['keep me']],
        );

        self::assertSame(['keep me'], $result['images']);
    }

    /**
     * TypoScript-style processor configuration (nested keys carry the trailing dot).
     *
     * @return array<string, mixed>
     */
    private static function layoutConfiguration(): array
    {
        return [
            'layouts.' => [
                'left.' => ['mediaCol' => 'col-lg-6'],
                'right.' => ['mediaCol' => 'col-lg-6', 'mediaOrder' => 'order-1 order-lg-2'],
                'above.' => ['mediaCol' => 'col-lg-9 mx-auto', 'modifier' => 'content--media-above'],
            ],
        ];
    }
}
