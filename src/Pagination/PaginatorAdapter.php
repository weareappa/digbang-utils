<?php

namespace Digbang\Utils\Pagination;

use Digbang\Utils\PaginationData;
use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;

class PaginatorAdapter
{
    public function make(Query $query, PaginationData $paginationData, bool $fetchJoinCollection = true, bool $resultsAreScalar = false): EntityPagination
    {
        if ($paginationData->getLimit()) {
            $query->setFirstResult($paginationData->getOffset());
            $query->setMaxResults($paginationData->getLimit());

            $doctrinePaginator = new DoctrinePaginator($query, $fetchJoinCollection);
            $results = iterator_to_array($doctrinePaginator);
            $count = $this->count($doctrinePaginator, $resultsAreScalar);
        } else {
            $results = $this->getResults($query, $resultsAreScalar);
            $count = count($results);
            // if zero results, fake a limit so paging calculations don't explode with division by zero
            $paginationData = $paginationData->clone($count ?: 1, 1);
        }

        return new EntityPagination($results, $count, $paginationData);
    }

    protected function getResults(Query $query, bool $resultsAreScalar = false): array
    {
        if ($resultsAreScalar) {
            return $query->getScalarResult();
        }

        return $query->getResult();
    }

    /**
     * @return int
     */
    protected function count(DoctrinePaginator $doctrinePaginator, bool $resultsAreScalar = false)
    {
        $useOutputWalkers = $doctrinePaginator->getUseOutputWalkers();

        if ($resultsAreScalar) {
            $doctrinePaginator->setUseOutputWalkers(false);
        }

        $count = $doctrinePaginator->count();

        $doctrinePaginator->setUseOutputWalkers($useOutputWalkers);

        return $count;
    }
}
