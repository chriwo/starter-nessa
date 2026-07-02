<?php

declare(strict_types=1);

namespace StarterTeam\StarterNessa\DataProcessing;

use StarterTeam\StarterNessa\Resource\ImageOrientType;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Derives the presentation state of the nessa_image_text (Split Hero) content
 * element from its raw fields, so the template can rely on ready-to-use values
 * instead of computing them inline.
 *
 * Provides:
 *   * {imageLeft}         bool   — image column rendered left of the text
 *   * {backgroundClass}   string — e.g. "hero-bg-dark" (empty when no background)
 *   * {isDarkBackground}  bool   — text/CTA styling should adapt to a dark surface
 *   * {ctaOutlineClass}   string — full class for the secondary (outline) CTA
 *   * {imageLoading}      string — "eager" in the hero region (colPos 1), else "lazy"
 *
 * See {@see AbstractHeroProcessor} for the configurable background and CTA
 * classes shared with the banner hero. Wiring:
 *
 *   ```
 *   30 = StarterTeam\StarterNessa\DataProcessing\NessaImageTextProcessor
 *   30 {
 *     backgroundClassPrefix = hero-
 *     ctaOutlineBaseClass = btn-nessa
 *     ctaOutlineClassOnDark = btn-nessa-outline-white
 *     ctaOutlineClassOnLight = btn-nessa-outline-dark
 *   }
 *   ```
 */
final class NessaImageTextProcessor extends AbstractHeroProcessor
{
    /**
     * Background identifiers that are treated as light surfaces (dark text/CTA).
     * Every other non-empty background is considered dark.
     *
     * @var list<string>
     */
    private const array LIGHT_BACKGROUNDS = ['', 'bg-light'];

    /**
     * colPos of the hero region. Content there sits above the fold, so its image
     * loads eagerly to protect the LCP; everywhere else it may load lazily.
     */
    private const int HERO_COL_POS = 1;

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

        $imageOrientValue = $data['imageorient'] ?? 0;
        $imageOrient = ImageOrientType::tryFrom(is_numeric($imageOrientValue) ? (int)$imageOrientValue : 0);
        $processedData['imageLeft'] = $imageOrient === ImageOrientType::BESIDE_TEXT_LEFT;

        $backgroundValue = $data['tx_starter_background'] ?? '';
        $background = trim(is_string($backgroundValue) ? $backgroundValue : '');
        $processedData['backgroundClass'] = $background !== ''
            ? $this->backgroundClassPrefix($processorConfiguration) . $background
            : '';

        $isDarkBackground = !in_array($background, self::LIGHT_BACKGROUNDS, true);
        $processedData['isDarkBackground'] = $isDarkBackground;
        $processedData['ctaOutlineClass'] = $this->ctaOutlineClass($processorConfiguration, $isDarkBackground);

        $colPos = (int)($data['colPos'] ?? 0);
        $processedData['imageLoading'] = $colPos === self::HERO_COL_POS ? 'eager' : 'lazy';

        return $processedData;
    }
}
