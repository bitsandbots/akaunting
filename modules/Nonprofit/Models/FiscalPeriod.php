<?php

namespace Modules\Nonprofit\Models;

use App\Abstracts\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FiscalPeriod extends Model
{
    use HasFactory;

    protected $table = 'fiscal_periods';

    protected $fillable = [
        'company_id',
        'name',
        'start_date',
        'end_date',
        'status',
        'closed_at',
        'closed_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'closed_at' => 'datetime',
    ];

    /**
     * Sortable columns.
     */
    protected $sortable = [
        'name',
        'start_date',
        'end_date',
        'status',
    ];

    protected static function booted(): void
    {
        static::deleting(function (self $period) {
            throw new \RuntimeException(
                'Fiscal periods are append-only audit objects and cannot be deleted. '
                . 'Use PeriodGuard::reopen() to flip status to open.'
            );
        });
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(user_model_class(), 'closed_by');
    }

    /**
     * Determine if the period is open.
     */
    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    /**
     * Determine if the period is closed.
     */
    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }
}
