<?php

namespace Digbang\Utils;

interface Criteria
{
    public function getFilter(): Filter;

    public function getSorting(): Sorting;

    public function getPaginationData(): PaginationData;
}
