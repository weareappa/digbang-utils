<?php

namespace Digbang\Utils\Enumerables;

abstract class EnumBase implements \JsonSerializable
{
    abstract public function __toString();

    abstract public function getValue();

    /**
     * @return EnumBase[]
     */
    abstract public static function getAllAsObjects(): array;

    public function jsonSerialize()
    {
        return $this->getValue();
    }

    public static function getAllValues(): array
    {
        $oClass = new \ReflectionClass(get_called_class());

        return array_values($oClass->getConstants());
    }

    public static function getAllValuesTranslated(): array
    {
        return array_combine(
            static::getAllValues(),
            array_map(
                function (EnumBase $value) {
                    return $value->translate();
                },
                static::getAllAsObjects()
            )
        );
    }

    /**
     * This requires the Laravel trans function so don't use it outside Laravel "context".
     */
    public function translate(): string
    {
        $oClass = get_called_class();
        $value = $this->getValue();

        return (string) trans("enum.$oClass.$value");
    }

    /**
     * Returns all values as key and the constant name as value.
     */
    public static function getAllInversed(): array
    {
        $oClass = new \ReflectionClass(get_called_class());

        $mappedValues = [];
        foreach ($oClass->getConstants() as $name => $value) {
            $mappedValues[$value] = strtolower($name);
        }

        return $mappedValues;
    }

    /**
     * Count of all possible values.
     */
    protected static function count(): int
    {
        $oClass = new \ReflectionClass(get_called_class());

        return count($oClass->getConstants());
    }
}
