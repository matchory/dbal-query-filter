<?php

declare(strict_types=1);

namespace Codin\DBAL\Filters;

use function array_intersect_key;
use function count;

class RangeFilter extends AbstractFilter
{
    public function validate(array $params): bool
    {
        $intersect = array_intersect_key(
            $params,
            $this->getVariants()
        );

        return count($intersect) > 0;
    }

    public function getVariants(): array
    {
        return [
            $this->queryName . '_gt' => '>',
            $this->queryName . '_gte' => '>=',
            $this->queryName . '_lt' => '<',
            $this->queryName . '_lte' => '<=',
        ];
    }
}
