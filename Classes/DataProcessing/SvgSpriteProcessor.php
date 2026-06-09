<?php

declare(strict_types=1);

namespace StarterTeam\StarterNessa\DataProcessing;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;

/**
 * Reads one or more SVG sprite files and provides their content as {iconSprite}
 * template variable for inline injection into the page body.
 *
 * Customer extensions can add their own sprite files via TypoScript:
 *   page.10.dataProcessing.5.spriteFiles.20 = EXT:customer_theme/.../icons-custom.svg
 */
final class SvgSpriteProcessor implements DataProcessorInterface
{
    private const DEFAULT_SPRITE_FILES = [
        '10' => 'EXT:starter_nessa/Resources/Public/Frontend/icons.svg',
    ];

    /** @var array<string, string> */
    private static array $spriteCache = [];

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
        array $processedData
    ): array {
        $spriteFiles = $processorConfiguration['spriteFiles.'] ?? self::DEFAULT_SPRITE_FILES;
        if (!is_array($spriteFiles)) {
            $spriteFiles = self::DEFAULT_SPRITE_FILES;
        }

        ksort($spriteFiles);

        $content = '';
        foreach ($spriteFiles as $key => $filePath) {
            if (!is_string($filePath) || str_ends_with((string)$key, '.')) {
                continue;
            }
            $absolutePath = GeneralUtility::getFileAbsFileName($filePath);
            if ($absolutePath === '' || !is_file($absolutePath)) {
                continue;
            }
            if (!isset(self::$spriteCache[$absolutePath])) {
                $fileContent = file_get_contents($absolutePath);
                self::$spriteCache[$absolutePath] = $fileContent !== false ? $fileContent : '';
            }
            $content .= self::$spriteCache[$absolutePath];
        }

        $processedData['iconSprite'] = $content;

        return $processedData;
    }
}
