<?php

namespace Modules\Nonprofit\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Nonprofit\Enums\AccountType;
use Modules\Nonprofit\Models\ChartOfAccount;

class SuspenseAccountSeeder extends Seeder
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
     * Run the database seeds.
     */
    public function run(): void
    {
        $companyId = $this->companyId ?? $this->command->argument('company');

        ChartOfAccount::firstOrCreate(
            [
                'company_id' => $companyId,
                'code'       => '9999',
            ],
            [
                'name'        => 'Suspense',
                'type'        => AccountType::Asset->value,
                'description' => 'Suspense account — used when no COA mapping exists for a transaction category.',
                'enabled'     => true,
            ]
        );
    }
}
