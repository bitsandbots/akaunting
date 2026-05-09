<?php

namespace Modules\Nonprofit\Http\Controllers;

use App\Abstracts\Http\Controller;
use Modules\Nonprofit\Enums\JournalEntryStatus;
use Modules\Nonprofit\Http\Requests\JournalEntryRequest;
use Modules\Nonprofit\Models\ChartOfAccount;
use Modules\Nonprofit\Models\FunctionalClass;
use Modules\Nonprofit\Models\Fund;
use Modules\Nonprofit\Models\JournalEntry;
use Modules\Nonprofit\Models\Program;
use Modules\Nonprofit\Services\LedgerService;

class JournalEntries extends Controller
{
    protected LedgerService $ledgerService;

    public function __construct(LedgerService $ledgerService)
    {
        parent::__construct();

        $this->ledgerService = $ledgerService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $entries = JournalEntry::with(['lines.account', 'lines.fund', 'lines.program', 'lines.functionalClass'])
            ->where('company_id', company_id())
            ->collect(['entry_date' => 'desc', 'entry_number' => 'desc']);

        return $this->response('nonprofit::journal_entries.index', compact('entries'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $accounts = ChartOfAccount::where('company_id', company_id())
            ->enabled()
            ->orderBy('code')
            ->get();

        $funds = Fund::where('company_id', company_id())
            ->enabled()
            ->orderBy('code')
            ->get();

        $programs = Program::where('company_id', company_id())
            ->enabled()
            ->orderBy('code')
            ->get();

        $functionalClasses = FunctionalClass::where('company_id', company_id())
            ->enabled()
            ->orderBy('code')
            ->get();

        $currencies = $this->getCurrencies();

        return view('nonprofit::journal_entries.create', compact(
            'accounts', 'funds', 'programs', 'functionalClasses', 'currencies'
        ));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  JournalEntryRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(JournalEntryRequest $request)
    {
        $action = $request->input('action', 'draft');

        if ($action === 'post') {
            return $this->storeAndPost($request);
        }

        // Save as draft
        $entry = JournalEntry::create([
            'company_id'     => company_id(),
            'entry_number'   => $this->generateDraftNumber(),
            'entry_date'     => $request->entry_date,
            'description'    => $request->description,
            'reference'      => $request->reference,
            'status'         => JournalEntryStatus::Draft->value,
            'currency_code'  => $request->currency_code ?? 'USD',
            'currency_rate'  => $request->currency_rate ?? 1.0,
            'created_by'     => user_id(),
        ]);

        $this->saveLines($entry, $request->lines);

        $message = trans('nonprofit::general.created', ['type' => trans_choice('nonprofit::general.journal_entry', 1)]);

        flash($message)->success();

        return redirect()->route('nonprofit.journal-entries.show', $entry->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  JournalEntry  $journalEntry
     * @return \Illuminate\Http\Response
     */
    public function show(JournalEntry $journalEntry)
    {
        $journalEntry->load([
            'lines.chartOfAccount',
            'lines.fund',
            'lines.program',
            'lines.functionalClass',
            'transaction',
            'reversedBy',
            'reverses',
        ]);

        return view('nonprofit::journal_entries.show', compact('journalEntry'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  JournalEntry  $journalEntry
     * @return \Illuminate\Http\Response
     */
    public function edit(JournalEntry $journalEntry)
    {
        if (! $journalEntry->isDraft()) {
            flash(trans('nonprofit::general.cannot_delete_posted_journal_entry'))->error();
            return redirect()->route('nonprofit.journal-entries.show', $journalEntry->id);
        }

        $journalEntry->load('lines');

        $accounts = ChartOfAccount::where('company_id', company_id())
            ->enabled()
            ->orderBy('code')
            ->get();

        $funds = Fund::where('company_id', company_id())
            ->enabled()
            ->orderBy('code')
            ->get();

        $programs = Program::where('company_id', company_id())
            ->enabled()
            ->orderBy('code')
            ->get();

        $functionalClasses = FunctionalClass::where('company_id', company_id())
            ->enabled()
            ->orderBy('code')
            ->get();

        $currencies = $this->getCurrencies();

        return view('nonprofit::journal_entries.edit', compact(
            'journalEntry', 'accounts', 'funds', 'programs', 'functionalClasses', 'currencies'
        ));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  JournalEntryRequest  $request
     * @param  JournalEntry  $journalEntry
     * @return \Illuminate\Http\Response
     */
    public function update(JournalEntryRequest $request, JournalEntry $journalEntry)
    {
        if (! $journalEntry->isDraft()) {
            flash(trans('nonprofit::general.cannot_delete_posted_journal_entry'))->error();
            return redirect()->route('nonprofit.journal-entries.show', $journalEntry->id);
        }

        $action = $request->input('action', 'draft');

        $journalEntry->update([
            'entry_date'    => $request->entry_date,
            'description'   => $request->description,
            'reference'     => $request->reference,
            'currency_code' => $request->currency_code ?? 'USD',
            'currency_rate' => $request->currency_rate ?? 1.0,
            'updated_by'    => user_id(),
        ]);

        // Replace lines
        $journalEntry->lines()->delete();
        $this->saveLines($journalEntry, $request->lines);

        if ($action === 'post') {
            return $this->postEntry($journalEntry);
        }

        $message = trans('nonprofit::general.updated', ['type' => trans_choice('nonprofit::general.journal_entry', 1)]);

        flash($message)->success();

        return redirect()->route('nonprofit.journal-entries.show', $journalEntry->id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  JournalEntry  $journalEntry
     * @return \Illuminate\Http\Response
     */
    public function destroy(JournalEntry $journalEntry)
    {
        if (! $journalEntry->isDraft()) {
            flash(trans('nonprofit::general.cannot_delete_posted_journal_entry'))->error();
            return redirect()->route('nonprofit.journal-entries.index');
        }

        $journalEntry->delete();

        $message = trans('nonprofit::general.deleted', ['type' => trans_choice('nonprofit::general.journal_entry', 1)]);

        flash($message)->success();

        return redirect()->route('nonprofit.journal-entries.index');
    }

    /**
     * Post a draft journal entry.
     *
     * @param  JournalEntry  $journalEntry
     * @return \Illuminate\Http\Response
     */
    public function post(JournalEntry $journalEntry)
    {
        return $this->postEntry($journalEntry);
    }

    /**
     * Reverse a posted journal entry.
     *
     * @param  JournalEntry  $journalEntry
     * @return \Illuminate\Http\Response
     */
    public function reverse(JournalEntry $journalEntry)
    {
        $reason = request()->input('reason', 'Manual reversal');

        try {
            $this->ledgerService->reverse($journalEntry, $reason);

            flash(trans('nonprofit::general.reversed_success'))->success();
        } catch (\RuntimeException $e) {
            flash($e->getMessage())->error();
        }

        return redirect()->route('nonprofit.journal-entries.show', $journalEntry->id);
    }

    /**
     * Void a draft journal entry.
     *
     * @param  JournalEntry  $journalEntry
     * @return \Illuminate\Http\Response
     */
    public function void(JournalEntry $journalEntry)
    {
        try {
            $this->ledgerService->void($journalEntry);

            flash(trans('nonprofit::general.voided_success'))->success();
        } catch (\RuntimeException $e) {
            flash($e->getMessage())->error();
        }

        return redirect()->route('nonprofit.journal-entries.show', $journalEntry->id);
    }

    // ------------------------------------------------------------------
    //  Internal helpers
    // ------------------------------------------------------------------

    /**
     * Save and post a new entry through LedgerService.
     */
    protected function storeAndPost(JournalEntryRequest $request): \Illuminate\Http\RedirectResponse
    {
        $lines = array_map(function ($line) {
            return [
                'chart_of_account_id'  => $line['chart_of_account_id'],
                'debit_amount'         => $line['debit_amount'] ?? 0,
                'credit_amount'        => $line['credit_amount'] ?? 0,
                'fund_id'              => $line['fund_id'] ?? null,
                'program_id'           => $line['program_id'] ?? null,
                'functional_class_id'  => $line['functional_class_id'] ?? null,
                'description'          => $line['description'] ?? null,
            ];
        }, $request->lines);

        try {
            $entry = $this->ledgerService->post([
                'entry_date'    => $request->entry_date,
                'description'   => $request->description,
                'reference'     => $request->reference,
                'currency_code' => $request->currency_code ?? 'USD',
                'currency_rate' => $request->currency_rate ?? 1.0,
                'posted_by'     => user_id(),
                'created_by'    => user_id(),
                'lines'         => $lines,
            ], company_id());

            flash(trans('nonprofit::general.posted_success'))->success();

            return redirect()->route('nonprofit.journal-entries.show', $entry->id);
        } catch (\Modules\Nonprofit\Exceptions\LedgerValidationException $e) {
            flash($e->getMessage())->error();

            foreach ($e->getErrors() as $error) {
                flash($error)->error();
            }

            return redirect()->back()->withInput();
        } catch (\Modules\Nonprofit\Exceptions\PeriodClosedException $e) {
            flash(trans('nonprofit::general.no_open_period'))->error();

            return redirect()->back()->withInput();
        }
    }

    /**
     * Post an existing draft entry.
     */
    protected function postEntry(JournalEntry $journalEntry): \Illuminate\Http\RedirectResponse
    {
        try {
            // Rebuild lines array for the ledger service
            $lines = $journalEntry->lines->map(function ($line) {
                return [
                    'chart_of_account_id'  => $line->chart_of_account_id,
                    'debit_amount'         => $line->debit_amount,
                    'credit_amount'        => $line->credit_amount,
                    'fund_id'              => $line->fund_id,
                    'program_id'           => $line->program_id,
                    'functional_class_id'  => $line->functional_class_id,
                    'description'          => $line->description,
                ];
            })->all();

            $newEntry = $this->ledgerService->post([
                'entry_date'    => $journalEntry->entry_date->format('Y-m-d'),
                'description'   => $journalEntry->description,
                'reference'     => $journalEntry->reference,
                'currency_code' => $journalEntry->currency_code,
                'currency_rate' => $journalEntry->currency_rate,
                'posted_by'     => user_id(),
                'created_by'    => $journalEntry->created_by,
                'lines'         => $lines,
            ], company_id());

            // Soft-delete the old draft and update relationships
            $journalEntry->lines()->delete();
            $journalEntry->delete();

            flash(trans('nonprofit::general.posted_success'))->success();

            return redirect()->route('nonprofit.journal-entries.show', $newEntry->id);
        } catch (\Modules\Nonprofit\Exceptions\LedgerValidationException $e) {
            flash($e->getMessage())->error();

            foreach ($e->getErrors() as $error) {
                flash($error)->error();
            }

            return redirect()->route('nonprofit.journal-entries.show', $journalEntry->id);
        } catch (\Modules\Nonprofit\Exceptions\PeriodClosedException $e) {
            flash(trans('nonprofit::general.no_open_period'))->error();

            return redirect()->route('nonprofit.journal-entries.show', $journalEntry->id);
        }
    }

    /**
     * Save lines for a draft entry.
     */
    protected function saveLines(JournalEntry $entry, array $lines): void
    {
        foreach ($lines as $line) {
            $entry->lines()->create([
                'company_id'          => company_id(),
                'chart_of_account_id' => $line['chart_of_account_id'],
                'debit_amount'        => $line['debit_amount'] ?? 0,
                'credit_amount'       => $line['credit_amount'] ?? 0,
                'debit_foreign'       => $line['debit_foreign'] ?? $line['debit_amount'] ?? 0,
                'credit_foreign'      => $line['credit_foreign'] ?? $line['credit_amount'] ?? 0,
                'currency_code'       => $line['currency_code'] ?? $entry->currency_code,
                'currency_rate'       => $line['currency_rate'] ?? $entry->currency_rate,
                'fund_id'             => $line['fund_id'] ?? null,
                'program_id'          => $line['program_id'] ?? null,
                'functional_class_id' => $line['functional_class_id'] ?? null,
                'description'         => $line['description'] ?? null,
            ]);
        }
    }

    /**
     * Generate a temporary draft number.
     */
    protected function generateDraftNumber(): string
    {
        $count = JournalEntry::where('company_id', company_id())
            ->where('status', JournalEntryStatus::Draft->value)
            ->withTrashed()
            ->count();

        return 'DRAFT-' . str_pad((string) ($count + 1), 5, '0', STR_PAD_LEFT);
    }

    /**
     * Get available currencies for the form.
     */
    protected function getCurrencies(): array
    {
        return \App\Models\Setting\Currency::enabled()
            ->orderBy('name')
            ->pluck('name', 'code')
            ->all();
    }
}
