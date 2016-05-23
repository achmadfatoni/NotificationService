<?php

namespace Klsandbox\NotificationService;

use Illuminate\Support\ServiceProvider;
use Klsandbox\NotificationService\Console\Commands\SendPendingNotifications;

class NotificationServiceServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('command.klsandbox.sendpendingnotifications', function () {
            return new SendPendingNotifications();
        });

        $this->commands('command.klsandbox.sendpendingnotifications');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../../../database/migrations/' => database_path('/migrations'),
        ], 'migrations');
    }
}
