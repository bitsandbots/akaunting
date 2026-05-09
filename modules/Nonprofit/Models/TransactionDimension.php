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
