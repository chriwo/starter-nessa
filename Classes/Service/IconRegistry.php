<?php

declare(strict_types=1);

namespace StarterTeam\StarterNessa\Service;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Scans the given icon directories for built *.svg files and derives a
 * human-readable label per icon.
 *
 * This feeds the dynamic TCA select items (see IconItemsProvider); the caller
 * decides — per site, via the site settings — which directories are scanned
 * (built-in directory and/or customer directories).
 */
final class IconRegistry
{
    /**
     * @var array<int, string>
     */
    private const array LABEL_PREFIXES = ['bi-', 'custom-'];

    /**
     * Overrides for icons whose auto-derived label would look off (brand names
     * and acronyms). Keyed by sprite symbol-id.
     *
     * @var array<string, string>
     */
    private const array LABEL_OVERRIDES = [
        'bi-twitter-x' => 'Twitter / X',
        'bi-link-45deg' => 'Web-Link',
        'bi-linkedin' => 'LinkedIn',
        'bi-github' => 'GitHub',
        'bi-tiktok' => 'TikTok',
        'bi-youtube' => 'YouTube',
        'custom-typo3' => 'TYPO3',
        'custom-xing' => 'Xing',
        'custom-youku' => 'Youku',
    ];

    /**
     * Collect all available icons from the given directories, indexed by their
     * sprite symbol-id and mapped to a human-readable label.
     *
     * @param array<int, string> $directories icon directories (EXT: paths)
     * @return array<string, string> symbol-id => label
     */
    public function collect(array $directories): array
    {
        $icons = [];

        foreach ($directories as $iconDirectory) {
            $absoluteDirectory = GeneralUtility::getFileAbsFileName($iconDirectory);
            if ($absoluteDirectory === '') {
                continue;
            }

            foreach (glob($absoluteDirectory . '*.svg') ?: [] as $svgFile) {
                $name = basename($svgFile, '.svg');
                $icons[$name] = $this->deriveLabel($name);
            }
        }

        return $icons;
    }

    /**
     * Derive a human-readable label from a sprite symbol-id: strip the leading
     * prefix, turn dashes into spaces and Title-Case the words. Special cases
     * are corrected via the override map.
     */
    public function deriveLabel(string $symbolId): string
    {
        if (isset(self::LABEL_OVERRIDES[$symbolId])) {
            return self::LABEL_OVERRIDES[$symbolId];
        }

        $withoutPrefix = $symbolId;
        foreach (self::LABEL_PREFIXES as $prefix) {
            if (str_starts_with($withoutPrefix, $prefix)) {
                $withoutPrefix = substr($withoutPrefix, strlen($prefix));
                break;
            }
        }

        return ucwords(str_replace('-', ' ', $withoutPrefix));
    }
}
