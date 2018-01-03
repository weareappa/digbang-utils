<?php

namespace Digbang\Utils;

abstract class Filter
{
    protected $values = [];

    public function __construct(array $data)
    {
        $oClass = new \ReflectionClass(get_called_class());
        $keys = array_values($oClass->getConstants());

        $this->values = array_only($data, $keys);
    }

    /**
     * Returns NULL if value is empty() === true.
     */
    public function get(string $key, $default = null)
    {
        $value = array_get($this->values, $key, $default);

        return empty($value) ? null : $value;
    }

    public function getRaw(string $key, $default = null)
    {
        return array_get($this->values, $key, $default);
    }

    /**
     * Returns an empty array as default.
     */
    public function getArray(string $key)
    {
        $value = $this->getRaw($key);

        return empty($value) ? [] : $value;
    }

    public function has(string $key)
    {
        return array_has($this->values, $key);
    }

    public function isNotEmpty(string $key)
    {
        return ! empty($this->getRaw($key));
    }

    public function values()
    {
        return $this->values;
    }
}
