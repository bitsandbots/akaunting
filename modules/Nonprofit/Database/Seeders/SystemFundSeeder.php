<?php

namespace Modules\Nonprofit\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Nonprofit\Enums\FundType;
use Modules\Nonprofit\Models\Fund;

class SystemFundSeeder extends Seeder
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

        Fund::firstOrCreate(
            [
                'company_id' => $companyId,
                'code'       => 'UNRESTRICTED',
            ],
            [
                'name'        => 'Unrestricted Operating',
                'type'        => FundType::WithoutDonorRestrictions->value,
                'description' => 'Default unrestricted operating fund — seeded automatically.',
                'enabled'     => true,
            ]
        );
    }
}
