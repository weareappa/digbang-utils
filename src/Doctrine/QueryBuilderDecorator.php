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
