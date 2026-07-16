<?php

declare(strict_types=1);

namespace StarterTeam\StarterNessa\DataProcessing;

use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;

/**
 * Reads one or more SVG sprite files and provides their content as {iconSprite}
 * template variable for inline injection into the page body.
 *
 * Which sprites are injected is driven by the "starter.icons.spriteFiles" site
 * setting (a list of SVG sprite files, built-in theme sprite first by default),
 * so a multi-site installation can configure this per site.
 */
final class SvgSpriteProcessor implements DataProcessorInterface
{
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
        $content = '';
        foreach ($this->resolveSpriteFiles($cObj) as $filePath) {
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

    /**
     * @return array<int, string>
     */
    private function resolveSpriteFiles(ContentObjectRenderer $cObj): array
    {
        $site = $cObj->getRequest()->getAttribute('site');
        if ($site instanceof Site === false) {
            return [];
        }

        return $this->asStringList($site->getSettings()->get('starter.icons.spriteFiles', []));
    }

    /**
     * @return array<int, string>
     */
    private function asStringList(mixed $value): array
    {
        if (is_array($value) === false) {
            return [];
        }

        $strings = [];
        foreach ($value as $entry) {
            if (is_string($entry) && $entry !== '') {
                $strings[] = $entry;
            }
        }

        return $strings;
    }
}
