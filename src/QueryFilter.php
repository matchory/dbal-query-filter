<?php

declare(strict_types=1);

namespace Codin\DBAL;

use Doctrine\DBAL\Query\QueryBuilder;

class QueryFilter
{
    protected array $definitions = [];

    public function map(Filters\Filter $filter): self
    {
        $this->definitions[$filter->getQueryParam()] = $filter;

        return $this;
    }

    public function __call(string $method, array $args): self
    {
        $name = __NAMESPACE__ . '\\Filters\\'.ucfirst($method);
        if (class_exists($name)) {
            return $this->map(new $name(...$args));
        }
        throw new \ErrorException('Filter does not exist: '.$name);
    }

    public function build(QueryBuilder $query, array $params): void
    {
        $filters = array_filter(
            $this->definitions,
            static function (Filters\Filter $filter) use ($params): bool {
                return $filter->validate($params);
            }
        );
        foreach ($filters as $filter) {
            $filter->build($query, $params);
        }
    }
}
