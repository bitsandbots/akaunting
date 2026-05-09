<?php

namespace Modules\Nonprofit\Exceptions;

class DimensionValidationException extends \DomainException
{
    public function __construct(string $message, public readonly string $rule = '')
    {
        parent::__construct($message);
    }
}
