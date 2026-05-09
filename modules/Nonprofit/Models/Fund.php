<?php

namespace Modules\Nonprofit\Models;

use App\Abstracts\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Fund extends Model
{
    use HasFactory;

    protected $table = 'funds';

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'type',
        'restriction_detail',
        'description',
        'parent_id',
        'enabled',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    /**
     * Sortable columns.
     */
    protected $sortable = [
        'code',
        'name',
        'type',
        'enabled',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(static::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(static::class, 'parent_id');
    }

    public function journalEntryLines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class, 'fund_id');
    }

    /**
     * Scope to filter by fund type.
     */
    public function scopeType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
