<?php

declare(strict_types=1);

namespace StarterTeam\StarterNessa\DataProcessing;

use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;

/**
 * Shared base for the hero content element DataProcessors (nessa_image_text,
 * nessa_banner). Holds the presentation logic both elements have in common:
 * the namespaced background class prefix and the secondary (outline) CTA class.
 *
 * The CSS classes originate in the frontend SCSS, not in the tt_content values,
 * so every class is configurable via TypoScript to keep its origin discoverable
 * in the site configuration:
 *
 *   ```
 *   # bridges the tt_content value "bg-dark" to the CSS hook ".hero-bg-dark"
 *   backgroundClassPrefix = hero-
 *
 *   # secondary (outline) CTA: base class + the variant per background brightness
 *   ctaOutlineBaseClass = btn-nessa
 *   ctaOutlineClassOnDark = btn-nessa-outline-white
 *   ctaOutlineClassOnLight = btn-nessa-outline-dark
 *   ```
 */
abstract class AbstractHeroProcessor implements DataProcessorInterface
{
    /**
     * Namespace prefix applied to the tt_content background value to build the
     * CSS class. Used when TypoScript does not override "backgroundClassPrefix".
     */
    protected const string DEFAULT_BACKGROUND_CLASS_PREFIX = 'hero-';

    /** Base class shared by both outline CTA variants. */
    protected const string DEFAULT_CTA_OUTLINE_BASE_CLASS = 'btn-nessa';

    /** Outline CTA variant rendered on a dark background. */
    protected const string DEFAULT_CTA_OUTLINE_CLASS_ON_DARK = 'btn-nessa-outline-white';

    /** Outline CTA variant rendered on a light background. */
    protected const string DEFAULT_CTA_OUTLINE_CLASS_ON_LIGHT = 'btn-nessa-outline-dark';

    /**
     * Namespace prefix used to turn a tt_content background value into its CSS
     * class (e.g. "bg-dark" → "hero-bg-dark").
     *
     * @param array<string, mixed> $processorConfiguration
     */
    protected function backgroundClassPrefix(array $processorConfiguration): string
    {
        return $this->stringConfig($processorConfiguration, 'backgroundClassPrefix', self::DEFAULT_BACKGROUND_CLASS_PREFIX);
    }

    /**
     * Full class for the secondary (outline) CTA: the base class plus the
     * variant matching the background brightness.
     *
     * @param array<string, mixed> $processorConfiguration
     */
    protected function ctaOutlineClass(array $processorConfiguration, bool $isDarkBackground): string
    {
        $variant = $isDarkBackground
            ? $this->stringConfig($processorConfiguration, 'ctaOutlineClassOnDark', self::DEFAULT_CTA_OUTLINE_CLASS_ON_DARK)
            : $this->stringConfig($processorConfiguration, 'ctaOutlineClassOnLight', self::DEFAULT_CTA_OUTLINE_CLASS_ON_LIGHT);
        $baseClass = $this->stringConfig($processorConfiguration, 'ctaOutlineBaseClass', self::DEFAULT_CTA_OUTLINE_BASE_CLASS);

        return trim($baseClass . ' ' . $variant);
    }

    /**
     * Reads a string value from the processor configuration, falling back to
     * $default when the key is missing or not a string.
     *
     * @param array<string, mixed> $processorConfiguration
     */
    protected function stringConfig(array $processorConfiguration, string $key, string $default): string
    {
        $value = $processorConfiguration[$key] ?? $default;

        return is_string($value) ? $value : $default;
    }
}
