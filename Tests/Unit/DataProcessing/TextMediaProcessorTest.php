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
            'numeric string is cast (25) → right' => [['imageorient' => '25'], 'right'],
            'above center (0) → above' => [['imageorient' => 0], 'above'],
            'below position → above' => [['imageorient' => 8], 'above'],
            'in-text position → above' => [['imageorient' => 17], 'above'],
            'unknown numeric value → above' => [['imageorient' => 99], 'above'],
            'missing imageorient → above' => [[], 'above'],
            'non-numeric imageorient → above' => [['imageorient' => 'foo'], 'above'],
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
    public function processFallsBackToAboveLayoutForUnconfiguredPosition(): void
    {
        $configuration = [
            'layouts.' => [
                'above.' => ['mediaCol' => 'col-lg-9 mx-auto'],
                // no 'right.' entry configured
            ],
        ];

        $result = (new TextMediaProcessor())->process(
            self::createStub(ContentObjectRenderer::class),
            [],
            $configuration,
            ['data' => ['imageorient' => 25]],
        );

        self::assertSame(['mediaCol' => 'col-lg-9 mx-auto'], $result['layout']);
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
