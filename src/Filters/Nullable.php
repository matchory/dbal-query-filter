<?php

declare(strict_types=1);

namespace Codin\DBAL\Filters;

use Doctrine\DBAL\Query\QueryBuilder;

class Nullable extends Filter
{
    protected bool $notNull;

    public function __construct(string $queryName, ?string $columnName = null, bool $notNull = false)
    {
        $this->notNull = $notNull;
        parent::__construct($queryName, $columnName);
    }

    public function validate(array $params): bool
    {
        return array_key_exists($this->queryName, $params) && $params[$this->queryName];
    }

    public function build(QueryBuilder $query, array $params): void
    {
        $query->andWhere(sprintf('%s %s null', $this->getColumnName(), $this->notNull ? 'is not' : 'is'));
    }
}
