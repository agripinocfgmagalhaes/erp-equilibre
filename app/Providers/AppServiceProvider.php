<?php
namespace App\Providers;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use App\Models\ContaPagar;
use App\Models\ContaReceber;
use App\Observers\ContaPagarObserver;
use App\Observers\ContaReceberObserver;
class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}
    public function boot(): void
    {
        ContaPagar::observe(ContaPagarObserver::class);
        ContaReceber::observe(ContaReceberObserver::class);

        RateLimiter::for('portal', fn () => Limit::perMinute(10));
        RateLimiter::for('template', fn () => Limit::perMinute(30));
    }
}
