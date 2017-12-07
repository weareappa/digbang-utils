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

    /** @return string|null */
    public function getValue()
    {
        return $this->value;
    }


    /**
     * @param string|null $name
     *
     * @return bool
     */
    public function is(string $name = null)
    {
        return $this->value === $name;
    }

    /**
     * @param string[] $names
     *
     * @return bool
     */
    public function isAny(array $names)
    {
        return in_array($this->value, $names, true);
    }

    /**
     * @param string|null $name
     *
     * @return bool
     */
    public function isNot(string $name = null)
    {
        return $this->value !== $name;
    }

    /**
     * @param array $names
     *
     * @return bool
     */
    public function isNotAny(array $names)
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
        }, array_filter(static::getAllValues()));
    }


    /**
     * @param string|null $name
     *
     * @throws \InvalidArgumentException
     *
     * @return Enum|mixed
     */
    public static function from(string $name = null)
    {
        return new static ($name);
    }

    private static function assert(string $name = null)
    {
        if (! in_array($name, static::getAllValues(), true)) {
            $oClass = new \ReflectionClass(get_called_class());

            throw new \InvalidArgumentException('enum.' . camel_case($oClass->getShortName()) . '.notFound');
        }
    }
}
