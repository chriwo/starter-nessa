<?php

declare(strict_types=1);

namespace StarterTeam\StarterNessa\Tca;

use StarterTeam\StarterNessa\Service\IconRegistry;

/**
 * TCA itemsProcFunc that fills an icon select field with exactly the icons that
 * were actually built into the frontend icon directory. This guarantees that
 * the dropdown can never offer an icon that is missing from the sprite.
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
        $icons = $this->iconRegistry->collect();
        uasort($icons, $this->compareByLabel(...));

        $items = $params['items'] ?? [];
        if (is_array($items) === false) {
            $items = [];
        }

        foreach ($icons as $symbolId => $icon) {
            $items[] = [
                'label' => $icon['label'],
                'value' => $symbolId,
                'icon' => $icon['identifier'],
            ];
        }

        $params['items'] = $items;
    }

    /**
     * @param array{identifier: string, source: string, label: string} $left
     * @param array{identifier: string, source: string, label: string} $right
     */
    private function compareByLabel(array $left, array $right): int
    {
        return strcasecmp($left['label'], $right['label']);
    }
}
