<?php

namespace App\Providers;

use App\Repositories\Contracts\MenuRepositoryInterface;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
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
        Schema::defaultStringLength(191);

        // Gunakan Bootstrap 5 styling untuk pagination links.
        Paginator::useBootstrapFive();

        // Share top-level active menus (with their active children) to all views.
        // Data diambil dari MenuRepository yang memakai cache (TTL 120s) sehingga
        // tidak membebani database pada setiap request.
        View::composer('*', function ($view) {
            if (Auth::check() && Schema::hasTable('menus')) {
                $sidebarMenus = app(MenuRepositoryInterface::class)->getSidebarMenus();
                $view->with('sidebarMenus', $sidebarMenus);
            }
        });
    }
}
