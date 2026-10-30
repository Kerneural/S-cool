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
        $classroomLang = base_path('lang/modules/classroom');
        if (is_dir($classroomLang)) {
            $this->loadJsonTranslationsFrom($classroomLang);
        }
    }
}
