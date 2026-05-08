<?php

namespace Modules\Nonprofit\Enums;

/**
 * Status workflow for journal entries.
 *
 * draft -> posted -> reversed
 *        -> void
 */
enum JournalEntryStatus: string
{
    case Draft = 'draft';
    case Posted = 'posted';
    case Reversed = 'reversed';
    case Void = 'void';

    /**
     * Get the label for the journal entry status.
     */
    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Posted => 'Posted',
            self::Reversed => 'Reversed',
            self::Void => 'Void',
        };
    }

    /**
     * Determine if this status allows editing.
     */
    public function isEditable(): bool
    {
        return $this === self::Draft;
    }
}
