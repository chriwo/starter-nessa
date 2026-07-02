<?php

declare(strict_types=1);

namespace StarterTeam\StarterNessa\DataProcessing;

use StarterTeam\StarterNessa\Resource\HeroContentPosition;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Derives the presentation state of the nessa_banner (Solid Banner Hero) content
 * element from its raw fields, so the template can rely on ready-to-use values
 * instead of computing them inline.
 *
 * Provides:
 *   * {position}          string — normalised content position, e.g. "center-left"
 *   * {hasContent}        bool   — false for position "none" (pure visual divider)
 *   * {hasImage}          bool   — a background image is set
 *   * {backgroundClass}   string — e.g. "hero-bg-dark" (never empty; falls back)
 *   * {imageClass}        string — " has-bg-image" when an image is set, else ""
 *   * {isDarkBackground}  bool   — text/CTA styling should adapt to a dark surface
 *   * {ctaOutlineClass}   string — full class for the secondary (outline) CTA
 *
 * See {@see AbstractHeroProcessor} for the configurable background and CTA
 * classes shared with the split hero. Wiring, plus the banner-only
 * "fallbackBackground" option:
 *
 *   ```
 *   20 = StarterTeam\StarterNessa\DataProcessing\NessaBannerProcessor
 *   20 {
 *     backgroundClassPrefix = hero-
 *     # background used when the element has no explicit one set
 *     fallbackBackground = bg-dark
 *
 *     ctaOutlineBaseClass = btn-nessa
 *     ctaOutlineClassOnDark = btn-nessa-outline-white
 *     ctaOutlineClassOnLight = btn-nessa-outline-dark
 *   }
 *   ```
 */
final class NessaBannerProcessor extends AbstractHeroProcessor
{
    /** Background applied when the element has no explicit one set. */
    private const string DEFAULT_FALLBACK_BACKGROUND = 'bg-dark';

    /** Marker class toggled on when a background image is present. */
    private const string HAS_IMAGE_CLASS = 'has-bg-image';

    /**
     * Background identifiers that are treated as light surfaces (dark text/CTA).
     * A background image always wins and forces a dark surface regardless.
     *
     * @var list<string>
     */
    private const LIGHT_BACKGROUNDS = ['bg-light'];

    /**
     * @param array<string, mixed> $contentObjectConfiguration
     * @param array<string, mixed> $processorConfiguration
     * @param array<string, mixed> $processedData
     * @return array<string, mixed>
     */
    #[\Override]
    public function process(
        ContentObjectRenderer $cObj,
        array $contentObjectConfiguration,
        array $processorConfiguration,
        array $processedData,
    ): array {
        $data = is_array($processedData['data'] ?? null) ? $processedData['data'] : [];

        $positionValue = $data['tx_starter_hero_content_position'] ?? '';
        $position = HeroContentPosition::tryFrom(is_string($positionValue) ? $positionValue : '')
            ?? HeroContentPosition::CENTER_LEFT;
        $processedData['position'] = $position->value;
        $processedData['hasContent'] = $position !== HeroContentPosition::NONE;

        $images = is_array($processedData['images'] ?? null) ? $processedData['images'] : [];
        $hasImage = $images !== [];
        $processedData['hasImage'] = $hasImage;
        $processedData['imageClass'] = $hasImage ? ' ' . self::HAS_IMAGE_CLASS : '';

        $fallbackBackground = $this->stringConfig($processorConfiguration, 'fallbackBackground', self::DEFAULT_FALLBACK_BACKGROUND);

        $backgroundValue = $data['tx_starter_background'] ?? '';
        $background = trim(is_string($backgroundValue) ? $backgroundValue : '');
        $effectiveBackground = $background !== '' ? $background : $fallbackBackground;
        $processedData['backgroundClass'] = $this->backgroundClassPrefix($processorConfiguration) . $effectiveBackground;

        $isDarkBackground = $hasImage || !in_array($effectiveBackground, self::LIGHT_BACKGROUNDS, true);
        $processedData['isDarkBackground'] = $isDarkBackground;
        $processedData['ctaOutlineClass'] = $this->ctaOutlineClass($processorConfiguration, $isDarkBackground);

        return $processedData;
    }
}
