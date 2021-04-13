<?php

declare(strict_types=1);

namespace Codin\DBAL\Filters;

use function array_key_exists;
use function count;
use function is_array;

class ContainsFilter extends AbstractFilter
{
    protected bool $notIn;

    public function __construct(
        string $parameter,
        ?string $subject = null,
        bool $notIn = false
    ) {
        parent::__construct($parameter, $subject);

        $this->notIn = $notIn;
    }

    public function isNotIn(): bool
    {
        return $this->notIn;
    }

    /**
     * @inheritDoc
     */
    public function validate(array $params): bool
    {
        return array_key_exists($this->queryName, $params)
               && is_array($params[$this->queryName])
               && count($params[$this->queryName]);
    }
}
