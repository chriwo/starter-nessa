<?php

declare(strict_types=1);

namespace StarterTeam\StarterNessa\DataProcessing;

use StarterTeam\StarterNessa\Resource\ImageOrientType;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;

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
 * The CSS classes produced here originate in the frontend SCSS, not in the
 * tt_content values, so every class is configurable via TypoScript to keep its
 * origin discoverable in the site configuration:
 *
 *   ```
 *   30 = StarterTeam\StarterNessa\DataProcessing\NessaImageTextProcessor
 *   30 {
 *     # bridges the tt_content value "bg-dark" to the CSS hook ".hero-bg-dark"
 *     backgroundClassPrefix = hero-
 *
 *     # secondary (outline) CTA: base class + the variant per background brightness
 *     ctaOutlineBaseClass = btn-nessa
 *     ctaOutlineClassOnDark = btn-nessa-outline-white
 *     ctaOutlineClassOnLight = btn-nessa-outline-dark
 *   }
 *   ```
 */
final class NessaImageTextProcessor implements DataProcessorInterface
{
    /**
     * Namespace prefix applied to the tt_content background value to build the
     * CSS class. Used when TypoScript does not override "backgroundClassPrefix".
     */
    private const DEFAULT_BACKGROUND_CLASS_PREFIX = 'hero-';

    /** Base class shared by both outline CTA variants. */
    private const DEFAULT_CTA_OUTLINE_BASE_CLASS = 'btn-nessa';

    /** Outline CTA variant rendered on a dark background. */
    private const DEFAULT_CTA_OUTLINE_CLASS_ON_DARK = 'btn-nessa-outline-white';

    /** Outline CTA variant rendered on a light background. */
    private const DEFAULT_CTA_OUTLINE_CLASS_ON_LIGHT = 'btn-nessa-outline-dark';

    /**
     * Background identifiers that are treated as light surfaces (dark text/CTA).
     * Every other non-empty background is considered dark.
     *
     * @var list<string>
     */
    private const LIGHT_BACKGROUNDS = ['', 'bg-light'];

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

        $backgroundClassPrefix = $this->stringConfig(
            $processorConfiguration,
            'backgroundClassPrefix',
            self::DEFAULT_BACKGROUND_CLASS_PREFIX,
        );

        $backgroundValue = $data['tx_starter_background'] ?? '';
        $background = trim(is_string($backgroundValue) ? $backgroundValue : '');
        $processedData['backgroundClass'] = $background !== '' ? $backgroundClassPrefix . $background : '';

        $isDarkBackground = !in_array($background, self::LIGHT_BACKGROUNDS, true);
        $processedData['isDarkBackground'] = $isDarkBackground;

        $ctaOutlineVariant = $isDarkBackground
            ? $this->stringConfig($processorConfiguration, 'ctaOutlineClassOnDark', self::DEFAULT_CTA_OUTLINE_CLASS_ON_DARK)
            : $this->stringConfig($processorConfiguration, 'ctaOutlineClassOnLight', self::DEFAULT_CTA_OUTLINE_CLASS_ON_LIGHT);
        $ctaOutlineBaseClass = $this->stringConfig($processorConfiguration, 'ctaOutlineBaseClass', self::DEFAULT_CTA_OUTLINE_BASE_CLASS);
        $processedData['ctaOutlineClass'] = trim($ctaOutlineBaseClass . ' ' . $ctaOutlineVariant);

        $colPos = (int)($data['colPos'] ?? 0);
        $processedData['imageLoading'] = $colPos === self::HERO_COL_POS ? 'eager' : 'lazy';

        return $processedData;
    }

    /**
     * Reads a string value from the processor configuration, falling back to
     * $default when the key is missing or not a string.
     *
     * @param array<string, mixed> $processorConfiguration
     */
    private function stringConfig(array $processorConfiguration, string $key, string $default): string
    {
        $value = $processorConfiguration[$key] ?? $default;

        return is_string($value) ? $value : $default;
    }
}
