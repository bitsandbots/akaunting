<?php

namespace Modules\Nonprofit\Services;

use Illuminate\Support\Facades\DB;
use Modules\Nonprofit\Enums\JournalEntryStatus;
use Modules\Nonprofit\Exceptions\LedgerValidationException;
use Modules\Nonprofit\Models\ChartOfAccount;
use Modules\Nonprofit\Models\JournalEntry;
use Modules\Nonprofit\Models\JournalEntryLine;

/**
 * Central service for posting, reversing, and voiding journal entries.
 *
 * All double-entry operations flow through LedgerService::post().
 * The service validates header balance, per-fund balance, foreign-currency
 * balance, line mutual exclusivity, period openness, and more before
 * persisting the entry and its lines in a single database transaction.
 */
class LedgerService
{
    /**
     * Rounding tolerance for balance comparisons (half a cent).
     */
    protected const BALANCE_TOLERANCE = 0.005;

    protected EntryNumberGenerator $numberGenerator;

    protected PeriodGuard $periodGuard;

    protected DimensionResolver $dimensionResolver;

    /**
     * Constructor.
     *
     * @param EntryNumberGenerator|null $numberGenerator
     * @param PeriodGuard|null $periodGuard
     * @param DimensionResolver|null $dimensionResolver
     */
    public function __construct(
        ?EntryNumberGenerator $numberGenerator = null,
        ?PeriodGuard $periodGuard = null,
        ?DimensionResolver $dimensionResolver = null
    ) {
        $this->numberGenerator = $numberGenerator ?? new EntryNumberGenerator();
        $this->periodGuard = $periodGuard ?? new PeriodGuard();
        $this->dimensionResolver = $dimensionResolver ?? new DimensionResolver();
    }

    /**
     * Validate and post a new journal entry.
     *
     * @param array $data Entry header and lines (see class docblock for shape).
     * @param int $companyId
     *
     * @return JournalEntry The persisted entry with lines loaded.
     *
     * @throws LedgerValidationException When any validation rule fails.
     * @throws \Modules\Nonprofit\Exceptions\PeriodClosedException When no open period covers entry_date.
     */
    public function post(array $data, int $companyId): JournalEntry
    {
        // Resolve dimensions on every line before validation.
        $lines = array_map(function (array $line) use ($companyId) {
            return $this->dimensionResolver->resolve($line, $companyId);
        }, $data['lines']);

        // Run all validation rules.
        $this->validate($lines, $data['entry_date'] ?? '', $companyId);

        return DB::transaction(function () use ($data, $lines, $companyId) {
            $entry = JournalEntry::create([
                'company_id'    => $companyId,
                'entry_number'  => $this->numberGenerator->next($companyId),
                'entry_date'    => $data['entry_date'] ?? now()->format('Y-m-d'),
                'description'   => $data['description'] ?? '',
                'reference'     => $data['reference'] ?? null,
                'transaction_id' => $data['transaction_id'] ?? null,
                'status'         => JournalEntryStatus::Posted->value,
                'currency_code'  => $data['currency_code'] ?? 'USD',
                'currency_rate'  => $data['currency_rate'] ?? 1.0,
                'posted_at'      => now(),
                'posted_by'      => $data['posted_by'] ?? null,
                'created_by'     => $data['created_by'] ?? null,
            ]);

            foreach ($lines as $line) {
                $entry->lines()->create([
                    'company_id'           => $companyId,
                    'chart_of_account_id'  => $line['chart_of_account_id'],
                    'debit_amount'         => $line['debit_amount'] ?? 0.0,
                    'credit_amount'        => $line['credit_amount'] ?? 0.0,
                    'debit_foreign'        => $line['debit_foreign'] ?? $line['debit_amount'] ?? 0.0,
                    'credit_foreign'       => $line['credit_foreign'] ?? $line['credit_amount'] ?? 0.0,
                    'currency_code'        => $line['currency_code'] ?? $data['currency_code'] ?? 'USD',
                    'currency_rate'        => $line['currency_rate'] ?? $data['currency_rate'] ?? 1.0,
                    'fund_id'              => $line['fund_id'],
                    'program_id'           => $line['program_id'] ?? null,
                    'functional_class_id'  => $line['functional_class_id'],
                    'description'          => $line['description'] ?? null,
                ]);
            }

            // Reload with relationships for the caller.
            return $entry->load('lines');
        });
    }

