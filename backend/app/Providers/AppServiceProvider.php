<?php

namespace App\Providers;

use App\Support\StorefrontCart;
use Illuminate\Support\Facades\DB;
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
        View::composer('layouts.storefront', function ($view): void {
            $categories = DB::table('categorias')
                ->select(['id', 'nombre'])
                ->where('activo', 1)
                ->orderBy('nombre')
                ->get();

            $view->with('storefrontUser', request()->session()->get('web_user'));
            $view->with('storefrontCategories', $categories);
            $view->with('storefrontCartCount', StorefrontCart::count(request()));
        });
    }
}
