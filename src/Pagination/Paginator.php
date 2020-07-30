<?php

namespace Digbang\Utils\Pagination;

use Digbang\Utils\PaginationData;
use Doctrine\ORM\Query;

trait Paginator
{
    public function paginate(Query $query, PaginationData $paginationData, bool $fetchJoinCollection = true, $resultsAreScalar = false): EntityPagination
    {
        return (new PaginatorAdapter())->make(
            $query,
            $paginationData,
            $fetchJoinCollection,
            $resultsAreScalar
        );
    }
}