    /**
     * Bridge: map an Akaunting Transaction to a journal entry and post it.
     *
     * Resolves COA accounts from the transaction's categories (income/expense),
     * builds balanced journal entry lines, and delegates to post().
     *
     * @param \App\Models\Banking\Transaction $transaction
     *
     * @return JournalEntry|null Returns null when the transaction has no category_id to map.
     */
    public function postFromTransaction($transaction): ?JournalEntry
    {
        $companyId = $transaction->company_id;

        // Determine the primary COA account for this transaction.
        $coaId = $this->resolveAccountMapping($transaction->category_id, $companyId);

        if (! $coaId) {
            // No mapping available — cannot auto-post.
            return null;
        }

        $isIncome = $transaction->isIncome();

        $lines = [
            [
                'chart_of_account_id' => $coaId,
                'debit_amount'        => $isIncome ? 0.0 : $transaction->amount,
                'credit_amount'       => $isIncome ? $transaction->amount : 0.0,
                'description'         => $transaction->description,
            ],
            [
                'chart_of_account_id' => $this->resolveAccountMapping(
                    // Counterparty: use a default receivable/payable or the same suspense.
                    // In a real mapping table, this would be a linked asset/liability account.
                    $this->getDefaultCounterpartyAccountId($companyId, $isIncome),
                    $companyId,
                    true // force fallback if not found
                ),
                'debit_amount'  => $isIncome ? $transaction->amount : 0.0,
                'credit_amount' => $isIncome ? 0.0 : $transaction->amount,
                'description'   => $transaction->description ? "Counterpart: {$transaction->description}" : 'Counterpart entry',
            ],
        ];

        // If dimensions are already tracked via TransactionDimension, carry them.
        if (method_exists($transaction, 'dimensions')) {
            $dims = $transaction->dimensions()->first();
            if ($dims) {
                foreach ($lines as &$line) {
                    $line['fund_id'] = $dims->fund_id;
                    $line['program_id'] = $dims->program_id;
                    $line['functional_class_id'] = $dims->functional_class_id;
                }
                unset($line);
            }
        }

        return $this->post([
            'entry_date'    => $transaction->paid_at instanceof \DateTime
                ? $transaction->paid_at->format('Y-m-d')
                : $transaction->paid_at,
            'description'   => $transaction->description ?? 'Auto-posted from transaction #' . $transaction->id,
            'reference'     => $transaction->number ?? null,
            'currency_code' => $transaction->currency_code ?? 'USD',
            'currency_rate' => $transaction->currency_rate ?? 1.0,
            'transaction_id' => $transaction->id,
            'lines'          => $lines,
        ], $companyId);
    }

    /**
     * Reverse a posted journal entry by creating a mirror entry with flipped debits/credits.
     *
     * The original entry is linked via reverses_entry_id; the new entry is linked
     * via reversed_by_entry_id.
     *
     * @param JournalEntry $entry The entry to reverse (must be posted).
     * @param string $reason Human-readable reason for the reversal.
     * @param string|null $date Reversal date (defaults to today).
     *
     * @return JournalEntry The newly created reversal entry.
     *
     * @throws \RuntimeException When the entry is not in 'posted' status.
     */
    public function reverse(JournalEntry $entry, string $reason, ?string $date = null): JournalEntry
    {
        if (! $entry->isPosted()) {
            throw new \RuntimeException(
                "Journal entry {$entry->id} must be in 'posted' status to reverse. " .
                "Current status: '{$entry->status}'."
            );
        }

        $entryDate = $date ?? now()->format('Y-m-d');

        $lines = $entry->lines->map(function (JournalEntryLine $line) {
            return [
                'chart_of_account_id'  => $line->chart_of_account_id,
                'debit_amount'         => $line->credit_amount,   // flipped
                'credit_amount'        => $line->debit_amount,    // flipped
                'debit_foreign'        => $line->credit_foreign,  // flipped
                'credit_foreign'       => $line->debit_foreign,   // flipped
                'currency_code'        => $line->currency_code,
                'currency_rate'        => $line->currency_rate,
                'fund_id'              => $line->fund_id,
                'program_id'           => $line->program_id,
                'functional_class_id'  => $line->functional_class_id,
                'description'          => $line->description
                    ? "Reversal: {$line->description}"
                    : 'Reversal entry',
            ];
        })->all();

        $reversal = $this->post([
            'entry_date'     => $entryDate,
            'description'    => "Reversal of {$entry->entry_number}: {$reason}",
            'reference'      => $entry->reference,
            'currency_code'  => $entry->currency_code,
            'currency_rate'  => $entry->currency_rate,
            'reverses_entry_id' => $entry->id,
            'lines'           => $lines,
        ], $entry->company_id);

        // Link the original entry to its reversal.
        $entry->update([
            'reversed_by_entry_id' => $reversal->id,
        ]);

        // Update original entry status.
        $entry->update([
            'status' => JournalEntryStatus::Reversed->value,
        ]);

        return $reversal;
    }

