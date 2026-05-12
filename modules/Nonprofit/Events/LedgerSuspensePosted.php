<?php

namespace Modules\Nonprofit\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when LedgerService::resolveAccountMapping() falls through to the
 * suspense account because the transaction's category has no configured COA
 * mapping. The dashboard widget consumes this to surface suspense balance,
 * and admins clear it via the "Reclassify Suspense" job.
 *
 * Spec reference: design 2026-05-08, lines 348–352.
 */
class LedgerSuspensePosted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly int $companyId,
        public readonly int $categoryId,
        public readonly int $suspenseAccountId,
        public readonly ?int $transactionId = null,
    ) {
    }
}
