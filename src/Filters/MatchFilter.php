<?php

declare(strict_types=1);

namespace Codin\DBAL\Filters;

use function array_key_exists;
use function is_string;

class MatchFilter extends AbstractFilter
{
    public function validate(array $params): bool
    {
        return array_key_exists($this->queryName, $params)
               && is_string($params[$this->queryName]);
    }
}