    /**
     * Void a draft journal entry.
     *
     * Only entries in 'draft' status can be voided. Posted entries must be
     * reversed, not voided.
     *
     * @param JournalEntry $entry
     *
     * @return JournalEntry
     *
     * @throws \RuntimeException When the entry is not in 'draft' status.
     */
    public function void(JournalEntry $entry): JournalEntry
    {
        if ($entry->status !== JournalEntryStatus::Draft->value) {
            throw new \RuntimeException(
                "Only draft journal entries can be voided. " .
                "Entry {$entry->id} has status '{$entry->status}'. " .
                'Use reverse() for posted entries.'
            );
        }

        $entry->update([
            'status' => JournalEntryStatus::Void->value,
        ]);

        return $entry->fresh();
    }

    /**
     * Resolve a COA account ID from an Akaunting category ID.
     *
     * Returns the suspense account (code 9999) when no explicit mapping exists.
     * In a future task, a category-to-COA mapping table will drive this lookup.
     *
     * @param int $categoryId The Akaunting category ID.
     * @param int $companyId
     * @param bool $forceSuspense Force return of suspense account (for counterparty fallback).
     *
     * @return int COA account ID.
     */
    public function resolveAccountMapping(int $categoryId, int $companyId, bool $forceSuspense = false): int
    {
        if ($forceSuspense) {
            return $this->getSuspenseAccountId($companyId);
        }

        // TODO: Replace with a real category-to-COA mapping table lookup.
        // For now, always returns suspense — the mapping table is a future deliverable.
        return $this->getSuspenseAccountId($companyId);
    }

    // ------------------------------------------------------------------
    //  Validation
    // ------------------------------------------------------------------

