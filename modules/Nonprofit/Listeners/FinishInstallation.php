<?php

namespace Modules\Nonprofit\Listeners;

use App\Events\Module\Installed as Event;
use App\Traits\Permissions;

class FinishInstallation
{
    use Permissions;

    public $alias = 'nonprofit';

    /**
     * Handle the event.
     *
     * @param  Event $event
     * @return void
     */
    public function handle(Event $event)
    {
        if ($event->alias != $this->alias) {
            return;
        }

        $this->updatePermissions();
        $this->runSeeders();
    }

    /**
     * Register module permissions and attach to admin roles.
     *
     * Permission format: c=create, r=read, u=update, d=delete
     *
     * @return void
     */
    protected function updatePermissions()
    {
        $this->attachPermissionsToAdminRoles([
            $this->alias . '-coa'        => 'c,r,u,d',
            $this->alias . '-funds'      => 'c,r,u,d',
            $this->alias . '-programs'   => 'c,r,u,d',
            $this->alias . '-functional-classes' => 'c,r,u,d',
            $this->alias . '-journal-entries'   => 'c,r,u,d',
            $this->alias . '-fiscal-periods'    => 'c,r,u,d',
            $this->alias . '-settings'   => 'r,u',
            $this->alias . '-reports'    => 'r',
            // Scope-specific keys for settings sub-controllers.
            // assignPermissionsToController() produces e.g.
            // 'nonprofit-settings-account-mappings', so we must
            // register those granular keys.
            $this->alias . '-settings-account-mappings'  => 'r,u',
            $this->alias . '-settings-dimension-defaults' => 'r,u',
        ]);
    }

    /**
     * Run required seeders during module installation.
     *
     * Seeds the functional expense classifications (FASB/SFAS 117),
     * system general fund (code 0001), and suspense account (code 9999).
     *
     * @return void
     */
    protected function runSeeders()
    {
        $companyId = company_id();

        // Seed functional expense classifications.
        $seeder = new \Modules\Nonprofit\Database\Seeders\FunctionalClassSeeder();
        $seeder->setCompanyId($companyId);
        $seeder->run();

        // Seed system general fund (Unrestricted).
        $seeder = new \Modules\Nonprofit\Database\Seeders\SystemFundSeeder();
        $seeder->setCompanyId($companyId);
        $seeder->run();

        // Seed suspense account for unmapped transactions.
        $seeder = new \Modules\Nonprofit\Database\Seeders\SuspenseAccountSeeder();
        $seeder->setCompanyId($companyId);
        $seeder->run();
    }
}
