<?php

declare(strict_types=1);

namespace StarterTeam\StarterNessa\Tests\Unit\DataProcessing;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use StarterTeam\StarterNessa\DataProcessing\NessaBannerProcessor;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class NessaBannerProcessorTest extends UnitTestCase
{
    /**
     * @param array<string, mixed> $data
     * @param list<mixed> $images
     * @param array<string, mixed> $processorConfiguration
     * @return array<string, mixed>
     */
    private function process(array $data, array $images = [], array $processorConfiguration = []): array
    {
        $subject = new NessaBannerProcessor();

        return $subject->process(
            self::createStub(ContentObjectRenderer::class),
            [],
            $processorConfiguration,
            ['data' => $data, 'images' => $images],
        );
    }

    /**
     * @return array<string, array{array<string, mixed>, string, bool}>
     */
    public static function positionDataProvider(): array
    {
        return [
            'explicit center-left' => [['tx_starter_hero_content_position' => 'center-left'], 'center-left', true],
            'center' => [['tx_starter_hero_content_position' => 'center'], 'center', true],
            'bottom-left' => [['tx_starter_hero_content_position' => 'bottom-left'], 'bottom-left', true],
            'none has no content' => [['tx_starter_hero_content_position' => 'none'], 'none', false],
            'empty falls back to center-left' => [[], 'center-left', true],
            'unknown value falls back to center-left' => [['tx_starter_hero_content_position' => 'nope'], 'center-left', true],
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    #[DataProvider('positionDataProvider')]
    #[Test]
    public function processNormalisesContentPosition(array $data, string $expectedPosition, bool $expectedHasContent): void
    {
        $result = $this->process($data);

        self::assertSame($expectedPosition, $result['position']);
        self::assertSame($expectedHasContent, $result['hasContent']);
    }

    #[Test]
    public function processDetectsBackgroundImage(): void
    {
        $withImage = $this->process([], ['a-file-reference']);
        self::assertTrue($withImage['hasImage']);
        self::assertSame(' has-bg-image', $withImage['imageClass']);

        $withoutImage = $this->process([]);
        self::assertFalse($withoutImage['hasImage']);
        self::assertSame('', $withoutImage['imageClass']);
    }

    /**
     * @return array<string, array{array<string, mixed>, list<mixed>, string, bool, string}>
     */
    public static function backgroundDataProvider(): array
    {
        return [
            'explicit dark background' => [
                ['tx_starter_background' => 'bg-dark'], [],
                'hero-bg-dark', true, 'btn-nessa btn-nessa-outline-white',
            ],
            'empty background falls back to bg-dark' => [
                [], [],
                'hero-bg-dark', true, 'btn-nessa btn-nessa-outline-white',
            ],
            'light background without image is light' => [
                ['tx_starter_background' => 'bg-light'], [],
                'hero-bg-light', false, 'btn-nessa btn-nessa-outline-dark',
            ],
            'light background with image is forced dark' => [
                ['tx_starter_background' => 'bg-light'], ['file'],
                'hero-bg-light', true, 'btn-nessa btn-nessa-outline-white',
            ],
            'coloured background is dark' => [
                ['tx_starter_background' => 'bg-primary'], [],
                'hero-bg-primary', true, 'btn-nessa btn-nessa-outline-white',
            ],
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @param list<mixed> $images
     */
    #[DataProvider('backgroundDataProvider')]
    #[Test]
    public function processDerivesBackgroundAndCtaState(
        array $data,
        array $images,
        string $expectedBackgroundClass,
        bool $expectedIsDark,
        string $expectedCtaOutlineClass,
    ): void {
        $result = $this->process($data, $images);

        self::assertSame($expectedBackgroundClass, $result['backgroundClass']);
        self::assertSame($expectedIsDark, $result['isDarkBackground']);
        self::assertSame($expectedCtaOutlineClass, $result['ctaOutlineClass']);
    }

    #[Test]
    public function processHonoursConfigurableClasses(): void
    {
        $result = $this->process(
            ['tx_starter_background' => ''],
            [],
            [
                'backgroundClassPrefix' => 'ce-',
                'fallbackBackground' => 'bg-night',
                'ctaOutlineBaseClass' => 'btn',
                'ctaOutlineClassOnDark' => 'btn-outline-light',
            ],
        );

        self::assertSame('ce-bg-night', $result['backgroundClass']);
        self::assertSame('btn btn-outline-light', $result['ctaOutlineClass']);
    }

    #[Test]
    public function processKeepsExistingProcessedData(): void
    {
        $subject = new NessaBannerProcessor();

        $result = $subject->process(
            self::createStub(ContentObjectRenderer::class),
            [],
            [],
            ['data' => [], 'images' => [], 'keep' => 'me'],
        );

        self::assertSame('me', $result['keep']);
    }
}
