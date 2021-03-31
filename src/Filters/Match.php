<?php

declare(strict_types=1);

namespace Codin\DBAL\Filters;

use Doctrine\DBAL\Query\QueryBuilder;

class Match extends Filter
{
    public function validate(array $params): bool
    {
        return array_key_exists($this->queryName, $params)
            && is_string($params[$this->queryName]);
    }

    public function build(QueryBuilder $query, array $params): void
    {
        $value = $params[$this->queryName];

        $query->andWhere(sprintf('%1$s = :%1$s', $this->getColumnName()))
            ->setParameter($this->getColumnName(), $value)
        ;
    }
}
