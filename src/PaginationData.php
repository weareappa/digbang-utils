<?php

namespace Digbang\Utils;

class PaginationData
{
    /**
     * @var int|null
     */
    private $page;
    /**
     * @var int
     */
    private $limit;

    public function __construct(int $limit = null, int $page = 1)
    {
        $this->limit = $limit;
        $this->page = $page;
    }

    /**
     * @return int
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @return int
     */
    public function getOffset()
    {
        return $this->getLimit() * ($this->getPage() - 1);
    }

    /**
     * Set the page according to current limit.
     */
    public function setPageFromOffset(int $offset)
    {
        $this->page = floor($offset / $this->limit) + 1;
    }

    public function clone(int $limit = null, int $page = 1)
    {
        return new static($limit, $page);
    }
}
