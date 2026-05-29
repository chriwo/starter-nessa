<?php

declare(strict_types=1);

namespace StarterTeam\StarterNessa\Resource;

use Doctrine\DBAL\Driver\Exception;
use InvalidArgumentException;
use Override;
use StarterTeam\StarterNessa\Resource\Exception\InterrupterDoesNotExistException;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Property\Exception\InvalidDataTypeException;

class InterrupterReference implements InterrupterInterface
{
    protected Interrupter $originalInterrupter;

    /**
     * Internal title of the original Interrupter object
     */
    protected string $name = '';

    protected array $mergedProperties = [];

    /**
     * @throws Exception
     * @throws InterrupterDoesNotExistException
     */
    public function __construct(
        protected array $propertiesOfInterrupterReference,
        ResourceFactory $factory,
    ) {
        if (!isset($this->propertiesOfInterrupterReference['uid_local'])) {
            throw new InvalidArgumentException('Incorrect reference to original interrupter given in InterrupterReference.', 1697706199);
        }
        $this->originalInterrupter = $this->getInterrupterObject((int)$this->propertiesOfInterrupterReference['uid_local'], $factory);
        $this->name = $this->originalInterrupter->getInternalTitle();
    }

    /**
     * @throws InterrupterDoesNotExistException
     * @throws Exception
     */
    private function getInterrupterObject(int $uidLocal, ResourceFactory $factory): Interrupter
    {
        if ($factory === null) {
            $factory = GeneralUtility::makeInstance(ResourceFactory::class);
        }

        return $factory->getInterrupterObject($uidLocal);
    }

    #[Override]
    public function getInternalTitle(): string
    {
        $value = $this->getProperty('internal_title');
        if (!is_string($value)) {
            throw new InvalidDataTypeException(
                'internalTitle only allow data type string, ' . gettype($value) . ' given.',
                1726739791
            );
        }

        return $value;
    }

    #[Override]
    public function getProperty(string $key): mixed
    {
        if (!$this->hasProperty($key)) {
            throw new InvalidArgumentException(
                'Property "' . $key . '" was not found in interrupter reference or original interrupter record',
                1697112696
            );
        }
        $properties = $this->getProperties();
        return $properties[$key];
    }

    #[Override]
    public function hasProperty(string $key): bool
    {
        return array_key_exists($key, $this->getProperties());
    }

    #[Override]
    public function getProperties(): array
    {
        if (empty($this->mergedProperties)) {
            $this->mergedProperties = $this->propertiesOfInterrupterReference;
            ArrayUtility::mergeRecursiveWithOverrule(
                $this->mergedProperties,
                $this->originalInterrupter->getProperties(),
                true,
                true,
                false
            );
            array_walk($this->mergedProperties, $this->restoreNonNullValuesCallback(...));
        }

        return $this->mergedProperties;
    }

    public function getHeader(): string
    {
        $value = $this->getProperty('header');
        if (!is_string($value)) {
            throw new InvalidDataTypeException(
                'header only allow data type string, ' . gettype($value) . ' given.',
                1726739792
            );
        }

        return $value;
    }

    public function getTeaser(): string
    {
        $value = $this->getProperty('teaser');
        if (!is_string($value)) {
            throw new InvalidDataTypeException(
                'teaser only allow data type string, ' . gettype($value) . ' given.',
                1726740110
            );
        }

        return $value;
    }

    public function getLink(): string
    {
        $value = $this->getProperty('link');
        if (!is_string($value)) {
            throw new InvalidDataTypeException(
                'link only allow data type string, ' . gettype($value) . ' given.',
                1726739793
            );
        }

        return $value;
    }

    public function getLinkText(): string
    {
        $value = $this->getProperty('link_text');
        if (!is_string($value)) {
            throw new InvalidDataTypeException(
                'linkText only allow data type string, ' . gettype($value) . ' given.',
                1726739794
            );
        }

        return $value;
    }

    public function getIdentifier(): int
    {
        $value = $this->getProperty('uid');
        if (!is_int($value)) {
            throw new InvalidDataTypeException(
                'identifier must be of type int, ' . gettype($value) . ' given.',
                1726739795
            );
        }

        return $value;
    }

    #[Override]
    public function getInterval(): int
    {
        $value = $this->getProperty('interval');
        if (MathUtility::canBeInterpretedAsInteger($value) === false) {
            throw new InvalidDataTypeException(
                'interval must be of type int, ' . gettype($value) . ' given.',
                1726739796
            );
        }

        return MathUtility::forceIntegerInRange($value, 0, PHP_INT_MAX);
    }

    #[Override]
    public function getLayout(): string
    {
        $value = $this->getProperty('layout');
        if (!is_string($value)) {
            throw new InvalidDataTypeException(
                'layout only allow data type string, ' . gettype($value) . ' given.',
                1726739797
            );
        }

        return $value;
    }

    #[Override]
    public function toArray(): array
    {
        return array_merge($this->originalInterrupter->toArray(), $this->propertiesOfInterrupterReference);
    }

    public function getOriginalInterrupter(): InterrupterInterface
    {
        return $this->originalInterrupter;
    }

    public function getReferenceProperty(int|string $key): null|string|int
    {
        if (!array_key_exists($key, $this->propertiesOfInterrupterReference)) {
            throw new InvalidArgumentException('Property "' . $key . '" of interrupter reference was not found.', 1697717133);
        }
        return $this->propertiesOfInterrupterReference[$key];
    }

    /**
     * Callback to handle the NULL value feature
     */
    protected function restoreNonNullValuesCallback(mixed &$value, int|string $key): void
    {
        if (array_key_exists($key, $this->propertiesOfInterrupterReference) && $this->propertiesOfInterrupterReference[$key] !== null) {
            $value = $this->propertiesOfInterrupterReference[$key];
        }
    }
}
