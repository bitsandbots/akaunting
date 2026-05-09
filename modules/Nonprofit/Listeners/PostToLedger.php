<?php

namespace Modules\Nonprofit\Listeners;

use App\Events\Banking\TransactionCreated;
use App\Events\Banking\TransactionDeleted;
use App\Events\Banking\TransactionUpdated;
use Modules\Nonprofit\Enums\JournalEntryStatus;
use Modules\Nonprofit\Models\JournalEntry;
use Modules\Nonprofit\Services\LedgerService;

/**
 * Bridge listener: mirrors Akaunting Banking Transactions into the
 * double-entry Nonprofit ledger.
 *
 * On TransactionCreated       -> Auto-post via LedgerService.
 * On TransactionUpdated       -> Recalculate entry lines or reverse + re-post.
 * On TransactionDeleted       -> Soft-delete draft entries; reverse posted entries.
 */
class PostToLedger
{
    protected LedgerService $ledger;

    public function __construct()
    {
        $this->ledger = app(LedgerService::class);
    }

    // ------------------------------------------------------------------
    //  TransactionCreated
    // ------------------------------------------------------------------

    /**
     * Handle transaction creation.
     *
     * @param  TransactionCreated  $event
     * @return void
     */
    public function onTransactionCreated(TransactionCreated $event): void
    {
        $transaction = $event->transaction;

        $this->ledger->postFromTransaction($transaction);
    }

    // ------------------------------------------------------------------
    //  TransactionUpdated
    // ------------------------------------------------------------------

    /**
     * Handle transaction update.
     *
     * If the linked journal entry is still a draft, simply regenerate
     * its lines to match the updated transaction data.
     *
     * If the linked entry is already posted, reverse the old entry and
     * create a new posted entry reflecting the updated transaction.
     *
     * @param  TransactionUpdated  $event
     * @return void
     */
    public function onTransactionUpdated(TransactionUpdated $event): void
    {
        $transaction = $event->transaction;

        $existingEntry = JournalEntry::where('transaction_id', $transaction->id)
            ->where('status', '!=', JournalEntryStatus::Void->value)
            ->first();

        if (! $existingEntry) {
            // No prior entry — create a new one.
            $this->ledger->postFromTransaction($transaction);
            return;
        }

        if ($existingEntry->isDraft()) {
            // Regenerate lines for the draft to match the updated transaction.
            $existingEntry->lines()->delete();

            $this->rebuildLinesFromTransaction($existingEntry, $transaction);
            return;
        }

        if ($existingEntry->isPosted()) {
            // Reverse old entry, then create a new posted entry.
            try {
                $this->ledger->reverse($existingEntry, 'Transaction #' . $transaction->id . ' updated');
            } catch (\RuntimeException $e) {
                // Log but continue — the entry may have already been reversed.
                logger()->warning('PostToLedger: Failed to reverse entry during update.', [
                    'journal_entry_id' => $existingEntry->id,
                    'error'            => $e->getMessage(),
                ]);
            }

            $this->ledger->postFromTransaction($transaction);
        }
    }

    // ------------------------------------------------------------------
    //  TransactionDeleted
    // ------------------------------------------------------------------

    /**
     * Handle transaction deletion.
     *
     * Draft entries linked to the deleted transaction are soft-deleted.
     * Posted entries are reversed (not deleted).
     *
     * @param  TransactionDeleted  $event
     * @return void
     */
    public function onTransactionDeleted(TransactionDeleted $event): void
    {
        $transaction = $event->transaction;

        $existingEntry = JournalEntry::where('transaction_id', $transaction->id)
            ->where('status', '!=', JournalEntryStatus::Void->value)
            ->first();

        if (! $existingEntry) {
            return;
        }

        if ($existingEntry->isDraft()) {
            $existingEntry->lines()->delete();
            $existingEntry->delete();
            return;
        }

        if ($existingEntry->isPosted()) {
            try {
                $this->ledger->reverse(
                    $existingEntry,
                    'Transaction #' . $transaction->id . ' deleted'
                );
            } catch (\RuntimeException $e) {
                logger()->warning('PostToLedger: Failed to reverse entry during deletion.', [
                    'journal_entry_id' => $existingEntry->id,
                    'error'            => $e->getMessage(),
                ]);
            }
        }
    }

    // ------------------------------------------------------------------
    //  Event Subscriber Registration
    // ------------------------------------------------------------------

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     * @return array<string, string>
     */
    public function subscribe($events): array
    {
        return [
            TransactionCreated::class => 'onTransactionCreated',
            TransactionUpdated::class => 'onTransactionUpdated',
            TransactionDeleted::class => 'onTransactionDeleted',
        ];
    }

    // ------------------------------------------------------------------
    //  Helpers
    // ------------------------------------------------------------------

    /**
     * Rebuild journal entry lines from the updated transaction data
     * without posting (keeps the entry in draft status).
     *
     * @param  JournalEntry  $entry
     * @param  \App\Models\Banking\Transaction  $transaction
     * @return void
     */
    protected function rebuildLinesFromTransaction(JournalEntry $entry, $transaction): void
    {
        $companyId = $transaction->company_id;
        $coaId = $this->ledger->resolveAccountMapping($transaction->category_id, $companyId);
        $isIncome = $transaction->isIncome();

        $counterpartyId = $this->ledger->resolveAccountMapping(
            $transaction->category_id,
            $companyId,
            true
        );

        $lines = [
            [
                'company_id'          => $companyId,
                'chart_of_account_id' => $coaId,
                'debit_amount'        => $isIncome ? 0.0 : $transaction->amount,
                'credit_amount'       => $isIncome ? $transaction->amount : 0.0,
                'debit_foreign'       => $isIncome ? 0.0 : $transaction->amount,
                'credit_foreign'      => $isIncome ? $transaction->amount : 0.0,
                'currency_code'       => $transaction->currency_code ?? 'USD',
                'currency_rate'       => $transaction->currency_rate ?? 1.0,
                'description'         => $transaction->description,
            ],
            [
                'company_id'          => $companyId,
                'chart_of_account_id' => $counterpartyId,
                'debit_amount'        => $isIncome ? $transaction->amount : 0.0,
                'credit_amount'       => $isIncome ? 0.0 : $transaction->amount,
                'debit_foreign'       => $isIncome ? $transaction->amount : 0.0,
                'credit_foreign'      => $isIncome ? 0.0 : $transaction->amount,
                'currency_code'       => $transaction->currency_code ?? 'USD',
                'currency_rate'       => $transaction->currency_rate ?? 1.0,
                'description'         => $transaction->description
                    ? "Counterpart: {$transaction->description}"
                    : 'Counterpart entry',
            ],
        ];

        foreach ($lines as $line) {
            $entry->lines()->create($line);
        }

        // Update entry metadata to match the transaction.
        $entry->update([
            'entry_date'    => $transaction->paid_at instanceof \DateTime
                ? $transaction->paid_at->format('Y-m-d')
                : ($transaction->paid_at ?? now()->format('Y-m-d')),
            'description'   => $transaction->description ?? 'Auto-posted from transaction #' . $transaction->id,
            'reference'     => $transaction->number ?? null,
            'currency_code' => $transaction->currency_code ?? 'USD',
            'currency_rate' => $transaction->currency_rate ?? 1.0,
        ]);
    }
}
