<?php

declare(strict_types=1);

namespace Codin\DBAL\Filters;

use Doctrine\DBAL\Query\QueryBuilder;

class Range extends Filter
{
    protected function getVariants(): array
    {
        return [
            $this->queryName . '_gt' => '>',
            $this->queryName . '_gte' => '>=',
            $this->queryName . '_lt' => '<',
            $this->queryName . '_lte' => '<=',
        ];
    }

    public function validate(array $params): bool
    {
        $intersect = array_intersect_key($params, $this->getVariants());
        return count($intersect) > 0;
    }

    public function build(QueryBuilder $query, array $params): void
    {
        foreach (array_intersect_key($this->getVariants(), $params) as $name => $operator) {
            $value = $params[$name];
            $query->andWhere(sprintf('%s %s :%s', $this->getColumnName(), $operator, $name))
                ->setParameter($name, $value)
            ;
        }
    }
}
