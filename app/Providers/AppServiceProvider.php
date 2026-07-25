<?php

namespace App\Providers;

use App\Domain\Payment\Contracts\PaymentGateway;
use App\Infrastructure\Payment\TriPay\TriPayGateway;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(PaymentGateway::class, TriPayGateway::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
