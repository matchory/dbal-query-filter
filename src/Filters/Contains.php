<?php

declare(strict_types=1);

namespace Codin\DBAL\Filters;

use Doctrine\DBAL\Query\QueryBuilder;

class Contains extends Filter
{
    protected bool $notIn;

    public function __construct(string $queryName, ?string $columnName = null, bool $notIn = false)
    {
        $this->notIn = $notIn;
        parent::__construct($queryName, $columnName);
    }

    public function validate(array $params): bool
    {
        return array_key_exists($this->queryName, $params)
            && is_array($params[$this->queryName])
            && count($params[$this->queryName]);
    }

    public function build(QueryBuilder $query, array $params): void
    {
        $bindings = implode(',', $this->bindParams($query, $this->getColumnName(), $params[$this->queryName]));

        $query->andWhere(sprintf('%s %s (%s)', $this->getColumnName(), $this->notIn ? 'not in' : 'in', $bindings));
    }
}
