<?php

declare(strict_types=1);

namespace StarterTeam\StarterNessa\Resource;

interface InterrupterInterface
{
    public function hasProperty(string $key): bool;

    public function getInternalTitle(): string;

    public function getProperties(): array;

    public function getProperty(string $key): mixed;

    public function getInterval(): int;

    public function getLayout(): string;

    public function toArray(): array;
}
