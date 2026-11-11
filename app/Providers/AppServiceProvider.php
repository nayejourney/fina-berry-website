<?php

namespace App\Providers;

use App\Services\CartService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();
        Carbon::setLocale('id');

        // Jumlah item keranjang untuk ikon di navbar publik.
        View::composer('layouts.public', function ($view) {
            $view->with('cartCount', app(CartService::class)->count());
        });

        // Lencana jumlah pesanan Menunggu di sidebar admin.
        View::composer('layouts.admin', function ($view) {
            $view->with('waitingCount', \App\Models\Order::where('status', \App\Models\Order::STATUS_PENDING)->count());
        });
    }
}
