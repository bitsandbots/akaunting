<?php

namespace Modules\Nonprofit\Providers;

use Illuminate\Support\ServiceProvider as Provider;

class Observer extends Provider
{
    /**
     * The model observers registered by the module.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $observers = [];

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        foreach ($this->observers as $model => $observers) {
            foreach ($observers as $observer) {
                $model::observe($observer);
            }
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
