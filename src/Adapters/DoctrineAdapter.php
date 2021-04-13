<?php

declare(strict_types=1);

namespace Codin\DBAL\Adapters;

use Closure;
use Codin\DBAL\Filters\CallbackFilter;
use Codin\DBAL\Filters\ContainsFilter;
use Codin\DBAL\Filters\MatchFilter;
use Codin\DBAL\Filters\NullableFilter;
use Codin\DBAL\Filters\RangeFilter;
use Doctrine\DBAL\Query\QueryBuilder;
use RuntimeException;

use function array_intersect_key;
use function array_values;
use function implode;
use function sprintf;

class DoctrineAdapter extends AbstractAdapter
{
    protected QueryBuilder $builder;

    public function __construct(QueryBuilder $builder)
    {
        $this->builder = $builder;
    }

    public function getBuilder(): QueryBuilder
    {
        return $this->builder;
    }

    public function setBuilder(QueryBuilder $builder): void
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

    public function bindParams(string $name, array $values): array
    {
        $params = [];

        foreach (array_values($values) as $index => $value) {
            $params[] = ':' . $name . $index;
            $this->builder->setParameter($name . $index, $value);
        }

        return $params;
    }

    protected function applyContains(
        ContainsFilter $filter,
        array $params
    ): void {
        $builder = $this->builder;

        $bindings = implode(',', $this->bindParams(
            $filter->getSubject(),
            $params[$filter->getParameter()]
        ));

        $builder->andWhere(sprintf(
            '%s %s (%s)',
            $filter->getSubject(),
            $filter->isNotIn() ? 'not in' : 'in',
            $bindings
        ));
    }

    protected function applyMatch(
        MatchFilter $filter,
        array $params
    ): void {
        $value = $params[$filter->getParameter()];

        $this->builder
            ->andWhere(sprintf(
                '%1$s = :%1$s',
                $filter->getSubject()
            ))
            ->setParameter($filter->getSubject(), $value);
    }

    protected function applyRange(
        RangeFilter $filter,
        array $params
    ): void {
        $operators = array_intersect_key(
            $filter->getVariants(),
            $params
        );

        foreach ($operators as $name => $operator) {
            $value = $params[$name];

            $this->builder
                ->andWhere(sprintf(
                    '%s %s :%s',
                    $filter->getSubject(),
                    $operator,
                    $name
                ))
                ->setParameter($name, $value);
        }
    }

    protected function applyNullable(NullableFilter $filter): void
    {
        $this->builder->andWhere(sprintf(
            '%s %s null',
            $filter->getSubject(),
            $filter->isNotNull() ? 'is not' : 'is'
        ));
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
