<?php

declare(strict_types=1);

namespace Codin\DBAL\Adapters;

use Codin\DBAL\Exceptions\UnsupportedFilterException;
use Codin\DBAL\Interfaces\AdapterInterface;
use Codin\DBAL\Interfaces\FilterInterface;
use ErrorException;

use function class_basename;
use function method_exists;
use function str_replace;

abstract class AbstractAdapter implements AdapterInterface
{
    /**
     * @param FilterInterface $filter
     * @param array           $params
     *
     * @throws ErrorException
     */
    public function apply(FilterInterface $filter, array $params): void
    {
        $filterClass = class_basename($filter);
        $method = 'apply' . str_replace(
                'Filter',
                '',
                $filterClass
            );

        if ( ! method_exists($this, $method)) {
            throw new UnsupportedFilterException($filter);
        }

        $this->$method($filter, $params);
    }

}
