<?php

declare(strict_types=1);

namespace StarterTeam\StarterNessa\Service;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Collects the SVG icons that were actually built into the frontend icon
 * directory (and any directory a customer project registered) and derives a
 * human-readable label per icon.
 *
 * This is the single source of truth for both the TYPO3 backend icon registry
 * (see Configuration/Icons.php) and the dynamic TCA select items (see
 * IconItemsProvider).
 *
 * It must not depend on any injected services because it is called from
 * Configuration/Icons.php, which runs before the dependency injection
 * container is available.
 */
final class IconRegistry
{
    private const string BUILT_IN_ICON_DIRECTORY = 'EXT:starter_nessa/Resources/Public/Frontend/Icons/';

    private const string IDENTIFIER_PREFIX = 'starter-nessa-';

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
     * Collect all available icons, indexed by their sprite symbol-id.
     *
     * @return array<string, array{identifier: string, source: string, label: string}>
     */
    public function collect(): array
    {
        $icons = [];

        foreach ($this->getIconDirectories() as $iconDirectory) {
            $absoluteDirectory = GeneralUtility::getFileAbsFileName($iconDirectory);
            if ($absoluteDirectory === '') {
                continue;
            }

            foreach (glob($absoluteDirectory . '*.svg') ?: [] as $svgFile) {
                $name = basename($svgFile, '.svg');
                $icons[$name] = [
                    'identifier' => self::IDENTIFIER_PREFIX . $name,
                    'source' => rtrim($iconDirectory, '/') . '/' . $name . '.svg',
                    'label' => $this->deriveLabel($name),
                ];
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

    /**
     * The built-in starter-nessa directory first, followed by any directory a
     * customer project registered via the extension configuration.
     *
     * @return array<int, string>
     */
    private function getIconDirectories(): array
    {
        $directories = [self::BUILT_IN_ICON_DIRECTORY];

        foreach ($this->getRegisteredIconDirectories() as $directory) {
            if (is_string($directory) && $directory !== '') {
                $directories[] = $directory;
            }
        }

        return $directories;
    }

    /**
     * @return array<int|string, mixed>
     */
    private function getRegisteredIconDirectories(): array
    {
        $extensions = $this->asArray($GLOBALS['TYPO3_CONF_VARS'] ?? null)['EXTENSIONS'] ?? null;
        $configuration = $this->asArray($extensions)['starter_nessa'] ?? null;
        $registered = $this->asArray($configuration)['iconDirectories'] ?? null;

        return $this->asArray($registered);
    }

    /**
     * @return array<int|string, mixed>
     */
    private function asArray(mixed $value): array
    {
        return is_array($value) ? $value : [];
    }
}
