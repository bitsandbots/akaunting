<?php

namespace Modules\Nonprofit\Enums;

/**
 * Status workflow for fiscal periods.
 */
enum FiscalPeriodStatus: string
{
    case Open = 'open';
    case Closed = 'closed';

    /**
     * Get the label for the fiscal period status.
     */
    public function label(): string
    {
        return match ($this) {
            self::Open => 'Open',
            self::Closed => 'Closed',
        };
    }

    /**
     * Determine if this status allows editing.
     */
    public function isEditable(): bool
    {
        return $this === self::Open;
    }
}
