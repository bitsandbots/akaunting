<?php

namespace Modules\Nonprofit\Models;

use App\Abstracts\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionDimension extends Model
{
    use HasFactory;

    protected $table = 'transaction_dimensions';

    protected $fillable = [
        'company_id',
        'transaction_id',
        'fund_id',
        'program_id',
        'functional_class_id',
        'allocation_percent',
        'allocation_amount',
        'sort_order',
    ];

    protected $casts = [
        'allocation_percent'    => 'double',
        'allocation_amount'     => 'double',
        'sort_order'            => 'integer',
    ];

    /**
     * Sortable columns.
     */
    protected $sortable = [
        'sort_order',
    ];

    /**
     * Set true around fixture/seeder writes that intentionally bypass the
     * cross-row validator (e.g. when populating known-good test data row by
     * row would otherwise fail R2 mid-build). Prefer wrapping the writes in
     * a closure that toggles the flag back off on completion.
     */
    public static bool $skipValidation = false;

    protected static function booted(): void
    {
        static::saving(function (self $row) {
            if (self::$skipValidation) {
                return;
            }

            $tx = $row->transaction()->first();
            if (! $tx) {
                return;
            }

            $siblings = self::where('transaction_id', $row->transaction_id)
                ->when($row->exists, fn ($q) => $q->where('id', '!=', $row->id))
                ->get()
                ->all();

            $set = array_merge(
                array_map(fn (self $s) => $s->toValidationArray(), $siblings),
                [$row->toValidationArray()],
            );

            app(\Modules\Nonprofit\Services\DimensionAllocationValidator::class)
                ->validate($set, (float) $tx->amount);
        });
    }

    public function toValidationArray(): array
    {
        return [
            'fund_id'             => $this->fund_id,
            'program_id'          => $this->program_id,
            'functional_class_id' => $this->functional_class_id,
            'allocation_percent'  => $this->allocation_percent,
            'allocation_amount'   => $this->allocation_amount,
        ];
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo('App\Models\Banking\Transaction', 'transaction_id');
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
}
