<?php

namespace Digbang\Utils;

class Sorting
{
    /**
     * ['name' => 'asc'].
     */
    protected $sorts;

    public function __construct(array $data, array $defaults = [])
    {
        $this->sorts = array_only($data, static::getKeys());

        if (empty($this->sorts)) {
            $this->sorts = array_only($defaults, static::getKeys());
        }
    }

    public function get(array $sortFields): array
    {
        //Validate keys

        $selected = [];
        foreach ($this->sorts as $sortKey => $direction) {
            $fields = $sortFields[$sortKey];

            if (is_string($fields)) {
                $selected[$fields] = $direction;
                continue;
            }

            if (is_array($fields)) {
                $selected = array_merge($selected, array_combine($fields, array_fill(0, count($fields), $direction)));
                continue;
            }

            if (is_callable($fields)) {
                $selected = array_merge($selected, $fields($direction));
                continue;
            }

            throw new \LogicException('Invalid sort field declaration.');
        }

        return $selected;
    }

    public function getRaw(): array
    {
        return $this->sorts;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->sorts);
    }

    private static function getKeys(): array
    {
        $oClass = new \ReflectionClass(get_called_class());

        return array_values($oClass->getConstants());
    }
}
