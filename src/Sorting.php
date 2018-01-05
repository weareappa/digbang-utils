<?php

namespace Digbang\Utils;

class Sorting
{
    /**
     * ['name' => 'asc'].
     */
    protected $sorts;

    public function __construct(array $data)
    {
        $this->sorts = array_only($data, static::getKeys());
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

    /**
     * @return string[]
     */
    private static function getKeys(): array
    {
        $oClass = new \ReflectionClass(get_called_class());

        return array_values($oClass->getConstants());
    }
}
