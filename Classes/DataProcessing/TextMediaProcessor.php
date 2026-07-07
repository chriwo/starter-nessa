<?php

declare(strict_types=1);

namespace StarterTeam\StarterNessa\DataProcessing;

use StarterTeam\StarterNessa\Resource\ImageOrientType;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;

/**
 * Maps the raw `imageorient` value of a Text & Media (textmedia) element onto a
 * template-friendly {imagePosition} token and resolves the matching {layout}
 * (grid classes + responsive image sizing) from the processor configuration, so
 * the Fluid template stays declarative and the mapping is overridable via
 * TypoScript — no GalleryProcessor and no layout logic in the template.
 *
 * Provides:
 *   * {imagePosition} string — "above" | "left" | "right"
 *   * {layout} array<string,string> — the "layouts.<position>" config entry
 *
 * Only the two "beside" positions exposed to editors (TCEFORM keeps imageorient
 * 25 = beside right, 26 = beside left) map to "right"/"left"; everything else —
 * including the "above" position (0) and any unset/unknown value — falls back to
 * "above". The `layouts` are configured in Textmedia.typoscript and can be
 * overridden from a customer extension, e.g.
 * `tt_content.textmedia.dataProcessing.20.layouts.above.imageWidth = 1200`.
 */
final class TextMediaProcessor implements DataProcessorInterface
{
    private const string FALLBACK_POSITION = 'above';

    /**
     * @param array<string, mixed> $contentObjectConfiguration
     * @param array<string, mixed> $processorConfiguration
     * @param array<string, mixed> $processedData
     * @return array<string, mixed>
     */
    public function process(
        ContentObjectRenderer $cObj,
        array $contentObjectConfiguration,
        array $processorConfiguration,
        array $processedData,
    ): array {
        $data = is_array($processedData['data'] ?? null) ? $processedData['data'] : [];

        $imageOrientValue = $data['imageorient'] ?? 0;
        $imageOrient = ImageOrientType::tryFrom(is_numeric($imageOrientValue) ? (int)$imageOrientValue : 0);

        $position = match ($imageOrient) {
            ImageOrientType::BESIDE_TEXT_LEFT => 'left',
            ImageOrientType::BESIDE_TEXT_RIGHT => 'right',
            default => self::FALLBACK_POSITION,
        };

        $processedData['imagePosition'] = $position;
        $processedData['layout'] = $this->resolveLayout($processorConfiguration, $position);

        return $processedData;
    }

    /**
     * Picks the "layouts.<position>" entry from the processor configuration and
     * flattens it to plain string values for the Fluid template. Falls back to
     * the "left" entry when the position is not configured.
     *
     * @param array<string, mixed> $processorConfiguration
     * @return array<string, string>
     */
    private function resolveLayout(array $processorConfiguration, string $position): array
    {
        $layouts = $processorConfiguration['layouts.'] ?? null;
        if (!is_array($layouts)) {
            return [];
        }

        $layout = $layouts[$position . '.'] ?? $layouts[self::FALLBACK_POSITION . '.'] ?? null;
        if (!is_array($layout)) {
            return [];
        }

        $resolved = [];
        foreach ($layout as $key => $value) {
            if (is_scalar($value)) {
                $resolved[(string)$key] = (string)$value;
            }
        }

        return $resolved;
    }
}
