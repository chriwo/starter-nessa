<?php

declare(strict_types=1);

namespace StarterTeam\StarterNessa\Tca;

use StarterTeam\StarterNessa\Service\IconRegistry;
use TYPO3\CMS\Core\Site\Entity\Site;

/**
 * TCA itemsProcFunc that fills an icon select field with exactly the icons that
 * are available for the current site: every *.svg found in the directories the
 * site lists via the "starter.icons.directories" setting (the built-in theme
 * directory is the first default entry there).
 *
 * The site is resolved by FormEngine from the edited record's page and passed
 * in via $params['site'], so the dropdown is site-aware in multi-site setups.
 *
 * itemsProcFunc instances are created via makeInstance, so the IconRegistry can
 * be injected via the constructor (keeps it testable).
 */
final class IconItemsProvider
{
    public function __construct(private readonly IconRegistry $iconRegistry)
    {
    }

    /**
     * @param array<string, mixed> $params
     */
    public function populate(array &$params): void
    {
        $icons = $this->iconRegistry->collect($this->resolveDirectories($params['site'] ?? null));
        asort($icons, SORT_FLAG_CASE | SORT_STRING);

        $items = $params['items'] ?? [];
        if (is_array($items) === false) {
            $items = [];
        }

        foreach ($icons as $symbolId => $label) {
            $items[] = [
                'label' => $label,
                'value' => $symbolId,
            ];
        }

        $params['items'] = $items;
    }

    /**
     * The icon directories this site lists (built-in directory first by default).
     *
     * @return array<int, string>
     */
    private function resolveDirectories(mixed $site): array
    {
        if ($site instanceof Site === false) {
            return [];
        }

        return $this->asStringList($site->getSettings()->get('starter.icons.directories', []));
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
