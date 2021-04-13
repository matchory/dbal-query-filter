<?php

declare(strict_types=1);

namespace Codin\DBAL\Interfaces;

interface AdapterInterface
{
    public function apply(FilterInterface $filter, array $params): void;
}
