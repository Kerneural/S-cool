<?php

namespace App\Providers;

use App\Contracts\PaymentGateway;
use App\Services\Payment\FakePaymentGateway;
use App\Services\Payment\SePayGateway;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(PaymentGateway::class, function ($app) {
            if ($app->environment('testing')) {
                return new FakePaymentGateway;
            }

            return new SePayGateway;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerModuleTranslations();
    }

    /**
     * Register per-module JSON translation catalogs sorted deterministically (D14).
     */
    protected function registerModuleTranslations(): void
    {
        $modulesPath = lang_path('modules');
        if (! is_dir($modulesPath)) {
            return;
        }

        $moduleDirs = glob($modulesPath.'/*', GLOB_ONLYDIR) ?: [];
        sort($moduleDirs, SORT_STRING);

        foreach ($moduleDirs as $moduleDir) {
            $this->loadJsonTranslationsFrom($moduleDir);
        }
    }
}
