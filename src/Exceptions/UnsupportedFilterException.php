<?php

declare(strict_types=1);

namespace Codin\DBAL\Exceptions;

use Codin\DBAL\Interfaces\FilterInterface;
use ErrorException;

use function get_class;

class UnsupportedFilterException extends ErrorException
{
    protected FilterInterface $filter;

    public function __construct(FilterInterface $filter)
    {
        $class = get_class($filter);

        parent::__construct("Adapter does not support filter $class");

        $this->filter = $filter;
    }

    public function getFilter(): FilterInterface
    {
        return $this->filter;
    }
}
