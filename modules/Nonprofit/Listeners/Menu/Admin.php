<?php

namespace Modules\Nonprofit\Listeners\Menu;

use App\Events\Menu\AdminCreated as Event;
use App\Traits\Modules;
use App\Traits\Permissions;

class Admin
{
    use Modules, Permissions;

    /**
     * Handle the event.
     *
     * @param  Event  $event
     * @return void
     */
    public function handle(Event $event)
    {
        if (! $this->moduleIsEnabled('nonprofit')) {
            return;
        }

        $event->menu->dropdown(
            trans('nonprofit::general.name'),
            [],
            70,
            ['icon' => 'hand-holding-heart']
        );

        // Chart of Accounts
        if ($this->canAccessMenuItem(
            trans_choice('nonprofit::general.coa', 2),
            'read-nonprofit-coa'
        )) {
            $event->menu->route(
                'nonprofit.coa.index',
                trans_choice('nonprofit::general.coa', 2),
            );
        }

        // Funds
        if ($this->canAccessMenuItem(
            trans_choice('nonprofit::general.fund', 2),
            'read-nonprofit-funds'
        )) {
            $event->menu->route(
                'nonprofit.funds.index',
                trans_choice('nonprofit::general.fund', 2),
            );
        }

        // Programs
        if ($this->canAccessMenuItem(
            trans_choice('nonprofit::general.program', 2),
            'read-nonprofit-programs'
        )) {
            $event->menu->route(
                'nonprofit.programs.index',
                trans_choice('nonprofit::general.program', 2),
            );
        }

        // Functional Classes
        if ($this->canAccessMenuItem(
            trans_choice('nonprofit::general.functional_class', 2),
            'read-nonprofit-functional-classes'
        )) {
            $event->menu->route(
                'nonprofit.functional-classes.index',
                trans_choice('nonprofit::general.functional_class', 2),
            );
        }

        // Fiscal Periods
        if ($this->canAccessMenuItem(
            trans_choice('nonprofit::general.fiscal_period', 2),
            'read-nonprofit-fiscal-periods'
        )) {
            $event->menu->route(
                'nonprofit.fiscal-periods.index',
                trans_choice('nonprofit::general.fiscal_period', 2),
            );
        }

        // Journal Entries
        if ($this->canAccessMenuItem(
            trans_choice('nonprofit::general.journal_entry', 2),
            'read-nonprofit-journal-entries'
        )) {
            $event->menu->route(
                'nonprofit.journal-entries.index',
                trans_choice('nonprofit::general.journal_entry', 2),
            );
        }

        // Settings section
        $event->menu->dropdown(
            trans_choice('general.settings', 2),
            [],
            1,
            ['icon' => 'settings']
        );

        // Account Mappings
        if ($this->canAccessMenuItem(
            trans('nonprofit::general.account_mappings'),
            'read-nonprofit-settings'
        )) {
            $event->menu->route(
                'nonprofit.settings.account-mappings.edit',
                trans('nonprofit::general.account_mappings'),
            );
        }

        // Dimension Defaults
        if ($this->canAccessMenuItem(
            trans('nonprofit::general.dimension_defaults'),
            'read-nonprofit-settings'
        )) {
            $event->menu->route(
                'nonprofit.settings.dimension-defaults.edit',
                trans('nonprofit::general.dimension_defaults'),
            );
        }
    }
}
