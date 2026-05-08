<?php

namespace Modules\Nonprofit\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Nonprofit\Enums\FunctionalClassParentClass;
use Modules\Nonprofit\Models\FunctionalClass;

class FunctionalClassSeeder extends Seeder
{
    /**
     * System functional classes always seeded per company.
     * Uses firstOrCreate for idempotency.
     */
    protected static array $systemClasses = [
        [
            'code'         => 'PS',
            'name'         => 'Program Services',
            'parent_class' => 'program_services',
        ],
        [
            'code'         => 'MG',
            'name'         => 'Management & General',
            'parent_class' => 'management_general',
        ],
        [
            'code'         => 'FR',
            'name'         => 'Fundraising',
            'parent_class' => 'fundraising',
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companyId = $this->command->argument('company');

        foreach (static::$systemClasses as $data) {
            FunctionalClass::firstOrCreate(
                [
                    'company_id' => $companyId,
                    'code'       => $data['code'],
                ],
                [
                    'name'         => $data['name'],
                    'parent_class' => $data['parent_class'],
                    'is_system'    => true,
                    'enabled'      => true,
                ]
            );
        }
    }
}
