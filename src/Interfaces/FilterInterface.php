<?php

declare(strict_types=1);

namespace Codin\DBAL\Interfaces;

interface FilterInterface
{
    /**
     * Validates whether the filter applies to the request.
     *
     * @param array<string, mixed> $params
     *
     * @return bool
     */
    public function validate(array $params): bool;

    /**
     * Retrieves the adapter-specific subject field the filter refers to.
     *
     * @return string
     */
    public function getSubject(): string;

    /**
     * Retrieves the name of the parameter the subject is referenced as in the
     * request data.
     *
     * @return string
     */
    public function getParameter(): string;
}
