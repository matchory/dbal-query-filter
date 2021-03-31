<?php

declare(strict_types=1);

namespace Codin\DBAL\Filters;

use Doctrine\DBAL\Query\QueryBuilder;

abstract class Filter
{
    protected string $queryName;

    protected ?string $columnName = null;

    public function __construct(string $queryName, ?string $columnName = null)
    {
        $this->queryName = $queryName;
        $this->columnName = $columnName;
    }

    public function getQueryParam(): string
    {
        return $this->queryName;
    }

    public function getColumnName(): string
    {
        return null === $this->columnName ? $this->queryName : $this->columnName;
    }

    public function validate(array $params): bool
    {
        return array_key_exists($this->queryName, $params);
    }

    public function bindParams(QueryBuilder $query, string $name, array $values): array
    {
        $params = [];
        foreach (array_values($values) as $index => $value) {
            $params[] = ':' . $name . $index;
            $query->setParameter($name . $index, $value);
        }
        return $params;
    }

    abstract public function build(QueryBuilder $query, array $params): void;
}
