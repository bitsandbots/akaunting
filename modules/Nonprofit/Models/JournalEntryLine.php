<?php

namespace Modules\Nonprofit\Models;

use App\Abstracts\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalEntryLine extends Model
{
    use HasFactory;

    protected $table = 'journal_entry_lines';

    protected $fillable = [
        'company_id',
        'journal_entry_id',
        'chart_of_account_id',
        'debit_amount',
        'credit_amount',
        'debit_foreign',
        'credit_foreign',
        'currency_code',
        'currency_rate',
        'fund_id',
        'program_id',
        'functional_class_id',
        'description',
    ];

    protected $casts = [
        'debit_amount'      => 'double',
        'credit_amount'     => 'double',
        'debit_foreign'     => 'double',
        'credit_foreign'    => 'double',
        'currency_rate'     => 'double',
    ];

    /**
     * Sortable columns.
     */
    protected $sortable = [
        'description',
    ];

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }

    public function chartOfAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'chart_of_account_id');
    }

    public function fund(): BelongsTo
    {
        return $this->belongsTo(Fund::class, 'fund_id');
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class, 'program_id');
    }

    public function functionalClass(): BelongsTo
    {
        return $this->belongsTo(FunctionalClass::class, 'functional_class_id');
    }

    /**
     * Get the net amount (debit - credit) in base currency.
     */
    public function getNetAmountAttribute(): float
    {
        return $this->debit_amount - $this->credit_amount;
    }

    /**
     * Get the net amount in foreign currency.
     */
    public function getNetForeignAttribute(): float
    {
        return $this->debit_foreign - $this->credit_foreign;
    }
}
