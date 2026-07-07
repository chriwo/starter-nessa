<?php

declare(strict_types=1);

namespace StarterTeam\StarterNessa\DataProcessing;

use StarterTeam\StarterNessa\Resource\ImageOrientType;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;

/**
 * Maps the raw `imageorient` value of a Text & Media (textmedia) element onto a
 * single, template-friendly {imagePosition} token, so the Fluid template can pick
 * its layout without knowing the numeric TYPO3 image orientation codes and
 * without the core GalleryProcessor.
 *
 * Provides:
 *   * {imagePosition} string — "above" | "left" | "right"
 *
 * Only the three positions actually exposed to editors (TCEFORM keeps imageorient
 * 0 = above centered, 25 = beside right, 26 = beside left) are mapped; anything
 * else falls back to "left" — the template's default layout.
 */
final class TextmediaProcessor implements DataProcessorInterface
{
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

        $processedData['imagePosition'] = match ($imageOrient) {
            ImageOrientType::BESIDE_TEXT_RIGHT => 'right',
            ImageOrientType::ABOVE_CENTER,
            ImageOrientType::ABOVE_RIGHT,
            ImageOrientType::ABOVE_LEFT => 'above',
            default => 'left',
        };

        return $processedData;
    }
}
