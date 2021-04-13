<?php

declare(strict_types=1);

namespace Codin\DBAL\Filters;

use function array_key_exists;

class NullableFilter extends AbstractFilter
{
    protected bool $notNull;

    public function __construct(
        string $parameter,
        ?string $subject = null,
        bool $notNull = false
    ) {
        parent::__construct($parameter, $subject);

        $this->notNull = $notNull;
    }

    public function validate(array $params): bool
    {
        return (
            array_key_exists($this->queryName, $params) &&
            $params[$this->queryName]
        );
    }

    public function isNotNull(): bool
    {
        return $this->notNull;
    }
}
