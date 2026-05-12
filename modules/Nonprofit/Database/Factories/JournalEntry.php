<?php

namespace Modules\Nonprofit\Database\Factories;

use App\Abstracts\Factory;
use Modules\Nonprofit\Enums\JournalEntryStatus;
use Modules\Nonprofit\Models\JournalEntry as Model;

class JournalEntry extends Factory
{
    protected $model = Model::class;

    public function definition(): array
    {
        return [
            'company_id'    => $this->company->id,
            'entry_number'  => 'JE-' . $this->faker->unique()->numerify('######'),
            'entry_date'    => $this->faker->date(),
            'description'   => $this->faker->sentence(4),
            'reference'     => null,
            'transaction_id'=> null,
            'status'        => JournalEntryStatus::Draft->value,
            'currency_code' => $this->company->default_currency ?? 'USD',
            'currency_rate' => 1.0,
            'created_by'    => $this->user?->id,
        ];
    }
}
