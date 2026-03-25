<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Merchant;
use Illuminate\Support\Facades\Auth;

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
        View::composer(['layouts.admin', 'admin.*'], function ($view) {
            $view->with('pendingKycCount', Merchant::where('kyc_status', 'pending')->count());
            
            // $user = Auth::guard('admin')->user() ?? Auth::user();
            // $unreadNotifications = $user ? $user->notifications()->whereNull('read_at')->count() : 0;
            $view->with('unreadNotifications', 0);
        });
    }
}

if (!function_exists('setting')) {
    function setting(string $key, $default = null) {
        return \Illuminate\Support\Facades\Cache::remember('setting:'.$key, 3600, function() use ($key, $default) {
            return \App\Models\Setting::where('key', $key)->value('value') ?? $default;
        });
    }
}
