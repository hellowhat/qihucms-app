<?php

namespace Qihucms\App;

use Illuminate\Support\ServiceProvider;
use Qihucms\App\Console\Install;
use Qihucms\App\Console\Uninstall;

class QihuAppServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->commands([
            Install::class,
            Uninstall::class
        ]);
        
        $this->loadViewsFrom(__DIR__.'/../resources/views','qihu-app');
        $this->loadRoutesFrom(__DIR__.'/routes.php');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang','qihu-app');

        $this->publishes([
            __DIR__.'/../resources/asset' => public_path('asset/app'),
        ], 'app');
    }
}
