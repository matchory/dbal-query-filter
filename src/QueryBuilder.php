<?php

declare(strict_types=1);

namespace Codin\DBAL;

use Codin\DBAL\Interfaces\AdapterInterface;
use Codin\DBAL\Interfaces\FilterInterface;

use function array_filter;
use function class_exists;
use function ucfirst;

/**
 * @method $this callback(string $queryName, callable $callback)
 * @method $this contains(string $queryName, ?string $columnName = null)
 * @method $this match(string $queryName, ?string $columnName = null)
 * @method $this nullable(string $queryName, ?string $columnName = null)
 * @method $this range(string $queryName, ?string $columnName = null)
 */
class QueryBuilder
{
    /**
     * @var array<string, FilterInterface>
     */
    protected array $definitions = [];

    private AdapterInterface $adapter;

    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    public function map(FilterInterface $filter): self
    {
        $this->definitions[$filter->getParameter()] = $filter;

        return $this;
    }

    /**
     * Adds filters dynamically from method calls, or forwards the calls to the
     * current adapter if no filters match the call.
     *
     * @param string $method
     * @param array  $args
     *
     * @return $this|mixed
     */
    public function __call(string $method, array $args)
    {
        $name = sprintf(
            '%s\Filters\%sFilter',
            __NAMESPACE__,
            ucfirst($method)
        );

        if (class_exists($name)) {
            $filter = new $name(...$args);

            return $this->map($filter);
        }

        return $this->getAdapter()->{$method}(...$args);
    }

    /**
     * Applies all filters to the query.
     *
     * @param array<string, string> $params
     *
     * @return $this
     */
    public function build(array $params): self
    {
        $filters = array_filter(
            $this->definitions,
            static function (FilterInterface $filter) use ($params): bool {
                return $filter->validate($params);
            }
        );

        foreach ($filters as $filter) {
            $this->getAdapter()->apply($filter, $params);
        }

        return $this;
    }

    public function getAdapter(): AdapterInterface
    {
        return $this->adapter;
    }
}
