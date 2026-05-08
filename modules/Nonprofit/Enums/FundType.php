<?php

namespace Modules\Nonprofit\Enums;

/**
 * Fund types per FASB ASU 2016-14 (ASU 958-205).
 *
 * Nonprofit entities must classify net assets into two classes:
 * - Net assets WITHOUT donor restrictions
 * - Net assets WITH donor restrictions
 */
enum FundType: string
{
    case WithoutDonorRestrictions = 'without_donor_restrictions';
    case WithDonorRestrictions = 'with_donor_restrictions';

    /**
     * Get the label for the fund type.
     */
    public function label(): string
    {
        return match ($this) {
            self::WithoutDonorRestrictions => 'Without Donor Restrictions',
            self::WithDonorRestrictions => 'With Donor Restrictions',
        };
    }
}
