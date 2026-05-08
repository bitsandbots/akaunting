<?php

namespace Modules\Nonprofit\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Nonprofit\Enums\AccountType;
use Modules\Nonprofit\Models\ChartOfAccount;

class SuspenseAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companyId = $this->command->argument('company');

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
