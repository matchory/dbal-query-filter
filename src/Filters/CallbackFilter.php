<?php

declare(strict_types=1);

namespace Codin\DBAL\Filters;

use Closure;

class CallbackFilter extends AbstractFilter
{
    protected Closure $callback;

    public function __construct(string $parameter, callable $callback)
    {
        parent::__construct($parameter);

        $this->callback = Closure::fromCallable($callback);
    }

    public function getCallback(): Closure
    {
        return $this->callback;
    }
}