    /**
     * Run all validation rules against the entry lines.
     *
     * @param array $lines Resolved/normalized line arrays.
     * @param string $entryDate Y-m-d.
     * @param int $companyId
     *
     * @return void
     *
     * @throws LedgerValidationException When any rule fails.
     * @throws \Modules\Nonprofit\Exceptions\PeriodClosedException When no open period.
     */
    protected function validate(array $lines, string $entryDate, int $companyId): void
    {
        $errors = [];

        // Rule 1: At least 2 lines.
        if (count($lines) < 2) {
            $errors['min_lines'] = 'A journal entry must contain at least 2 lines.';
        }

        // Rule 2: Mutually exclusive sides per line.
        foreach ($lines as $i => $line) {
            $debit = (float) ($line['debit_amount'] ?? 0);
            $credit = (float) ($line['credit_amount'] ?? 0);

            if ($debit > 0 && $credit > 0) {
                $errors["mutual_exclusivity_{$i}"] =
                    "Line {$i}: debit_amount ({$debit}) and credit_amount ({$credit}) " .
                    'cannot both be greater than zero.';
            }
        }

        // Rule 3: Header balance — total debits must equal total credits.
        $totalDebit = array_sum(array_map(fn($l) => (float) ($l['debit_amount'] ?? 0), $lines));
        $totalCredit = array_sum(array_map(fn($l) => (float) ($l['credit_amount'] ?? 0), $lines));

        if (abs($totalDebit - $totalCredit) > static::BALANCE_TOLERANCE) {
            $errors['header_balance'] = sprintf(
                'Journal entry is not balanced: total debits = %.4f, total credits = %.4f (difference = %.4f).',
                $totalDebit,
                $totalCredit,
                abs($totalDebit - $totalCredit)
            );
        }

        // Rule 4: Per-fund balance.
        $byFund = [];
        foreach ($lines as $line) {
            $fid = $line['fund_id'];
            $byFund[$fid]['debit'] = ($byFund[$fid]['debit'] ?? 0) + (float) ($line['debit_amount'] ?? 0);
            $byFund[$fid]['credit'] = ($byFund[$fid]['credit'] ?? 0) + (float) ($line['credit_amount'] ?? 0);
        }

        foreach ($byFund as $fundId => $totals) {
            if (abs($totals['debit'] - $totals['credit']) > static::BALANCE_TOLERANCE) {
                $errors["fund_balance_{$fundId}"] = sprintf(
                    'Fund %s is not balanced: debits = %.4f, credits = %.4f.',
                    $fundId,
                    $totals['debit'],
                    $totals['credit']
                );
            }
        }

        // Rule 5: Foreign-currency balance.
        $byCurrency = [];
        foreach ($lines as $line) {
            $ccy = $line['currency_code'] ?? 'USD';
            $byCurrency[$ccy]['debit'] = ($byCurrency[$ccy]['debit'] ?? 0) + (float) ($line['debit_foreign'] ?? 0);
            $byCurrency[$ccy]['credit'] = ($byCurrency[$ccy]['credit'] ?? 0) + (float) ($line['credit_foreign'] ?? 0);
        }

        foreach ($byCurrency as $ccy => $totals) {
            if (abs($totals['debit'] - $totals['credit']) > static::BALANCE_TOLERANCE) {
                $errors["foreign_balance_{$ccy}"] = sprintf(
                    'Foreign-currency (%s) is not balanced: debits = %.4f, credits = %.4f.',
                    $ccy,
                    $totals['debit'],
                    $totals['credit']
                );
            }
        }

        if (! empty($errors)) {
            throw new LedgerValidationException(
                'Journal entry validation failed. See errors for details.',
                $errors
            );
        }

        // Rule 6: Period must be open (delegated to PeriodGuard).
        $this->periodGuard->assertOpen($companyId, $entryDate);
    }

    // ------------------------------------------------------------------
    //  Internal helpers
    // ------------------------------------------------------------------

    /**
     * Get the suspense account ID for the given company.
     *
     * @param int $companyId
     *
     * @return int
     *
     * @throws \RuntimeException When the suspense account has not been seeded.
     */
    protected function getSuspenseAccountId(int $companyId): int
    {
        $suspense = ChartOfAccount::where('company_id', $companyId)
            ->where('code', '9999')
            ->first();

        if (! $suspense) {
            throw new \RuntimeException(
                "Suspense account (code 9999) not found for company {$companyId}. " .
                'Seed the SuspenseAccountSeeder first.'
            );
        }

        return $suspense->id;
    }

    /**
     * Get a default counterparty account ID for transaction bridging.
     *
     * For income: attempts to find an accounts-receivable asset account.
     * For expense: attempts to find an accounts-payable liability account.
     * Falls back to the suspense account if no suitable account exists.
     *
     * @param int $companyId
     * @param bool $isIncome
     *
     * @return int
     */
    protected function getDefaultCounterpartyAccountId(int $companyId, bool $isIncome): int
    {
        if ($isIncome) {
            $acct = ChartOfAccount::where('company_id', $companyId)
                ->where('type', 'asset')
                ->where('name', 'like', '%receivable%')
                ->where('enabled', true)
                ->first();
        } else {
            $acct = ChartOfAccount::where('company_id', $companyId)
                ->where('type', 'liability')
                ->where('name', 'like', '%payable%')
                ->where('enabled', true)
                ->first();
        }

        return $acct ? $acct->id : $this->getSuspenseAccountId($companyId);
    }
}
