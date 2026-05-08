<?php

namespace Modules\Nonprofit\Exceptions;

use RuntimeException;

/**
 * Thrown when journal entry data fails validation before posting.
 *
 * Contains a list of validation errors keyed by rule name.
 */
class LedgerValidationException extends RuntimeException
{
    /**
     * Validation errors keyed by rule name.
     */
    protected array $errors;

    /**
     * Constructor.
     *
     * @param string $message Human-readable summary message.
     * @param array $errors Detailed errors keyed by rule name.
     */
    public function __construct(string $message = '', array $errors = [])
    {
        parent::__construct($message);
        $this->errors = $errors;
    }

    /**
     * Get all validation errors.
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
