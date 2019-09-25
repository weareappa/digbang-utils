<?php

namespace Digbang\Utils\Doctrine;

use Digbang\Utils\Sorting;
use Doctrine\ORM\QueryBuilder;

class QueryBuilderDecorator extends QueryBuilder
{
    public function __construct(QueryBuilder $queryBuilder)
    {
        parent::__construct($queryBuilder->getEntityManager());
    }

    /**
     * @return QueryBuilderDecorator
     */
    public function select($select = null)
    {
        /** @var static $queryBuilder */
        $queryBuilder = parent::select($select);

        return $queryBuilder;
    }

    /**
     * @return QueryBuilderDecorator
     */
    public function from($from, $alias, $indexBy = null)
    {
        /** @var static $queryBuilder */
        $queryBuilder = parent::from($from, $alias, $indexBy);

        return $queryBuilder;
    }

    /**
     * Adds "order by" statement if sort is found in sortOptions.
     * Returns true if order by was added.
     * sortOptions example:
     * [
     *   'sortKeySentInPaginationData' => 'actualFieldDefinitionForThatKey',
     *   'aliasJoin' => 'actualFieldDefinitionForThatKey',
     *   'nameFieldValueObject' => [
     *      'actualFieldDefinitionForThatKey',
     *      'actualFieldDefinitionForThatKey'
     *    ]
     * ].
     *
     * @param Sorting $sorting
     * @param array $sortOptions
     *
     * @return QueryBuilderDecorator
     */
    public function addSorting(Sorting $sorting, array $sortOptions): QueryBuilderDecorator
    {
        foreach ($sorting->get($sortOptions) as $sortBy => $sortSense) {
            $this->addOrderBy($sortBy, $sortSense);
        }

        return $this;
    }

    /**
     * Adds "order by" statement with raw PaginationData sorting values
     * Returns true if order by was added.
     *
     * @param Sorting $sorting
     *
     * @return QueryBuilderDecorator
     */
    public function addRawSorting(Sorting $sorting): QueryBuilderDecorator
    {
        foreach ($sorting->getRaw() as $sortBy => $sortSense) {
            $this->addOrderBy($sortBy, $sortSense);
        }

        return $this;
    }

    /**
     * Adds "join" and "leftJoin" statements.
     * join/leftJoin example:
     * [
     *   'aliasJoinA' => 'alias.fieldA',
     *   'aliasJoinB' => 'alias.fieldB',
     *   'aliasJoinC' => 'aliasJoinA.fieldA',
     * ].
     *
     * @param array $joins
     * @param array|null $leftJoins
     *
     * @return QueryBuilderDecorator
     */
    public function applyJoins(array $joins, array $leftJoins = null): QueryBuilderDecorator
    {
        foreach ($joins as $alias => $field) {
            $this->join($field, $alias);
        }

        if ($leftJoins) {
            foreach ($leftJoins as $alias => $field) {
                $this->leftJoin($field, $alias);
            }
        }

        return $this;
    }

    /**
     * Adds "andWhere's" statements for each filter.
     *
     * @param array $filters
     *
     * @return QueryBuilderDecorator
     */
    public function applyFilters(array $filters): QueryBuilderDecorator
    {
        $expr = $this->expr();
        if (! empty($filters)) {
            $this->where($expr->andX(...$filters));
        }

        return $this;
    }
}
