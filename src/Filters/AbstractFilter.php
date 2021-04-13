<?php

declare(strict_types=1);

namespace Codin\DBAL\Filters;

use Codin\DBAL\Interfaces\FilterInterface;

use function array_key_exists;

abstract class AbstractFilter implements FilterInterface
{
    protected string $queryName;

    protected ?string $fieldName;

    /**
     * @param string      $parameter Name of the parameter the subject is
     *                               referenced as in the request data.
     * @param string|null $subject   Name of the adapter-specific subject field
     *                               the filter refers to.
     */
    public function __construct(string $parameter, ?string $subject = null)
    {
        $this->queryName = $parameter;
        $this->fieldName = $subject;
    }

    /**
     * @inheritDoc
     */
    public function getParameter(): string
    {
        return $this->queryName;
    }

    /**
     * @inheritDoc
     */
    public function getSubject(): string
    {
        return $this->fieldName ?? $this->queryName;
    }

    /**
     * @inheritDoc
     */
    public function validate(array $params): bool
    {
        return array_key_exists($this->getParameter(), $params);
    }
}
