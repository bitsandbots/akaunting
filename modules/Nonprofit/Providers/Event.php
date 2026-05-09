<?php

namespace Modules\Nonprofit\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as Provider;

class Event extends Provider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        // Module lifecycle
        \App\Events\Module\Installed::class => [
            \Modules\Nonprofit\Listeners\FinishInstallation::class,
        ],

        // Admin menu integration
        \App\Events\Menu\AdminCreated::class => [
            \Modules\Nonprofit\Listeners\Menu\Admin::class,
        ],

        // Transaction bridge -> double-entry ledger
        \App\Events\Banking\TransactionCreated::class => [
            \Modules\Nonprofit\Listeners\PostToLedger::class . '@onTransactionCreated',
        ],
        \App\Events\Banking\TransactionUpdated::class => [
            \Modules\Nonprofit\Listeners\PostToLedger::class . '@onTransactionUpdated',
        ],
        \App\Events\Banking\TransactionDeleted::class => [
            \Modules\Nonprofit\Listeners\PostToLedger::class . '@onTransactionDeleted',
        ],
    ];

    /**
     * The subscriber classes to register.
     *
     * @var array
     */
    protected $subscribe = [
        \Modules\Nonprofit\Listeners\PostToLedger::class,
    ];

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return true;
    }

    /**
     * Get the listener directories that should be used to discover events.
     *
     * @return array
     */
    protected function discoverEventsWithin()
    {
        return [
            __DIR__ . '/../Listeners',
        ];
    }
}
