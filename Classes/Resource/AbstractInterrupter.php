<?php

declare(strict_types=1);

namespace StarterTeam\StarterNessa\Resource;

use Override;
use TYPO3\CMS\Core\Resource\FileReference;

abstract class AbstractInterrupter implements InterrupterInterface
{
    protected array $properties = [];

    protected int $interval = 0;

    protected string $internalTitle;

    protected int $uid = 0;

    protected string $layout;

    protected array $assets = [];

    protected bool $deleted = false;

    #[Override]
    public function hasProperty(string $key): bool
    {
        return array_key_exists($key, $this->properties);
    }

    #[Override]
    public function getProperty(string $key): mixed
    {
        if ($this->hasProperty($key)) {
            return $this->properties[$key];
        }
        return null;
    }

    #[Override]
    public function getProperties(): array
    {
        return $this->properties;
    }

    public function getAssets(): array
    {
        return $this->assets;
    }

    public function getFirstAsset(): ?FileReference
    {
        return !empty($this->assets) ? $this->assets[0] : null;
    }

    #[Override]
    public function getInternalTitle(): string
    {
        return $this->internalTitle;
    }

    #[Override]
    public function getInterval(): int
    {
        return $this->interval;
    }

    public function getUid(): int
    {
        return $this->uid;
    }

    #[Override]
    public function getLayout(): string
    {
        return $this->layout;
    }

    public function isDeleted(): bool
    {
        return $this->deleted;
    }
}
