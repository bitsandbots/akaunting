<?php

namespace Modules\Nonprofit\Models;

use App\Abstracts\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FunctionalClass extends Model
{
    use HasFactory;

    protected $table = 'functional_classes';

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'parent_class',
        'is_system',
        'enabled',
    ];

    protected $casts = [
        'is_system' => 'boolean',
        'enabled' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    /**
     * Sortable columns.
     */
    protected $sortable = [
        'code',
        'name',
        'parent_class',
        'enabled',
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::deleting(function (self $functionalClass) {
            if ($functionalClass->is_system) {
                throw new \RuntimeException(trans('nonprofit::general.cannot_delete_system_functional_class'));
            }
        });
    }

    public function journalEntryLines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class, 'functional_class_id');
    }

    /**
     * Scope to filter by parent class.
     */
    public function scopeParentClass($query, string $parentClass)
    {
        return $query->where('parent_class', $parentClass);
    }
}
