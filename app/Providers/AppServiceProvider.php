<?php
// app/Providers/AppServiceProvider.php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Enregistrement du middleware SuperAdmin
        $this->app['router']->aliasMiddleware('super.admin', \App\Http\Middleware\SuperAdmin::class);
        
        // Utiliser le template bootstrap-5 pour la pagination
        Paginator::useBootstrapFive();
    }
}
?>
