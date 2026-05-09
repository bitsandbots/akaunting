<?php

namespace Modules\Nonprofit\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Nonprofit\Enums\FunctionalClassParentClass;
use Modules\Nonprofit\Models\FunctionalClass;

class FunctionalClassSeeder extends Seeder
{
    /**
     * Optional company ID set programmatically (e.g., during module installation).
     *
     * @var int|null
     */
    private ?int $companyId = null;

    /**
     * Set the company ID before calling run() when not invoked via Artisan command.
     *
     * @param int $companyId
     *
     * @return $this
     */
    public function setCompanyId(int $companyId): self
    {
        $this->companyId = $companyId;

        return $this;
    }

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
        $companyId = $this->companyId ?? $this->command->argument('company');

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
