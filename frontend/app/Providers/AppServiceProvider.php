<?php

namespace App\Providers;

use App\Services\BackendApiClient;
use App\Support\StorefrontCart;
use Illuminate\Support\Carbon;
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
            $api = app(BackendApiClient::class);
            $categoriesResponse = $api->get('categorias');
            $view->with('storefrontUser', request()->session()->get('web_user'));
            $view->with('storefrontCategories', collect($api->okData($categoriesResponse, null, [])));
            $view->with('storefrontCartCount', StorefrontCart::count(request()));
        });

        View::composer('layouts.admin', function ($view): void {
            $adminUser = request()->session()->get('web_admin');
            $notifications = collect();

            if ($adminUser && request()->session()->has('auth_token')) {
                $api = app(BackendApiClient::class);
                $response = $api->get('notificaciones/admin/pendientes');
                $notifications = collect($api->okData($response, 'notificaciones', []))
                    ->map(function ($item) {
                        $row = (object) $item;
                        $row->title = (string) ($row->title ?? $row->titulo ?? 'Notificacion');
                        $row->body = (string) ($row->body ?? $row->mensaje ?? '');
                        $row->createdAt = !empty($row->createdAt ?? null)
                            ? Carbon::parse($row->createdAt)
                            : (!empty($row->created_at ?? null) ? Carbon::parse($row->created_at) : null);

                        return $row;
                    })
                    ->values();
            }

            $view->with('adminUser', $adminUser);
            $view->with('adminNotifications', $notifications);
            $view->with('adminNotificationsCount', $notifications->count());
        });
    }
}
