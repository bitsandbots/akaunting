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
        ]);
    }
}
