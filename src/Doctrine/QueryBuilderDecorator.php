<?php

namespace Digbang\Utils\Doctrine;

use Digbang\Utils\Sorting;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Expr\From;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Expr\Select;
use Doctrine\ORM\QueryBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class QueryBuilderDecorator extends QueryBuilder
{
    public function __construct(QueryBuilder $queryBuilder)
    {
        parent::__construct($queryBuilder->getEntityManager());

        $this->decorateDQLParts($queryBuilder->getDQLParts());
    }

    /**
     * {@inheritDoc}
     *
     * @throws \InvalidArgumentException
     */
    public function from($from, $alias, $indexBy = null)
    {
        $singleAlias = $this->getSingleAlias($alias);

        /** @var From $part */
        foreach ($this->getDQLPart('from') as $part) {
            if ($part->getAlias() === $singleAlias && $part->getFrom() !== $from) {
                throw new \InvalidArgumentException("Duplicated FROM alias: $singleAlias.");
            }
        }

        return parent::from($from, $singleAlias, $indexBy);
    }

    /**
     * {@inheritDoc}
     */
    public function select($select = null)
    {
        $selects = is_array($select) ? $select : func_get_args();

        return parent::select(collect($selects)
            ->unique()
            ->toArray()
        );
    }

    /**
     * {@inheritDoc}
     */
    public function addSelect($select = null)
    {
        $selects = is_array($select) ? $select : func_get_args();
        $dqlSelectParts = collect();

        /** @var Select $part */
        foreach ($this->getDQLPart('select') as $part) {
            foreach ($part->getParts() as $element) {
                $dqlSelectParts->add($element);
            }
        }

        return parent::addSelect(collect($selects)
            ->unique()
            ->diff($dqlSelectParts)
            ->toArray()
        );
    }

    /**
     * {@inheritDoc}
     *
     * @throws \InvalidArgumentException
     */
    public function innerJoin($join, $alias, $conditionType = null, $condition = null, $indexBy = null)
    {
        $singleAlias = $this->getSingleAlias($alias);

        if ($this->isJoined($singleAlias, Join::INNER_JOIN)) {
            return $this->mergeJoinConditions($singleAlias, $conditionType, $condition);
        }

        return parent::innerJoin($join, $singleAlias, $conditionType, $condition, $indexBy);
    }

    /**
     * {@inheritDoc}
     *
     * @throws \InvalidArgumentException
     */
    public function leftJoin($join, $alias, $conditionType = null, $condition = null, $indexBy = null)
    {
        $singleAlias = $this->getSingleAlias($alias);

        if ($this->isJoined($singleAlias, Join::LEFT_JOIN)) {
            return $this->mergeJoinConditions($singleAlias, $conditionType, $condition);
        }

        return parent::leftJoin($join, $singleAlias, $conditionType, $condition, $indexBy);
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
     * @deprecated
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

    /**
     * Pre: Receive a DQLParts array.
     *
     * Post: Fill the current instance DQLParts with the given ones.
     *
     * @param array $dqlParts
     *
     * @return void
     */
    private function decorateDQLParts(array $dqlParts): void
    {
        $filledDQLParts = array_filter($dqlParts);

        foreach ($filledDQLParts as $key => $value) {
            $this->add($key, $value, false);
        }
    }

    /**
     * Pre: Receive an array or string like one of the following (with all it's variants):
     * a) 'a, b, c'
     * b) ['a as foo, b, c']
     * c) ['a as foo', 'b', 'c']
     *
     * Post: Return a normalized array like the following: ['a', 'b', 'c']
     *
     * Normalized array means no NULL, empty strings, duplicates or ' ' like should be inside of it.
     *
     * @param string|array $alias
     *
     * @return Collection
     */
    private function normalizeAlias($alias): Collection
    {
        return Str::of(collect($alias)->join(','))
            ->explode(',')
            ->map(function (string $value) {
                return trim($value);
            })
            ->filter()
            ->unique()
            ->values();
    }

    /**
     * Pre: Receive an alias
     *
     * Post: Return the first normalized alias if it has only one, otherwise thrown an \InvalidArgumentException if the alias is empty or has more than one alias
     *
     * @param null|string|array $alias
     *
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    private function getSingleAlias($alias): string
    {
        $normalizedAlias = $this->normalizeAlias($alias);

        if ($normalizedAlias->count() !== 1) {
            throw new \InvalidArgumentException('There should be one alias on the current context.');
        }

        return $normalizedAlias->first();
    }

    /**
     * Pre: Receive two strings: join alias and type.
     *
     * Post: Return a true if the join was defined before, otherwhise return false. \InvalidArgumentException may be
     * thrown if the alias was defined for a different join type.
     *
     * @param string $alias
     * @param string $joinType
     *
     * @throws \InvalidArgumentException
     *
     * @return bool
     */
    private function isJoined(string $alias, string $joinType): bool
    {
        /** @var Join[] $joins */
        $joins = collect($this->getDQLPart('join'))->flatten();

        foreach ($joins as $join) {
            if ($join->getAlias() !== $alias) {
                continue;
            }

            if ($join->getJoinType() === $joinType) {
                return true;
            }

            throw new \InvalidArgumentException("Alias '$alias' is defined for a different join type: {$join->getJoinType()}.");
        }

        return false;
    }

    /**
     * Pre: Receive the alias, condition type and the join condition as strings.
     *
     * Post: Merge the given condition with the desired join.
     *
     * @param string $alias
     * @param null|string $conditionType
     * @param null|string|Expr\Comparison|Expr\Func|Expr\Orx $condition
     *
     * @return QueryBuilderDecorator
     */
    private function mergeJoinConditions(string $alias, ?string $conditionType = Join::WITH, $condition = null): self
    {
        if (is_null($condition)) {
            return $this;
        }

        /** @var Join[] $joins */
        foreach ($this->getDQLPart('join') as $key => $joins) {
            foreach ($joins as $index => $join) {
                if ($join->getAlias() !== $alias) {
                    continue;
                }

                $newConditionType = in_array(Join::ON, [$join->getConditionType(), $conditionType]) ? Join::ON : Join::WITH;
                $newCondition = $this->expr()->andX($join->getCondition(), $condition);

                $joins[$index] = new Join(
                    $join->getJoinType(),
                    $join->getJoin(),
                    $join->getAlias(),
                    $newConditionType,
                    $newCondition,
                    $join->getIndexBy()
                );

                return $this->add('join', [$key => $joins], false);
            }
        }

        return $this;
    }
}
