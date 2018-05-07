<?php

namespace Digbang\Utils\Tests;

use PHPUnit\Framework\TestCase;

class SortingTest extends TestCase
{
    private $alias;
    private $sortFields;

    protected function setUp()
    {
        $this->alias = 'alias';

        $this->sortFields = [
            SortingStub::NAME => "{$this->alias}.name",
            SortingStub::BRAND => "{$this->alias}.brand.name",
            SortingStub::OTHER => [
                "{$this->alias}.other",
                "{$this->alias}.name",
            ],
            SortingStub::INVERTED => function ($direction) {
                return [
                    'name' => 'not ' . $direction,
                ];
            },
        ];
    }

    public function test_simple()
    {
        $sorting = new SortingStub([SortingStub::NAME => 'asc']);

        $applied = $sorting->get($this->sortFields);

        static::assertEquals(["{$this->alias}.name" => 'asc'], $applied);
    }

    public function test_array_sort()
    {
        $sorting = new SortingStub([SortingStub::OTHER => 'desc']);

        $applied = $sorting->get($this->sortFields);

        static::assertEquals([
            "{$this->alias}.other" => 'desc',
            "{$this->alias}.name" => 'desc',
        ],
            $applied);
    }

    public function test_function_sort()
    {
        $sorting = new SortingStub([SortingStub::INVERTED => 'desc']);

        $applied = $sorting->get($this->sortFields);

        static::assertEquals([
            'name' => 'not desc',
        ],
            $applied);
    }

    public function test_multiple_sorts()
    {
        $sorting = new SortingStub([
            SortingStub::BRAND => 'desc',
            SortingStub::OTHER => 'asc',
            SortingStub::INVERTED => 'asc',
        ]);

        $applied = $sorting->get($this->sortFields);

        static::assertEquals([
            "{$this->alias}.brand.name" => 'desc',
            "{$this->alias}.other" => 'asc',
            "{$this->alias}.name" => 'asc',
            'name' => 'not asc',
        ],
            $applied);
    }

    public function test_invalid_keys_ignored()
    {
        $sorting = new SortingStub(['not existent' => 'asc', SortingStub::BRAND => 'desc']);

        $applied = $sorting->get($this->sortFields);

        static::assertEquals(["{$this->alias}.brand.name" => 'desc'], $applied);
    }
}
