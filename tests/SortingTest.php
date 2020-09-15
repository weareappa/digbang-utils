<?php

namespace Digbang\Utils\Tests;

use PHPUnit\Framework\TestCase;

class SortingTest extends TestCase
{
    private $alias;
    private $sortFields;

    protected function setUp(): void
    {
        $this->alias = 'alias';

        $this->sortFields = [
            ProductSortingStub::NAME => "{$this->alias}.name",
            ProductSortingStub::BRAND => "{$this->alias}.brand.name",
            ProductSortingStub::OTHER => [
                "{$this->alias}.other",
                "{$this->alias}.name",
            ],
            ProductSortingStub::INVERTED => function ($direction) {
                return [
                    'name' => 'not ' . $direction,
                ];
            },
        ];
    }

    public function test_simple()
    {
        $sorting = new ProductSortingStub([ProductSortingStub::NAME => 'asc']);

        $applied = $sorting->get($this->sortFields);

        static::assertEquals(["{$this->alias}.name" => 'asc'], $applied);
    }

    public function test_array_sort()
    {
        $sorting = new ProductSortingStub([ProductSortingStub::OTHER => 'desc']);

        $applied = $sorting->get($this->sortFields);

        static::assertEquals([
            "{$this->alias}.other" => 'desc',
            "{$this->alias}.name" => 'desc',
        ],
            $applied);
    }

    public function test_function_sort()
    {
        $sorting = new ProductSortingStub([ProductSortingStub::INVERTED => 'desc']);

        $applied = $sorting->get($this->sortFields);

        static::assertEquals([
            'name' => 'not desc',
        ],
            $applied);
    }

    public function test_multiple_sorts()
    {
        $sorting = new ProductSortingStub([
            ProductSortingStub::BRAND => 'desc',
            ProductSortingStub::OTHER => 'asc',
            ProductSortingStub::INVERTED => 'asc',
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
        $sorting = new ProductSortingStub(['not existent' => 'asc', ProductSortingStub::BRAND => 'desc']);

        $applied = $sorting->get($this->sortFields);

        static::assertEquals(["{$this->alias}.brand.name" => 'desc'], $applied);
    }
}
