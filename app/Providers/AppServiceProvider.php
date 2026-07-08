<?php
namespace App\Providers;
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
    }
}
