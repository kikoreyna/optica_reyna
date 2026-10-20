<?php

namespace App\Providers;

use App\Models\Empresa;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Pluralizer;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Pluralizer::useLanguage('spanish');

        View::composer('layouts.app', function ($view): void {
            $view->with('empresa', Empresa::query()->find(1));
        });
    }
}
