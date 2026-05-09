<?php

namespace Modules\Nonprofit\Models;

use App\Abstracts\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Program extends Model
{
    use HasFactory;

    protected $table = 'programs';

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'description',
        'start_date',
        'end_date',
        'enabled',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'enabled' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    /**
     * Sortable columns.
     */
    protected $sortable = [
        'code',
        'name',
        'start_date',
        'end_date',
        'enabled',
    ];

    public function journalEntryLines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class, 'program_id');
    }
}
