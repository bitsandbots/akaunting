<?php

namespace Modules\Nonprofit\Enums;

/**
 * Functional class parent categories — rolls up to IRS Form 990 columns.
 *
 * Per FASB ASC 958-720-45, expenses must be reported by functional classification:
 * - Program services (mission-related activities)
 * - Management and general (administration)
 * - Fundraising
 */
enum FunctionalClassParentClass: string
{
    case ProgramServices = 'program_services';
    case ManagementGeneral = 'management_general';
    case Fundraising = 'fundraising';

    /**
     * Get the label for the parent class.
     */
    public function label(): string
    {
        return match ($this) {
            self::ProgramServices => 'Program Services',
            self::ManagementGeneral => 'Management & General',
            self::Fundraising => 'Fundraising',
        };
    }

    /**
     * Get the Form 990 Part IX line reference for this class.
     */
    public function form990Reference(): string
    {
        return match ($this) {
            self::ProgramServices => 'Part IX, Line 25 (Column B)',
            self::ManagementGeneral => 'Part IX, Line 25 (Column C)',
            self::Fundraising => 'Part IX, Line 25 (Column D)',
        };
    }
}
