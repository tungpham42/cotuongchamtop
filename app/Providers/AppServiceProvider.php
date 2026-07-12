<?php

namespace App\Providers;

use App\Services\LocalizedUrlService;
use App\Services\XiangqiEngineService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use App\Contracts\BracketGeneratorInterface;
use App\Services\SingleEliminationBracketGenerator;
use App\Presenters\UserPresenter;

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

        $this->app->bind(
            BracketGeneratorInterface::class,
            SingleEliminationBracketGenerator::class
        );
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
            $view->with('userPresenter', app(UserPresenter::class));
            $view->with('showAds', !($user && $user->hasAdsRemoved()));
        });
    }
}
