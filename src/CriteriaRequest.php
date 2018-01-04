<?php

namespace Digbang\Utils;

use Illuminate\Http\Request;

/**
 * Example usage:

    class UserCriteriaRequest extends CriteriaRequest
    {
       protected function getFilterClass(): string
       {
            return UserFilter::class;
       }

       protected function getFilterClass(): string
       {
            return UserSorting::class;
       }
    }
*/

abstract class CriteriaRequest implements Criteria
{
    /** @var Request */
    protected $request;
    /** @var string */
    protected $sortRequestKey = 'sort';
    /** @var string */
    protected $limitRequestKey = 'limit';
    /** @var string */
    protected $pageRequestKey = 'page';

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function getFilter(): Filter
    {
        $filterClass = $this->getFilterClass();

        return new $filterClass($this->request->all());
    }

    public function getSorting(): Sorting
    {
        $sortingClass = $this->getSortingClass();

        return new $sortingClass($this->request->input($this->sortRequestKey, []));
    }

    public function getPaginationData(): PaginationData
    {
        return new PaginationData(
            $this->request->input($this->limitRequestKey, 10),
            $this->request->input($this->pageRequestKey, 1)
        );
    }

    abstract protected function getFilterClass(): string;
    abstract protected function getSortingClass(): string;
}
