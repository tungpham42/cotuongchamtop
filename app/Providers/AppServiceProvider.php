<?php

namespace App\Providers;

use App\Services\LocalizedUrlService;
use App\Services\XiangqiEngineService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(LocalizedUrlService::class);

        $this->app->singleton(XiangqiEngineService::class, function ($app) {
            return new XiangqiEngineService();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        View::composer('*', function ($view) {
            $user = Auth::user();
            $view->with('showAds', !($user && $user->hasAdsRemoved()));
        });
    }
}
