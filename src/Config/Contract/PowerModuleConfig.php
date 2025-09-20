<?php

namespace Modular\Framework\Config\Contract;

use BackedEnum;
use SplObjectStorage;
use UnitEnum;

abstract class PowerModuleConfig
{
    /**
     * @var SplObjectStorage<\BackedEnum|\UnitEnum,mixed>
     */
    protected SplObjectStorage $properties;

    final public function __construct()
    {
        $this->properties = new SplObjectStorage();
    }

    public static function create(): static
    {
        return new static();
    }

    /**
     * Returns config filename with custom/overwritten params.
     */
    abstract public function getConfigFilename(): string;

    /**
     * Returns all config properties.
     *
     * @return iterable<BackedEnum|UnitEnum|object,mixed>
     */
    public function getAll(): iterable
    {
        foreach ($this->properties as $enum) {
            yield $enum => $this->properties->getInfo();
        }
    }

    public function has(BackedEnum|UnitEnum $configPropertyEnum): bool
    {
        return $this->properties->offsetExists($configPropertyEnum);
    }

    public function get(BackedEnum|UnitEnum $configPropertyEnum): mixed
    {
        if (!$this->properties->offsetExists($configPropertyEnum)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Config property "%s" does not exist in config "%s".',
                    $configPropertyEnum->name,
                    static::class,
                ),
            );
        }

        return $this->properties->offsetGet($configPropertyEnum);
    }

    public function set(BackedEnum|UnitEnum $configPropertyEnum, mixed $value): static
    {
        $this->properties->offsetSet($configPropertyEnum, $value);

        return $this;
    }
}
