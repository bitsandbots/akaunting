<?php

namespace Modules\Nonprofit\Models;

use App\Abstracts\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Nonprofit\Enums\JournalEntryStatus;

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
    /**
     * Set inside withSanctionedReversal() to allow exactly one transition —
     * posted → reversed — to bypass the immutability guard. Any other update
     * attempt against a non-draft entry must throw.
     */
    protected static bool $sanctionedReversal = false;

    /**
     * Run a closure with the sanctioned-reversal bypass enabled. Only
     * LedgerService::reverse() should call this; it ring-fences the single
     * legal mutation against a posted entry (the link + status flip that
     * records its reversal).
     */
    public static function withSanctionedReversal(\Closure $callback): mixed
    {
        $previous = self::$sanctionedReversal;
        self::$sanctionedReversal = true;

        try {
            return $callback();
        } finally {
            self::$sanctionedReversal = $previous;
        }
    }

    protected static function booted(): void
    {
        static::deleting(function (self $entry) {
            if ($entry->status !== JournalEntryStatus::Draft->value) {
                throw new \RuntimeException(trans('nonprofit::general.cannot_delete_posted_journal_entry'));
            }
        });

        // Spec validation rule 8: posted entries are read-only; the only legal
        // mutation is the posted→reversed transition produced by
        // LedgerService::reverse(), which routes through withSanctionedReversal().
        // Reversed and void are terminal — no further updates of any kind.
        static::updating(function (self $entry) {
            $original = $entry->getOriginal('status');

            // Reaching 'reversed' is reserved for LedgerService::reverse() —
            // it is the only path that wraps the update in withSanctionedReversal().
            if (
                $entry->isDirty('status')
                && $entry->status === JournalEntryStatus::Reversed->value
                && ! self::$sanctionedReversal
            ) {
                throw new \RuntimeException(trans(
                    'nonprofit::general.cannot_modify_posted_journal_entry',
                    ['id' => $entry->id]
                ));
            }

            if ($original === JournalEntryStatus::Draft->value) {
                return;
            }

            if (in_array($original, [JournalEntryStatus::Reversed->value, JournalEntryStatus::Void->value], true)) {
                throw new \RuntimeException(trans(
                    'nonprofit::general.cannot_modify_terminal_journal_entry',
                    ['id' => $entry->id, 'status' => $original]
                ));
            }

            // original === posted — only the sanctioned posted→reversed flip is legal.
            $isReversal = self::$sanctionedReversal
                && $entry->status === JournalEntryStatus::Reversed->value;

            if (! $isReversal) {
                throw new \RuntimeException(trans(
                    'nonprofit::general.cannot_modify_posted_journal_entry',
                    ['id' => $entry->id]
                ));
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

    protected static function newFactory()
    {
        return \Modules\Nonprofit\Database\Factories\JournalEntry::new();
    }
}
