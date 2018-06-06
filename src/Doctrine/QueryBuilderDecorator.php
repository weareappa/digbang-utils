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
     * @param QueryBuilder $builder
     * @param Sorting $sorting
     * @param array $sortOptions
     *
     * @return QueryBuilder
     */
    public function addSorting(QueryBuilder $builder, Sorting $sorting, array $sortOptions): QueryBuilder
    {
        foreach ($sorting->get($sortOptions) as $sortBy => $sortSense) {
            $builder->addOrderBy($sortBy, $sortSense);
        }

        return $builder;
    }

    /**
     * Adds "order by" statement with raw PaginationData sorting values
     * Returns true if order by was added.
     *
     * @param QueryBuilder $builder
     * @param Sorting $sorting
     *
     * @return QueryBuilder
     */
    public function addRawSorting(QueryBuilder $builder, Sorting $sorting): QueryBuilder
    {
        foreach ($sorting->getRaw() as $sortBy => $sortSense) {
            $builder->addOrderBy($sortBy, $sortSense);
        }

        return $builder;
    }

    /**
     * Adds "join" and "leftJoin" statements.
     * join/leftJoin example:
     * [
     *   'aliasJoinA' => 'alias.fieldA',
     *   'aliasJoinB' => 'alias.fieldB',
     *   'aliasJoinC' => 'aliasJoinA.fieldA',
     * ]
     *
     * @param QueryBuilder $builder
     * @param array $joins
     * @param array|null $leftJoins
     *
     * @return QueryBuilder
     */
    public function applyJoins(QueryBuilder $builder, array $joins, array $leftJoins = null): QueryBuilder
    {
        foreach ($joins as $alias => $field) {
            $builder->join($field, $alias);
        }

        if ($leftJoins) {
            foreach ($leftJoins as $alias => $field) {
                $builder->leftJoin($field, $alias);
            }
        }

        return $builder;
    }
}
