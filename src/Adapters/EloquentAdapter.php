<?php

declare(strict_types=1);

namespace Codin\DBAL\Adapters;

use Closure;
use Codin\DBAL\Filters\CallbackFilter;
use Codin\DBAL\Filters\ContainsFilter;
use Codin\DBAL\Filters\MatchFilter;
use Codin\DBAL\Filters\NullableFilter;
use Codin\DBAL\Filters\RangeFilter;
use Illuminate\Database\Eloquent\Builder;
use RuntimeException;

use function array_intersect_key;

class EloquentAdapter extends AbstractAdapter
{
    protected Builder $builder;

    public function __construct(Builder $builder)
    {
        $this->builder = $builder;
    }

    public function getBuilder(): Builder
    {
        return $this->builder;
    }

    public function setBuilder(Builder $builder): void
    {
        $this->builder = $builder;
    }

    /**
     * Proxies method calls to the query builder instance to allow fluent
     * call chaining.
     *
     * @param string $method
     * @param array  $args
     *
     * @return mixed
     */
    public function __call(string $method, array $args)
    {
        return $this->getBuilder()->$method(...$args);
    }

    /**
     * @param ContainsFilter $filter
     * @param array          $params
     */
    protected function applyContains(
        ContainsFilter $filter,
        array $params
    ): void {
        $column = $filter->getSubject();
        $values = $params[$filter->getParameter()];

        $filter->isNotIn()
            ? $this->builder->whereNotIn($column, $values)
            : $this->builder->whereIn($column, $values);
    }

    protected function applyMatch(
        MatchFilter $filter,
        array $params
    ): void {
        $this->builder->where(
            $filter->getSubject(),
            $params[$filter->getParameter()]
        );
    }

    protected function applyRange(
        RangeFilter $filter,
        array $params
    ): void {
        $column = $filter->getSubject();
        $operators = array_intersect_key(
            $filter->getVariants(),
            $params
        );

        foreach ($operators as $name => $operator) {
            $value = $params[$name];

            $this->builder->where(
                $column,
                $operator,
                $value
            );
        }
    }

    protected function applyNullable(NullableFilter $filter): void
    {
        $filter->isNotNull()
            ? $this->builder->whereNotNull($filter->getSubject())
            : $this->builder->whereNull($filter->getSubject());
    }

    /**
     * @param CallbackFilter $filter
     * @param array          $params
     *
     * @throws RuntimeException
     */
    protected function applyCallback(
        CallbackFilter $filter,
        array $params
    ): void {
        $callback = Closure::bind($filter->getCallback(), $this);

        if ( ! $callback) {
            throw new RuntimeException(
                "Failed to bind closure for {$filter->getSubject()}"
            );
        }

        $callback($this->builder, $params);
    }
}
