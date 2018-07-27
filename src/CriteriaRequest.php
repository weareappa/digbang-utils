<?php

namespace Digbang\Utils;

use Illuminate\Http\Request;

/**
    Example usage:

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

/**
 * @method all($keys = null)
 * @method only($keys)
 * @method except($keys)
 * @method get($key, $default = null)
 * @method input($key = null, $default = null)
 * @method exists($key)
 * @method has($key)
 * @method user($guard = null)
 */
abstract class CriteriaRequest implements Criteria
{
    public const LIMIT_KEY = 'limit';
    public const LIMIT_DEFAULT = 10;
    public const PAGE_KEY = 'page';
    public const SORT_KEY = 'sort';

    /** @var Request */
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Magic call method works as a proxy for the request.
     *
     * @throws \BadMethodCallException
     */
    public function __call($method, $args)
    {
        if (method_exists($this->request, $method)) {
            return call_user_func_array([$this->request, $method], $args);
        }

        throw new \BadMethodCallException("Request method [{$method}] does not exist.");
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getFilter(): Filter
    {
        $filterClass = $this->getFilterClass();

        return new $filterClass($this->request->all());
    }

    public function getSorting(): Sorting
    {
        $sortingClass = $this->getSortingClass();

        return new $sortingClass($this->buildSorting(), $this->getSortingDefaults());
    }

    public function getPaginationData(): PaginationData
    {
        return new PaginationData(
            $this->request->input(static::LIMIT_KEY, static::LIMIT_DEFAULT),
            $this->request->input(static::PAGE_KEY, 1)
        );
    }

    abstract protected function getFilterClass(): string;

    abstract protected function getSortingClass(): string;

    protected function buildSorting(): array
    {
        return $this->request->input(static::SORT_KEY, []);
    }

    protected function getSortingDefaults(): array
    {
        return [];
    }
}
