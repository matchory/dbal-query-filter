<?php

declare(strict_types=1);

namespace Codin\DBAL\Filters;

use Closure;
use Doctrine\DBAL\Query\QueryBuilder;

class Callback extends Filter
{
    protected Closure $callback;

    public function __construct(string $queryName, callable $callback)
    {
        $this->callback = Closure::fromCallable($callback);
        parent::__construct($queryName);
    }

    public function build(QueryBuilder $query, array $params): void
    {
        Closure::bind($this->callback, $this)($query, $params);
    }
}
