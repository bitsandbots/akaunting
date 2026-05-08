<?php

namespace Modules\Nonprofit\Exceptions;

use RuntimeException;

/**
 * Thrown when an operation targets a date that falls outside any open fiscal period,
 * or targets a closed fiscal period.
 */
class PeriodClosedException extends RuntimeException
{
    protected string $entryDate;

    protected int $companyId;

    /**
     * Constructor.
     *
     * @param string $entryDate The date that was checked (Y-m-d).
     * @param int $companyId The company whose fiscal periods were checked.
     */
    public function __construct(string $entryDate, int $companyId)
    {
        $this->entryDate = $entryDate;
        $this->companyId = $companyId;

        $message = sprintf(
            'No open fiscal period found for date %s (company %d). ' .
            'Please create or re-open a fiscal period that covers this date.',
            $entryDate,
            $companyId
        );

        parent::__construct($message);
    }

    /**
     * Get the entry date that failed the period check.
     */
    public function getEntryDate(): string
    {
        return $this->entryDate;
    }

    /**
     * Get the company ID that was checked.
     */
    public function getCompanyId(): int
    {
        return $this->companyId;
    }
}
