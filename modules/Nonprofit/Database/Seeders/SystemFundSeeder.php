<?php

namespace Modules\Nonprofit\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Nonprofit\Enums\FundType;
use Modules\Nonprofit\Models\Fund;

class SystemFundSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companyId = $this->command->argument('company');

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
