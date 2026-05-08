<?php

namespace Modules\Nonprofit\Models;

use App\Abstracts\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JournalEntry extends Model
{
    use HasFactory;

    protected $table = 'journal_entries';

    protected $fillable = [
        'company_id',
        'entry_number',
        'entry_date',
        'description',
        'reference',
        'transaction_id',
        'status',
        'reversed_by_entry_id',
        'reverses_entry_id',
        'currency_code',
        'currency_rate',
        'posted_at',
        'posted_by',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'entry_date'        => 'date',
        'currency_rate'     => 'double',
        'posted_at'         => 'datetime',
        'deleted_at'        => 'datetime',
    ];

    /**
     * Sortable columns.
     */
    protected $sortable = [
        'entry_number',
        'entry_date',
        'status',
        'description',
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::deleting(function (self $entry) {
            if ($entry->status !== 'draft') {
                throw new \RuntimeException(trans('nonprofit::general.cannot_delete_posted_journal_entry'));
            }
        });
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo('App\Models\Banking\Transaction', 'transaction_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class, 'journal_entry_id');
    }

    public function reversedBy(): BelongsTo
    {
        return $this->belongsTo(static::class, 'reversed_by_entry_id');
    }

    public function reverses(): BelongsTo
    {
        return $this->belongsTo(static::class, 'reverses_entry_id');
    }

    public function postedBy(): BelongsTo
    {
        return $this->belongsTo(user_model_class(), 'posted_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(user_model_class(), 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(user_model_class(), 'updated_by');
    }

    /**
     * Scope to filter by status.
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Determine if the entry is in draft status.
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Determine if the entry is posted.
     */
    public function isPosted(): bool
    {
        return $this->status === 'posted';
    }
}
