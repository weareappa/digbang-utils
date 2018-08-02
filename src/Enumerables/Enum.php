<?php

namespace Digbang\Utils\Enumerables;

abstract class Enum extends EnumBase
{
    /** @var string */
    protected $value;

    public function __construct(string $value = null)
    {
        static::assert($value);

        $this->value = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->getValue() ?? '';
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function is(string $name = null): bool
    {
        return $this->value === $name;
    }

    /**
     * @param string[] $names
     */
    public function isAny(array $names): bool
    {
        return in_array($this->value, $names, true);
    }

    public function isNot(string $name = null): bool
    {
        return $this->value !== $name;
    }

    public function isNotAny(array $names): bool
    {
        return ! $this->isAny($names);
    }

    /**
     * @return Enum[]
     */
    public static function getAllAsObjects(): array
    {
        return array_map(function (?string $value) {
            return new static($value);
        }, array_filter(static::getAllValues(), function ($value) {
            return ! \in_array($value, ['', null], true);
        }));
    }

    /**
     * @deprecated use fromString
     */
    public static function from(string $name = null)
    {
        return static::fromString($name);
    }

    public static function fromString(string $name = null)
    {
        return new static ($name);
    }

    protected static function assert(string $name = null)
    {
        if (! in_array($name, static::getAllValues(), true)) {
            $oClass = new \ReflectionClass(get_called_class());

            throw new \InvalidArgumentException('enum.' . camel_case($oClass->getShortName()) . '.notFound');
        }
    }
}
